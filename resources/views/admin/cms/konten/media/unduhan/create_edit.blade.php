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
                    <a href="{{ route('prt.apps.unduhan.index') }}" class="text-gray-700 text-hover-primary">Unduhan</a>
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
    <form id="kt_unduhan_form" class="form d-flex flex-column flex-lg-row" action="{{ isset($data) ? route('prt.apps.unduhan.update', [$uuid_enc]) : route('prt.apps.unduhan.store') }}"
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
                        id="kt_unduhan_status" name="status" required>
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
                    <div class="text-muted fs-7">Pilih status publikasi unduhan.</div>
                </div>
            </div>
            {{-- end::Status settings --}}

            {{-- begin::Thumbnail settings --}}
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Thumbnail</h2>
                    </div>
                </div>
                <div class="card-body text-center pt-0">
                    @if (isset($data) && !empty($data->thumbnails))
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
                                <input type="file" name="thumbnails" id="thumbnails" accept=".png,.jpg,.jpeg" required />
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
                    <div class="text-muted fs-7">Pilih kategori untuk unduhan ini.</div>
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
                            <span class="text-gray-800 fw-bold" id="unduhan_slug_preview">{{ isset($data) ? $data->slug : '-' }}</span>
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
                        <h2>Detail Unduhan</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="mb-10 fv-row">
                        <label class="required form-label">Sumber Unduhan</label>
                        @if (isset($data))
                            <input type="text" class="form-control mb-2" value="{{ old('sumber', $data->sumber) }}" readonly>
                            <input type="hidden" name="sumber" id="sumber" value="{{ old('sumber', $data->sumber) }}">
                        @else
                            <select class="form-select mb-2 @error('sumber') is-invalid @enderror" id="sumber" name="sumber" data-placeholder="Pilih sumber unduhan..."
                                required>
                                <option value="Link" {{ old('sumber') == 'Link' ? 'selected' : '' }}>Link</option>
                                <option value="Upload" {{ old('sumber') == 'Upload' ? 'selected' : '' }}>Upload</option>
                            </select>
                            @error('sumber')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                        <div class="text-muted fs-7">Pilih sumber unduhan (Link atau Upload).</div>
                    </div>

                    <div class="mb-10 fv-row {{ old('sumber', isset($data) ? $data->sumber : '') != 'Link' ? 'd-none' : '' }}" id="item_link">
                        <label class="form-label {{ old('sumber', isset($data) ? $data->sumber : '') == 'Link' ? 'required' : '' }}">URL Link</label>
                        <input type="url" name="url" id="url" class="form-control mb-2 @error('url') is-invalid @enderror" placeholder="Masukkan URL"
                            value="{{ old('url', isset($data) ? $data->url : '') }}" maxlength="300" autocomplete="off"
                            {{ old('sumber', isset($data) ? $data->sumber : '') == 'Link' ? 'required' : '' }} />
                        <div class="text-muted fs-7">Contoh: <strong>https://example.com/dokumen.pdf</strong></div>
                        @error('url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if (isset($data) && $data->sumber == 'Link')
                            <div class="mt-3">
                                <a href="{{ $data->url }}" target="_blank" class="btn btn-sm btn-light-primary">
                                    <i class="ki-outline ki-eye fs-3"></i>Lihat File
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="mb-10 fv-row {{ old('sumber', isset($data) ? $data->sumber : '') != 'Upload' ? 'd-none' : '' }}" id="item_upload">
                        <div class="mb-10 fv-row">
                            <label class="form-label {{ old('sumber', isset($data) ? $data->sumber : '') == 'Upload' && !isset($data) ? 'required' : '' }}">File Unduhan</label>
                            <input type="file" name="file_unduhan" id="file_unduhan" class="form-control mb-2 @error('file_unduhan') is-invalid @enderror"
                                accept=".jpg,.jpeg,.png,.gif,.bmp,.svg,.tiff,.webp,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.odt,.ods,.odp,.rtf,.pdf,.txt,.csv,.xml,.json,.md,.mp3,.wav,.ogg,.m4a,.flac,.aac,.mp4,.mkv,.avi,.mov,.wmv,.flv,.webm,.3gp,.mpeg,.zip,.rar,.tar,.gz,.7z,.bz2,.xz,.iso"
                                {{ old('sumber', isset($data) ? $data->sumber : '') == 'Upload' && !isset($data) ? 'required' : '' }} />
                            <div class="text-muted fs-7">Format: JPG, PNG, PDF, MP4, ZIP, dll (kecuali .html, .php) | Max: 200MB</div>
                            @error('file_unduhan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if (isset($data) && $data->sumber == 'Upload')
                                <div class="mt-3">
                                    <a href="{{ \Helper::urlImg($data->url) }}" target="_blank" class="btn btn-sm btn-light-primary">
                                        <i class="ki-outline ki-eye fs-3"></i>Lihat File
                                    </a>
                                </div>
                            @endif
                        </div>
                        <div class="mb-10 fv-row">
                            <label class="form-label">Password</label>
                            <input type="text" name="password" id="password" class="form-control mb-2 @error('password') is-invalid @enderror" placeholder="Masukkan password"
                                value="{{ old('password', isset($data) ? $data->password : '') }}" maxlength="100" autocomplete="off" />
                            <div class="text-muted fs-7">Berikan password jika file tidak untuk diakses secara publik.</div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-10 fv-row">
                        <label class="required form-label">Judul Unduhan</label>
                        <input type="text" name="judul" id="judul" class="form-control mb-2 @error('judul') is-invalid @enderror" placeholder="Masukkan judul unduhan"
                            value="{{ old('judul', isset($data) ? $data->judul : '') }}" maxlength="300" autocomplete="off" required />
                        <div class="text-muted fs-7">Judul unduhan maksimal 300 karakter.</div>
                        @error('judul')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-10 fv-row">
                        <label class="required form-label">Tanggal Publikasi</label>
                        <input type="datetime-local" name="tanggal" id="tanggal" class="form-control mb-2 @error('tanggal') is-invalid @enderror"
                            value="{{ old('tanggal', isset($data) ? date('Y-m-d\TH:i', strtotime($data->tanggal)) : date('Y-m-d\TH:i')) }}" autocomplete="off" required />
                        <div class="text-muted fs-7">Tanggal dan waktu publikasi unduhan.</div>
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
                            <h2>Preview File</h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        @if ($data->sumber == 'Link')
                            <div class="alert alert-warning alert-dismissible fade show">
                                <i class="ki-outline ki-information-3 fs-2 me-2"></i>
                                <strong>PERHATIAN!</strong> Pratinjau tidak tersedia untuk file eksternal. Klik tombol di bawah untuk mengakses file.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <div class="mt-3">
                                <a href="{{ $data->url }}" target="_blank" class="btn btn-sm btn-light-primary">
                                    <i class="ki-outline ki-eye fs-3"></i>Buka File Eksternal
                                </a>
                            </div>
                        @else
                            @if (in_array($data->tipe, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'tiff', 'webp']))
                                <div class="image-preview">
                                    <img src="{{ \Helper::urlImg($data->url) }}" alt="{{ e($data->judul) }}" class="img-fluid rounded" style="max-width: 100%; height: auto;" />
                                </div>
                            @elseif ($data->tipe == 'pdf')
                                <div class="ratio ratio-16x9">
                                    <iframe src="{{ \Helper::urlImg($data->url) }}#toolbar=0" title="{{ e($data->judul) }}" style="width: 100%; height: 100%;"
                                        loading="lazy"></iframe>
                                </div>
                            @elseif (in_array($data->tipe, ['mp4', 'webm', 'ogg', 'mkv', 'avi', 'mov', 'wmv', 'flv', '3gp', 'mpeg']))
                                <video width="100%" height="auto" controls>
                                    <source src="{{ \Helper::urlImg($data->url) }}" type="video/{{ $data->tipe }}">
                                    Your browser does not support the video tag.
                                </video>
                            @elseif (in_array($data->tipe, ['mp3', 'wav', 'ogg', 'm4a', 'flac', 'aac']))
                                <audio controls style="width: 100%;">
                                    <source src="{{ \Helper::urlImg($data->url) }}" type="audio/{{ $data->tipe }}">
                                    Your browser does not support the audio tag.
                                </audio>
                            @else
                                <div class="alert alert-info alert-dismissible fade show">
                                    <i class="ki-outline ki-information-3 fs-2 me-2"></i>
                                    <strong>INFORMASI!</strong> Pratinjau tidak tersedia untuk tipe file ini. Klik tombol di bawah untuk mengunduh file.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ \Helper::urlImg($data->url) }}" target="_blank" class="btn btn-sm btn-light-primary">
                                        <i class="ki-outline ki-eye fs-3"></i>Unduh File
                                    </a>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @endif

            <div class="d-flex justify-content-end">
                <a href="{{ route('prt.apps.unduhan.index') }}" id="kt_unduhan_cancel" class="btn btn-light me-5">
                    <i class="ki-outline ki-arrow-left fs-2"></i>Batal
                </a>
                <button type="submit" id="kt_unduhan_submit" class="btn btn-primary">
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
    <script>
        "use strict";

        var KTAppUnduhanSave = function() {
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
                    var fileUnduhan = form.querySelector('input[name="file_unduhan"]');
                    var thumbnails = form.querySelector('input[name="thumbnails"]');
                    var password = form.querySelector('input[name="password"]') ? form.querySelector('input[name="password"]').value.trim() : '';

                    var isValid = true;
                    var errorMessage = '';

                    if (!judul) {
                        isValid = false;
                        errorMessage = 'Judul unduhan wajib diisi';
                    } else if (judul.length > 300) {
                        isValid = false;
                        errorMessage = 'Judul unduhan maksimal 300 karakter';
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
                        errorMessage = 'Sumber unduhan wajib dipilih';
                    }

                    if (sumber === 'Upload' && !{{ isset($data) ? 'true' : 'false' }}) {
                        if (!fileUnduhan || !fileUnduhan.files.length) {
                            isValid = false;
                            errorMessage = 'File unduhan wajib diisi';
                        }
                    }

                    if (!thumbnails || !thumbnails.files.length && !{{ isset($data) ? 'true' : 'false' }}) {
                        isValid = false;
                        errorMessage = 'Thumbnail wajib diisi';
                    }

                    if (password && password.length > 100) {
                        isValid = false;
                        errorMessage = 'Password maksimal 100 karakter';
                    }

                    if (isValid) {
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
                const slugPreview = document.querySelector('#unduhan_slug_preview');

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
                const itemLink = document.querySelector('#item_link');
                const itemUpload = document.querySelector('#item_upload');
                const urlInput = document.querySelector('#url');
                const fileUnduhanInput = document.querySelector('#file_unduhan');

                const isEditMode = {{ isset($data) ? 'true' : 'false' }};

                function toggleItems() {
                    if (!itemLink || !itemUpload || !urlInput || !fileUnduhanInput) {
                        console.warn('One or more elements not found:', {
                            sumberSelect,
                            itemLink,
                            itemUpload,
                            urlInput,
                            fileUnduhanInput
                        });
                        return;
                    }

                    const selectedSumber = sumberSelect ? sumberSelect.value : '{{ old('sumber', isset($data) ? $data->sumber : '') }}';
                    if (selectedSumber === 'Link') {
                        itemLink.classList.remove('d-none');
                        itemUpload.classList.add('d-none');
                        const urlLabel = urlInput.closest('.fv-row')?.querySelector('.form-label');
                        if (urlInput && urlLabel) {
                            urlInput.setAttribute('required', 'required');
                            urlLabel.classList.add('required');
                        }
                        if (fileUnduhanInput) {
                            fileUnduhanInput.removeAttribute('required');
                            const fileUnduhanLabel = fileUnduhanInput.closest('.fv-row')?.querySelector('.form-label');
                            if (fileUnduhanLabel) fileUnduhanLabel.classList.remove('required');
                        }
                    } else if (selectedSumber === 'Upload') {
                        itemLink.classList.add('d-none');
                        itemUpload.classList.remove('d-none');
                        if (urlInput) {
                            urlInput.removeAttribute('required');
                            const urlLabel = urlInput.closest('.fv-row')?.querySelector('.form-label');
                            if (urlLabel) urlLabel.classList.remove('required');
                        }
                        if (fileUnduhanInput && !isEditMode) {
                            fileUnduhanInput.setAttribute('required', 'required');
                            const fileUnduhanLabel = fileUnduhanInput.closest('.fv-row')?.querySelector('.form-label');
                            if (fileUnduhanLabel) fileUnduhanLabel.classList.add('required');
                        }
                    }
                }

                if (sumberSelect && sumberSelect.tagName === 'SELECT') {
                    sumberSelect.addEventListener('change', toggleItems);
                }

                toggleItems();
                @if (old('sumber'))
                    if (sumberSelect) toggleItems();
                @endif
            }

            return {
                init: function() {
                    form = document.querySelector('#kt_unduhan_form');
                    submitButton = document.querySelector('#kt_unduhan_submit');
                    cancelButton = document.querySelector('#kt_unduhan_cancel');

                    if (!form) {
                        return;
                    }

                    handleForm();
                    handleSlugPreview();
                    handleSumberToggle();
                }
            };
        }();

        KTUtil.onDOMContentLoaded(function() {
            KTAppUnduhanSave.init();
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
