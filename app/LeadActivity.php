<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LeadActivity extends Model
{
    protected $fillable = [
        'lead_id',
        'user_id',
        'type',
        'subject',
        'description',
        'call_result',
        'call_duration',
        'property_id',
        'vehicle_id',
        'old_status',
        'new_status',
        'activity_at',
    ];

    protected $casts = [
        'activity_at' => 'datetime',
        'call_duration' => 'integer',
    ];

    // Tipos de actividad
    const TYPE_CALL = 'call';
    const TYPE_EMAIL = 'email';
    const TYPE_WHATSAPP = 'whatsapp';
    const TYPE_VISIT = 'visit';
    const TYPE_MEETING = 'meeting';
    const TYPE_NOTE = 'note';
    const TYPE_STATUS_CHANGE = 'status_change';
    const TYPE_OTHER = 'other';

    public static function getTypes(): array
    {
        return [
            self::TYPE_CALL => 'Llamada',
            self::TYPE_EMAIL => 'Email',
            self::TYPE_WHATSAPP => 'WhatsApp',
            self::TYPE_VISIT => 'Visita',
            self::TYPE_MEETING => 'Reunión',
            self::TYPE_NOTE => 'Nota',
            self::TYPE_STATUS_CHANGE => 'Cambio de Estado',
            self::TYPE_OTHER => 'Otro',
        ];
    }

    public static function getCallResults(): array
    {
        return [
            'answered' => 'Contestada',
            'no_answer' => 'No Contestó',
            'busy' => 'Ocupado',
            'voicemail' => 'Buzón de Voz',
            'callback_requested' => 'Solicitó Llamada',
        ];
    }

    // Relaciones
    public function lead()
    {
        return $this->belongsTo(Lead::class);
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

    // Helpers
    public function getTypeLabelAttribute(): string
    {
        return self::getTypes()[$this->type] ?? $this->type;
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            self::TYPE_CALL => 'ti-mobile',
            self::TYPE_EMAIL => 'ti-email',
            self::TYPE_WHATSAPP => 'ti-comment-alt',
            self::TYPE_VISIT => 'ti-home',
            self::TYPE_MEETING => 'ti-user',
            self::TYPE_NOTE => 'ti-notepad',
            self::TYPE_STATUS_CHANGE => 'ti-exchange-vertical',
            default => 'ti-time',
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            self::TYPE_CALL => 'primary',
            self::TYPE_EMAIL => 'info',
            self::TYPE_WHATSAPP => 'success',
            self::TYPE_VISIT => 'warning',
            self::TYPE_MEETING => 'secondary',
            self::TYPE_NOTE => 'dark',
            self::TYPE_STATUS_CHANGE => 'purple',
            default => 'secondary',
        };
    }

    public function getCallResultLabelAttribute(): ?string
    {
        if (!$this->call_result) {
            return null;
        }
        return self::getCallResults()[$this->call_result] ?? $this->call_result;
    }

    public function getCallDurationFormattedAttribute(): ?string
    {
        if (!$this->call_duration) {
            return null;
        }

        $minutes = floor($this->call_duration / 60);
        $seconds = $this->call_duration % 60;

        if ($minutes > 0) {
            return "{$minutes}m {$seconds}s";
        }

        return "{$seconds}s";
    }
}
