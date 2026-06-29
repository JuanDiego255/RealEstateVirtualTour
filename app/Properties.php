<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Properties extends Model
{
    use HasFactory;

    protected $fillable = [
        'share_token',
        'category_id',
        'subcategory_id',
        'user_id',
        'name',
        'code',
        'property_type',
        'description',
        'rooms',
        'bathrooms',
        'garage',
        'floor_levels',
        'construction',
        'land',
        'construction_year',
        'maintenance',
        'price',
        'currency',
        'image',
        'location',
        'address',
        'latitude',
        'longitude',
        'status',
        'is_exclusive',
        'commission_percentage',
        'commission_notes',
        'has_virtual_tour',
        'api_consumable',
        'spin_active',
        'spin_auto_rotate',
        'is_featured',
        'views_count',
        'published_at',
        'sold_at',
        'is_demo_tour',
        // Campos de vehículo
        'brand',
        'model',
        'year',
        'color',
        'mileage_km',
        'fuel_tank_capacity',
        'fuel_type',
        'engine_cc',
        'doors',
        'passengers',
        'tires',
        'drivetrain',
        'transmission',
        'condition',
        'plate',
        // Campos de audio para Test Drive Inmersivo
        'engine_startup_audio',
        'engine_idle_audio',
        'engine_rev_audio',
        'interior_pov_video',
        'featured_image',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_exclusive' => 'boolean',
        'commission_percentage' => 'decimal:2',
        'has_virtual_tour' => 'boolean',
        'api_consumable' => 'boolean',
        'spin_active' => 'boolean',
        'spin_auto_rotate' => 'boolean',
        'is_featured' => 'boolean',
        'views_count' => 'integer',
        'published_at' => 'datetime',
        'sold_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($property) {
            if (empty($property->share_token)) {
                $property->share_token = \Illuminate\Support\Str::random(32);
            }
        });
    }

    /**
     * Obtener URL compartible
     */
    public function getShareUrlAttribute()
    {
        return $this->share_token ? url('/p/' . $this->share_token) : null;
    }

    /**
     * Tipos de propiedad
     */
    const TYPE_HOUSE = 'house';
    const TYPE_APARTMENT = 'apartment';
    const TYPE_LAND = 'land';
    const TYPE_VEHICLE = 'vehicle';
    const TYPE_COMMERCIAL = 'commercial';
    const TYPE_OTHER = 'other';

    /**
     * Estados
     */
    const STATUS_AVAILABLE = 'available';
    const STATUS_RESERVED = 'reserved';
    const STATUS_NEGOTIATING = 'negotiating';
    const STATUS_SOLD = 'sold';
    const STATUS_RENTED = 'rented';
    const STATUS_INACTIVE = 'inactive';

    /**
     * Get the category this property belongs to
     */
    public function category()
    {
        return $this->belongsTo('App\Category', 'category_id');
    }

    /**
     * Subcategoría del inmueble
     */
    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    /**
     * Usuario creador
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the scenes for this property
     */
    public function scenes()
    {
        return $this->hasMany('App\Scene', 'property_id');
    }

    /**
     * Imágenes adicionales
     */
    public function images()
    {
        return $this->hasMany(PropertyImage::class, 'property_id')->orderBy('sort_order');
    }

    /**
     * Solicitudes de comisión
     */
    public function commissionRequests()
    {
        return $this->hasMany(CommissionRequest::class, 'property_id');
    }

    /**
     * Venta (si fue vendida)
     */
    public function sale()
    {
        return $this->hasOne(Sale::class, 'property_id');
    }

    /**
     * Obtener el spin activo (directamente asociado o a través de escenas)
     */
    public function getActiveSpinAttribute()
    {
        try {
            // Primero buscar spin directamente asociado al vehículo
            $spin = \App\Models\Spin::where('vehicle_id', $this->id)
                ->where('status', 'ready')
                ->first();

            if ($spin) return $spin;

            // Si no hay spin directo, buscar a través de escenas
            $scene = $this->scenes()->whereNotNull('spin_id')->first();
            if (!$scene) return null;

            return \App\Models\Spin::where('id', $scene->spin_id)
                ->where('status', 'ready')
                ->first();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Obtener precio formateado
     */
    public function getFormattedPriceAttribute(): string
    {
        $symbol = $this->currency === 'USD' ? '$' : '₡';
        return $symbol . number_format((float)$this->price, 0, ',', '.');
    }

    /**
     * URL de la imagen principal
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            return route('file', $this->image);
        }
        return asset('images/producto-sin-imagen.PNG');
    }

    /**
     * Obtener nombre del tipo de propiedad
     */
    public function getPropertyTypeNameAttribute(): string
    {
        $names = [
            'house' => 'Casa',
            'apartment' => 'Apartamento',
            'land' => 'Lote/Terreno',
            'vehicle' => 'Vehículo',
            'commercial' => 'Comercial',
            'other' => 'Otro',
        ];
        return $names[$this->property_type] ?? $this->property_type ?? 'Casa';
    }

    /**
     * Obtener nombre del estado
     */
    public function getStatusNameAttribute(): string
    {
        $names = [
            'available' => 'Disponible',
            'reserved' => 'Reservado',
            'negotiating' => 'En negociación',
            'sold' => 'Vendido',
            'rented' => 'Alquilado',
            'inactive' => 'Inactivo',
        ];
        return $names[$this->status] ?? $this->status ?? 'Disponible';
    }

    /**
     * Obtener color del badge según estado
     */
    public function getStatusColorAttribute(): string
    {
        $colors = [
            'available' => 'success',
            'reserved' => 'warning',
            'negotiating' => 'info',
            'sold' => 'danger',
            'rented' => 'primary',
            'inactive' => 'secondary',
        ];
        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Verificar si está disponible para venta
     */
    public function isAvailable(): bool
    {
        return in_array($this->status, [self::STATUS_AVAILABLE, self::STATUS_NEGOTIATING]);
    }

    /**
     * Verificar si acepta comisiones externas
     */
    public function canAcceptCommission(): bool
    {
        return !$this->is_exclusive
            && $this->commission_percentage > 0
            && $this->isAvailable();
    }

    /**
     * Verificar si tiene tour virtual
     */
    public function hasTour(): bool
    {
        return $this->has_virtual_tour && $this->scenes()->count() > 0;
    }

    /**
     * Incrementar contador de vistas
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Publicar propiedad
     */
    public function publish(): bool
    {
        $this->published_at = now();
        $this->status = self::STATUS_AVAILABLE;
        return $this->save();
    }

    /**
     * Scope para propiedades publicadas
     */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    /**
     * Scope para propiedades disponibles
     */
    public function scopeAvailable($query)
    {
        return $query->whereIn('status', [self::STATUS_AVAILABLE, self::STATUS_NEGOTIATING]);
    }

    /**
     * Scope para propiedades que aceptan comisiones
     */
    public function scopeAcceptsCommission($query)
    {
        return $query->where('is_exclusive', false)
            ->whereNotNull('commission_percentage')
            ->where('commission_percentage', '>', 0)
            ->available();
    }

    /**
     * Scope para propiedades con tour virtual
     */
    public function scopeWithTour($query)
    {
        return $query->where('has_virtual_tour', true);
    }

    /**
     * Scope para propiedades destacadas
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope para propiedades habilitadas para consumo por API externa
     */
    public function scopeApiConsumable($query)
    {
        return $query->where('api_consumable', true);
    }

    /**
     * Scope por sector
     */
    public function scopeInSector($query, $sectorId)
    {
        return $query->whereHas('category', function ($q) use ($sectorId) {
            $q->where('sector_id', $sectorId);
        });
    }

    /**
     * Verificar si es un vehículo
     */
    public function isVehicle(): bool
    {
        return $this->property_type === self::TYPE_VEHICLE;
    }

    /**
     * Scope para solo vehículos
     */
    public function scopeVehicles($query)
    {
        return $query->where('property_type', self::TYPE_VEHICLE);
    }

    /**
     * Scope para solo propiedades (no vehículos)
     */
    public function scopeRealEstate($query)
    {
        return $query->where('property_type', '!=', self::TYPE_VEHICLE);
    }

    /**
     * Favoritos de esta propiedad
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'property_id');
    }

    /**
     * Usuarios que han marcado como favorita esta propiedad
     */
    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites', 'property_id', 'user_id')
            ->withTimestamps();
    }
}
