{{-- FAVICON --}}
<meta name="msapplication-TileImage" content="{{ asset('favicon/ms-icon-144x144.png') }}" />
<link rel="apple-touch-icon" sizes="57x57" href="{{ asset('favicon/apple-icon-57x57.png') }}" />
<link rel="apple-touch-icon" sizes="60x60" href="{{ asset('favicon/apple-icon-60x60.png') }}" />
<link rel="apple-touch-icon" sizes="72x72" href="{{ asset('favicon/apple-icon-72x72.png') }}" />
<link rel="apple-touch-icon" sizes="76x76" href="{{ asset('favicon/apple-icon-76x76.png') }}" />
<link rel="apple-touch-icon" sizes="114x114" href="{{ asset('favicon/apple-icon-114x114.png') }}" />
<link rel="apple-touch-icon" sizes="120x120" href="{{ asset('favicon/apple-icon-120x120.png') }}" />
<link rel="apple-touch-icon" sizes="144x144" href="{{ asset('favicon/apple-icon-144x144.png') }}" />
<link rel="apple-touch-icon" sizes="152x152" href="{{ asset('favicon/apple-icon-152x152.png') }}" />
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-icon-180x180.png') }}" />
<link rel="icon" type="image/png" sizes="36x36" href="{{ asset('favicon/android-icon-36x36.png') }}" />
<link rel="icon" type="image/png" sizes="48x48" href="{{ asset('favicon/android-icon-48x48.png') }}" />
<link rel="icon" type="image/png" sizes="72x72" href="{{ asset('favicon/android-icon-72x72.png') }}" />
<link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon/android-icon-96x96.png') }}" />
<link rel="icon" type="image/png" sizes="144x144" href="{{ asset('favicon/android-icon-144x144.png') }}" />
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon/android-icon-192x192.png') }}" />
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon/favicon-32x32.png') }}" />
<link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon/favicon-96x96.png') }}" />
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon/favicon-16x16.png') }}" />
<link rel="manifest" href="{{ asset('favicon/manifest.json') }}" />
{{-- FAVICON --}}

{{-- ===== CSS LINK ======= --}}
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
{{-- begin::Global Stylesheets Bundle(mandatory for all pages) --}}
<link href="{{ asset('be') }}/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{ asset('be') }}/plugins/global/plugins.bundle.css" />
<link rel="stylesheet" href="{{ asset('be') }}/css/style.bundle.css" />
{{-- DATATABLES --}}
<link rel="stylesheet" href="{{ asset('be/css/cid.css?v=') . date('YmdHis') }}">
{{-- end::Global Stylesheets Bundle --}}
{{-- =====  JS SCRIPT LINK ======= --}}
<script>
    if (window.top != window.self) {
        window.top.location.replace(window.self.location.href);
    }
</script>
