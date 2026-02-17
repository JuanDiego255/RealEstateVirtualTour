<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'image',
        'color',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Categorías del sector
     */
    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Categorías activas del sector
     */
    public function activeCategories()
    {
        return $this->categories()->where('is_active', true);
    }

    /**
     * Obtener cantidad de propiedades en este sector
     */
    public function getPropertiesCountAttribute(): int
    {
        return Properties::whereIn('category_id', $this->categories()->pluck('id'))->count();
    }

    /**
     * Obtener cantidad de categorías activas
     */
    public function getActiveCategoriesCountAttribute(): int
    {
        return $this->activeCategories()->count();
    }

    /**
     * URL de la imagen
     */
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        return null;
    }

    /**
     * Scope para sectores activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope ordenados
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Generar slug único
     */
    public static function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = \Illuminate\Support\Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        $query = static::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $query = static::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            $counter++;
        }

        return $slug;
    }
}
