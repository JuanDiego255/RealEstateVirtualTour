@if(!$isPdf)
@extends('admin.main')
@section('title', 'Manual de Usuario CRM')
@section('content')
@include('admin.crm.layouts._crm-styles')
<style>
.manual-wrap { max-width: 960px; margin: 0 auto; padding: 24px 16px; }
.manual-title { color: #1a1a2e; font-size: 28px; font-weight: 700; border-bottom: 4px solid #c2ac1f; padding-bottom: 12px; margin-bottom: 8px; }
.manual-subtitle { color: #666; font-size: 14px; margin-bottom: 32px; }
.manual-toc { background: #f8f8f2; border-left: 4px solid #c2ac1f; border-radius: 8px; padding: 16px 20px; margin-bottom: 32px; }
.manual-toc h4 { color: #1a1a2e; font-size: 15px; font-weight: 700; margin-bottom: 10px; }
.manual-toc ol { margin: 0; padding-left: 20px; column-count: 2; }
.manual-toc li { font-size: 13px; margin-bottom: 4px; }
.manual-toc a { color: #1a1a2e; text-decoration: none; }
.manual-toc a:hover { color: #c2ac1f; }
.manual-section { margin-bottom: 40px; }
.manual-section h2 { color: #1a1a2e; font-size: 19px; font-weight: 700; border-left: 5px solid #c2ac1f; padding-left: 14px; margin-bottom: 14px; margin-top: 0; }
.manual-section h3 { color: #2d2d4e; font-size: 15px; font-weight: 600; margin-top: 18px; margin-bottom: 8px; }
.manual-section p, .manual-section li { font-size: 13.5px; color: #333; line-height: 1.7; }
.manual-section ul, .manual-section ol { padding-left: 22px; }
.manual-tip { background: #fffbeb; border-left: 4px solid #c2ac1f; border-radius: 6px; padding: 10px 14px; margin: 14px 0; font-size: 13px; color: #444; }
.manual-tip strong { color: #c2ac1f; }
.manual-note { background: #eff6ff; border-left: 4px solid #3b82f6; border-radius: 6px; padding: 10px 14px; margin: 14px 0; font-size: 13px; color: #1e3a5f; }
.manual-warn { background: #fff1f2; border-left: 4px solid #ef4444; border-radius: 6px; padding: 10px 14px; margin: 14px 0; font-size: 13px; color: #7f1d1d; }
.manual-table { width: 100%; border-collapse: collapse; margin: 12px 0; font-size: 13px; }
.manual-table th { background: #1a1a2e; color: #fff; padding: 8px 10px; text-align: left; font-weight: 600; }
.manual-table td { border: 1px solid #e5e7eb; padding: 7px 10px; color: #333; }
.manual-table tr:nth-child(even) { background: #f9f9f9; }
.badge-nuevo { background:#dbeafe;color:#1d4ed8;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600; }
.badge-contactado { background:#fef9c3;color:#854d0e;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600; }
.badge-calificado { background:#dcfce7;color:#166534;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600; }
.badge-propuesta { background:#f3e8ff;color:#6b21a8;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600; }
.badge-negociacion { background:#ffedd5;color:#9a3412;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600; }
.badge-ganado { background:#d1fae5;color:#065f46;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600; }
.badge-perdido { background:#fee2e2;color:#991b1b;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600; }
.pdf-btn { display:inline-flex;align-items:center;gap:8px;background:#ef4444;color:#fff;border:none;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;cursor:pointer; }
.pdf-btn:hover { background:#dc2626;color:#fff; }
.back-btn { display:inline-flex;align-items:center;gap:8px;background:#1a1a2e;color:#fff;border:none;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;cursor:pointer; }
.back-btn:hover { background:#2d2d4e;color:#fff; }
.step-list { counter-reset: steps; list-style: none; padding-left: 0; }
.step-list li { counter-increment: steps; padding: 6px 0 6px 36px; position: relative; font-size: 13.5px; color: #333; }
.step-list li::before { content: counter(steps); position: absolute; left: 0; top: 4px; background: #1a1a2e; color: #fff; width: 22px; height: 22px; border-radius: 50%; font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; }
.divider { border: none; border-top: 1px solid #e5e7eb; margin: 32px 0; }
</style>
<div class="crm-page">
    <div class="crm-page-header">
        <div>
            <h2><i class="fa fa-book"></i> Manual de Usuario — CRM</h2>
            <div class="sub">Guía completa del sistema de gestión de clientes</div>
        </div>
        <div class="actions">
            <a href="{{ route('admin.crm.leads.index') }}" class="back-btn"><i class="fa fa-arrow-left"></i> Volver al CRM</a>
            <a href="{{ route('admin.crm.manual.pdf') }}" target="_blank" class="pdf-btn"><i class="fa fa-file-pdf-o"></i> Descargar PDF</a>
        </div>
    </div>
    <div class="manual-wrap">
@else
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  body { font-family: helvetica, sans-serif; font-size: 12px; color: #333; margin: 20px; }
  h1 { color: #1a1a2e; font-size: 20px; border-bottom: 3px solid #c2ac1f; padding-bottom: 8px; margin-bottom: 4px; }
  .manual-subtitle { color: #666; font-size: 11px; margin-bottom: 18px; }
  h2 { color: #1a1a2e; font-size: 15px; margin-top: 24px; border-left: 4px solid #c2ac1f; padding-left: 10px; }
  h3 { color: #444; font-size: 13px; margin-top: 16px; }
  p, li { font-size: 12px; line-height: 1.6; color: #333; }
  ul, ol { padding-left: 20px; }
  table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 11px; }
  th { background: #1a1a2e; color: #fff; padding: 6px 8px; text-align: left; }
  td { border: 1px solid #ddd; padding: 5px 8px; }
  tr:nth-child(even) { background: #f9f9f9; }
  .badge { display: inline-block; padding: 2px 7px; border-radius: 10px; font-size: 10px; font-weight: bold; }
  .page-break { page-break-after: always; }
  .manual-tip { background: #fffbeb; border-left: 4px solid #c2ac1f; padding: 8px 10px; margin: 10px 0; font-size: 11px; }
  .manual-note { background: #eff6ff; border-left: 4px solid #3b82f6; padding: 8px 10px; margin: 10px 0; font-size: 11px; }
  .manual-warn { background: #fff1f2; border-left: 4px solid #ef4444; padding: 8px 10px; margin: 10px 0; font-size: 11px; }
  .step-list { list-style: decimal; padding-left: 20px; }
  .step-list li { margin-bottom: 4px; }
  .toc-list { column-count: 2; font-size: 11px; padding-left: 18px; }
  .toc-list li { margin-bottom: 3px; }
  .section-divider { border: none; border-top: 1px solid #e5e7eb; margin: 20px 0; }
</style>
</head>
<body>
@endif

@if(!$isPdf)
        {{-- TOC --}}
        <div class="manual-toc">
            <h4><i class="fa fa-list"></i> Tabla de Contenidos</h4>
            <ol>
                <li><a href="#sec1">Introducción al CRM</a></li>
                <li><a href="#sec2">Gestión de Leads</a></li>
                <li><a href="#sec3">Pipeline de Ventas</a></li>
                <li><a href="#sec4">Actividades</a></li>
                <li><a href="#sec5">Tareas</a></li>
                <li><a href="#sec6">Citas</a></li>
                <li><a href="#sec7">Recordatorios</a></li>
                <li><a href="#sec8">Plantillas de Actividad</a></li>
                <li><a href="#sec9">Plantillas de Mensajes</a></li>
                <li><a href="#sec10">Formularios Web</a></li>
                <li><a href="#sec11">Reglas de Pipeline</a></li>
                <li><a href="#sec12">Motor de Matching</a></li>
                <li><a href="#sec13">Cotizaciones</a></li>
                <li><a href="#sec14">Portal del Cliente</a></li>
                <li><a href="#sec15">Importar Leads por CSV</a></li>
                <li><a href="#sec16">Métricas y Reportes</a></li>
                <li><a href="#sec17">Roles y Permisos</a></li>
            </ol>
        </div>
@else
        <h1>Manual de Usuario — CRM</h1>
        <div class="manual-subtitle">Guía completa del sistema de gestión de clientes · {{ now()->format('d/m/Y') }}</div>
        <h3>Tabla de Contenidos</h3>
        <ol class="toc-list">
            <li>Introducción al CRM</li>
            <li>Gestión de Leads</li>
            <li>Pipeline de Ventas</li>
            <li>Actividades</li>
            <li>Tareas</li>
            <li>Citas</li>
            <li>Recordatorios</li>
            <li>Plantillas de Actividad</li>
            <li>Plantillas de Mensajes</li>
            <li>Formularios Web</li>
            <li>Reglas de Pipeline</li>
            <li>Motor de Matching</li>
            <li>Cotizaciones</li>
            <li>Portal del Cliente</li>
            <li>Importar Leads por CSV</li>
            <li>Métricas y Reportes</li>
            <li>Roles y Permisos</li>
        </ol>
        <div class="page-break"></div>
@endif

{{-- =====================================================
     SECTION 1: Introducción
     ===================================================== --}}
@if(!$isPdf)
<div class="manual-section" id="sec1">
    <h2>1. Introducción al CRM</h2>
@else
<h2>1. Introducción al CRM</h2>
@endif

<p>El <strong>CRM (Customer Relationship Management)</strong> es el módulo central de gestión de relaciones con clientes potenciales (leads) y actuales. Permite al equipo de ventas dar seguimiento completo al proceso comercial, desde la primera consulta hasta el cierre de la operación inmobiliaria.</p>

<h3>Módulos disponibles</h3>
<p>El sistema CRM integra los siguientes módulos:</p>
@if(!$isPdf)
<table class="manual-table">
@else
<table>
@endif
<thead><tr><th>Módulo</th><th>Descripción</th><th>Acceso rápido</th></tr></thead>
<tbody>
<tr><td><strong>Leads</strong></td><td>Gestión de clientes potenciales con todos sus datos y seguimiento</td><td>/admin/crm/leads</td></tr>
<tr><td><strong>Pipeline</strong></td><td>Vista Kanban del embudo de ventas con las 7 etapas del proceso</td><td>/admin/crm/leads (vista pipeline)</td></tr>
<tr><td><strong>Actividades</strong></td><td>Registro de interacciones: llamadas, emails, visitas, WhatsApp, notas</td><td>Desde detalle del lead</td></tr>
<tr><td><strong>Tareas</strong></td><td>Lista de pendientes asignables a agentes con prioridad y fecha límite</td><td>/admin/crm/tasks</td></tr>
<tr><td><strong>Citas</strong></td><td>Agenda de citas con clientes, vinculable a propiedades</td><td>/admin/crm/appointments</td></tr>
<tr><td><strong>Recordatorios</strong></td><td>Alertas programadas sobre leads para no perder seguimientos</td><td>/admin/crm/reminders</td></tr>
<tr><td><strong>Plantillas de Actividad</strong></td><td>Scripts reutilizables para llamadas y mensajes por etapa de pipeline</td><td>/admin/crm/activity-templates</td></tr>
<tr><td><strong>Plantillas de Mensajes</strong></td><td>Plantillas de WhatsApp y Email con variables dinámicas</td><td>/admin/crm/message-templates</td></tr>
<tr><td><strong>Formularios Web</strong></td><td>Formularios embebibles para captura automática de leads desde el sitio web</td><td>/admin/crm/forms</td></tr>
<tr><td><strong>Reglas de Pipeline</strong></td><td>Automatizaciones que alertan o crean recordatorios cuando un lead está inactivo</td><td>/admin/crm/pipeline-rules</td></tr>
<tr><td><strong>Matching</strong></td><td>Motor de compatibilidad entre preferencias del lead y propiedades disponibles</td><td>Desde detalle del lead</td></tr>
<tr><td><strong>Cotizaciones</strong></td><td>Generación de propuestas económicas formales para leads</td><td>/admin/crm/quotes</td></tr>
<tr><td><strong>Portal del Cliente</strong></td><td>Página segura personalizada para que el lead vea su información y propiedades</td><td>/portal/{token}</td></tr>
<tr><td><strong>Importar CSV</strong></td><td>Carga masiva de leads desde archivo Excel/CSV</td><td>/admin/crm/leads/import</td></tr>
<tr><td><strong>Métricas</strong></td><td>Dashboard con KPIs, funnel de ventas y reportes exportables</td><td>/admin/metrics</td></tr>
</tbody>
</table>

@if(!$isPdf)
<div class="manual-tip"><strong>Consejo:</strong> Comience siempre desde la sección <strong>Leads</strong> (/admin/crm/leads). Desde ahí puede acceder a todas las funcionalidades mediante el panel lateral de acceso rápido.</div>
</div>
<hr class="divider">
@else
<div class="manual-tip"><strong>Consejo:</strong> Comience siempre desde la sección <strong>Leads</strong>. Desde ahí puede acceder a todas las funcionalidades mediante el panel lateral de acceso rápido.</div>
<div class="page-break"></div>
@endif

{{-- =====================================================
     SECTION 2: Gestión de Leads
     ===================================================== --}}
@if(!$isPdf)
<div class="manual-section" id="sec2">
    <h2>2. Gestión de Leads</h2>
@else
<h2>2. Gestión de Leads</h2>
@endif

<p>Un <strong>lead</strong> es un cliente potencial que ha mostrado interés en una propiedad o servicio. El sistema permite registrar, editar y dar seguimiento completo a cada lead.</p>

<h3>Cómo crear un lead</h3>
<ol class="step-list">
    <li>Ir a <strong>/admin/crm/leads</strong> y hacer clic en <strong>"+ Nuevo Lead"</strong>.</li>
    <li>Completar los campos requeridos: nombre completo, correo electrónico y/o teléfono.</li>
    <li>Seleccionar la fuente de origen (de dónde viene el lead).</li>
    <li>Asignar el lead a un agente (campo <em>assigned_to</em>).</li>
    <li>Definir el presupuesto mínimo y máximo si se conoce.</li>
    <li>Agregar notas iniciales con información relevante.</li>
    <li>Hacer clic en <strong>"Guardar Lead"</strong>.</li>
</ol>

<h3>Campos del formulario de lead</h3>
@if(!$isPdf)
<table class="manual-table">
@else
<table>
@endif
<thead><tr><th>Campo</th><th>Tipo</th><th>Descripción</th><th>Obligatorio</th></tr></thead>
<tbody>
<tr><td>nombre</td><td>Texto</td><td>Nombre completo del cliente potencial</td><td>Sí</td></tr>
<tr><td>correo</td><td>Email</td><td>Dirección de correo electrónico</td><td>No</td></tr>
<tr><td>telefono</td><td>Texto</td><td>Número telefónico principal</td><td>No</td></tr>
<tr><td>whatsapp</td><td>Texto</td><td>Número para contacto por WhatsApp</td><td>No</td></tr>
<tr><td>fuente (source)</td><td>Select</td><td>Canal de origen del lead (ver tabla de fuentes)</td><td>No</td></tr>
<tr><td>estado (status)</td><td>Select</td><td>Etapa actual en el pipeline de ventas</td><td>Sí</td></tr>
<tr><td>prioridad (priority)</td><td>Select</td><td>Nivel de urgencia del seguimiento</td><td>No</td></tr>
<tr><td>presupuesto_min</td><td>Numérico</td><td>Presupuesto mínimo del cliente</td><td>No</td></tr>
<tr><td>presupuesto_max</td><td>Numérico</td><td>Presupuesto máximo del cliente</td><td>No</td></tr>
<tr><td>notas</td><td>Texto largo</td><td>Observaciones y comentarios sobre el lead</td><td>No</td></tr>
<tr><td>assigned_to</td><td>Select</td><td>Agente responsable del lead</td><td>No</td></tr>
</tbody>
</table>

<h3>Puntaje del lead (Score 0–100)</h3>
<p>El sistema calcula automáticamente un puntaje de 0 a 100 para cada lead basado en su nivel de interacción:</p>
<ul>
    <li><strong>0–30 puntos:</strong> Lead frío — tiene poca interacción, no ha sido contactado recientemente.</li>
    <li><strong>31–60 puntos:</strong> Lead tibio — hay algunos seguimientos pero no hay compromiso claro.</li>
    <li><strong>61–85 puntos:</strong> Lead caliente — interés evidente, múltiples actividades registradas.</li>
    <li><strong>86–100 puntos:</strong> Lead muy caliente — alta probabilidad de cierre, acciones recientes frecuentes.</li>
</ul>
<p>El puntaje aumenta con cada actividad registrada, cita completada y tarea marcada como terminada. Disminuye con el paso del tiempo sin interacción.</p>

<h3>Cómo editar y eliminar un lead</h3>
<ul>
    <li><strong>Editar:</strong> Desde la lista de leads, hacer clic en el nombre del lead o en el ícono de lápiz. Modificar los campos necesarios y guardar.</li>
    <li><strong>Eliminar:</strong> Desde el detalle del lead, hacer clic en el botón rojo de eliminación. Esta acción es irreversible y elimina también actividades, tareas y recordatorios asociados.</li>
</ul>

@if(!$isPdf)
<div class="manual-warn"><strong>Advertencia:</strong> La eliminación de un lead es permanente. Asegúrese de que realmente desea eliminar el registro antes de confirmar.</div>
</div>
<hr class="divider">
@else
<div class="manual-warn"><strong>Advertencia:</strong> La eliminación de un lead es permanente. Asegúrese antes de confirmar.</div>
<div class="page-break"></div>
@endif

{{-- =====================================================
     SECTION 3: Pipeline de Ventas
     ===================================================== --}}
@if(!$isPdf)
<div class="manual-section" id="sec3">
    <h2>3. Pipeline de Ventas</h2>
@else
<h2>3. Pipeline de Ventas</h2>
@endif

<p>El pipeline es una vista visual del embudo de ventas que muestra en qué etapa se encuentra cada lead. Permite gestionar el proceso de venta de forma ordenada y sistemática.</p>

<h3>Las 7 etapas del pipeline</h3>
@if(!$isPdf)
<table class="manual-table">
@else
<table>
@endif
<thead><tr><th>Etapa</th><th>Color</th><th>Descripción</th><th>Acción típica</th></tr></thead>
<tbody>
<tr><td><span @if(!$isPdf) class="badge-nuevo" @else class="badge" style="background:#dbeafe;color:#1d4ed8;" @endif>Nuevo</span></td><td>Azul</td><td>Lead recién ingresado al sistema, aún no contactado</td><td>Primer contacto telefónico o por WhatsApp</td></tr>
<tr><td><span @if(!$isPdf) class="badge-contactado" @else class="badge" style="background:#fef9c3;color:#854d0e;" @endif>Contactado</span></td><td>Amarillo</td><td>Se ha realizado al menos un contacto con el lead</td><td>Calificar interés y necesidades</td></tr>
<tr><td><span @if(!$isPdf) class="badge-calificado" @else class="badge" style="background:#dcfce7;color:#166534;" @endif>Calificado</span></td><td>Verde</td><td>Lead con potencial confirmado y presupuesto validado</td><td>Mostrar propiedades relevantes</td></tr>
<tr><td><span @if(!$isPdf) class="badge-propuesta" @else class="badge" style="background:#f3e8ff;color:#6b21a8;" @endif>Propuesta</span></td><td>Morado</td><td>Se ha enviado una cotización o propuesta formal</td><td>Dar seguimiento a la propuesta</td></tr>
<tr><td><span @if(!$isPdf) class="badge-negociacion" @else class="badge" style="background:#ffedd5;color:#9a3412;" @endif>Negociación</span></td><td>Naranja</td><td>Se están negociando términos y condiciones</td><td>Cerrar acuerdo final</td></tr>
<tr><td><span @if(!$isPdf) class="badge-ganado" @else class="badge" style="background:#d1fae5;color:#065f46;" @endif>Ganado</span></td><td>Verde oscuro</td><td>Operación cerrada exitosamente</td><td>Generar documentos y dar acceso al portal</td></tr>
<tr><td><span @if(!$isPdf) class="badge-perdido" @else class="badge" style="background:#fee2e2;color:#991b1b;" @endif>Perdido</span></td><td>Rojo</td><td>El lead no concretó la operación</td><td>Registrar motivo de pérdida en notas</td></tr>
</tbody>
</table>

<h3>Cómo cambiar la etapa de un lead</h3>
<p>Hay dos maneras de cambiar la etapa:</p>
<ol class="step-list">
    <li><strong>Vista Pipeline (Kanban):</strong> Arrastrar la tarjeta del lead de una columna a otra directamente.</li>
    <li><strong>Desde el detalle del lead:</strong> Hacer clic en el botón de la etapa deseada en la barra de progreso del lead.</li>
</ol>

@if(!$isPdf)
<div class="manual-tip"><strong>Consejo:</strong> Use la vista Kanban cuando necesite mover varios leads rápidamente. Use el detalle del lead para cambios individuales con más contexto.</div>
</div>
<hr class="divider">
@else
<div class="manual-tip"><strong>Consejo:</strong> Use la vista Kanban para mover varios leads rápidamente. Use el detalle del lead para cambios individuales.</div>
@endif

{{-- =====================================================
     SECTION 4: Actividades
     ===================================================== --}}
@if(!$isPdf)
<div class="manual-section" id="sec4">
    <h2>4. Actividades</h2>
@else
<h2>4. Actividades</h2>
@endif

<p>Las <strong>actividades</strong> son el registro cronológico de todas las interacciones con un lead. Cada actividad queda guardada en el historial del lead y contribuye a su puntaje (score).</p>

<h3>Tipos de actividades</h3>
@if(!$isPdf)
<table class="manual-table">
@else
<table>
@endif
<thead><tr><th>Tipo</th><th>Icono</th><th>Uso</th><th>Impacto en score</th></tr></thead>
<tbody>
<tr><td>Llamada</td><td>fa-phone</td><td>Contacto telefónico con el lead</td><td>+5 puntos</td></tr>
<tr><td>Email</td><td>fa-envelope</td><td>Envío o recepción de correo electrónico</td><td>+4 puntos</td></tr>
<tr><td>WhatsApp</td><td>fa-whatsapp</td><td>Conversación por WhatsApp</td><td>+4 puntos</td></tr>
<tr><td>Visita</td><td>fa-home</td><td>Visita física a una propiedad</td><td>+10 puntos</td></tr>
<tr><td>Reunión</td><td>fa-users</td><td>Reunión presencial o virtual con el cliente</td><td>+8 puntos</td></tr>
<tr><td>Nota</td><td>fa-sticky-note</td><td>Anotación interna sin contacto directo</td><td>+1 punto</td></tr>
<tr><td>Otro</td><td>fa-ellipsis-h</td><td>Cualquier otra interacción no categorizada</td><td>+2 puntos</td></tr>
</tbody>
</table>

<h3>Cómo registrar una actividad</h3>
<ol class="step-list">
    <li>Abrir el detalle del lead (clic en el nombre desde la lista).</li>
    <li>Localizar la sección <strong>"Nueva Actividad"</strong> o el botón <strong>"+ Actividad"</strong>.</li>
    <li>Seleccionar el tipo de actividad.</li>
    <li>Escribir el contenido/descripción de la actividad en el campo de texto.</li>
    <li>Seleccionar la fecha y hora (por defecto: ahora mismo).</li>
    <li>Hacer clic en <strong>"Guardar Actividad"</strong>.</li>
</ol>
<p>La actividad aparecerá inmediatamente en el historial cronológico del lead y el puntaje se actualizará automáticamente.</p>

@if(!$isPdf)
<div class="manual-tip"><strong>Consejo:</strong> Registre cada contacto con el lead aunque sea breve. Un historial completo ayuda a los agentes a entender el contexto al retomar un lead.</div>
</div>
<hr class="divider">
@else
<div class="manual-tip"><strong>Consejo:</strong> Registre cada contacto con el lead aunque sea breve. Un historial completo facilita el seguimiento.</div>
<div class="page-break"></div>
@endif

{{-- =====================================================
     SECTION 5: Tareas
     ===================================================== --}}
@if(!$isPdf)
<div class="manual-section" id="sec5">
    <h2>5. Tareas</h2>
@else
<h2>5. Tareas</h2>
@endif

<p>Las <strong>tareas</strong> son acciones pendientes que deben realizarse en relación con un lead. Pueden asignarse a agentes específicos con fecha límite y prioridad.</p>

<h3>Cómo crear una tarea</h3>
<ol class="step-list">
    <li>Ir a <strong>/admin/crm/tasks</strong> y clic en <strong>"+ Nueva Tarea"</strong>, o bien desde el detalle de un lead.</li>
    <li>Escribir el título descriptivo de la tarea.</li>
    <li>Seleccionar el lead al que corresponde (si aplica).</li>
    <li>Asignar a un agente (campo <em>assigned_to</em>).</li>
    <li>Establecer la prioridad (ver tabla de prioridades).</li>
    <li>Definir la fecha y hora límite.</li>
    <li>Guardar la tarea.</li>
</ol>

<h3>Niveles de prioridad</h3>
@if(!$isPdf)
<table class="manual-table">
@else
<table>
@endif
<thead><tr><th>Prioridad</th><th>Color</th><th>Uso recomendado</th></tr></thead>
<tbody>
<tr><td>Baja (low)</td><td>Gris</td><td>Tareas que pueden esperar varios días</td></tr>
<tr><td>Media (medium)</td><td>Azul</td><td>Tareas normales del flujo de trabajo</td></tr>
<tr><td>Alta (high)</td><td>Naranja</td><td>Tareas que deben completarse hoy o mañana</td></tr>
<tr><td>Urgente (urgent)</td><td>Rojo</td><td>Acción inmediata requerida</td></tr>
</tbody>
</table>

<h3>Vistas disponibles</h3>
<ul>
    <li><strong>Vista Kanban:</strong> Columnas por estado (Pendiente / En progreso / Completada). Permite arrastrar tareas entre estados.</li>
    <li><strong>Vista Lista:</strong> Tabla ordenable con filtros por agente, prioridad, fecha y estado.</li>
</ul>

<h3>Alertas de tareas vencidas</h3>
<p>Las tareas cuya fecha límite ha pasado sin ser completadas aparecen resaltadas en rojo en ambas vistas. El sistema también muestra un contador de tareas vencidas en el menú superior del CRM.</p>

<h3>Marcar como completada</h3>
<p>Desde la lista o desde el Kanban, hacer clic en el botón de check (✓) junto a la tarea. También puede ir al detalle de la tarea y usar el botón <strong>"Marcar como Completada"</strong>.</p>

@if(!$isPdf)
</div>
<hr class="divider">
@else
<div class="page-break"></div>
@endif

{{-- =====================================================
     SECTION 6: Citas
     ===================================================== --}}
@if(!$isPdf)
<div class="manual-section" id="sec6">
    <h2>6. Citas</h2>
@else
<h2>6. Citas</h2>
@endif

<p>El módulo de <strong>citas</strong> permite programar reuniones y visitas a propiedades con los leads. Dispone de una vista de calendario completa.</p>

<h3>Cómo crear una cita</h3>
<ol class="step-list">
    <li>Desde el detalle de un lead, localizar la sección <strong>"Citas"</strong> y clic en <strong>"+ Nueva Cita"</strong>.</li>
    <li>Alternativamente, ir a <strong>/admin/crm/appointments/create</strong>.</li>
    <li>Seleccionar el lead asociado (búsqueda por nombre).</li>
    <li>Definir título, descripción y tipo de cita.</li>
    <li>Establecer fecha y hora de inicio y fin.</li>
    <li>Vincular una propiedad si la cita es para una visita (campo <em>property_id</em>).</li>
    <li>Guardar la cita.</li>
</ol>

<h3>Estados de una cita</h3>
@if(!$isPdf)
<table class="manual-table">
@else
<table>
@endif
<thead><tr><th>Estado</th><th>Descripción</th><th>Acción</th></tr></thead>
<tbody>
<tr><td>Programada (scheduled)</td><td>Cita confirmada y pendiente de realizarse</td><td>Estado inicial por defecto</td></tr>
<tr><td>Completada (completed)</td><td>La cita se realizó exitosamente</td><td>Botón "Completar" desde la cita</td></tr>
<tr><td>Cancelada (cancelled)</td><td>La cita fue cancelada por alguna de las partes</td><td>Botón "Cancelar" desde la cita</td></tr>
</tbody>
</table>

<h3>Vista de calendario</h3>
<p>En <strong>/admin/crm/appointments</strong> encontrará un calendario mensual con todas las citas. También puede ver las citas del día en <strong>/admin/crm/appointments/today</strong> y las próximas citas en <strong>/admin/crm/appointments/upcoming</strong>.</p>

@if(!$isPdf)
<div class="manual-note"><strong>Nota:</strong> Al vincular una cita a una propiedad, el lead podrá ver esa propiedad en su Portal del Cliente si el portal está habilitado.</div>
</div>
<hr class="divider">
@else
<div class="manual-note"><strong>Nota:</strong> Al vincular una cita a una propiedad, el lead podrá ver esa propiedad en su Portal del Cliente si el portal está habilitado.</div>
<div class="page-break"></div>
@endif

{{-- =====================================================
     SECTION 7: Recordatorios
     ===================================================== --}}
@if(!$isPdf)
<div class="manual-section" id="sec7">
    <h2>7. Recordatorios</h2>
@else
<h2>7. Recordatorios</h2>
@endif

<p>Los <strong>recordatorios</strong> son alertas programadas que aparecen en el dashboard del CRM para indicar que debe realizarse un seguimiento a un lead en un momento específico.</p>

<h3>Cómo crear un recordatorio</h3>
<ol class="step-list">
    <li>Desde el detalle del lead, localizar la sección <strong>"Recordatorios"</strong> o usar el botón de campana <strong>"+ Recordatorio"</strong>.</li>
    <li>Escribir el mensaje del recordatorio (qué debe hacerse).</li>
    <li>Definir la fecha y hora en que debe aparecer la alerta.</li>
    <li>Guardar el recordatorio.</li>
</ol>
<p>También puede crear recordatorios desde <strong>/admin/crm/reminders/create</strong> y en ese caso seleccionar el lead manualmente.</p>

<h3>Recordatorios en el dashboard</h3>
<p>Los recordatorios pendientes aparecen en la parte superior del dashboard del CRM como notificaciones. También se muestra un contador de recordatorios vencidos/pendientes en el menú superior.</p>

<h3>Acciones sobre un recordatorio</h3>
@if(!$isPdf)
<table class="manual-table">
@else
<table>
@endif
<thead><tr><th>Acción</th><th>Descripción</th></tr></thead>
<tbody>
<tr><td>Completar</td><td>Marca el recordatorio como atendido. Se elimina de la lista de pendientes.</td></tr>
<tr><td>Posponer (Snooze)</td><td>Retrasa el recordatorio una cantidad de minutos/horas. Vuelve a aparecer en el tiempo indicado.</td></tr>
<tr><td>Descartar (Dismiss)</td><td>Oculta el recordatorio sin marcarlo como completado.</td></tr>
</tbody>
</table>

@if(!$isPdf)
<div class="manual-tip"><strong>Consejo:</strong> Use recordatorios para tareas de seguimiento como "Llamar al cliente en 2 días" o "Enviar información de proyecto mañana a las 10am".</div>
</div>
<hr class="divider">
@else
<div class="manual-tip"><strong>Consejo:</strong> Use recordatorios para seguimientos como "Llamar al cliente en 2 días" o "Enviar información mañana a las 10am".</div>
@endif

{{-- =====================================================
     SECTION 8: Plantillas de Actividad
     ===================================================== --}}
@if(!$isPdf)
<div class="manual-section" id="sec8">
    <h2>8. Plantillas de Actividad</h2>
@else
<h2>8. Plantillas de Actividad</h2>
@endif

<p>Las <strong>plantillas de actividad</strong> son guiones o scripts reutilizables para las interacciones más comunes con leads. Se asocian a etapas específicas del pipeline para guiar al agente en lo que debe hacer cuando un lead llega a esa etapa.</p>

<h3>Cómo crear una plantilla de actividad</h3>
<ol class="step-list">
    <li>Ir a <strong>/admin/crm/activity-templates/create</strong>.</li>
    <li>Asignar un nombre descriptivo a la plantilla.</li>
    <li>Seleccionar el tipo de actividad (Llamada, Email, WhatsApp, etc.).</li>
    <li>Seleccionar la etapa del pipeline a la que corresponde.</li>
    <li>Escribir el contenido/guión de la plantilla usando variables dinámicas.</li>
    <li>Activar o desactivar la opción <em>auto_create_task</em> según se necesite.</li>
    <li>Guardar la plantilla.</li>
</ol>

<h3>Variables disponibles</h3>
@if(!$isPdf)
<table class="manual-table">
@else
<table>
@endif
<thead><tr><th>Variable</th><th>Reemplaza con</th><th>Ejemplo</th></tr></thead>
<tbody>
<tr><td>{{'{{'}}nombre{{'}}'}}</td><td>Nombre completo del lead</td><td>Estimado Carlos Rodríguez</td></tr>
<tr><td>{{'{{'}}agente{{'}}'}}</td><td>Nombre del agente asignado</td><td>Con saludos de María García</td></tr>
<tr><td>{{'{{'}}propiedad{{'}}'}}</td><td>Nombre de la propiedad de interés</td><td>Casa en Escazú #A-201</td></tr>
<tr><td>{{'{{'}}fecha{{'}}'}}</td><td>Fecha actual formateada</td><td>15 de mayo de 2026</td></tr>
</tbody>
</table>

<h3>Creación automática de tarea (auto_create_task)</h3>
<p>Cuando la opción <strong>auto_create_task</strong> está activada en una plantilla, el sistema creará automáticamente una tarea en el lead cada vez que ese lead avance a la etapa asociada a la plantilla. Esto garantiza que el agente siempre tenga un recordatorio de la acción que debe realizar en ese punto del proceso.</p>

@if(!$isPdf)
<div class="manual-note"><strong>Nota:</strong> Solo se pueden crear plantillas para las etapas del pipeline: Nuevo, Contactado, Calificado, Propuesta, Negociación. No aplica para Ganado o Perdido.</div>
</div>
<hr class="divider">
@else
<div class="manual-note"><strong>Nota:</strong> Solo aplica para las etapas: Nuevo, Contactado, Calificado, Propuesta, Negociación.</div>
<div class="page-break"></div>
@endif

{{-- =====================================================
     SECTION 9: Plantillas de Mensajes
     ===================================================== --}}
@if(!$isPdf)
<div class="manual-section" id="sec9">
    <h2>9. Plantillas de Mensajes</h2>
@else
<h2>9. Plantillas de Mensajes</h2>
@endif

<p>Las <strong>plantillas de mensajes</strong> son textos predefinidos para WhatsApp y Email que permiten enviar comunicaciones profesionales y consistentes a los leads de forma ágil.</p>

<h3>Tipos de plantillas</h3>
<ul>
    <li><strong>WhatsApp:</strong> Mensajes de texto para enviar por WhatsApp. Se abren directamente en WhatsApp Web con el mensaje prellenado.</li>
    <li><strong>Email:</strong> Correos con asunto y cuerpo predefinidos. Se envían desde el sistema usando el correo configurado de la empresa.</li>
</ul>

<h3>Variables disponibles</h3>
@if(!$isPdf)
<table class="manual-table">
@else
<table>
@endif
<thead><tr><th>Variable</th><th>Reemplaza con</th></tr></thead>
<tbody>
<tr><td>{{'{{'}}nombre{{'}}'}}</td><td>Nombre completo del lead</td></tr>
<tr><td>{{'{{'}}agente{{'}}'}}</td><td>Nombre del agente asignado al lead</td></tr>
<tr><td>{{'{{'}}propiedad{{'}}'}}</td><td>Nombre de la propiedad de interés</td></tr>
<tr><td>{{'{{'}}fecha{{'}}'}}</td><td>Fecha actual formateada</td></tr>
<tr><td>{{'{{'}}enlace{{'}}'}}</td><td>URL del portal del cliente o enlace de la propiedad</td></tr>
</tbody>
</table>

<h3>Cómo usar una plantilla al enviar un mensaje</h3>
<ol class="step-list">
    <li>Abrir el detalle del lead.</li>
    <li>Hacer clic en <strong>"Enviar Email"</strong> o el ícono de WhatsApp.</li>
    <li>En el modal de envío, seleccionar una plantilla del menú desplegable.</li>
    <li>El sistema rellenará automáticamente el mensaje con los datos del lead.</li>
    <li>Revisar y editar el mensaje si es necesario antes de enviar.</li>
    <li>Confirmar el envío.</li>
</ol>

@if(!$isPdf)
<div class="manual-tip"><strong>Consejo:</strong> Cree plantillas específicas para cada etapa del pipeline: bienvenida (Nuevo), calificación (Contactado), propuesta (Propuesta), cierre (Negociación).</div>
</div>
<hr class="divider">
@else
<div class="manual-tip"><strong>Consejo:</strong> Cree plantillas para cada etapa del pipeline: bienvenida, calificación, propuesta y cierre.</div>
@endif

{{-- =====================================================
     SECTION 10: Formularios Web
     ===================================================== --}}
@if(!$isPdf)
<div class="manual-section" id="sec10">
    <h2>10. Formularios Web</h2>
@else
<h2>10. Formularios Web</h2>
@endif

<p>Los <strong>formularios web</strong> permiten crear formularios de captura de leads embebibles en sitios web externos. Cuando un visitante completa el formulario, el sistema crea automáticamente un nuevo lead en el CRM.</p>

<h3>Cómo crear un formulario web</h3>
<ol class="step-list">
    <li>Ir a <strong>/admin/crm/forms/create</strong>.</li>
    <li>Asignar un nombre al formulario (uso interno).</li>
    <li>Definir un <em>slug</em> único que formará parte de la URL pública.</li>
    <li>Seleccionar qué campos mostrar (nombre, correo, teléfono, mensaje, etc.).</li>
    <li>Personalizar el título y texto de confirmación que verá el usuario.</li>
    <li>Guardar el formulario.</li>
</ol>

<h3>URL pública del formulario</h3>
<p>Cada formulario tiene una URL pública en el formato: <strong>/f/{slug}</strong></p>
<p>Por ejemplo, si el slug es "contacto-web", la URL será: <em>https://sudominio.com/f/contacto-web</em></p>

<h3>Cómo integrar en un sitio web externo</h3>
<p>Desde el detalle del formulario en <strong>/admin/crm/forms/{id}</strong>, encontrará el código HTML para embeber. Copie ese código e insértelo en la página HTML donde desee mostrar el formulario. El código incluye un iframe que carga el formulario directamente.</p>

<h3>Procesamiento automático de submissions</h3>
<p>Cuando alguien completa el formulario:</p>
<ul>
    <li>Se crea un lead nuevo en el CRM con los datos enviados.</li>
    <li>La fuente del lead se registra automáticamente como "Formulario Web".</li>
    <li>El estado inicial del lead será "Nuevo".</li>
    <li>Se puede configurar una notificación por correo al administrador.</li>
</ul>

@if(!$isPdf)
<div class="manual-note"><strong>Nota:</strong> El slug debe ser único en todo el sistema. Si intenta usar un slug ya existente, el sistema le notificará el conflicto.</div>
</div>
<hr class="divider">
@else
<div class="manual-note"><strong>Nota:</strong> El slug debe ser único. Si hay conflicto, el sistema le notificará.</div>
<div class="page-break"></div>
@endif

{{-- =====================================================
     SECTION 11: Reglas de Pipeline
     ===================================================== --}}
@if(!$isPdf)
<div class="manual-section" id="sec11">
    <h2>11. Reglas de Pipeline</h2>
@else
<h2>11. Reglas de Pipeline</h2>
@endif

<p>Las <strong>reglas de pipeline</strong> son automatizaciones que se activan cuando un lead permanece inactivo en una etapa durante un número de días determinado. Permiten que el sistema alerte automáticamente al equipo sobre leads descuidados.</p>

<h3>Cómo configurar una regla</h3>
<ol class="step-list">
    <li>Ir a <strong>/admin/crm/pipeline-rules/create</strong>.</li>
    <li>Asignar un nombre descriptivo a la regla.</li>
    <li>Seleccionar la etapa del pipeline a monitorear.</li>
    <li>Definir el número de días de inactividad que activan la regla.</li>
    <li>Seleccionar la acción a ejecutar (ver tabla de acciones).</li>
    <li>Activar la regla y guardar.</li>
</ol>

<h3>Acciones disponibles</h3>
@if(!$isPdf)
<table class="manual-table">
@else
<table>
@endif
<thead><tr><th>Acción</th><th>Clave</th><th>Descripción</th></tr></thead>
<tbody>
<tr><td>Alertar (Nota)</td><td>alert</td><td>Crea una nota automática en el lead indicando que está inactivo</td></tr>
<tr><td>Crear Recordatorio</td><td>create_reminder</td><td>Genera un recordatorio para el agente asignado al lead</td></tr>
<tr><td>Notificar Administrador</td><td>notify_admin</td><td>Envía una notificación al administrador de la empresa</td></tr>
</tbody>
</table>

<h3>Activar o desactivar una regla</h3>
<p>Desde la lista en <strong>/admin/crm/pipeline-rules</strong>, cada regla tiene un toggle de activación. Las reglas inactivas no se evalúan aunque se ejecute el comando del sistema.</p>

<h3>Ejecución automática</h3>
<p>Las reglas se evalúan diariamente a las <strong>07:00 AM</strong> mediante el comando de artisan:</p>
<pre style="background:#f3f4f6;padding:8px 12px;border-radius:6px;font-size:12px;">php artisan crm:run-pipeline-rules</pre>
<p>Este comando revisa todos los leads activos y aplica las reglas correspondientes a aquellos que llevan más días de los configurados en la misma etapa sin actividad.</p>

@if(!$isPdf)
<div class="manual-tip"><strong>Consejo:</strong> Configure al menos una regla de alerta para cada etapa con 5-7 días de inactividad. Esto garantiza que ningún lead sea olvidado por el equipo.</div>
</div>
<hr class="divider">
@else
<div class="manual-tip"><strong>Consejo:</strong> Configure reglas de alerta para cada etapa con 5-7 días de inactividad para no olvidar ningún lead.</div>
@endif

{{-- =====================================================
     SECTION 12: Motor de Matching
     ===================================================== --}}
@if(!$isPdf)
<div class="manual-section" id="sec12">
    <h2>12. Motor de Matching</h2>
@else
<h2>12. Motor de Matching</h2>
@endif

<p>El <strong>motor de matching</strong> analiza las preferencias del lead y las compara contra las propiedades disponibles en el sistema, asignando un puntaje de compatibilidad de 0 a 100 para cada combinación.</p>

<h3>Cálculo del puntaje de matching</h3>
@if(!$isPdf)
<table class="manual-table">
@else
<table>
@endif
<thead><tr><th>Criterio</th><th>Puntos máximos</th><th>Cómo se evalúa</th></tr></thead>
<tbody>
<tr><td>Ajuste de presupuesto</td><td>40 puntos</td><td>El precio de la propiedad está dentro del rango presupuestario del lead (budget_min a budget_max)</td></tr>
<tr><td>Habitaciones / cuartos</td><td>30 puntos</td><td>La cantidad de habitaciones de la propiedad está entre preferred_bedrooms_min y preferred_bedrooms_max del lead</td></tr>
<tr><td>Zona / sector</td><td>30 puntos</td><td>El sector de la propiedad coincide con alguna de las zonas preferidas del lead (preferred_zones)</td></tr>
</tbody>
</table>
<p>Una propiedad con los 100 puntos es perfectamente compatible con las preferencias del lead.</p>

<h3>Configurar preferencias del lead</h3>
<p>Las preferencias se configuran en el formulario de edición del lead, en la sección <strong>"Preferencias de Búsqueda"</strong>:</p>
<ul>
    <li><strong>preferred_zones:</strong> Lista de zonas o sectores de interés (separados por coma o seleccionados del catálogo).</li>
    <li><strong>preferred_bedrooms_min / max:</strong> Rango de habitaciones deseadas.</li>
    <li><strong>preferred_property_types:</strong> Tipo de propiedad: casa, apartamento, local comercial, terreno, etc.</li>
</ul>

<h3>Ver propiedades de matching</h3>
<ol class="step-list">
    <li>Abrir el detalle del lead.</li>
    <li>Hacer clic en la pestaña o botón <strong>"Propiedades Compatibles"</strong> (o ir a /admin/crm/leads/{id}/matching).</li>
    <li>El sistema mostrará las propiedades ordenadas por puntaje de compatibilidad, de mayor a menor.</li>
    <li>Hacer clic en una propiedad para ver su ficha completa o compartirla con el lead.</li>
</ol>

@if(!$isPdf)
<div class="manual-tip"><strong>Consejo:</strong> Siempre complete las preferencias del lead durante la fase de calificación. Cuanto más completas sean las preferencias, mejores serán las recomendaciones del motor de matching.</div>
</div>
<hr class="divider">
@else
<div class="manual-tip"><strong>Consejo:</strong> Complete las preferencias del lead en la fase de calificación para obtener mejores recomendaciones.</div>
<div class="page-break"></div>
@endif

{{-- =====================================================
     SECTION 13: Cotizaciones
     ===================================================== --}}
@if(!$isPdf)
<div class="manual-section" id="sec13">
    <h2>13. Cotizaciones</h2>
@else
<h2>13. Cotizaciones</h2>
@endif

<p>El módulo de <strong>cotizaciones</strong> permite generar propuestas económicas formales para los leads, con líneas de detalle, descuentos y exportación en PDF.</p>

<h3>Cómo crear una cotización</h3>
<ol class="step-list">
    <li>Ir a <strong>/admin/crm/quotes/create</strong> o desde el detalle del lead, sección "Cotizaciones".</li>
    <li>Seleccionar el lead al que va dirigida la cotización.</li>
    <li>Definir el número de cotización y la fecha de validez.</li>
    <li>Seleccionar la moneda: CRC (Colones) o USD (Dólares).</li>
    <li>Agregar las líneas de detalle con descripción, cantidad y precio unitario.</li>
    <li>Aplicar el porcentaje de descuento si aplica.</li>
    <li>Agregar notas o condiciones comerciales.</li>
    <li>Guardar la cotización.</li>
</ol>

<h3>Estados de una cotización</h3>
@if(!$isPdf)
<table class="manual-table">
@else
<table>
@endif
<thead><tr><th>Estado</th><th>Descripción</th><th>Transición</th></tr></thead>
<tbody>
<tr><td>Borrador</td><td>En elaboración, aún no enviada al cliente</td><td>Estado inicial</td></tr>
<tr><td>Enviada</td><td>La cotización fue enviada al lead</td><td>Botón "Marcar como Enviada"</td></tr>
<tr><td>Vista</td><td>El lead abrió la cotización (desde el portal)</td><td>Automático</td></tr>
<tr><td>Aceptada</td><td>El lead confirmó la aceptación de la propuesta</td><td>Botón "Marcar como Aceptada"</td></tr>
<tr><td>Rechazada</td><td>El lead rechazó la propuesta</td><td>Botón "Marcar como Rechazada"</td></tr>
</tbody>
</table>

<h3>Generar PDF de la cotización</h3>
<p>Desde el detalle de la cotización, hacer clic en el botón <strong>"Descargar PDF"</strong>. El sistema generará un documento PDF con el membrete de la empresa, los datos del lead y todas las líneas de la cotización.</p>
<p>La URL del PDF es: <strong>/admin/crm/quotes/{id}/pdf</strong></p>

<h3>Monedas soportadas</h3>
<ul>
    <li><strong>CRC:</strong> Colones costarricenses (₡)</li>
    <li><strong>USD:</strong> Dólares estadounidenses ($)</li>
</ul>

@if(!$isPdf)
</div>
<hr class="divider">
@endif

{{-- =====================================================
     SECTION 14: Portal del Cliente
     ===================================================== --}}
@if(!$isPdf)
<div class="manual-section" id="sec14">
    <h2>14. Portal del Cliente</h2>
@else
<h2>14. Portal del Cliente</h2>
@endif

<p>El <strong>portal del cliente</strong> es una página web segura y personalizada donde el lead puede ver su propia información, las propiedades compatibles y sus citas programadas, sin necesidad de crear una cuenta en el sistema.</p>

<h3>Cómo generar el portal para un lead</h3>
<ol class="step-list">
    <li>Abrir el detalle del lead.</li>
    <li>Localizar la sección <strong>"Portal del Cliente"</strong> en el panel de acciones.</li>
    <li>Hacer clic en <strong>"Generar Token de Portal"</strong>.</li>
    <li>El sistema generará un token único y seguro para ese lead.</li>
    <li>La URL del portal será: <strong>/portal/{token}</strong></li>
    <li>Copiar y compartir el enlace con el lead por WhatsApp o correo.</li>
</ol>

<h3>Contenido visible para el cliente</h3>
<p>Cuando el lead accede a su portal personalizado puede ver:</p>
<ul>
    <li>Su información personal registrada en el CRM.</li>
    <li>Las propiedades compatibles con sus preferencias (resultado del motor de matching).</li>
    <li>Sus citas programadas con el agente.</li>
    <li>Las cotizaciones enviadas a su nombre.</li>
</ul>

<h3>Seguridad del portal</h3>
<p>El token es único e irrepetible. Si se genera un nuevo token para el mismo lead, el anterior queda invalidado. No es posible adivinar el token de otro lead. La página no requiere contraseña adicional.</p>

@if(!$isPdf)
<div class="manual-warn"><strong>Importante:</strong> Comparta el enlace del portal solo con el lead correspondiente. El portal muestra información confidencial del proceso de compra.</div>
</div>
<hr class="divider">
@else
<div class="manual-warn"><strong>Importante:</strong> Comparta el enlace del portal solo con el lead correspondiente. Contiene información confidencial.</div>
<div class="page-break"></div>
@endif

{{-- =====================================================
     SECTION 15: Importar Leads por CSV
     ===================================================== --}}
@if(!$isPdf)
<div class="manual-section" id="sec15">
    <h2>15. Importar Leads por CSV</h2>
@else
<h2>15. Importar Leads por CSV</h2>
@endif

<p>La función de <strong>importación por CSV</strong> permite cargar múltiples leads de forma masiva desde un archivo de hoja de cálculo (Excel o CSV). Es útil cuando se migran datos de otro sistema o se reciben listas de clientes.</p>

<h3>Guía paso a paso</h3>
<ol class="step-list">
    <li>Preparar el archivo CSV con los encabezados correctos (ver tabla de columnas).</li>
    <li>Guardar el archivo en formato CSV con codificación <strong>UTF-8</strong>.</li>
    <li>Ir a <strong>/admin/crm/leads/import</strong>.</li>
    <li>Hacer clic en <strong>"Seleccionar archivo"</strong> y elegir el CSV preparado.</li>
    <li>El sistema mostrará una <strong>vista previa de las primeras 10 filas</strong> para verificar que los datos son correctos.</li>
    <li>Revisar que las columnas se mapeen correctamente a los campos del sistema.</li>
    <li>Hacer clic en <strong>"Confirmar e Importar"</strong>.</li>
    <li>Esperar a que el proceso complete. Se mostrará un resumen de leads importados vs errores.</li>
</ol>

<h3>Referencia de columnas del CSV</h3>
@if(!$isPdf)
<table class="manual-table">
@else
<table>
@endif
<thead><tr><th>Columna CSV</th><th>Campo en CRM</th><th>Obligatorio</th><th>Formato</th></tr></thead>
<tbody>
<tr><td>nombre</td><td>Nombre del lead</td><td>Sí</td><td>Texto libre</td></tr>
<tr><td>correo</td><td>Email del lead</td><td>No</td><td>email@dominio.com</td></tr>
<tr><td>telefono</td><td>Teléfono principal</td><td>No</td><td>+50612345678 o 12345678</td></tr>
<tr><td>whatsapp</td><td>Número WhatsApp</td><td>No</td><td>+50612345678</td></tr>
<tr><td>fuente</td><td>source</td><td>No</td><td>Valores: website, referral, social, cold_call, event, other</td></tr>
<tr><td>prioridad</td><td>priority</td><td>No</td><td>Valores: low, medium, high, urgent</td></tr>
<tr><td>presupuesto_min</td><td>budget_min</td><td>No</td><td>Número entero sin símbolos</td></tr>
<tr><td>presupuesto_max</td><td>budget_max</td><td>No</td><td>Número entero sin símbolos</td></tr>
<tr><td>notas</td><td>notes</td><td>No</td><td>Texto libre</td></tr>
</tbody>
</table>

<h3>Recomendaciones y límites</h3>
<ul>
    <li>Máximo recomendado: <strong>500 filas</strong> por importación para garantizar el rendimiento.</li>
    <li>Use codificación <strong>UTF-8</strong> para evitar problemas con caracteres especiales (tildes, ñ, etc.).</li>
    <li>Elimine filas en blanco o con datos incompletos antes de importar.</li>
    <li>Si un correo ya existe en el sistema, el lead no se duplicará (se actualizará).</li>
    <li>Los leads importados inician con estado <strong>"Nuevo"</strong> por defecto.</li>
</ul>

@if(!$isPdf)
<div class="manual-tip"><strong>Consejo:</strong> Descargue la plantilla de ejemplo desde la página de importación para asegurarse de usar el formato correcto desde el inicio.</div>
</div>
<hr class="divider">
@else
<div class="manual-tip"><strong>Consejo:</strong> Descargue la plantilla de ejemplo desde la página de importación para usar el formato correcto.</div>
<div class="page-break"></div>
@endif

{{-- =====================================================
     SECTION 16: Métricas y Reportes
     ===================================================== --}}
@if(!$isPdf)
<div class="manual-section" id="sec16">
    <h2>16. Métricas y Reportes</h2>
@else
<h2>16. Métricas y Reportes</h2>
@endif

<p>El módulo de <strong>métricas</strong> proporciona una visión analítica del rendimiento del equipo de ventas y el estado del pipeline. Accesible desde <strong>/admin/metrics</strong>.</p>

<h3>Dashboard principal de métricas</h3>
<p>En la página principal de métricas encontrará los siguientes indicadores:</p>
<ul>
    <li><strong>Total de leads:</strong> Conteo total de leads activos en el sistema.</li>
    <li><strong>Distribución por score:</strong> Gráfico de distribución de puntajes (fríos, tibios, calientes).</li>
    <li><strong>Funnel de etapas:</strong> Cuántos leads hay en cada etapa del pipeline en este momento.</li>
    <li><strong>Tiempo promedio en etapa:</strong> Días promedio que un lead permanece en cada etapa antes de avanzar.</li>
    <li><strong>Forecast de ventas:</strong> Proyección de ingresos basada en leads en etapas avanzadas.</li>
    <li><strong>Rendimiento por agente:</strong> Leads gestionados, actividades realizadas y conversiones por agente.</li>
</ul>

<h3>Reportes disponibles</h3>
@if(!$isPdf)
<table class="manual-table">
@else
<table>
@endif
<thead><tr><th>Reporte</th><th>URL</th><th>Descripción</th></tr></thead>
<tbody>
<tr><td>Reporte de leads</td><td>/admin/crm/reports/leads-general</td><td>Lista completa de leads con filtros por fecha, fuente y estado</td></tr>
<tr><td>Reporte de pipeline</td><td>/admin/crm/reports/pipeline</td><td>Estado actual del embudo de ventas con métricas por etapa</td></tr>
<tr><td>Reporte de actividades</td><td>/admin/crm/reports/follow-ups</td><td>Historial de actividades y seguimientos realizados</td></tr>
<tr><td>Reporte de citas</td><td>/admin/crm/reports/appointments</td><td>Citas programadas, completadas y canceladas</td></tr>
<tr><td>Sin seguimiento</td><td>/admin/crm/reports/no-followup</td><td>Leads que no han tenido actividad en los últimos días</td></tr>
<tr><td>Por agente</td><td>/admin/crm/reports/by-agent</td><td>Comparativa de rendimiento entre agentes del equipo</td></tr>
<tr><td>Recordatorios vencidos</td><td>/admin/crm/reports/overdue-reminders</td><td>Recordatorios que no fueron atendidos a tiempo</td></tr>
</tbody>
</table>

<h3>Filtros por rango de fechas</h3>
<p>La mayoría de reportes permiten filtrar por rango de fechas usando los campos <strong>"Fecha desde"</strong> y <strong>"Fecha hasta"</strong> en la parte superior de la página. Esto permite analizar períodos específicos como un mes, un trimestre o un año.</p>

<h3>Exportar reportes a PDF</h3>
<p>Cada reporte tiene un botón <strong>"Exportar PDF"</strong> que genera un documento descargable con los datos filtrados. Los PDFs incluyen el logo de la empresa y la fecha de generación.</p>

@if(!$isPdf)
<div class="manual-note"><strong>Nota:</strong> El acceso a métricas está restringido a roles de Administrador y Super Administrador.</div>
</div>
<hr class="divider">
@else
<div class="manual-note"><strong>Nota:</strong> El acceso a métricas está restringido a Administrador y Super Administrador.</div>
@endif

{{-- =====================================================
     SECTION 17: Roles y Permisos
     ===================================================== --}}
@if(!$isPdf)
<div class="manual-section" id="sec17">
    <h2>17. Roles y Permisos</h2>
@else
<h2>17. Roles y Permisos</h2>
@endif

<p>El sistema CRM implementa un modelo de permisos basado en roles que determina qué puede ver y hacer cada usuario.</p>

<h3>Roles disponibles</h3>
@if(!$isPdf)
<table class="manual-table">
@else
<table>
@endif
<thead><tr><th>Rol</th><th>Nivel de acceso</th><th>Descripción</th></tr></thead>
<tbody>
<tr><td><strong>Super Admin</strong></td><td>Total</td><td>Acceso completo a todas las empresas y módulos del sistema. Puede gestionar suscripciones, empresas y configuración global.</td></tr>
<tr><td><strong>Admin (company_admin)</strong></td><td>Empresa completa</td><td>Acceso completo a todos los leads, agentes, métricas y configuración del CRM dentro de su empresa.</td></tr>
<tr><td><strong>Agente</strong></td><td>Limitado</td><td>Solo puede ver y gestionar los leads que le han sido asignados (campo assigned_to). No accede a métricas globales ni leads de otros agentes.</td></tr>
</tbody>
</table>

<h3>Asignar leads a agentes</h3>
<p>Solo los Administradores y Super Admins pueden reasignar leads entre agentes. Para asignar un lead:</p>
<ol class="step-list">
    <li>Abrir el detalle del lead.</li>
    <li>En el formulario de edición, localizar el campo <strong>"Agente asignado"</strong> (assigned_to).</li>
    <li>Seleccionar el agente de la lista desplegable.</li>
    <li>Guardar los cambios.</li>
</ol>
<p>Al asignar un lead, el agente verá ese lead en su lista cuando inicie sesión.</p>

<h3>El campo assigned_to</h3>
<p>El campo <strong>assigned_to</strong> es clave en el sistema de permisos:</p>
<ul>
    <li>Los agentes solo ven leads donde <em>assigned_to = su ID de usuario</em>.</li>
    <li>Los administradores ven todos los leads de su empresa.</li>
    <li>Los leads sin asignar (assigned_to = null) solo son visibles para administradores.</li>
</ul>

@if(!$isPdf)
<div class="manual-tip"><strong>Consejo:</strong> Asigne siempre un agente responsable a cada lead. Los leads sin asignar pueden perderse en la lista de administración sin que ningún agente los gestione.</div>
</div>
<hr class="divider">
@else
<div class="manual-tip"><strong>Consejo:</strong> Asigne siempre un agente responsable a cada lead para evitar que queden sin gestión.</div>
<div class="page-break"></div>
@endif

{{-- =====================================================
     APPENDIX: Tablas de referencia
     ===================================================== --}}
@if(!$isPdf)
<div class="manual-section" id="appendix">
    <h2>Apéndice: Tablas de Referencia</h2>
@else
<h2>Apéndice: Tablas de Referencia</h2>
@endif

<h3>Estados del lead (status)</h3>
@if(!$isPdf)
<table class="manual-table">
@else
<table>
@endif
<thead><tr><th>Valor en sistema</th><th>Etiqueta en español</th><th>Descripción</th></tr></thead>
<tbody>
<tr><td>new</td><td>Nuevo</td><td>Lead recién ingresado, sin contacto</td></tr>
<tr><td>contacted</td><td>Contactado</td><td>Se realizó primer contacto</td></tr>
<tr><td>qualified</td><td>Calificado</td><td>Interés y presupuesto confirmados</td></tr>
<tr><td>proposal</td><td>Propuesta</td><td>Se envió propuesta formal</td></tr>
<tr><td>negotiation</td><td>Negociación</td><td>En proceso de negociación</td></tr>
<tr><td>won</td><td>Ganado</td><td>Operación cerrada exitosamente</td></tr>
<tr><td>lost</td><td>Perdido</td><td>El lead no concretó la operación</td></tr>
</tbody>
</table>

<h3>Fuentes del lead (source)</h3>
@if(!$isPdf)
<table class="manual-table">
@else
<table>
@endif
<thead><tr><th>Valor en sistema</th><th>Etiqueta en español</th></tr></thead>
<tbody>
<tr><td>website</td><td>Sitio web</td></tr>
<tr><td>referral</td><td>Referido</td></tr>
<tr><td>social</td><td>Redes sociales</td></tr>
<tr><td>cold_call</td><td>Llamada en frío</td></tr>
<tr><td>event</td><td>Evento</td></tr>
<tr><td>web_form</td><td>Formulario web</td></tr>
<tr><td>other</td><td>Otro</td></tr>
</tbody>
</table>

<h3>Prioridades (priority)</h3>
@if(!$isPdf)
<table class="manual-table">
@else
<table>
@endif
<thead><tr><th>Valor en sistema</th><th>Etiqueta en español</th><th>Color indicador</th></tr></thead>
<tbody>
<tr><td>low</td><td>Baja</td><td>Gris</td></tr>
<tr><td>medium</td><td>Media</td><td>Azul</td></tr>
<tr><td>high</td><td>Alta</td><td>Naranja</td></tr>
<tr><td>urgent</td><td>Urgente</td><td>Rojo</td></tr>
</tbody>
</table>

<h3>Tipos de actividad</h3>
@if(!$isPdf)
<table class="manual-table">
@else
<table>
@endif
<thead><tr><th>Valor en sistema</th><th>Etiqueta en español</th></tr></thead>
<tbody>
<tr><td>call</td><td>Llamada</td></tr>
<tr><td>email</td><td>Email</td></tr>
<tr><td>whatsapp</td><td>WhatsApp</td></tr>
<tr><td>visit</td><td>Visita</td></tr>
<tr><td>meeting</td><td>Reunión</td></tr>
<tr><td>note</td><td>Nota</td></tr>
<tr><td>other</td><td>Otro</td></tr>
</tbody>
</table>

@if(!$isPdf)
</div>
@endif

@if(!$isPdf)
    </div>{{-- .manual-wrap --}}
</div>{{-- .crm-page --}}
@endsection
@else
</body>
</html>
@endif
