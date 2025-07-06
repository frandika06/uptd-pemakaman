@extends('layouts.admin')

{{-- SEO::BEGIN --}}
@section('title', $title)
@section('description', '{{ $title }}')
{{-- SEO::END --}}

{{-- TOOLBAR::BEGIN --}}
@section('toolbar')
    <div class="d-flex flex-stack flex-row-fluid">
        {{-- begin::Toolbar container --}}
        <div class="d-flex flex-column flex-row-fluid">
            {{-- begin::Page title --}}
            <div class="page-title d-flex align-items-center me-3">
                <h1 class="page-heading d-flex flex-column justify-content-center text-dark fw-bold fs-lg-2x gap-2">
                    <span>{{ $title }}</span>
                </h1>
            </div>
            {{-- end::Page title --}}
            {{-- begin::Breadcrumb --}}
            <ul class="breadcrumb breadcrumb-separatorless fw-semibold mb-3 fs-7">
                <li class="breadcrumb-item text-gray-700 fw-bold lh-1">
                    <a href="{{ route('auth.home') }}" class="text-gray-700 text-hover-primary">
                        <i class="ki-outline ki-home fs-6"></i>
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                <li class="breadcrumb-item text-gray-700">Pengaturan</li>
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                <li class="breadcrumb-item text-gray-700">Profile</li>
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                <li class="breadcrumb-item text-gray-700">Edit</li>
            </ul>
            {{-- end::Breadcrumb --}}
        </div>
        {{-- end::Toolbar container --}}
    </div>
@endsection
{{-- TOOLBAR::END --}}

