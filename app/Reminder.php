<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Reminder extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'remindable_type',
        'remindable_id',
        'title',
        'description',
        'priority',
        'remind_at',
        'is_completed',
        'completed_at',
        'is_dismissed',
        'dismissed_at',
        'notification_sent',
        'notification_sent_at',
        'email_notification',
        'is_recurring',
        'recurrence_type',
        'recurrence_interval',
        'recurrence_ends_at',
    ];

    protected $casts = [
        'remind_at' => 'datetime',
        'completed_at' => 'datetime',
        'dismissed_at' => 'datetime',
        'notification_sent_at' => 'datetime',
        'recurrence_ends_at' => 'date',
        'is_completed' => 'boolean',
        'is_dismissed' => 'boolean',
        'notification_sent' => 'boolean',
        'email_notification' => 'boolean',
        'is_recurring' => 'boolean',
    ];

    // Prioridades
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    public static function getPriorities(): array
    {
        return [
            self::PRIORITY_LOW => 'Baja',
            self::PRIORITY_MEDIUM => 'Media',
            self::PRIORITY_HIGH => 'Alta',
            self::PRIORITY_URGENT => 'Urgente',
        ];
    }

    public static function getRecurrenceTypes(): array
    {
        return [
            'daily' => 'Diario',
            'weekly' => 'Semanal',
            'monthly' => 'Mensual',
            'yearly' => 'Anual',
        ];
    }

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function remindable()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopePending($query)
    {
        return $query->where('is_completed', false)
            ->where('is_dismissed', false);
    }

    public function scopeDue($query)
    {
        return $query->pending()
            ->where('remind_at', '<=', now());
    }

    public function scopeUpcoming($query, $minutes = 60)
    {
        return $query->pending()
            ->where('remind_at', '>', now())
            ->where('remind_at', '<=', now()->addMinutes($minutes));
    }

    public function scopeToday($query)
    {
        return $query->whereDate('remind_at', Carbon::today());
    }

    public function scopeOverdue($query)
    {
        return $query->pending()
            ->where('remind_at', '<', now());
    }

    public function scopeNeedsNotification($query)
    {
        return $query->pending()
            ->where('notification_sent', false)
            ->where('remind_at', '<=', now());
    }

    // Helpers
    public function getPriorityLabelAttribute(): string
    {
        return self::getPriorities()[$this->priority] ?? $this->priority;
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'secondary',
            self::PRIORITY_MEDIUM => 'info',
            self::PRIORITY_HIGH => 'warning',
            self::PRIORITY_URGENT => 'danger',
            default => 'secondary',
        };
    }

    public function getRecurrenceTypeLabelAttribute(): ?string
    {
        if (!$this->recurrence_type) {
            return null;
        }
        return self::getRecurrenceTypes()[$this->recurrence_type] ?? $this->recurrence_type;
    }

    public function isDue(): bool
    {
        return !$this->is_completed && !$this->is_dismissed && $this->remind_at <= now();
    }

    public function isOverdue(): bool
    {
        return !$this->is_completed && !$this->is_dismissed && $this->remind_at < now();
    }

    public function complete(): void
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);

        // Si es recurrente, crear el siguiente recordatorio
        if ($this->is_recurring && $this->shouldCreateNextRecurrence()) {
            $this->createNextRecurrence();
        }
    }

    public function dismiss(): void
    {
        $this->update([
            'is_dismissed' => true,
            'dismissed_at' => now(),
        ]);
    }

    public function snooze(int $minutes = 15): void
    {
        $this->update([
            'remind_at' => now()->addMinutes($minutes),
            'notification_sent' => false,
        ]);
    }

    public function markNotificationSent(): void
    {
        $this->update([
            'notification_sent' => true,
            'notification_sent_at' => now(),
        ]);
    }

    protected function shouldCreateNextRecurrence(): bool
    {
        if (!$this->is_recurring) {
            return false;
        }

        if ($this->recurrence_ends_at && Carbon::parse($this->recurrence_ends_at)->isPast()) {
            return false;
        }

        return true;
    }

    protected function createNextRecurrence(): self
    {
        $nextRemindAt = match($this->recurrence_type) {
            'daily' => $this->remind_at->addDays($this->recurrence_interval ?? 1),
            'weekly' => $this->remind_at->addWeeks($this->recurrence_interval ?? 1),
            'monthly' => $this->remind_at->addMonths($this->recurrence_interval ?? 1),
            'yearly' => $this->remind_at->addYears($this->recurrence_interval ?? 1),
            default => $this->remind_at->addDays(1),
        };

        return self::create([
            'user_id' => $this->user_id,
            'company_id' => $this->company_id,
            'remindable_type' => $this->remindable_type,
            'remindable_id' => $this->remindable_id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'remind_at' => $nextRemindAt,
            'email_notification' => $this->email_notification,
            'is_recurring' => true,
            'recurrence_type' => $this->recurrence_type,
            'recurrence_interval' => $this->recurrence_interval,
            'recurrence_ends_at' => $this->recurrence_ends_at,
        ]);
    }

    public function getRelatedItemNameAttribute(): ?string
    {
        if (!$this->remindable) {
            return null;
        }

        if ($this->remindable instanceof Lead) {
            return 'Lead: ' . $this->remindable->name;
        }

        if ($this->remindable instanceof Appointment) {
            return 'Cita: ' . $this->remindable->title;
        }

        if ($this->remindable instanceof Properties) {
            return 'Propiedad: ' . $this->remindable->title;
        }

        return null;
    }
}
