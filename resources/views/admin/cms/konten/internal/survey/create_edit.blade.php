@extends('layouts.admin')

{{-- SEO::BEGIN --}}
@section('title', $title)
{{-- SEO::END --}}

{{-- TOOLBAR::BEGIN --}}
@section('toolbar')
    <div class="d-flex flex-stack flex-row-fluid">
        {{-- begin::Toolbar container --}}
        <div class="d-flex flex-column flex-row-fluid">
            {{-- begin::Toolbar wrapper --}}
            {{-- begin::Page title --}}
            <div class="page-title d-flex align-items-center me-3">
                {{-- begin::Title --}}
                <h1 class="page-heading d-flex flex-column justify-content-center text-dark fw-bold fs-lg-2x gap-2">
                    <span>{{ $title }}</span>
                </h1>
                {{-- end::Title --}}
            </div>
            {{-- end::Page title --}}
            {{-- begin::Breadcrumb --}}
            <ul class="breadcrumb breadcrumb-separatorless fw-semibold mb-3 fs-7">
                {{-- begin::Item --}}
                <li class="breadcrumb-item text-gray-700 fw-bold lh-1">
                    <a href="{{ route('prt.apps.index') }}" class="text-gray-700 text-hover-primary">
                        <i class="ki-outline ki-home fs-6"></i>
                    </a>
                </li>
                {{-- end::Item --}}
                {{-- begin::Item --}}
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                {{-- end::Item --}}
                {{-- begin::Item --}}
                <li class="breadcrumb-item text-gray-700">Konten</li>
                {{-- end::Item --}}
                {{-- begin::Item --}}
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                {{-- end::Item --}}
                {{-- begin::Item --}}
                <li class="breadcrumb-item text-gray-700">Konten Internal</li>
                {{-- end::Item --}}
                {{-- begin::Item --}}
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                {{-- end::Item --}}
                {{-- begin::Item --}}
                <li class="breadcrumb-item text-gray-700">Links</li>
                {{-- end::Item --}}
                {{-- begin::Item --}}
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                {{-- end::Item --}}
                {{-- begin::Item --}}
                <li class="breadcrumb-item text-gray-700">
                    <a href="{{ route('prt.apps.links.index', [$tags]) }}" class="text-gray-700 text-hover-primary">{{ $kategori }}</a>
                </li>
                {{-- end::Item --}}
                {{-- begin::Item --}}
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                {{-- end::Item --}}
                {{-- begin::Item --}}
                <li class="breadcrumb-item text-gray-700">{{ isset($data) ? 'Edit' : 'Tambah' }}</li>
                {{-- end::Item --}}
            </ul>
            {{-- end::Breadcrumb --}}
        </div>
        {{-- end::Toolbar container --}}
    </div>
@endsection
{{-- TOOLBAR::END --}}

