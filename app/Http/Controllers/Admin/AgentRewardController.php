<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentReward;
use App\Models\AgentRewardGrant;
use App\Models\EventLead;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentRewardController extends Controller
{
    // =========================================================
    // Reglas de Recompensas (sólo super_admin puede crear/editar/borrar)
    // =========================================================

    public function index()
    {
        abort_unless(Auth::user()->isSuperAdmin() || Auth::user()->isCompanyAdmin(), 403);

        $rewards = AgentReward::orderByDesc('is_active')->orderBy('min_conversions')->get();

        return view('admin.rewards.index', compact('rewards'));
    }

    public function create()
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        return view('admin.rewards.create');
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $request->validate([
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string',
            'min_conversions'  => 'required|integer|min:0',
            'max_conversions'  => 'nullable|integer|min:0|gte:min_conversions',
            'reward_type'      => 'required|in:' . implode(',', array_keys(AgentReward::types())),
            'reward_value'     => 'nullable|numeric|min:0',
            'reward_currency'  => 'nullable|in:CRC,USD',
            'is_active'        => 'boolean',
        ]);

        AgentReward::create([
            'name'            => $request->name,
            'description'     => $request->description,
            'min_conversions' => $request->min_conversions,
            'max_conversions' => $request->max_conversions ?: null,
            'reward_type'     => $request->reward_type,
            'reward_value'    => $request->reward_value ?? 0,
            'reward_currency' => $request->reward_currency ?? 'CRC',
            'is_active'       => $request->boolean('is_active', true),
            'created_by'      => Auth::id(),
        ]);

        return redirect()->route('admin.rewards.index')->with('success', 'Recompensa creada correctamente.');
    }

    public function edit(AgentReward $reward)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        return view('admin.rewards.edit', compact('reward'));
    }

    public function update(Request $request, AgentReward $reward)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $request->validate([
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string',
            'min_conversions'  => 'required|integer|min:0',
            'max_conversions'  => 'nullable|integer|min:0',
            'reward_type'      => 'required|in:' . implode(',', array_keys(AgentReward::types())),
            'reward_value'     => 'nullable|numeric|min:0',
            'reward_currency'  => 'nullable|in:CRC,USD',
            'is_active'        => 'boolean',
        ]);

        $reward->update([
            'name'            => $request->name,
            'description'     => $request->description,
            'min_conversions' => $request->min_conversions,
            'max_conversions' => $request->max_conversions ?: null,
            'reward_type'     => $request->reward_type,
            'reward_value'    => $request->reward_value ?? 0,
            'reward_currency' => $request->reward_currency ?? 'CRC',
            'is_active'       => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.rewards.index')->with('success', 'Recompensa actualizada.');
    }

    public function destroy(AgentReward $reward)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $reward->delete();

        return redirect()->route('admin.rewards.index')->with('success', 'Recompensa eliminada.');
    }

    public function toggleActive(AgentReward $reward)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $reward->update(['is_active' => !$reward->is_active]);

        return back()->with('success', 'Estado actualizado.');
    }

    // =========================================================
    // Gestión de Grants (company_admin + super_admin)
    // =========================================================

    public function grantIndex(Request $request)
    {
        $user      = Auth::user();
        $companyId = $user->isSuperAdmin()
            ? $request->get('company_id', $user->company_id)
            : $user->company_id;

        $month  = $request->get('month', now()->format('Y-m'));
        $from   = Carbon::parse($month . '-01')->startOfMonth();

        $grants = AgentRewardGrant::with(['agent', 'reward', 'grantedBy'])
            ->whereHas('agent', fn($q) => $q->where('company_id', $companyId))
            ->where('month', $from->format('Y-m-d'))
            ->latest('granted_at')
            ->get();

        $agents  = User::where('company_id', $companyId)->where('role', User::ROLE_AGENT)->orderBy('name')->get();
        $rewards = AgentReward::where('is_active', true)->orderBy('min_conversions')->get();

        return view('admin.rewards.grants.index', compact('grants', 'agents', 'rewards', 'month', 'companyId'));
    }

    public function grant(Request $request)
    {
        $request->validate([
            'user_id'   => 'required|exists:users,id',
            'reward_id' => 'required|exists:agent_rewards,id',
            'month'     => 'required|date_format:Y-m',
            'notes'     => 'nullable|string',
        ]);

        $me    = Auth::user();
        $agent = User::findOrFail($request->user_id);

        if (!$me->isSuperAdmin() && $agent->company_id !== $me->company_id) {
            abort(403);
        }

        $month = Carbon::parse($request->month . '-01');
        $from  = $month->copy()->startOfMonth();
        $to    = $month->copy()->endOfMonth();

        $conversions = EventLead::where('captured_by_user_id', $request->user_id)
            ->where('sale_status', 'converted')
            ->whereBetween('converted_at', [$from, $to])
            ->count();

        $leads = EventLead::where('captured_by_user_id', $request->user_id)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        AgentRewardGrant::updateOrCreate(
            [
                'user_id'   => $request->user_id,
                'reward_id' => $request->reward_id,
                'month'     => $month->format('Y-m-d'),
            ],
            [
                'conversions_count' => $conversions,
                'leads_count'       => $leads,
                'notes'             => $request->notes,
                'granted_by'        => $me->id,
                'granted_at'        => now(),
            ]
        );

        return back()->with('success', 'Recompensa otorgada correctamente.');
    }

    public function revokeGrant(AgentRewardGrant $grant)
    {
        $me = Auth::user();

        if (!$me->isSuperAdmin()) {
            $agent = $grant->agent;
            abort_unless($agent && $agent->company_id === $me->company_id, 403);
        }

        $grant->delete();

        return back()->with('success', 'Recompensa revocada.');
    }
}
