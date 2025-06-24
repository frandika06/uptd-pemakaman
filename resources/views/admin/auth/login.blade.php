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
    {{-- begin::Authentication - Sign-in --}}
    <div class="d-flex flex-column flex-lg-row flex-column-fluid">
        {{-- begin::Logo --}}
        <a href="#" class="d-block d-lg-none mx-auto py-20">
            <img alt="Logo" src="{{ asset('logo/logo-color.png') }}" class="theme-light-show h-70px" />
            <img alt="Logo" src="{{ asset('logo/logo-color.png') }}" class="theme-dark-show h-70px" />
        </a>
        {{-- end::Logo --}}
        {{-- begin::Aside --}}
        <div class="d-flex flex-column flex-column-fluid flex-center w-lg-50 p-10">
            {{-- begin::Wrapper --}}
            <div class="d-flex justify-content-between flex-column-fluid flex-column w-100 mw-450px">
                {{-- begin::Header --}}
                <div class="d-flex flex-stack py-2">
                    {{-- begin::Back link --}}
                    <div class="me-2"></div>
                    {{-- end::Back link --}}
                    {{-- begin::Sign Up link --}}
                    <div class="m-0">&nbsp;</div>
                    {{-- end::Sign Up link= --}}
                </div>
                {{-- end::Header --}}
                {{-- begin::Body --}}
                <div class="py-lg-20">
                    {{-- begin::Form --}}
                    <form class="form w-100" action="{{ route('auth.login.store') }}" method="POST">
                        @csrf
                        {{-- begin::Body --}}
                        <div class="card shadow">
                            <div class="card-body">
                                {{-- begin::Heading --}}
                                <div class="text-start mb-10">
                                    {{-- begin::Title --}}
                                    <h1 class="text-dark mb-3 fs-3x" data-kt-translate="sign-in-title">Login</h1>
                                    {{-- end::Title --}}
                                    {{-- begin::Text --}}
                                    <div class="text-gray-800 fw-semibold fs-6" data-kt-translate="general-desc">Akses ke Admin Website UPTD Pemakaman</div>
                                    {{-- end::Link --}}
                                </div>
                                {{-- begin::Heading --}}

                                {{-- begin::Global Alert Error --}}
                                @if ($errors->any())
                                    <div class="alert alert-danger d-flex align-items-center p-5 mb-10">
                                        <i class="ki-duotone ki-shield-cross fs-2hx text-danger me-4">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <div class="d-flex flex-column">
                                            <h4 class="mb-1 text-danger">Terjadi Kesalahan!</h4>
                                            <span>
                                                @foreach ($errors->all() as $error)
                                                    • {{ $error }}<br>
                                                @endforeach
                                            </span>
                                        </div>
                                    </div>
                                @endif
                                {{-- end::Global Alert Error --}}

                                {{-- begin::Input group - Email --}}
                                <div class="fv-row mb-8">
                                    {{-- begin::Email --}}
                                    <input type="text" placeholder="Email" name="email" autocomplete="off" data-kt-translate="sign-in-input-email"
                                        class="form-control form-control-solid @error('email') is-invalid @enderror" value="{{ old('email') }}" autocomplete="off" required />
                                    {{-- end::Email --}}
                                    {{-- begin::Error Email --}}
                                    @error('email')
                                        <div class="invalid-feedback">
                                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                        </div>
                                    @enderror
                                    {{-- end::Error Email --}}
                                </div>
                                {{-- end::Input group - Email --}}

                                {{-- begin::Input group - Password --}}
                                <div class="fv-row mb-7">
                                    {{-- begin::Password --}}
                                    <input type="password" placeholder="Password" name="password" autocomplete="off" data-kt-translate="sign-in-input-password"
                                        class="form-control form-control-solid @error('password') is-invalid @enderror" autocomplete="off" required />
                                    {{-- end::Password --}}
                                    {{-- begin::Error Password --}}
                                    @error('password')
                                        <div class="invalid-feedback">
                                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                        </div>
                                    @enderror
                                    {{-- end::Error Password --}}
                                </div>
                                {{-- end::Input group - Password --}}

                                {{-- begin::Input group - Captcha --}}
                                <div class="row mb-3">
                                    <div class="col-md-5 col-sm-5 col-6">
                                        <div class="captcha" style="cursor: pointer;">
                                            <span id="reload">{!! captcha_img('math') !!}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-7 col-sm-7 col-6">
                                        <div class="input-group mb-3 input-primary">
                                            <span class="input-group-text"><i class="fa-solid fa-certificate"></i></span>
                                            <input type="text" name="captcha" id="captcha" class="form-control @error('captcha') is-invalid @enderror" placeholder="Captcha"
                                                autocomplete="off" maxlength="10" required />
                                            {{-- begin::Error Captcha --}}
                                            @error('captcha')
                                                <div class="invalid-feedback">
                                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                                </div>
                                            @enderror
                                            {{-- end::Error Captcha --}}
                                        </div>
                                    </div>
                                </div>
                                {{-- end::Input group - Captcha --}}

                                {{-- begin::Remember Me & Forgot Password --}}
                                <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-10">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="remember_me" name="remember_me">
                                        <label class="form-check-label" for="remember_me">
                                            Ingat Saya
                                        </label>
                                    </div>
                                    {{-- begin::Link --}}
                                    <a href="#" class="link-primary" data-kt-translate="sign-in-forgot-password">Lupa Password &quest;</a>
                                    {{-- end::Link --}}
                                </div>
                                {{-- end::Remember Me & Forgot Password --}}

                                {{-- begin::Actions --}}
                                <div class="d-flex flex-stack">
                                    {{-- begin::Submit --}}
                                    <button type="submit" class="btn btn-info me-2 flex-shrink-0">
                                        <span class="indicator-label">Login <i class="fa fa-sign-in"></i></span>
                                        <span class="indicator-progress">
                                            Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                        </span>
                                    </button>
                                    {{-- end::Submit --}}
                                    {{-- begin::Social --}}
                                    <div class="d-flex align-items-center">
                                        <div class="text-gray-400 fw-semibold fs-6 me-3 me-md-6" data-kt-translate="general-or">Atau</div>
                                        {{-- begin::Symbol --}}
                                        <a href="#" class="symbol symbol-circle symbol-45px w-45px bg-light me-3">
                                            <img alt="Logo" src="{{ asset('be') }}/media/svg/brand-logos/google-icon.svg" class="p-4" />
                                        </a>
                                        {{-- end::Symbol --}}
                                    </div>
                                    {{-- end::Social --}}
                                </div>
                                {{-- end::Actions --}}

                                {{-- begin::Register Link --}}
                                <div class="mt-8">
                                    <div class="text-gray-500 text-center fw-semibold fs-6">Belum memiliki Akun?
                                        <a href="#" class="link-primary">Daftar</a>
                                    </div>
                                </div>
                                {{-- end::Register Link --}}
                            </div>
                        </div>
                        {{-- end::Body --}}
                    </form>
                    {{-- end::Form --}}
                </div>
                {{-- end::Body --}}
                {{-- begin::Footer --}}
                <div class="m-0">&nbsp;</div>
                {{-- end::Footer --}}
            </div>
            {{-- end::Wrapper --}}
        </div>
        {{-- end::Aside --}}
        {{-- begin::Body --}}
        <div class="d-none d-lg-flex flex-lg-row-fluid w-50 bgi-size-cover bgi-position-y-start bgi-position-x-start bgi-no-repeat"
            style="background-image: url({{ asset('be') }}/media/custom/bg-login-2.png)"></div>
        {{-- begin::Body --}}
    </div>
    {{-- end::Authentication - Sign-in --}}
@endsection
{{-- CONTENT::END --}}

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('#reload').click(function() {
                $.ajax({
                    type: 'GET',
                    url: '{{ route('ajax.captcha.get') }}',
                    success: function(data) {
                        $(".captcha span").html(data.captcha);
                    }
                });
            });

            // Form submission with loading state
            document.querySelector('form').addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.setAttribute('data-kt-indicator', 'on');
                submitBtn.disabled = true;
            });
        });
    </script>
@endpush
