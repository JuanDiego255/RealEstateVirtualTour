<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Sector extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'icon', 'description', 'image',
        'color', 'status', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Auto-generate slug from name
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($sector) {
            if (empty($sector->slug)) {
                $sector->slug = Str::slug($sector->name);
            }
        });

        static::updating(function ($sector) {
            if ($sector->isDirty('name')) {
                $sector->slug = Str::slug($sector->name);
            }
        });
    }

    /**
     * Get the categories for this sector
     */
    public function categories()
    {
        return $this->hasMany('App\Category', 'sector_id');
    }

    /**
     * Categorías activas del sector
     */
    public function activeCategories()
    {
        return $this->categories()->where(function ($q) {
            $q->where('is_active', true)->orWhere('status', true);
        });
    }

    /**
     * Obtener cantidad de propiedades en este sector
     */
    public function getPropertiesCountAttribute(): int
    {
        return Properties::whereIn('category_id', $this->categories()->pluck('id'))->count();
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
        return $query->where(function ($q) {
            $q->where('status', true)->orWhere('is_active', true);
        });
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
        $slug = Str::slug($name);
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
