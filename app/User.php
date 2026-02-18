<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'password',
        'role',
        'company_id',
        'avatar',
        'status',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Roles disponibles
     */
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_COMPANY_ADMIN = 'company_admin';
    const ROLE_AGENT = 'agent';

    /**
     * Estados disponibles
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';

    /**
     * Relación con la empresa
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Empresas que este usuario posee
     */
    public function ownedCompanies()
    {
        return $this->hasMany(Company::class, 'owner_id');
    }

    /**
     * Propiedades creadas por este usuario
     */
    public function properties()
    {
        return $this->hasMany(Properties::class, 'user_id');
    }

    /**
     * Solicitudes de comisión enviadas
     */
    public function commissionRequestsSent()
    {
        return $this->hasMany(CommissionRequest::class, 'requester_id');
    }

    /**
     * Solicitudes de comisión recibidas
     */
    public function commissionRequestsReceived()
    {
        return $this->hasMany(CommissionRequest::class, 'owner_id');
    }

    /**
     * Ventas realizadas
     */
    public function sales()
    {
        return $this->hasMany(Sale::class, 'seller_id');
    }

    /**
     * Verificar si es super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    /**
     * Verificar si es admin de empresa
     */
    public function isCompanyAdmin(): bool
    {
        return $this->role === self::ROLE_COMPANY_ADMIN;
    }

    /**
     * Verificar si es agente
     */
    public function isAgent(): bool
    {
        return $this->role === self::ROLE_AGENT;
    }

    /**
     * Verificar si tiene rol de admin (super o company)
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_COMPANY_ADMIN]);
    }

    /**
     * Verificar si el usuario está activo
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Verificar si tiene suscripción activa
     */
    public function hasActiveSubscription(): bool
    {
        if (!$this->company) {
            return false;
        }
        return $this->company->hasActiveSubscription();
    }

    /**
     * Obtener la suscripción activa de la empresa
     */
    public function getActiveSubscription()
    {
        if (!$this->company) {
            return null;
        }
        return $this->company->activeSubscription();
    }

    /**
     * Verificar si puede acceder a la bolsa inmobiliaria
     */
    public function canAccessBolsa(): bool
    {
        $subscription = $this->getActiveSubscription();
        if (!$subscription) {
            return false;
        }
        return $subscription->package->allows_commission ?? false;
    }

    /**
     * Verificar si puede crear tours virtuales
     */
    public function canCreateTours(): bool
    {
        $subscription = $this->getActiveSubscription();
        if (!$subscription) {
            return false;
        }
        return $subscription->package->allows_tours ?? false;
    }

    /**
     * Obtener nombre completo para mostrar
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Obtener avatar URL o default
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=random';
    }
}
