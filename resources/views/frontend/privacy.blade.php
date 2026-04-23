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
            <p>Space 360 CR · Última actualización: {{ \Carbon\Carbon::now()->format('d \d\e F \d\e Y') }}</p>
        </div>
    </section>

    {{-- Cuerpo --}}
    <div class="pp-body">

        <div class="pp-highlight">
            Esta política explica qué información recopilamos cuando usás nuestra aplicación móvil (<strong>Space 360 CR</strong>) y nuestro sitio web (<strong>space360cr.com</strong>), cómo la usamos y cuáles son tus derechos.
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

        {{-- 2. Datos que recopilamos --}}
        <div class="pp-section">
            <h2><i class="fas fa-database"></i> 2. Datos que recopilamos</h2>
            <p>Únicamente recopilamos los datos que vos nos proporcionás voluntariamente al completar el formulario de contacto:</p>
            <table class="pp-table mt-3">
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
                        <td>Identificarte para contactarte</td>
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
                        <td>Tipo de tour</td>
                        <td>No</td>
                        <td>Clasificar tu solicitud internamente</td>
                    </tr>
                </tbody>
            </table>
            <div class="pp-highlight mt-3">
                <i class="fas fa-shield-alt" style="color:#c2ac1f;margin-right:8px"></i>
                <strong>No recopilamos</strong> datos de ubicación, contactos, fotos, identificadores de dispositivo, ni información de pago.
            </div>
        </div>

        {{-- 3. Finalidad --}}
        <div class="pp-section">
            <h2><i class="fas fa-bullseye"></i> 3. Para qué usamos tus datos</h2>
            <ul>
                <li>Contactarte para dar seguimiento a tu solicitud de Tour Virtual 360°.</li>
                <li>Enviarte información sobre nuestros servicios si lo solicitaste.</li>
                <li>Mejorar la calidad de nuestro servicio de atención al cliente.</li>
            </ul>
            <p class="mt-2">No usamos tus datos para publicidad de terceros, perfilado automatizado ni decisiones automatizadas con efectos jurídicos.</p>
        </div>

        {{-- 4. Base legal --}}
        <div class="pp-section">
            <h2><i class="fas fa-gavel"></i> 4. Base legal del tratamiento</h2>
            <p>El tratamiento de tus datos se basa en <strong style="color:#fff">tu consentimiento expreso</strong>, que otorgás al completar y enviar el formulario de contacto. Podés retirar este consentimiento en cualquier momento contactándonos.</p>
        </div>

        {{-- 5. Compartición --}}
        <div class="pp-section">
            <h2><i class="fas fa-share-alt"></i> 5. Compartición con terceros</h2>
            <p>No vendemos, alquilamos ni compartimos tus datos personales con terceros, excepto:</p>
            <ul>
                <li><strong style="color:#fff">Proveedores de infraestructura</strong> (servidor de hosting) que procesan los datos bajo acuerdos de confidencialidad.</li>
                <li>Cuando sea requerido por <strong style="color:#fff">ley o autoridad competente</strong> en Costa Rica.</li>
            </ul>
        </div>

        {{-- 6. Retención --}}
        <div class="pp-section">
            <h2><i class="fas fa-clock"></i> 6. Retención de datos</h2>
            <p>Conservamos tus datos de contacto por un máximo de <strong style="color:#fff">2 años</strong> desde la última interacción, o hasta que solicitás su eliminación. Pasado ese plazo, los datos son eliminados de forma segura.</p>
        </div>

        {{-- 7. Seguridad --}}
        <div class="pp-section">
            <h2><i class="fas fa-lock"></i> 7. Seguridad</h2>
            <p>Todas las comunicaciones entre la app y nuestro servidor se realizan mediante <strong style="color:#fff">HTTPS (TLS)</strong>. Los datos se almacenan en servidores con acceso restringido. No obstante, ningún sistema es 100 % seguro y no podemos garantizar seguridad absoluta.</p>
        </div>

        {{-- 8. Tus derechos --}}
        <div class="pp-section">
            <h2><i class="fas fa-user-shield"></i> 8. Tus derechos</h2>
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

        {{-- 9. Cookies --}}
        <div class="pp-section">
            <h2><i class="fas fa-cookie-bite"></i> 9. Cookies y tecnologías similares</h2>
            <p>La <strong style="color:#fff">aplicación móvil</strong> no utiliza cookies. El <strong style="color:#fff">sitio web</strong> puede utilizar cookies técnicas esenciales para el funcionamiento de la sesión. No utilizamos cookies de seguimiento ni de publicidad de terceros.</p>
        </div>

        {{-- 10. Menores --}}
        <div class="pp-section">
            <h2><i class="fas fa-child"></i> 10. Menores de edad</h2>
            <p>Nuestros servicios están dirigidos a personas mayores de 18 años. No recopilamos intencionalmente datos de menores. Si detectamos que un menor nos ha enviado datos, los eliminaremos de inmediato.</p>
        </div>

        {{-- 11. Cambios --}}
        <div class="pp-section">
            <h2><i class="fas fa-sync-alt"></i> 11. Cambios a esta política</h2>
            <p>Podemos actualizar esta política ocasionalmente. Te notificaremos de cambios significativos a través de la app o por correo electrónico (si lo proporcionaste). La fecha de "última actualización" al inicio del documento siempre refleja la versión vigente.</p>
        </div>

        {{-- 12. Contacto --}}
        <div class="pp-section">
            <h2><i class="fas fa-envelope"></i> 12. Contacto</h2>
            <div class="pp-contact-card">
                <p class="mb-1"><strong style="color:#fff">Space 360 CR</strong></p>
                <p class="mb-1">Correo: <a href="mailto:privacidad@space360cr.com">privacidad@space360cr.com</a></p>
                <p class="mb-0">Web: <a href="https://space360cr.com" target="_blank">space360cr.com</a></p>
            </div>
        </div>

        <p class="pp-updated">
            <i class="fas fa-calendar-alt mr-2"></i>
            Política de Privacidad de Space 360 CR — Versión 1.0 — {{ \Carbon\Carbon::now()->format('d/m/Y') }}
        </p>
    </div>
</div>
@endsection
