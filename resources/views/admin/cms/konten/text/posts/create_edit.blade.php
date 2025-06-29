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
                    <a href="{{ route('prt.apps.index') }}" class="text-gray-700 text-hover-primary">
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
                <li class="breadcrumb-item text-gray-700">Konten Text</li>
                {{-- end::Item --}}
                {{-- begin::Item --}}
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                {{-- end::Item --}}
                {{-- begin::Item --}}
                <li class="breadcrumb-item text-gray-700">
                    <a href="{{ route('prt.apps.post.index') }}" class="text-gray-700 text-hover-primary">Postingan</a>
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
    <form id="kt_postingan_form" class="form d-flex flex-column flex-lg-row" action="{{ isset($data) ? route('prt.apps.post.update', [$uuid_enc]) : route('prt.apps.post.store') }}"
        method="POST" enctype="multipart/form-data">
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
                    <select class="form-select mb-2 @error('status') is-invalid @enderror" data-control="select2" data-hide-search="true" data-placeholder="Pilih Status"
                        id="kt_postingan_status" name="status" required>
                        <option></option>
                        @if ($auth->role == 'Super Admin' || $auth->role == 'Admin' || $auth->role == 'Editor')
                            <option value="Draft" {{ old('status', isset($data) ? $data->status : '') == 'Draft' ? 'selected' : '' }}>Draft</option>
                            <option value="Pending Review" {{ old('status', isset($data) ? $data->status : '') == 'Pending Review' ? 'selected' : '' }}>Pending Review</option>
                            <option value="Published" {{ old('status', isset($data) ? $data->status : '') == 'Published' ? 'selected' : '' }}>Published</option>
                            <option value="Scheduled" {{ old('status', isset($data) ? $data->status : '') == 'Scheduled' ? 'selected' : '' }}>Scheduled</option>
                            <option value="Archived" {{ old('status', isset($data) ? $data->status : '') == 'Archived' ? 'selected' : '' }}>Archived</option>
                        @else
                            <option value="Draft" {{ old('status', isset($data) ? $data->status : '') == 'Draft' ? 'selected' : '' }}>Draft</option>
                            <option value="Pending Review" {{ old('status', isset($data) ? $data->status : '') == 'Pending Review' ? 'selected' : '' }}>Pending Review</option>
                        @endif
                    </select>
                    {{-- end::Select2 --}}
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    {{-- begin::Description --}}
                    <div class="text-muted fs-7">Pilih status publikasi postingan.</div>
                    {{-- end::Description --}}
                </div>
                {{-- end::Card body --}}
            </div>
            {{-- end::Status settings --}}

            {{-- begin::Thumbnail settings --}}
            <div class="card card-flush py-4">
                {{-- begin::Card header --}}
                <div class="card-header">
                    {{-- begin::Card title --}}
                    <div class="card-title">
                        <h2>Thumbnail</h2>
                    </div>
                    {{-- end::Card title --}}
                </div>
                {{-- end::Card header --}}
                {{-- begin::Card body --}}
                <div class="card-body text-center pt-0">
                    @if (isset($data) && $data->thumbnails != '')
                        {{-- begin::Image input placeholder --}}
                        <div class="image-input image-input-outline" data-kt-image-input="true" style="background-image: url('{{ asset('be/media/svg/files/blank-image.svg') }}')">
                            {{-- begin::Preview existing image --}}
                            <div class="image-input-wrapper w-150px h-150px" style="background-image: url('{{ \Helper::urlImg($data->thumbnails) }}')"></div>
                            {{-- end::Preview existing image --}}
                            {{-- begin::Label --}}
                            <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip"
                                title="Ubah thumbnail">
                                <i class="ki-outline ki-pencil fs-7"></i>
                                {{-- begin::Inputs --}}
                                <input type="file" name="thumbnails" accept=".png,.jpg,.jpeg" />
                                <input type="hidden" name="thumbnails_remove" />
                                {{-- end::Inputs --}}
                            </label>
                            {{-- end::Label --}}
                            {{-- begin::Cancel --}}
                            <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip"
                                title="Batalkan thumbnail">
                                <i class="ki-outline ki-cross fs-2"></i>
                            </span>
                            {{-- end::Cancel --}}
                            {{-- begin::Remove --}}
                            <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip"
                                title="Hapus thumbnail">
                                <i class="ki-outline ki-cross fs-2"></i>
                            </span>
                            {{-- end::Remove --}}
                        </div>
                        {{-- end::Image input placeholder --}}
                        @error('thumbnails')
                            <div class="text-danger fs-7 mt-2">{{ $message }}</div>
                        @enderror
                        {{-- begin::View button --}}
                        <div class="mt-3">
                            <a href="{{ \Helper::urlImg($data->thumbnails) }}" target="_blank" class="btn btn-sm btn-light-primary">
                                <i class="ki-outline ki-eye fs-3"></i>Lihat Thumbnail
                            </a>
                        </div>
                        {{-- end::View button --}}
                        {{-- begin::Description --}}
                        <div class="text-muted fs-7 mt-3">Format: PNG, JPG, JPEG | Max: 2MB<br>Layout landscape akan di-resize ke 1200x628px</div>
                        {{-- end::Description --}}
                    @else
                        {{-- begin::Image input placeholder --}}
                        <div class="image-input image-input-outline image-input-placeholder" data-kt-image-input="true">
                            {{-- begin::Preview existing image --}}
                            <div class="image-input-wrapper w-150px h-150px"></div>
                            {{-- end::Preview existing image --}}
                            {{-- begin::Label --}}
                            <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip"
                                title="Pilih thumbnail">
                                <i class="ki-outline ki-pencil fs-7"></i>
                                {{-- begin::Inputs --}}
                                <input type="file" name="thumbnails" accept=".png,.jpg,.jpeg" required />
                                <input type="hidden" name="thumbnails_remove" />
                                {{-- end::Inputs --}}
                            </label>
                            {{-- end::Label --}}
                            {{-- begin::Cancel --}}
                            <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip"
                                title="Batalkan thumbnail">
                                <i class="ki-outline ki-cross fs-2"></i>
                            </span>
                            {{-- end::Cancel --}}
                            {{-- begin::Remove --}}
                            <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip"
                                title="Hapus thumbnail">
                                <i class="ki-outline ki-cross fs-2"></i>
                            </span>
                            {{-- end::Remove --}}
                        </div>
                        {{-- end::Image input placeholder --}}
                        @error('thumbnails')
                            <div class="text-danger fs-7 mt-2">{{ $message }}</div>
                        @enderror
                        {{-- begin::Description --}}
                        <div class="text-muted fs-7 mt-3">Format: PNG, JPG, JPEG | Max: 2MB<br>Layout landscape akan di-resize ke 1200x628px</div>
                        {{-- end::Description --}}
                    @endif
                </div>
                {{-- end::Card body --}}
            </div>
            {{-- end::Thumbnail settings --}}

            {{-- begin::Category settings --}}
            <div class="card card-flush py-4">
                {{-- begin::Card header --}}
                <div class="card-header">
                    {{-- begin::Card title --}}
                    <div class="card-title">
                        <h2>Kategori</h2>
                    </div>
                    {{-- end::Card title --}}
                </div>
                {{-- end::Card header --}}
                {{-- begin::Card body --}}
                <div class="card-body pt-0">
                    {{-- begin::Select2 --}}
                    <select class="form-select mb-2 @error('kategori') is-invalid @enderror" data-control="select2" data-placeholder="Pilih kategori..." data-allow-clear="true"
                        multiple="multiple" name="kategori[]" required>
                        @if (isset($data))
                            @php
                                $exKategori = explode(',', $data->kategori);
                            @endphp
                            @foreach ($kategori as $item)
                                <option value="{{ $item->nama }}" @if (in_array($item->nama, $exKategori)) selected @endif>{{ $item->nama }}</option>
                            @endforeach
                        @else
                            @foreach ($kategori as $item)
                                <option @if (old('kategori') && in_array($item->nama, old('kategori'))) selected @endif value="{{ $item->nama }}">{{ $item->nama }}</option>
                            @endforeach
                        @endif
                    </select>
                    {{-- end::Select2 --}}
                    @error('kategori')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    {{-- begin::Description --}}
                    <div class="text-muted fs-7">Pilih satu atau lebih kategori untuk postingan ini.</div>
                    {{-- end::Description --}}
                </div>
                {{-- end::Card body --}}
            </div>
            {{-- end::Category settings --}}

            {{-- begin::Information --}}
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
                            <span class="fw-bold text-gray-600">Slug:</span><br />
                            <span class="text-gray-800 fw-bold" id="postingan_slug_preview">{{ isset($data) ? $data->slug : '-' }}</span>
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
                    {{-- end::Information --}}
                </div>
                {{-- end::Card body --}}
            </div>
            {{-- end::Information --}}
        </div>
        {{-- end::Aside column --}}

        {{-- begin::Main column --}}
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            {{-- begin::General options --}}
            <div class="card card-flush py-4">
                {{-- begin::Card header --}}
                <div class="card-header">
                    <div class="card-title">
                        <h2>Detail Postingan</h2>
                    </div>
                </div>
                {{-- end::Card header --}}
                {{-- begin::Card body --}}
                <div class="card-body pt-0">
                    {{-- begin::Input group --}}
                    <div class="mb-10 fv-row">
                        {{-- begin::Label --}}
                        <label class="required form-label">Judul Postingan</label>
                        {{-- end::Label --}}
                        {{-- begin::Input --}}
                        <input type="text" name="judul" id="judul" class="form-control mb-2 @error('judul') is-invalid @enderror" placeholder="Masukkan judul postingan"
                            value="{{ old('judul', isset($data) ? $data->judul : '') }}" maxlength="300" autocomplete="off" required />
                        {{-- end::Input --}}
                        {{-- begin::Description --}}
                        <div class="text-muted fs-7">Judul postingan maksimal 300 karakter.</div>
                        {{-- end::Description --}}
                        @error('judul')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- end::Input group --}}

                    {{-- begin::Input group --}}
                    <div class="mb-10 fv-row">
                        {{-- begin::Label --}}
                        <label class="required form-label">Tanggal Publikasi</label>
                        {{-- end::Label --}}
                        {{-- begin::Input --}}
                        <input type="datetime-local" name="tanggal" id="tanggal" class="form-control mb-2 @error('tanggal') is-invalid @enderror"
                            value="{{ old('tanggal', isset($data) ? date('Y-m-d\TH:i', strtotime($data->tanggal)) : date('Y-m-d\TH:i')) }}" autocomplete="off" required />
                        {{-- end::Input --}}
                        {{-- begin::Description --}}
                        <div class="text-muted fs-7">Tanggal dan waktu publikasi postingan.</div>
                        {{-- end::Description --}}
                        @error('tanggal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- end::Input group --}}

                    {{-- begin::Input group --}}
                    <div class="mb-10 fv-row">
                        {{-- begin::Label --}}
                        <label class="required form-label">Deskripsi SEO</label>
                        {{-- end::Label --}}
                        {{-- begin::Input --}}
                        <textarea name="deskripsi" id="deskripsi" class="form-control mb-2 @error('deskripsi') is-invalid @enderror" rows="2" placeholder="Masukkan deskripsi untuk SEO"
                            maxlength="160" autocomplete="off" required>{{ old('deskripsi', isset($data) ? $data->deskripsi : '') }}</textarea>
                        {{-- end::Input --}}
                        {{-- begin::Description --}}
                        <div class="text-muted fs-7">Deskripsi untuk meningkatkan SEO (Search Engine Optimization) | Maksimal 160 karakter.</div>
                        {{-- end::Description --}}
                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- end::Input group --}}
                </div>
                {{-- end::Card body --}}
            </div>
            {{-- end::General options --}}

            {{-- begin::Content editor --}}
            <div class="card card-flush py-4">
                {{-- begin::Card header --}}
                <div class="card-header">
                    <div class="card-title">
                        <h2>Konten Postingan</h2>
                    </div>
                </div>
                {{-- end::Card header --}}
                {{-- begin::Card body --}}
                <div class="card-body pt-0">
                    {{-- begin::Input group --}}
                    <div class="fv-row">
                        {{-- begin::Label --}}
                        <label class="required form-label">Isi Postingan</label>
                        {{-- end::Label --}}
                        {{-- begin::Editor --}}
                        <textarea name="post" id="post" class="form-control @error('post') is-invalid @enderror" placeholder="Masukkan konten postingan" required>{{ old('post') }}</textarea>
                        {{-- end::Editor --}}
                        @error('post')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- end::Input group --}}
                </div>
                {{-- end::Card body --}}
            </div>
            {{-- end::Content editor --}}

            {{-- begin::Actions --}}
            <div class="d-flex justify-content-end">
                {{-- begin::Button --}}
                <a href="{{ route('prt.apps.post.index') }}" id="kt_postingan_cancel" class="btn btn-light me-5">
                    <i class="ki-outline ki-arrow-left fs-2"></i>Batal
                </a>
                {{-- end::Button --}}
                {{-- begin::Button --}}
                <button type="submit" id="kt_postingan_submit" class="btn btn-primary">
                    <span class="indicator-label">
                        <i class="fa-solid fa-save me-2"></i>{{ $submit }}
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
    {{-- TinyMCE --}}
    <script src="{{ asset('be/plugins/custom/tinymce/tinymce.bundle.js') }}"></script>

    <script>
        "use strict";

        // Class definition
        var KTAppPostinganSave = function() {
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
                    var judul = form.querySelector('input[name="judul"]').value.trim();
                    var tanggal = form.querySelector('input[name="tanggal"]').value.trim();
                    var deskripsi = form.querySelector('textarea[name="deskripsi"]').value.trim();
                    var status = form.querySelector('select[name="status"]').value;
                    var kategori = form.querySelector('select[name="kategori[]"]');
                    var thumbnails = form.querySelector('input[name="thumbnails"]');
                    var post = tinymce.get('post').getContent().trim();

                    var isValid = true;
                    var errorMessage = '';

                    // Validate judul
                    if (!judul) {
                        isValid = false;
                        errorMessage = 'Judul postingan wajib diisi';
                    } else if (judul.length > 300) {
                        isValid = false;
                        errorMessage = 'Judul postingan maksimal 300 karakter';
                    }

                    // Validate tanggal
                    if (!tanggal) {
                        isValid = false;
                        errorMessage = 'Tanggal publikasi wajib diisi';
                    }

                    // Validate deskripsi
                    if (!deskripsi) {
                        isValid = false;
                        errorMessage = 'Deskripsi SEO wajib diisi';
                    } else if (deskripsi.length > 160) {
                        isValid = false;
                        errorMessage = 'Deskripsi SEO maksimal 160 karakter';
                    }

                    // Validate status
                    if (!status) {
                        isValid = false;
                        errorMessage = 'Status wajib dipilih';
                    }

                    // Validate kategori
                    var selectedKategori = $(kategori).val();
                    if (!selectedKategori || selectedKategori.length === 0) {
                        isValid = false;
                        errorMessage = 'Kategori wajib dipilih';
                    }

                    // Validate thumbnails (only for create)
                    @if (!isset($data))
                        if (!thumbnails.files.length) {
                            isValid = false;
                            errorMessage = 'Thumbnail wajib dipilih';
                        }
                    @endif

                    // Validate post content
                    if (!post || post === '<p></p>' || post === '' || post === '<p><br></p>') {
                        isValid = false;
                        errorMessage = 'Konten postingan wajib diisi';
                    }

                    if (isValid) {
                        // Sync TinyMCE content to form
                        tinymce.get('post').save();

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

            // Handle slug preview
            var handleSlugPreview = function() {
                const judulInput = document.querySelector('input[name="judul"]');
                const slugPreview = document.querySelector('#postingan_slug_preview');

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

            // handleTinyMCE
            var handleTinyMCE = function() {
                tinymce.init({
                    selector: "#post",
                    height: 480,
                    menubar: false,
                    statusbar: false,
                    toolbar_mode: 'sliding',
                    toolbar: [
                        "styleselect fontselect fontsizeselect",
                        "undo redo | cut copy paste | bold italic underline strikethrough | link image | alignleft aligncenter alignright alignjustify",
                        "bullist numlist | outdent indent | blockquote subscript superscript | table | charmap | print preview | code fullscreen"
                    ],
                    plugins: "advlist autolink link image lists charmap print preview code fullscreen table paste wordcount",

                    // Content style
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

                    // Paste config
                    paste_data_images: true,
                    paste_as_text: false,
                    paste_block_drop: false,

                    // DISABLE automatic uploads
                    automatic_uploads: false,
                    images_upload_url: false,

                    // Convert URLs
                    convert_urls: false,
                    relative_urls: false,

                    // File picker
                    file_picker_callback: function(callback, value, meta) {
                        if (meta.filetype === 'image') {
                            var input = document.createElement('input');
                            input.type = 'file';
                            input.accept = 'image/*';

                            input.onchange = function() {
                                var file = this.files[0];
                                if (!file) return;

                                if (file.size > 5 * 1024 * 1024) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'File Terlalu Besar',
                                        text: 'Ukuran maksimal 5MB'
                                    });
                                    return;
                                }

                                if (!file.type.match(/^image\//)) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Format Salah',
                                        text: 'File harus berupa gambar'
                                    });
                                    return;
                                }

                                var reader = new FileReader();
                                reader.onload = function(e) {
                                    callback(e.target.result, {
                                        alt: file.name.replace(/\.[^/.]+$/, ""),
                                        title: file.name
                                    });
                                };
                                reader.readAsDataURL(file);
                            };

                            input.click();
                        }
                    },

                    setup: function(editor) {
                        // Function untuk auto-style images
                        function autoStyleImages() {
                            var images = editor.getBody().querySelectorAll('img');
                            images.forEach(function(img) {
                                if (!img.hasAttribute('data-styled')) {
                                    img.style.width = '100%';
                                    img.style.height = 'auto';
                                    img.style.maxWidth = '100%';
                                    img.style.display = 'block';
                                    img.style.margin = '15px auto';
                                    img.setAttribute('width', '100%');
                                    img.setAttribute('height', 'auto');
                                    img.setAttribute('data-styled', 'true');
                                }
                            });
                        }

                        editor.on('init', function() {
                            @if (isset($data))
                                var content = {!! json_encode($data->post) !!};
                                editor.setContent(content);
                            @endif

                            setTimeout(autoStyleImages, 500);
                        });

                        editor.on('change', function() {
                            editor.save();
                        });

                        editor.on('NodeChange', function(e) {
                            if (e.element.nodeName === 'IMG') {
                                autoStyleImages();
                            }
                        });

                        editor.on('SetContent paste', function() {
                            setTimeout(autoStyleImages, 100);
                        });

                        // Handle paste events untuk direct file handling
                        editor.on('paste', function(e) {
                            var clipboardData = e.clipboardData || window.clipboardData;

                            if (clipboardData && clipboardData.items) {
                                for (var i = 0; i < clipboardData.items.length; i++) {
                                    var item = clipboardData.items[i];

                                    if (item.type.indexOf('image') !== -1) {
                                        e.preventDefault();

                                        var file = item.getAsFile();
                                        if (file) {
                                            var reader = new FileReader();
                                            reader.onload = function(event) {
                                                var imgHtml = '<img src="' + event.target.result +
                                                    '" style="width: 100%; height: auto; max-width: 100%; display: block; margin: 15px auto;" alt="Pasted image" data-styled="true">';
                                                editor.insertContent(imgHtml);
                                            };
                                            reader.readAsDataURL(file);
                                        }
                                        break;
                                    }
                                }
                            }
                        });

                        // Handle drop events
                        editor.on('drop', function(e) {
                            var files = e.dataTransfer.files;

                            if (files && files.length > 0) {
                                for (var i = 0; i < files.length; i++) {
                                    var file = files[i];

                                    if (file.type.match(/^image\//)) {
                                        e.preventDefault();

                                        var reader = new FileReader();
                                        reader.onload = function(event) {
                                            var imgHtml = '<img src="' + event.target.result +
                                                '" style="width: 100%; height: auto; max-width: 100%; display: block; margin: 15px auto;" alt="Dropped image" data-styled="true">';
                                            editor.insertContent(imgHtml);
                                        };
                                        reader.readAsDataURL(file);
                                        break;
                                    }
                                }
                            }
                        });
                    },

                    // Security
                    verify_html: false,
                    valid_elements: 'p,br,strong,em,h1,h2,h3,h4,h5,h6,ul,ol,li,a[href|target|title],img[src|alt|width|height|style|data-*],table,tr,td,th,tbody,thead,tfoot,blockquote,div,span[style],sub,sup,strike,u,code,pre',
                    extended_valid_elements: 'img[*]',

                    // Style formats
                    style_formats: [{
                            title: 'Paragraph',
                            block: 'p'
                        },
                        {
                            title: 'Heading 1',
                            block: 'h1'
                        },
                        {
                            title: 'Heading 2',
                            block: 'h2'
                        },
                        {
                            title: 'Heading 3',
                            block: 'h3'
                        },
                        {
                            title: 'Heading 4',
                            block: 'h4'
                        },
                        {
                            title: 'Heading 5',
                            block: 'h5'
                        },
                        {
                            title: 'Heading 6',
                            block: 'h6'
                        }
                    ],

                    // Font options
                    font_family_formats: "Arial=arial,helvetica,sans-serif; Courier New=courier new,courier,monospace; Georgia=georgia,palatino,serif; Helvetica=helvetica,arial,sans-serif; Impact=impact,sans-serif; Tahoma=tahoma,arial,helvetica,sans-serif; Times New Roman=times new roman,times,serif; Verdana=verdana,arial,helvetica,sans-serif;",
                    font_size_formats: "8pt 9pt 10pt 11pt 12pt 14pt 16pt 18pt 20pt 24pt 28pt 32pt 36pt 48pt"
                });
            }

            // Public methods
            return {
                init: function() {
                    // Elements
                    form = document.querySelector('#kt_postingan_form');
                    submitButton = document.querySelector('#kt_postingan_submit');
                    cancelButton = document.querySelector('#kt_postingan_cancel');

                    if (!form) {
                        return;
                    }

                    handleForm();
                    handleSlugPreview();
                    handleTinyMCE();
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

        // On document ready
        KTUtil.onDOMContentLoaded(function() {
            KTAppPostinganSave.init();
        });
    </script>

    {{-- Image Input --}}
    <script>
        // Initialize image input plugins
        KTUtil.onDOMContentLoaded(function() {
            // Image input
            var imageInputs = document.querySelectorAll('[data-kt-image-input]');
            imageInputs.forEach(function(element) {
                new KTImageInput(element);
            });
        });
    </script>
@endpush
{{-- SCRIPTS::END --}}
