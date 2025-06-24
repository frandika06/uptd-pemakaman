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

<body id="kt_app_body" data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-push-toolbar="true" data-kt-app-sidebar-push-footer="true"
    data-kt-app-toolbar-enabled="true" class="app-default">
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

    {{-- begin::App --}}
    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        {{-- begin::Page --}}
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
            {{-- HEADER::BEGIN --}}
            @include('partials.admin.header')
            {{-- HEADER::END --}}
            {{-- begin::Wrapper --}}
            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
                {{-- begin::Wrapper container --}}
                <div class="app-container container-fluid d-flex flex-row-fluid">
                    {{-- Sidebar::BEGIN --}}
                    @include('partials.admin.sidebar')
                    {{-- Sidebar::END --}}
                    {{-- begin::Main --}}
                    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                        {{-- begin::Content wrapper --}}
                        <div class="d-flex flex-column flex-column-fluid">
                            {{-- begin::Toolbar --}}
                            <div id="kt_app_toolbar" class="app-toolbar d-flex pb-3 pb-lg-5">
                                @yield('toolbar')
                            </div>
                            {{-- end::Toolbar --}}
                            {{-- begin::Content --}}
                            <div id="kt_app_content" class="app-content flex-column-fluid">
                                @yield('content')
                            </div>
                            {{-- end::Content --}}
                        </div>
                        {{-- end::Content wrapper --}}
                        {{-- begin::Footer --}}
                        @include('partials.admin.footer')
                        {{-- end::Footer --}}
                    </div>
                    {{-- end::Main --}}
                </div>
                {{-- end::Wrapper container --}}
            </div>
            {{-- end::Wrapper --}}
        </div>
        {{-- end::Page --}}
    </div>
    {{-- end:App --}}

    {{-- begin::Scrolltop --}}
    <div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
        <i class="ki-outline ki-arrow-up"></i>
    </div>
    {{-- end::Scrolltop --}}

    {{-- JS::BEGIN --}}
    @include('partials.admin.js')
    @stack('scripts')
    {{-- JS::END --}}
    {{-- sweer alert --}}
    @include('sweetalert::alert')
    {{-- sweer alert --}}
</body>

</html>
