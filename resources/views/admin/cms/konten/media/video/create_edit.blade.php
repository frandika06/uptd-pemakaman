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
                <li class="breadcrumb-item text-gray-700">Konten</li>
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                <li class="breadcrumb-item text-gray-700">Media</li>
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                <li class="breadcrumb-item text-gray-700">
                    <a href="{{ route('prt.apps.video.index') }}" class="text-gray-700 text-hover-primary">Video</a>
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
    <form id="kt_video_form" class="form d-flex flex-column flex-lg-row" action="{{ isset($data) ? route('prt.apps.video.update', [$uuid_enc]) : route('prt.apps.video.store') }}"
        method="POST" enctype="multipart/form-data">
        @csrf
        @isset($data)
            @method('PUT')
        @endisset

        {{-- begin::Aside column --}}
        <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
            {{-- begin::Status settings --}}
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Status</h2>
                    </div>
                </div>
                <div class="card-body text-center pt-0">
                    <select class="form-select mb-2 @error('status') is-invalid @enderror" data-control="select2" data-hide-search="true" data-placeholder="Pilih Status"
                        id="kt_video_status" name="status" required>
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
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="text-muted fs-7">Pilih status publikasi video.</div>
                </div>
            </div>
            {{-- end::Status settings --}}

            {{-- begin::Thumbnail settings --}}
            <div class="card card-flush py-4 {{ old('sumber', isset($data) ? $data->sumber : '') != 'Upload' ? 'd-none' : '' }}" id="thumbnail_card">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Thumbnail</h2>
                    </div>
                </div>
                <div class="card-body text-center pt-0">
                    @if (isset($data) && $data->sumber == 'Upload' && !empty($data->thumbnails))
                        <div class="image-input image-input-outline" data-kt-image-input="true" style="background-image: url('{{ asset('be/media/svg/files/blank-image.svg') }}')">
                            <div class="image-input-wrapper w-150px h-150px" style="background-image: url('{{ \Helper::urlImg($data->thumbnails) }}')"></div>
                            <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip"
                                title="Ubah thumbnail">
                                <i class="ki-outline ki-pencil fs-7"></i>
                                <input type="file" name="thumbnails" id="thumbnails" accept=".png,.jpg,.jpeg" />
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
                        <div class="mt-3">
                            <a href="{{ \Helper::urlImg($data->thumbnails) }}" target="_blank" class="btn btn-sm btn-light-primary">
                                <i class="ki-outline ki-eye fs-3"></i>Lihat Thumbnail
                            </a>
                        </div>
                        <div class="text-muted fs-7 mt-3">Format: PNG, JPG, JPEG | Max: 2MB<br>Layout landscape akan di-resize ke 1200x628px</div>
                    @else
                        <div class="image-input image-input-outline image-input-placeholder" data-kt-image-input="true">
                            <div class="image-input-wrapper w-150px h-150px"></div>
                            <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip"
                                title="Pilih thumbnail">
                                <i class="ki-outline ki-pencil fs-7"></i>
                                <input type="file" name="thumbnails" id="thumbnails" accept=".png,.jpg,.jpeg"
                                    {{ old('sumber', isset($data) ? $data->sumber : '') == 'Upload' && !isset($data) ? 'required' : '' }} />
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

            {{-- begin::Category settings --}}
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Kategori</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <select class="form-select mb-2 @error('kategori') is-invalid @enderror" data-control="select2" data-placeholder="Pilih kategori..." name="kategori" required>
                        <option></option>
                        @foreach ($kategoriList as $item)
                            <option value="{{ $item->nama }}" {{ old('kategori', isset($data) ? $data->kategori : '') == $item->nama ? 'selected' : '' }}>{{ $item->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('kategori')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="text-muted fs-7">Pilih kategori untuk video ini.</div>
                </div>
            </div>
            {{-- end::Category settings --}}

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
                            <span class="text-gray-800 fw-bold" id="video_slug_preview">{{ isset($data) ? $data->slug : '-' }}</span>
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
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Detail Video</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="mb-10 fv-row">
                        <label class="required form-label">Sumber Video</label>
                        @if (isset($data))
                            <input type="text" class="form-control mb-2" value="{{ old('sumber', $data->sumber) }}" readonly>
                            <input type="hidden" name="sumber" id="sumber" value="{{ old('sumber', $data->sumber) }}">
                        @else
                            <select class="form-select mb-2 @error('sumber') is-invalid @enderror" id="sumber" name="sumber" required>
                                <option value="">Pilih sumber video...</option>
                                <option value="YouTube" {{ old('sumber') == 'YouTube' ? 'selected' : '' }}>YouTube</option>
                                <option value="Upload" {{ old('sumber') == 'Upload' ? 'selected' : '' }}>Upload</option>
                            </select>
                            @error('sumber')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                        <div class="text-muted fs-7">Pilih sumber video (YouTube atau Upload).</div>
                    </div>

                    <div class="mb-10 fv-row {{ old('sumber', isset($data) ? $data->sumber : '') != 'YouTube' ? 'd-none' : '' }}" id="item_youtube">
                        <label class="form-label {{ old('sumber', isset($data) ? $data->sumber : '') == 'YouTube' ? 'required' : '' }}">URL YouTube</label>
                        <input type="url" name="url" id="url" class="form-control mb-2 @error('url') is-invalid @enderror" placeholder="Masukkan URL YouTube"
                            value="{{ old('url', isset($data) ? $data->url : '') }}" maxlength="300" autocomplete="off"
                            {{ old('sumber', isset($data) ? $data->sumber : '') == 'YouTube' ? 'required' : '' }} />
                        <div class="text-muted fs-7">Contoh: <strong>https://www.youtube.com/watch?v=3oD5YSTCun0</strong></div>
                        @error('url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-10 fv-row {{ old('sumber', isset($data) ? $data->sumber : '') != 'Upload' ? 'd-none' : '' }}" id="item_upload">
                        <div class="mb-10 fv-row">
                            <label class="form-label {{ old('sumber', isset($data) ? $data->sumber : '') == 'Upload' && !isset($data) ? 'required' : '' }}">File Video</label>
                            <input type="file" name="file_video" id="file_video" class="form-control mb-2 @error('file_video') is-invalid @enderror" accept=".mp4,.webm,.ogg"
                                {{ old('sumber', isset($data) ? $data->sumber : '') == 'Upload' && !isset($data) ? 'required' : '' }} />
                            <div class="text-muted fs-7">Format: MP4, WebM, OGG | Max: 200MB</div>
                            @error('file_video')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-10 fv-row">
                        <label class="required form-label">Judul Video</label>
                        <input type="text" name="judul" id="judul" class="form-control mb-2 @error('judul') is-invalid @enderror" placeholder="Masukkan judul video"
                            value="{{ old('judul', isset($data) ? $data->judul : '') }}" maxlength="300" autocomplete="off" required />
                        <div class="text-muted fs-7">Judul video maksimal 300 karakter.</div>
                        @error('judul')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-10 fv-row">
                        <label class="required form-label">Tanggal Publikasi</label>
                        <input type="datetime-local" name="tanggal" id="tanggal" class="form-control mb-2 @error('tanggal') is-invalid @enderror"
                            value="{{ old('tanggal', isset($data) ? date('Y-m-d\TH:i', strtotime($data->tanggal)) : date('Y-m-d\TH:i')) }}" autocomplete="off" required />
                        <div class="text-muted fs-7">Tanggal dan waktu publikasi video.</div>
                        @error('tanggal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-10 fv-row">
                        <label class="required form-label">Deskripsi SEO</label>
                        <textarea name="deskripsi" id="deskripsi" class="form-control mb-2 @error('deskripsi') is-invalid @enderror" rows="2" placeholder="Masukkan deskripsi untuk SEO"
                            maxlength="160" autocomplete="off" required>{{ old('deskripsi', isset($data) ? $data->deskripsi : '') }}</textarea>
                        <div class="text-muted fs-7">Deskripsi untuk meningkatkan SEO (Search Engine Optimization) | Maksimal 160 karakter.</div>
                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            @if (isset($data))
                <div class="card card-flush py-4">
                    <div class="card-header">
                        <div class="card-title">
                            <h2>Preview Video</h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        @if ($data->sumber == 'YouTube')
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="ki-outline ki-information-3 fs-2 me-2"></i>
                                <strong>PERHATIAN!</strong> Jika video tidak muncul, berarti ada kesalahan pada URL; video telah dihapus; atau URL teblokir oleh server CSP.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <div class="ratio ratio-16x9">
                                <iframe src="https://www.youtube.com/embed/{{ \Helper::getYouTubeVideoID($data->url) }}?rel=0" title="{{ e($data->judul) }}"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen loading="lazy"
                                    referrerpolicy="strict-origin-when-cross-origin"></iframe>
                            </div>
                        @else
                            <video width="100%" height="auto" controls>
                                <source src="{{ \Helper::urlImg($data->url) }}" type="video/{{ $data->tipe }}">
                                Your browser does not support the video tag.
                            </video>
                        @endif
                    </div>
                </div>
            @endif

            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Konten Post</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="fv-row">
                        <label class="form-label">Isi Post</label>
                        <textarea name="post" id="post" class="form-control @error('post') is-invalid @enderror" placeholder="Masukkan konten post"></textarea>
                        @error('post')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('prt.apps.video.index') }}" id="kt_video_cancel" class="btn btn-light me-5">
                    <i class="ki-outline ki-arrow-left fs-2"></i>Batal
                </a>
                <button type="submit" id="kt_video_submit" class="btn btn-primary">
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
@endsection
{{-- CONTENT::END --}}

{{-- SCRIPTS::BEGIN --}}
@push('scripts')
    <script src="{{ asset('be/plugins/custom/tinymce/tinymce.bundle.js') }}"></script>

    <script>
        "use strict";

        var KTAppVideoSave = function() {
            var form;
            var submitButton;
            var cancelButton;

            var handleForm = function() {
                submitButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    var judul = form.querySelector('input[name="judul"]').value.trim();
                    var tanggal = form.querySelector('input[name="tanggal"]').value.trim();
                    var deskripsi = form.querySelector('textarea[name="deskripsi"]').value.trim();
                    var status = form.querySelector('select[name="status"]').value;
                    var kategori = form.querySelector('select[name="kategori"]').value;
                    var sumber = form.querySelector('#sumber') ? form.querySelector('#sumber').value : '{{ old('sumber', isset($data) ? $data->sumber : '') }}';
                    var url = form.querySelector('input[name="url"]') ? form.querySelector('input[name="url"]').value.trim() : '';
                    var fileVideo = form.querySelector('input[name="file_video"]');
                    var thumbnails = form.querySelector('input[name="thumbnails"]');
                    var post = tinymce.get('post').getContent().trim();

                    var isValid = true;
                    var errorMessage = '';

                    if (!judul) {
                        isValid = false;
                        errorMessage = 'Judul video wajib diisi';
                    } else if (judul.length > 300) {
                        isValid = false;
                        errorMessage = 'Judul video maksimal 300 karakter';
                    }

                    if (!tanggal) {
                        isValid = false;
                        errorMessage = 'Tanggal publikasi wajib diisi';
                    }

                    if (!deskripsi) {
                        isValid = false;
                        errorMessage = 'Deskripsi SEO wajib diisi';
                    } else if (deskripsi.length > 160) {
                        isValid = false;
                        errorMessage = 'Deskripsi SEO maksimal 160 karakter';
                    }

                    if (!status) {
                        isValid = false;
                        errorMessage = 'Status wajib dipilih';
                    }

                    if (!kategori) {
                        isValid = false;
                        errorMessage = 'Kategori wajib dipilih';
                    }

                    if (!sumber) {
                        isValid = false;
                        errorMessage = 'Sumber video wajib dipilih';
                    }

                    if (sumber === 'YouTube' && !url) {
                        isValid = false;
                        errorMessage = 'URL YouTube wajib diisi';
                    } else if (sumber === 'YouTube' && url && !url.match(/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+$/)) {
                        isValid = false;
                        errorMessage = 'URL YouTube tidak valid';
                    }

                    if (sumber === 'Upload' && !{{ isset($data) ? 'true' : 'false' }}) {
                        if (!fileVideo || !fileVideo.files.length) {
                            isValid = false;
                            errorMessage = 'File video wajib diisi';
                        }
                        if (!thumbnails || !thumbnails.files.length) {
                            isValid = false;
                            errorMessage = 'Thumbnail wajib diisi';
                        }
                    }

                    if (isValid) {
                        tinymce.get('post').save();
                        submitButton.setAttribute('data-kt-indicator', 'on');
                        submitButton.disabled = true;
                        form.submit();
                    } else {
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

            var handleSlugPreview = function() {
                const judulInput = document.querySelector('input[name="judul"]');
                const slugPreview = document.querySelector('#video_slug_preview');

                if (judulInput && slugPreview) {
                    judulInput.addEventListener('input', function() {
                        const slug = createSlug(this.value);
                        slugPreview.textContent = slug || '-';
                    });

                    const initialJudul = judulInput.value;
                    if (initialJudul) {
                        slugPreview.textContent = createSlug(initialJudul);
                    }
                }
            }

            var createSlug = function(str) {
                return str
                    .toLowerCase()
                    .trim()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/[\s_-]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }

            var handleSumberToggle = function() {
                const sumberSelect = document.querySelector('#sumber');
                const itemYouTube = document.querySelector('#item_youtube');
                const itemUpload = document.querySelector('#item_upload');
                const thumbnailCard = document.querySelector('#thumbnail_card');
                const urlInput = document.querySelector('#url');
                const fileVideoInput = document.querySelector('#file_video');
                const thumbnailsInput = document.querySelector('#thumbnails');

                const isEditMode = {{ isset($data) ? 'true' : 'false' }};

                function toggleItems() {
                    if (!sumberSelect || !itemYouTube || !itemUpload || !thumbnailCard || !urlInput || !fileVideoInput || !thumbnailsInput) {
                        console.warn('One or more elements not found:', {
                            sumberSelect,
                            itemYouTube,
                            itemUpload,
                            thumbnailCard,
                            urlInput,
                            fileVideoInput,
                            thumbnailsInput
                        });
                        return;
                    }

                    const selectedSumber = sumberSelect.value;
                    if (selectedSumber === 'YouTube') {
                        itemYouTube.classList.remove('d-none');
                        itemUpload.classList.add('d-none');
                        thumbnailCard.classList.add('d-none');
                        const urlLabel = urlInput.closest('.fv-row')?.querySelector('.form-label');
                        if (urlInput && urlLabel) {
                            urlInput.setAttribute('required', 'required');
                            urlLabel.classList.add('required');
                        }
                        if (fileVideoInput) {
                            fileVideoInput.removeAttribute('required');
                            const fileVideoLabel = fileVideoInput.closest('.fv-row')?.querySelector('.form-label');
                            if (fileVideoLabel) fileVideoLabel.classList.remove('required');
                        }
                        if (thumbnailsInput) {
                            thumbnailsInput.removeAttribute('required');
                            const thumbnailsLabel = thumbnailsInput.closest('.fv-row')?.querySelector('.form-label');
                            if (thumbnailsLabel) thumbnailsLabel.classList.remove('required');
                        }
                    } else if (selectedSumber === 'Upload') {
                        itemYouTube.classList.add('d-none');
                        itemUpload.classList.remove('d-none');
                        thumbnailCard.classList.remove('d-none');
                        if (urlInput) {
                            urlInput.removeAttribute('required');
                            const urlLabel = urlInput.closest('.fv-row')?.querySelector('.form-label');
                            if (urlLabel) urlLabel.classList.remove('required');
                        }
                        if (fileVideoInput && !isEditMode) {
                            fileVideoInput.setAttribute('required', 'required');
                            const fileVideoLabel = fileVideoInput.closest('.fv-row')?.querySelector('.form-label');
                            if (fileVideoLabel) fileVideoLabel.classList.add('required');
                        }
                        if (thumbnailsInput && !isEditMode) {
                            thumbnailsInput.setAttribute('required', 'required');
                            const thumbnailsLabel = thumbnailsInput.closest('.fv-row')?.querySelector('.form-label');
                            if (thumbnailsLabel) thumbnailsLabel.classList.add('required');
                        }
                    }
                }

                if (sumberSelect && sumberSelect.tagName === 'SELECT') {
                    sumberSelect.addEventListener('change', toggleItems);
                }

                // Set initial state only if sumberSelect exists (create mode)
                if (sumberSelect) {
                    toggleItems();
                } else if (isEditMode) {
                    // Handle edit mode initial state
                    const initialSumber = '{{ old('sumber', isset($data) ? $data->sumber : '') }}';
                    if (initialSumber === 'YouTube' && itemYouTube && urlInput) {
                        itemYouTube.classList.remove('d-none');
                        itemUpload.classList.add('d-none');
                        thumbnailCard.classList.add('d-none');
                        const urlLabel = urlInput.closest('.fv-row')?.querySelector('.form-label');
                        if (urlLabel) urlLabel.classList.add('required');
                        urlInput.setAttribute('required', 'required');
                    } else if (initialSumber === 'Upload' && itemUpload && fileVideoInput && thumbnailsInput) {
                        itemYouTube.classList.add('d-none');
                        itemUpload.classList.remove('d-none');
                        thumbnailCard.classList.remove('d-none');
                        if (!isEditMode) {
                            const fileVideoLabel = fileVideoInput.closest('.fv-row')?.querySelector('.form-label');
                            if (fileVideoLabel) fileVideoLabel.classList.add('required');
                            fileVideoInput.setAttribute('required', 'required');
                            const thumbnailsLabel = thumbnailsInput.closest('.fv-row')?.querySelector('.form-label');
                            if (thumbnailsLabel) thumbnailsLabel.classList.add('required');
                            thumbnailsInput.setAttribute('required', 'required');
                        }
                    }
                }

                @if (old('sumber'))
                    if (sumberSelect) toggleItems();
                @endif
            }

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
                                var content = {!! json_encode(\Helper::updateImageUrls($data->post)) !!};
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
                    verify_html: false,
                    valid_elements: 'p,br,strong,em,h1,h2,h3,h4,h5,h6,ul,ol,li,a[href|target|title],img[src|alt|width|height|style|data-*],table,tr,td,th,tbody,thead,tfoot,blockquote,div,span[style],sub,sup,strike,u,code,pre',
                    extended_valid_elements: 'img[*]',
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
                    font_family_formats: "Arial=arial,helvetica,sans-serif; Courier New=courier new,courier,monospace; Georgia=georgia,palatino,serif; Helvetica=helvetica,arial,sans-serif; Impact=impact,sans-serif; Tahoma=tahoma,arial,helvetica,sans-serif; Times New Roman=times new roman,times,serif; Verdana=verdana,arial,helvetica,sans-serif;",
                    font_size_formats: "8pt 9pt 10pt 11pt 12pt 14pt 16pt 18pt 20pt 24pt 28pt 32pt 36pt 48pt"
                });
            }

            return {
                init: function() {
                    form = document.querySelector('#kt_video_form');
                    submitButton = document.querySelector('#kt_video_submit');
                    cancelButton = document.querySelector('#kt_video_cancel');

                    if (!form) {
                        return;
                    }

                    handleForm();
                    handleSlugPreview();
                    handleSumberToggle();
                    handleTinyMCE();
                }
            };
        }();

        document.addEventListener('DOMContentLoaded', function() {
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
            KTAppVideoSave.init();
        });

        KTUtil.onDOMContentLoaded(function() {
            var imageInputs = document.querySelectorAll('[data-kt-image-input]');
            imageInputs.forEach(function(element) {
                new KTImageInput(element);
            });
        });
    </script>
@endpush
{{-- SCRIPTS::END --}}
