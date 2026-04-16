@extends('frontend.front')
@section('title', 'Vende tu Vehículo con Tour Virtual 360°')
@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css">
    <style>
        .sv-page {
            font-family: 'Poppins', sans-serif;
        }

        .sv-gold {
            color: #c2ac1f;
        }

        /* HERO */
        .sv-hero {
            position: relative;
            height: 100vh;
            min-height: 620px;
            overflow: hidden;
        }

        .sv-hero-slide {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
        }

        .sv-hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(0, 0, 0, .75), rgba(0, 0, 0, .45));
            z-index: 1;
        }

        .sv-hero-content {
            position: absolute;
            inset: 0;
            z-index: 2;
            display: flex;
            align-items: center;
        }

        /* Per-slide text overlay */
        .sv-slide-caption {
            position: absolute;
            bottom: 90px;
            left: 0;
            right: 0;
            text-align: center;
            z-index: 3;
            pointer-events: none;
        }

        .sv-slide-tag {
            display: inline-block;
            background: rgba(194, 172, 31, .18);
            border: 1px solid rgba(194, 172, 31, .5);
            color: #c2ac1f;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            padding: 5px 16px;
            border-radius: 50px;
            margin-bottom: 10px;
            opacity: 0;
            transform: translateY(14px);
            transition: opacity .6s ease .1s, transform .6s ease .1s;
        }

        .sv-slide-sub {
            font-size: clamp(.85rem, 1.5vw, 1.05rem);
            color: rgba(255, 255, 255, .75);
            font-weight: 300;
            letter-spacing: .5px;
            opacity: 0;
            transform: translateY(14px);
            transition: opacity .6s ease .3s, transform .6s ease .3s;
        }

        .carousel-item.active .sv-slide-tag,
        .carousel-item.active .sv-slide-sub {
            opacity: 1;
            transform: translateY(0);
        }

        .sv-hero h1 {
            font-size: clamp(2rem, 5vw, 3.8rem);
            font-weight: 800;
            line-height: 1.15;
            color: #fff;
        }

        .sv-hero h1 span {
            color: #c2ac1f;
        }

        .sv-hero p.lead {
            font-size: clamp(1rem, 2vw, 1.2rem);
            color: rgba(255, 255, 255, .85);
            font-weight: 300;
            max-width: 560px;
        }

        .sv-btn-gold {
            background: #c2ac1f;
            color: #000;
            font-weight: 700;
            border: none;
            padding: 14px 32px;
            border-radius: 50px;
            font-size: 1rem;
            transition: all .3s;
            text-decoration: none;
            display: inline-block;
        }

        .sv-btn-gold:hover {
            background: #a89318;
            color: #000;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(194, 172, 31, .4);
            text-decoration: none;
        }

        .sv-btn-outline {
            background: transparent;
            color: #fff;
            font-weight: 600;
            border: 2px solid rgba(255, 255, 255, .6);
            padding: 13px 30px;
            border-radius: 50px;
            font-size: 1rem;
            transition: all .3s;
            text-decoration: none;
            display: inline-block;
        }

        .sv-btn-outline:hover {
            border-color: #c2ac1f;
            color: #c2ac1f;
            transform: translateY(-2px);
            text-decoration: none;
        }

        .sv-live-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .15);
            backdrop-filter: blur(8px);
            padding: 8px 18px;
            border-radius: 50px;
            color: #fff;
            font-size: .85rem;
            font-weight: 500;
            margin-bottom: 24px;
        }

        .sv-live-dot {
            width: 8px;
            height: 8px;
            background: #ff3b30;
            border-radius: 50%;
            animation: sv-pulse 1.4s ease-in-out infinite;
        }

        @@keyframes sv-pulse {

            0%,
            100% {
                opacity: 1;
                transform: scale(1)
            }

            50% {
                opacity: .5;
                transform: scale(1.4)
            }
        }

        @@keyframes sv-bounce {

            0%,
            100% {
                transform: translateX(-50%) translateY(0)
            }

            50% {
                transform: translateX(-50%) translateY(8px)
            }
        }

        /* STATS */
        .sv-stats {
            background: #111;
            border-top: 1px solid rgba(255, 255, 255, .06);
            border-bottom: 1px solid rgba(255, 255, 255, .06);
            padding: 56px 0;
        }

        .sv-stat-num {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 900;
            color: #c2ac1f;
            line-height: 1;
        }

        .sv-stat-label {
            font-size: .95rem;
            color: rgba(255, 255, 255, .6);
            margin-top: 6px;
        }

        .sv-stat-divider {
            width: 1px;
            background: rgba(255, 255, 255, .1);
            height: 60px;
        }

        /* SECTION HEADERS */
        .sv-section-tag {
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #c2ac1f;
            margin-bottom: 12px;
        }

        .sv-section-title {
            font-size: clamp(1.8rem, 3.5vw, 2.6rem);
            font-weight: 800;
            line-height: 1.2;
            color: #1a1a1a;
        }

        .sv-section-title.light {
            color: #fff;
        }

        /* PROBLEM */
        .sv-pain-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 20px;
        }

        .sv-pain-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: .85rem;
            margin-top: 2px;
        }

        .sv-pain-icon.bad {
            background: #fee;
            color: #d00;
        }

        .sv-pain-icon.good {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .sv-phone-mockup {
            width: 260px;
            margin: 0 auto;
        }

        .sv-phone-frame {
            background: #1a1a1a;
            border-radius: 36px;
            padding: 14px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, .25), inset 0 0 0 2px rgba(255, 255, 255, .08);
        }

        .sv-phone-screen {
            background: #000;
            border-radius: 24px;
            overflow: hidden;
            aspect-ratio: 9/19;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 10px;
        }

        /* STEPS */
        .sv-step-card {
            background: #fff;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 4px 30px rgba(0, 0, 0, .07);
            transition: transform .3s, box-shadow .3s;
            height: 100%;
        }

        .sv-step-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, .12);
        }

        .sv-step-num {
            font-size: 5rem;
            font-weight: 900;
            color: rgba(194, 172, 31, .15);
            line-height: 1;
            margin-bottom: -10px;
        }

        .sv-step-icon {
            font-size: 2.2rem;
            color: #c2ac1f;
            margin-bottom: 16px;
        }

        .sv-step-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 10px;
        }

        .sv-step-desc {
            font-size: .92rem;
            color: #666;
            line-height: 1.6;
        }

        /* DEMO */
        .sv-demo {
            background: #0a0a0a;
            padding: 96px 0;
        }

        .sv-laptop-frame {
            background: #1e1e1e;
            border-radius: 16px 16px 8px 8px;
            padding: 12px 12px 0;
            box-shadow: 0 30px 80px rgba(0, 0, 0, .6);
            border: 1px solid rgba(255, 255, 255, .08);
        }

        .sv-laptop-bar {
            background: #2a2a2a;
            border-radius: 6px 6px 0 0;
            height: 28px;
            display: flex;
            align-items: center;
            padding: 0 12px;
            gap: 6px;
            margin-bottom: 8px;
        }

        .sv-laptop-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .sv-laptop-screen {
            background: #000;
            border-radius: 4px;
            overflow: hidden;
            aspect-ratio: 16/9;
            position: relative;
        }

        .sv-laptop-base {
            height: 16px;
            background: #252525;
            border-radius: 0 0 8px 8px;
            margin: 0 -12px;
        }

        .sv-demo-badge {
            background: rgba(255, 255, 255, .06);
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: 12px;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }

        .sv-demo-badge-icon {
            font-size: 1.3rem;
            color: #c2ac1f;
            flex-shrink: 0;
        }

        .sv-demo-badge-text strong {
            color: #fff;
            display: block;
            font-size: .92rem;
            font-weight: 600;
        }

        .sv-demo-badge-text span {
            color: rgba(255, 255, 255, .5);
            font-size: .8rem;
        }

        /* FEATURES */
        .sv-feat-card {
            background: #fafafa;
            border: 1px solid #f0f0f0;
            border-radius: 16px;
            padding: 32px 24px;
            text-align: center;
            transition: all .3s;
            height: 100%;
        }

        .sv-feat-card:hover {
            border-color: #c2ac1f;
            box-shadow: 0 8px 30px rgba(194, 172, 31, .15);
            transform: translateY(-4px);
        }

        .sv-feat-icon {
            font-size: 2.4rem;
            color: #c2ac1f;
            margin-bottom: 18px;
        }

        .sv-feat-title {
            font-size: 1rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .sv-feat-desc {
            font-size: .85rem;
            color: #888;
            line-height: 1.55;
        }

        /* TESTIMONIALS */
        .sv-testi-card {
            background: #fff;
            border-radius: 20px;
            padding: 36px 28px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .07);
            height: 100%;
        }

        .sv-testi-avatar {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: linear-gradient(135deg, #c2ac1f, #a89318);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            font-weight: 800;
            color: #000;
            flex-shrink: 0;
        }

        .sv-testi-stars {
            color: #c2ac1f;
            font-size: .9rem;
            margin-bottom: 14px;
        }

        .sv-testi-quote {
            font-size: .95rem;
            color: #444;
            line-height: 1.65;
            font-style: italic;
            margin-bottom: 20px;
        }

        .sv-testi-name {
            font-weight: 700;
            color: #1a1a1a;
            font-size: .92rem;
        }

        .sv-testi-city {
            color: #999;
            font-size: .82rem;
        }

        /* FORM */
        .sv-form-section {
            position: relative;
            padding: 96px 0;
        }

        .sv-form-bg {
            position: absolute;
            inset: 0;
            background-image: url('{{ asset('virtualtour/images/bg_2.jpg') }}');
            background-size: cover;
            background-position: center;
        }

        .sv-form-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, .82);
        }

        .sv-form-wrap {
            position: relative;
            z-index: 2;
        }

        .sv-form-card {
            background: rgba(255, 255, 255, .04);
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: 24px;
            padding: 48px 40px;
            backdrop-filter: blur(10px);
        }

        .sv-form-card .form-control {
            background: rgba(255, 255, 255, .07);
            border: 1px solid rgba(255, 255, 255, .12);
            color: #fff;
            border-radius: 12px;
            padding: 14px 18px;
            font-size: .95rem;
        }

        .sv-form-card .form-control::placeholder {
            color: rgba(255, 255, 255, .35);
        }

        .sv-form-card .form-control:focus {
            background: rgba(255, 255, 255, .1);
            border-color: #c2ac1f;
            color: #fff;
            box-shadow: 0 0 0 3px rgba(194, 172, 31, .15);
        }

        .sv-form-card label {
            color: rgba(255, 255, 255, .7);
            font-size: .85rem;
            font-weight: 500;
            margin-bottom: 6px;
        }

        /* QR */
        .sv-qr-section {
            background: #0d0d0d;
            padding: 96px 0;
        }

        .sv-poster-preview {
            background: linear-gradient(135deg, #1a1a1a, #111);
            border: 1px solid rgba(194, 172, 31, .3);
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .5);
            max-width: 320px;
            margin: 0 auto;
        }

        .sv-poster-logo {
            font-size: .75rem;
            letter-spacing: 3px;
            color: rgba(255, 255, 255, .4);
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .sv-poster-title {
            font-size: 1.1rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 4px;
        }

        .sv-poster-sub {
            font-size: .78rem;
            color: rgba(255, 255, 255, .5);
            margin-bottom: 24px;
        }

        .sv-poster-qr-wrap {
            background: #fff;
            border-radius: 12px;
            padding: 16px;
            display: inline-block;
        }

        .sv-poster-tagline {
            font-size: .72rem;
            color: rgba(255, 255, 255, .35);
            margin-top: 20px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .sv-qr-download-btn {
            background: #c2ac1f;
            color: #000;
            font-weight: 700;
            border: none;
            padding: 14px 32px;
            border-radius: 50px;
            font-size: .95rem;
            transition: all .3s;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .sv-qr-download-btn:hover {
            background: #a89318;
            transform: translateY(-2px);
        }

        @@media(max-width:767px) {
            .sv-hero h1 {
                font-size: 2rem;
            }

            .sv-stat-divider {
                display: none;
            }

            .sv-form-card {
                padding: 28px 18px;
            }

            .sv-phone-mockup {
                width: 200px;
                margin-top: 40px;
            }
        }

        /* ── Hotspot styles (identical to welcome.blade) ── */
        .hotspot-tooltip-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .hotspot-label {
            background-color: rgba(0,0,0,0.75);
            color: #fff;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 5px;
            white-space: nowrap;
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .hotspot-label-info  { background-color:rgba(52,152,219,.9); border:1px solid #2980b9; }
        .hotspot-label-scene { background-color:rgba(46,204,113,.9); border:1px solid #27ae60; }
        .circular-hotspot-img {
            width: 50px; height: 50px;
            object-fit: contain;
            cursor: pointer;
            transition: transform .2s ease;
        }
        .circular-hotspot-img:hover { transform: scale(1.1); }
        /* Floor projection hotspot (sin imagen personalizada) */
        .floor-hotspot {
            width: 58px; height: 58px;
            position: relative;
            cursor: pointer;
            transform: perspective(60px) rotateX(64deg);
        }
        @@media (max-width:1024px) {
            .floor-hotspot { width:46px; height:46px; transform:perspective(48px) rotateX(64deg); }
        }
        @@media (max-width:600px) {
            .floor-hotspot { width:36px; height:36px; transform:perspective(38px) rotateX(64deg); }
        }
        .floor-hotspot-inner {
            position: absolute; inset: 0;
            border-radius: 50%;
            border: 2.5px solid rgba(255,255,255,.92);
            box-shadow: 0 0 10px rgba(255,255,255,.65), 0 0 22px rgba(255,255,255,.25), inset 0 0 6px rgba(255,255,255,.08);
            animation: floor-pulse 2.6s ease-in-out infinite;
        }
        @@keyframes floor-pulse {
            0%,100% { transform:scale(1);    opacity:.82; }
            50%      { transform:scale(1.07); opacity:1;   }
        }
        .floor-hotspot:hover .floor-hotspot-inner {
            animation: none;
            transform: scale(1.1);
            opacity: 1;
            box-shadow: 0 0 16px rgba(255,255,255,.9), 0 0 32px rgba(255,255,255,.4), inset 0 0 8px rgba(255,255,255,.12);
        }
        .pnlm-hotspot.circular-hotspot {
            background: transparent !important;
            border: none !important;
            width: auto !important;
            height: auto !important;
        }
        .pnlm-load-box { display:none !important; }
    </style>
@endpush

@section('content')
    <div class="sv-page">

        {{-- ===== S1: HERO ===== --}}
        <section class="sv-hero">
            <div id="svHero" class="carousel slide carousel-fade h-100" data-ride="carousel" data-interval="5000">
                <div class="carousel-inner h-100">
                    <div class="carousel-item h-100 active">
                        <div class="sv-hero-slide"
                            style="background-image:url('{{ asset('virtualtour/images/bg_2.jpg') }}')"></div>
                        <div class="sv-slide-caption">
                            <div class="sv-slide-tag">Tour Virtual · 360°</div>
                            <div class="sv-slide-sub">Mostrá tu vehículo desde todos los ángulos, sin que el comprador tenga que moverse</div>
                        </div>
                    </div>
                    <div class="carousel-item h-100">
                        <div class="sv-hero-slide"
                            style="background-image:url('{{ asset('virtualtour/images/bg_1.jpeg') }}')"></div>
                        <div class="sv-slide-caption">
                            <div class="sv-slide-tag">Vendé más rápido</div>
                            <div class="sv-slide-sub">Los compradores deciden con los ojos — dales la experiencia inmersiva que convierte</div>
                        </div>
                    </div>
                    <div class="carousel-item h-100">
                        <div class="sv-hero-slide"
                            style="background-image:url('{{ asset('virtualtour/images/bg_3.jpeg') }}')"></div>
                        <div class="sv-slide-caption">
                            <div class="sv-slide-tag">Disponible 24/7</div>
                            <div class="sv-slide-sub">Tu vehículo se muestra solo, a cualquier hora, desde cualquier lugar del mundo</div>
                        </div>
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
                                Te ayudamos a potenciarlo con un Tour Virtual 360° que captura compradores reales — desde
                                cualquier lugar, a cualquier hora.
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
            <a href="#stats"
                style="position:absolute;bottom:30px;left:50%;transform:translateX(-50%);z-index:3;color:rgba(255,255,255,.5);font-size:1.6rem;animation:sv-bounce 2s infinite">
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
                        <div class="sv-stat-num"><span data-count="3">0</span><span
                                style="color:#fff;font-size:2.5rem">×</span></div>
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


        {{-- ===== S3: PROBLEMA / OPORTUNIDAD ===== --}}
        <section class="ftco-section bg-white" id="problema" style="padding:96px 0;">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-5 mb-lg-0" data-aos="fade-right">
                        <div class="sv-section-tag">El problema real</div>
                        <h2 class="sv-section-title mb-4">Los compradores modernos<br>deciden <span class="sv-gold">con los
                                ojos</span></h2>
                        <p class="text-muted mb-4" style="font-size:.97rem;line-height:1.7">
                            Publicar fotos planas ya no es suficiente. El comprador de hoy quiere explorar, sentir el
                            espacio y moverse dentro del vehículo — antes de siquiera llamarte.
                        </p>
                        <p
                            style="font-size:.78rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#999;margin-bottom:14px">
                            Sin Tour Virtual</p>
                        <div class="sv-pain-item">
                            <div class="sv-pain-icon bad"><i class="fas fa-times"></i></div>
                            <div><strong style="font-size:.92rem">Fotos estáticas</strong><br><span class="text-muted"
                                    style="font-size:.85rem">El comprador no puede explorar el interior real</span></div>
                        </div>
                        <div class="sv-pain-item">
                            <div class="sv-pain-icon bad"><i class="fas fa-times"></i></div>
                            <div><strong style="font-size:.92rem">Visitas innecesarias</strong><br><span class="text-muted"
                                    style="font-size:.85rem">Pierdes tiempo con curiosos que no van a comprar</span></div>
                        </div>
                        <div class="sv-pain-item mb-4">
                            <div class="sv-pain-icon bad"><i class="fas fa-times"></i></div>
                            <div><strong style="font-size:.92rem">Alcance limitado</strong><br><span class="text-muted"
                                    style="font-size:.85rem">Solo compradores cercanos en horario restringido</span></div>
                        </div>
                        <p
                            style="font-size:.78rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#c2ac1f;margin-bottom:14px">
                            Con Tour Virtual 360°</p>
                        <div class="sv-pain-item">
                            <div class="sv-pain-icon good"><i class="fas fa-check"></i></div>
                            <div><strong style="font-size:.92rem">Experiencia inmersiva</strong><br><span
                                    class="text-muted" style="font-size:.85rem">El comprador explora cada ángulo desde su
                                    celular</span></div>
                        </div>
                        <div class="sv-pain-item">
                            <div class="sv-pain-icon good"><i class="fas fa-check"></i></div>
                            <div><strong style="font-size:.92rem">Solo visitas calificadas</strong><br><span
                                    class="text-muted" style="font-size:.85rem">Quien llama ya vio el Tour y está listo
                                    para negociar</span></div>
                        </div>
                        <div class="sv-pain-item">
                            <div class="sv-pain-icon good"><i class="fas fa-check"></i></div>
                            <div><strong style="font-size:.92rem">Disponible 24/7 en todo el mundo</strong><br><span
                                    class="text-muted" style="font-size:.85rem">Tu vehículo se muestra solo, sin que estés
                                    presente</span></div>
                        </div>
                    </div>
                    <div class="col-lg-6 text-center" data-aos="fade-left" data-aos-delay="100">
                        <div class="sv-phone-mockup">
                            <div class="sv-phone-frame">
                                <div class="sv-phone-screen" style="min-height:420px;">
                                    @if ($demoImageUrl)
                                        <div id="sv-phone-pannellum" style="width:100%;height:100%;min-height:420px;"></div>
                                    @else
                                        <i class="fas fa-vr-cardboard" style="font-size:3rem;color:#c2ac1f"></i>
                                        <span style="color:rgba(255,255,255,.5);font-size:.8rem;letter-spacing:1px">TOUR VIRTUAL 360°</span>
                                        <div style="display:flex;gap:6px">
                                            <span style="width:6px;height:6px;border-radius:50%;background:#c2ac1f;"></span>
                                            <span style="width:6px;height:6px;border-radius:50%;background:rgba(255,255,255,.3);"></span>
                                            <span style="width:6px;height:6px;border-radius:50%;background:rgba(255,255,255,.3);"></span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <p class="text-muted mt-4" style="font-size:.85rem">Vista del Tour Virtual en dispositivo móvil
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===== S4: CÓMO FUNCIONA ===== --}}
        <section style="background:#f8f8f8;padding:96px 0;" id="como-funciona">
            <div class="container">
                <div class="row justify-content-center mb-5">
                    <div class="col-lg-7 text-center" data-aos="fade-up">
                        <div class="sv-section-tag">Proceso simple</div>
                        <h2 class="sv-section-title">Cómo funciona</h2>
                        <p class="text-muted mt-3" style="font-size:.95rem">En 3 pasos tenés tu Tour Virtual publicado y
                            listo para atraer compradores</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="0">
                        <div class="sv-step-card">
                            <div class="sv-step-num">01</div>
                            <div class="sv-step-icon"><i class="fas fa-camera"></i></div>
                            <div class="sv-step-title">Registrá tu vehículo</div>
                            <div class="sv-step-desc">Completá el formulario con los datos de tu vehículo. Nos enviás fotos
                                o las tomamos nosotros — vos decidís.</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="sv-step-card">
                            <div class="sv-step-num">02</div>
                            <div class="sv-step-icon"><i class="fas fa-magic"></i></div>
                            <div class="sv-step-title">Creamos el Tour</div>
                            <div class="sv-step-desc">Nuestro equipo produce y publica tu Tour Virtual 360° profesional.
                                Listo en menos de 48 horas.</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                        <div class="sv-step-card">
                            <div class="sv-step-num">03</div>
                            <div class="sv-step-icon"><i class="fas fa-handshake"></i></div>
                            <div class="sv-step-title">Vendés más rápido</div>
                            <div class="sv-step-desc">Compradores visitan tu Tour desde cualquier lugar. Vos solo atendés a
                                quienes ya están convencidos.</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        {{-- ===== S5: DEMO TOUR ===== --}}
        <section class="sv-demo" id="demo-tour">
            <div class="container">
                <div class="row justify-content-center mb-5">
                    <div class="col-lg-7 text-center" data-aos="fade-up">
                        <div class="sv-section-tag">Demo en vivo</div>
                        <h2 class="sv-section-title light">Probá el Tour Virtual ahora</h2>
                        <p style="color:rgba(255,255,255,.55);font-size:.95rem;margin-top:12px">Arrastrá, hacé zoom y
                            explorá el espacio 360° — así experimenta tu comprador</p>
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-lg-8 mb-5 mb-lg-0" data-aos="fade-right">
                        <div class="sv-laptop-frame">
                            <div class="sv-laptop-bar">
                                <div class="sv-laptop-dot" style="background:#ff5f57"></div>
                                <div class="sv-laptop-dot" style="background:#ffbd2e"></div>
                                <div class="sv-laptop-dot" style="background:#28ca42"></div>
                                <div
                                    style="flex:1;background:#333;border-radius:4px;height:14px;margin-left:8px;padding:0 8px;display:flex;align-items:center;">
                                    <span
                                        style="color:rgba(255,255,255,.3);font-size:.65rem">space360.cr/tour/vehiculo</span>
                                </div>
                            </div>
                            <div class="sv-laptop-screen">
                                @if ($scenes->isNotEmpty())
                                    <div id="sv-pannellum" style="width:100%;height:100%;"></div>
                                @else
                                    <div
                                        style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:16px;background:linear-gradient(135deg,#111,#1e1e1e);">
                                        <i class="fas fa-vr-cardboard" style="font-size:4rem;color:#c2ac1f;"></i>
                                        <div style="text-align:center;">
                                            <p style="color:#fff;font-weight:600;margin-bottom:4px;">Tour Virtual 360°</p>
                                            <p style="color:rgba(255,255,255,.4);font-size:.82rem;">Vista interactiva —
                                                arrastrar para explorar</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="sv-laptop-base"></div>
                        </div>
                    </div>
                    <div class="col-lg-4" data-aos="fade-left" data-aos-delay="100">
                        <div class="sv-demo-badge">
                            <div class="sv-demo-badge-icon"><i class="fas fa-hand-point-up"></i></div>
                            <div class="sv-demo-badge-text"><strong>Arrastrá para explorar</strong><span>Vista 360°
                                    interactiva</span></div>
                        </div>
                        <div class="sv-demo-badge">
                            <div class="sv-demo-badge-icon"><i class="fas fa-search-plus"></i></div>
                            <div class="sv-demo-badge-text"><strong>Zoom de precisión</strong><span>Detalle en cada
                                    rincón</span></div>
                        </div>
                        <div class="sv-demo-badge">
                            <div class="sv-demo-badge-icon"><i class="fas fa-map-marker-alt"></i></div>
                            <div class="sv-demo-badge-text"><strong>Hotspots informativos</strong><span>Puntos de interés
                                    clicables</span></div>
                        </div>
                        <div class="sv-demo-badge">
                            <div class="sv-demo-badge-icon"><i class="fas fa-mobile-alt"></i></div>
                            <div class="sv-demo-badge-text"><strong>100% móvil</strong><span>Funciona en cualquier
                                    dispositivo</span></div>
                        </div>
                        <div class="mt-4">
                            <a href="#formulario" class="sv-btn-gold d-block text-center">Quiero mi Tour Virtual</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===== S6: FEATURE CARDS ===== --}}
        <section style="background:#fff;padding:96px 0;" id="ventajas">
            <div class="container">
                <div class="row justify-content-center mb-5">
                    <div class="col-lg-7 text-center" data-aos="fade-up">
                        <div class="sv-section-tag">Por qué elegirnos</div>
                        <h2 class="sv-section-title">Todo lo que incluye tu Tour</h2>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 col-sm-6 mb-4" data-aos="fade-up" data-aos-delay="0">
                        <div class="sv-feat-card">
                            <div class="sv-feat-icon"><i class="fas fa-clock"></i></div>
                            <div class="sv-feat-title">Disponible 24/7</div>
                            <div class="sv-feat-desc">Tu vehículo se muestra solo, a cualquier hora, sin que tengas que
                                estar presente.</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 mb-4" data-aos="fade-up" data-aos-delay="80">
                        <div class="sv-feat-card">
                            <div class="sv-feat-icon"><i class="fab fa-whatsapp"></i></div>
                            <div class="sv-feat-title">Compartir por QR / WhatsApp</div>
                            <div class="sv-feat-desc">Un link y un QR únicos para compartir en WhatsApp, Instagram o donde
                                quieras.</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 mb-4" data-aos="fade-up" data-aos-delay="160">
                        <div class="sv-feat-card">
                            <div class="sv-feat-icon"><i class="fas fa-ban"></i></div>
                            <div class="sv-feat-title">Sin visitas inútiles</div>
                            <div class="sv-feat-desc">Quien contacta ya exploró el Tour y está genuinamente interesado.
                                Ahorrás tiempo.</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 mb-4" data-aos="fade-up" data-aos-delay="0">
                        <div class="sv-feat-card">
                            <div class="sv-feat-icon"><i class="fas fa-star"></i></div>
                            <div class="sv-feat-title">Presentación profesional</div>
                            <div class="sv-feat-desc">Tu vehículo luce premium. Una presentación de calidad genera mayor
                                confianza y mejor precio.</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 mb-4" data-aos="fade-up" data-aos-delay="80">
                        <div class="sv-feat-card">
                            <div class="sv-feat-icon"><i class="fas fa-globe"></i></div>
                            <div class="sv-feat-title">Alcance ilimitado</div>
                            <div class="sv-feat-desc">Llegá a compradores de cualquier zona o país. Tu vitrina virtual no
                                tiene fronteras.</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 mb-4" data-aos="fade-up" data-aos-delay="160">
                        <div class="sv-feat-card">
                            <div class="sv-feat-icon"><i class="fas fa-mobile-alt"></i></div>
                            <div class="sv-feat-title">Cualquier dispositivo</div>
                            <div class="sv-feat-desc">Celular, tablet o PC — el Tour se adapta perfectamente a cualquier
                                pantalla.</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        {{-- ===== S7: TESTIMONIOS ===== --}}
        <section style="background:#f5f5f5;padding:96px 0;" id="testimonios">
            <div class="container">
                <div class="row justify-content-center mb-5">
                    <div class="col-lg-6 text-center" data-aos="fade-up">
                        <div class="sv-section-tag">Lo que dicen nuestros clientes</div>
                        <h2 class="sv-section-title">Resultados reales</h2>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="0">
                        <div class="sv-testi-card">
                            <div class="sv-testi-stars">★★★★★</div>
                            <p class="sv-testi-quote">"Publiqué mi Corolla con el Tour Virtual y en menos de una semana
                                tuve 3 compradores serios. Vendí al precio que pedía, sin rebajas."</p>
                            <div class="d-flex align-items-center" style="gap:12px;">
                                <div class="sv-testi-avatar">M</div>
                                <div>
                                    <div class="sv-testi-name">Marcos V.</div>
                                    <div class="sv-testi-city">San José, Costa Rica</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="sv-testi-card">
                            <div class="sv-testi-stars">★★★★★</div>
                            <p class="sv-testi-quote">"La gente llegaba ya convencida de comprar. No tuve que perder tiempo
                                explicando el estado del carro — el Tour lo mostraba todo."</p>
                            <div class="d-flex align-items-center" style="gap:12px;">
                                <div class="sv-testi-avatar">A</div>
                                <div>
                                    <div class="sv-testi-name">Andrea L.</div>
                                    <div class="sv-testi-city">Heredia, Costa Rica</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                        <div class="sv-testi-card">
                            <div class="sv-testi-stars">★★★★★</div>
                            <p class="sv-testi-quote">"Compartí el link en un grupo de WhatsApp y en 48 horas ya tenía el
                                vehículo vendido. El QR en el parabrisas fue una idea genial."</p>
                            <div class="d-flex align-items-center" style="gap:12px;">
                                <div class="sv-testi-avatar">R</div>
                                <div>
                                    <div class="sv-testi-name">Rodrigo S.</div>
                                    <div class="sv-testi-city">Cartago, Costa Rica</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===== S8: FORMULARIO ===== --}}
        <section class="sv-form-section" id="formulario">
            <div class="sv-form-bg"></div>
            <div class="sv-form-overlay"></div>
            <div class="sv-form-wrap container">
                <div class="row justify-content-center mb-5">
                    <div class="col-lg-7 text-center" data-aos="fade-up">
                        <div class="sv-section-tag">Empezá ahora</div>
                        <h2 class="sv-section-title light">¿Listo para vender tu vehículo?</h2>
                        <p style="color:rgba(255,255,255,.55);margin-top:12px;font-size:.95rem">Completá el formulario y te
                            contactamos en menos de 24 horas</p>
                    </div>
                </div>
                <div class="row justify-content-center">
                    <div class="col-lg-7" data-aos="fade-up" data-aos-delay="100">
                        <div class="sv-form-card">
                            <form id="sv-contact-form">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>Nombre completo</label>
                                        <input type="text" name="name" class="form-control"
                                            placeholder="Tu nombre" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Teléfono / WhatsApp</label>
                                        <input type="tel" name="phone" class="form-control"
                                            placeholder="+506 8888-0000" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>Correo electrónico</label>
                                    <input type="email" name="email" class="form-control"
                                        placeholder="correo@ejemplo.com" required>
                                </div>
                                <div class="mb-4">
                                    <label>Descripción del vehículo</label>
                                    <textarea name="vehicle_description" class="form-control" rows="4"
                                        placeholder="Marca, modelo, año, estado general..." required></textarea>
                                </div>
                                <button type="submit" class="sv-btn-gold w-100" style="font-size:1rem;padding:16px;">
                                    <i class="fas fa-paper-plane mr-2"></i>Enviar solicitud
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        {{-- ===== S9: QR CODE ===== --}}
        <section class="sv-qr-section" id="qr-code">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-5 mb-lg-0" data-aos="fade-right">
                        <div class="sv-section-tag">Tu cartel digital</div>
                        <h2 class="sv-section-title light mb-4">Llevá tu Tour Virtual<br>al mundo físico</h2>
                        <p style="color:rgba(255,255,255,.55);font-size:.95rem;line-height:1.7;margin-bottom:32px;">
                            Generamos un código QR único para esta página. Imprimilo en un cartel y ponelo en el parabrisas
                            de tu vehículo — cualquier interesado escanea y entra directo al Tour Virtual.
                        </p>
                        <ul class="list-unstyled mb-4">
                            <li class="d-flex align-items-center mb-3" style="color:rgba(255,255,255,.7);">
                                <i class="fas fa-check-circle mr-3" style="color:#c2ac1f;font-size:1.1rem;"></i>
                                Generado automáticamente para esta página
                            </li>
                            <li class="d-flex align-items-center mb-3" style="color:rgba(255,255,255,.7);">
                                <i class="fas fa-check-circle mr-3" style="color:#c2ac1f;font-size:1.1rem;"></i>
                                Descargable como PNG para imprimir
                            </li>
                            <li class="d-flex align-items-center" style="color:rgba(255,255,255,.7);">
                                <i class="fas fa-check-circle mr-3" style="color:#c2ac1f;font-size:1.1rem;"></i>
                                Vista previa de cartel incluida
                            </li>
                        </ul>
                        <button class="sv-qr-download-btn" onclick="downloadQR()">
                            <i class="fas fa-download"></i> Descargar QR como PNG
                        </button>
                    </div>
                    <div class="col-lg-6 text-center" data-aos="fade-left" data-aos-delay="100">
                        <div class="sv-poster-preview" id="sv-poster">
                            <div class="sv-poster-logo">Space 360 &middot; Tour Virtual</div>
                            <div class="sv-poster-title">¡Escanéame!</div>
                            <div class="sv-poster-sub">Mirá el Tour Virtual 360° de este vehículo</div>
                            <div class="sv-poster-qr-wrap">
                                <div id="sv-qr-canvas"></div>
                            </div>
                            <div class="sv-poster-tagline">Powered by Space 360</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>{{-- /sv-page --}}
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js"></script>
    <script>
        // Counter animation
        (function() {
            var done = false;
            var obs = new IntersectionObserver(function(entries) {
                if (done) return;
                entries.forEach(function(e) {
                    if (e.isIntersecting) {
                        done = true;
                        document.querySelectorAll('[data-count]').forEach(function(el) {
                            var target = parseInt(el.getAttribute('data-count'));
                            var cur = 0;
                            var step = Math.ceil(target / 60);
                            var t = setInterval(function() {
                                cur = Math.min(cur + step, target);
                                el.textContent = cur;
                                if (cur >= target) clearInterval(t);
                            }, 20);
                        });
                    }
                });
            }, {
                threshold: 0.3
            });
            var el = document.getElementById('stats');
            if (el) obs.observe(el);
        })();

        // QR Code
        window.addEventListener('load', function() {
            var el = document.getElementById('sv-qr-canvas');
            if (el) new QRCode(el, {
                text: window.location.href,
                width: 180,
                height: 180,
                colorDark: '#000000',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H
            });
        });

        // Download QR poster
        function downloadQR() {
            var poster = document.getElementById('sv-poster');
            if (!poster) return;
            html2canvas(poster, {
                backgroundColor: '#1a1a1a',
                scale: 2
            }).then(function(canvas) {
                var a = document.createElement('a');
                a.download = 'qr-tour-virtual.png';
                a.href = canvas.toDataURL('image/png');
                a.click();
            });
        }

        // ── Hotspot tooltip function (same as welcome.blade) ──
        function hotspotTooltipFunction(hotSpotDiv, args) {
            var imageUrl   = args.imageUrl || null;
            var pitchScale = 1;
            if (!imageUrl && args.pitch !== undefined) {
                var absPitch = Math.abs(args.pitch);
                var tanRef   = Math.tan(50 * Math.PI / 180);
                pitchScale   = Math.min(1.0, Math.max(0.42, Math.tan(absPitch * Math.PI / 180) / tanRef));
            }
            var container = document.createElement('div');
            container.classList.add('hotspot-tooltip-container');
            // No label text — displayText is intentionally omitted in the demo viewer
            if (imageUrl) {
                var img = document.createElement('img');
                img.classList.add('circular-hotspot-img');
                img.src = imageUrl;
                img.alt = 'hotspot';
                container.appendChild(img);
            } else {
                var scaleWrapper = document.createElement('div');
                scaleWrapper.style.cssText = 'display:inline-block;transform:scale(' + pitchScale.toFixed(3) + ');transform-origin:center bottom;';
                var floor = document.createElement('div');
                floor.classList.add('floor-hotspot');
                var inner = document.createElement('div');
                inner.classList.add('floor-hotspot-inner');
                floor.appendChild(inner);
                scaleWrapper.appendChild(floor);
                container.appendChild(scaleWrapper);
            }
            hotSpotDiv.appendChild(container);
        }
        window.hotspotTooltipFunction = hotspotTooltipFunction;

        function onHotspotClick(e, args) {
            if (args && args.targetSceneId && window._svViewer) {
                window._svViewer.loadScene(args.targetSceneId);
            }
        }
        window.onHotspotClick = onHotspotClick;

        @php
            // Build Pannellum config in blade exactly like welcome.blade.php
            $jsonOptions = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
            // Primera escena: la marcada como principal (status=1), igual que SceneController
            $fscene = $scenes->firstWhere('status', '1') ?? $scenes->firstWhere('type', '!=', 'video') ?? $scenes->first();

            $pannellumDefault = [
                'firstScene'        => $fscene ? (string) $fscene->id : '',
                'hfov'              => 100,
                'minHfov'           => 50,
                'maxHfov'           => 120,
                'autoLoad'          => true,
                'sceneFadeDuration' => 1000,
                'autoRotate'        => -2,
                'showControls'      => true,
                'mouseZoom'         => true,
            ];

            $scenesConfig = [];
            foreach ($scenes as $scene) {
                $hotspotsForScene = [];
                foreach ($hotspots->where('sourceScene', $scene->id) as $hotspot) {
                    $hs = [
                        'pitch'             => (float) $hotspot->pitch,
                        'yaw'               => (float) $hotspot->yaw,
                        'cssClass'          => 'circular-hotspot',
                        'type'              => 'custom',
                        'createTooltipFunc' => 'hotspotTooltipFunction',
                        'createTooltipArgs' => [
                            'imageUrl'    => !empty($hotspot->image) ? route('file', $hotspot->image) : null,
                            'displayText' => null,
                            'hotspotType' => $hotspot->type,
                            'pitch'       => (float) $hotspot->pitch,
                        ],
                        'text' => $hotspot->info,
                    ];
                    if ($hotspot->type === 'scene' && $hotspot->targetScene) {
                        $hs['clickHandlerFunc'] = 'onHotspotClick';
                        $clickArgs = [
                            'targetSceneId' => (string) $hotspot->targetScene,
                            'yaw'           => (float) $hotspot->yaw,
                            'pitch'         => (float) $hotspot->pitch,
                        ];
                        if ($hotspot->target_yaw   !== null) $clickArgs['targetYaw']   = (float) $hotspot->target_yaw;
                        if ($hotspot->target_pitch !== null) $clickArgs['targetPitch'] = (float) $hotspot->target_pitch;
                        $hs['clickHandlerArgs'] = $clickArgs;
                    }
                    $hotspotsForScene[] = $hs;
                }
                $scenesConfig[(string) $scene->id] = [
                    'title'    => $scene->title,
                    'hfov'     => (float) ($scene->hfov ?? 100),
                    'pitch'    => (float) ($scene->pitch ?? 0),
                    'yaw'      => (float) ($scene->yaw ?? 0),
                    'type'     => $scene->type ?? 'equirectangular',
                    'panorama' => $scene->image
                        ? route('file', $scene->image)
                        : url('images/producto-sin-imagen.PNG'),
                    'hotSpots' => $hotspotsForScene,
                ];
            }
        @endphp

        @if (!empty($scenesConfig))
            window.addEventListener('load', function() {
                var pannellumConfig = {
                    default: @json($pannellumDefault, $jsonOptions),
                    scenes:  @json($scenesConfig,  $jsonOptions)
                };
                // Reconectar strings → funciones reales (igual que welcome.blade)
                Object.keys(pannellumConfig.scenes || {}).forEach(function(sceneId) {
                    (pannellumConfig.scenes[sceneId].hotSpots || []).forEach(function(h) {
                        if (typeof h.createTooltipFunc === 'string') {
                            var fn = window[h.createTooltipFunc];
                            if (typeof fn === 'function') h.createTooltipFunc = fn;
                            else delete h.createTooltipFunc;
                        }
                        if (typeof h.clickHandlerFunc === 'string') {
                            var fn = window[h.clickHandlerFunc];
                            if (typeof fn === 'function') h.clickHandlerFunc = fn;
                            else delete h.clickHandlerFunc;
                        }
                    });
                });
                window._svViewer = pannellum.viewer('sv-pannellum', pannellumConfig);
                @if ($demoImageUrl)
                pannellum.viewer('sv-phone-pannellum', {
                    type: 'equirectangular',
                    panorama: '{{ $demoImageUrl }}',
                    autoLoad: true,
                    autoRotate: -3,
                    showControls: false,
                    mouseZoom: false,
                    pitch: -10
                });
                @endif
            });
        @endif

        // Form AJAX
        document.getElementById('sv-contact-form').addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = this.querySelector('button[type=submit]');
            var orig = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...';
            btn.disabled = true;
            fetch('{{ route('sell-vehicle.contact') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    },
                    body: new FormData(this)
                })
                .then(function(r) {
                    return r.json();
                })
                .then(function(res) {
                    if (res.success) {
                        swal('¡Enviado!', res.message, 'success');
                        document.getElementById('sv-contact-form').reset();
                    } else {
                        swal('Error', 'Ocurrió un problema. Intentá nuevamente.', 'error');
                    }
                })
                .catch(function() {
                    swal('Error', 'No se pudo conectar. Intentá nuevamente.', 'error');
                })
                .finally(function() {
                    btn.innerHTML = orig;
                    btn.disabled = false;
                });
        });
    </script>
@endpush
