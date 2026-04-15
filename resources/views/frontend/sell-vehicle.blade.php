@extends('frontend.front')
@section('title', 'Vende tu Vehículo con Tour Virtual 360°')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css">
<style>
.sv-page { font-family:'Poppins',sans-serif; }
.sv-gold { color:#c2ac1f; }

/* HERO */
.sv-hero { position:relative; height:100vh; min-height:620px; overflow:hidden; }
.sv-hero-slide { position:absolute; inset:0; background-size:cover; background-position:center; }
.sv-hero-overlay { position:absolute; inset:0; background:linear-gradient(135deg,rgba(0,0,0,.75),rgba(0,0,0,.45)); z-index:1; }
.sv-hero-content { position:relative; z-index:2; height:100%; display:flex; align-items:center; }
.sv-hero h1 { font-size:clamp(2rem,5vw,3.8rem); font-weight:800; line-height:1.15; color:#fff; }
.sv-hero h1 span { color:#c2ac1f; }
.sv-hero p.lead { font-size:clamp(1rem,2vw,1.2rem); color:rgba(255,255,255,.85); font-weight:300; max-width:560px; }
.sv-btn-gold { background:#c2ac1f; color:#000; font-weight:700; border:none; padding:14px 32px; border-radius:50px; font-size:1rem; transition:all .3s; text-decoration:none; display:inline-block; }
.sv-btn-gold:hover { background:#a89318; color:#000; transform:translateY(-2px); box-shadow:0 8px 25px rgba(194,172,31,.4); text-decoration:none; }
.sv-btn-outline { background:transparent; color:#fff; font-weight:600; border:2px solid rgba(255,255,255,.6); padding:13px 30px; border-radius:50px; font-size:1rem; transition:all .3s; text-decoration:none; display:inline-block; }
.sv-btn-outline:hover { border-color:#c2ac1f; color:#c2ac1f; transform:translateY(-2px); text-decoration:none; }
.sv-live-badge { display:inline-flex; align-items:center; gap:8px; background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.15); backdrop-filter:blur(8px); padding:8px 18px; border-radius:50px; color:#fff; font-size:.85rem; font-weight:500; margin-bottom:24px; }
.sv-live-dot { width:8px; height:8px; background:#ff3b30; border-radius:50%; animation:sv-pulse 1.4s ease-in-out infinite; }
@keyframes sv-pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(1.4)} }
@keyframes sv-bounce { 0%,100%{transform:translateX(-50%) translateY(0)} 50%{transform:translateX(-50%) translateY(8px)} }

/* STATS */
.sv-stats { background:#111; border-top:1px solid rgba(255,255,255,.06); border-bottom:1px solid rgba(255,255,255,.06); padding:56px 0; }
.sv-stat-num { font-size:clamp(2.5rem,5vw,4rem); font-weight:900; color:#c2ac1f; line-height:1; }
.sv-stat-label { font-size:.95rem; color:rgba(255,255,255,.6); margin-top:6px; }
.sv-stat-divider { width:1px; background:rgba(255,255,255,.1); height:60px; }

/* SECTION HEADERS */
.sv-section-tag { font-size:.78rem; font-weight:700; letter-spacing:3px; text-transform:uppercase; color:#c2ac1f; margin-bottom:12px; }
.sv-section-title { font-size:clamp(1.8rem,3.5vw,2.6rem); font-weight:800; line-height:1.2; color:#1a1a1a; }
.sv-section-title.light { color:#fff; }

/* PROBLEM */
.sv-pain-item { display:flex; align-items:flex-start; gap:14px; margin-bottom:20px; }
.sv-pain-icon { width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:.85rem; margin-top:2px; }
.sv-pain-icon.bad { background:#fee; color:#d00; }
.sv-pain-icon.good { background:#e8f5e9; color:#2e7d32; }
.sv-phone-mockup { width:260px; margin:0 auto; }
.sv-phone-frame { background:#1a1a1a; border-radius:36px; padding:14px; box-shadow:0 30px 80px rgba(0,0,0,.25),inset 0 0 0 2px rgba(255,255,255,.08); }
.sv-phone-screen { background:#000; border-radius:24px; overflow:hidden; aspect-ratio:9/19; position:relative; display:flex; align-items:center; justify-content:center; flex-direction:column; gap:10px; }

/* STEPS */
.sv-step-card { background:#fff; border-radius:20px; padding:40px 30px; text-align:center; box-shadow:0 4px 30px rgba(0,0,0,.07); transition:transform .3s,box-shadow .3s; height:100%; }
.sv-step-card:hover { transform:translateY(-6px); box-shadow:0 12px 40px rgba(0,0,0,.12); }
.sv-step-num { font-size:5rem; font-weight:900; color:rgba(194,172,31,.15); line-height:1; margin-bottom:-10px; }
.sv-step-icon { font-size:2.2rem; color:#c2ac1f; margin-bottom:16px; }
.sv-step-title { font-size:1.2rem; font-weight:700; color:#1a1a1a; margin-bottom:10px; }
.sv-step-desc { font-size:.92rem; color:#666; line-height:1.6; }

/* DEMO */
.sv-demo { background:#0a0a0a; padding:96px 0; }
.sv-laptop-frame { background:#1e1e1e; border-radius:16px 16px 8px 8px; padding:12px 12px 0; box-shadow:0 30px 80px rgba(0,0,0,.6); border:1px solid rgba(255,255,255,.08); }
.sv-laptop-bar { background:#2a2a2a; border-radius:6px 6px 0 0; height:28px; display:flex; align-items:center; padding:0 12px; gap:6px; margin-bottom:8px; }
.sv-laptop-dot { width:10px; height:10px; border-radius:50%; }
.sv-laptop-screen { background:#000; border-radius:4px; overflow:hidden; aspect-ratio:16/9; position:relative; }
.sv-laptop-base { height:16px; background:#252525; border-radius:0 0 8px 8px; margin:0 -12px; }
.sv-demo-badge { background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.1); border-radius:12px; padding:14px 18px; display:flex; align-items:center; gap:12px; margin-bottom:14px; }
.sv-demo-badge-icon { font-size:1.3rem; color:#c2ac1f; flex-shrink:0; }
.sv-demo-badge-text strong { color:#fff; display:block; font-size:.92rem; font-weight:600; }
.sv-demo-badge-text span { color:rgba(255,255,255,.5); font-size:.8rem; }

/* FEATURES */
.sv-feat-card { background:#fafafa; border:1px solid #f0f0f0; border-radius:16px; padding:32px 24px; text-align:center; transition:all .3s; height:100%; }
.sv-feat-card:hover { border-color:#c2ac1f; box-shadow:0 8px 30px rgba(194,172,31,.15); transform:translateY(-4px); }
.sv-feat-icon { font-size:2.4rem; color:#c2ac1f; margin-bottom:18px; }
.sv-feat-title { font-size:1rem; font-weight:700; color:#1a1a1a; margin-bottom:8px; }
.sv-feat-desc { font-size:.85rem; color:#888; line-height:1.55; }

/* TESTIMONIALS */
.sv-testi-card { background:#fff; border-radius:20px; padding:36px 28px; box-shadow:0 4px 20px rgba(0,0,0,.07); height:100%; }
.sv-testi-avatar { width:52px; height:52px; border-radius:50%; background:linear-gradient(135deg,#c2ac1f,#a89318); display:flex; align-items:center; justify-content:center; font-size:1.4rem; font-weight:800; color:#000; flex-shrink:0; }
.sv-testi-stars { color:#c2ac1f; font-size:.9rem; margin-bottom:14px; }
.sv-testi-quote { font-size:.95rem; color:#444; line-height:1.65; font-style:italic; margin-bottom:20px; }
.sv-testi-name { font-weight:700; color:#1a1a1a; font-size:.92rem; }
.sv-testi-city { color:#999; font-size:.82rem; }

/* FORM */
.sv-form-section { position:relative; padding:96px 0; }
.sv-form-bg { position:absolute; inset:0; background-image:url('{{ asset("virtualtour/images/bg_2.jpg") }}'); background-size:cover; background-position:center; }
.sv-form-overlay { position:absolute; inset:0; background:rgba(0,0,0,.82); }
.sv-form-wrap { position:relative; z-index:2; }
.sv-form-card { background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.1); border-radius:24px; padding:48px 40px; backdrop-filter:blur(10px); }
.sv-form-card .form-control { background:rgba(255,255,255,.07); border:1px solid rgba(255,255,255,.12); color:#fff; border-radius:12px; padding:14px 18px; font-size:.95rem; }
.sv-form-card .form-control::placeholder { color:rgba(255,255,255,.35); }
.sv-form-card .form-control:focus { background:rgba(255,255,255,.1); border-color:#c2ac1f; color:#fff; box-shadow:0 0 0 3px rgba(194,172,31,.15); }
.sv-form-card label { color:rgba(255,255,255,.7); font-size:.85rem; font-weight:500; margin-bottom:6px; }

/* QR */
.sv-qr-section { background:#0d0d0d; padding:96px 0; }
.sv-poster-preview { background:linear-gradient(135deg,#1a1a1a,#111); border:1px solid rgba(194,172,31,.3); border-radius:20px; padding:40px 30px; text-align:center; box-shadow:0 20px 60px rgba(0,0,0,.5); max-width:320px; margin:0 auto; }
.sv-poster-logo { font-size:.75rem; letter-spacing:3px; color:rgba(255,255,255,.4); text-transform:uppercase; margin-bottom:20px; }
.sv-poster-title { font-size:1.1rem; font-weight:800; color:#fff; margin-bottom:4px; }
.sv-poster-sub { font-size:.78rem; color:rgba(255,255,255,.5); margin-bottom:24px; }
.sv-poster-qr-wrap { background:#fff; border-radius:12px; padding:16px; display:inline-block; }
.sv-poster-tagline { font-size:.72rem; color:rgba(255,255,255,.35); margin-top:20px; letter-spacing:1px; text-transform:uppercase; }
.sv-qr-download-btn { background:#c2ac1f; color:#000; font-weight:700; border:none; padding:14px 32px; border-radius:50px; font-size:.95rem; transition:all .3s; cursor:pointer; display:inline-flex; align-items:center; gap:8px; }
.sv-qr-download-btn:hover { background:#a89318; transform:translateY(-2px); }

@media(max-width:767px){
  .sv-hero h1{font-size:2rem;}
  .sv-stat-divider{display:none;}
  .sv-form-card{padding:28px 18px;}
  .sv-phone-mockup{width:200px;margin-top:40px;}
}
</style>
@endpush

@section('content')
<div class="sv-page">

{{-- ===== S1: HERO ===== --}}
<section class="sv-hero">
  <div id="svHero" class="carousel slide carousel-fade h-100" data-ride="carousel" data-interval="5000">
    <div class="carousel-inner h-100">
      <div class="carousel-item h-100 active">
        <div class="sv-hero-slide" style="background-image:url('{{ asset('virtualtour/images/bg_2.jpg') }}')"></div>
      </div>
      <div class="carousel-item h-100">
        <div class="sv-hero-slide" style="background-image:url('{{ asset('virtualtour/images/bg_1.jpeg') }}')"></div>
      </div>
      <div class="carousel-item h-100">
        <div class="sv-hero-slide" style="background-image:url('{{ asset('virtualtour/images/bg_3.jpeg') }}')"></div>
      </div>
    </div>
  </div>
  <div class="sv-hero-overlay"></div>
  <div class="sv-hero-content">
    <div class="container">
      <div class="row">
        <div class="col-lg-7" data-aos="fade-right" data-aos-duration="800">
          <div class="sv-live-badge">
            <span class="sv-live-dot"></span> Tour Virtual 360°
          </div>
          <h1>¿Tienes un vehículo<br>que <span>vender</span>?</h1>
          <p class="lead mt-3 mb-4">
            Te ayudamos a potenciarlo con un Tour Virtual 360° que captura compradores reales — desde cualquier lugar, a cualquier hora.
          </p>
          <a href="#formulario" class="sv-btn-gold mr-3">
            <i class="fas fa-car mr-2"></i>Quiero vender mi vehículo
          </a>
          <a href="#demo-tour" class="sv-btn-outline mt-3 mt-sm-0">
            <i class="fas fa-vr-cardboard mr-2"></i>Ver demo del Tour
          </a>
        </div>
      </div>
    </div>
  </div>
  <a href="#stats" style="position:absolute;bottom:30px;left:50%;transform:translateX(-50%);z-index:3;color:rgba(255,255,255,.5);font-size:1.6rem;animation:sv-bounce 2s infinite">
    <i class="ion-ios-arrow-round-down"></i>
  </a>
</section>

{{-- ===== S2: STATS BAR ===== --}}
<section class="sv-stats" id="stats">
  <div class="container">
    <div class="row align-items-center text-center">
      <div class="col-12 col-md-4 mb-4 mb-md-0" data-aos="fade-up">
        <div class="sv-stat-num"><span data-count="360">0</span>°</div>
        <div class="sv-stat-label">Experiencia inmersiva total</div>
      </div>
      <div class="col-1 d-none d-md-flex justify-content-center">
        <div class="sv-stat-divider"></div>
      </div>
      <div class="col-12 col-md-3 mb-4 mb-md-0" data-aos="fade-up" data-aos-delay="100">
        <div class="sv-stat-num"><span data-count="3">0</span><span style="color:#fff;font-size:2.5rem">×</span></div>
        <div class="sv-stat-label">Más interés de compradores</div>
      </div>
      <div class="col-1 d-none d-md-flex justify-content-center">
        <div class="sv-stat-divider"></div>
      </div>
      <div class="col-12 col-md-3" data-aos="fade-up" data-aos-delay="200">
        <div class="sv-stat-num" style="font-size:3rem">24/7</div>
        <div class="sv-stat-label">Disponible siempre</div>
      </div>
    </div>
  </div>
</section>

{{-- RESTO COMING --}}
</div>
@endsection
