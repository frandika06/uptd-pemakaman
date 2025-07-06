@push('styles')
    <style>
        .card-header {
            border-bottom: 1px solid #e4e6ef;
        }

        .form-control-solid:focus {
            border-color: #009ef7;
            box-shadow: 0 0 5px rgba(0, 158, 247, 0.3);
        }

        .image-input-wrapper {
            width: 150px;
            height: 100px;
            border-radius: 8px;
            background-size: contain;
            background-position: center;
            background-repeat: no-repeat;
            border: 2px dashed #e4e6ef;
        }

        .image-input-placeholder {
            background: #f5f8fa;
            border-color: #e4e6ef;
        }

        .photo-row {
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .photo-row:hover {
            background: #f1faff;
            transform: translateY(-2px);
        }

        .photo-preview {
            cursor: pointer;
            max-width: 150px;
            max-height: 100px;
            object-fit: contain;
            border-radius: 8px;
            border: 2px solid #e1e5e9;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .photo-preview:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .fslightbox-container {
            background: rgba(0, 0, 0, 0.9) !important;
        }

        .fslightbox-slide {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .fslightbox-source {
            max-width: 90vw !important;
            max-height: 90vh !important;
            object-fit: contain !important;
            transform: none !important;
        }

        .remove-photo-btn {
            transition: all 0.2s ease;
        }

        .remove-photo-btn:hover {
            background-color: #f1416c !important;
            color: #fff !important;
        }

        .add-photo-btn {
            transition: all 0.2s ease;
        }

        .add-photo-btn:hover {
            background-color: #009ef7 !important;
            transform: translateY(-2px);
        }

        .form-label {
            font-weight: 600;
            color: #3f4254;
        }

        .required:after {
            content: '*';
            color: #f1416c;
            margin-left: 4px;
        }

        .form-section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #181c32;
            margin-bottom: 15px;
            border-left: 4px solid #009ef7;
            padding-left: 10px;
        }

        .alert {
            border-radius: 8px;
            padding: 15px;
        }

        .btn-submit {
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 158, 247, 0.3);
        }
    </style>
@endpush

@extends('layouts.admin')

