<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Company extends Model
{
    protected $fillable = [
        'name',
        'legal_name',
        'tax_id',
        'description',
        'logo',
        'cover_image',
        'phone',
        'email',
        'address',
        'website',
        'owner_id',
        'status',
        'settings',
        'other_kiosk',
    ];

    protected $casts = [
        'settings' => 'array',
        'other_kiosk' => 'boolean',
    ];

    /**
     * Estados disponibles
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';

    /**
     * Dueño de la empresa
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Usuarios de la empresa
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Categorías de la empresa
     */
    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Suscripciones de la empresa
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Suscripción activa
     */
    public function activeSubscription()
    {
        return $this->subscriptions()
            ->whereIn('status', ['active', 'trial'])
            ->where('ends_at', '>=', Carbon::now())
            ->orderBy('ends_at', 'desc')
            ->first();
    }

    /**
     * Verificar si tiene suscripción activa
     */
    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription() !== null;
    }

    /**
     * Obtener el paquete actual
     */
    public function getCurrentPackage()
    {
        $subscription = $this->activeSubscription();
        return $subscription ? $subscription->package : null;
    }

    /**
     * Verificar si puede crear más categorías
     */
    public function canCreateCategory(): bool
    {
        $package = $this->getCurrentPackage();
        if (!$package) {
            return false;
        }
        $currentCount = $this->categories()->count();
        return $currentCount < $package->max_categories;
    }

    /**
     * Verificar si puede crear más usuarios
     */
    public function canAddUser(): bool
    {
        $package = $this->getCurrentPackage();
        if (!$package) {
            return false;
        }
        $currentCount = $this->users()->where('status', 'active')->count();
        return $currentCount < $package->max_users;
    }

    /**
     * Obtener el número de propiedades en todas las categorías
     */
    public function getTotalPropertiesCount(): int
    {
        return Properties::whereIn('category_id', $this->categories()->pluck('id'))->count();
    }

    /**
     * Solicitudes de comisión recibidas (para inmuebles de esta empresa)
     */
    public function commissionRequestsReceived()
    {
        return $this->hasMany(CommissionRequest::class, 'owner_company_id');
    }

    /**
     * Solicitudes de comisión enviadas (por usuarios de esta empresa)
     */
    public function commissionRequestsSent()
    {
        return $this->hasMany(CommissionRequest::class, 'requester_company_id');
    }

    /**
     * Ventas de la empresa
     */
    public function sales()
    {
        return $this->hasMany(Sale::class, 'seller_company_id');
    }

    /**
     * Obtener logo URL o default
     */
    public function getLogoUrlAttribute(): string
    {
        if ($this->logo) {
            return asset('storage/' . $this->logo);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=0D8ABC&color=fff&size=128';
    }

    /**
     * Verificar si la empresa está activa
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
