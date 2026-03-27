<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title> @yield('title') </title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" type="image/png" href="{{ asset('img/UnsoedIcon.png') }}">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('virtualtour/css/open-iconic-bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('virtualtour/css/animate.css') }}" />
    <link rel="stylesheet" href="{{ asset('virtualtour/css/owl.carousel.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('virtualtour/css/owl.theme.default.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('virtualtour/css/magnific-popup.css') }}" />
    <link rel="stylesheet" href="{{ asset('virtualtour/css/aos.css') }}" />
    <link rel="stylesheet" href="{{ asset('virtualtour/css/ionicons.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('virtualtour/css/bootstrap-datepicker.css') }}" />
    <link rel="stylesheet" href="{{ asset('virtualtour/css/jquery.timepicker.css') }}" />
    <link rel="stylesheet" href="{{ asset('virtualtour/css/flaticon.css') }}" />
    <link rel="stylesheet" href="{{ asset('virtualtour/css/icomoon.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{ asset('virtualtour/css/style.css') }}" />

    <style>
        /* Navbar ajustes globales para asegurar logo visible */
        .ftco_navbar .navbar-brand .logo {
            max-height: 50px;
            width: auto;
            display: block;
        }

        /* Para páginas con navbar fixed/scrolled */
        .ftco_navbar.scrolled {
            background: #fff !important;
        }

        .ftco_navbar.scrolled .navbar-nav > .nav-item > .nav-link {
            color: #000 !important;
        }

        .ftco_navbar.scrolled .navbar-nav > .nav-item > .nav-link:hover,
        .ftco_navbar.scrolled .navbar-nav > .nav-item.active > .nav-link {
            color: #c2ac1f !important;
        }
    </style>

    @stack('styles')
</head>
