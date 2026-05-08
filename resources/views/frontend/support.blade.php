@extends('frontend.front')
@section('title', 'Soporte — Space 360 CR')
@push('styles')
<style>
.sp-page  { font-family: 'Segoe UI', sans-serif; background: #0d0d0d; color: #e8e8e8; min-height: 100vh; }

/* ── Hero ── */
.sp-hero  { background: linear-gradient(135deg, #0d0d0d 0%, #1a1500 60%, #0d0d0d 100%); border-bottom: 1px solid rgba(194,172,31,.15); padding: 90px 0 60px; position: relative; overflow: hidden; }
.sp-hero::before { content:''; position:absolute; inset:0; background: radial-gradient(ellipse 60% 80% at 50% 0%, rgba(194,172,31,.07) 0%, transparent 70%); pointer-events:none; }
.sp-hero h1 { font-size: clamp(2rem,5vw,3.2rem); font-weight: 800; color: #fff; letter-spacing: -.5px; }
.sp-hero h1 span { color: #c2ac1f; }
.sp-hero p  { color: rgba(255,255,255,.5); font-size: 1rem; margin-top: 10px; max-width: 480px; line-height: 1.7; }
.sp-badge  { display:inline-flex; align-items:center; gap:8px; background:rgba(194,172,31,.12); border:1px solid rgba(194,172,31,.3); color:#c2ac1f; font-size:.72rem; font-weight:700; letter-spacing:2.5px; text-transform:uppercase; padding:6px 18px; border-radius:50px; margin-bottom:22px; }
.sp-badge i { font-size:.9rem; }

/* ── Body ── */
.sp-body   { max-width: 760px; margin: 0 auto; padding: 60px 24px 90px; }

/* ── Channel cards ── */
.sp-cards  { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 20px; margin-top: 40px; }
.sp-card   {
    background: #161616;
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 20px;
    padding: 32px 24px 28px;
    text-align: center;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 14px;
    transition: transform .2s, border-color .2s, box-shadow .2s;
    cursor: pointer;
}
.sp-card:hover { transform: translateY(-4px); border-color: rgba(194,172,31,.4); box-shadow: 0 12px 40px rgba(194,172,31,.08); text-decoration: none; }
.sp-card-icon  { width:64px; height:64px; border-radius:18px; display:flex; align-items:center; justify-content:center; font-size:1.6rem; margin-bottom:4px; }
.sp-card-label { font-size:.72rem; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:rgba(255,255,255,.35); }
.sp-card-value { font-size:1rem; font-weight:700; color:#fff; line-height:1.3; }
.sp-card-hint  { font-size:.8rem; color:rgba(255,255,255,.4); }
.sp-card-btn   { margin-top:4px; display:inline-flex; align-items:center; gap:6px; background:transparent; border:1px solid; border-radius:50px; padding:7px 18px; font-size:.8rem; font-weight:600; transition:background .18s, color .18s; }

/* WhatsApp */
.sp-card--wa   .sp-card-icon { background:rgba(37,211,102,.12); color:#25d366; }
.sp-card--wa   .sp-card-btn  { border-color:#25d366; color:#25d366; }
.sp-card--wa:hover .sp-card-btn { background:#25d366; color:#000; }

/* Email */
.sp-card--email .sp-card-icon { background:rgba(194,172,31,.12); color:#c2ac1f; }
.sp-card--email .sp-card-btn  { border-color:#c2ac1f; color:#c2ac1f; }
.sp-card--email:hover .sp-card-btn { background:#c2ac1f; color:#000; }

/* Web */
.sp-card--web  .sp-card-icon { background:rgba(99,179,255,.1); color:#63b3ff; }
.sp-card--web  .sp-card-btn  { border-color:#63b3ff; color:#63b3ff; }
.sp-card--web:hover .sp-card-btn { background:#63b3ff; color:#000; }

/* ── Info strip ── */
.sp-strip { margin-top:44px; background:rgba(194,172,31,.06); border:1px solid rgba(194,172,31,.15); border-radius:16px; padding:28px 30px; display:flex; align-items:center; gap:20px; }
.sp-strip i { font-size:1.8rem; color:#c2ac1f; flex-shrink:0; }
.sp-strip p { margin:0; font-size:.9rem; color:rgba(255,255,255,.6); line-height:1.7; }
.sp-strip strong { color:#fff; }

/* ── Footer note ── */
.sp-foot { margin-top:48px; padding-top:20px; border-top:1px solid rgba(255,255,255,.05); font-size:.8rem; color:rgba(255,255,255,.25); text-align:center; }
.sp-foot a { color:rgba(194,172,31,.6); text-decoration:none; }
.sp-foot a:hover { color:#c2ac1f; }

@media (max-width:520px) {
    .sp-cards { grid-template-columns: 1fr; }
    .sp-strip { flex-direction:column; text-align:center; }
}
</style>
@endpush

@section('content')
<div class="sp-page">

    {{-- Hero --}}
    <section class="sp-hero">
        <div class="container">
            <div class="sp-badge"><i class="fas fa-headset"></i> Soporte</div>
            <h1>¿Necesitás <span>ayuda?</span></h1>
            <p>Estamos aquí para vos. Contactanos por el canal que prefieras y te respondemos lo antes posible.</p>
        </div>
    </section>

    {{-- Contact cards --}}
    <div class="sp-body">

        <div class="sp-cards">

            {{-- WhatsApp --}}
            <a href="https://wa.me/50686182152" target="_blank" class="sp-card sp-card--wa">
                <div class="sp-card-icon"><i class="fab fa-whatsapp"></i></div>
                <div>
                    <div class="sp-card-label">WhatsApp</div>
                    <div class="sp-card-value">+506 8618-2152</div>
                    <div class="sp-card-hint">Lun–Sáb · 8 am – 6 pm</div>
                </div>
                <div class="sp-card-btn"><i class="fab fa-whatsapp"></i> Abrir chat</div>
            </a>

            {{-- Email --}}
            <a href="mailto:virtualtour@space360cr.com" class="sp-card sp-card--email">
                <div class="sp-card-icon"><i class="fas fa-envelope"></i></div>
                <div>
                    <div class="sp-card-label">Correo electrónico</div>
                    <div class="sp-card-value" style="font-size:.88rem">virtualtour@space360cr.com</div>
                    <div class="sp-card-hint">Respondemos en &lt; 24 h</div>
                </div>
                <div class="sp-card-btn"><i class="fas fa-paper-plane"></i> Enviar correo</div>
            </a>

            {{-- Web --}}
            <a href="https://space360cr.com" target="_blank" class="sp-card sp-card--web">
                <div class="sp-card-icon"><i class="fas fa-globe"></i></div>
                <div>
                    <div class="sp-card-label">Sitio web</div>
                    <div class="sp-card-value">space360cr.com</div>
                    <div class="sp-card-hint">Tours · Vehículos · Info</div>
                </div>
                <div class="sp-card-btn"><i class="fas fa-external-link-alt"></i> Visitar</div>
            </a>

        </div>

        {{-- Info strip --}}
        <div class="sp-strip">
            <i class="fas fa-clock"></i>
            <p>
                <strong>Horario de atención</strong><br>
                Lunes a Sábado de <strong>8:00 a.m.</strong> a <strong>6:00 p.m.</strong> (hora Costa Rica, UTC−6).<br>
                Para consultas fuera de horario, envianos un correo y te contactamos el siguiente día hábil.
            </p>
        </div>

        <p class="sp-foot">
            Space 360 CR &mdash; Costa Rica &nbsp;·&nbsp;
            <a href="{{ route('privacy') }}">Política de Privacidad</a>
            &nbsp;·&nbsp; © {{ date('Y') }} Todos los derechos reservados.
        </p>

    </div>
</div>
@endsection
