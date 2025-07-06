@extends('layouts.admin')

{{-- SEO::BEGIN --}}
@section('title', 'Master Sosial Media')
@section('description', 'Master Sosial Media | UPTD Pemakaman DPPP Kabupaten Tangerang')
{{-- SEO::END --}}

{{-- TOOLBAR::BEGIN --}}
@section('toolbar')
    <div class="d-flex flex-stack flex-row-fluid">
        {{-- begin::Toolbar container --}}
        <div class="d-flex flex-column flex-row-fluid">
            {{-- begin::Page title --}}
            <div class="page-title d-flex align-items-center me-3">
                <h1 class="page-heading d-flex flex-column justify-content-center text-dark fw-bold fs-lg-2x gap-2">
                    <span>Master Sosial Media</span>
                </h1>
            </div>
            {{-- end::Page title --}}
            {{-- begin::Breadcrumb --}}
            <ul class="breadcrumb breadcrumb-separatorless fw-semibold mb-3 fs-7">
                <li class="breadcrumb-item text-gray-700 fw-bold lh-1">
                    <a href="{{ route('auth.home') }}" class="text-gray-700 text-hover-primary">
                        <i class="ki-outline ki-home fs-6"></i>
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                <li class="breadcrumb-item text-gray-700">Pengaturan</li>
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                <li class="breadcrumb-item text-gray-700">Master Data</li>
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                <li class="breadcrumb-item text-gray-700">Sosial Media</li>
            </ul>
            {{-- end::Breadcrumb --}}
        </div>
        {{-- end::Toolbar container --}}
    </div>
@endsection
{{-- TOOLBAR::END --}}

{{-- CONTENT::BEGIN --}}
@section('content')
    <form id="kt_sosmed_form" class="form d-flex flex-column flex-lg-row" action="{{ route('prt.apps.mst.sosmed.update') }}" method="POST">
        @csrf
        @method('PUT')

        {{-- begin::Main column --}}
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            {{-- begin::General options --}}
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Detail Sosial Media</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    @foreach ($data as $item)
                        <div class="row mb-10">
                            <div class="col-lg-6">
                                <div class="fv-row">
                                    <label class="required form-label">Nama Sosial Media</label>
                                    <input type="hidden" name="uuid[]" value="{{ $item->uuid }}">
                                    <input type="text" name="sosmed[]" class="form-control mb-2 @error('sosmed.' . $loop->index) is-invalid @enderror"
                                        placeholder="Masukkan nama sosial media" value="{{ old('sosmed.' . $loop->index, $item->sosmed) }}" autocomplete="off" maxlength="100" required
                                        readonly />
                                    @error('sosmed.' . $loop->index)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="fv-row">
                                    <label class="required form-label">URL Sosial Media</label>
                                    <input type="url" name="url[]" class="form-control mb-2 @error('url.' . $loop->index) is-invalid @enderror"
                                        placeholder="Masukkan URL sosial media" value="{{ old('url.' . $loop->index, $item->url) }}" autocomplete="off" maxlength="300" required />
                                    @error('url.' . $loop->index)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            {{-- end::General options --}}

            {{-- begin::Actions --}}
            <div class="d-flex justify-content-end">
                <a href="{{ route('setup.apps.index') }}" id="kt_sosmed_cancel" class="btn btn-light me-5">
                    <i class="ki-outline ki-arrow-left fs-2"></i>Batal
                </a>
                <button type="submit" id="kt_sosmed_submit" class="btn btn-primary">
                    <span class="indicator-label">
                        <i class="fa-solid fa-save me-2"></i>Simpan
                    </span>
                    <span class="indicator-progress">
                        Please wait...
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                    </span>
                </button>
            </div>
            {{-- end::Actions --}}
        </div>
        {{-- end::Main column --}}
    </form>
@endsection
{{-- CONTENT::END --}}

{{-- SCRIPTS::BEGIN --}}
@push('scripts')
    <script>
        "use strict";

        // Class definition
        var KTAppSosmedSave = function() {
            // Elements
            var form;
            var submitButton;
            var cancelButton;

            // Handle form
            var handleForm = function(e) {
                submitButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Simple validation
                    var sosmedInputs = form.querySelectorAll('input[name="sosmed[]"]');
                    var urlInputs = form.querySelectorAll('input[name="url[]"]');
                    var isValid = true;
                    var errorMessage = '';

                    // Validate Sosmed
                    sosmedInputs.forEach(function(input, index) {
                        var value = input.value.trim();
                        if (!value) {
                            isValid = false;
                            errorMessage = 'Nama sosial media wajib diisi';
                            input.classList.add('is-invalid');
                        } else if (value.length > 100) {
                            isValid = false;
                            errorMessage = 'Nama sosial media maksimal 100 karakter';
                            input.classList.add('is-invalid');
                        } else {
                            input.classList.remove('is-invalid');
                        }
                    });

                    // Validate URL
                    urlInputs.forEach(function(input, index) {
                        var value = input.value.trim();
                        if (!value) {
                            isValid = false;
                            errorMessage = 'URL sosial media wajib diisi';
                            input.classList.add('is-invalid');
                        } else if (!/^(https?:\/\/)?([\da-z.-]+)\.([a-z.]{2,6})([/\w .-]*)*\/?$/.test(value)) {
                            isValid = false;
                            errorMessage = 'URL sosial media tidak valid';
                            input.classList.add('is-invalid');
                        } else if (value.length > 300) {
                            isValid = false;
                            errorMessage = 'URL sosial media maksimal 300 karakter';
                            input.classList.add('is-invalid');
                        } else {
                            input.classList.remove('is-invalid');
                        }
                    });

                    if (isValid) {
                        submitButton.setAttribute('data-kt-indicator', 'on');
                        submitButton.disabled = true;
                        form.submit();
                    } else {
                        Swal.fire({
                            text: errorMessage,
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, saya mengerti!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        });
                    }
                });

                cancelButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    Swal.fire({
                        text: "Apakah Anda yakin ingin membatalkan?",
                        icon: "warning",
                        showCancelButton: true,
                        buttonsStyling: false,
                        confirmButtonText: "Ya, batalkan!",
                        cancelButtonText: "Tidak",
                        customClass: {
                            confirmButton: "btn btn-primary",
                            cancelButton: "btn btn-active-light"
                        }
                    }).then(function(result) {
                        if (result.value) {
                            window.location = cancelButton.getAttribute('href');
                        }
                    });
                });
            };

            // Public methods
            return {
                init: function() {
                    form = document.querySelector('#kt_sosmed_form');
                    submitButton = document.querySelector('#kt_sosmed_submit');
                    cancelButton = document.querySelector('#kt_sosmed_cancel');

                    if (!form) {
                        return;
                    }

                    handleForm();
                }
            };
        }();

        // On document ready
        KTUtil.onDOMContentLoaded(function() {
            KTAppSosmedSave.init();
        });
    </script>
@endpush
{{-- SCRIPTS::END --}}
