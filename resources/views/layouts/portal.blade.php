<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    {{-- SEO::BEGIN --}}
    @include('partials.portal.seo')
    {{-- SEO::END --}}

    {{-- HEAD::BEGIN --}}
    @include('partials.portal.head')
    @stack('styles')
    {{-- HEAD::END --}}
</head>

<body class="homepage4-body">
    {{-- PRELOAD::BEGIN --}}
    @include('partials.portal.preload')
    {{-- PRELOAD::END --}}

    {{-- HEADER::BEGIN --}}
    @include('partials.portal.header')
    {{-- HEADER::END --}}

    {{-- CONTENT::BEGIN --}}
    @yield('content')
    {{-- CONTENT::END --}}

    {{-- FOOTER::BEGIN --}}
    @include('partials.portal.footer')
    {{-- FOOTER::END --}}

    {{-- JS::BEGIN --}}
    {{-- sweer alert --}}
    @include('sweetalert::alert')
    {{-- sweer alert --}}
    @include('partials.portal.js')
    @stack('scripts')
    {{-- JS::END --}}
</body>

</html>
