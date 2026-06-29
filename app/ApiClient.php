<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Cliente autorizado a consumir el API de tours virtuales.
 * El token plano nunca se almacena: solo su hash sha256.
 */
class ApiClient extends Model
{
    protected $fillable = [
        'name',
        'token_hash',
        'is_active',
        'last_used_at',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'last_used_at' => 'datetime',
    ];

    protected $hidden = [
        'token_hash',
    ];

    /**
     * Hashear un token plano de forma consistente.
     */
    public static function hashToken(string $plain): string
    {
        return hash('sha256', $plain);
    }

    /**
     * Buscar un cliente activo por su token plano.
     */
    public static function findByToken(?string $plain): ?self
    {
        if (empty($plain)) {
            return null;
        }

        return static::where('token_hash', self::hashToken($plain))
            ->where('is_active', true)
            ->first();
    }
}
