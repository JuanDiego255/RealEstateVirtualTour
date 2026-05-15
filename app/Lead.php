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
        'event_name',
        'event_lead_id',
        // Phase 2 fields
        'score',
        'score_updated_at',
        'stage_entered_at',
        'portal_token',
        'portal_token_expires_at',
        'preferred_zones',
        'preferred_property_types',
        'preferred_bedrooms_min',
        'preferred_bedrooms_max',
    ];

    protected $casts = [
        'budget_min'               => 'decimal:2',
        'budget_max'               => 'decimal:2',
        'next_follow_up'           => 'date',
        'first_contact_at'         => 'datetime',
        'last_contact_at'          => 'datetime',
        'converted_at'             => 'datetime',
        'score_updated_at'         => 'datetime',
        'stage_entered_at'         => 'datetime',
        'portal_token_expires_at'  => 'datetime',
        'preferred_zones'          => 'array',
        'preferred_property_types' => 'array',
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
    const SOURCE_EVENT = 'event';
    const SOURCE_KIOSK = 'kiosk';
    const SOURCE_QR = 'qr';
    const SOURCE_QUOTE = 'quote';
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
            self::SOURCE_EVENT => 'Evento',
            self::SOURCE_KIOSK => 'Kiosko',
            self::SOURCE_QR => 'Código QR',
            self::SOURCE_QUOTE => 'Cotización',
            self::SOURCE_OTHER => 'Otro',
        ];
    }

    /**
     * Fuentes que provienen de eventos (para filtrado)
     */
    public static function getEventSources(): array
    {
        return [
            self::SOURCE_EVENT,
            self::SOURCE_KIOSK,
            self::SOURCE_QR,
            self::SOURCE_QUOTE,
            self::SOURCE_WALK_IN,
        ];
    }

    /**
     * Fuentes del flujo normal de agencia
     */
    public static function getAgencySources(): array
    {
        return [
            self::SOURCE_WEBSITE,
            self::SOURCE_WHATSAPP,
            self::SOURCE_PHONE,
            self::SOURCE_REFERRAL,
            self::SOURCE_SOCIAL_MEDIA,
            self::SOURCE_OTHER,
        ];
    }

    /**
     * Scope para leads de eventos
     */
    public function scopeFromEvent($query)
    {
        return $query->whereIn('source', self::getEventSources());
    }

    /**
     * Scope para leads de agencia (flujo normal)
     */
    public function scopeFromAgency($query)
    {
        return $query->whereIn('source', self::getAgencySources());
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
        return $this->belongsTo(Properties::class, 'vehicle_id');
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

    public function tasks()
    {
        return $this->hasMany(LeadTask::class);
    }

    public function quotes()
    {
        return $this->hasMany(\App\Models\LeadQuote::class);
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

        $this->update([
            'status'           => $newStatus,
            'stage_entered_at' => now(),
        ]);

        if ($newStatus === self::STATUS_WON) {
            $this->update(['converted_at' => now()]);
        }

        $this->logActivity('status_change', [
            'old_status'  => $oldStatus,
            'new_status'  => $newStatus,
            'description' => $note,
        ]);

        // Auto-create tasks from active templates configured for this stage
        if ($this->company_id) {
            \App\ActivityTemplate::where('company_id', $this->company_id)
                ->where('stage', $newStatus)
                ->where('is_active', true)
                ->where('auto_create_task', true)
                ->get()
                ->each(function ($tpl) {
                    \App\LeadTask::create([
                        'company_id'  => $this->company_id,
                        'lead_id'     => $this->id,
                        'created_by'  => auth()->id() ?? $this->user_id,
                        'assigned_to' => $this->user_id,
                        'title'       => $tpl->name,
                        'type'        => $tpl->type,
                        'priority'    => 'medium',
                        'status'      => 'pending',
                        'description' => $tpl->body,
                        'due_at'      => now()->addHours($tpl->task_due_hours ?? 24),
                    ]);
                });
        }

        $this->recalculateScore();
    }

    // ── Lead Scoring ──────────────────────────────────────────────────────────

    /**
     * Calculate and persist the lead score (0–100).
     *
     * Breakdown:
     *   Status progression : up to 30 pts
     *   Priority           : up to 15 pts
     *   Activity recency   : up to 25 pts
     *   Activity volume    : up to 15 pts
     *   Budget defined     : up to 10 pts
     *   Source quality     : up to  5 pts
     */
    public function recalculateScore(): int
    {
        $score = 0;

        // Status progression (30 pts)
        $statusScores = [
            'new' => 5, 'contacted' => 10, 'qualified' => 18,
            'proposal' => 22, 'negotiation' => 28, 'won' => 30, 'lost' => 0,
        ];
        $score += $statusScores[$this->status] ?? 0;

        // Priority (15 pts)
        $priorityScores = ['low' => 2, 'medium' => 6, 'high' => 11, 'urgent' => 15];
        $score += $priorityScores[$this->priority] ?? 6;

        // Activity recency (25 pts) — decay based on days since last contact
        $lastContact = $this->last_contact_at ?? $this->created_at;
        $daysSince   = $lastContact->diffInDays(now());
        if ($daysSince === 0)       $score += 25;
        elseif ($daysSince <= 2)    $score += 22;
        elseif ($daysSince <= 7)    $score += 16;
        elseif ($daysSince <= 14)   $score += 10;
        elseif ($daysSince <= 30)   $score += 5;

        // Activity volume (15 pts) — capped at 5 activities
        $actCount = $this->activities()->where('type', '!=', 'status_change')->count();
        $score   += min(15, $actCount * 3);

        // Budget defined (10 pts)
        if ($this->budget_min || $this->budget_max) $score += 10;

        // Source quality (5 pts)
        $highValueSources = ['referral', 'walk_in', 'event', 'quote'];
        if (in_array($this->source, $highValueSources)) $score += 5;

        $score = min(100, $score);

        $this->updateQuietly(['score' => $score, 'score_updated_at' => now()]);

        return $score;
    }

    public function getScoreLabelAttribute(): string
    {
        return match(true) {
            $this->score >= 80 => 'Muy caliente',
            $this->score >= 60 => 'Caliente',
            $this->score >= 40 => 'Tibio',
            $this->score >= 20 => 'Frío',
            default            => 'Muy frío',
        };
    }

    public function getScoreColorAttribute(): string
    {
        return match(true) {
            $this->score >= 80 => '#ef4444',
            $this->score >= 60 => '#f97316',
            $this->score >= 40 => '#eab308',
            $this->score >= 20 => '#3b82f6',
            default            => '#94a3b8',
        };
    }

    // ── Client Portal ─────────────────────────────────────────────────────────

    public function generatePortalToken(): string
    {
        $token = \Illuminate\Support\Str::random(48);
        $this->update([
            'portal_token'            => $token,
            'portal_token_expires_at' => now()->addDays(30),
        ]);
        return $token;
    }

    public function getPortalUrlAttribute(): ?string
    {
        if (!$this->portal_token) return null;
        return route('crm.portal', $this->portal_token);
    }

    public function isPortalValid(): bool
    {
        return $this->portal_token
            && ($this->portal_token_expires_at === null || $this->portal_token_expires_at->isFuture());
    }

    // ── Property Matching ─────────────────────────────────────────────────────

    /**
     * Score a property/vehicle against this lead's preferences.
     * Returns 0–100.
     */
    public function matchScore($property): int
    {
        $score = 0;

        // Budget match (40 pts)
        $price = (float) $property->price;
        if ($price) {
            $inRange = (!$this->budget_min || $price >= $this->budget_min)
                    && (!$this->budget_max || $price <= $this->budget_max * 1.1);
            if ($inRange) $score += 40;
            elseif ($this->budget_max && $price <= $this->budget_max * 1.25) $score += 20;
        }

        // Bedrooms match (30 pts)
        $beds = (int) $property->rooms ?? null;
        if ($beds && ($this->preferred_bedrooms_min || $this->preferred_bedrooms_max)) {
            $minOk = !$this->preferred_bedrooms_min || $beds >= $this->preferred_bedrooms_min;
            $maxOk = !$this->preferred_bedrooms_max || $beds <= $this->preferred_bedrooms_max;
            if ($minOk && $maxOk) $score += 30;
            elseif ($minOk || $maxOk) $score += 15;
        } elseif (!$this->preferred_bedrooms_min && !$this->preferred_bedrooms_max) {
            $score += 15; // No preference = partial match
        }

        // Zone/sector match (30 pts)
        $zones = $this->preferred_zones ?? [];
        if (!empty($zones)) {
            $propZone = $property->category?->sector?->name ?? null;
            if ($propZone && collect($zones)->contains(fn($z) => stripos($propZone, $z) !== false)) {
                $score += 30;
            }
        } else {
            $score += 15; // No zone preference = partial match
        }

        return min(100, $score);
    }
}

