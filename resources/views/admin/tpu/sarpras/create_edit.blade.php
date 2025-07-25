@push('styles')
    <style>
        .form-control.is-invalid {
            border-color: #f1416c;
        }

        .fv-row .text-muted {
            font-size: 0.9rem;
        }

        .btn-primary .indicator-progress {
            display: none;
        }

        .btn-primary[data-kt-indicator="on"] .indicator-label {
            display: none;
        }

        .btn-primary[data-kt-indicator="on"] .indicator-progress {
            display: inline-flex;
            align-items: center;
        }
    </style>
@endpush

@extends('layouts.admin')

@section('title', $title)
@section('description', '{{ $title }}')

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
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                <li class="breadcrumb-item text-gray-700">Manajemen TPU</li>
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                <li class="breadcrumb-item text-gray-700">
                    <a href="{{ route('tpu.sarpras.index') }}" class="text-gray-700 text-hover-primary">Data Sarpras</a>
                </li>
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                <li class="breadcrumb-item text-gray-700">{{ isset($data) ? 'Edit' : 'Tambah' }}</li>
            </ul>
        </div>
    </div>
@endsection

@section('content')
    <form id="kt_sarpras_form" class="form d-flex flex-column flex-lg-row" action="{{ isset($data) ? route('tpu.sarpras.update', $uuid_enc) : route('tpu.sarpras.store') }}"
        method="POST">
        @csrf
        @isset($data)
            @method('PUT')
        @endisset

        <!-- begin::Aside column -->
        <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
            <!-- begin::Information settings -->
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Informasi</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="d-flex flex-column gap-5">
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
            <!-- end::Information settings -->
        </div>
        <!-- end::Aside column -->

        <!-- begin::Main column -->
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <!-- begin::General options -->
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Informasi Umum</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <!-- begin::Input group - Lahan -->
                    <div class="mb-10 fv-row">
                        <label class="required form-label">Lahan</label>
                        @if (isset($data))
                            <input type="text" class="form-control mb-2" value="{{ $data->Lahan->kode_lahan }} ({{ $data->Lahan->Tpu->nama ?? '-' }})" readonly />
                            <input type="hidden" name="uuid_lahan" value="{{ $data->uuid_lahan }}" />
                        @else
                            <select class="form-select mb-2 @error('uuid_lahan') is-invalid @enderror" data-control="select2" data-placeholder="Pilih Lahan" name="uuid_lahan"
                                id="kt_sarpras_lahan" required>
                                <option value="">Pilih Lahan</option>
                                @foreach ($lahans as $lahan)
                                    <option value="{{ $lahan->uuid }}" {{ old('uuid_lahan') === $lahan->uuid ? 'selected' : '' }}>
                                        {{ $lahan->kode_lahan }} ({{ $lahan->Tpu->nama ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('uuid_lahan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                        <div class="text-muted fs-7">Pilih lahan tempat sarpras berada.</div>
                    </div>
                    <!-- end::Input group - Lahan -->

                    <!-- begin::Input group - Nama Sarpras -->
                    <div class="mb-10 fv-row">
                        <label class="required form-label">Nama Sarpras</label>
                        <input type="text" name="nama" class="form-control mb-2 @error('nama') is-invalid @enderror" placeholder="Masukkan nama sarpras"
                            value="{{ old('nama', isset($data) ? $data->nama : '') }}" maxlength="255" autocomplete="off" required />
                        <div class="text-muted fs-7">Nama sarpras, misalnya: Mushola, Kantor Pengelola, dll.</div>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- end::Input group - Nama Sarpras -->

                    <!-- begin::Input group - Jenis Sarpras -->
                    <div class="mb-10 fv-row">
                        <label class="form-label">Jenis Sarpras</label>
                        <select class="form-select mb-2 @error('jenis_sarpras') is-invalid @enderror" data-control="select2" data-placeholder="Pilih Jenis Sarpras" name="jenis_sarpras"
                            id="kt_sarpras_jenis">
                            <option value="">Pilih Jenis Sarpras</option>
                            @foreach ($jenis_sarpras as $jenis)
                                <option value="{{ $jenis->nama }}" {{ old('jenis_sarpras', isset($data) ? $data->jenis_sarpras : '') === $jenis->nama ? 'selected' : '' }}>
                                    {{ $jenis->nama }}
                                </option>
                            @endforeach
                        </select>
                        <div class="text-muted fs-7">Pilih jenis sarpras (opsional).</div>
                        @error('jenis_sarpras')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- end::Input group - Jenis Sarpras -->

                    <!-- begin::Input group - Luas -->
                    <div class="mb-10 fv-row">
                        <label class="form-label">Luas Sarpras (m²)</label>
                        <input type="number" name="luas_m2" class="form-control mb-2 @error('luas_m2') is-invalid @enderror" placeholder="Masukkan luas sarpras"
                            value="{{ old('luas_m2', isset($data) ? $data->luas_m2 : '') }}" step="0.01" min="0" autocomplete="off" />
                        <div class="text-muted fs-7">Luas sarpras dalam meter persegi (opsional).</div>
                        @error('luas_m2')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- end::Input group - Luas -->
                </div>
            </div>
            <!-- end::General options -->

            <!-- begin::Deskripsi -->
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Deskripsi</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <!-- begin::Input group - Deskripsi -->
                    <div class="mb-10 fv-row">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control mb-2 @error('deskripsi') is-invalid @enderror" rows="4"
                            placeholder="Masukkan deskripsi atau keterangan tambahan tentang sarpras...">{{ old('deskripsi', isset($data) ? $data->deskripsi : '') }}</textarea>
                        <div class="text-muted fs-7">Deskripsi atau keterangan tambahan mengenai sarpras (opsional).</div>
                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- end::Input group - Deskripsi -->
                </div>
            </div>
            <!-- end::Deskripsi -->

            <!-- begin::Actions -->
            <div class="d-flex justify-content-end">
                <a href="{{ route('tpu.sarpras.index') }}" id="kt_sarpras_cancel" class="btn btn-light me-5">
                    <i class="ki-outline ki-arrow-left fs-2"></i>Batal
                </a>
                <button type="submit" id="kt_sarpras_submit" class="btn btn-primary">
                    <span class="indicator-label">
                        <i class="ki-outline ki-check-circle fs-2 me-2"></i>{{ isset($data) ? 'Perbarui' : 'Simpan' }}
                    </span>
                    <span class="indicator-progress">
                        Please wait...
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                    </span>
                </button>
            </div>
            <!-- end::Actions -->
        </div>
        <!-- end::Main column -->
    </form>
@endsection

@push('scripts')
    <script>
        "use strict";

        document.addEventListener('DOMContentLoaded', function() {
            var KTSarprasForm = function() {
                var form;
                var submitButton;
                var cancelButton;
                var validator;

                var initForm = function() {
                    form = document.querySelector('#kt_sarpras_form');
                    submitButton = document.querySelector('#kt_sarpras_submit');
                    cancelButton = document.querySelector('#kt_sarpras_cancel');

                    console.log('Form initialized:', {
                        form: !!form,
                        submitButton: !!submitButton,
                        cancelButton: !!cancelButton
                    });
                }

                var initValidation = function() {
                    validator = {
                        validate: function() {
                            return new Promise(function(resolve) {
                                var isValid = true;
                                var errors = [];

                                // Validate Lahan (only for create mode)
                                @if (!isset($data))
                                    var lahanSelect = document.querySelector('select[name="uuid_lahan"]');
                                    if (!lahanSelect.value) {
                                        isValid = false;
                                        errors.push('Lahan harus dipilih');
                                        lahanSelect.classList.add('is-invalid');
                                    } else {
                                        lahanSelect.classList.remove('is-invalid');
                                    }
                                @endif

                                // Validate Nama Sarpras
                                var namaInput = document.querySelector('input[name="nama"]');
                                if (!namaInput.value.trim()) {
                                    isValid = false;
                                    errors.push('Nama sarpras harus diisi');
                                    namaInput.classList.add('is-invalid');
                                } else if (namaInput.value.length > 255) {
                                    isValid = false;
                                    errors.push('Nama sarpras maksimal 255 karakter');
                                    namaInput.classList.add('is-invalid');
                                } else {
                                    namaInput.classList.remove('is-invalid');
                                }

                                // Validate Luas (optional)
                                var luasInput = document.querySelector('input[name="luas_m2"]');
                                if (luasInput.value) {
                                    var luasValue = parseFloat(luasInput.value);
                                    if (isNaN(luasValue) || luasValue < 0) {
                                        isValid = false;
                                        errors.push('Luas sarpras harus 0 atau lebih');
                                        luasInput.classList.add('is-invalid');
                                    } else {
                                        luasInput.classList.remove('is-invalid');
                                    }
                                }

                                // Validate Jenis Sarpras (optional, but check if valid if selected)
                                var jenisSelect = document.querySelector('select[name="jenis_sarpras"]');
                                if (jenisSelect.value) {
                                    var validOptions = Array.from(jenisSelect.options).map(opt => opt.value);
                                    if (!validOptions.includes(jenisSelect.value)) {
                                        isValid = false;
                                        errors.push('Jenis sarpras tidak valid');
                                        jenisSelect.classList.add('is-invalid');
                                    } else {
                                        jenisSelect.classList.remove('is-invalid');
                                    }
                                }

                                if (!isValid && errors.length > 0) {
                                    showError('Validasi gagal: ' + errors.join(', '));
                                }

                                resolve(isValid ? 'Valid' : 'Invalid');
                            });
                        }
                    };
                }

                var showError = function(message) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: message,
                            timer: 3000,
                            showConfirmButton: false
                        });
                    } else {
                        alert(message);
                    }
                }

                var handleSubmit = function() {
                    submitButton.onclick = null;

                    submitButton.addEventListener('click', function(e) {
                        e.preventDefault();
                        console.log('Submit button clicked');

                        if (validator) {
                            validator.validate().then(function(status) {
                                console.log('Validation status:', status);
                                if (status === 'Valid') {
                                    console.log('Form is valid, submitting...');
                                    submitButton.setAttribute('data-kt-indicator', 'on');
                                    submitButton.disabled = true;
                                    form.submit();
                                } else {
                                    console.log('Form validation failed');
                                }
                            });
                        } else {
                            console.log('No validator, submitting directly...');
                            submitButton.setAttribute('data-kt-indicator', 'on');
                            submitButton.disabled = true;
                            form.submit();
                        }
                    });
                }

                var handleCancel = function() {
                    if (!cancelButton) return;

                    cancelButton.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (typeof Swal !== 'undefined') {
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
                        } else {
                            if (confirm("Apakah Anda yakin ingin membatalkan?")) {
                                window.location = cancelButton.getAttribute('href');
                            }
                        }
                    });
                }

                return {
                    init: function() {
                        try {
                            console.log('Initializing KTSarprasForm...');
                            initForm();
                            initValidation();
                            handleSubmit();
                            handleCancel();
                            console.log('KTSarprasForm initialized successfully');
                        } catch (error) {
                            console.error('Error initializing KTSarprasForm:', error);
                        }
                    }
                };
            }();

            KTSarprasForm.init();
        });
    </script>
@endpush
