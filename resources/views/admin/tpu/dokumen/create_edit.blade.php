{{-- resources/views/admin/tpu/dokumen/create_edit.blade.php --}}
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
                <li class="breadcrumb-item text-gray-700">Data Pendukung TPU</li>
                {{-- end::Item --}}
                {{-- begin::Item --}}
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                {{-- end::Item --}}
                {{-- begin::Item --}}
                <li class="breadcrumb-item text-gray-700">
                    <a href="{{ route('tpu.dokumen.index', $nama_modul) }}" class="text-gray-700 text-hover-primary">Dokumen {{ $nama_modul }}</a>
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
    <form id="kt_dokumen_form" class="form d-flex flex-column flex-lg-row"
        action="{{ isset($data) ? route('tpu.dokumen.update', [$nama_modul, $uuid_enc]) : route('tpu.dokumen.store', $nama_modul) }}"
        method="POST" enctype="multipart/form-data">
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
                                {{ isset($data) ? $data->created_at->format('d/m/Y H:i') : 'Baru' }}
                            </span>
                        </div>
                        <div class="m-0 p-0">
                            <span class="fw-bold text-gray-600">Diperbarui:</span><br />
                            <span class="text-gray-800 fw-bold">
                                {{ isset($data) ? $data->updated_at->format('d/m/Y H:i') : 'Baru' }}
                            </span>
                        </div>
                        @isset($data)
                            @if($data->url)
                                <div class="m-0 p-0">
                                    <span class="fw-bold text-gray-600">File Saat Ini:</span><br />
                                    <div class="d-flex align-items-center gap-2 mt-2">
                                        <span class="badge badge-light-info">{{ strtoupper($data->tipe) }}</span>
                                        <span class="text-muted fs-7">{{ \App\Helpers\Helper::formatFileSize($data->size) }}</span>
                                    </div>
                                    <a href="{{ route('tpu.dokumen.download', \App\Helpers\Helper::encode($data->uuid)) }}"
                                       class="btn btn-sm btn-light-primary mt-2">
                                        <i class="ki-outline ki-cloud-download fs-6"></i>Download
                                    </a>
                                </div>
                            @endif
                        @endisset
                    </div>
                    {{-- end::Information --}}
                </div>
                {{-- end::Card body --}}
            </div>
            {{-- end::Information settings --}}

            {{-- begin::File upload --}}
            <div class="card card-flush py-4">
                {{-- begin::Card header --}}
                <div class="card-header">
                    {{-- begin::Card title --}}
                    <div class="card-title">
                        <h2>File Dokumen</h2>
                    </div>
                    {{-- end::Card title --}}
                </div>
                {{-- end::Card header --}}
                {{-- begin::Card body --}}
                <div class="card-body pt-0">
                    {{-- begin::File input --}}
                    <div class="fv-row mb-2">
                        <label class="form-label {{ !isset($data) ? 'required' : '' }}">Upload File</label>
                        <input type="file" class="form-control form-control-solid" name="file"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif"
                               {{ !isset($data) ? 'required' : '' }}>
                        <div class="form-text">
                            Format yang didukung: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, JPEG, PNG, GIF.<br>
                            Maksimal ukuran file: 10MB.
                        </div>
                        @error('file')
                            <div class="fv-plugins-message-container invalid-feedback">
                                <div>{{ $message }}</div>
                            </div>
                        @enderror
                    </div>
                    {{-- end::File input --}}
                </div>
                {{-- end::Card body --}}
            </div>
            {{-- end::File upload --}}
        </div>
        {{-- end::Aside column --}}

        {{-- begin::Main column --}}
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            {{-- begin::General options --}}
            <div class="card card-flush py-4">
                {{-- begin::Card header --}}
                <div class="card-header">
                    <div class="card-title">
                        <h2>Informasi Dokumen {{ $nama_modul }}</h2>
                    </div>
                </div>
                {{-- end::Card header --}}
                {{-- begin::Card body --}}
                <div class="card-body pt-0">
                    {{-- begin::Input group --}}
                    <div class="mb-10 fv-row">
                        {{-- begin::Label --}}
                        <label class="required form-label">{{ $nama_modul === 'TPU' ? 'Pilih TPU' : ($nama_modul === 'Lahan' ? 'Pilih Lahan' : 'Pilih Sarpras') }}</label>
                        {{-- end::Label --}}
                        {{-- begin::Select2 --}}
                        <select class="form-select form-select-solid" name="uuid_modul" data-control="select2"
                                data-placeholder="Pilih {{ $nama_modul === 'TPU' ? 'TPU' : ($nama_modul === 'Lahan' ? 'Lahan' : 'Sarpras') }}..."
                                data-allow-clear="false">
                            <option></option>
                            @foreach($moduls as $modul)
                                <option value="{{ \App\Helpers\Helper::encode($modul->uuid) }}"
                                        {{ isset($data) && $data->uuid_modul === $modul->uuid ? 'selected' : '' }}>
                                    @if($nama_modul === 'TPU')
                                        {{ $modul->nama }}
                                    @elseif($nama_modul === 'Lahan')
                                        {{ $modul->nama }} ({{ $modul->Tpu->nama }})
                                    @else
                                        {{ $modul->nama }} ({{ $modul->Lahan->nama }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        {{-- end::Select2 --}}
                        @error('uuid_modul')
                            <div class="fv-plugins-message-container invalid-feedback">
                                <div>{{ $message }}</div>
                            </div>
                        @enderror
                    </div>
                    {{-- end::Input group --}}

                    {{-- begin::Input group --}}
                    <div class="mb-10 fv-row">
                        {{-- begin::Label --}}
                        <label class="required form-label">Kategori Dokumen</label>
                        {{-- end::Label --}}
                        {{-- begin::Select2 --}}
                        <select class="form-select form-select-solid" name="kategori" data-control="select2"
                                data-placeholder="Pilih kategori dokumen..." data-allow-clear="false">
                            <option></option>
                            @foreach($kategoris as $kategori)
                                <option value="{{ \App\Helpers\Helper::encode($kategori->uuid) }}"
                                        {{ isset($data) && $data->kategori === $kategori->uuid ? 'selected' : '' }}>
                                    {{ $kategori->nama }}
                                </option>
                            @endforeach
                        </select>
                        {{-- end::Select2 --}}
                        @error('kategori')
                            <div class="fv-plugins-message-container invalid-feedback">
                                <div>{{ $message }}</div>
                            </div>
                        @enderror
                    </div>
                    {{-- end::Input group --}}

                    {{-- begin::Input group --}}
                    <div class="mb-10 fv-row">
                        {{-- begin::Label --}}
                        <label class="required form-label">Nama File</label>
                        {{-- end::Label --}}
                        {{-- begin::Input --}}
                        <input type="text" name="nama_file" class="form-control form-control-solid mb-3 mb-lg-0"
                               placeholder="Masukkan nama file yang akan ditampilkan"
                               value="{{ old('nama_file', isset($data) ? $data->nama_file : '') }}" required>
                        {{-- end::Input --}}
                        <div class="form-text">Nama file yang akan ditampilkan di daftar dokumen.</div>
                        @error('nama_file')
                            <div class="fv-plugins-message-container invalid-feedback">
                                <div>{{ $message }}</div>
                            </div>
                        @enderror
                    </div>
                    {{-- end::Input group --}}

                    {{-- begin::Input group --}}
                    <div class="mb-10 fv-row">
                        {{-- begin::Label --}}
                        <label class="form-label">Deskripsi</label>
                        {{-- end::Label --}}
                        {{-- begin::Textarea --}}
                        <textarea name="deskripsi" class="form-control form-control-solid" rows="4"
                                  placeholder="Masukkan deskripsi dokumen (opsional)">{{ old('deskripsi', isset($data) ? $data->deskripsi : '') }}</textarea>
                        {{-- end::Textarea --}}
                        <div class="form-text">Deskripsi singkat mengenai isi dokumen.</div>
                        @error('deskripsi')
                            <div class="fv-plugins-message-container invalid-feedback">
                                <div>{{ $message }}</div>
                            </div>
                        @enderror
                    </div>
                    {{-- end::Input group --}}
                </div>
                {{-- end::Card body --}}
            </div>
            {{-- end::General options --}}

            {{-- begin::Actions --}}
            <div class="d-flex justify-content-end">
                {{-- begin::Button --}}
                <a href="{{ route('tpu.dokumen.index', $nama_modul) }}" class="btn btn-light me-5">Batal</a>
                {{-- end::Button --}}
                {{-- begin::Button --}}
                <button type="submit" class="btn btn-primary">
                    <span class="indicator-label">{{ $submit }}</span>
                    <span class="indicator-progress">Please wait...
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
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

{{-- JAVASCRIPT::BEGIN --}}
@push('javascript')
    <script>
        "use strict";

        // Class definition
        var KTDokumenCreateApp = function () {
            var form, submitButton;

            // Private functions
            var initForm = function () {
                // Get form element
                form = document.getElementById('kt_dokumen_form');
                submitButton = form.querySelector('[type="submit"]');

                // Submit button handler
                submitButton.addEventListener('click', function (e) {
                    // Prevent default button action
                    e.preventDefault();

                    // Validate form before submit
                    if (validateForm()) {
                        // Show loading indication
                        submitButton.setAttribute('data-kt-indicator', 'on');

                        // Disable button to avoid multiple click
                        submitButton.disabled = true;

                        // Submit form
                        form.submit();
                    }
                });
            }

            var validateForm = function () {
                // Get required fields
                var requiredFields = form.querySelectorAll('[required]');
                var isValid = true;

                // Check each required field
                requiredFields.forEach(function (field) {
                    if (!field.value.trim()) {
                        isValid = false;
                        // Add validation feedback
                        field.classList.add('is-invalid');

                        // Create feedback element if not exists
                        if (!field.nextElementSibling || !field.nextElementSibling.classList.contains('invalid-feedback')) {
                            var feedback = document.createElement('div');
                            feedback.classList.add('invalid-feedback');
                            feedback.innerHTML = 'Field ini wajib diisi.';
                            field.parentNode.insertBefore(feedback, field.nextSibling);
                        }
                    } else {
                        // Remove validation feedback
                        field.classList.remove('is-invalid');
                        if (field.nextElementSibling && field.nextElementSibling.classList.contains('invalid-feedback')) {
                            field.nextElementSibling.remove();
                        }
                    }
                });

                // Validate file if required (only for create)
                @if(!isset($data))
                var fileInput = form.querySelector('input[type="file"]');
                if (fileInput && !fileInput.files.length) {
                    isValid = false;
                    fileInput.classList.add('is-invalid');
                    if (!fileInput.nextElementSibling || !fileInput.nextElementSibling.classList.contains('invalid-feedback')) {
                        var feedback = document.createElement('div');
                        feedback.classList.add('invalid-feedback');
                        feedback.innerHTML = 'File dokumen wajib diupload.';
                        fileInput.parentNode.insertBefore(feedback, fileInput.nextSibling);
                    }
                } else if (fileInput && fileInput.files.length) {
                    fileInput.classList.remove('is-invalid');
                    if (fileInput.nextElementSibling && fileInput.nextElementSibling.classList.contains('invalid-feedback')) {
                        fileInput.nextElementSibling.remove();
                    }
                }
                @endif

                return isValid;
            }

            // Public methods
            return {
                init: function () {
                    initForm();
                }
            };
        }();

        // On document ready
        KTUtil.onDOMContentLoaded(function () {
            KTDokumenCreateApp.init();
        });

        // File input change handler
        $(document).on('change', 'input[type="file"]', function() {
            var file = this.files[0];
            if (file) {
                // Validate file size (10MB = 10 * 1024 * 1024 bytes)
                var maxSize = 10 * 1024 * 1024;
                if (file.size > maxSize) {
                    Swal.fire({
                        title: 'File Terlalu Besar!',
                        text: 'Ukuran file maksimal adalah 10MB.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    $(this).val('');
                    return;
                }

                // Validate file type
                var allowedTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif'];
                var fileExtension = file.name.split('.').pop().toLowerCase();

                if (!allowedTypes.includes(fileExtension)) {
                    Swal.fire({
                        title: 'Format File Tidak Didukung!',
                        text: 'Format file yang didukung: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, JPEG, PNG, GIF.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    $(this).val('');
                    return;
                }

                // Remove validation feedback if exists
                $(this).removeClass('is-invalid');
                $(this).next('.invalid-feedback').remove();
            }
        });
    </script>
@endpush
{{-- JAVASCRIPT::END --}}
