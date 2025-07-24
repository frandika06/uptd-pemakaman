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
                <li class="breadcrumb-item text-gray-700">Manajemen TPU</li>
                {{-- end::Item --}}
                {{-- begin::Item --}}
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                {{-- end::Item --}}
                {{-- begin::Item --}}
                <li class="breadcrumb-item text-gray-700">
                    <a href="{{ route('tpu.datas.index') }}" class="text-gray-700 text-hover-primary">Data TPU</a>
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
    <form id="kt_tpu_data_form" class="form d-flex flex-column flex-lg-row" action="{{ isset($data) ? route('tpu.datas.update', [$uuid_enc]) : route('tpu.datas.store') }}"
        method="POST">
        @csrf
        @isset($data)
            @method('PUT')
        @endisset

        {{-- begin::Aside column --}}
        <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
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
                    {{-- begin::Information --}}
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
                    {{-- end::Information --}}
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
                        <h2>Detail Data TPU</h2>
                    </div>
                </div>
                {{-- end::Card header --}}
                {{-- begin::Card body --}}
                <div class="card-body pt-0">
                    {{-- begin::Input group - Nama --}}
                    <div class="mb-10 fv-row">
                        {{-- begin::Label --}}
                        <label class="required form-label">Nama TPU</label>
                        {{-- end::Label --}}
                        {{-- begin::Input --}}
                        <input type="text" name="nama" class="form-control mb-2 @error('nama') is-invalid @enderror" placeholder="Masukkan nama TPU"
                            value="{{ old('nama', isset($data) ? $data->nama : '') }}" maxlength="100" autocomplete="off" required />
                        {{-- end::Input --}}
                        {{-- begin::Description --}}
                        <div class="text-muted fs-7">Nama TPU harus unik dan sesuai dengan data resmi.</div>
                        {{-- end::Description --}}
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- end::Input group - Nama --}}

                    {{-- begin::Input group - Alamat --}}
                    <div class="mb-10 fv-row">
                        {{-- begin::Label --}}
                        <label class="required form-label">Alamat</label>
                        {{-- end::Label --}}
                        {{-- begin::Input --}}
                        <textarea name="alamat" class="form-control mb-2 @error('alamat') is-invalid @enderror" placeholder="Masukkan alamat TPU" rows="3" maxlength="255" required>{{ old('alamat', isset($data) ? $data->alamat : '') }}</textarea>
                        {{-- end::Input --}}
                        {{-- begin::Description --}}
                        <div class="text-muted fs-7">Alamat lengkap TPU, maksimal 255 karakter.</div>
                        {{-- end::Description --}}
                        @error('alamat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- end::Input group - Alamat --}}

                    {{-- begin::Input group - Kecamatan --}}
                    <div class="mb-10 fv-row">
                        {{-- begin::Label --}}
                        <label class="required form-label">Kecamatan</label>
                        {{-- end::Label --}}
                        {{-- begin::Select --}}
                        <select class="form-select mb-2 @error('kecamatan_id') is-invalid @enderror" name="kecamatan_id" id="kt_tpu_data_kecamatan" required>
                            <option value="">Pilih Kecamatan</option>
                            @foreach ($kecamatans['data'] as $kecamatan)
                                <option value="{{ $kecamatan['id'] }}" {{ old('kecamatan_id', isset($data) ? $data->kecamatan_id : '') == $kecamatan['id'] ? 'selected' : '' }}>
                                    {{ $kecamatan['name'] }}
                                </option>
                            @endforeach
                        </select>
                        {{-- end::Select --}}
                        {{-- begin::Description --}}
                        <div class="text-muted fs-7">Pilih kecamatan tempat TPU berada.</div>
                        {{-- end::Description --}}
                        @error('kecamatan_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- end::Input group - Kecamatan --}}

                    {{-- begin::Input group - Kelurahan --}}
                    <div class="mb-10 fv-row">
                        {{-- begin::Label --}}
                        <label class="required form-label">Kelurahan</label>
                        {{-- end::Label --}}
                        {{-- begin::Select --}}
                        <select class="form-select mb-2 @error('kelurahan_id') is-invalid @enderror" name="kelurahan_id" id="kt_tpu_data_kelurahan" required>
                            <option value="">Pilih Kelurahan</option>
                            @isset($data)
                                @foreach ($kelurahans['data'] as $kelurahan)
                                    <option value="{{ $kelurahan['id'] }}" {{ old('kelurahan_id', $data->kelurahan_id) == $kelurahan['id'] ? 'selected' : '' }}>
                                        {{ $kelurahan['name'] }}
                                    </option>
                                @endforeach
                            @endisset
                        </select>
                        {{-- end::Description --}}
                        <div class="text-muted fs-7">Pilih kelurahan tempat TPU berada.</div>
                        {{-- end::Description --}}
                        @error('kelurahan_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- end::Input group - Kelurahan --}}

                    {{-- <div class="mb-10 fv-row">
                        <label class="form-label">Latitude</label>
                        <input type="number" step="any" name="latitude" class="form-control mb-2 @error('latitude') is-invalid @enderror"
                            placeholder="Masukkan latitude (contoh: -6.123456)" value="{{ old('latitude', isset($data) ? $data->latitude : '') }}" autocomplete="off" />
                        <div class="text-muted fs-7">Koordinat latitude TPU (opsional, antara -90 hingga 90).</div>
                        @error('latitude')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-10 fv-row">
                        <label class="form-label">Longitude</label>
                        <input type="number" step="any" name="longitude" class="form-control mb-2 @error('longitude') is-invalid @enderror"
                            placeholder="Masukkan longitude (contoh: 106.123456)" value="{{ old('longitude', isset($data) ? $data->longitude : '') }}" autocomplete="off" />
                        <div class="text-muted fs-7">Koordinat longitude TPU (opsional, antara -180 hingga 180).</div>
                        @error('longitude')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div> --}}

                    {{-- begin::Input group - Jenis TPU --}}
                    <div class="mb-10 fv-row">
                        {{-- begin::Label --}}
                        <label class="required form-label">Jenis TPU</label>
                        {{-- end::Label --}}
                        {{-- begin::Select --}}
                        <select class="form-select mb-2 @error('jenis_tpu') is-invalid @enderror" name="jenis_tpu" id="kt_tpu_data_jenis_tpu" required>
                            <option value="">Pilih Jenis TPU</option>
                            <option value="muslim" {{ old('jenis_tpu', isset($data) ? $data->jenis_tpu : '') == 'muslim' ? 'selected' : '' }}>Muslim</option>
                            <option value="non_muslim" {{ old('jenis_tpu', isset($data) ? $data->jenis_tpu : '') == 'non_muslim' ? 'selected' : '' }}>Non Muslim</option>
                            <option value="gabungan" {{ old('jenis_tpu', isset($data) ? $data->jenis_tpu : '') == 'gabungan' ? 'selected' : '' }}>Gabungan</option>
                        </select>
                        {{-- end::Select --}}
                        {{-- begin::Description --}}
                        <div class="text-muted fs-7">Pilih jenis TPU sesuai dengan kategorinya.</div>
                        {{-- end::Description --}}
                        @error('jenis_tpu')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- end::Input group - Jenis TPU --}}

                    {{-- begin::Input group - Status --}}
                    <div class="mb-10 fv-row">
                        {{-- begin::Label --}}
                        <label class="required form-label">Status</label>
                        {{-- end::Label --}}
                        {{-- begin::Select --}}
                        <select class="form-select mb-2 @error('status') is-invalid @enderror" name="status" id="kt_tpu_data_status_edit" required>
                            <option value="">Pilih Status</option>
                            <option value="Aktif" {{ old('status', isset($data) ? $data->status : '') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="Tidak Aktif" {{ old('status', isset($data) ? $data->status : '') == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                            <option value="Penuh" {{ old('status', isset($data) ? $data->status : '') == 'Penuh' ? 'selected' : '' }}>Penuh</option>
                        </select>
                        {{-- end::Select --}}
                        {{-- begin::Description --}}
                        <div class="text-muted fs-7">Pilih status TPU berdasarkan kondisi terkini.</div>
                        {{-- end::Description --}}
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- end::Input group - Status --}}
                </div>
                {{-- end::Card body --}}
            </div>
            {{-- end::General options --}}

            {{-- begin::Actions --}}
            <div class="d-flex justify-content-end">
                {{-- begin::Button - Cancel --}}
                <a href="{{ route('tpu.datas.index') }}" id="kt_tpu_data_cancel" class="btn btn-light me-5">
                    <i class="ki-outline ki-arrow-left fs-2"></i>Batal
                </a>
                {{-- end::Button - Cancel --}}
                {{-- begin::Button - Submit --}}
                <button type="submit" id="kt_tpu_data_submit" class="btn btn-primary">
                    <span class="indicator-label">
                        <i class="fa-solid fa-save me-2"></i>{{ $submit }}
                    </span>
                    <span class="indicator-progress">
                        Please wait...
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                    </span>
                </button>
                {{-- end::Button - Submit --}}
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
        var KTAppTpuDataSave = function() {
            // Elements
            var form;
            var submitButton;
            var cancelButton;
            var kecamatanSelect;
            var kelurahanSelect;

            // Initialize
            var init = function() {
                form = document.querySelector('#kt_tpu_data_form');
                submitButton = document.querySelector('#kt_tpu_data_submit');
                cancelButton = document.querySelector('#kt_tpu_data_cancel');
                kecamatanSelect = document.querySelector('#kt_tpu_data_kecamatan');
                kelurahanSelect = document.querySelector('#kt_tpu_data_kelurahan');

                if (!form || !submitButton || !cancelButton || !kecamatanSelect || !kelurahanSelect) {
                    console.error('Form atau elemen tidak ditemukan!');
                    return;
                }

                // Event listener untuk perubahan kecamatan
                kecamatanSelect.addEventListener('change', function() {
                    var kecamatanId = this.value;
                    kelurahanSelect.innerHTML = '<option value="">Pilih Kelurahan</option>';
                    if (kecamatanId) {
                        loadKelurahan(kecamatanId);
                    }
                });

                // Load kelurahan untuk mode edit
                @isset($data)
                    if (kecamatanSelect && '{{ $data->kecamatan_id }}') {
                        kecamatanSelect.value = '{{ $data->kecamatan_id }}';
                        loadKelurahan('{{ $data->kecamatan_id }}', '{{ $data->kelurahan_id }}');
                    }
                @endisset
            };

            // Load kelurahan
            var loadKelurahan = function(kecamatanId, selectedKelurahanId = null) {
                $.ajax({
                    url: '{{ route('wilayah.kelurahan', ':id') }}'.replace(':id', kecamatanId),
                    type: 'GET',
                    success: function(response) {
                        kelurahanSelect.innerHTML = '<option value="">Pilih Kelurahan</option>';
                        response.forEach(function(item) {
                            var selected = (selectedKelurahanId && selectedKelurahanId == item['id']) ? 'selected' : '';
                            kelurahanSelect.innerHTML += `<option value="${item['id']}" ${selected}>${item['name']}</option>`;
                        });
                    },
                    error: function(xhr) {
                        console.error('Error loading kelurahan:', xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Gagal memuat data kelurahan. Silakan coba lagi.',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            };

            // Handle form
            var handleForm = function() {
                // Handle form submit
                submitButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Simple validation
                    var nama = form.querySelector('input[name="nama"]').value.trim();
                    var alamat = form.querySelector('textarea[name="alamat"]').value.trim();
                    var kecamatanId = form.querySelector('select[name="kecamatan_id"]').value;
                    var kelurahanId = form.querySelector('select[name="kelurahan_id"]').value;
                    var jenis_tpu = form.querySelector('select[name="jenis_tpu"]').value;
                    var status = form.querySelector('select[name="status"]').value;
                    // var latitude = form.querySelector('input[name="latitude"]').value.trim();
                    // var longitude = form.querySelector('input[name="longitude"]').value.trim();

                    var isValid = true;
                    var errorMessage = '';

                    // Validate fields
                    if (!nama) {
                        isValid = false;
                        errorMessage = 'Nama TPU wajib diisi';
                    } else if (nama.length > 100) {
                        isValid = false;
                        errorMessage = 'Nama TPU maksimal 100 karakter';
                    }

                    if (!alamat) {
                        isValid = false;
                        errorMessage = 'Alamat TPU wajib diisi';
                    } else if (alamat.length > 255) {
                        isValid = false;
                        errorMessage = 'Alamat TPU maksimal 255 karakter';
                    }

                    if (!kecamatanId) {
                        isValid = false;
                        errorMessage = 'Kecamatan wajib dipilih';
                    }

                    if (!kelurahanId) {
                        isValid = false;
                        errorMessage = 'Kelurahan wajib dipilih';
                    }

                    // if (latitude && (isNaN(latitude) || latitude < -90 || latitude > 90)) {
                    //     isValid = false;
                    //     errorMessage = 'Latitude harus antara -90 dan 90';
                    // }

                    // if (longitude && (isNaN(longitude) || longitude < -180 || longitude > 180)) {
                    //     isValid = false;
                    //     errorMessage = 'Longitude harus antara -180 dan 180';
                    // }

                    if (!jenis_tpu) {
                        isValid = false;
                        errorMessage = 'Jenis TPU wajib dipilih';
                    }

                    if (!status) {
                        isValid = false;
                        errorMessage = 'Status TPU wajib dipilih';
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
            };

            // Public methods
            return {
                init: function() {
                    init();
                    handleForm();
                }
            };
        }();

        // On document ready
        KTUtil.onDOMContentLoaded(function() {
            KTAppTpuDataSave.init();
        });
    </script>
@endpush
{{-- SCRIPTS::END --}}
