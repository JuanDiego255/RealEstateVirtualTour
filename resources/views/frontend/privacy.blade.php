@extends('frontend.front')
@section('title', 'Política de Privacidad — Space 360 CR')
@push('styles')
<style>
.pp-page { font-family: 'Segoe UI', sans-serif; background: #0d0d0d; color: #e8e8e8; min-height: 100vh; }
.pp-hero  { background: linear-gradient(135deg, #111 0%, #1a1500 100%); border-bottom: 1px solid rgba(194,172,31,.15); padding: 80px 0 48px; }
.pp-hero h1 { font-size: clamp(1.8rem,4vw,2.8rem); font-weight: 800; color: #fff; }
.pp-hero p  { color: rgba(255,255,255,.55); font-size: .95rem; margin-top: 8px; }
.pp-gold    { color: #c2ac1f; }
.pp-badge   { display: inline-block; background: rgba(194,172,31,.12); border: 1px solid rgba(194,172,31,.3); color: #c2ac1f; font-size: .72rem; font-weight: 700; letter-spacing: 2.5px; text-transform: uppercase; padding: 5px 16px; border-radius: 50px; margin-bottom: 20px; }
.pp-body    { max-width: 820px; margin: 0 auto; padding: 56px 24px 80px; }
.pp-section { margin-bottom: 40px; }
.pp-section h2 { font-size: 1.15rem; font-weight: 700; color: #fff; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid rgba(255,255,255,.07); display: flex; align-items: center; gap: 10px; }
.pp-section h2 i { color: #c2ac1f; font-size: 1rem; }
.pp-section h3 { font-size: .95rem; font-weight: 600; color: rgba(255,255,255,.85); margin: 16px 0 8px; }
.pp-section p, .pp-section li { font-size: .92rem; color: rgba(255,255,255,.6); line-height: 1.75; }
.pp-section ul { padding-left: 20px; margin-top: 8px; }
.pp-section li { margin-bottom: 6px; }
.pp-card    { background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.07); border-radius: 14px; padding: 22px 24px; margin-top: 12px; }
.pp-table   { width: 100%; border-collapse: collapse; margin-top: 12px; font-size: .88rem; }
.pp-table th { background: rgba(194,172,31,.1); color: #c2ac1f; font-weight: 600; text-align: left; padding: 10px 14px; border: 1px solid rgba(255,255,255,.07); }
.pp-table td { padding: 9px 14px; border: 1px solid rgba(255,255,255,.06); color: rgba(255,255,255,.6); vertical-align: top; }
.pp-highlight { background: rgba(194,172,31,.07); border-left: 3px solid #c2ac1f; padding: 14px 18px; border-radius: 0 10px 10px 0; margin-top: 16px; font-size: .88rem; color: rgba(255,255,255,.65); }
.pp-contact-card { background: rgba(194,172,31,.08); border: 1px solid rgba(194,172,31,.2); border-radius: 14px; padding: 24px; margin-top: 16px; }
.pp-contact-card a { color: #c2ac1f; text-decoration: none; }
.pp-contact-card a:hover { text-decoration: underline; }
.pp-updated { font-size: .8rem; color: rgba(255,255,255,.3); margin-top: 40px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,.06); }
</style>
@endpush

@section('content')
<div class="pp-page">

    {{-- Hero --}}
    <section class="pp-hero">
        <div class="container">
            <div class="pp-badge">Legal</div>
            <h1>Política de <span class="pp-gold">Privacidad</span></h1>
            <p>Space 360 CR · Versión 2.0 · Última actualización: Mayo 2026</p>
        </div>
    </section>

    {{-- Cuerpo --}}
    <div class="pp-body">

        <div class="pp-highlight">
            Esta política explica qué información recopilamos cuando usás nuestra aplicación móvil (<strong>Space 360 CR</strong>) y nuestro sitio web (<strong>space360cr.com</strong>), cómo la usamos, con quién la compartimos y cuáles son tus derechos de conformidad con la Ley N.° 8968 de Protección de la Persona frente al Tratamiento de sus Datos Personales (Costa Rica).
        </div>

        {{-- 1. Responsable --}}
        <div class="pp-section mt-5">
            <h2><i class="fas fa-building"></i> 1. Responsable del tratamiento</h2>
            <div class="pp-card">
                <p><strong style="color:#fff">Space 360 CR</strong><br>
                Correo de contacto: <a href="mailto:privacidad@space360cr.com" style="color:#c2ac1f">privacidad@space360cr.com</a><br>
                Costa Rica</p>
            </div>
        </div>

        {{-- 2. Tipos de usuarios --}}
        <div class="pp-section">
            <h2><i class="fas fa-users"></i> 2. Tipos de usuarios y alcance</h2>
            <p>Esta política aplica a dos tipos de usuarios:</p>
            <ul>
                <li><strong style="color:#fff">Usuarios públicos</strong> — personas que visitan nuestro sitio web o envían el formulario de contacto para solicitar un Tour Virtual 360°.</li>
                <li><strong style="color:#fff">Agentes / usuarios internos</strong> — colaboradores que acceden al panel CRM, módulo Kiosko o herramientas administrativas de Space 360 CR para gestionar clientes, citas, propiedades y vehículos.</li>
            </ul>
        </div>

        {{-- 3. Datos que recopilamos --}}
        <div class="pp-section">
            <h2><i class="fas fa-database"></i> 3. Datos que recopilamos</h2>

            <h3>A. Usuarios públicos (formulario de contacto)</h3>
            <table class="pp-table">
                <thead>
                    <tr>
                        <th>Dato</th>
                        <th>Obligatorio</th>
                        <th>Finalidad</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Nombre completo</td>
                        <td>Sí</td>
                        <td>Identificarte para dar seguimiento</td>
                    </tr>
                    <tr>
                        <td>Teléfono / WhatsApp</td>
                        <td>Sí</td>
                        <td>Comunicarnos con vos</td>
                    </tr>
                    <tr>
                        <td>Correo electrónico</td>
                        <td>No</td>
                        <td>Enviar información adicional si lo solicitás</td>
                    </tr>
                    <tr>
                        <td>Descripción del vehículo / mensaje</td>
                        <td>Sí</td>
                        <td>Entender tu solicitud de Tour Virtual</td>
                    </tr>
                    <tr>
                        <td>Tipo de tour solicitado</td>
                        <td>No</td>
                        <td>Clasificar tu solicitud internamente</td>
                    </tr>
                    <tr>
                        <td>Dirección IP</td>
                        <td>Automático</td>
                        <td>Registro de seguridad y prevención de abusos</td>
                    </tr>
                </tbody>
            </table>

            <h3>B. Agentes / usuarios internos (CRM y Kiosko)</h3>
            <table class="pp-table">
                <thead>
                    <tr>
                        <th>Categoría</th>
                        <th>Datos</th>
                        <th>Módulo</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Credenciales de acceso</td>
                        <td>Correo electrónico, contraseña (hash), token de sesión</td>
                        <td>Autenticación</td>
                    </tr>
                    <tr>
                        <td>Datos de contacto de clientes (leads)</td>
                        <td>Nombre, teléfono, WhatsApp, correo, dirección, redes sociales</td>
                        <td>CRM</td>
                    </tr>
                    <tr>
                        <td>Datos financieros de clientes</td>
                        <td>Presupuesto (mínimo/máximo), precio del vehículo, tasa de interés, enganche, plazo de financiamiento</td>
                        <td>CRM / Kiosko</td>
                    </tr>
                    <tr>
                        <td>Citas y ubicación</td>
                        <td>Dirección, coordenadas GPS (latitud/longitud), fecha, hora, notas de resultado</td>
                        <td>CRM</td>
                    </tr>
                    <tr>
                        <td>Datos de eventos presenciales</td>
                        <td>Nombre, teléfono, correo y fotografía de prospectos captados</td>
                        <td>Kiosko</td>
                    </tr>
                    <tr>
                        <td>Actividad comercial</td>
                        <td>Historial de interacciones, llamadas, visitas y seguimientos</td>
                        <td>CRM</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- 4. Finalidad --}}
        <div class="pp-section">
            <h2><i class="fas fa-bullseye"></i> 4. Para qué usamos tus datos</h2>
            <h3>Usuarios públicos</h3>
            <ul>
                <li>Contactarte para dar seguimiento a tu solicitud de Tour Virtual 360°.</li>
                <li>Enviarte información sobre nuestros servicios si lo solicitaste.</li>
                <li>Mejorar la calidad de nuestro servicio de atención al cliente.</li>
            </ul>
            <h3>Agentes / usuarios internos</h3>
            <ul>
                <li>Gestionar el ciclo de ventas y seguimiento de clientes (CRM).</li>
                <li>Agendar y controlar citas de visita a propiedades y vehículos.</li>
                <li>Generar cotizaciones de financiamiento de vehículos.</li>
                <li>Capturar prospectos en eventos presenciales (Kiosko).</li>
                <li>Medir el desempeño comercial por agente.</li>
            </ul>
            <p class="mt-2">No usamos datos para publicidad de terceros, perfilado automatizado ni decisiones automatizadas con efectos jurídicos.</p>
        </div>

        {{-- 5. Datos financieros --}}
        <div class="pp-section">
            <h2><i class="fas fa-dollar-sign"></i> 5. Datos financieros y cotizaciones</h2>
            <p>El módulo de cotizaciones del Kiosko y el CRM procesan información financiera de referencia de los clientes, incluyendo:</p>
            <ul>
                <li>Precio del vehículo de interés.</li>
                <li>Tasa de interés anual y plazo de financiamiento.</li>
                <li>Monto de enganche / prima.</li>
                <li>Rango de presupuesto declarado por el prospecto.</li>
            </ul>
            <p class="mt-2">Esta información se usa exclusivamente para generar cotizaciones de referencia y facilitar el proceso de venta. No compartimos datos financieros con entidades crediticias sin el consentimiento explícito del cliente.</p>
        </div>

        {{-- 6. Almacenamiento local --}}
        <div class="pp-section">
            <h2><i class="fas fa-mobile-alt"></i> 6. Almacenamiento local en el dispositivo</h2>
            <p>La aplicación móvil almacena en el dispositivo del agente:</p>
            <ul>
                <li><strong style="color:#fff">Token de sesión (api_token):</strong> guardado de forma local (SharedPreferences) para mantener la sesión activa sin necesidad de autenticarse cada vez. Es una cadena cifrada de autenticación; no contiene información personal visible.</li>
            </ul>
            <p class="mt-2">Este dato permanece en el dispositivo hasta que el agente cierra sesión en la aplicación.</p>
        </div>

        {{-- 7. Base legal --}}
        <div class="pp-section">
            <h2><i class="fas fa-gavel"></i> 7. Base legal del tratamiento</h2>
            <ul>
                <li><strong style="color:#fff">Usuarios públicos:</strong> tu consentimiento expreso al completar y enviar el formulario de contacto.</li>
                <li><strong style="color:#fff">Agentes / usuarios internos:</strong> ejecución del contrato laboral o de prestación de servicios con Space 360 CR, y el interés legítimo en gestionar las operaciones comerciales de la empresa.</li>
            </ul>
            <p class="mt-2">Podés retirar tu consentimiento en cualquier momento contactándonos.</p>
        </div>

        {{-- 8. Compartición --}}
        <div class="pp-section">
            <h2><i class="fas fa-share-alt"></i> 8. Compartición con terceros y proveedores</h2>
            <p>Compartimos datos con los siguientes proveedores de servicios bajo acuerdos de confidencialidad:</p>
            <ul>
                <li><strong style="color:#fff">Proveedor de correo electrónico (Amazon SES / Mailgun / Postmark):</strong> recibe direcciones de correo y contenido de mensajes para el envío de notificaciones y comunicaciones.</li>
                <li><strong style="color:#fff">Almacenamiento en la nube (Amazon S3 o similar):</strong> almacena imágenes de tours 360°, fotografías de prospectos y archivos multimedia.</li>
                <li><strong style="color:#fff">Proveedor de hosting / infraestructura:</strong> aloja la base de datos y los servidores de la aplicación bajo medidas de seguridad estrictas.</li>
            </ul>
            <div class="pp-highlight mt-3">
                No vendemos, alquilamos ni cedemos datos personales a terceros con fines comerciales propios. Podemos divulgar datos cuando sea requerido por ley o autoridad competente en Costa Rica.
            </div>
        </div>

        {{-- 9. Retención --}}
        <div class="pp-section">
            <h2><i class="fas fa-clock"></i> 9. Retención de datos</h2>
            <table class="pp-table">
                <thead>
                    <tr>
                        <th>Tipo de dato</th>
                        <th>Período de retención</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Formulario de contacto (usuarios públicos)</td>
                        <td>Máximo 2 años desde la última interacción</td>
                    </tr>
                    <tr>
                        <td>Leads y datos de clientes CRM</td>
                        <td>Mientras el cliente sea activo + 3 años tras el cierre del caso</td>
                    </tr>
                    <tr>
                        <td>Cotizaciones y datos financieros</td>
                        <td>5 años (obligaciones de registro comercial)</td>
                    </tr>
                    <tr>
                        <td>Fotografías de eventos (Kiosko)</td>
                        <td>Máximo 1 año</td>
                    </tr>
                    <tr>
                        <td>Logs de acceso y seguridad</td>
                        <td>90 días</td>
                    </tr>
                </tbody>
            </table>
            <p class="mt-3">Podés solicitar la eliminación de tus datos antes de estos plazos contactándonos.</p>
        </div>

        {{-- 10. Seguridad --}}
        <div class="pp-section">
            <h2><i class="fas fa-lock"></i> 10. Seguridad</h2>
            <ul>
                <li>Todas las comunicaciones entre la app y nuestro servidor se realizan mediante <strong style="color:#fff">HTTPS (TLS)</strong>.</li>
                <li>Las contraseñas se almacenan con <strong style="color:#fff">hash seguro (bcrypt)</strong>.</li>
                <li>El token de sesión se almacena únicamente en el dispositivo del agente.</li>
                <li>Los servidores tienen acceso restringido y monitoreo de seguridad.</li>
            </ul>
            <p class="mt-2">Ningún sistema es 100% seguro. En caso de incidente de seguridad que afecte tus datos personales, te notificaremos en el plazo que establece la legislación aplicable.</p>
        </div>

        {{-- 11. Tus derechos --}}
        <div class="pp-section">
            <h2><i class="fas fa-user-shield"></i> 11. Tus derechos</h2>
            <p>De acuerdo con la Ley N.° 8968 de Protección de la Persona frente al Tratamiento de sus Datos Personales (Costa Rica), tenés derecho a:</p>
            <ul>
                <li><strong style="color:#fff">Acceso</strong> — conocer qué datos tenemos sobre vos.</li>
                <li><strong style="color:#fff">Rectificación</strong> — corregir datos incorrectos.</li>
                <li><strong style="color:#fff">Supresión</strong> — solicitar la eliminación de tus datos.</li>
                <li><strong style="color:#fff">Oposición</strong> — oponerte al tratamiento para fines específicos.</li>
                <li><strong style="color:#fff">Portabilidad</strong> — recibir tus datos en formato estructurado.</li>
            </ul>
            <div class="pp-contact-card mt-3">
                <p class="mb-0">Para ejercer cualquiera de estos derechos, enviá un correo a: <a href="mailto:privacidad@space360cr.com"><strong>privacidad@space360cr.com</strong></a> con el asunto <em>"Ejercicio de derechos ARCO"</em>. Respondemos en un máximo de <strong>15 días hábiles</strong>.</p>
            </div>
        </div>

        {{-- 12. Menores --}}
        <div class="pp-section">
            <h2><i class="fas fa-child"></i> 12. Menores de edad</h2>
            <p>Nuestros servicios están dirigidos a personas mayores de 18 años. No recopilamos intencionalmente datos de menores. Si detectamos que un menor nos ha enviado datos, los eliminaremos de inmediato.</p>
        </div>

        {{-- 13. Cookies --}}
        <div class="pp-section">
            <h2><i class="fas fa-cookie-bite"></i> 13. Cookies y tecnologías similares</h2>
            <p>La <strong style="color:#fff">aplicación móvil</strong> no utiliza cookies. El <strong style="color:#fff">sitio web</strong> puede utilizar cookies técnicas esenciales para el funcionamiento de la sesión. No utilizamos cookies de seguimiento ni de publicidad de terceros.</p>
        </div>

        {{-- 14. Cambios --}}
        <div class="pp-section">
            <h2><i class="fas fa-sync-alt"></i> 14. Cambios a esta política</h2>
            <p>Podemos actualizar esta política ocasionalmente. Te notificaremos de cambios significativos a través de la app o por correo electrónico (si lo proporcionaste). La fecha de "última actualización" al inicio del documento siempre refleja la versión vigente.</p>
        </div>

        {{-- 15. Contacto --}}
        <div class="pp-section">
            <h2><i class="fas fa-envelope"></i> 15. Contacto</h2>
            <div class="pp-contact-card">
                <p class="mb-1"><strong style="color:#fff">Space 360 CR</strong></p>
                <p class="mb-1">Correo: <a href="mailto:privacidad@space360cr.com">privacidad@space360cr.com</a></p>
                <p class="mb-0">Web: <a href="https://space360cr.com" target="_blank">space360cr.com</a></p>
            </div>
        </div>

        <p class="pp-updated">
            <i class="fas fa-calendar-alt mr-2"></i>
            Política de Privacidad de Space 360 CR — Versión 2.0 — Mayo 2026 — Ley N.° 8968 CR
        </p>
    </div>
</div>
@endsection
