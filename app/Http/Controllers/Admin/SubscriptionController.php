<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Company;
use App\Package;
use App\Subscription;
use App\SubscriptionPayment;
use App\User;
use App\Notifications\SubscriptionApproved;
use App\Notifications\PaymentReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of all subscriptions (super_admin).
     */
    public function index(Request $request)
    {
        $query = Subscription::with(['company', 'package', 'createdBy', 'approvedBy']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('package_id')) {
            $query->where('package_id', $request->package_id);
        }

        $subscriptions = $query->orderBy('created_at', 'desc')->paginate(20);
        $companies = Company::orderBy('name')->get();
        $packages = Package::active()->ordered()->get();

        return view('admin.subscriptions.index', compact('subscriptions', 'companies', 'packages'));
    }

    /**
     * Display pending subscriptions (for approval).
     */
    public function pending()
    {
        $subscriptions = Subscription::with(['company', 'package', 'createdBy'])
            ->pending()
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        return view('admin.subscriptions.pending', compact('subscriptions'));
    }

    /**
     * Display expiring soon subscriptions.
     */
    public function expiring()
    {
        $subscriptions = Subscription::with(['company', 'package'])
            ->expiringSoon(14) // Próximos 14 días
            ->orderBy('ends_at', 'asc')
            ->paginate(20);

        return view('admin.subscriptions.expiring', compact('subscriptions'));
    }

    /**
     * Show form to create a new subscription (admin creates for company).
     */
    public function create()
    {
        $companies = Company::where('status', 'active')->orderBy('name')->get();
        $packages = Package::active()->ordered()->get();

        return view('admin.subscriptions.create', compact('companies', 'packages'));
    }

    /**
     * Store a newly created subscription.
     */
    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'package_id' => 'required|exists:packages,id',
            'starts_at' => 'required|date',
            'billing_periods' => 'required|integer|min:1|max:12',
            'payment_method' => 'required|in:transfer,sinpe,card',
            'auto_approve' => 'nullable|boolean',
            'start_trial' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $package = Package::findOrFail($request->package_id);
        $startsAt = Carbon::parse($request->starts_at);

        // Calculate end date based on billing period
        $endsAt = match($package->billing_period) {
            'monthly' => $startsAt->copy()->addMonths($request->billing_periods),
            'quarterly' => $startsAt->copy()->addMonths($request->billing_periods * 3),
            'yearly' => $startsAt->copy()->addYears($request->billing_periods),
            default => $startsAt->copy()->addMonth(),
        };

        // Check if company already has active subscription
        $company = Company::findOrFail($request->company_id);
        if ($company->hasActiveSubscription()) {
            return redirect()->back()
                ->with('error', 'La empresa ya tiene una suscripción activa.')
                ->withInput();
        }

        $subscription = Subscription::create([
            'company_id' => $request->company_id,
            'package_id' => $request->package_id,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => $request->has('start_trial') && $package->trial_days > 0 ? 'trial' : 'pending',
            'payment_method' => $request->payment_method,
            'auto_renew' => $request->has('auto_renew'),
            'notes' => $request->notes,
            'created_by' => Auth::id(),
        ]);

        // If starting trial
        if ($request->has('start_trial') && $package->trial_days > 0) {
            $subscription->startTrial();
        }

        // If auto-approve (super admin creating)
        if ($request->has('auto_approve') && Auth::user()->isSuperAdmin()) {
            $subscription->approve(Auth::user());
        }

        return redirect()->route('admin.subscriptions.show', $subscription)
            ->with('success', 'Suscripción creada correctamente.');
    }

    /**
     * Display the specified subscription.
     */
    public function show(Subscription $subscription)
    {
        $subscription->load(['company.users', 'package', 'payments', 'createdBy', 'approvedBy']);

        return view('admin.subscriptions.show', compact('subscription'));
    }

    /**
     * Approve a pending subscription.
     */
    public function approve(Request $request, Subscription $subscription)
    {
        if ($subscription->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Solo se pueden aprobar suscripciones pendientes.');
        }

        $subscription->approve(Auth::user());

        // Notificar al owner de la empresa
        if ($subscription->company && $subscription->company->owner) {
            $subscription->company->owner->notify(new SubscriptionApproved($subscription));
        }

        return redirect()->back()
            ->with('success', 'Suscripción aprobada correctamente.');
    }

    /**
     * Suspend a subscription.
     */
    public function suspend(Request $request, Subscription $subscription)
    {
        $request->validate([
            'reason' => 'nullable|string',
        ]);

        $subscription->update([
            'status' => 'suspended',
            'notes' => $subscription->notes . "\n\nSuspendida: " . ($request->reason ?? 'Sin razón especificada'),
        ]);

        return redirect()->back()
            ->with('success', 'Suscripción suspendida.');
    }

    /**
     * Cancel a subscription.
     */
    public function cancel(Request $request, Subscription $subscription)
    {
        $subscription->update(['status' => 'cancelled']);

        return redirect()->back()
            ->with('success', 'Suscripción cancelada.');
    }

    /**
     * Extend a subscription.
     */
    public function extend(Request $request, Subscription $subscription)
    {
        $request->validate([
            'periods' => 'required|integer|min:1|max:12',
        ]);

        $package = $subscription->package;
        $currentEnd = Carbon::parse($subscription->ends_at);

        $newEnd = match($package->billing_period) {
            'monthly' => $currentEnd->addMonths($request->periods),
            'quarterly' => $currentEnd->addMonths($request->periods * 3),
            'yearly' => $currentEnd->addYears($request->periods),
            default => $currentEnd->addMonth(),
        };

        $subscription->update([
            'ends_at' => $newEnd,
            'status' => 'active',
        ]);

        return redirect()->back()
            ->with('success', "Suscripción extendida hasta {$newEnd->format('d/m/Y')}.");
    }

    /**
     * Display subscription payments.
     */
    public function payments(Subscription $subscription)
    {
        $payments = $subscription->payments()->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.subscriptions.payments', compact('subscription', 'payments'));
    }

    /**
     * Show form to register a payment.
     */
    public function createPayment(Subscription $subscription)
    {
        return view('admin.subscriptions.create-payment', compact('subscription'));
    }

    /**
     * Store a payment for a subscription.
     */
    public function storePayment(Request $request, Subscription $subscription)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|in:CRC,USD',
            'payment_method' => 'required|in:transfer,sinpe,card',
            'payment_reference' => 'nullable|string|max:255',
            'proof_image' => 'nullable|image|max:5120', // 5MB
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
            'auto_approve' => 'nullable|boolean',
        ]);

        $data = $request->only(['amount', 'currency', 'payment_method', 'payment_reference', 'payment_date', 'notes']);
        $data['subscription_id'] = $subscription->id;
        $data['status'] = 'pending';

        if ($request->hasFile('proof_image')) {
            $data['proof_image'] = $request->file('proof_image')->store('payment-proofs', 'public');
        }

        $payment = SubscriptionPayment::create($data);

        // Notificar a super admins sobre el nuevo pago
        if (!Auth::user()->isSuperAdmin()) {
            $admins = User::where('role', 'super_admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new PaymentReceived($payment));
            }
        }

        // Auto-approve if super admin
        if ($request->has('auto_approve') && Auth::user()->isSuperAdmin()) {
            $payment->approve(Auth::user());

            // Activate subscription if pending
            if ($subscription->status === 'pending') {
                $subscription->approve(Auth::user());
            }
        }

        return redirect()->route('admin.subscriptions.show', $subscription)
            ->with('success', 'Pago registrado correctamente.');
    }

    /**
     * Display pending payments (all subscriptions).
     */
    public function pendingPayments()
    {
        $payments = SubscriptionPayment::with(['subscription.company', 'subscription.package'])
            ->pending()
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        return view('admin.subscriptions.pending-payments', compact('payments'));
    }

    /**
     * Approve a payment.
     */
    public function approvePayment(SubscriptionPayment $payment)
    {
        $payment->approve(Auth::user());

        // If subscription was pending and this is the first approved payment, activate it
        $subscription = $payment->subscription;
        if ($subscription->status === 'pending') {
            $subscription->approve(Auth::user());

            // Notificar al owner de la empresa
            if ($subscription->company && $subscription->company->owner) {
                $subscription->company->owner->notify(new SubscriptionApproved($subscription));
            }
        }

        return redirect()->back()
            ->with('success', 'Pago aprobado correctamente.');
    }

    /**
     * Reject a payment.
     */
    public function rejectPayment(Request $request, SubscriptionPayment $payment)
    {
        $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        $payment->reject(Auth::user(), $request->rejection_reason);

        return redirect()->back()
            ->with('success', 'Pago rechazado.');
    }

    /**
     * My subscription (for company_admin/agent).
     */
    public function mySubscription()
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            return redirect()->route('home')
                ->with('error', 'No está asociado a ninguna empresa.');
        }

        $subscription = $company->activeSubscription();
        $payments = $subscription ? $subscription->payments()->orderBy('created_at', 'desc')->limit(10)->get() : collect();

        return view('admin.subscriptions.my', compact('company', 'subscription', 'payments'));
    }
}
