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
        method="POST" enctype="multipart/form-data">
        @csrf
        @isset($data)
            @method('PUT')
        @endisset

        {{-- Hidden field for deleted dokumen IDs (only if dokumen feature enabled) --}}
        @if (isset($kategoriDokumen) && $kategoriDokumen->count() > 0)
            <input type="hidden" name="deleted_dokumen_ids" id="deleted_dokumen_ids" value="">
        @endif

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

                    {{-- Uncomment if you want to use coordinates --}}
                    {{--
                    <div class="mb-10 fv-row">
                        <label class="form-label">Latitude</label>
                        <input type="number" step="any" name="latitude"
                               class="form-control mb-2 @error('latitude') is-invalid @enderror"
                               placeholder="Masukkan latitude (contoh: -6.123456)"
                               value="{{ old('latitude', isset($data) ? $data->latitude : '') }}" autocomplete="off" />
                        <div class="text-muted fs-7">Koordinat latitude TPU (opsional, antara -90 hingga 90).</div>
                        @error('latitude')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-10 fv-row">
                        <label class="form-label">Longitude</label>
                        <input type="number" step="any" name="longitude"
                               class="form-control mb-2 @error('longitude') is-invalid @enderror"
                               placeholder="Masukkan longitude (contoh: 106.123456)"
                               value="{{ old('longitude', isset($data) ? $data->longitude : '') }}" autocomplete="off" />
                        <div class="text-muted fs-7">Koordinat longitude TPU (opsional, antara -180 hingga 180).</div>
                        @error('longitude')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    --}}

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
                            Upload dokumen pendukung untuk TPU ini. Format yang didukung: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, JPEG, PNG, dll.
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
            var dokumenCounter = 0;
            var deletedDokumenIds = [];

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

                // Initialize dokumen management (only if dokumen feature is enabled)
                @if (isset($kategoriDokumen) && $kategoriDokumen->count() > 0)
                    initDokumenManagement();
                @endif
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
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Gagal memuat data kelurahan. Silakan coba lagi.',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert('Gagal memuat data kelurahan. Silakan coba lagi.');
                        }
                    }
                });
            };

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

                    if (!jenis_tpu) {
                        isValid = false;
                        errorMessage = 'Jenis TPU wajib dipilih';
                    }

                    if (!status) {
                        isValid = false;
                        errorMessage = 'Status TPU wajib dipilih';
                    }

                    if (isValid) {
                        // Update deleted dokumen ids if feature is enabled
                        var deletedField = document.getElementById('deleted_dokumen_ids');
                        if (deletedField) {
                            deletedField.value = deletedDokumenIds.join(',');
                        }

                        // Show loading
                        submitButton.setAttribute('data-kt-indicator', 'on');
                        submitButton.disabled = true;

                        // Submit form
                        form.submit();
                    } else {
                        // Show error
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                text: errorMessage,
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "Ok, saya mengerti!",
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                            });
                        } else {
                            alert(errorMessage);
                        }
                    }
                });

                // Handle cancel button
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
        if (typeof KTUtil !== 'undefined') {
            KTUtil.onDOMContentLoaded(function() {
                KTAppTpuDataSave.init();
            });
        } else {
            document.addEventListener('DOMContentLoaded', function() {
                KTAppTpuDataSave.init();
            });
        }
    </script>
@endpush
{{-- SCRIPTS::END --}}
