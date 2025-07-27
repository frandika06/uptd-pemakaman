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
        method="POST" enctype="multipart/form-data">
        @csrf
        @isset($data)
            @method('PUT')
        @endisset

        {{-- Hidden field for deleted dokumen IDs (only if dokumen feature enabled) --}}
        @if (isset($kategoriDokumen) && $kategoriDokumen->count() > 0)
            <input type="hidden" name="deleted_dokumen_ids" id="deleted_dokumen_ids" value="">
        @endif

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

                            {{-- Show dokumen count if available --}}
                            @if (isset($existingDokumen))
                                <div class="m-0 p-0">
                                    <span class="fw-bold text-gray-600">Total Dokumen:</span><br />
                                    <span class="text-gray-800 fw-bold">
                                        <span class="badge badge-light-info" id="dokumen_count">
                                            {{ $existingDokumen->count() ?? 0 }} Dokumen
                                        </span>
                                    </span>
                                </div>
                            @endif
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
                            <!-- Display Lahan as text in edit mode -->
                            <input type="hidden" name="uuid_lahan" value="{{ old('uuid_lahan', $data->uuid_lahan) }}">
                            <div class="form-control form-control-solid">
                                {{ $data->Lahan ? $data->Lahan->kode_lahan . ' (' . ($data->Lahan->Tpu ? $data->Lahan->Tpu->nama : '-') . ')' : '-' }}
                            </div>
                        @else
                            <!-- Show select dropdown in create mode -->
                            <select class="form-select mb-2 @error('uuid_lahan') is-invalid @enderror" data-control="select2" data-placeholder="Pilih Lahan" name="uuid_lahan"
                                id="kt_sarpras_lahan" required>
                                <option value="" disabled {{ !old('uuid_lahan') ? 'selected' : '' }}>Pilih Lahan</option>
                                @foreach ($lahans as $lahan)
                                    <option value="{{ $lahan->uuid }}" {{ old('uuid_lahan') === $lahan->uuid ? 'selected' : '' }}>
                                        {{ $lahan->kode_lahan }} ({{ $lahan->Tpu->nama ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        <div class="text-muted fs-7">Pilih lahan tempat sarpras berada.</div>
                        @error('uuid_lahan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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

            {{-- begin::Dokumen Pendukung (Optional) --}}
            @if (isset($kategoriDokumen) && $kategoriDokumen->count() > 0)
                <div class="card card-flush py-4">
                    {{-- begin::Card header --}}
                    <div class="card-header">
                        <div class="card-title">
                            <h2>Dokumen Pendukung</h2>
                        </div>
                        <div class="card-toolbar">
                            <button type="button" class="btn btn-sm btn-light-primary" id="add_dokumen_btn">
                                <i class="ki-outline ki-plus fs-2"></i>Tambah Dokumen
                            </button>
                        </div>
                    </div>
                    {{-- end::Card header --}}
                    {{-- begin::Card body --}}
                    <div class="card-body pt-0">
                        <div class="text-muted fs-7 mb-5">
                            Upload dokumen pendukung untuk sarpras ini. Format yang didukung: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, JPEG, PNG, dll.
                            Maksimal ukuran file: 10MB per file.
                        </div>

                        {{-- Existing Dokumen --}}
                        @if (isset($existingDokumen) && $existingDokumen->count() > 0)
                            @foreach ($existingDokumen as $index => $dokumen)
                                <div class="dokumen-item border border-gray-300 rounded p-4 mb-4" data-existing-id="{{ $dokumen->uuid }}">
                                    <input type="hidden" name="existing_dokumen_id[]" value="{{ $dokumen->uuid }}">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label required">Kategori Dokumen</label>
                                            <select class="form-select form-select-solid" name="dokumen_kategori[]" required>
                                                <option value="">Pilih Kategori...</option>
                                                @foreach ($kategoriDokumen as $kategori)
                                                    <option value="{{ $kategori->uuid }}" {{ $dokumen->kategori == $kategori->uuid ? 'selected' : '' }}>
                                                        {{ $kategori->nama }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-8 mb-3">
                                            <label class="form-label required">Nama Dokumen</label>
                                            <input type="text" class="form-control" name="dokumen_nama[]" value="{{ $dokumen->nama_file }}" placeholder="Nama dokumen..."
                                                maxlength="100" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">File Dokumen</label>
                                            <input type="file" class="form-control" name="dokumen_file[]"
                                                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.zip,.rar">
                                            <div class="text-muted fs-8 mt-1">
                                                File saat ini: <a href="{{ asset('storage/' . $dokumen->url) }}" target="_blank" class="text-primary">
                                                    {{ $dokumen->nama_file }}.{{ $dokumen->tipe }}
                                                </a> ({{ number_format($dokumen->size / 1024, 2) }} KB)
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Deskripsi</label>
                                            <textarea class="form-control" name="dokumen_deskripsi[]" rows="2" placeholder="Deskripsi dokumen..." maxlength="500">{{ $dokumen->deskripsi }}</textarea>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-sm btn-light-danger remove_dokumen_btn">
                                            <i class="ki-outline ki-trash fs-4"></i>Hapus
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        {{-- Container for new dokumen --}}
                        <div id="dokumen_container">
                            {{-- Dynamic dokumen items will be added here --}}
                        </div>

                        {{-- Template for new dokumen item --}}
                        <div id="dokumen_template" style="display: none;">
                            <div class="dokumen-item border border-gray-300 rounded p-4 mb-4">
                                <input type="hidden" name="existing_dokumen_id[]" value="">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label required">Kategori Dokumen</label>
                                        <select class="form-select form-select-solid" name="dokumen_kategori[]" required>
                                            <option value="">Pilih Kategori...</option>
                                            @foreach ($kategoriDokumen as $kategori)
                                                <option value="{{ $kategori->uuid }}">{{ $kategori->nama }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label required">Nama Dokumen</label>
                                        <input type="text" class="form-control" name="dokumen_nama[]" placeholder="Nama dokumen..." maxlength="100" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">File Dokumen</label>
                                        <input type="file" class="form-control" name="dokumen_file[]"
                                            accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.zip,.rar" required>
                                        <div class="text-muted fs-8 mt-1">Maksimal 10MB. Format: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, JPEG, PNG, GIF, ZIP, RAR</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Deskripsi</label>
                                        <textarea class="form-control" name="dokumen_deskripsi[]" rows="2" placeholder="Deskripsi dokumen..." maxlength="500"></textarea>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-sm btn-light-danger remove_dokumen_btn">
                                        <i class="ki-outline ki-trash fs-4"></i>Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- end::Card body --}}
                </div>
            @endif
            {{-- end::Dokumen Pendukung --}}

            <!-- begin::Actions -->
            <div class="d-flex justify-content-end">
                <a href="{{ route('tpu.sarpras.index') }}" id="kt_sarpras_cancel" class="btn btn-light me-5">
                    <i class="ki-outline ki-arrow-left fs-2"></i>Batal
                </a>
                <button type="submit" id="kt_sarpras_submit" class="btn btn-primary">
                    <span class="indicator-label">
                        <i class="fa-solid fa-save me-2"></i>{{ isset($data) ? 'Perbarui' : 'Simpan' }}
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
                var dokumenCounter = 0;
                var deletedDokumenIds = [];

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
                    // Simple validation without FormValidation dependency
                    validator = {
                        validate: function() {
                            return new Promise(function(resolve) {
                                var isValid = true;
                                var errors = [];

                                // Validate Lahan
                                var lahanSelect = document.querySelector('select[name="uuid_lahan"]') || document.querySelector(
                                    'input[name="uuid_lahan"]');
                                if (!lahanSelect || !lahanSelect.value) {
                                    isValid = false;
                                    errors.push('Lahan harus dipilih');
                                    if (lahanSelect) lahanSelect.classList.add('is-invalid');
                                } else {
                                    if (lahanSelect) lahanSelect.classList.remove('is-invalid');
                                }

                                // Validate Nama Sarpras
                                var namaInput = document.querySelector('input[name="nama"]');
                                if (!namaInput.value.trim()) {
                                    isValid = false;
                                    errors.push('Nama sarpras harus diisi');
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

                                if (!isValid && errors.length > 0) {
                                    showError('Validasi gagal: ' + errors.join(', '));
                                }

                                resolve(isValid ? 'Valid' : 'Invalid');
                            });
                        }
                    };
                }

                // Initialize dokumen management (only if enabled)
                var initDokumenManagement = function() {
                    // Add dokumen button
                    var addBtn = document.getElementById('add_dokumen_btn');
                    if (addBtn) {
                        addBtn.addEventListener('click', function() {
                            addDokumenItem();
                        });
                    }

                    // Handle remove buttons
                    document.addEventListener('click', function(e) {
                        if (e.target.closest('.remove_dokumen_btn')) {
                            removeDokumenItem(e.target.closest('.remove_dokumen_btn'));
                        }
                    });
                };

                // Add new dokumen item
                var addDokumenItem = function() {
                    var template = document.getElementById('dokumen_template');
                    var container = document.getElementById('dokumen_container');
                    if (!template || !container) return;

                    var newItem = template.cloneNode(true);
                    newItem.id = 'dokumen_item_' + dokumenCounter;
                    newItem.style.display = 'block';
                    container.appendChild(newItem);
                    dokumenCounter++;
                    updateDokumenCount();
                };

                // Remove dokumen item
                var removeDokumenItem = function(button) {
                    var dokumenItem = button.closest('.dokumen-item');
                    var existingId = dokumenItem.getAttribute('data-existing-id');

                    if (existingId) {
                        // Add to deleted list if it's an existing dokumen
                        deletedDokumenIds.push(existingId);
                    }

                    dokumenItem.remove();
                    updateDokumenCount();
                };

                // Update dokumen count
                var updateDokumenCount = function() {
                    var count = document.querySelectorAll('.dokumen-item').length;
                    var countElement = document.getElementById('dokumen_count');
                    if (countElement) {
                        countElement.textContent = count + ' Dokumen';
                    }
                };

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

                var showSuccess = function(message) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert(message);
                    }
                }

                var handleSubmit = function() {
                    // Remove any existing event listeners
                    submitButton.onclick = null;

                    submitButton.addEventListener('click', function(e) {
                        e.preventDefault();
                        console.log('Submit button clicked');

                        if (validator) {
                            validator.validate().then(function(status) {
                                console.log('Validation status:', status);
                                if (status === 'Valid') {
                                    console.log('Form is valid, submitting...');

                                    // Update deleted dokumen ids if feature is enabled
                                    var deletedField = document.getElementById('deleted_dokumen_ids');
                                    if (deletedField) {
                                        deletedField.value = deletedDokumenIds.join(',');
                                    }

                                    submitButton.setAttribute('data-kt-indicator', 'on');
                                    submitButton.disabled = true;
                                    form.submit();
                                } else {
                                    console.log('Form validation failed');
                                }
                            });
                        } else {
                            console.log('No validator, submitting directly...');
                            // Fallback if validator is not available
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

                            // Initialize dokumen management (only if dokumen feature is enabled)
                            @if (isset($kategoriDokumen) && $kategoriDokumen->count() > 0)
                                initDokumenManagement();
                            @endif

                            console.log('KTSarprasForm initialized successfully');
                        } catch (error) {
                            console.error('Error initializing KTSarprasForm:', error);
                        }
                    }
                };
            }();

            // Initialize the form
            KTSarprasForm.init();
        });
    </script>
@endpush
