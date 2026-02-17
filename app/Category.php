<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = [
        'company_id',
        'sector_id',
        'name',
        'slug',
        'description',
        'logo',
        'cover_image',
        'contact_name',
        'contact_email',
        'contact_phone',
        'contact_whatsapp',
        'address',
        'latitude',
        'longitude',
        'website',
        'social_facebook',
        'social_instagram',
        'share_token',
        'is_active',
        'views_count',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
        'views_count' => 'integer',
    ];

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->share_token)) {
                $category->share_token = Str::random(32);
            }
            if (empty($category->slug)) {
                $category->slug = static::generateUniqueSlug($category->name, $category->company_id);
            }
        });
    }

    /**
     * Empresa de la categoría
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Sector de la categoría
     */
    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }

    /**
     * Propiedades de la categoría
     */
    public function properties()
    {
        return $this->hasMany(Properties::class, 'category_id');
    }

    /**
     * Propiedades activas (disponibles)
     */
    public function activeProperties()
    {
        return $this->properties()
            ->whereNotNull('published_at')
            ->whereIn('status', ['available', 'reserved', 'negotiating']);
    }

    /**
     * Obtener cantidad de propiedades
     */
    public function getPropertiesCountAttribute(): int
    {
        return $this->properties()->count();
    }

    /**
     * Obtener cantidad de propiedades activas
     */
    public function getActivePropertiesCountAttribute(): int
    {
        return $this->activeProperties()->count();
    }

    /**
     * URL del logo
     */
    public function getLogoUrlAttribute(): ?string
    {
        if ($this->logo) {
            return asset('storage/' . $this->logo);
        }
        // Fallback al logo de la empresa
        return $this->company?->logo_url;
    }

    /**
     * URL de la imagen de portada
     */
    public function getCoverUrlAttribute(): ?string
    {
        if ($this->cover_image) {
            return asset('storage/' . $this->cover_image);
        }
        return null;
    }

    /**
     * Obtener enlace compartible
     */
    public function getShareUrlAttribute(): string
    {
        return route('category.share', $this->share_token);
    }

    /**
     * Obtener número de WhatsApp formateado para enlaces
     */
    public function getWhatsappLinkAttribute(): ?string
    {
        if (!$this->contact_whatsapp) {
            return null;
        }
        $number = preg_replace('/[^0-9]/', '', $this->contact_whatsapp);
        return "https://wa.me/{$number}";
    }

    /**
     * Verificar si puede agregar más propiedades
     */
    public function canAddProperty(): bool
    {
        $package = $this->company?->getCurrentPackage();
        if (!$package) {
            return false;
        }
        return $this->properties_count < $package->max_posts_per_category;
    }

    /**
     * Incrementar contador de vistas
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Scope para categorías activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope con empresa activa
     */
    public function scopeWithActiveCompany($query)
    {
        return $query->whereHas('company', function ($q) {
            $q->where('status', 'active');
        });
    }

    /**
     * Generar slug único dentro de la empresa
     */
    public static function generateUniqueSlug(string $name, int $companyId, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        $query = static::where('slug', $slug)->where('company_id', $companyId);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $query = static::where('slug', $slug)->where('company_id', $companyId);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            $counter++;
        }

        return $slug;
    }
}
