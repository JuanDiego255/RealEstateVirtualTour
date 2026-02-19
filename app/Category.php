<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Vehicle;
use App\Properties;
use App\Subcategory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'sector_id',
        'name',
        'slug',
        'description',
        'location',
        'facilities',
        'notes',
        'features',
        'image',
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
        'status',
        'is_active',
        'views_count',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_active' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'views_count' => 'integer',
    ];

    /**
     * Boot del modelo
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->share_token)) {
                $category->share_token = Str::random(32);
            }
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name')) {
                $category->slug = Str::slug($category->name);
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
     * Get the sector this category belongs to
     */
    public function sector()
    {
        return $this->belongsTo('App\Sector', 'sector_id');
    }

    /**
     * Propiedades directas (legacy, por category_id)
     */
    public function directProperties()
    {
        return $this->hasMany('App\Properties', 'category_id');
    }

    /**
     * Propiedades a través de subcategorías
     */
    public function properties()
    {
        return $this->hasManyThrough(Properties::class, Subcategory::class, 'category_id', 'subcategory_id');
    }

    /**
     * Subcategorías de esta sucursal
     */
    public function subcategories()
    {
        return $this->hasMany(Subcategory::class);
    }

    /**
     * Vehículos directos (legacy, por category_id)
     */
    public function directVehicles()
    {
        return $this->hasMany('App\Vehicle', 'category_id');
    }

    /**
     * Vehículos a través de subcategorías
     */
    public function vehicles()
    {
        return $this->hasManyThrough(Vehicle::class, Subcategory::class, 'category_id', 'subcategory_id');
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
     * URL del logo
     */
    public function getLogoUrlAttribute(): ?string
    {
        if ($this->logo) {
            return asset('storage/' . $this->logo);
        }
        return $this->company ? $this->company->logo_url : null;
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
        if ($this->share_token) {
            return url('/c/' . $this->share_token);
        }
        return route('category.show', $this->slug);
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
        if (!$this->company) {
            return true; // Categorías sin empresa no tienen límite
        }
        $package = $this->company->getCurrentPackage();
        if (!$package) {
            return false;
        }
        $totalProperties = Properties::whereHas('subcategory', function ($q) {
            $q->where('category_id', $this->id);
        })->count();
        return $totalProperties < $package->max_posts_per_category;
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
        return $query->where(function ($q) {
            $q->where('status', true)->orWhere('is_active', true);
        });
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
     * Generar slug único
     */
    public static function generateUniqueSlug(string $name, ?int $companyId = null, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        $query = static::where('slug', $slug);
        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $query = static::where('slug', $slug);
            if ($companyId) {
                $query->where('company_id', $companyId);
            }
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            $counter++;
        }

        return $slug;
    }
}