{{-- CONTENT::BEGIN --}}
@section('content')
    {{-- begin::Form --}}
    <form id="kt_links_form" class="form d-flex flex-column flex-lg-row"
        action="{{ isset($data) ? route('prt.apps.links.update', [$tags, $uuid_enc]) : route('prt.apps.links.store', [$tags]) }}" method="POST">
        @csrf
        @isset($data)
            @method('PUT')
        @endisset

        {{-- begin::Aside column --}}
        <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
            {{-- begin::Status settings --}}
            <div class="card card-flush py-4">
                {{-- begin::Card header --}}
                <div class="card-header">
                    {{-- begin::Card title --}}
                    <div class="card-title">
                        <h2>Status</h2>
                    </div>
                    {{-- end::Card title --}}
                </div>
                {{-- end::Card header --}}
                {{-- begin::Card body --}}
                <div class="card-body text-center pt-0">
                    {{-- begin::Select2 --}}
                    <select class="form-select mb-2" data-control="select2" data-hide-search="true" data-placeholder="Pilih Status" id="kt_links_status" name="status" disabled>
                        <option></option>
                        <option value="1" {{ isset($data) && $data->status == '1' ? 'selected' : (!isset($data) ? 'selected' : '') }}>Aktif</option>
                        <option value="0" {{ isset($data) && $data->status == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                    {{-- end::Select2 --}}
                    {{-- begin::Description --}}
                    <div class="text-muted fs-7">Status akan otomatis diatur setelah data disimpan.</div>
                    {{-- end::Description --}}
                </div>
                {{-- end::Card body --}}
            </div>
            {{-- end::Status settings --}}

            {{-- begin::Information --}}
            <div class="card card-flush py-4">
                {{-- begin::Card header --}}
                <div class="card-header">
                    {{-- begin::Card title --}}
                    <div class="card-title">
                        <h2>Informasi</h2>
                    </div>
                    {{-- end::Card title --}}
                </div>
                {{-- end::Card header --}}
                {{-- begin::Card body --}}
                <div class="card-body pt-0">
                    {{-- begin::Information --}}
                    <div class="d-flex flex-column gap-5">
                        <div class="m-0 p-0">
                            <span class="fw-bold text-gray-600">Kategori:</span><br />
                            <span class="text-gray-800 fw-bold">{{ $kategori }}</span>
                        </div>
                        <div class="m-0 p-0">
                            <span class="fw-bold text-gray-600">Dibuat:</span><br />
                            <span class="text-gray-800 fw-bold">
                                {{ isset($data) ? $data->created_at->format('d M Y H:i') : 'Akan diatur otomatis' }}
                            </span>
                        </div>
                        @isset($data)
                            <div class="m-0 p-0">
                                <span class="fw-bold text-gray-600">Diperbarui:</span><br />
                                <span class="text-gray-800 fw-bold">{{ $data->updated_at->format('d M Y H:i') }}</span>
                            </div>
                        @endisset
                    </div>
                    {{-- end::Information --}}
                </div>
                {{-- end::Card body --}}
            </div>
            {{-- end::Information --}}
        </div>
        {{-- end::Aside column --}}

        {{-- begin::Main column --}}
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            {{-- begin::General options --}}
            <div class="card card-flush py-4">
                {{-- begin::Card header --}}
                <div class="card-header">
                    <div class="card-title">
                        <h2>Detail Links {{ $kategori }}</h2>
                    </div>
                </div>
                {{-- end::Card header --}}
                {{-- begin::Card body --}}
                <div class="card-body pt-0">
                    {{-- begin::Input group --}}
                    <div class="mb-10 fv-row">
                        {{-- begin::Label --}}
                        <label class="required form-label">Nomor Urut</label>
                        {{-- end::Label --}}
                        {{-- begin::Input --}}
                        <input type="number" name="no_urut" class="form-control mb-2 @error('no_urut') is-invalid @enderror" placeholder="Masukkan nomor urut"
                            value="{{ old('no_urut', isset($data) ? $data->no_urut : \Helper::GetNoUrutLinks($tags)) }}" autocomplete="off" min="1" required />
                        {{-- end::Input --}}
                        {{-- begin::Description --}}
                        <div class="text-muted fs-7">Nomor urut untuk pengurutan links.</div>
                        {{-- end::Description --}}
                        @error('no_urut')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- end::Input group --}}

                    {{-- begin::Input group --}}
                    <div class="mb-10 fv-row">
                        {{-- begin::Label --}}
                        <label class="required form-label">Judul Links</label>
                        {{-- end::Label --}}
                        {{-- begin::Input --}}
                        <input type="text" name="judul" id="judul" class="form-control mb-2 @error('judul') is-invalid @enderror" placeholder="Masukkan judul links"
                            value="{{ old('judul', isset($data) ? $data->judul : '') }}" maxlength="300" autocomplete="off" required />
                        {{-- end::Input --}}
                        {{-- begin::Description --}}
                        <div class="text-muted fs-7">Judul links maksimal 300 karakter.</div>
                        {{-- end::Description --}}
                        @error('judul')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- end::Input group --}}

                    {{-- begin::Input group --}}
                    <div class="mb-10 fv-row">
                        {{-- begin::Label --}}
                        <label class="required form-label">URL</label>
                        {{-- end::Label --}}
                        {{-- begin::Input --}}
                        <input type="url" name="url" id="url" class="form-control mb-2 @error('url') is-invalid @enderror" placeholder="https://example.com"
                            value="{{ old('url', isset($data) ? $data->url : '') }}" maxlength="300" autocomplete="off" required />
                        {{-- end::Input --}}
                        {{-- begin::Description --}}
                        <div class="text-muted fs-7">URL lengkap termasuk protokol (http:// atau https://).</div>
                        {{-- end::Description --}}
                        @error('url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- end::Input group --}}

                    {{-- begin::URL Preview --}}
                    <div class="mb-10">
                        <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-6">
                            <i class="ki-outline ki-information-5 fs-2tx text-primary me-4"></i>
                            <div class="d-flex flex-stack flex-grow-1">
                                <div class="fw-semibold">
                                    <h4 class="text-gray-900 fw-bold">Preview URL</h4>
                                    <div class="fs-6 text-gray-700">
                                        <span id="url_preview_text">Masukkan URL untuk melihat preview</span>
                                        <br>
                                        <a href="#" id="url_preview_link" target="_blank" class="text-primary d-none">
                                            <i class="ki-outline ki-external-link fs-7 me-1"></i>Buka URL
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- end::URL Preview --}}
                </div>
                {{-- end::Card body --}}
            </div>
            {{-- end::General options --}}

            {{-- begin::Actions --}}
            <div class="d-flex justify-content-end">
                {{-- begin::Button --}}
                <a href="{{ route('prt.apps.links.index', [$tags]) }}" id="kt_links_cancel" class="btn btn-light me-5">
                    <i class="ki-outline ki-arrow-left fs-2"></i>Batal
                </a>
                {{-- end::Button --}}
                {{-- begin::Button --}}
                <button type="submit" id="kt_links_submit" class="btn btn-primary">
                    <span class="indicator-label">
                        <i class="fa-solid fa-save me-2"></i>{{ $submit }}
                    </span>
                    <span class="indicator-progress">
                        Please wait...
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                    </span>
                </button>
                {{-- end::Button --}}
            </div>
            {{-- end::Actions --}}
        </div>
        {{-- end::Main column --}}
    </form>
    {{-- end::Form --}}
