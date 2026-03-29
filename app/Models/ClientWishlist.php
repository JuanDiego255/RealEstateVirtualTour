<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Properties;

class ClientWishlist extends Model
{
    protected $fillable = [
        'share_token', 'client_name', 'client_email', 'client_phone',
        'property_ids', 'vehicle_ids', 'event_name', 'access_count', 'last_accessed_at',
        'expires_at', 'notes'
    ];

    protected $casts = [
        'property_ids' => 'array',
        'vehicle_ids' => 'array',
        'last_accessed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Generar token unico
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
     * Obtener propiedades (vehiculos) de la wishlist
     */
    public function getPropertiesAttribute()
    {
        $ids = $this->property_ids ?? $this->vehicle_ids ?? [];
        if (empty($ids)) {
            return collect();
        }

        return Properties::whereIn('id', $ids)->get();
    }

    /**
     * Alias para compatibilidad
     */
    public function getVehiclesAttribute()
    {
        return $this->properties;
    }

    /**
     * Agregar propiedad (vehiculo) a la wishlist
     */
    public function addProperty($propertyId)
    {
        $ids = $this->property_ids ?? [];
        if (!in_array($propertyId, $ids)) {
            $ids[] = $propertyId;
            $this->update(['property_ids' => $ids]);
        }
        return $this;
    }

    /**
     * Alias para compatibilidad
     */
    public function addVehicle($vehicleId)
    {
        return $this->addProperty($vehicleId);
    }

    /**
     * Remover propiedad (vehiculo) de la wishlist
     */
    public function removeProperty($propertyId)
    {
        $ids = $this->property_ids ?? [];
        $ids = array_filter($ids, fn($id) => $id != $propertyId);
        $this->update(['property_ids' => array_values($ids)]);
        return $this;
    }

    /**
     * Alias para compatibilidad
     */
    public function removeVehicle($vehicleId)
    {
        return $this->removeProperty($vehicleId);
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
     * Verificar si la wishlist esta vigente
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
