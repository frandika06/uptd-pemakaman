@extends('layouts.admin')

{{-- SEO::BEGIN --}}
@section('title', $title)
{{-- SEO::END --}}

{{-- TOOLBAR::BEGIN --}}
@section('toolbar')
    <div class="d-flex flex-stack flex-row-fluid">
        <div class="d-flex flex-column flex-row-fluid">
            <div class="page-title d-flex align-items-center me-3">
                <h1 class="page-heading d-flex flex-column justify-content-center text-dark fw-bold fs-lg-2x gap-2">
                    <span>{{ $title }}</span>
                </h1>
            </div>
            <ul class="breadcrumb breadcrumb-separatorless fw-semibold mb-3 fs-7">
                <li class="breadcrumb-item text-gray-700 fw-bold lh-1">
                    <a href="{{ route('auth.home') }}" class="text-gray-700 text-hover-primary">
                        <i class="ki-outline ki-home fs-6"></i>
                    </a>
                </li>
                <li class="breadcrumb-item"><i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i></li>
                <li class="breadcrumb-item text-gray-700">Konten</li>
                <li class="breadcrumb-item"><i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i></li>
                <li class="breadcrumb-item text-gray-700">Konten Media</li>
                <li class="breadcrumb-item"><i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i></li>
                <li class="breadcrumb-item text-gray-700">
                    <a href="{{ route('prt.apps.banner.index') }}" class="text-gray-700 text-hover-primary">Banner</a>
                </li>
                <li class="breadcrumb-item"><i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i></li>
                <li class="breadcrumb-item text-gray-700">{{ isset($data) ? 'Edit' : 'Tambah' }}</li>
            </ul>
        </div>
    </div>
@endsection
{{-- TOOLBAR::END --}}

