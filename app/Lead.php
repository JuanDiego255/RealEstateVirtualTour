<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Lead extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'property_id',
        'vehicle_id',
        'name',
        'email',
        'phone',
        'whatsapp',
        'status',
        'source',
        'priority',
        'interest_type',
        'budget_min',
        'budget_max',
        'budget_currency',
        'notes',
        'requirements',
        'next_follow_up',
        'first_contact_at',
        'last_contact_at',
        'converted_at',
    ];

    protected $casts = [
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
        'next_follow_up' => 'date',
        'first_contact_at' => 'datetime',
        'last_contact_at' => 'datetime',
        'converted_at' => 'datetime',
    ];

    // Estados del lead
    const STATUS_NEW = 'new';
    const STATUS_CONTACTED = 'contacted';
    const STATUS_QUALIFIED = 'qualified';
    const STATUS_PROPOSAL = 'proposal';
    const STATUS_NEGOTIATION = 'negotiation';
    const STATUS_WON = 'won';
    const STATUS_LOST = 'lost';

    // Fuentes
    const SOURCE_WEBSITE = 'website';
    const SOURCE_WHATSAPP = 'whatsapp';
    const SOURCE_PHONE = 'phone';
    const SOURCE_REFERRAL = 'referral';
    const SOURCE_SOCIAL_MEDIA = 'social_media';
    const SOURCE_WALK_IN = 'walk_in';
    const SOURCE_OTHER = 'other';

    // Prioridades
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_NEW => 'Nuevo',
            self::STATUS_CONTACTED => 'Contactado',
            self::STATUS_QUALIFIED => 'Calificado',
            self::STATUS_PROPOSAL => 'Propuesta',
            self::STATUS_NEGOTIATION => 'Negociación',
            self::STATUS_WON => 'Ganado',
            self::STATUS_LOST => 'Perdido',
        ];
    }

    public static function getSources(): array
    {
        return [
            self::SOURCE_WEBSITE => 'Sitio Web',
            self::SOURCE_WHATSAPP => 'WhatsApp',
            self::SOURCE_PHONE => 'Teléfono',
            self::SOURCE_REFERRAL => 'Referido',
            self::SOURCE_SOCIAL_MEDIA => 'Redes Sociales',
            self::SOURCE_WALK_IN => 'Visita Directa',
            self::SOURCE_OTHER => 'Otro',
        ];
    }

    public static function getPriorities(): array
    {
        return [
            self::PRIORITY_LOW => 'Baja',
            self::PRIORITY_MEDIUM => 'Media',
            self::PRIORITY_HIGH => 'Alta',
            self::PRIORITY_URGENT => 'Urgente',
        ];
    }

    public static function getInterestTypes(): array
    {
        return [
            'buy' => 'Comprar',
            'rent' => 'Alquilar',
            'sell' => 'Vender',
            'other' => 'Otro',
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

    public function property()
    {
        return $this->belongsTo(Properties::class, 'property_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function activities()
    {
        return $this->hasMany(LeadActivity::class)->orderBy('activity_at', 'desc');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
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

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [self::STATUS_WON, self::STATUS_LOST]);
    }

    public function scopeNeedsFollowUp($query)
    {
        return $query->whereNotNull('next_follow_up')
            ->where('next_follow_up', '<=', Carbon::today())
            ->active();
    }

    // Helpers
    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    public function getSourceLabelAttribute(): string
    {
        return self::getSources()[$this->source] ?? $this->source;
    }

    public function getPriorityLabelAttribute(): string
    {
        return self::getPriorities()[$this->priority] ?? $this->priority;
    }

    public function getInterestTypeLabelAttribute(): string
    {
        return self::getInterestTypes()[$this->interest_type] ?? $this->interest_type;
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_NEW => 'info',
            self::STATUS_CONTACTED => 'primary',
            self::STATUS_QUALIFIED => 'warning',
            self::STATUS_PROPOSAL => 'secondary',
            self::STATUS_NEGOTIATION => 'dark',
            self::STATUS_WON => 'success',
            self::STATUS_LOST => 'danger',
            default => 'secondary',
        };
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

    public function getBudgetRangeAttribute(): string
    {
        if (!$this->budget_min && !$this->budget_max) {
            return 'No especificado';
        }

        $currency = $this->budget_currency === 'USD' ? '$' : '₡';

        if ($this->budget_min && $this->budget_max) {
            return $currency . number_format($this->budget_min) . ' - ' . $currency . number_format($this->budget_max);
        }

        if ($this->budget_min) {
            return 'Desde ' . $currency . number_format($this->budget_min);
        }

        return 'Hasta ' . $currency . number_format($this->budget_max);
    }

    public function isOverdueForFollowUp(): bool
    {
        return $this->next_follow_up && $this->next_follow_up < Carbon::today();
    }

    public function needsFollowUpToday(): bool
    {
        return $this->next_follow_up && $this->next_follow_up->isToday();
    }

    public function logActivity(string $type, array $data = []): LeadActivity
    {
        $oldStatus = $this->status;

        $activity = $this->activities()->create(array_merge([
            'user_id' => auth()->id(),
            'type' => $type,
            'activity_at' => now(),
        ], $data));

        // Actualizar último contacto
        if (in_array($type, ['call', 'email', 'whatsapp', 'visit', 'meeting'])) {
            $this->update(['last_contact_at' => now()]);
        }

        return $activity;
    }

    public function changeStatus(string $newStatus, ?string $note = null): void
    {
        $oldStatus = $this->status;

        $this->update(['status' => $newStatus]);

        if ($newStatus === self::STATUS_WON) {
            $this->update(['converted_at' => now()]);
        }

        $this->logActivity('status_change', [
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'description' => $note,
        ]);
    }
}
