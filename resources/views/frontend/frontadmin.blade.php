<!doctype html>
<html class="no-js" lang="en">

@include('admin.layouts.front')

<body class="g-sidenav-show  bg-gray-200">

    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
       
        <div>
            @include('frontend.frontnavbar')            
            @yield('content')
        </div>
    </main>    

   
    <script async defer src="https://buttons.github.io/buttons.js"></script>
   
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

    
    @include('admin.layouts.scripts')
    @stack('script')

</body>


</html>
