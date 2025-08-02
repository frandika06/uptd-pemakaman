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
                <li class="breadcrumb-item text-gray-700">Master Data</li>
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                <li class="breadcrumb-item text-gray-700">
                    <a href="{{ route('prt.apps.mst.portal_setup.index') }}" class="text-gray-700 text-hover-primary">Portal Setup</a>
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
    <form id="kt_portal_setup_form" class="form d-flex flex-column flex-lg-row"
        action="{{ isset($data) ? route('prt.apps.mst.portal_setup.update', ['uuid' => \App\Helpers\Helper::encode($data->uuid)]) : route('prt.apps.mst.portal_setup.store') }}"
        method="POST">
        @csrf
        @if (isset($data))
            @method('PUT')
        @endif

        <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Status</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <select class="form-select mb-2" data-control="select2" data-hide-search="true" data-placeholder="Pilih Status" id="kt_portal_setup_status" name="status">
                        <option value=""></option>
                        <option value="1" {{ old('status', isset($data) ? $data->status : '1') == '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('status', isset($data) ? $data->status : '') == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                    <div class="text-muted fs-7">Pilih status pengaturan portal. Status aktif akan langsung diterapkan di portal.</div>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

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
                                {{ isset($data) ? $data->created_at->format('d M Y H:i') : 'Akan diatur otomatis' }}
                            </span>
                        </div>
                        @if (isset($data))
                            <div class="m-0 p-0">
                                <span class="fw-bold text-gray-600">Diperbarui:</span><br />
                                <span class="text-gray-800 fw-bold">{{ $data->updated_at->format('d M Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Detail Pengaturan</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="mb-10 fv-row">
                        <label class="required form-label">Nama Pengaturan</label>
                        <input type="text" name="nama_pengaturan" class="form-control mb-2 @error('nama_pengaturan') is-invalid @enderror" placeholder="Masukkan nama pengaturan"
                            value="{{ old('nama_pengaturan', isset($data) ? $data->nama_pengaturan : '') }}" maxlength="100" />
                        <div class="text-muted fs-7">Nama pengaturan harus unik untuk setiap kategori dan site yang sama.</div>
                        @error('nama_pengaturan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-10 fv-row">
                        <label class="required form-label">Value Pengaturan</label>
                        <textarea name="value_pengaturan" class="form-control mb-2 @error('value_pengaturan') is-invalid @enderror" placeholder="Masukkan value pengaturan">{{ old('value_pengaturan', isset($data) ? $data->value_pengaturan : '') }}</textarea>
                        <div class="text-muted fs-7">Masukkan nilai pengaturan yang akan digunakan di portal.</div>
                        @error('value_pengaturan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-10 fv-row">
                        <label class="required form-label">Kategori</label>
                        <select class="form-select mb-2" data-control="select2" data-placeholder="Pilih Kategori" name="kategori" id="kt_portal_setup_kategori">
                            <option value=""></option>
                            @php
                                $kategoriList = ['Header', 'Footer', 'SEO', 'Hero', 'Kontak', 'Organisasi', 'Layanan'];
                            @endphp
                            @foreach ($kategoriList as $kategori)
                                <option value="{{ $kategori }}" {{ old('kategori', isset($data) ? $data->kategori : '') == $kategori ? 'selected' : '' }}>{{ $kategori }}
                                </option>
                            @endforeach
                        </select>
                        <div class="text-muted fs-7">Pilih kategori pengaturan yang sesuai.</div>
                        @error('kategori')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-10 fv-row">
                        <label class="required form-label">Sites</label>
                        <select class="form-select mb-2" data-control="select2" data-placeholder="Pilih Site" name="sites" id="kt_portal_setup_sites">
                            <option value=""></option>
                            @php
                                $sitesList = ['Portal', 'Admin'];
                            @endphp
                            @foreach ($sitesList as $site)
                                <option value="{{ $site }}" {{ old('sites', isset($data) ? $data->sites : '') == $site ? 'selected' : '' }}>{{ $site }}</option>
                            @endforeach
                        </select>
                        <div class="text-muted fs-7">Pilih site tempat pengaturan ini diterapkan.</div>
                        @error('sites')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-10 fv-row">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control mb-2 @error('keterangan') is-invalid @enderror" placeholder="Masukkan keterangan (opsional)">{{ old('keterangan', isset($data) ? $data->keterangan : '') }}</textarea>
                        <div class="text-muted fs-7">Masukkan keterangan tambahan untuk pengaturan ini (opsional, maks. 255 karakter).</div>
                        @error('keterangan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('prt.apps.mst.portal_setup.index') }}" id="kt_portal_setup_cancel" class="btn btn-light me-5">
                    <i class="ki-outline ki-arrow-left fs-2"></i>Batal
                </a>
                <button type="submit" id="kt_portal_setup_submit" class="btn btn-primary">
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
@endsection
{{-- CONTENT::END --}}

{{-- SCRIPTS::BEGIN --}}
@push('scripts')
    <script>
        "use strict";

        var KTAppPortalSetupSave = function() {
            var form;
            var submitButton;
            var cancelButton;

            var handleForm = function() {
                submitButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Ambil nilai field
                    var nama = form.querySelector('input[name="nama_pengaturan"]').value.trim();
                    var value = form.querySelector('textarea[name="value_pengaturan"]').value.trim();
                    var kategori = form.querySelector('select[name="kategori"]').value;
                    var sites = form.querySelector('select[name="sites"]').value;
                    var status = form.querySelector('select[name="status"]').value;

                    var isValid = true;
                    var errorMessage = '';

                    // Validasi field
                    if (!nama) {
                        isValid = false;
                        errorMessage = 'Nama pengaturan wajib diisi';
                    } else if (nama.length > 100) {
                        isValid = false;
                        errorMessage = 'Nama pengaturan maksimal 100 karakter';
                    }

                    if (!value) {
                        isValid = false;
                        errorMessage = 'Value pengaturan wajib diisi';
                    }

                    if (!kategori) {
                        isValid = false;
                        errorMessage = 'Kategori wajib dipilih';
                    }

                    if (!sites) {
                        isValid = false;
                        errorMessage = 'Site wajib dipilih';
                    }

                    if (status === '') {
                        isValid = false;
                        errorMessage = 'Status wajib dipilih';
                    }

                    if (isValid) {
                        submitButton.setAttribute('data-kt-indicator', 'on');
                        submitButton.disabled = true;
                        try {
                            // Debug: log data yang akan dikirim
                            console.log('Form data:', {
                                nama_pengaturan: nama,
                                value_pengaturan: value,
                                kategori: kategori,
                                sites: sites,
                                status: status,
                                _token: form.querySelector('input[name="_token"]').value,
                                _method: form.querySelector('input[name="_method"]')?.value
                            });
                            form.submit();
                        } catch (error) {
                            console.error('Form submission error:', error);
                            submitButton.removeAttribute('data-kt-indicator');
                            submitButton.disabled = false;
                            Swal.fire({
                                text: 'Terjadi kesalahan saat mengirim form: ' + error.message,
                                icon: 'error',
                                buttonsStyling: false,
                                confirmButtonText: 'Ok',
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                }
                            });
                        }
                    } else {
                        Swal.fire({
                            text: errorMessage,
                            icon: 'error',
                            buttonsStyling: false,
                            confirmButtonText: 'Ok, saya mengerti!',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            }
                        });
                    }
                });

                cancelButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    Swal.fire({
                        text: 'Apakah Anda yakin ingin membatalkan?',
                        icon: 'warning',
                        showCancelButton: true,
                        buttonsStyling: false,
                        confirmButtonText: 'Ya, batalkan!',
                        cancelButtonText: 'Tidak',
                        customClass: {
                            confirmButton: 'btn btn-primary',
                            cancelButton: 'btn btn-active-light'
                        }
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            window.location = cancelButton.getAttribute('href');
                        }
                    });
                });
            }

            return {
                init: function() {
                    form = document.querySelector('#kt_portal_setup_form');
                    submitButton = document.querySelector('#kt_portal_setup_submit');
                    cancelButton = document.querySelector('#kt_portal_setup_cancel');

                    if (!form) {
                        console.error('Form not found');
                        Swal.fire({
                            text: 'Form tidak ditemukan. Silakan periksa halaman.',
                            icon: 'error',
                            buttonsStyling: false,
                            confirmButtonText: 'Ok',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            }
                        });
                        return;
                    }

                    handleForm();
                }
            };
        }();

        KTUtil.onDOMContentLoaded(function() {
            KTAppPortalSetupSave.init();
        });
    </script>
@endpush
{{-- SCRIPTS::END --}}
