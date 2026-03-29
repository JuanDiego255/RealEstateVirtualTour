<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Vehicle;

class ClientWishlist extends Model
{
    protected $fillable = [
        'share_token', 'client_name', 'client_email', 'client_phone',
        'vehicle_ids', 'event_name', 'access_count', 'last_accessed_at',
        'expires_at', 'notes'
    ];

    protected $casts = [
        'vehicle_ids' => 'array',
        'last_accessed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Generar token único
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($wishlist) {
            if (empty($wishlist->share_token)) {
                $wishlist->share_token = Str::random(32);
            }
            if (empty($wishlist->expires_at)) {
                $wishlist->expires_at = now()->addDays(30);
            }
        });
    }

    /**
     * Obtener vehículos de la wishlist
     */
    public function getVehiclesAttribute()
    {
        if (empty($this->vehicle_ids)) {
            return collect();
        }

        return Vehicle::whereIn('id', $this->vehicle_ids)->get();
    }

    /**
     * Agregar vehículo a la wishlist
     */
    public function addVehicle($vehicleId)
    {
        $ids = $this->vehicle_ids ?? [];
        if (!in_array($vehicleId, $ids)) {
            $ids[] = $vehicleId;
            $this->update(['vehicle_ids' => $ids]);
        }
        return $this;
    }

    /**
     * Remover vehículo de la wishlist
     */
    public function removeVehicle($vehicleId)
    {
        $ids = $this->vehicle_ids ?? [];
        $ids = array_filter($ids, fn($id) => $id != $vehicleId);
        $this->update(['vehicle_ids' => array_values($ids)]);
        return $this;
    }

    /**
     * Registrar acceso
     */
    public function recordAccess()
    {
        $this->update([
            'access_count' => $this->access_count + 1,
            'last_accessed_at' => now(),
        ]);
        return $this;
    }

    /**
     * Verificar si la wishlist está vigente
     */
    public function isValid()
    {
        return !$this->expires_at || $this->expires_at->isFuture();
    }

    /**
     * URL compartible
     */
    public function getShareUrlAttribute()
    {
        return url("/wishlist/{$this->share_token}");
    }

    /**
     * Buscar por token
     */
    public static function findByToken($token)
    {
        return self::where('share_token', $token)->first();
    }
}
