<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>@yield('title', 'Virtual Tour')</title>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('virtualtour/css/style.css') }}" />

    @stack('styles')
</head>

<body>

    @include('frontend.frontnavbar')

    @yield('content')

    @include('frontend.footer')

    <!-- Scripts -->
    <script src="{{ asset('virtualtour/js/jquery.min.js') }}"></script>
    <script src="{{ asset('virtualtour/js/jquery-migrate-3.0.1.min.js') }}"></script>
    <script src="{{ asset('virtualtour/js/popper.min.js') }}"></script>
    <script src="{{ asset('virtualtour/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('virtualtour/js/jquery.easing.1.3.js') }}"></script>
    <script src="{{ asset('virtualtour/js/jquery.waypoints.min.js') }}"></script>
    <script src="{{ asset('virtualtour/js/jquery.stellar.min.js') }}"></script>
    <script src="{{ asset('virtualtour/js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('virtualtour/js/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('virtualtour/js/aos.js') }}"></script>
    <script src="{{ asset('virtualtour/js/jquery.animateNumber.min.js') }}"></script>
    <script src="{{ asset('virtualtour/js/bootstrap-datepicker.js') }}"></script>
    <script src="{{ asset('virtualtour/js/jquery.timepicker.min.js') }}"></script>
    <script src="{{ asset('virtualtour/js/scrollax.min.js') }}"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false"></script>
    <script src="{{ asset('virtualtour/js/google-map.js') }}"></script>
    <script src="{{ asset('virtualtour/js/main.js') }}"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="{{ asset('js/favorites.js') }}"></script>

    @stack('script')
    @stack('scripts')

</body>

</html>
