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
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                <li class="breadcrumb-item text-gray-700">Data Utama TPU</li>
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                <li class="breadcrumb-item text-gray-700">
                    <a href="{{ route('tpu.makam.index') }}" class="text-gray-700 text-hover-primary">Data Makam</a>
                </li>
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                <li class="breadcrumb-item text-gray-700">{{ isset($data) ? 'Edit' : 'Tambah' }}</li>
            </ul>
        </div>
    </div>
@endsection
{{-- TOOLBAR::END --}}

{{-- CONTENT::BEGIN --}}
@section('content')
    <form id="kt_makam_form" class="form d-flex flex-column flex-lg-row" action="{{ isset($data) ? route('tpu.makam.update', $uuid_enc) : route('tpu.makam.store') }}" method="POST"
        enctype="multipart/form-data">
        @csrf
        @if (isset($data))
            @method('PUT')
        @endif

        <!-- begin::Aside column -->
        <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
            <!-- begin::Information settings -->
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Informasi Status</h2>
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

            <!-- begin::Status settings -->
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Status Makam</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="fv-row">
                        <select class="form-select mb-2 @error('status_makam') is-invalid @enderror" data-control="select2" data-placeholder="Pilih Status Makam" name="status_makam"
                            id="kt_makam_status" required>
                            <option></option>
                            @foreach ($statusMakam as $status)
                                <option value="{{ $status->nama }}" {{ old('status_makam', isset($data) ? $data->status_makam : '') == $status->nama ? 'selected' : '' }}>
                                    {{ $status->nama }}
                                </option>
                            @endforeach
                        </select>
                        <div class="text-muted fs-7">Pilih status makam saat ini.</div>
                        @error('status_makam')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <!-- end::Status settings -->

            <!-- begin::Calculation info -->
            <div class="card card-flush py-4" id="calculation_info_card" style="display: none;">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Informasi Perhitungan</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div id="calculation_details" class="text-muted fs-7" style="white-space: pre-line;"></div>
                </div>
            </div>
            <!-- end::Calculation info -->
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
                            <input type="hidden" name="uuid_lahan" value="{{ $data->uuid_lahan }}">
                            <input type="text" class="form-control mb-2"
                                value="{{ $data->lahan->kode_lahan }} - {{ $data->lahan->Tpu->nama }} ({{ ucfirst(str_replace('_', ' ', $data->lahan->Tpu->jenis_tpu)) }})" readonly
                                required />
                            <div class="text-muted fs-7">Lahan tidak dapat diubah saat edit.</div>
                        @else
                            <select class="form-select mb-2 @error('uuid_lahan') is-invalid @enderror" data-control="select2" data-placeholder="Pilih Lahan" name="uuid_lahan"
                                id="kt_makam_lahan" required>
                                <option></option>
                                @foreach ($lahans as $lahan)
                                    <option value="{{ $lahan->uuid }}" data-tpu="{{ $lahan->Tpu->nama }}" data-jenis="{{ $lahan->Tpu->jenis_tpu }}"
                                        data-luas="{{ $lahan->luas_m2 }}" {{ old('uuid_lahan') == $lahan->uuid ? 'selected' : '' }}>
                                        {{ $lahan->kode_lahan }} - {{ $lahan->Tpu->nama }} ({{ ucfirst(str_replace('_', ' ', $lahan->Tpu->jenis_tpu)) }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="text-muted fs-7">Pilih lahan tempat makam berada.</div>
                            @error('uuid_lahan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>
                    <!-- end::Input group - Lahan -->

                    <!-- begin::TPU Info -->
                    <div class="mb-10" id="tpu_info_container" style="display: none;">
                        <div class="alert alert-info d-flex align-items-center p-5">
                            <i class="ki-outline ki-information-5 fs-2hx text-info me-4"></i>
                            <div class="d-flex flex-column">
                                <h4 class="mb-1 text-info">Informasi TPU</h4>
                                <span id="tpu_info_text"></span>
                            </div>
                        </div>
                    </div>
                    <!-- end::TPU Info -->

                    <!-- begin::Row - Dimensi -->
                    <div class="row g-9 mb-10">
                        <!-- begin::Col - Panjang -->
                        <div class="col-md-6 fv-row">
                            <label class="required form-label">Panjang (meter)</label>
                            <input type="number" step="0.01" min="0.01" name="panjang_m" id="panjang_input" class="form-control mb-2 @error('panjang_m') is-invalid @enderror"
                                placeholder="Masukkan panjang makam" value="{{ old('panjang_m', isset($data) ? number_format($data->panjang_m, 2, '.', '') : '') }}" required />
                            <div class="text-muted fs-7">Panjang makam dalam meter.</div>
                            @error('panjang_m')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- end::Col - Panjang -->

                        <!-- begin::Col - Lebar -->
                        <div class="col-md-6 fv-row">
                            <label class="required form-label">Lebar (meter)</label>
                            <input type="number" step="0.01" min="0.01" name="lebar_m" id="lebar_input" class="form-control mb-2 @error('lebar_m') is-invalid @enderror"
                                placeholder="Masukkan lebar makam" value="{{ old('lebar_m', isset($data) ? number_format($data->lebar_m, 2, '.', '') : '') }}" required />
                            <div class="text-muted fs-7">Lebar makam dalam meter.</div>
                            @error('lebar_m')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- end::Col - Lebar -->
                    </div>
                    <!-- end::Row - Dimensi -->

                    <!-- begin::Input group - Luas (readonly) -->
                    <div class="mb-10 fv-row">
                        <label class="form-label">Luas Makam (m²)</label>
                        <input type="text" name="luas_display" id="luas_display" class="form-control mb-2" placeholder="Akan dihitung otomatis" readonly />
                        <input type="hidden" name="luas_m2" id="luas_m2" value="{{ old('luas_m2', isset($data) ? number_format($data->luas_m2, 2, '.', '') : '') }}" />
                        <div class="text-muted fs-7">Luas akan dihitung otomatis berdasarkan panjang × lebar.</div>
                    </div>
                    <!-- end::Input group - Luas -->

                    <!-- begin::Input group - Kapasitas -->
                    <div class="mb-10 fv-row">
                        <label class="required form-label">Kapasitas (orang)</label>
                        <div class="input-group">
                            <input type="number" min="1" name="kapasitas" id="kapasitas_input" class="form-control @error('kapasitas') is-invalid @enderror"
                                placeholder="Akan dihitung otomatis" value="{{ old('kapasitas', isset($data) ? $data->kapasitas : '') }}" />
                            <button type="button" id="auto_calculate_btn" class="btn btn-light-primary">
                                <i class="ki-outline ki-calculator fs-2"></i>
                                Auto
                            </button>
                        </div>
                        <div class="text-muted fs-7">Kapasitas akan dihitung berdasarkan jenis TPU dan luas makam. Klik "Auto" untuk perhitungan otomatis.</div>
                        @error('kapasitas')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- end::Input group - Kapasitas -->

                    <!-- begin::Input group - Keterangan -->
                    <div class="mb-10 fv-row">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="4" placeholder="Masukkan keterangan tambahan (opsional)">{{ old('keterangan', isset($data) ? $data->keterangan : '') }}</textarea>
                        <div class="text-muted fs-7">Keterangan atau catatan tambahan tentang makam ini.</div>
                        @error('keterangan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- end::Input group - Keterangan -->
                </div>
            </div>
            <!-- end::General options -->

            <div class="d-flex justify-content-end">
                <a href="{{ route('tpu.makam.index') }}" id="kt_makam_cancel" class="btn btn-light me-5">Batal</a>
                <button type="submit" id="kt_makam_submit" class="btn btn-primary">
                    <span class="indicator-label">{{ $submit }}</span>
                    <span class="indicator-progress">Please wait...
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                    </span>
                </button>
            </div>
        </div>
        <!-- end::Main column -->
    </form>

    @isset($data)
        {{-- Hidden data untuk edit mode --}}
        <script type="application/json" id="edit-data">
            {
                "uuid_lahan": "{{ $data->uuid_lahan }}",
                "panjang_m": {{ $data->panjang_m }},
                "lebar_m": {{ $data->lebar_m }},
                "luas_m2": {{ $data->luas_m2 }},
                "kapasitas": {{ $data->kapasitas ?? 0 }}
            }
        </script>
    @endisset
@endsection

{{-- SCRIPTS::BEGIN --}}
@push('scripts')
    <script>
        "use strict";

        var KTMakamCreateEdit = function() {
            var form;
            var submitButton;
            var cancelButton; // Tambahkan variabel untuk tombol batal
            var validator;
            var lahanSelect;
            var panjangInput;
            var lebarInput;
            var kapasitasInput;
            var autoCalculateBtn;
            var luasDisplay;
            var luasHidden;
            var isEditMode = false;
            var editData = null;

            var initForm = function() {
                // Check if edit mode
                var editDataElement = document.getElementById('edit-data');
                if (editDataElement) {
                    isEditMode = true;
                    try {
                        editData = JSON.parse(editDataElement.textContent);
                        console.log('Edit data loaded:', editData);
                    } catch (e) {
                        console.error('Error parsing edit data:', e);
                    }
                }

                // Calculate luas automatically
                function calculateLuas() {
                    var panjang = parseFloat(panjangInput.value) || 0;
                    var lebar = parseFloat(lebarInput.value) || 0;
                    var luas = panjang * lebar;

                    if (luas > 0) {
                        luasDisplay.value = luas.toFixed(2) + ' m²';
                        luasHidden.value = luas.toFixed(2);
                    } else {
                        luasDisplay.value = '';
                        luasHidden.value = '';
                    }

                    return luas;
                }

                // Show TPU info when lahan is selected
                function showTpuInfo() {
                    if (isEditMode) return; // Skip for edit mode

                    var selectedOption = lahanSelect.options[lahanSelect.selectedIndex];
                    if (selectedOption && selectedOption.value) {
                        var tpu = selectedOption.getAttribute('data-tpu');
                        var jenis = selectedOption.getAttribute('data-jenis');
                        var luas = selectedOption.getAttribute('data-luas');

                        var jenisDisplay = jenis ? ucfirst(jenis.replace('_', ' ')) : '';
                        var infoText = `
                            <strong>TPU:</strong> ${tpu || 'Tidak diketahui'}<br>
                            <strong>Jenis:</strong> ${jenisDisplay}<br>
                            <strong>Luas Lahan:</strong> ${luas ? parseFloat(luas).toLocaleString('id-ID', { minimumFractionDigits: 2 }) + ' m²' : 'Tidak diketahui'}
                        `;

                        document.getElementById('tpu_info_text').innerHTML = infoText;
                        document.getElementById('tpu_info_container').style.display = 'block';
                    } else {
                        document.getElementById('tpu_info_container').style.display = 'none';
                    }
                }

                // Load existing calculation info for edit mode
                function loadExistingCalculationInfo() {
                    if (!isEditMode || !editData) return;

                    var lahanUuid = editData.uuid_lahan;
                    var panjang = editData.panjang_m;
                    var lebar = editData.lebar_m;

                    if (lahanUuid && panjang && lebar) {
                        fetch('{{ route('tpu.makam.calculate-kapasitas') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    uuid_lahan: lahanUuid,
                                    panjang_m: panjang,
                                    lebar_m: lebar
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status && data.data) {
                                    document.getElementById('calculation_details').innerHTML = data.data.calculation_info || 'Informasi perhitungan tidak tersedia.';
                                    document.getElementById('calculation_info_card').style.display = 'block';
                                    kapasitasInput.value = data.data.kapasitas; // Set kapasitas dari database saat load
                                }
                            })
                            .catch(error => console.error('Could not load calculation info:', error));
                    }
                }

                // Auto calculate kapasitas
                function autoCalculateKapasitas() {
                    var lahanUuid = isEditMode ? editData.uuid_lahan : lahanSelect.value;
                    var panjang = parseFloat(panjangInput.value);
                    var lebar = parseFloat(lebarInput.value);

                    if (!lahanUuid) {
                        Swal.fire({
                            title: 'Perhatian!',
                            text: 'Lahan tidak tersedia.',
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    if (!panjang || !lebar || panjang <= 0 || lebar <= 0) {
                        Swal.fire({
                            title: 'Perhatian!',
                            text: 'Panjang dan lebar makam harus diisi dengan angka lebih dari 0.',
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    autoCalculateBtn.innerHTML = '<span class="spinner-border spinner-border-sm align-middle"></span> Menghitung...';
                    autoCalculateBtn.disabled = true;

                    fetch('{{ route('tpu.makam.calculate-kapasitas') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                uuid_lahan: lahanUuid,
                                panjang_m: panjang,
                                lebar_m: lebar
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status && data.data) {
                                kapasitasInput.value = data.data.kapasitas;
                                document.getElementById('calculation_details').innerHTML = data.data.calculation_info || 'Informasi perhitungan tidak tersedia.';
                                document.getElementById('calculation_info_card').style.display = 'block';
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: `Kapasitas dihitung: ${data.data.kapasitas} orang`,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: data.message || 'Gagal menghitung kapasitas.',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        })
                        .catch(error => {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Terjadi kesalahan saat menghitung kapasitas: ' + error.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        })
                        .finally(() => {
                            autoCalculateBtn.innerHTML = '<i class="ki-outline ki-calculator fs-2"></i> Auto';
                            autoCalculateBtn.disabled = false;
                        });
                }

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

                // Event binding
                panjangInput.addEventListener('input', calculateLuas);
                lebarInput.addEventListener('input', calculateLuas);

                if (!isEditMode) {
                    lahanSelect.addEventListener('change', function() {
                        showTpuInfo();
                        calculateLuas();
                    });
                }

                autoCalculateBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    autoCalculateKapasitas();
                });

                // Initial setup
                setTimeout(function() {
                    if (!isEditMode) {
                        $(lahanSelect).select2().on('change', function() {
                            showTpuInfo();
                            calculateLuas();
                        });
                        showTpuInfo();
                    } else {
                        // Set TPU info for edit mode
                        var tpu = '{{ $data->lahan->Tpu->nama ?? 'Tidak diketahui' }}';
                        var jenis = '{{ ucfirst(str_replace('_', ' ', $data->lahan->Tpu->jenis_tpu ?? '')) }}';
                        var luas = '{{ $data->lahan->luas_m2 ?? 'Tidak diketahui' }}';
                        document.getElementById('tpu_info_text').innerHTML = `
                            <strong>TPU:</strong> ${tpu}<br>
                            <strong>Jenis:</strong> ${jenis}<br>
                            <strong>Luas Lahan:</strong> ${luas !== 'Tidak diketahui' ? parseFloat(luas).toLocaleString('id-ID', { minimumFractionDigits: 2 }) + ' m²' : 'Tidak diketahui'}
                        `;
                        document.getElementById('tpu_info_container').style.display = 'block';
                    }

                    calculateLuas();
                    if (isEditMode) loadExistingCalculationInfo();
                }, 200);
            }

            var initValidation = function() {
                validator = FormValidation.formValidation(
                    form, {
                        fields: {
                            'uuid_lahan': {
                                validators: {
                                    notEmpty: {
                                        message: 'Lahan harus dipilih'
                                    }
                                }
                            },
                            'panjang_m': {
                                validators: {
                                    notEmpty: {
                                        message: 'Panjang makam harus diisi'
                                    },
                                    numeric: {
                                        message: 'Panjang makam harus berupa angka'
                                    },
                                    greaterThan: {
                                        min: 0.01,
                                        message: 'Panjang makam minimal 0.01 meter'
                                    }
                                }
                            },
                            'lebar_m': {
                                validators: {
                                    notEmpty: {
                                        message: 'Lebar makam harus diisi'
                                    },
                                    numeric: {
                                        message: 'Lebar makam harus berupa angka'
                                    },
                                    greaterThan: {
                                        min: 0.01,
                                        message: 'Lebar makam minimal 0.01 meter'
                                    }
                                }
                            },
                            'kapasitas': {
                                validators: {
                                    integer: {
                                        message: 'Kapasitas harus berupa angka bulat'
                                    },
                                    greaterThan: {
                                        min: 1,
                                        message: 'Kapasitas harus lebih dari 0'
                                    }
                                }
                            },
                            'status_makam': {
                                validators: {
                                    notEmpty: {
                                        message: 'Status makam harus dipilih'
                                    }
                                }
                            },
                            'keterangan': {
                                validators: {
                                    stringLength: {
                                        max: 1000,
                                        message: 'Keterangan maksimal 1000 karakter'
                                    }
                                }
                            }
                        },
                        plugins: {
                            trigger: new FormValidation.plugins.Trigger(),
                            bootstrap: new FormValidation.plugins.Bootstrap5({
                                rowSelector: '.fv-row',
                                eleInvalidClass: 'is-invalid',
                                eleValidClass: ''
                            })
                        }
                    }
                );
            }

            var handleSubmit = function() {
                submitButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    if (validator) {
                        validator.validate().then(function(status) {
                            if (status === 'Valid') {
                                submitButton.setAttribute('data-kt-indicator', 'on');
                                submitButton.disabled = true;
                                form.submit();
                            }
                        });
                    }
                });
            }

            return {
                init: function() {
                    form = document.querySelector('#kt_makam_form');
                    submitButton = document.querySelector('#kt_makam_submit');
                    cancelButton = document.querySelector('#kt_makam_cancel'); // Inisialisasi tombol batal
                    lahanSelect = document.querySelector('#kt_makam_lahan');
                    panjangInput = document.querySelector('#panjang_input');
                    lebarInput = document.querySelector('#lebar_input');
                    kapasitasInput = document.querySelector('#kapasitas_input');
                    autoCalculateBtn = document.querySelector('#auto_calculate_btn');
                    luasDisplay = document.querySelector('#luas_display');
                    luasHidden = document.querySelector('#luas_m2');

                    if (!form || !submitButton || !cancelButton || !panjangInput || !lebarInput || !kapasitasInput || !autoCalculateBtn || !luasDisplay || !luasHidden) {
                        console.error('Form elements not found');
                        return;
                    }

                    initForm();
                    initValidation();
                    handleSubmit();
                }
            };
        }();

        // Initialize when DOM is ready
        function initializeWhenReady() {
            if (typeof $ !== 'undefined' && $.fn.select2) {
                KTMakamCreateEdit.init();
            } else {
                setTimeout(initializeWhenReady, 100);
            }
        }

        if (typeof KTUtil !== 'undefined' && KTUtil.onDOMContentLoaded) {
            KTUtil.onDOMContentLoaded(initializeWhenReady);
        } else if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeWhenReady);
        } else {
            initializeWhenReady();
        }
    </script>
@endpush
{{-- SCRIPTS::END --}}
