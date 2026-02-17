<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CommissionNegotiation extends Model
{
    protected $fillable = [
        'commission_request_id',
        'from_user_id',
        'to_user_id',
        'proposed_percentage',
        'message',
        'is_counter_offer',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'proposed_percentage' => 'decimal:2',
        'is_counter_offer' => 'boolean',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Solicitud de comisión relacionada
     */
    public function commissionRequest()
    {
        return $this->belongsTo(CommissionRequest::class);
    }

    /**
     * Usuario que envió el mensaje
     */
    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    /**
     * Usuario destinatario del mensaje
     */
    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    /**
     * Verificar si es una contra-oferta
     */
    public function isCounterOffer(): bool
    {
        return $this->is_counter_offer && $this->proposed_percentage !== null;
    }

    /**
     * Marcar como leído
     */
    public function markAsRead(): bool
    {
        if ($this->is_read) {
            return true;
        }
        $this->is_read = true;
        $this->read_at = now();
        return $this->save();
    }

    /**
     * Scope para mensajes no leídos
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope para mensajes de un usuario específico
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where('to_user_id', $user->id);
    }
}
