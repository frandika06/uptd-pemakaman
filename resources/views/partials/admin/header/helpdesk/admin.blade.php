<?php
$profile = \Helper::DataPP();
$auth = \Auth::user();
?>
<div id="kt_app_header" class="app-header" data-kt-sticky="true" data-kt-sticky-activate-="true" data-kt-sticky-name="app-header-sticky"
    data-kt-sticky-offset="{default: '200px', lg: '300px'}">
    {{-- begin::Header container --}}
    <div class="app-container container-fluid d-flex align-items-stretch justify-content-between" id="kt_app_header_container">
        {{-- begin::Header wrapper --}}
        <div class="app-header-wrapper d-flex flex-grow-1 align-items-stretch justify-content-between" id="kt_app_header_wrapper">
            {{-- begin::Logo wrapper --}}
            <div class="app-header-logo d-flex flex-shrink-0 align-items-center justify-content-between justify-content-lg-center">
                {{-- begin::Logo wrapper --}}
                <button class="btn btn-icon btn-color-gray-600 btn-active-color-primary ms-n3 me-2 d-flex d-lg-none" id="kt_app_sidebar_toggle">
                    <i class="ki-outline ki-abstract-14 fs-2"></i>
                </button>
                {{-- end::Logo wrapper --}}
                {{-- begin::Logo image --}}
                <a href="{{ route('auth.home') }}">
                    <img alt="Logo" src="{{ asset('logo/logo-light.png') }}" class="h-30px h-lg-60px theme-light-show" />
                    <img alt="Logo" src="{{ asset('logo/logo-light.png') }}" class="h-30px h-lg-60px theme-dark-show" />
                </a>
                {{-- end::Logo image --}}
            </div>
            {{-- end::Logo wrapper --}}
            {{-- begin::Menu wrapper --}}
            <div id="kt_app_header_menu_wrapper" class="d-flex align-items-center w-100">
                {{-- begin::Header menu --}}
                <div class="app-header-menu app-header-mobile-drawer align-items-start align-items-lg-center w-100" data-kt-drawer="true" data-kt-drawer-name="app-header-menu"
                    data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="250px" data-kt-drawer-direction="end"
                    data-kt-drawer-toggle="#kt_app_header_menu_toggle" data-kt-swapper="true" data-kt-swapper-mode="{default: 'append', lg: 'prepend'}"
                    data-kt-swapper-parent="{default: '#kt_app_body', lg: '#kt_app_header_menu_wrapper'}">
                    {{-- begin::Menu --}}
                    <div class="menu menu-rounded menu-column menu-lg-row menu-active-bg menu-state-primary menu-title-gray-700 menu-arrow-gray-400 menu-bullet-gray-400 my-5 my-lg-0 align-items-stretch fw-semibold px-2 px-lg-0"
                        id="#kt_header_menu" data-kt-menu="true">
                        {{-- begin:Menu item --}}
                        <div class="menu-item">
                            {{-- begin:Menu link --}}
                            <a class="menu-link active" href="{{ route('prt.apps.kotak.pesan.index') }}">
                                <span class="menu-title">Dashboard</span>
                            </a>
                            {{-- end:Menu link --}}
                        </div>
                        {{-- end:Menu item --}}
                    </div>
                    {{-- end::Menu --}}
                </div>
                {{-- end::Header menu --}}
            </div>
            {{-- end::Menu wrapper --}}
            {{-- begin::Navbar --}}
            <div class="app-navbar flex-shrink-0">
                {{-- begin::User menu --}}
                <div class="app-navbar-item ms-3 ms-lg-5" id="kt_header_user_menu_toggle">
                    {{-- begin::Menu wrapper --}}
                    <div class="cursor-pointer symbol symbol-35px symbol-md-40px" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent"
                        data-kt-menu-placement="bottom-end">
                        <img class="symbol symbol-circle symbol-35px symbol-md-40px" src="{{ \Helper::pp($profile->foto) }}" alt="{{ $profile->nama_lengkap }}" />
                    </div>
                    {{-- begin::User account menu --}}
                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px"
                        data-kt-menu="true">
                        {{-- begin::Menu item --}}
                        <div class="menu-item px-3">
                            <div class="menu-content d-flex align-items-center px-3">
                                {{-- begin::Avatar --}}
                                <div class="symbol symbol-50px me-5">
                                    <img alt="{{ $profile->nama_lengkap }}" src="{{ \Helper::pp($profile->foto) }}" />
                                </div>
                                {{-- end::Avatar --}}
                                {{-- begin::Username --}}
                                <div class="d-flex flex-column">
                                    <div class="fw-bold d-flex align-items-center fs-5">{{ $profile->nama_lengkap }}</div>
                                    <a href="#" class="fw-semibold text-muted text-hover-primary fs-7">{{ $auth->username }}</a>
                                </div>
                                {{-- end::Username --}}
                            </div>
                        </div>
                        {{-- end::Menu item --}}
                        {{-- begin::Menu separator --}}
                        <div class="separator my-2"></div>
                        {{-- end::Menu separator --}}
                        {{-- begin::Menu item --}}
                        <div class="menu-item px-5">
                            <a href="{{ route('prt.apps.profile.index') }}" class="menu-link px-5">My Profile</a>
                        </div>
                        {{-- end::Menu item --}}
                        {{-- begin::Menu separator --}}
                        <div class="separator my-2"></div>
                        {{-- end::Menu separator --}}
                        {{-- begin::Menu item --}}
                        <div class="menu-item px-5" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="left-start" data-kt-menu-offset="-15px, 0">
                            <a href="#" class="menu-link px-5">
                                <span class="menu-title position-relative">Mode
                                    <span class="ms-5 position-absolute translate-middle-y top-50 end-0">
                                        <i class="ki-outline ki-night-day theme-light-show fs-2"></i>
                                        <i class="ki-outline ki-moon theme-dark-show fs-2"></i>
                                    </span></span>
                            </a>
                            {{-- begin::Menu --}}
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-150px"
                                data-kt-menu="true" data-kt-element="theme-mode-menu">
                                {{-- begin::Menu item --}}
                                <div class="menu-item px-3 my-0">
                                    <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="light">
                                        <span class="menu-icon" data-kt-element="icon">
                                            <i class="ki-outline ki-night-day fs-2"></i>
                                        </span>
                                        <span class="menu-title">Light</span>
                                    </a>
                                </div>
                                {{-- end::Menu item --}}
                                {{-- begin::Menu item --}}
                                <div class="menu-item px-3 my-0">
                                    <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="dark">
                                        <span class="menu-icon" data-kt-element="icon">
                                            <i class="ki-outline ki-moon fs-2"></i>
                                        </span>
                                        <span class="menu-title">Dark</span>
                                    </a>
                                </div>
                                {{-- end::Menu item --}}
                                {{-- begin::Menu item --}}
                                <div class="menu-item px-3 my-0">
                                    <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="system">
                                        <span class="menu-icon" data-kt-element="icon">
                                            <i class="ki-outline ki-screen fs-2"></i>
                                        </span>
                                        <span class="menu-title">System</span>
                                    </a>
                                </div>
                                {{-- end::Menu item --}}
                            </div>
                            {{-- end::Menu --}}
                        </div>
                        {{-- end::Menu item --}}
                        {{-- begin::Menu item --}}
                        <div class="menu-item px-5">
                            <a href="{{ route('prt.login.logout') }}" class="menu-link px-5">Logout</a>
                        </div>
                        {{-- end::Menu item --}}
                    </div>
                    {{-- end::User account menu --}}
                    {{-- end::Menu wrapper --}}
                </div>
                {{-- end::User menu --}}
                {{-- begin::Header menu toggle --}}
                <div class="app-navbar-item d-lg-none ms-2 me-n3" title="Show header menu">
                    <div class="btn btn-icon btn-custom btn-active-color-primary btn-color-gray-700 w-35px h-35px w-md-40px h-md-40px" id="kt_app_header_menu_toggle">
                        <i class="ki-outline ki-text-align-left fs-1"></i>
                    </div>
                </div>
                {{-- end::Header menu toggle --}}
            </div>
            {{-- end::Navbar --}}
        </div>
        {{-- end::Header wrapper --}}
    </div>
    {{-- end::Header container --}}
</div>
