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
                <li class="breadcrumb-item text-gray-700">Master Data</li>
                {{-- end::Item --}}
                {{-- begin::Item --}}
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                {{-- end::Item --}}
                {{-- begin::Item --}}
                <li class="breadcrumb-item text-gray-700">
                    <a href="{{ route('prt.apps.mst.tags.index') }}" class="text-gray-700 text-hover-primary">Kategori</a>
                </li>
                {{-- end::Item --}}
                {{-- begin::Item --}}
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                {{-- end::Item --}}
                {{-- begin::Item --}}
                <li class="breadcrumb-item text-gray-700">
                    <a href="{{ route('prt.apps.mst.tags.sub.index', [$uuid_tags_enc]) }}" class="text-gray-700 text-hover-primary">Sub Kategori</a>
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
    <form id="kt_kategori_sub_form" class="form d-flex flex-column flex-lg-row"
        action="{{ isset($data) ? route('prt.apps.mst.tags.sub.update', [$uuid_tags_enc, $uuid_enc]) : route('prt.apps.mst.tags.sub.store', [$uuid_tags_enc]) }}" method="POST">
        @csrf
        @isset($data)
            @method('PUT')
        @endisset

        {{-- begin::Aside column --}}
        <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
            {{-- begin::Parent Category Info --}}
            <div class="card card-flush py-4">
                {{-- begin::Card header --}}
                <div class="card-header">
                    {{-- begin::Card title --}}
                    <div class="card-title">
                        <h2>Kategori Induk</h2>
                    </div>
                    {{-- end::Card title --}}
                </div>
                {{-- end::Card header --}}
                {{-- begin::Card body --}}
                <div class="card-body text-center pt-0">
                    {{-- begin::Parent info --}}
                    <div class="d-flex flex-column gap-3">
                        <div class="symbol symbol-100px symbol-circle mx-auto">
                            <div class="symbol-label fs-2 fw-semibold text-success bg-light-success">
                                {{ strtoupper(substr($master_kategori->nama, 0, 2)) }}
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="fs-4 fw-bold text-gray-900 mb-1">{{ $master_kategori->nama }}</div>
                            <div class="fs-7 fw-semibold text-muted mb-2">{{ $master_kategori->type }}</div>
                            <div class="badge badge-light-primary">{{ $master_kategori->slug }}</div>
                        </div>
                    </div>
                    {{-- end::Parent info --}}
                </div>
                {{-- end::Card body --}}
            </div>
            {{-- end::Parent Category Info --}}

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
                    <select class="form-select mb-2" data-control="select2" data-hide-search="true" data-placeholder="Pilih Status" id="kt_kategori_sub_status" name="status" disabled>
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

            {{-- begin::Information settings --}}
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
                    {{-- begin::Template --}}
                    <div class="d-flex flex-column gap-5">
                        <div class="d-flex flex-stack">
                            <span class="fw-bold text-gray-600">Slug:</span>
                            <span class="text-gray-800 fw-bold" id="kategori_sub_slug_preview">-</span>
                        </div>
                        <div class="d-flex flex-stack">
                            <span class="fw-bold text-gray-600">Dibuat:</span>
                            <span class="text-gray-800 fw-bold">
                                {{ isset($data) ? $data->created_at->format('d M Y H:i') : 'Akan diatur otomatis' }}
                            </span>
                        </div>
                        @isset($data)
                            <div class="d-flex flex-stack">
                                <span class="fw-bold text-gray-600">Diperbarui:</span>
                                <span class="text-gray-800 fw-bold">{{ $data->updated_at->format('d M Y H:i') }}</span>
                            </div>
                        @endisset
                    </div>
                    {{-- end::Template --}}
                </div>
                {{-- end::Card body --}}
            </div>
            {{-- end::Information settings --}}
        </div>
        {{-- end::Aside column --}}

        {{-- begin::Main column --}}
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            {{-- begin::General options --}}
            <div class="card card-flush py-4">
                {{-- begin::Card header --}}
                <div class="card-header">
                    <div class="card-title">
                        <h2>Detail Sub Kategori</h2>
                    </div>
                </div>
                {{-- end::Card header --}}
                {{-- begin::Card body --}}
                <div class="card-body pt-0">
                    {{-- begin::Input group --}}
                    <div class="mb-10 fv-row">
                        {{-- begin::Label --}}
                        <label class="required form-label">Nama Sub Kategori</label>
                        {{-- end::Label --}}
                        {{-- begin::Input --}}
                        <input type="text" name="nama" class="form-control mb-2 @error('nama') is-invalid @enderror" placeholder="Masukkan nama sub kategori"
                            value="{{ old('nama', isset($data) ? $data->nama : '') }}" maxlength="100" autocomplete="off" required />
                        {{-- end::Input --}}
                        {{-- begin::Description --}}
                        <div class="text-muted fs-7">
                            Nama sub kategori harus unik dalam kategori induk "{{ $master_kategori->nama }}".
                        </div>
                        {{-- end::Description --}}
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- end::Input group --}}

                    {{-- begin::Parent category info --}}
                    <div class="mb-10">
                        {{-- begin::Label --}}
                        <label class="form-label">Kategori Induk</label>
                        {{-- end::Label --}}
                        {{-- begin::Info display --}}
                        <div class="d-flex align-items-center bg-light-primary rounded p-5">
                            <div class="symbol symbol-50px me-5">
                                <div class="symbol-label fs-3 fw-bold text-primary bg-light-primary">
                                    {{ strtoupper(substr($master_kategori->nama, 0, 1)) }}
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-gray-900 fs-6 mb-1">{{ $master_kategori->nama }}</div>
                                <div class="text-muted fs-7">
                                    <span class="badge badge-light me-2">{{ $master_kategori->type }}</span>
                                    <span class="text-gray-600">{{ $master_kategori->slug }}</span>
                                </div>
                            </div>
                        </div>
                        {{-- end::Info display --}}
                        {{-- begin::Description --}}
                        <div class="text-muted fs-7 mt-2">
                            Sub kategori ini akan menjadi bagian dari kategori "{{ $master_kategori->nama }}".
                        </div>
                        {{-- end::Description --}}
                    </div>
                    {{-- end::Parent category info --}}
                </div>
                {{-- end::Card body --}}
            </div>
            {{-- end::General options --}}

            {{-- begin::Actions --}}
            <div class="d-flex justify-content-end">
                {{-- begin::Button --}}
                <a href="{{ route('prt.apps.mst.tags.sub.index', [$uuid_tags_enc]) }}" id="kt_kategori_sub_cancel" class="btn btn-light me-5">
                    <i class="ki-outline ki-arrow-left fs-2"></i>Batal
                </a>
                {{-- end::Button --}}
                {{-- begin::Button --}}
                <button type="submit" id="kt_kategori_sub_submit" class="btn btn-primary">
                    <span class="indicator-label">
                        <i class="ki-outline ki-check fs-2"></i>{{ $submit }}
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
        var KTAppKategoriSubSave = function() {
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
                    var nama = form.querySelector('input[name="nama"]').value.trim();

                    var isValid = true;
                    var errorMessage = '';

                    // Validate nama
                    if (!nama) {
                        isValid = false;
                        errorMessage = 'Nama sub kategori wajib diisi';
                    } else if (nama.length > 100) {
                        isValid = false;
                        errorMessage = 'Nama sub kategori maksimal 100 karakter';
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

            // Handle slug and URL preview
            var handlePreview = function() {
                const namaInput = document.querySelector('input[name="nama"]');
                const slugPreview = document.querySelector('#kategori_sub_slug_preview');

                if (namaInput && slugPreview) {
                    namaInput.addEventListener('input', function() {
                        const slug = createSlug(this.value);
                        slugPreview.textContent = slug || '-';
                    });

                    // Set initial slug if editing
                    const initialNama = namaInput.value;
                    if (initialNama) {
                        const initialSlug = createSlug(initialNama);
                        slugPreview.textContent = initialSlug;
                    }
                }
            }

            // Create slug from string
            var createSlug = function(str) {
                return str
                    .toLowerCase()
                    .trim()
                    .replace(/[^\w\s-]/g, '') // Remove special characters
                    .replace(/[\s_-]+/g, '-') // Replace spaces and underscores with hyphens
                    .replace(/^-+|-+$/g, ''); // Remove leading/trailing hyphens
            }

            // Public methods
            return {
                init: function() {
                    // Elements
                    form = document.querySelector('#kt_kategori_sub_form');
                    submitButton = document.querySelector('#kt_kategori_sub_submit');
                    cancelButton = document.querySelector('#kt_kategori_sub_cancel');

                    if (!form) {
                        return;
                    }

                    handleForm();
                    handlePreview();
                }
            };
        }();

        // On document ready
        KTUtil.onDOMContentLoaded(function() {
            KTAppKategoriSubSave.init();
        });
    </script>
@endpush
{{-- SCRIPTS::END --}}
