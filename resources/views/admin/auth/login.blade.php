@extends('layouts.auth')

{{-- SEO::BEGIN --}}
@section('title', 'Login - UPTD Pemakaman DPPP Kab. Tangerang')
{{-- SEO::END --}}

{{-- STYLE::BEGIN --}}
@push('styles')
@endpush
{{-- STYLE::END --}}

{{-- CONTENT::BEGIN --}}
@section('content')
    <!--begin::Authentication - Sign-in -->
    <div class="d-flex flex-column flex-lg-row flex-column-fluid">
        <!--begin::Logo-->
        <a href="#" class="d-block d-lg-none mx-auto py-20">
            <img alt="Logo" src="{{ asset('logo/logo-color.png') }}" class="theme-light-show h-70px" />
            <img alt="Logo" src="{{ asset('logo/logo-color.png') }}" class="theme-dark-show h-70px" />
        </a>
        <!--end::Logo-->
        <!--begin::Aside-->
        <div class="d-flex flex-column flex-column-fluid flex-center w-lg-50 p-10">
            <!--begin::Wrapper-->
            <div class="d-flex justify-content-between flex-column-fluid flex-column w-100 mw-450px">
                <!--begin::Header-->
                <div class="d-flex flex-stack py-2">
                    <!--begin::Back link-->
                    <div class="me-2"></div>
                    <!--end::Back link-->
                    <!--begin::Sign Up link-->
                    <div class="m-0">&nbsp;</div>
                    <!--end::Sign Up link=-->
                </div>
                <!--end::Header-->
                <!--begin::Body-->
                <div class="py-lg-20">
                    <!--begin::Form-->
                    <form class="form w-100" action="#">
                        <!--begin::Body-->
                        <div class="card shadow">
                            <div class="card-body">
                                <!--begin::Heading-->
                                <div class="text-start mb-10">
                                    <!--begin::Title-->
                                    <h1 class="text-dark mb-3 fs-3x" data-kt-translate="sign-in-title">Login</h1>
                                    <!--end::Title-->
                                    <!--begin::Text-->
                                    <div class="text-gray-800 fw-semibold fs-6" data-kt-translate="general-desc">Akses ke Admin Website UPTD Pemakaman</div>
                                    <!--end::Link-->
                                </div>
                                <!--begin::Heading-->
                                <!--begin::Input group=-->
                                <div class="fv-row mb-8">
                                    <!--begin::Email-->
                                    <input type="text" placeholder="Email" name="email" autocomplete="off" data-kt-translate="sign-in-input-email"
                                        class="form-control form-control-solid" />
                                    <!--end::Email-->
                                </div>
                                <!--end::Input group=-->
                                <div class="fv-row mb-7">
                                    <!--begin::Password-->
                                    <input type="text" placeholder="Password" name="password" autocomplete="off" data-kt-translate="sign-in-input-password"
                                        class="form-control form-control-solid" />
                                    <!--end::Password-->
                                </div>
                                <!--end::Input group=-->
                                <!--begin::Wrapper-->
                                <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-10">
                                    <div></div>
                                    <!--begin::Link-->
                                    <a href="#" class="link-primary" data-kt-translate="sign-in-forgot-password">Lupa Password &quest;</a>
                                    <!--end::Link-->
                                </div>
                                <!--end::Wrapper-->
                                <!--begin::Actions-->
                                <div class="d-flex flex-stack">
                                    <!--begin::Submit-->
                                    <button type="submit" class="btn btn-info me-2 flex-shrink-0">Login <i class="fa fa-sign-in"></i></button>
                                    <!--end::Submit-->
                                    <!--begin::Social-->
                                    <div class="d-flex align-items-center">
                                        <div class="text-gray-400 fw-semibold fs-6 me-3 me-md-6" data-kt-translate="general-or">Atau</div>
                                        <!--begin::Symbol-->
                                        <a href="#" class="symbol symbol-circle symbol-45px w-45px bg-light me-3">
                                            <img alt="Logo" src="{{ asset('be') }}/media/svg/brand-logos/google-icon.svg" class="p-4" />
                                        </a>
                                        <!--end::Symbol-->
                                    </div>
                                    <!--end::Social-->
                                </div>
                                <!--end::Actions-->
                                <div class="mt-8">
                                    <div class="text-gray-500 text-center fw-semibold fs-6">Belum memiliki Akun?
                                        <a href="../../demo37/dist/authentication/layouts/corporate/sign-up.html" class="link-primary">Daftar</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--begin::Body-->
                    </form>
                    <!--end::Form-->
                </div>
                <!--end::Body-->
                <!--begin::Footer-->
                <div class="m-0">&nbsp;</div>
                <!--end::Footer-->
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Aside-->
        <!--begin::Body-->
        <div class="d-none d-lg-flex flex-lg-row-fluid w-50 bgi-size-cover bgi-position-y-start bgi-position-x-start bgi-no-repeat"
            style="background-image: url({{ asset('be') }}/media/custom/bg-login-2.png)"></div>
        <!--begin::Body-->
    </div>
    <!--end::Authentication - Sign-in-->
@endsection
{{-- CONTENT::END --}}