{{-- CONTENT::BEGIN --}}
@section('content')
    <form id="kt_profile_form" class="form d-flex flex-column flex-lg-row" action="{{ route('prt.apps.profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- begin::Aside column --}}
        <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
            {{-- begin::Photo settings --}}
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Foto Profil</h2>
                    </div>
                </div>
                <div class="card-body text-center pt-0">
                    <div class="image-input image-input-outline" data-kt-image-input="true">
                        <div class="image-input-wrapper w-125px h-125px"
                            style="background-image: url({{ isset($data->foto) && Storage::disk('public')->exists($data->foto) ? asset('storage/' . $data->foto) : asset('be/media/avatars/blank.png') }})">
                        </div>
                        <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip"
                            title="Ubah Foto">
                            <i class="ki-outline ki-pencil fs-7"></i>
                            <input type="file" name="foto" accept=".png, .jpg, .jpeg" />
                            <input type="hidden" name="avatar_remove" />
                        </label>
                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip"
                            title="Hapus Foto">
                            <i class="ki-outline ki-trash fs-7"></i>
                        </span>
                    </div>
                    <div class="text-muted fs-7 mt-3">Type: .png, .jpg, .jpeg | Max: 2 MB | Gunakan layout square.</div>
                    @error('foto')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            {{-- end::Photo settings --}}

            {{-- begin::Info settings --}}
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Informasi</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="d-flex flex-column gap-5">
                        <div class="m-0 p-0">
                            <span class="fw-bold text-gray-600">Dibuat:</span><br />
                            <span class="text-gray-800 fw-bold">
                                {{ $data->created_at->format('d M Y H:i') }}
                            </span>
                        </div>
                        <div class="m-0 p-0">
                            <span class="fw-bold text-gray-600">Diperbarui:</span><br />
                            <span class="text-gray-800 fw-bold">{{ $data->updated_at->format('d M Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            {{-- end::Info settings --}}
        </div>
        {{-- end::Aside column --}}

        {{-- begin::Main column --}}
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            {{-- begin::General options --}}
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Detail Profile</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-10 fv-row">
                                <label class="required form-label">NIP</label>
                                <input type="text" name="nip" class="form-control mb-2 @error('nip') is-invalid @enderror" placeholder="Masukkan NIP atau tanda (-) jika Non ASN"
                                    value="{{ old('nip', $data->nip) }}" autocomplete="off" maxlength="20" required />
                                <div class="text-muted fs-7">Masukkan tanda (-) jika Non ASN.</div>
                                @error('nip')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-10 fv-row">
                                <label class="required form-label">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" class="form-control mb-2 @error('nama_lengkap') is-invalid @enderror" placeholder="Masukkan nama lengkap"
                                    value="{{ old('nama_lengkap', $data->nama_lengkap) }}" autocomplete="off" maxlength="100" required />
                                @error('nama_lengkap')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-10 fv-row">
                                <label class="required form-label">Jenis Kelamin</label>
                                <select class="form-select mb-2 @error('jenis_kelamin') is-invalid @enderror" data-control="select2" data-placeholder="Pilih Jenis Kelamin"
                                    name="jenis_kelamin" required>
                                    <option></option>
                                    <option value="L" {{ old('jenis_kelamin', $data->jenis_kelamin) == 'L' ? 'selected' : '' }}>
                                        Laki-laki</option>
                                    <option value="P" {{ old('jenis_kelamin', $data->jenis_kelamin) == 'P' ? 'selected' : '' }}>
                                        Perempuan</option>
                                </select>
                                @error('jenis_kelamin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-10 fv-row">
                                <label class="required form-label">Kontak</label>
                                <input type="text" name="kontak" class="form-control mb-2 @error('kontak') is-invalid @enderror" placeholder="Masukkan nomor kontak"
                                    value="{{ old('kontak', $data->kontak) }}" autocomplete="off" maxlength="15" required />
                                @error('kontak')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-10 fv-row">
                                <label class="required form-label">Email</label>
                                <input type="email" name="email" class="form-control mb-2 @error('email') is-invalid @enderror" placeholder="Masukkan email"
                                    value="{{ old('email', $data->email) }}" autocomplete="off" maxlength="100" required />
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-10 fv-row">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control mb-2 @error('password') is-invalid @enderror"
                                    placeholder="Masukkan password baru (kosongkan jika tidak ingin mengubah)" autocomplete="off" maxlength="100" />
                                <div class="text-muted fs-7">Password minimal 8 karakter, harus mengandung huruf besar, huruf kecil, angka, dan simbol.</div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mb-10 fv-row">
                        <label class="required form-label">Jabatan</label>
                        <input type="text" name="jabatan" class="form-control mb-2 @error('jabatan') is-invalid @enderror" placeholder="Masukkan jabatan"
                            value="{{ old('jabatan', $data->jabatan) }}" autocomplete="off" maxlength="100" required />
                        @error('jabatan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            {{-- end::General options --}}

            {{-- begin::Actions --}}
            <div class="d-flex justify-content-end">
                <a href="{{ route('setup.apps.index') }}" id="kt_profile_cancel" class="btn btn-light me-5">
                    <i class="ki-outline ki-arrow-left fs-2"></i>Batal
                </a>
                <button type="submit" id="kt_profile_submit" class="btn btn-primary">
                    <span class="indicator-label">
                        <i class="fa-solid fa-save me-2"></i>{{ $submit }}
                    </span>
                    <span class="indicator-progress">
                        Please wait...
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

{{-- STYLES::BEGIN --}}
@push('styles')
    <style>
        .image-input-wrapper {
            background-size: cover;
            background-position: center;
        }
    </style>
@endpush
{{-- STYLES::END --}}

{{-- SCRIPTS::BEGIN --}}
@push('scripts')
    <script>
        "use strict";

        // Class definition
        var KTAppProfileSave = function() {
            // Elements
            var form;
            var submitButton;
            var cancelButton;

            // Handle form
            var handleForm = function(e) {
                submitButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Simple client-side validation
                    var nip = form.querySelector('input[name="nip"]').value.trim();
                    var nama = form.querySelector('input[name="nama_lengkap"]').value.trim();
                    var jenisKelamin = form.querySelector('select[name="jenis_kelamin"]').value;
                    var kontak = form.querySelector('input[name="kontak"]').value.trim();
                    var email = form.querySelector('input[name="email"]').value.trim();
                    var jabatan = form.querySelector('input[name="jabatan"]').value.trim();
                    var password = form.querySelector('input[name="password"]').value.trim();

                    var isValid = true;
                    var errorMessage = '';

                    // Validate NIP
                    if (!nip) {
                        isValid = false;
                        errorMessage = 'NIP wajib diisi';
                    } else if (nip.length > 20) {
                        isValid = false;
                        errorMessage = 'NIP maksimal 20 karakter';
                    }

                    // Validate Nama Lengkap
                    if (!nama) {
                        isValid = false;
                        errorMessage = 'Nama lengkap wajib diisi';
                    } else if (nama.length > 100) {
                        isValid = false;
                        errorMessage = 'Nama lengkap maksimal 100 karakter';
                    }

                    // Validate Jenis Kelamin
                    if (!jenisKelamin) {
                        isValid = false;
                        errorMessage = 'Jenis kelamin wajib dipilih';
                    }

                    // Validate Kontak
                    if (!kontak) {
                        isValid = false;
                        errorMessage = 'Kontak wajib diisi';
                    } else if (!/^\d+$/.test(kontak)) {
                        isValid = false;
                        errorMessage = 'Kontak harus berupa angka';
                    } else if (kontak.length > 15) {
                        isValid = false;
                        errorMessage = 'Kontak maksimal 15 karakter';
                    }

                    // Validate Email
                    if (!email) {
                        isValid = false;
                        errorMessage = 'Email wajib diisi';
                    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                        isValid = false;
                        errorMessage = 'Email tidak valid';
                    } else if (email.length > 100) {
                        isValid = false;
                        errorMessage = 'Email maksimal 100 karakter';
                    }

                    // Validate Jabatan
                    if (!jabatan) {
                        isValid = false;
                        errorMessage = 'Jabatan wajib diisi';
                    } else if (jabatan.length > 100) {
                        isValid = false;
                        errorMessage = 'Jabatan maksimal 100 karakter';
                    }

                    // Validate Password (only if filled)
                    if (password) {
                        if (password.length < 8) {
                            isValid = false;
                            errorMessage = 'Password minimal 8 karakter';
                        } else if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/.test(password)) {
                            isValid = false;
                            errorMessage = 'Password harus mengandung huruf besar, huruf kecil, angka, dan simbol';
                        }
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
            };

            // Public methods
            return {
                init: function() {
                    form = document.querySelector('#kt_profile_form');
                    submitButton = document.querySelector('#kt_profile_submit');
                    cancelButton = document.querySelector('#kt_profile_cancel');

                    if (!form) {
                        return;
                    }

                    handleForm();
                }
            };
        }();

        // On document ready
        KTUtil.onDOMContentLoaded(function() {
            KTAppProfileSave.init();
        });
    </script>
@endpush
{{-- SCRIPTS::END --}}
