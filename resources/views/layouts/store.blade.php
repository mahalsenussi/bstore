<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? 'B-Store' }}</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cookie&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap"
    rel="stylesheet">

    <!-- Css Styles -->
    <link rel="stylesheet" href="{{ asset('theme/css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('theme/css/font-awesome.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('theme/css/elegant-icons.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('theme/css/jquery-ui.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('theme/css/magnific-popup.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('theme/css/owl.carousel.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('theme/css/slicknav.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('theme/css/style.css') }}" type="text/css">
    @stack('styles')
</head>

<body>
    <!-- Page Preloder -->
    <div id="preloder">
        <div class="loader"></div>
    </div>

    @include('store.partials.offcanvas-menu')
    @include('store.partials.header')

    @yield('breadcrumb')

    @yield('content')

    @include('store.partials.instagram')
    @include('store.partials.footer')
    @include('store.partials.search-model')

    <!-- Js Plugins -->
    <script src="{{ asset('theme/js/jquery-3.3.1.min.js') }}"></script>
    <script src="{{ asset('theme/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('theme/js/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('theme/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('theme/js/jquery.countdown.min.js') }}"></script>
    <script src="{{ asset('theme/js/jquery.slicknav.js') }}"></script>
    <script src="{{ asset('theme/js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('theme/js/jquery.nicescroll.min.js') }}"></script>
    <script src="{{ asset('theme/js/main.js') }}"></script>
    @stack('scripts')
</body>

</html>