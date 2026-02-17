<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PropertyImage extends Model
{
    protected $fillable = [
        'property_id',
        'image',
        'caption',
        'sort_order',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Propiedad relacionada
     */
    public function property()
    {
        return $this->belongsTo(Properties::class, 'property_id');
    }

    /**
     * URL de la imagen
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->image);
    }

    /**
     * Marcar como imagen principal
     */
    public function markAsPrimary(): bool
    {
        // Desmarcar otras imágenes como primarias
        static::where('property_id', $this->property_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);

        $this->is_primary = true;
        return $this->save();
    }

    /**
     * Scope para imágenes principales
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope ordenadas
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('is_primary', 'desc')->orderBy('sort_order');
    }
}
