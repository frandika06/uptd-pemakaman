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
                    <a href="{{ route('auth.home') }}" class="text-gray-700 text-hover-primary">
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
                <li class="breadcrumb-item text-gray-700">Media</li>
                {{-- end::Item --}}
                {{-- begin::Item --}}
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                {{-- end::Item --}}
                {{-- begin::Item --}}
                <li class="breadcrumb-item text-gray-700">
                    <a href="{{ route('prt.apps.banner.index') }}" class="text-gray-700 text-hover-primary">Banner</a>
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
    <form id="kt_banner_form" class="form d-flex flex-column flex-lg-row" action="{{ isset($data) ? route('prt.apps.banner.update', [$uuid_enc]) : route('prt.apps.banner.store') }}"
        method="POST" enctype="multipart/form-data">
        @csrf
        @isset($data)
            @method('PUT')
        @endisset

        {{-- begin::Aside column --}}
        <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
            {{-- begin::Thumbnail settings --}}
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Thumbnail Banner</h2>
                    </div>
                </div>
                <div class="card-body text-center pt-0">
                    @if (isset($data) && !empty($data->thumbnails))
                        <div class="image-input image-input-outline image-input-placeholder" data-kt-image-input="true">
                            <div class="image-input-wrapper w-150px h-150px" style="background-image: url('{{ \Helper::urlImg($data->thumbnails) }}')"></div>
                            <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip"
                                title="Ganti thumbnail">
                                <i class="ki-outline ki-pencil fs-7"></i>
                                <input type="file" name="thumbnails" accept=".png,.jpg,.jpeg" />
                                <input type="hidden" name="thumbnails_remove" />
                            </label>
                            <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip"
                                title="Batalkan thumbnail">
                                <i class="ki-outline ki-cross fs-2"></i>
                            </span>
                            <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip"
                                title="Hapus thumbnail">
                                <i class="ki-outline ki-cross fs-2"></i>
                            </span>
                        </div>
                        <div class="mt-3">
                            <a href="{{ \Helper::urlImg($data->thumbnails) }}" target="_blank" class="btn btn-sm btn-light-primary">
                                <i class="ki-outline ki-eye fs-7 me-1"></i>Lihat Gambar
                            </a>
                        </div>
                        @error('thumbnails')
                            <div class="text-danger fs-7 mt-2">{{ $message }}</div>
                        @enderror
                        <div class="text-muted fs-7 mt-3">Format: PNG, JPG, JPEG | Max: 2MB<br>Layout landscape akan di-resize ke 1200x628px</div>
                    @else
                        <div class="image-input image-input-outline image-input-placeholder" data-kt-image-input="true">
                            <div class="image-input-wrapper w-150px h-150px"></div>
                            <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip"
                                title="Pilih thumbnail">
                                <i class="ki-outline ki-pencil fs-7"></i>
                                <input type="file" name="thumbnails" accept=".png,.jpg,.jpeg" required />
                                <input type="hidden" name="thumbnails_remove" />
                            </label>
                            <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip"
                                title="Batalkan thumbnail">
                                <i class="ki-outline ki-cross fs-2"></i>
                            </span>
                            <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip"
                                title="Hapus thumbnail">
                                <i class="ki-outline ki-cross fs-2"></i>
                            </span>
                        </div>
                        @error('thumbnails')
                            <div class="text-danger fs-7 mt-2">{{ $message }}</div>
                        @enderror
                        <div class="text-muted fs-7 mt-3">Format: PNG, JPG, JPEG | Max: 2MB<br>Layout landscape akan di-resize ke 1200x628px</div>
                    @endif
                </div>
            </div>
            {{-- end::Thumbnail settings --}}

            {{-- begin::Status settings --}}
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Status</h2>
                    </div>
                </div>
                <div class="card-body text-center pt-0">
                    <select class="form-select mb-2 @error('status') is-invalid @enderror" data-control="select2" data-hide-search="true" data-placeholder="Pilih Status"
                        id="kt_banner_status" name="status" required>
                        <option></option>
                        <option value="1" {{ old('status', isset($data) ? $data->status : '1') == '1' ? 'selected' : '' }}>Aktif
                        </option>
                        <option value="0" {{ old('status', isset($data) ? $data->status : '1') == '0' ? 'selected' : '' }}>Tidak Aktif
                        </option>
                    </select>
                    @error('status')
                        <div class="text-danger fs-7 mt-2">{{ $message }}</div>
                    @enderror
                    <div class="text-muted fs-7">Atur status publikasi banner.</div>
                </div>
            </div>
            {{-- end::Status settings --}}

            {{-- begin::Information --}}
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Informasi</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="d-flex flex-column gap-5">
                        <div class="m-0 p-0">
                            <span class="fw-bold text-gray-600">Kategori:</span><br />
                            <span class="text-gray-800 fw-bold">{{ old('kategori', isset($data) ? $data->kategori : '-') }}</span>
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
                </div>
            </div>
            {{-- end::Information --}}
        </div>
        {{-- end::Aside column --}}

        {{-- begin::Main column --}}
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            {{-- begin::General options --}}
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Detail Banner</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    {{-- begin::Input group (Judul) --}}
                    <div class="mb-10 fv-row">
                        <label class="required form-label">Judul</label>
                        <input type="text" name="judul" id="judul" class="form-control mb-2 @error('judul') is-invalid @enderror" placeholder="Masukkan judul banner"
                            value="{{ old('judul', isset($data) ? $data->judul : '') }}" maxlength="300" autocomplete="off" required />
                        <div class="text-muted fs-7">Judul banner maksimal 300 karakter.</div>
                        @error('judul')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- end::Input group --}}

                    {{-- begin::Input group (URL) --}}
                    <div class="mb-10 fv-row">
                        <label class="form-label">URL</label>
                        <input type="url" name="url" id="url" class="form-control mb-2 @error('url') is-invalid @enderror" placeholder="https://example.com"
                            value="{{ old('url', isset($data) ? $data->url : '') }}" maxlength="300" autocomplete="off" />
                        <div class="text-muted fs-7">Masukkan URL jika banner saat diklik akan membuka link.</div>
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

                    {{-- begin::Input group (Deskripsi) --}}
                    <div class="mb-10 fv-row">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" class="form-control mb-2 @error('deskripsi') is-invalid @enderror" rows="3" placeholder="Masukkan deskripsi banner"
                            maxlength="500">{{ old('deskripsi', isset($data) ? $data->deskripsi : '') }}</textarea>
                        <div class="text-muted fs-7">Masukkan deskripsi jika hero section memiliki deskripsi banner. Maksimal 500 karakter.</div>
                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- end::Input group --}}

                    {{-- begin::Input group (Warna Text) --}}
                    <div class="mb-10 fv-row">
                        <label class="required form-label">Warna Text</label>
                        <select class="form-select mb-2 @error('warna_text') is-invalid @enderror" data-control="select2" data-placeholder="Pilih Warna Text" name="warna_text"
                            id="warna_text" required>
                            <option></option>
                            <option value="Light" {{ old('warna_text', isset($data) ? $data->warna_text : '') == 'Light' ? 'selected' : '' }}>
                                Light</option>
                            <option value="Dark" {{ old('warna_text', isset($data) ? $data->warna_text : '') == 'Dark' ? 'selected' : '' }}>
                                Dark</option>
                        </select>
                        <div class="text-muted fs-7">Pilih warna teks untuk banner (Light atau Dark).</div>
                        @error('warna_text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- end::Input group --}}

                    {{-- begin::Input group (Kategori) --}}
                    <div class="mb-10 fv-row">
                        <label class="required form-label">Kategori</label>
                        <select class="form-select mb-2 @error('kategori') is-invalid @enderror" data-control="select2" data-placeholder="Pilih Kategori" name="kategori"
                            id="kategori" required>
                            <option></option>
                            @foreach ($kategoriList as $item)
                                <option value="{{ $item->nama }}" {{ old('kategori', isset($data) ? $data->kategori : '') == $item->nama ? 'selected' : '' }}>
                                    {{ $item->nama }}</option>
                            @endforeach
                        </select>
                        <div class="text-muted fs-7">Pilih kategori untuk banner.</div>
                        @error('kategori')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- end::Input group --}}
                </div>
            </div>
            {{-- end::General options --}}

            {{-- begin::Actions --}}
            <div class="d-flex justify-content-end">
                <a href="{{ route('prt.apps.banner.index') }}" id="kt_banner_cancel" class="btn btn-light me-5">
                    <i class="ki-outline ki-arrow-left fs-2"></i>Batal
                </a>
                <button type="submit" id="kt_banner_submit" class="btn btn-primary">
                    <span class="indicator-label">
                        <i class="fa-solid fa-save me-2"></i>{{ $submit }}
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
    {{-- end::Form --}}
