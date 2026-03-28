<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Appointment extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'lead_id',
        'property_id',
        'vehicle_id',
        'title',
        'description',
        'type',
        'starts_at',
        'ends_at',
        'all_day',
        'location',
        'address',
        'latitude',
        'longitude',
        'client_name',
        'client_phone',
        'client_email',
        'status',
        'cancellation_reason',
        'outcome_notes',
        'outcome',
        'reminder_sent',
        'reminder_minutes',
        'color',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'all_day' => 'boolean',
        'reminder_sent' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    // Tipos de cita
    const TYPE_PROPERTY_VISIT = 'property_visit';
    const TYPE_VEHICLE_VISIT = 'vehicle_visit';
    const TYPE_MEETING = 'meeting';
    const TYPE_CALL = 'call';
    const TYPE_VIDEO_CALL = 'video_call';
    const TYPE_SIGNING = 'signing';
    const TYPE_OTHER = 'other';

    // Estados
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_NO_SHOW = 'no_show';

    public static function getTypes(): array
    {
        return [
            self::TYPE_PROPERTY_VISIT => 'Visita a Propiedad',
            self::TYPE_VEHICLE_VISIT => 'Visita a Vehículo',
            self::TYPE_MEETING => 'Reunión',
            self::TYPE_CALL => 'Llamada',
            self::TYPE_VIDEO_CALL => 'Videollamada',
            self::TYPE_SIGNING => 'Firma de Documentos',
            self::TYPE_OTHER => 'Otro',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_SCHEDULED => 'Programada',
            self::STATUS_CONFIRMED => 'Confirmada',
            self::STATUS_IN_PROGRESS => 'En Progreso',
            self::STATUS_COMPLETED => 'Completada',
            self::STATUS_CANCELLED => 'Cancelada',
            self::STATUS_NO_SHOW => 'No Asistió',
        ];
    }

    public static function getOutcomes(): array
    {
        return [
            'successful' => 'Exitosa',
            'follow_up_needed' => 'Requiere Seguimiento',
            'not_interested' => 'No Interesado',
            'pending' => 'Pendiente',
        ];
    }

    public static function getDefaultColors(): array
    {
        return [
            self::TYPE_PROPERTY_VISIT => '#4CAF50',
            self::TYPE_VEHICLE_VISIT => '#2196F3',
            self::TYPE_MEETING => '#9C27B0',
            self::TYPE_CALL => '#FF9800',
            self::TYPE_VIDEO_CALL => '#00BCD4',
            self::TYPE_SIGNING => '#E91E63',
            self::TYPE_OTHER => '#607D8B',
        ];
    }

    // Relaciones
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function property()
    {
        return $this->belongsTo(Properties::class, 'property_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function reminders()
    {
        return $this->morphMany(Reminder::class, 'remindable');
    }

    // Scopes
    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('starts_at', '>=', now())
            ->whereNotIn('status', [self::STATUS_CANCELLED, self::STATUS_COMPLETED])
            ->orderBy('starts_at', 'asc');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('starts_at', Carbon::today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('starts_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('starts_at', [$start, $end]);
    }

    public function scopeNeedsReminder($query)
    {
        return $query->where('reminder_sent', false)
            ->where('status', self::STATUS_SCHEDULED)
            ->whereRaw('starts_at <= DATE_ADD(NOW(), INTERVAL reminder_minutes MINUTE)');
    }

    // Helpers
    public function getTypeLabelAttribute(): string
    {
        return self::getTypes()[$this->type] ?? $this->type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    public function getOutcomeLabelAttribute(): ?string
    {
        if (!$this->outcome) {
            return null;
        }
        return self::getOutcomes()[$this->outcome] ?? $this->outcome;
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_SCHEDULED => 'info',
            self::STATUS_CONFIRMED => 'primary',
            self::STATUS_IN_PROGRESS => 'warning',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_CANCELLED => 'danger',
            self::STATUS_NO_SHOW => 'dark',
            default => 'secondary',
        };
    }

    public function getCalendarColorAttribute(): string
    {
        if ($this->color) {
            return $this->color;
        }
        return self::getDefaultColors()[$this->type] ?? '#607D8B';
    }

    public function getClientDisplayNameAttribute(): string
    {
        if ($this->lead) {
            return $this->lead->name;
        }
        return $this->client_name ?? 'Sin cliente';
    }

    public function getClientDisplayPhoneAttribute(): ?string
    {
        if ($this->lead) {
            return $this->lead->phone ?? $this->lead->whatsapp;
        }
        return $this->client_phone;
    }

    public function getDurationInMinutesAttribute(): int
    {
        return $this->starts_at->diffInMinutes($this->ends_at);
    }

    public function isUpcoming(): bool
    {
        return $this->starts_at > now() && !in_array($this->status, [self::STATUS_CANCELLED, self::STATUS_COMPLETED]);
    }

    public function isPast(): bool
    {
        return $this->ends_at < now();
    }

    public function isToday(): bool
    {
        return $this->starts_at->isToday();
    }

    public function isInProgress(): bool
    {
        return now()->between($this->starts_at, $this->ends_at);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_SCHEDULED, self::STATUS_CONFIRMED]);
    }

    public function cancel(?string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancellation_reason' => $reason,
        ]);
    }

    public function complete(?string $outcome = null, ?string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'outcome' => $outcome,
            'outcome_notes' => $notes,
        ]);

        // Si tiene lead asociado, registrar actividad
        if ($this->lead) {
            $this->lead->logActivity('visit', [
                'subject' => $this->title,
                'description' => $notes,
                'property_id' => $this->property_id,
                'vehicle_id' => $this->vehicle_id,
            ]);
        }
    }

    // Para FullCalendar
    public function toCalendarEvent(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'start' => $this->starts_at->toIso8601String(),
            'end' => $this->ends_at->toIso8601String(),
            'allDay' => $this->all_day,
            'color' => $this->calendar_color,
            'extendedProps' => [
                'type' => $this->type,
                'status' => $this->status,
                'client' => $this->client_display_name,
                'location' => $this->location,
                'lead_id' => $this->lead_id,
                'property_id' => $this->property_id,
                'vehicle_id' => $this->vehicle_id,
            ],
        ];
    }
}
