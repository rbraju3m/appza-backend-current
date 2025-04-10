<!doctype html>
<html lang="{{app()->getLocale()}}">
<head>

    <!-- meta item -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ __('messages.LazyCoder') }}">
    <meta name="author" content="{{ __('messages.LazyCoder') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('Fav.svg') }}" type="image/x-icon"/>

    <title>{{ __('messages.LazyCoder') }}</title>

    <meta name="theme-color" content="#7952b3">
    <!-- Bootstrap core CSS -->
    @include('layouts.css')
    @stack('CustomStyle')

    <style>
        .btn-outline-primary:hover .fas,
        .btn-outline-danger:hover .fas {
            color: #fff;
        }
        .sticky-navbar {
            position: fixed;
            top: 0;
            width: 87%;
            z-index: 1030; /* Make sure it's above other elements */
        }
    </style>
</head>
<body id="{{app()->getLocale()}}" class="app">



{{--<div class="sidebar">
    <div class="sidebar-inner">
        <!-- logo part -->
    @include('layouts.logo')
    <!-- menu part -->
        @include('layouts.nav')
    </div>
</div>--}}

@include('layouts.nav')


<div class="container-wide" style="margin-top: 70px">
    <!-- top nav part -->
@include('layouts.topnav')

<!-- body part -->
@yield('body')

<!-- footer part -->
@include('layouts.footer')
<!-- js part -->
    @include('layouts.js')

    @yield('footer.scripts')

</div>
</body>
</html>