@endsection
{{-- CONTENT::END --}}

{{-- SCRIPTS::BEGIN --}}
@push('scripts')
    <script>
        "use strict";

        // Class definition
        var KTAppLinksSave = function() {
            // Elements
            var form;
            var submitButton;
            var cancelButton;

            // Handle form
            var handleForm = function(e) {
                // Handle form submit
                submitButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Simple validation
                    var no_urut = form.querySelector('input[name="no_urut"]').value.trim();
                    var judul = form.querySelector('input[name="judul"]').value.trim();
                    var url = form.querySelector('input[name="url"]').value.trim();

                    var isValid = true;
                    var errorMessage = '';

                    // Validate no_urut
                    if (!no_urut) {
                        isValid = false;
                        errorMessage = 'Nomor urut wajib diisi';
                    } else if (parseInt(no_urut) < 1) {
                        isValid = false;
                        errorMessage = 'Nomor urut minimal 1';
                    }

                    // Validate judul
                    if (!judul) {
                        isValid = false;
                        errorMessage = 'Judul links wajib diisi';
                    } else if (judul.length > 300) {
                        isValid = false;
                        errorMessage = 'Judul links maksimal 300 karakter';
                    }

                    // Validate URL
                    if (!url) {
                        isValid = false;
                        errorMessage = 'URL wajib diisi';
                    } else if (url.length > 300) {
                        isValid = false;
                        errorMessage = 'URL maksimal 300 karakter';
                    } else if (!isValidURL(url)) {
                        isValid = false;
                        errorMessage = 'Format URL tidak valid. Harus dimulai dengan http:// atau https://';
                    }

                    if (isValid) {
                        // Show loading
                        submitButton.setAttribute('data-kt-indicator', 'on');
                        submitButton.disabled = true;

                        // Submit form
                        form.submit();
                    } else {
                        // Show error
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

                // Handle cancel button
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
            }

            // Handle slug preview
            var handleSlugPreview = function() {
                // No slug needed for links
            }

            // Handle URL preview
            var handleUrlPreview = function() {
                const urlInput = document.querySelector('input[name="url"]');
                const urlPreviewText = document.querySelector('#url_preview_text');
                const urlPreviewLink = document.querySelector('#url_preview_link');

                if (urlInput && urlPreviewText && urlPreviewLink) {
                    urlInput.addEventListener('input', function() {
                        const url = this.value.trim();

                        if (url && isValidURL(url)) {
                            urlPreviewText.textContent = url;
                            urlPreviewLink.href = url;
                            urlPreviewLink.classList.remove('d-none');
                        } else if (url) {
                            urlPreviewText.textContent = url + ' (Format URL tidak valid)';
                            urlPreviewLink.classList.add('d-none');
                        } else {
                            urlPreviewText.textContent = 'Masukkan URL untuk melihat preview';
                            urlPreviewLink.classList.add('d-none');
                        }
                    });

                    // Set initial URL if editing
                    const initialUrl = urlInput.value;
                    if (initialUrl) {
                        if (isValidURL(initialUrl)) {
                            urlPreviewText.textContent = initialUrl;
                            urlPreviewLink.href = initialUrl;
                            urlPreviewLink.classList.remove('d-none');
                        } else {
                            urlPreviewText.textContent = initialUrl + ' (Format URL tidak valid)';
                            urlPreviewLink.classList.add('d-none');
                        }
                    }
                }
            }

            // Create slug from string (not used for links)
            var createSlug = function(str) {
                return str
                    .toLowerCase()
                    .trim()
                    .replace(/[^\w\s-]/g, '') // Remove special characters
                    .replace(/[\s_-]+/g, '-') // Replace spaces and underscores with hyphens
                    .replace(/^-+|-+$/g, ''); // Remove leading/trailing hyphens
            }

            // Validate URL format
            var isValidURL = function(string) {
                try {
                    new URL(string);
                    return string.startsWith('http://') || string.startsWith('https://');
                } catch (_) {
                    return false;
                }
            }

            // Public methods
            return {
                init: function() {
                    // Elements
                    form = document.querySelector('#kt_links_form');
                    submitButton = document.querySelector('#kt_links_submit');
                    cancelButton = document.querySelector('#kt_links_cancel');

                    if (!form) {
                        return;
                    }

                    handleForm();
                    handleUrlPreview();
                }
            };
        }();

        // On document ready
        KTUtil.onDOMContentLoaded(function() {
            KTAppLinksSave.init();
        });
    </script>
@endpush
{{-- SCRIPTS::END --}}
