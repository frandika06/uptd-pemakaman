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
                <li class="breadcrumb-item text-gray-700">Konten Internal</li>
                {{-- end::Item --}}
                {{-- begin::Item --}}
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                {{-- end::Item --}}
                {{-- begin::Item --}}
                <li class="breadcrumb-item text-gray-700">
                    <a href="{{ route('prt.apps.faq.index') }}" class="text-gray-700 text-hover-primary">FAQ</a>
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
    <form id="kt_faq_form" class="form d-flex flex-column flex-lg-row" action="{{ isset($data) ? route('prt.apps.faq.update', $uuid_enc) : route('prt.apps.faq.store') }}"
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
                        <h2>Thumbnail FAQ</h2>
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

            {{-- begin::Status settings --}}
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Status</h2>
                    </div>
                </div>
                <div class="card-body text-center pt-0">
                    <select class="form-select mb-2 @error('status') is-invalid @enderror" data-control="select2" data-hide-search="true" data-placeholder="Pilih Status"
                        id="kt_faq_status" name="status" required>
                        <option></option>
                        <option value="1" {{ old('status', isset($data) ? $data->status : '1') == '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('status', isset($data) ? $data->status : '1') == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                    @error('status')
                        <div class="text-danger fs-7 mt-2">{{ $message }}</div>
                    @enderror
                    <div class="text-muted fs-7">Atur status publikasi FAQ.</div>
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
                            <span class="fw-bold text-gray-600">Slug:</span><br />
                            <span class="text-gray-800 fw-bold" id="faq_slug_preview">{{ isset($data) ? $data->slug : '-' }}</span>
                        </div>
                        <div class="m-0 p-0">
                            <span class="fw-bold text-gray-600">Jumlah FAQ:</span><br />
                            <span class="text-gray-800 fw-bold">
                                <span id="faq_list_counter">{{ isset($list_faq) ? count($list_faq) : 0 }}</span>
                                <span class="ms-2">pertanyaan</span>
                            </span>
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
                        <h2>Detail FAQ</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="mb-10 fv-row">
                        <label class="required form-label">Judul FAQ</label>
                        <input type="text" name="judul" id="judul" class="form-control mb-2 @error('judul') is-invalid @enderror" placeholder="Masukkan judul FAQ"
                            value="{{ old('judul', isset($data) ? $data->judul : '') }}" maxlength="300" autocomplete="off" required />
                        <div class="text-muted fs-7">Judul FAQ maksimal 300 karakter.</div>
                        @error('judul')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-10 fv-row">
                        <label class="required form-label">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" class="form-control mb-2 @error('deskripsi') is-invalid @enderror" rows="3" placeholder="Masukkan deskripsi FAQ untuk SEO"
                            maxlength="160" autocomplete="off" required>{{ old('deskripsi', isset($data) ? $data->deskripsi : '') }}</textarea>
                        <div class="text-muted fs-7">Deskripsi untuk SEO (Search Engine Optimization) maksimal 160 karakter.</div>
                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-10 fv-row">
                        <label class="required form-label">Tanggal Publikasi</label>
                        <input type="datetime-local" name="tanggal" id="tanggal" class="form-control mb-2 @error('tanggal') is-invalid @enderror"
                            value="{{ old('tanggal', isset($data) ? $data->tanggal : date('Y-m-d\TH:i')) }}" required />
                        <div class="text-muted fs-7">Tentukan kapan FAQ ini akan dipublikasikan.</div>
                        @error('tanggal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            {{-- end::General options --}}

            {{-- begin::FAQ List --}}
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Daftar Pertanyaan & Jawaban</h2>
                    </div>
                    <div class="card-toolbar">
                        <button type="button" id="addFaq" class="btn btn-sm btn-light-primary">
                            <i class="ki-outline ki-plus fs-2"></i>Tambah FAQ
                        </button>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div id="faqFields">
                        @if (old('pertanyaan') || (isset($list_faq) && $list_faq->isNotEmpty()))
                            @if (old('pertanyaan'))
                                @foreach (old('pertanyaan') as $index => $pertanyaan)
                                    <div class="faq-entry mb-7" data-index="{{ $index }}">
                                        <div class="card border">
                                            <div class="card-header border-0 pt-6">
                                                <div class="card-title">
                                                    <h4 class="fw-bold text-gray-800">
                                                        <i class="ki-outline ki-questionnaire-tablet text-primary fs-2 me-2"></i>
                                                        FAQ #<span class="faq-number">{{ $index + 1 }}</span>
                                                    </h4>
                                                </div>
                                                <div class="card-toolbar">
                                                    <button type="button" class="btn btn-icon btn-sm btn-light-danger remove-faq-entry" data-bs-toggle="tooltip"
                                                        title="Hapus FAQ ini">
                                                        <i class="ki-outline ki-trash fs-5"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card-body pt-0">
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <div class="mb-7 fv-row">
                                                            <label class="required form-label fw-semibold text-gray-700">Pertanyaan</label>
                                                            <input type="text" name="pertanyaan[]"
                                                                class="form-control form-control-solid @if ($errors->has('pertanyaan.' . $index)) is-invalid @endif"
                                                                placeholder="Masukkan pertanyaan FAQ" value="{{ old('pertanyaan.' . $index) }}" autocomplete="off" required />
                                                            <div class="text-muted fs-7 mt-1">Masukkan pertanyaan singkat</div>
                                                            @if ($errors->has('pertanyaan.' . $index))
                                                                <div class="invalid-feedback">{{ $errors->first('pertanyaan.' . $index) }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-12">
                                                        <div class="mb-7 fv-row">
                                                            <label class="required form-label fw-semibold text-gray-700">Jawaban</label>
                                                            <textarea name="jawaban[]" class="form-control form-control-solid jawaban-editor @if ($errors->has('jawaban.' . $index)) is-invalid @endif" placeholder="Masukkan jawaban FAQ"
                                                                id="jawaban-editor-{{ $index }}" required>{{ old('jawaban.' . $index) }}</textarea>
                                                            <div class="text-muted fs-7 mt-1">Gunakan editor untuk format jawaban</div>
                                                            @if ($errors->has('jawaban.' . $index))
                                                                <div class="invalid-feedback">{{ $errors->first('jawaban.' . $index) }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <div class="mb-7 fv-row">
                                                            <label class="required form-label fw-semibold text-gray-700">Status</label>
                                                            <select name="status_list[]" class="form-select form-select-solid @if ($errors->has('status_list.' . $index)) is-invalid @endif"
                                                                data-control="select2" data-hide-search="true" required>
                                                                <option value="1" {{ old('status_list.' . $index, '1') == '1' ? 'selected' : '' }}>Aktif</option>
                                                                <option value="0" {{ old('status_list.' . $index, '1') == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                                                            </select>
                                                            @if ($errors->has('status_list.' . $index))
                                                                <div class="invalid-feedback">{{ $errors->first('status_list.' . $index) }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @elseif(isset($list_faq) && $list_faq->isNotEmpty())
                                @foreach ($list_faq as $index => $faq)
                                    <div class="faq-entry mb-7" data-index="{{ $index }}">
                                        <div class="card border">
                                            <div class="card-header border-0 pt-6">
                                                <div class="card-title">
                                                    <h4 class="fw-bold text-gray-800">
                                                        <i class="ki-outline ki-questionnaire-tablet text-primary fs-2 me-2"></i>
                                                        FAQ #<span class="faq-number">{{ $index + 1 }}</span>
                                                    </h4>
                                                </div>
                                                <div class="card-toolbar">
                                                    <button type="button" class="btn btn-icon btn-sm btn-light-danger remove-faq-entry" data-bs-toggle="tooltip"
                                                        title="Hapus FAQ ini">
                                                        <i class="ki-outline ki-trash fs-5"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card-body pt-0">
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <div class="mb-7 fv-row">
                                                            <label class="required form-label fw-semibold text-gray-700">Pertanyaan</label>
                                                            <input type="text" name="pertanyaan[]" class="form-control form-control-solid" placeholder="Masukkan pertanyaan FAQ"
                                                                value="{{ $faq->pertanyaan }}" autocomplete="off" required />
                                                            <div class="text-muted fs-7 mt-1">Masukkan pertanyaan singkat</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-12">
                                                        <div class="mb-7 fv-row">
                                                            <label class="required form-label fw-semibold text-gray-700">Jawaban</label>
                                                            <textarea name="jawaban[]" class="form-control form-control-solid jawaban-editor @if ($errors->has('jawaban.' . $index)) is-invalid @endif" placeholder="Masukkan jawaban FAQ"
                                                                id="jawaban-editor-{{ $index }}" required>{{ $faq->jawaban }}</textarea>
                                                            <div class="text-muted fs-7 mt-1">Gunakan editor untuk format jawaban</div>
                                                            @if ($errors->has('jawaban.' . $index))
                                                                <div class="invalid-feedback">{{ $errors->first('jawaban.' . $index) }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <div class="mb-7 fv-row">
                                                            <label class="required form-label fw-semibold text-gray-700">Status</label>
                                                            <select name="status_list[]" class="form-select form-select-solid" data-control="select2" data-hide-search="true"
                                                                required>
                                                                <option value="1" {{ $faq->status == '1' ? 'selected' : '' }}>Aktif</option>
                                                                <option value="0" {{ $faq->status == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        @else
                            <div id="no-faq-message" class="text-center py-10">
                                <div class="mb-7">
                                    <i class="ki-outline ki-questionnaire-tablet text-muted" style="font-size: 3rem;"></i>
                                </div>
                                <h3 class="text-gray-600 fw-semibold mb-2">Belum Ada FAQ</h3>
                                <p class="text-muted mb-0">Klik tombol "Tambah FAQ" untuk menambahkan pertanyaan pertama</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            {{-- end::FAQ List --}}

            <div class="d-flex justify-content-end">
                <a href="{{ route('prt.apps.faq.index') }}" id="kt_faq_cancel" class="btn btn-light me-5">
                    <i class="ki-outline ki-arrow-left fs-2"></i>Batal
                </a>
                <button type="submit" id="kt_faq_submit" class="btn btn-primary">
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
        {{-- end::Main column --}}
    </form>
    {{-- end::Form --}}

    <template id="faq-template">
        <div class="faq-entry mb-7" data-index="__INDEX__">
            <div class="card border">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <h4 class="fw-bold text-gray-800">
                            <i class="ki-outline ki-questionnaire-tablet text-primary fs-2 me-2"></i>
                            FAQ #<span class="faq-number">__NUMBER__</span>
                        </h4>
                    </div>
                    <div class="card-toolbar">
                        <button type="button" class="btn btn-icon btn-sm btn-light-danger remove-faq-entry" data-bs-toggle="tooltip" title="Hapus FAQ ini">
                            <i class="ki-outline ki-trash fs-5"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="mb-7 fv-row">
                                <label class="required form-label fw-semibold text-gray-700">Pertanyaan</label>
                                <input type="text" name="pertanyaan[]" class="form-control form-control-solid" placeholder="Masukkan pertanyaan FAQ" autocomplete="off"
                                    required />
                                <div class="text-muted fs-7 mt-1">Masukkan pertanyaan singkat</div>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="mb-7 fv-row">
                                <label class="required form-label fw-semibold text-gray-700">Jawaban</label>
                                <textarea name="jawaban[]" class="form-control form-control-solid jawaban-editor" placeholder="Masukkan jawaban FAQ" id="jawaban-editor-__INDEX__" required></textarea>
                                <div class="text-muted fs-7 mt-1">Gunakan editor untuk format jawaban</div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mb-7 fv-row">
                                <label class="required form-label fw-semibold text-gray-700">Status</label>
                                <select name="status_list[]" class="form-select form-select-solid" data-control="select2" data-hide-search="true" required>
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
@endsection
{{-- CONTENT::END --}}

{{-- SCRIPTS::BEGIN --}}
@push('scripts')
    <script src="{{ asset('be/plugins/custom/tinymce/tinymce.bundle.js') }}"></script>
    <script>
        "use strict";

        var KTAppFAQ = function() {
            var form, submitButton, cancelButton, faqFields, faqCounter;

            var updateFaqCounter = function() {
                faqCounter = document.querySelectorAll('.faq-entry').length;
                var counterElement = document.getElementById('faq_list_counter');
                if (counterElement) {
                    counterElement.innerText = faqCounter;
                }
                var noFaqMessage = document.getElementById('no-faq-message');
                if (noFaqMessage) {
                    noFaqMessage.style.display = faqCounter === 0 ? 'block' : 'none';
                }
            };

            var reindexFaqEntries = function() {
                var entries = document.querySelectorAll('.faq-entry');
                if (entries.length > 0) {
                    entries.forEach(function(entry, index) {
                        entry.setAttribute('data-index', index);
                        var numberElement = entry.querySelector('.faq-number');
                        if (numberElement) {
                            numberElement.innerText = index + 1;
                        }
                        var textarea = entry.querySelector('textarea');
                        if (textarea && !textarea.id) {
                            textarea.id = 'jawaban-editor-' + index;
                        }
                    });
                }
                updateFaqCounter();
            };

            var initSelect2 = function(selectElement) {
                if (selectElement && !$(selectElement).data('select2')) {
                    $(selectElement).select2({
                        minimumResultsForSearch: Infinity
                    }).on('select2:unselect', function() {
                        $(this).trigger('change');
                    });
                }
            };

            var initTinyMCE = function(editorElement) {
                var textarea = editorElement.querySelector('textarea');
                if (textarea && !textarea.classList.contains('tinymce-initialized')) {
                    if (!textarea.id) {
                        console.warn('Textarea without ID detected, skipping TinyMCE initialization:', textarea);
                        return;
                    }
                    tinymce.init({
                        selector: '#' + textarea.id,
                        height: 300,
                        toolbar: "advlist | autolink | link image | lists charmap | print preview",
                        plugins: "advlist autolink link image lists charmap print preview",
                        content_style: `
                    body {
                        font-family: 'Inter', sans-serif;
                        font-size: 14px;
                        line-height: 1.6;
                    }
                    img {
                        max-width: 100% !important;
                        width: 100% !important;
                        height: auto !important;
                        display: block;
                        margin: 15px auto;
                        border-radius: 8px;
                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                    }
                `,
                        paste_data_images: true,
                        paste_as_text: false,
                        paste_block_drop: false,
                        automatic_uploads: false,
                        images_upload_url: false,
                        convert_urls: false,
                        relative_urls: false,
                        setup: function(editor) {
                            editor.on('init', function() {
                                textarea.classList.add('tinymce-initialized');
                            });
                            editor.on('change', function() {
                                textarea.value = editor.getContent();
                            });
                        }
                    }).then(function(editors) {
                        if (textarea.value) {
                            editors[0].setContent(textarea.value);
                        }
                    }).catch(function(error) {
                        console.error('TinyMCE initialization failed for textarea #' + textarea.id + ':', error);
                    });
                }
            };

            var addFaqEntry = function() {
                var template = document.getElementById('faq-template');
                if (!template) {
                    console.error('FAQ template not found');
                    return;
                }
                var clone = template.content.cloneNode(true);
                var index = faqCounter;
                var faqEntry = clone.querySelector('.faq-entry');
                if (faqEntry) {
                    faqEntry.setAttribute('data-index', index);
                    var numberElement = faqEntry.querySelector('.faq-number');
                    if (numberElement) {
                        numberElement.innerText = index + 1;
                    }
                    var textarea = faqEntry.querySelector('textarea');
                    if (textarea) {
                        textarea.id = 'jawaban-editor-' + index;
                    }
                    faqFields.appendChild(clone);
                    faqCounter++;

                    var newSelect = faqEntry.querySelector('select');
                    initSelect2(newSelect);

                    initTinyMCE(faqEntry);

                    var noFaqMessage = document.getElementById('no-faq-message');
                    if (noFaqMessage) {
                        noFaqMessage.style.display = 'none';
                    }
                    reindexFaqEntries();
                }
            };

            var removeFaqEntry = function(element) {
                var faqEntry = element.closest('.faq-entry');
                if (faqEntry) {
                    var select = faqEntry.querySelector('select');
                    if (select && $(select).data('select2')) {
                        $(select).select2('destroy');
                    }
                    var textarea = faqEntry.querySelector('textarea');
                    if (textarea && tinymce.get(textarea.id)) {
                        tinymce.get(textarea.id).remove();
                    }
                    faqEntry.remove();
                    reindexFaqEntries();
                }
            };

            var handleSlugPreview = function() {
                const judulInput = document.querySelector('input[name="judul"]');
                const slugPreview = document.getElementById('faq_slug_preview');

                if (judulInput && slugPreview) {
                    judulInput.addEventListener('input', function() {
                        const slug = createSlug(this.value);
                        slugPreview.textContent = slug || '-';
                    });

                    // Set initial slug if editing
                    const initialJudul = judulInput.value;
                    if (initialJudul) {
                        slugPreview.textContent = createSlug(initialJudul);
                    }
                }
            };

            var createSlug = function(str) {
                return str
                    .toLowerCase()
                    .trim()
                    .replace(/[^\w\s-]/g, '') // Remove special characters
                    .replace(/[\s_-]+/g, '-') // Replace spaces and underscores with hyphens
                    .replace(/^-+|-+$/g, ''); // Remove leading/trailing hyphens
            };

            var handleCancelButton = function() {
                if (cancelButton) {
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
            };

            var initForm = function() {
                form = document.getElementById('kt_faq_form');
                submitButton = document.getElementById('kt_faq_submit');
                cancelButton = document.getElementById('kt_faq_cancel');
                faqFields = document.getElementById('faqFields');
                if (!faqFields) {
                    console.error('FAQ fields container not found');
                    return;
                }
                faqCounter = document.querySelectorAll('.faq-entry').length;

                // Initialize Select2 for existing selects
                document.querySelectorAll('select[data-control="select2"]').forEach(function(select) {
                    initSelect2(select);
                });

                // Reindex and initialize TinyMCE for existing editors
                reindexFaqEntries();
                var entries = document.querySelectorAll('.faq-entry');
                if (entries.length > 0) {
                    entries.forEach(function(entry) {
                        initTinyMCE(entry);
                    });
                }

                var addFaqButton = document.getElementById('addFaq');
                if (addFaqButton) {
                    addFaqButton.addEventListener('click', addFaqEntry);
                }

                if (faqFields) {
                    faqFields.addEventListener('click', function(e) {
                        if (e.target.closest('.remove-faq-entry')) {
                            removeFaqEntry(e.target.closest('.remove-faq-entry'));
                        }
                    });
                }

                if (submitButton) {
                    submitButton.addEventListener('click', function(e) {
                        e.preventDefault();
                        submitButton.setAttribute('data-kt-indicator', 'on');
                        submitButton.disabled = true;
                        setTimeout(function() {
                            if (form) form.submit();
                        }, 1000);
                    });
                }

                if (typeof KTImageInput !== 'undefined' && KTImageInput.createInstances) {
                    KTImageInput.createInstances();
                }
                updateFaqCounter();
                handleSlugPreview();
                handleCancelButton();
            };

            return {
                init: function() {
                    initForm();
                }
            };
        }();

        document.addEventListener('DOMContentLoaded', function() {
            // Function untuk auto-resize semua gambar di TinyMCE
            function autoResizeImages() {
                var editors = tinymce.editors;
                for (var i = 0; i < editors.length; i++) {
                    var editor = editors[i];
                    if (editor.getBody) {
                        var images = editor.getBody().querySelectorAll('img');

                        images.forEach(function(img) {
                            if (!img.hasAttribute('data-auto-resized')) {
                                img.style.width = '100%';
                                img.style.height = 'auto';
                                img.setAttribute('width', '100%');
                                img.setAttribute('height', 'auto');
                                img.setAttribute('data-auto-resized', 'true');
                            }
                        });
                    }
                }
            }
            setInterval(autoResizeImages, 1000);
        });

        KTUtil.onDOMContentLoaded(function() {
            if (typeof KTUtil !== 'undefined') {
                KTAppFAQ.init();
            } else {
                console.error('KTUtil is not defined');
            }
        });
    </script>
@endpush
{{-- SCRIPTS::END --}}