@endsection
{{-- CONTENT::END --}}

{{-- SCRIPTS::BEGIN --}}
@push('scripts')
    <script>
        "use strict";

        // Class definition
        var KTAppBannerSave = function() {
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
                    var judul = form.querySelector('input[name="judul"]').value.trim();
                    var thumbnails = form.querySelector('input[name="thumbnails"]').files;
                    var thumbnails_remove = form.querySelector('input[name="thumbnails_remove"]').value;
                    var url = form.querySelector('input[name="url"]').value.trim();
                    var deskripsi = form.querySelector('textarea[name="deskripsi"]').value.trim();
                    var warna_text = form.querySelector('select[name="warna_text"]').value;
                    var kategori = form.querySelector('select[name="kategori"]').value;
                    var status = form.querySelector('select[name="status"]').value;

                    var isValid = true;
                    var errorMessage = '';

                    // Validate judul
                    if (!judul) {
                        isValid = false;
                        errorMessage = 'Judul banner wajib diisi';
                    } else if (judul.length > 300) {
                        isValid = false;
                        errorMessage = 'Judul banner maksimal 300 karakter';
                    }

                    // Validate thumbnails
                    @if (!isset($data))
                        if (thumbnails.length === 0) {
                            isValid = false;
                            errorMessage = 'Thumbnail wajib diunggah';
                        } else {
                            var file = thumbnails[0];
                            var validTypes = ['image/png', 'image/jpeg', 'image/jpg'];
                            if (!validTypes.includes(file.type)) {
                                isValid = false;
                                errorMessage = 'Thumbnail harus berupa file .png, .jpg, atau .jpeg';
                            } else if (file.size > 2 * 1024 * 1024) {
                                isValid = false;
                                errorMessage = 'Ukuran thumbnail maksimal 2 MB';
                            }
                        }
                    @else
                        if (thumbnails.length > 0) {
                            var file = thumbnails[0];
                            var validTypes = ['image/png', 'image/jpeg', 'image/jpg'];
                            if (!validTypes.includes(file.type)) {
                                isValid = false;
                                errorMessage = 'Thumbnail harus berupa file .png, .jpg, atau .jpeg';
                            } else if (file.size > 2 * 1024 * 1024) {
                                isValid = false;
                                errorMessage = 'Ukuran thumbnail maksimal 2 MB';
                            }
                        } else if (thumbnails_remove === '1') {
                            isValid = false;
                            errorMessage = 'Thumbnail tidak boleh dihapus tanpa mengunggah yang baru';
                        }
                    @endif

                    // Validate URL
                    if (url && !isValidURL(url)) {
                        isValid = false;
                        errorMessage = 'Format URL tidak valid. Harus dimulai dengan http:// atau https://';
                    } else if (url.length > 300) {
                        isValid = false;
                        errorMessage = 'URL maksimal 300 karakter';
                    }

                    // Validate deskripsi
                    if (deskripsi.length > 500) {
                        isValid = false;
                        errorMessage = 'Deskripsi maksimal 500 karakter';
                    }

                    // Validate warna_text
                    if (!warna_text) {
                        isValid = false;
                        errorMessage = 'Warna teks wajib dipilih';
                    }

                    // Validate kategori
                    if (!kategori) {
                        isValid = false;
                        errorMessage = 'Kategori wajib dipilih';
                    }

                    // Validate status
                    if (!status) {
                        isValid = false;
                        errorMessage = 'Status wajib dipilih';
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
                    form = document.querySelector('#kt_banner_form');
                    submitButton = document.querySelector('#kt_banner_submit');
                    cancelButton = document.querySelector('#kt_banner_cancel');

                    if (!form) {
                        return;
                    }

                    handleForm();
                    handleUrlPreview();

                    // Initialize Select2
                    $('#warna_text').select2({
                        minimumResultsForSearch: Infinity,
                        placeholder: "Pilih Warna Text"
                    });
                    $('#kategori').select2({
                        placeholder: "Pilih Kategori"
                    });
                    $('#kt_banner_status').select2({
                        minimumResultsForSearch: Infinity,
                        placeholder: "Pilih Status"
                    });

                    // Initialize KTImageInput
                    if (typeof KTImageInput !== 'undefined') {
                        KTImageInput.createInstances();
                    }
                }
            };
        }();

        // On document ready
        KTUtil.onDOMContentLoaded(function() {
            KTAppBannerSave.init();
        });
    </script>
@endpush
{{-- SCRIPTS::END --}}