{{-- CONTENT::BEGIN --}}
@section('content')
    <form id="kt_banner_form" class="form d-flex flex-column flex-lg-row" action="{{ isset($data) ? route('prt.apps.banner.update', [$uuid_enc]) : route('prt.apps.banner.store') }}"
        method="POST" enctype="multipart/form-data">
        @csrf
        @isset($data)
            @method('PUT')
        @endisset

        <!-- Aside Column -->
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
                            <div class="image-input-wrapper w-150px h-150px" style="background-image: url('{{ Helper::urlImg($data->thumbnails) }}')"></div>
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
                            <a href="{{ Helper::urlImg($data->thumbnails) }}" target="_blank" class="btn btn-sm btn-light-primary">
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

            <!-- Status Settings -->
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Status</h2>
                    </div>
                </div>
                <div class="card-body text-center pt-0">
                    <select class="form-select mb-2" data-control="select2" data-hide-search="true" data-placeholder="Pilih Status" name="status">
                        <option></option>
                        <option value="1" {{ isset($data) && $data->status == '1' ? 'selected' : (!isset($data) ? 'selected' : '') }}>Aktif</option>
                        <option value="0" {{ isset($data) && $data->status == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                    <div class="text-muted fs-7">Status banner akan menentukan tampil atau tidaknya banner.</div>
                </div>
            </div>

            <!-- Information -->
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
                            <span class="text-gray-800 fw-bold">{{ isset($data) ? $data->kategori : 'Akan diatur otomatis' }}</span>
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
        </div>

        <!-- Main Column -->
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Detail Banner</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <!-- Judul -->
                    <div class="mb-10 fv-row">
                        <label class="required form-label">Judul</label>
                        <input type="text" name="judul" id="judul" class="form-control mb-2 @error('judul') is-invalid @enderror" placeholder="Masukkan Judul"
                            value="{{ old('judul', isset($data) ? $data->judul : '') }}" maxlength="300" autocomplete="off" required />
                        <div class="text-muted fs-7">Judul banner maksimal 300 karakter.</div>
                        @error('judul')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- URL -->
                    <div class="mb-10 fv-row">
                        <label class="form-label">URL</label>
                        <input type="url" name="url" id="url" class="form-control mb-2 @error('url') is-invalid @enderror" placeholder="https://example.com"
                            value="{{ old('url', isset($data) ? $data->url : '') }}" maxlength="300" autocomplete="off" />
                        <div class="text-muted fs-7">Masukkan URL jika banner saat diklik terbuka link.</div>
                        @error('url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Deskripsi -->
                    <div class="mb-10 fv-row">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" class="form-control mb-2 @error('deskripsi') is-invalid @enderror" rows="3" placeholder="Masukkan Deskripsi" maxlength="500"
                            autocomplete="off">{{ old('deskripsi', isset($data) ? $data->deskripsi : '') }}</textarea>
                        <div class="text-muted fs-7">Masukkan deskripsi jika hero section ada deskripsi banner.</div>
                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Warna Text -->
                    <div class="mb-10 fv-row">
                        <label class="required form-label">Warna Text</label>
                        <select class="form-select mb-2" name="warna_text" id="warna_text" required>
                            <option value="Light" {{ old('warna_text', isset($data) ? $data->warna_text : '') == 'Light' ? 'selected' : '' }}>Light</option>
                            <option value="Dark" {{ old('warna_text', isset($data) ? $data->warna_text : '') == 'Dark' ? 'selected' : '' }}>Dark</option>
                        </select>
                        @error('warna_text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Kategori -->
                    <div class="mb-10 fv-row">
                        <label class="required form-label">Kategori</label>
                        <select class="form-select mb-2" name="kategori" id="kategori" required>
                            @foreach ($kategoriList as $item)
                                <option value="{{ $item->nama }}" {{ old('kategori', isset($data) ? $data->kategori : '') == $item->nama ? 'selected' : '' }}>{{ $item->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('kategori')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Actions -->
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
        </div>
    </form>
@endsection
{{-- CONTENT::END --}}

@push('scripts')
    <script>
        "use strict";

        var KTAppBannerSave = function() {
            var form;
            var submitButton;
            var cancelButton;

            // Initialize elements
            var initElements = function() {
                form = document.querySelector('#kt_banner_form');
                submitButton = document.querySelector('#kt_banner_submit');
                cancelButton = document.querySelector('#kt_banner_cancel');
            };

            // Handle form submission
            var handleFormSubmit = function() {
                if (!form) return;

                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // Show loading indicator
                    submitButton.setAttribute('data-kt-indicator', 'on');
                    submitButton.disabled = true;

                    // Submit form
                    form.submit();
                });
            };

            // Handle cancel action
            var handleCancel = function() {
                if (!cancelButton) return;

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

            // Handle URL preview
            var handleUrlPreview = function() {
                const urlInput = document.querySelector('input[name="url"]');
                if (!urlInput) return;

                const previewText = document.createElement('span');
                const previewLink = document.createElement('a');
                previewText.id = 'url_preview_text';
                previewLink.id = 'url_preview_link';
                previewLink.className = 'text-primary d-none';
                previewLink.innerHTML = '<i class="ki-outline ki-external-link fs-7 me-1"></i>Buka URL';
                previewLink.target = '_blank';

                const noticeDiv = document.createElement('div');
                noticeDiv.className = 'notice d-flex bg-light-primary rounded border-primary border border-dashed p-6 mb-10';
                noticeDiv.innerHTML = `
                    <i class="ki-outline ki-information-5 fs-2tx text-primary me-4"></i>
                    <div class="d-flex flex-stack flex-grow-1">
                        <div class="fw-semibold">
                            <h4 class="text-gray-900 fw-bold">Preview URL</h4>
                            <div class="fs-6 text-gray-700"></div>
                        </div>
                    </div>
                `;
                noticeDiv.querySelector('.fs-6').appendChild(previewText);
                noticeDiv.querySelector('.fs-6').appendChild(previewLink);
                urlInput.parentNode.parentNode.insertAdjacentElement('afterend', noticeDiv);

                urlInput.addEventListener('input', function() {
                    const url = this.value.trim();
                    updateUrlPreview(url, previewText, previewLink);
                });

                const initialUrl = urlInput.value;
                if (initialUrl) updateUrlPreview(initialUrl, previewText, previewLink);
            };

            // Update URL preview
            var updateUrlPreview = function(url, previewText, previewLink) {
                if (url && isValidURL(url)) {
                    previewText.textContent = url;
                    previewLink.href = url;
                    previewLink.classList.remove('d-none');
                } else if (url) {
                    previewText.textContent = url + ' (Format URL tidak valid)';
                    previewLink.classList.add('d-none');
                } else {
                    previewText.textContent = 'Masukkan URL untuk melihat preview';
                    previewLink.classList.add('d-none');
                }
            };

            // Validate URL
            var isValidURL = function(string) {
                try {
                    new URL(string);
                    return string.startsWith('http://') || string.startsWith('https://');
                } catch (_) {
                    return false;
                }
            };

            // Public methods
            return {
                init: function() {
                    initElements();
                    handleFormSubmit();
                    handleCancel();
                    handleUrlPreview();
                }
            };
        }();

        // On document ready
        document.addEventListener('DOMContentLoaded', function() {
            KTAppBannerSave.init();
        });
    </script>
@endpush
