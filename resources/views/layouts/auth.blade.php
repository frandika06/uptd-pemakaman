<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    {{-- SEO::BEGIN --}}
    @include('partials.admin.seo')
    {{-- SEO::END --}}

    {{-- HEAD::BEGIN --}}
    @include('partials.admin.head')
    @stack('styles')
    {{-- HEAD::END --}}
</head>

<body id="kt_body" class="app-blank">
    {{-- begin::Theme mode setup on page load --}}
    <script>
        var defaultThemeMode = "light";
        var themeMode;
        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
            } else {
                if (localStorage.getItem("data-bs-theme") !== null) {
                    themeMode = localStorage.getItem("data-bs-theme");
                } else {
                    themeMode = defaultThemeMode;
                }
            }
            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }
            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }
    </script>
    {{-- end::Theme mode setup on page load --}}

    <!--begin::Content-->
    <div class="d-flex flex-column flex-root" id="kt_app_root">
        @yield('content')
    </div>
    <!--end::Content-->

    {{-- JS::BEGIN --}}
    @include('partials.admin.js')
    @stack('scripts')
    {{-- JS::END --}}
</body>

</html>
