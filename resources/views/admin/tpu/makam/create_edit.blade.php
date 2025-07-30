@extends('layouts.admin')

{{-- SEO::BEGIN --}}
@section('title', $title)
{{-- SEO::END --}}

{{-- TOOLBAR::BEGIN --}}
@section('toolbar')
    <div class="d-flex flex-stack flex-row-fluid">
        {{-- begin::Toolbar container --}}
        <div class="d-flex flex-column flex-row-fluid">
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
                <li class="breadcrumb-item text-gray-700">TPU</li>
                {{-- end::Item --}}
                {{-- begin::Item --}}
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                {{-- end::Item --}}
                {{-- begin::Item --}}
                <li class="breadcrumb-item text-gray-700">
                    <a href="{{ route('tpu.makam.index') }}" class="text-gray-700 text-hover-primary">Data Makam</a>
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
    <form id="kt_makam_form" class="form d-flex flex-column flex-lg-row" action="{{ isset($data) ? route('tpu.makam.update', [$uuid_enc]) : route('tpu.makam.store') }}" method="POST">
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
                    <div class="card-title">
                        <h2>Status Makam</h2>
                    </div>
                </div>
                {{-- end::Card header --}}

                {{-- begin::Card body --}}
                <div class="card-body pt-0">
                    <select class="form-select mb-2" data-control="select2" data-placeholder="Pilih Status Makam" id="kt_makam_status" name="status_makam" required>
                        <option></option>
                        @foreach ($statusMakam as $status)
                            <option value="{{ $status->nama }}"
                                {{ isset($data) && $data->status_makam == $status->nama ? 'selected' : (old('status_makam') == $status->nama ? 'selected' : '') }}>
                                {{ $status->nama }}
                            </option>
                        @endforeach
                    </select>
                    <div class="text-muted fs-7">Pilih status dari makam ini.</div>
                    @error('status_makam')
                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                    @enderror
                </div>
                {{-- end::Card body --}}
            </div>
            {{-- end::Status settings --}}

            {{-- begin::Kapasitas Info --}}
            <div class="card card-flush py-4">
                {{-- begin::Card header --}}
                <div class="card-header">
                    <div class="card-title">
                        <h2>Info Kapasitas</h2>
                    </div>
                </div>
                {{-- end::Card header --}}

                {{-- begin::Card body --}}
                <div class="card-body pt-0">
                    {{-- Progress bar kapasitas --}}
                    <div id="capacity-progress" class="d-none">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="progress h-6px flex-grow-1">
                                <div id="capacity-progress-bar" class="progress-bar bg-success" style="width: 0%"></div>
                            </div>
                            <span id="capacity-percentage" class="text-muted fs-8">0%</span>
                        </div>
                        <div class="fs-7 text-muted">
                            <span id="capacity-details">Terisi: 0 | Sisa: 0 | Total: 0</span>
                        </div>
                    </div>

                    {{-- Info text when no data --}}
                    <div id="capacity-placeholder" class="text-muted fs-7">
                        Isi kapasitas dan makam terisi untuk melihat visualisasi kapasitas.
                    </div>
                </div>
                {{-- end::Card body --}}
            </div>
            {{-- end::Kapasitas Info --}}

        </div>
        {{-- end::Aside column --}}

        {{-- begin::Main column --}}
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">

            {{-- begin::Lahan Info --}}
            <div class="card card-flush py-4">
                {{-- begin::Card header --}}
                <div class="card-header">
                    <div class="card-title">
                        <h2>Informasi Lahan</h2>
                    </div>
                </div>
                {{-- end::Card header --}}

                {{-- begin::Card body --}}
                <div class="card-body pt-0">
                    <div class="fv-row">
                        <label class="required form-label">Lahan</label>

                        @if (isset($data))
                            {{-- EDIT MODE: Readonly field --}}
                            <div class="input-group mb-5">
                                <span class="input-group-text">
                                    <i class="ki-outline ki-geolocation fs-2"></i>
                                </span>
                                <input type="text" class="form-control" readonly
                                    value="{{ $data->Lahan ? $data->Lahan->kode_lahan . ' - ' . ($data->Lahan->Tpu ? $data->Lahan->Tpu->nama : 'TPU Tidak Diketahui') : 'Lahan Tidak Diketahui' }}" />
                            </div>
                            <input type="hidden" name="uuid_lahan" value="{{ $data->uuid_lahan }}" />
                            <div class="text-muted fs-7 mb-5">Lahan tidak dapat diubah saat edit data makam.</div>

                            {{-- Show detailed lahan info --}}
                            @if ($data->Lahan && $data->Lahan->Tpu)
                                <div class="notice d-flex bg-light-info rounded border-info border border-dashed p-6">
                                    <i class="ki-outline ki-information-5 fs-2tx text-info me-4"></i>
                                    <div class="d-flex flex-stack flex-grow-1">
                                        <div class="fw-semibold">
                                            <h4 class="text-gray-900 fw-bold">Detail Lahan</h4>
                                            <div class="fs-6 text-gray-700">
                                                <div class="d-flex align-items-center mb-2">
                                                    <span class="bullet bullet-vertical h-20px bg-info me-3"></span>
                                                    <span><strong>Kode Lahan:</strong> {{ $data->Lahan->kode_lahan }}</span>
                                                </div>
                                                <div class="d-flex align-items-center mb-2">
                                                    <span class="bullet bullet-vertical h-20px bg-info me-3"></span>
                                                    <span><strong>TPU:</strong> {{ $data->Lahan->Tpu->nama }}</span>
                                                </div>
                                                <div class="d-flex align-items-center mb-2">
                                                    <span class="bullet bullet-vertical h-20px bg-info me-3"></span>
                                                    <span><strong>Jenis TPU:</strong> {{ ucfirst(str_replace('_', ' ', $data->Lahan->Tpu->jenis_tpu)) }}</span>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <span class="bullet bullet-vertical h-20px bg-info me-3"></span>
                                                    <span><strong>Luas Lahan:</strong> {{ number_format($data->Lahan->luas_m2, 2) }} m²</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @else
                            {{-- CREATE MODE: Dropdown --}}
                            <select class="form-select mb-2" data-control="select2" data-placeholder="Pilih Lahan" id="kt_makam_lahan" name="uuid_lahan" required>
                                <option></option>
                                @foreach ($lahans as $lahan)
                                    <option value="{{ $lahan->uuid }}" data-tpu-nama="{{ $lahan->Tpu ? $lahan->Tpu->nama : '' }}"
                                        data-tpu-jenis="{{ $lahan->Tpu ? $lahan->Tpu->jenis_tpu : '' }}" data-luas-lahan="{{ $lahan->luas_m2 }}"
                                        data-kode-lahan="{{ $lahan->kode_lahan }}" {{ old('uuid_lahan') == $lahan->uuid ? 'selected' : '' }}>
                                        {{ $lahan->kode_lahan }} - {{ $lahan->Tpu ? $lahan->Tpu->nama : 'TPU Tidak Diketahui' }}
                                        @if ($lahan->Tpu)
                                            ({{ ucfirst(str_replace('_', ' ', $lahan->Tpu->jenis_tpu)) }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <div class="text-muted fs-7">Pilih lahan tempat makam ini akan berada.</div>
                            @error('uuid_lahan')
                                <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                            @enderror

                            {{-- Dynamic TPU Info Container for CREATE mode --}}
                            <div class="mt-5" id="tpu_info_container" style="display: none;">
                                <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-6">
                                    <i class="ki-outline ki-information-5 fs-2tx text-primary me-4"></i>
                                    <div class="d-flex flex-stack flex-grow-1">
                                        <div class="fw-semibold">
                                            <h4 class="text-gray-900 fw-bold">Detail Lahan Terpilih</h4>
                                            <div class="fs-6 text-gray-700" id="tpu_info_text"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                {{-- end::Card body --}}
            </div>
            {{-- end::Lahan Info --}}

            {{-- begin::Dimensi & Kapasitas --}}
            <div class="card card-flush py-4">
                {{-- begin::Card header --}}
                <div class="card-header">
                    <div class="card-title">
                        <h2>Dimensi & Kapasitas</h2>
                    </div>
                </div>
                {{-- end::Card header --}}

                {{-- begin::Card body --}}
                <div class="card-body pt-0">

                    {{-- begin::Row - Dimensi --}}
                    <div class="row g-9 mb-8">
                        <div class="col-md-4 fv-row">
                            <label class="required form-label">Panjang (m)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0.01" max="999.99" name="panjang_m" id="panjang_input"
                                    class="form-control @error('panjang_m') is-invalid @enderror" placeholder="0.00"
                                    value="{{ old('panjang_m', isset($data) ? number_format($data->panjang_m, 2, '.', '') : '') }}" required />
                                <span class="input-group-text">meter</span>
                            </div>
                            @error('panjang_m')
                                <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 fv-row">
                            <label class="required form-label">Lebar (m)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0.01" max="999.99" name="lebar_m" id="lebar_input"
                                    class="form-control @error('lebar_m') is-invalid @enderror" placeholder="0.00"
                                    value="{{ old('lebar_m', isset($data) ? number_format($data->lebar_m, 2, '.', '') : '') }}" required />
                                <span class="input-group-text">meter</span>
                            </div>
                            @error('lebar_m')
                                <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 fv-row">
                            <label class="form-label">Luas (m²)</label>
                            <div class="input-group">
                                <input type="text" id="luas_display" class="form-control" placeholder="Otomatis dihitung" readonly />
                                <span class="input-group-text">m²</span>
                            </div>
                            <input type="hidden" name="luas_m2" id="luas_m2" value="{{ old('luas_m2', isset($data) ? $data->luas_m2 : '') }}" />
                            <div class="text-muted fs-7 mt-1">Otomatis dihitung: panjang × lebar</div>
                        </div>
                    </div>
                    {{-- end::Row - Dimensi --}}

                    {{-- begin::Row - Kapasitas --}}
                    <div class="row g-9 mb-8">
                        <div class="col-md-4 fv-row">
                            <label class="form-label">Kapasitas Total</label>
                            <div class="input-group">
                                <input type="number" min="1" max="99999" name="kapasitas" id="kapasitas_input"
                                    class="form-control @error('kapasitas') is-invalid @enderror" placeholder="Auto calculate"
                                    value="{{ old('kapasitas', isset($data) ? $data->kapasitas : '') }}" />
                                <button type="button" id="auto_calculate_btn" class="btn btn-outline-primary">
                                    <i class="ki-outline ki-arrows-circle fs-6"></i>
                                    Auto
                                </button>
                            </div>
                            <div class="text-muted fs-7 mt-1">Kosongkan untuk kalkulasi otomatis</div>
                            @error('kapasitas')
                                <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 fv-row">
                            <label class="form-label">Makam Terisi</label>
                            <div class="input-group">
                                <input type="number" min="0" max="99999" name="makam_terisi" id="makam_terisi_input"
                                    class="form-control @error('makam_terisi') is-invalid @enderror" placeholder="0"
                                    value="{{ old('makam_terisi', isset($data) ? $data->makam_terisi : '0') }}" />
                                <span class="input-group-text">makam</span>
                            </div>
                            <div class="text-muted fs-7 mt-1">Jumlah makam yang sudah terisi</div>
                            @error('makam_terisi')
                                <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 fv-row">
                            <label class="form-label">Sisa Kapasitas</label>
                            <div class="input-group">
                                <input type="number" name="sisa_kapasitas" id="sisa_kapasitas_display" class="form-control" placeholder="Auto calculate" readonly
                                    value="{{ old('sisa_kapasitas', isset($data) ? $data->sisa_kapasitas : '0') }}" />
                                <span class="input-group-text">makam</span>
                            </div>
                            <div class="text-muted fs-7 mt-1">Otomatis dihitung: kapasitas - terisi</div>
                        </div>
                    </div>
                    {{-- end::Row - Kapasitas --}}

                </div>
                {{-- end::Card body --}}
            </div>
            {{-- end::Dimensi & Kapasitas --}}

            {{-- begin::Keterangan --}}
            <div class="card card-flush py-4">
                {{-- begin::Card header --}}
                <div class="card-header">
                    <div class="card-title">
                        <h2>Keterangan</h2>
                    </div>
                </div>
                {{-- end::Card header --}}

                {{-- begin::Card body --}}
                <div class="card-body pt-0">
                    <div class="fv-row">
                        <label class="form-label">Catatan Tambahan</label>
                        <textarea class="form-control @error('keterangan') is-invalid @enderror" name="keterangan" rows="4" placeholder="Masukkan keterangan atau catatan tambahan (opsional)">{{ old('keterangan', isset($data) ? $data->keterangan : '') }}</textarea>
                        <div class="text-muted fs-7 mt-1">Keterangan atau catatan tambahan tentang makam ini.</div>
                        @error('keterangan')
                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                {{-- end::Card body --}}
            </div>
            {{-- end::Keterangan --}}

            {{-- begin::Actions --}}
            <div class="d-flex justify-content-end">
                <a href="{{ route('tpu.makam.index') }}" id="kt_makam_cancel" class="btn btn-light me-5">
                    <i class="ki-outline ki-arrow-left fs-6 me-1"></i>Batal
                </a>
                <button type="submit" id="kt_makam_submit" class="btn btn-primary">
                    <span class="indicator-label">
                        <i class="fa-solid fa-save me-2"></i>{{ $submit }}
                    </span>
                    <span class="indicator-progress">Please wait...
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

        var KTMakamCreateEdit = function() {
            var form, submitButton, cancelButton, lahanSelect, panjangInput, lebarInput,
                kapasitasInput, makamTerisiInput, sisaKapasitasDisplay, autoCalculateBtn,
                luasDisplay, luasHidden, validator;

            var initForm = function() {
                // Initialize Select2 hanya untuk create mode
                if (lahanSelect) {
                    $(lahanSelect).select2({
                        placeholder: "Pilih Lahan",
                        allowClear: true
                    });

                    // Event handler untuk perubahan lahan (hanya create mode)
                    $(lahanSelect).on('change', function() {
                        updateTpuInfo();
                        clearCalculationValues();
                    });
                }

                $('#kt_makam_status').select2({
                    placeholder: "Pilih Status Makam",
                    allowClear: true
                });

                // Load edit data if available
                @isset($data)
                    var editData = {
                        uuid_lahan: "{{ $data->uuid_lahan }}",
                        panjang_m: {{ $data->panjang_m }},
                        lebar_m: {{ $data->lebar_m }},
                        luas_m2: {{ $data->luas_m2 }},
                        kapasitas: {{ $data->kapasitas ?? 0 }},
                        makam_terisi: {{ $data->makam_terisi ?? 0 }},
                        sisa_kapasitas: {{ $data->sisa_kapasitas ?? 0 }},
                        status_makam: "{{ $data->status_makam }}",
                        keterangan: "{{ $data->keterangan ?? '' }}"
                    };
                    loadEditData(editData);
                @endisset

                // Auto calculate luas when dimensi changes
                $(panjangInput).add(lebarInput).on('input', calculateLuas);

                // Auto calculate sisa kapasitas when kapasitas or makam_terisi changes
                $(kapasitasInput).add(makamTerisiInput).on('input', function() {
                    calculateSisaKapasitas();
                    updateCapacityVisualization();

                    // Revalidate makam_terisi when kapasitas changes
                    if (validator && makamTerisiInput.value) {
                        validator.revalidateField('makam_terisi');
                    }
                });

                // Auto calculate kapasitas button
                $(autoCalculateBtn).on('click', autoCalculateKapasitas);

                // Update capacity visualization
                $(kapasitasInput).add(makamTerisiInput).on('input', updateCapacityVisualization);

                // Initial calculations
                calculateLuas();
                calculateSisaKapasitas();
                updateCapacityVisualization();
            };

            var updateTpuInfo = function() {
                if (!lahanSelect) return; // Hanya untuk create mode

                var selectedOption = $(lahanSelect).find('option:selected');
                var tpuInfoContainer = document.getElementById('tpu_info_container');
                var tpuInfoText = document.getElementById('tpu_info_text');

                if (selectedOption.val() && selectedOption.val() !== '') {
                    var tpuNama = selectedOption.data('tpu-nama');
                    var tpuJenis = selectedOption.data('tpu-jenis');
                    var luasLahan = selectedOption.data('luas-lahan');
                    var kodeLahan = selectedOption.data('kode-lahan');

                    var jenisDisplay = tpuJenis ? ucfirst(tpuJenis.replace('_', ' ')) : 'Tidak diketahui';
                    var infoText = `
                        <div class="d-flex align-items-center mb-2">
                            <span class="bullet bullet-vertical h-20px bg-primary me-3"></span>
                            <span><strong>Kode Lahan:</strong> ${kodeLahan}</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="bullet bullet-vertical h-20px bg-primary me-3"></span>
                            <span><strong>TPU:</strong> ${tpuNama || 'Tidak diketahui'}</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="bullet bullet-vertical h-20px bg-primary me-3"></span>
                            <span><strong>Jenis TPU:</strong> ${jenisDisplay}</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="bullet bullet-vertical h-20px bg-primary me-3"></span>
                            <span><strong>Luas Lahan:</strong> ${luasLahan ? parseFloat(luasLahan).toLocaleString('id-ID', { minimumFractionDigits: 2 }) + ' m²' : 'Tidak diketahui'}</span>
                        </div>
                    `;

                    tpuInfoText.innerHTML = infoText;
                    tpuInfoContainer.style.display = 'block';
                } else {
                    tpuInfoContainer.style.display = 'none';
                }
            };

            var clearCalculationValues = function() {
                // Clear kapasitas when lahan changes
                if (kapasitasInput) {
                    kapasitasInput.value = '';
                }
                calculateSisaKapasitas();
                updateCapacityVisualization();
            };

            var ucfirst = function(str) {
                if (!str) return '';
                return str.charAt(0).toUpperCase() + str.slice(1);
            };

            var loadEditData = function(data) {
                // Load data ke form fields
                if (data.panjang_m) $(panjangInput).val(data.panjang_m);
                if (data.lebar_m) $(lebarInput).val(data.lebar_m);
                if (data.kapasitas) $(kapasitasInput).val(data.kapasitas);
                if (data.makam_terisi) $(makamTerisiInput).val(data.makam_terisi);

                // Set status makam
                if (data.status_makam) {
                    $('#kt_makam_status').val(data.status_makam).trigger('change');
                }

                // Set keterangan
                if (data.keterangan) {
                    $('textarea[name="keterangan"]').val(data.keterangan);
                }

                // Trigger calculations
                calculateLuas();
                calculateSisaKapasitas();
                updateCapacityVisualization();
            };

            var calculateLuas = function() {
                var panjang = parseFloat($(panjangInput).val()) || 0;
                var lebar = parseFloat($(lebarInput).val()) || 0;
                var luas = panjang * lebar;

                $(luasHidden).val(luas.toFixed(2));
                $(luasDisplay).val(luas > 0 ? luas.toFixed(2) : '');
            };

            var calculateSisaKapasitas = function() {
                var kapasitas = parseInt($(kapasitasInput).val()) || 0;
                var makamTerisi = parseInt($(makamTerisiInput).val()) || 0;
                var sisaKapasitas = Math.max(0, kapasitas - makamTerisi);

                $(sisaKapasitasDisplay).val(sisaKapasitas);

                // Validate makam terisi tidak melebihi kapasitas
                if (makamTerisi > kapasitas && kapasitas > 0) {
                    $(makamTerisiInput).addClass('is-invalid');
                    $(makamTerisiInput).siblings('.invalid-feedback').remove();
                    $(makamTerisiInput).after('<div class="invalid-feedback">Makam terisi tidak boleh melebihi kapasitas total</div>');
                } else {
                    $(makamTerisiInput).removeClass('is-invalid');
                    $(makamTerisiInput).siblings('.invalid-feedback').remove();
                }
            };

            var updateCapacityVisualization = function() {
                var kapasitas = parseInt($(kapasitasInput).val()) || 0;
                var makamTerisi = parseInt($(makamTerisiInput).val()) || 0;
                var sisaKapasitas = Math.max(0, kapasitas - makamTerisi);

                if (kapasitas > 0) {
                    var percentage = Math.round((makamTerisi / kapasitas) * 100);
                    var progressClass = percentage >= 90 ? 'bg-danger' : (percentage >= 70 ? 'bg-warning' : 'bg-success');

                    $('#capacity-progress').removeClass('d-none');
                    $('#capacity-placeholder').addClass('d-none');

                    $('#capacity-progress-bar')
                        .removeClass('bg-success bg-warning bg-danger')
                        .addClass(progressClass)
                        .css('width', percentage + '%');

                    $('#capacity-percentage').text(percentage + '%');
                    $('#capacity-details').text(`Terisi: ${makamTerisi} | Sisa: ${sisaKapasitas} | Total: ${kapasitas}`);
                } else {
                    $('#capacity-progress').addClass('d-none');
                    $('#capacity-placeholder').removeClass('d-none');
                }
            };

            var autoCalculateKapasitas = function() {
                var uuid_lahan;
                var panjang = parseFloat($(panjangInput).val()) || 0;
                var lebar = parseFloat($(lebarInput).val()) || 0;

                // Get uuid_lahan berdasarkan mode
                @if (isset($data))
                    // Edit mode: ambil dari data existing
                    uuid_lahan = "{{ $data->uuid_lahan }}";
                @else
                    // Create mode: ambil dari dropdown
                    uuid_lahan = $(lahanSelect).val();
                @endif

                if (!uuid_lahan) {
                    Swal.fire({
                        title: "Peringatan",
                        text: "Lahan tidak tersedia",
                        icon: "warning"
                    });
                    return;
                }

                if (panjang <= 0 || lebar <= 0) {
                    Swal.fire({
                        title: "Peringatan",
                        text: "Pastikan panjang dan lebar sudah diisi dengan benar",
                        icon: "warning"
                    });
                    return;
                }

                // Show loading
                $(autoCalculateBtn).prop('disabled', true);
                $(autoCalculateBtn).html('<span class="spinner-border spinner-border-sm" role="status"></span> Menghitung...');

                $.ajax({
                    url: "{{ route('tpu.makam.calculate-kapasitas') }}",
                    type: 'POST',
                    data: {
                        uuid_lahan: uuid_lahan,
                        panjang_m: panjang,
                        lebar_m: lebar,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.status && response.data) {
                            // Set kapasitas
                            $(kapasitasInput).val(response.data.kapasitas);

                            // Update capacity visualization
                            calculateSisaKapasitas();
                            updateCapacityVisualization();

                            // Show calculation info
                            var info = response.data.calculation_info || 'Informasi perhitungan tidak tersedia';

                            Swal.fire({
                                title: "Berhasil Menghitung Kapasitas",
                                html: `
                                    <div class="text-start">
                                        <p><strong>Kapasitas: ${response.data.kapasitas} makam</strong></p>
                                        <hr>
                                        <pre style="font-size: 12px; text-align: left; white-space: pre-line;">${info}</pre>
                                    </div>
                                `,
                                icon: "success",
                                confirmButtonText: "OK"
                            });
                        } else {
                            Swal.fire({
                                title: "Error",
                                text: response.message || "Gagal menghitung kapasitas",
                                icon: "error"
                            });
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = "Gagal menghitung kapasitas";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            title: "Error",
                            text: errorMessage,
                            icon: "error"
                        });
                    },
                    complete: function() {
                        $(autoCalculateBtn).prop('disabled', false);
                        $(autoCalculateBtn).html('<i class="ki-outline ki-arrows-circle fs-6"></i> Auto');
                    }
                });
            };

            var initValidation = function() {
                // Tentukan fields validation berdasarkan mode
                var fields = {
                    'panjang_m': {
                        validators: {
                            notEmpty: {
                                message: 'Panjang harus diisi'
                            },
                            numeric: {
                                message: 'Panjang harus berupa angka'
                            },
                            between: {
                                min: 0.01,
                                max: 999.99,
                                message: 'Panjang harus antara 0.01 - 999.99 meter'
                            }
                        }
                    },
                    'lebar_m': {
                        validators: {
                            notEmpty: {
                                message: 'Lebar harus diisi'
                            },
                            numeric: {
                                message: 'Lebar harus berupa angka'
                            },
                            between: {
                                min: 0.01,
                                max: 999.99,
                                message: 'Lebar harus antara 0.01 - 999.99 meter'
                            }
                        }
                    },
                    'kapasitas': {
                        validators: {
                            integer: {
                                message: 'Kapasitas harus berupa angka bulat'
                            },
                            between: {
                                min: 1,
                                max: 99999,
                                message: 'Kapasitas harus antara 1 - 99999'
                            }
                        }
                    },
                    'makam_terisi': {
                        validators: {
                            integer: {
                                message: 'Makam terisi harus berupa angka bulat'
                            },
                            between: {
                                min: 0,
                                max: 99999,
                                message: 'Makam terisi harus antara 0 - 99999'
                            },
                            callback: {
                                message: 'Makam terisi tidak boleh melebihi kapasitas',
                                callback: function(value, validator, field) {
                                    const kapasitas = parseInt($(kapasitasInput).val()) || 0;
                                    const makamTerisi = parseInt(value.value) || 0;
                                    return kapasitas === 0 || makamTerisi <= kapasitas;
                                }
                            }
                        }
                    },
                    'status_makam': {
                        validators: {
                            notEmpty: {
                                message: 'Status makam harus dipilih'
                            }
                        }
                    }
                };

                // Tambahkan validasi uuid_lahan hanya untuk create mode
                @if (!isset($data))
                    fields['uuid_lahan'] = {
                        validators: {
                            notEmpty: {
                                message: 'Lahan harus dipilih'
                            }
                        }
                    };
                @endif

                // Form validation using FormValidation.io
                const validator = FormValidation.formValidation(form, {
                    fields: fields,
                    plugins: {
                        trigger: new FormValidation.plugins.Trigger(),
                        bootstrap: new FormValidation.plugins.Bootstrap5({
                            rowSelector: '.fv-row',
                            eleInvalidClass: '',
                            eleValidClass: ''
                        })
                    }
                });

                return validator;
            };

            var handleSubmit = function() {
                validator = initValidation();

                submitButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    if (validator) {
                        validator.validate().then(function(status) {
                            if (status == 'Valid') {
                                // Show loading state
                                submitButton.setAttribute('data-kt-indicator', 'on');
                                submitButton.disabled = true;

                                // Submit form
                                form.submit();
                            }
                        });
                    }
                });
            };

            return {
                init: function() {
                    form = document.querySelector('#kt_makam_form');
                    submitButton = document.querySelector('#kt_makam_submit');
                    cancelButton = document.querySelector('#kt_makam_cancel');
                    lahanSelect = document.querySelector('#kt_makam_lahan'); // Hanya ada di create mode
                    panjangInput = document.querySelector('#panjang_input');
                    lebarInput = document.querySelector('#lebar_input');
                    kapasitasInput = document.querySelector('#kapasitas_input');
                    makamTerisiInput = document.querySelector('#makam_terisi_input');
                    sisaKapasitasDisplay = document.querySelector('#sisa_kapasitas_display');
                    autoCalculateBtn = document.querySelector('#auto_calculate_btn');
                    luasDisplay = document.querySelector('#luas_display');
                    luasHidden = document.querySelector('#luas_m2');

                    if (!form || !submitButton || !cancelButton || !panjangInput ||
                        !lebarInput || !kapasitasInput || !autoCalculateBtn ||
                        !luasDisplay || !luasHidden || !makamTerisiInput || !sisaKapasitasDisplay) {
                        console.error('Required form elements not found');
                        return;
                    }

                    initForm();
                    handleSubmit();
                }
            };
        }();

        // Initialize when DOM is ready
        function initializeWhenReady() {
            if (typeof $ !== 'undefined' && $.fn.select2 && typeof FormValidation !== 'undefined') {
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
