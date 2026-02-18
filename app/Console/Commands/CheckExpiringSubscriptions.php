<?php

namespace App\Console\Commands;

use App\Notifications\SubscriptionExpiring;
use App\Subscription;
use Illuminate\Console\Command;

class CheckExpiringSubscriptions extends Command
{
    protected $signature = 'subscriptions:check-expiring {--days=7 : Days before expiration to notify}';
    protected $description = 'Notificar a usuarios cuyas suscripciones están por vencer';

    public function handle()
    {
        $days = (int) $this->option('days');
        $targetDate = now()->addDays($days);

        $subscriptions = Subscription::where('status', 'active')
            ->whereDate('ends_at', '<=', $targetDate)
            ->whereDate('ends_at', '>=', now())
            ->with(['company.owner', 'company.users'])
            ->get();

        $notified = 0;

        foreach ($subscriptions as $subscription) {
            $daysLeft = now()->diffInDays($subscription->ends_at);

            // Notify company owner
            if ($subscription->company && $subscription->company->owner) {
                $owner = $subscription->company->owner;

                // Avoid duplicate notifications (check if already notified today)
                $alreadyNotified = $owner->notifications()
                    ->where('type', SubscriptionExpiring::class)
                    ->whereDate('created_at', today())
                    ->exists();

                if (!$alreadyNotified) {
                    $owner->notify(new SubscriptionExpiring($subscription, $daysLeft));
                    $notified++;
                }
            }

            // Notify company admins
            if ($subscription->company) {
                foreach ($subscription->company->users->where('role', 'company_admin') as $admin) {
                    if ($subscription->company->owner && $admin->id === $subscription->company->owner->id) {
                        continue; // Skip owner, already notified
                    }

                    $alreadyNotified = $admin->notifications()
                        ->where('type', SubscriptionExpiring::class)
                        ->whereDate('created_at', today())
                        ->exists();

                    if (!$alreadyNotified) {
                        $admin->notify(new SubscriptionExpiring($subscription, $daysLeft));
                        $notified++;
                    }
                }
            }
        }

        $this->info("Se notificaron {$notified} usuarios sobre suscripciones por vencer.");
    }
}
