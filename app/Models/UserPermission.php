<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    protected $table = 'user_permissions';

    protected $fillable = ['user_id', 'permissions'];

    protected $casts = ['permissions' => 'array'];

    /**
     * Módulos disponibles con sus acciones.
     * Usado para construir el formulario de configuración de roles.
     */
    public static function modules(): array
    {
        return [
            'branches' => [
                'label'   => 'Sucursales',
                'icon'    => 'fa-map-marker',
                'actions' => [
                    'view'   => 'Ver',
                    'create' => 'Agregar',
                    'edit'   => 'Editar',
                    'delete' => 'Eliminar',
                ],
            ],
            'properties' => [
                'label'   => 'Publicaciones',
                'icon'    => 'fa-th-list',
                'actions' => [
                    'view'     => 'Ver',
                    'create'   => 'Agregar',
                    'edit'     => 'Editar',
                    'delete'   => 'Eliminar',
                    'scenes'   => 'Crear escenas',
                    'hotspots' => 'Crear hotspots',
                    'markers'  => 'Crear marcadores',
                ],
            ],
            'favorites' => [
                'label'   => 'Mis Favoritos',
                'icon'    => 'fa-heart',
                'actions' => ['view' => 'Ver'],
            ],
            'ratings' => [
                'label'   => 'Mis Calificaciones',
                'icon'    => 'fa-star',
                'actions' => ['view' => 'Ver'],
            ],
            'bolsa' => [
                'label'   => 'Bolsa Inmobiliaria',
                'icon'    => 'fa-exchange',
                'actions' => ['view' => 'Ver (Dashboard, Disponibles, Solicitudes, Recibidas)'],
            ],
            'sales' => [
                'label'   => 'Ventas',
                'icon'    => 'fa-money',
                'actions' => ['view' => 'Ver'],
            ],
            'event_dashboard' => [
                'label'   => 'Dashboard Eventos',
                'icon'    => 'fa-desktop',
                'actions' => [
                    'view'           => 'Ver dashboard',
                    'create_lead'    => 'Crear lead',
                    'mark_contacted' => 'Marcar como contactado',
                    'whatsapp'       => 'Contactar por WhatsApp',
                ],
            ],
            'crm' => [
                'label'   => 'CRM + Agenda',
                'icon'    => 'fa-address-book',
                'actions' => ['view' => 'Ver (Leads, Pipeline, Agenda, Recordatorios)'],
            ],
            'kiosk' => [
                'label'   => 'Kiosko',
                'icon'    => 'fa-tablet',
                'actions' => ['view' => 'Acceder al Kiosko'],
            ],
        ];
    }

    /**
     * Verificar si un módulo/acción está permitido.
     */
    public function can(string $module, string $action = 'view'): bool
    {
        $perms = $this->permissions ?? [];
        return (bool) ($perms[$module][$action] ?? false);
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }
}
