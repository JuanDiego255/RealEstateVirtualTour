# CRM — Guía de Navegación UX (Fase 5)

## Resumen de cambios

Se implementó la **Opción A + Dashboard de Hoy**: el CRM pasa de 10 enlaces en el sidebar a 5 ítems limpios, y el detalle del lead usa tabs Bootstrap en lugar de scroll vertical infinito.

---

## Estructura de navegación

### Sidebar CRM (5 ítems)

```
CRM
├── 📊 Dashboard Hoy        /admin/crm/dashboard
├── 👥 Leads                /admin/crm/leads
├── 📋 Pipeline             /admin/crm/leads/pipeline
├── 🗓️ Agenda               /admin/crm/appointments
├── 📈 Reportes             /admin/crm/reports
└── ⚙️ Configuración        (solo company_admin)
    ├── Reglas de Pipeline  /admin/crm/pipeline-rules
    └── Auditoría Leads     /admin/crm/leads/audit
```

**Módulos que dejaron de estar en el sidebar** (siguen accesibles desde el lead):
- Recordatorios → tab "Citas & Tareas" del lead
- Seguimientos → botón en Dashboard Hoy / Reportes
- Tareas → tab "Citas & Tareas" del lead
- Cotizaciones → tab "Cotizaciones" del lead

---

## Dashboard de Hoy (`/admin/crm/dashboard`)

Vista que agrega todo lo urgente en una sola pantalla:

| Sección | Contenido |
|---------|-----------|
| **Stat cards** | Citas hoy, Tareas vencidas, Recordatorios pendientes, Leads sin atender |
| **Citas de Hoy** | Lista cronológica de citas del día con link directo al lead (tab Citas) |
| **Tareas que Vencen Hoy** | Tareas con due_at = hoy, botón completar inline |
| **Tareas Vencidas** | Overdue tasks con tiempo vencido, botón completar inline |
| **Recordatorios Pendientes** | Recordatorios vencidos o de hoy, botones completar/descartar |
| **Leads sin Atender** | Leads con follow-up vencido o sin contacto en 7+ días |

**Controlador:** `app/Http/Controllers/Admin/CrmDashboardController.php` → método `today()`

---

## Lead Detail — Tabs

El detalle del lead (`/admin/crm/leads/{id}`) ahora tiene 4 tabs:

| Tab | Contenido | Ruta directa |
|-----|-----------|--------------|
| **Resumen** | Contacto, Detalles, Notas, Cambiar Estado, Property/Vehicle Matching | `?tab=resumen` |
| **Actividad** | Formulario Registrar Actividad + Timeline de actividades side-by-side | `?tab=actividad` |
| **Citas & Tareas** | Recordatorios, Citas (con modal de detalle), Tareas pendientes | `?tab=citas` |
| **Cotizaciones** | Lista completa + botón Nueva Cotización (modal con calculadora de vehículo) | `?tab=cotizaciones` |

### Tab persistence

El tab activo se guarda en la URL (`?tab=citas`) y se restaura al cargar la página.  
Los links del Dashboard de Hoy apuntan a tabs específicos:
```
/admin/crm/leads/{id}?tab=citas       ← desde citas/tareas del dashboard
/admin/crm/leads/{id}?tab=cotizaciones
```

### Badges en tabs

- **Actividad**: muestra el total de registros
- **Citas & Tareas**: muestra en rojo el número de items urgentes (citas programadas + tareas vencidas)
- **Cotizaciones**: muestra el total de cotizaciones del lead

---

## Archivos modificados

| Archivo | Cambio |
|---------|--------|
| `resources/views/admin/layouts/sidebar.blade.php` | Sidebar reducido a 5 ítems; Configuración como submenú |
| `resources/views/admin/crm/leads/show.blade.php` | Detalle del lead con 4 tabs Bootstrap + JS de tab persistence |
| `app/Http/Controllers/Admin/CrmDashboardController.php` | **Nuevo** — controller del dashboard |
| `resources/views/admin/crm/dashboard.blade.php` | **Nueva** — vista del Dashboard de Hoy |
| `routes/web.php` | Nueva ruta `GET /admin/crm/dashboard` |

---

## Cómo reactivar módulos en el sidebar

Si en el futuro se necesita volver a mostrar Tareas, Recordatorios, etc. en el sidebar, agregar dentro del bloque `<ul>` del CRM en `sidebar.blade.php`:

```blade
<li class="{{ Request::routeIs('admin.crm.tasks.*') ? 'active' : '' }}">
    <a href="{{ route('admin.crm.tasks.index') }}"><i class="fa fa-check-square-o"></i> Tareas</a>
</li>
<li class="{{ Request::routeIs('admin.crm.reminders.*') ? 'active' : '' }}">
    <a href="{{ route('admin.crm.reminders.index') }}">
        <i class="fa fa-bell"></i> Recordatorios
        @if($dueReminders > 0)<span class="badge badge-warning">{{ $dueReminders }}</span>@endif
    </a>
</li>
```

---

## Cómo agregar un nuevo tab al lead detail

1. Agregar el `<li class="nav-item">` en la lista `crm-lead-tabs` en `show.blade.php`
2. Agregar el `<div class="tab-pane fade" id="tab-nuevo">` en `#leadTabContent`
3. Agregar la clave al objeto `tabMap` en el JS de persistencia al final de la vista