@section('title', $title)

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
                    <a href="{{ route('prt.apps.galeri.index') }}" class="text-gray-700 text-hover-primary">Galeri</a>
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
    <form id="kt_galeri_form" class="form d-flex flex-column flex-lg-row" action="{{ isset($data) ? route('prt.apps.galeri.update', $uuid_enc) : route('prt.apps.galeri.store') }}"
        method="POST" enctype="multipart/form-data">
        @csrf
        @if (isset($data))
            @method('PUT')
        @endif

        <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Thumbnail Galeri</h2>
                    </div>
                </div>
                <div class="card-body text-center pt-0">
                    @if (isset($data) && $data->thumbnails)
                        <div class="image-input image-input-outline image-input-placeholder" data-kt-image-input="true">
                            <div class="image-input-wrapper w-150px h-100px" style="background-image: url('{{ Helper::thumbnail($data->thumbnails) }}')"></div>
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
                            <a href="{{ Helper::thumbnail($data->thumbnails) }}" data-fslightbox="gallery" class="btn btn-sm btn-light-primary">
                                <i class="ki-outline ki-eye fs-7 me-1"></i>Lihat Gambar
                            </a>
                        </div>
                        @error('thumbnails')
                            <div class="text-danger fs-7 mt-2">{{ $message }}</div>
                        @enderror
                        <div class="text-muted fs-7 mt-3">Format: PNG, JPG, JPEG | Max: 2MB<br>Disarankan: Landscape (1200x628px) atau Square</div>
                    @else
                        <div class="image-input image-input-outline image-input-placeholder" data-kt-image-input="true">
                            <div class="image-input-wrapper w-150px h-100px"></div>
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
                        <div class="text-muted fs-7 mt-3">Format: PNG, JPG, JPEG | Max: 2MB<br>Disarankan: Landscape (1200x628px) atau Square</div>
                    @endif
                </div>
            </div>

            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Status</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <select class="form-select mb-2 @error('status') is-invalid @enderror" data-control="select2" data-placeholder="Pilih Status" name="status" required>
                        <option></option>
                        @foreach (['Draft', 'Pending Review', 'Published', 'Scheduled', 'Archived'] as $statusOption)
                            @if (Helper::validateStatus($auth->role, $statusOption))
                                <option @selected(old('status', isset($data) ? $data->status : '') == $statusOption) value="{{ $statusOption }}">{{ $statusOption }}</option>
                            @endif
                        @endforeach
                    </select>
                    @error('status')
                        <div class="text-danger fs-7 mt-2">{{ $message }}</div>
                    @enderror
                    <div class="text-muted fs-7">Atur status publikasi galeri.</div>
                </div>
            </div>

            @if (isset($data))
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
                                <span class="text-gray-800 fw-bold" id="galeri_slug_preview">{{ $data->slug ?: '-' }}</span>
                            </div>
                            <div class="m-0 p-0">
                                <span class="fw-bold text-gray-600">Jumlah Foto:</span><br />
                                <span class="text-gray-800 fw-bold">
                                    <span id="photo_list_counter">{{ count($data->RelGaleriList) }}</span>
                                    <span class="ms-2">foto</span>
                                </span>
                            </div>
                            <div class="m-0 p-0">
                                <span class="fw-bold text-gray-600">Dibuat:</span><br />
                                <span class="text-gray-800 fw-bold">{{ $data->created_at->format('d M Y H:i') }}</span>
                            </div>
                            <div class="m-0 p-0">
                                <span class="fw-bold text-gray-600">Diperbarui:</span><br />
                                <span class="text-gray-800 fw-bold">{{ $data->updated_at->format('d M Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Detail Galeri</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="mb-10 fv-row">
                        <label class="required form-label">Judul</label>
                        <input type="text" name="judul" id="judul" class="form-control mb-2 @error('judul') is-invalid @enderror" placeholder="Masukkan judul galeri"
                            value="{{ old('judul', isset($data) ? $data->judul : '') }}" maxlength="100" autocomplete="off" required />
                        <div class="text-muted fs-7">Judul galeri maksimal 100 karakter.</div>
                        @error('judul')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-10 fv-row">
                        <label class="required form-label">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" class="form-control mb-2 @error('deskripsi') is-invalid @enderror" rows="3" placeholder="Masukkan deskripsi galeri untuk SEO"
                            maxlength="160" autocomplete="off" required>{{ old('deskripsi', isset($data) ? $data->deskripsi : '') }}</textarea>
                        <div class="text-muted fs-7">Deskripsi untuk SEO (Search Engine Optimization) maksimal 160 karakter.</div>
                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-10 fv-row">
                        <label class="required form-label">Tanggal Publikasi</label>
                        <input type="datetime-local" name="tanggal" id="tanggal" class="form-control mb-2 @error('tanggal') is-invalid @enderror"
                            value="{{ old('tanggal', isset($data) && $data->tanggal ? $data->tanggal : '') }}" required />
                        <div class="text-muted fs-7">Tentukan kapan galeri ini akan dipublikasikan.</div>
                        @error('tanggal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-10 fv-row">
                        <label class="required form-label">Kategori</label>
                        <select name="kategori" class="form-select mb-2 @error('kategori') is-invalid @enderror" data-control="select2" data-placeholder="Pilih Kategori" required>
                            <option></option>
                            @foreach ($kategoriList as $kategori)
                                <option @selected(old('kategori', isset($data) ? $data->kategori : '') == $kategori->nama) value="{{ $kategori->nama }}">{{ $kategori->nama }}</option>
                            @endforeach
                        </select>
                        <div class="text-muted fs-7">Pilih kategori galeri.</div>
                        @error('kategori')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Daftar Foto</h2>
                    </div>
                    <div class="card-toolbar">
                        <button type="button" id="addPhoto" class="btn btn-sm btn-light-primary add-photo-btn">
                            <i class="ki-outline ki-plus fs-2"></i>Tambah Foto
                        </button>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div id="photoFields">
                        @if (isset($data) && $data->RelGaleriList->isNotEmpty())
                            @foreach ($data->RelGaleriList as $index => $foto)
                                <div class="photo-entry mb-7" data-index="{{ $index }}">
                                    <div class="card border">
                                        <div class="card-header border-0 pt-6">
                                            <div class="card-title">
                                                <h4 class="fw-bold text-gray-800">
                                                    <i class="ki-outline ki-picture text-primary fs-2 me-2"></i>
                                                    Foto #<span class="photo-number">{{ $index + 1 }}</span>
                                                </h4>
                                            </div>
                                            <div class="card-toolbar">
                                                <button type="button" class="btn btn-icon btn-sm btn-light-danger remove-photo-btn" data-delete="{{ Helper::encode($foto->uuid) }}"
                                                    data-bs-toggle="tooltip" title="Hapus Foto">
                                                    <i class="ki-outline ki-trash fs-5"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div class="row">
                                                <div class="col-lg-8 col-md-12">
                                                    <div class="mb-7 fv-row">
                                                        <label class="form-label fw-semibold text-gray-700">Judul Foto</label>
                                                        <input type="text" name="judul_foto_list[]"
                                                            class="form-control form-control-solid @error('judul_foto_list.' . $index) is-invalid @enderror"
                                                            value="{{ old('judul_foto_list.' . $index, $foto->judul) }}" placeholder="Masukkan judul foto" maxlength="100"
                                                            autocomplete="off" />
                                                        <input type="hidden" name="uuid_foto[]" value="{{ $foto->uuid }}" />
                                                        <div class="text-muted fs-7 mt-1">Judul foto maksimal 100 karakter.</div>
                                                        @error('judul_foto_list.' . $index)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-md-12">
                                                    <div class="mb-7 fv-row">
                                                        <label class="form-label fw-semibold text-gray-700">Pratinjau</label><br />
                                                        <div class="image-input image-input-outline image-input-placeholder">
                                                            <a href="{{ asset('storage/' . $foto->url) }}" data-fslightbox="gallery">
                                                                <div class="image-input-wrapper w-150px h-100px photo-preview"
                                                                    style="background-image: url('{{ Helper::thumbnail($foto->url) }}')"></div>
                                                            </a>
                                                        </div>
                                                        <div class="mt-3">
                                                            <a href="{{ asset('storage/' . $foto->url) }}" data-fslightbox="gallery" class="btn btn-sm btn-light-primary">
                                                                <i class="ki-outline ki-eye fs-7 me-1"></i>Lihat Gambar
                                                            </a>
                                                        </div>
                                                        <div class="text-muted fs-7 mt-3">Klik gambar untuk melihat dalam lightbox. Disarankan: Landscape (1200x628px) atau Square
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div id="no-photo-message" class="text-center py-10">
                                <div class="mb-7">
                                    <i class="ki-outline ki-picture text-muted" style="font-size: 3rem;"></i>
                                </div>
                                <h3 class="text-gray-600 fw-semibold mb-2">Belum Ada Foto</h3>
                                <p class="text-muted mb-0">Klik tombol "Tambah Foto" untuk menambahkan foto pertama</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('prt.apps.galeri.index') }}" id="kt_galeri_cancel" class="btn btn-light me-5">
                    <i class="ki-outline ki-arrow-left fs-2"></i>Batal
                </a>
                <button type="submit" id="kt_galeri_submit" form="kt_galeri_form" class="btn btn-primary btn-submit">
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
    </form>

    <template id="photo-template">
        <div class="photo-entry mb-7" data-index="__INDEX__">
            <div class="card border">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <h4 class="fw-bold text-gray-800">
                            <i class="ki-outline ki-picture text-primary fs-2 me-2"></i>
                            Foto #<span class="photo-number">__NUMBER__</span>
                        </h4>
                    </div>
                    <div class="card-toolbar">
                        <button type="button" class="btn btn-icon btn-sm btn-light-danger remove-photo-btn" data-bs-toggle="tooltip" title="Hapus Foto">
                            <i class="ki-outline ki-trash fs-5"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="row">
                        <div class="col-lg-8 col-md-12">
                            <div class="mb-7 fv-row">
                                <label class="required form-label fw-semibold text-gray-700">Judul Foto</label>
                                <input type="text" name="judul_foto[]" class="form-control form-control-solid @error('judul_foto') is-invalid @enderror"
                                    placeholder="Masukkan judul foto" maxlength="100" autocomplete="off" required />
                                <div class="text-muted fs-7 mt-1">Judul foto maksimal 100 karakter.</div>
                                @error('judul_foto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-12">
                            <div class="mb-7 fv-row">
                                <label class="required form-label fw-semibold text-gray-700">Foto</label><br />
                                <div class="image-input image-input-outline image-input-placeholder" data-kt-image-input="true">
                                    <div class="image-input-wrapper w-150px h-100px photo-preview"></div>
                                    <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change"
                                        data-bs-toggle="tooltip" title="Pilih Foto">
                                        <i class="ki-outline ki-picture fs-7"></i>
                                        <input type="file" name="file_foto[]" accept=".png,.jpg,.jpeg" required />
                                    </label>
                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel"
                                        data-bs-toggle="tooltip" title="Batalkan Foto">
                                        <i class="ki-outline ki-cross fs-2"></i>
                                    </span>
                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove"
                                        data-bs-toggle="tooltip" title="Hapus Foto">
                                        <i class="ki-outline ki-cross fs-2"></i>
                                    </span>
                                </div>
                                <div class="text-muted fs-7 mt-3">Format: PNG, JPG, JPEG | Max: 2MB<br>Disarankan: Landscape (1200x628px) atau Square</div>
                                @error('file_foto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
@endsection

@push('scripts')
    <script src="{{ asset('be/plugins/custom/fslightbox/fslightbox.bundle.js') }}"></script>
    <script>
        "use strict";

        var KTAppGaleri = function() {
            var form, submitButton, cancelButton, photoFields, photoCounter;

            var updatePhotoCounter = function() {
                photoCounter = document.querySelectorAll('.photo-entry').length;
                var counterElement = document.getElementById('photo_list_counter');
                if (counterElement) {
                    counterElement.innerText = photoCounter;
                }
                var noPhotoMessage = document.getElementById('no-photo-message');
                if (noPhotoMessage) {
                    noPhotoMessage.style.display = photoCounter === 0 ? 'block' : 'none';
                }
            };

            var reindexPhotoEntries = function() {
                var entries = document.querySelectorAll('.photo-entry');
                entries.forEach(function(entry, index) {
                    entry.setAttribute('data-index', index);
                    var numberElement = entry.querySelector('.photo-number');
                    if (numberElement) {
                        numberElement.innerText = index + 1;
                    }
                });
                updatePhotoCounter();
            };

            var initSelect2 = function(selectElement) {
                if (selectElement && !$(selectElement).data('select2')) {
                    $(selectElement).select2({
                        minimumResultsForSearch: Infinity,
                        dropdownParent: $('.card-body')
                    }).on('select2:unselect', function() {
                        $(this).trigger('change');
                    });
                }
            };

            var addPhotoEntry = function() {
                var template = document.getElementById('photo-template');
                if (!template) {
                    console.error('Photo template not found');
                    return;
                }
                var clone = template.content.cloneNode(true);
                var index = photoCounter;
                var photoEntry = clone.querySelector('.photo-entry');
                if (photoEntry) {
                    photoEntry.setAttribute('data-index', index);
                    var numberElement = photoEntry.querySelector('.photo-number');
                    if (numberElement) {
                        numberElement.innerText = index + 1;
                    }
                    photoFields.appendChild(clone);
                    photoCounter++;

                    var newSelect = photoEntry.querySelector('select');
                    initSelect2(newSelect);

                    var fileInput = photoEntry.querySelector('input[type="file"]');
                    if (fileInput) {
                        fileInput.addEventListener('change', handleImagePreview);
                    }

                    KTImageInput.createInstances();
                    refreshFsLightbox();
                    updatePhotoCounter();
                }
            };

            var handleImagePreview = function(event) {
                var input = event.target;
                var preview = input.closest('.image-input').querySelector('.photo-preview');
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var img = new Image();
                        img.src = e.target.result;
                        img.onload = function() {
                            var isLandscape = img.width > img.height;
                            preview.style.backgroundImage = 'url(' + e.target.result + ')';
                            preview.style.backgroundSize = isLandscape ? 'contain' : 'cover';
                            preview.style.backgroundPosition = 'center';
                            preview.classList.add(isLandscape ? 'landscape' : 'portrait');
                        };
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            };

            var removePhotoEntry = function(element, uuid) {
                if (uuid) {
                    Swal.fire({
                        text: "Apakah Anda yakin ingin menghapus foto ini?",
                        icon: "warning",
                        showCancelButton: true,
                        buttonsStyling: false,
                        confirmButtonText: "Ya, hapus!",
                        cancelButtonText: "Tidak",
                        customClass: {
                            confirmButton: "btn btn-primary",
                            cancelButton: "btn btn-active-light"
                        }
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('prt.apps.galeri.destroy') }}",
                                type: 'POST',
                                data: {
                                    uuid: uuid,
                                    tags: 'list_galeri',
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function(res) {
                                    element.closest('.photo-entry').remove();
                                    reindexPhotoEntries();
                                    Swal.fire({
                                        title: "Berhasil",
                                        text: res.message,
                                        icon: "success",
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                },
                                error: function(xhr) {
                                    Swal.fire({
                                        title: "Error",
                                        text: xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan',
                                        icon: "error"
                                    });
                                }
                            });
                        }
                    });
                } else {
                    element.closest('.photo-entry').remove();
                    reindexPhotoEntries();
                }
            };

            var handleSlugPreview = function() {
                var judulInput = document.querySelector('input[name="judul"]');
                var slugPreview = document.getElementById('galeri_slug_preview');
                if (judulInput && slugPreview) {
                    judulInput.addEventListener('input', function() {
                        var slug = createSlug(this.value);
                        slugPreview.textContent = slug || '-';
                    });
                    var initialJudul = judulInput.value;
                    if (initialJudul) {
                        slugPreview.textContent = createSlug(initialJudul);
                    }
                }
            };

            var createSlug = function(str) {
                return str
                    .toLowerCase()
                    .trim()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/[\s_-]+/g, '-')
                    .replace(/^-+|-+$/g, '');
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
                form = document.getElementById('kt_galeri_form');
                submitButton = document.getElementById('kt_galeri_submit');
                cancelButton = document.getElementById('kt_galeri_cancel');
                photoFields = document.getElementById('photoFields');
                if (!photoFields) {
                    console.error('Photo fields container not found');
                    return;
                }
                photoCounter = document.querySelectorAll('.photo-entry').length;

                document.querySelectorAll('select[data-control="select2"]').forEach(function(select) {
                    initSelect2(select);
                });

                document.querySelectorAll('input[type="file"]').forEach(function(input) {
                    input.addEventListener('change', handleImagePreview);
                });

                KTImageInput.createInstances();

                var addPhotoButton = document.getElementById('addPhoto');
                if (addPhotoButton) {
                    addPhotoButton.addEventListener('click', addPhotoEntry);
                }

                if (photoFields) {
                    photoFields.addEventListener('click', function(e) {
                        if (e.target.closest('.remove-photo-btn')) {
                            var button = e.target.closest('.remove-photo-btn');
                            var uuid = button.getAttribute('data-delete');
                            removePhotoEntry(button, uuid);
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

                updatePhotoCounter();
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
            KTUtil.onDOMContentLoaded(function() {
                if (typeof KTUtil !== 'undefined') {
                    KTAppGaleri.init();
                } else {
                    console.error('KTUtil is not defined');
                }
            });
            refreshFsLightbox();
        });
    </script>
@endpush
