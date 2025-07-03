@extends('layouts.admin')

{{-- SEO::BEGIN --}}
@section('title', $title)
@section('description', 'Kotak Pesan | ' . env('APP_NAME'))
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
                    <a href="{{ route('prt.apps.index') }}" class="text-gray-700 text-hover-primary">
                        <i class="ki-outline ki-home fs-6"></i>
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                <li class="breadcrumb-item text-gray-700">Helpdesk</li>
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                <li class="breadcrumb-item text-gray-700">
                    <a href="{{ route('prt.apps.kotak.pesan.index') }}" class="text-gray-700 text-hover-primary">Kotak Pesan</a>
                </li>
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                <li class="breadcrumb-item text-gray-700">{{ $data->status == 'Pending' ? 'Balas' : 'Lihat' }}</li>
            </ul>
            {{-- end::Breadcrumb --}}
        </div>
        {{-- end::Toolbar container --}}
    </div>
@endsection
{{-- TOOLBAR::END --}}

{{-- CONTENT::BEGIN --}}
@section('content')
    <form id="kt_pesan_form" class="form d-flex flex-column flex-lg-row" action="{{ route('prt.apps.kotak.pesan.update', [$uuid_enc]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- begin::Aside column --}}
        <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
            {{-- begin::Status settings --}}
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Status</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <span class="badge badge-light-{{ $data->status == 'Pending' ? 'warning' : 'success' }} fw-bold fs-7 px-3 py-2">{{ $data->status }}</span>
                    <div class="text-muted fs-7 mt-2">Status pesan saat ini.</div>
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
                            <span class="fw-bold text-gray-600">Dibuat:</span><br />
                            <span class="text-gray-800 fw-bold">{{ Helper::TglJam($data->created_at) }}</span>
                        </div>
                        @if ($data->status == 'Responded')
                            <div class="m-0 p-0">
                                <span class="fw-bold text-gray-600">Diperbarui:</span><br />
                                <span class="text-gray-800 fw-bold">{{ Helper::TglJam($data->updated_at) }}</span>
                            </div>
                            @if ($data->Replyed)
                                <div class="m-0 p-0">
                                    <span class="fw-bold text-gray-600">Dibalas Oleh:</span><br />
                                    <span class="text-gray-800 fw-bold">{{ $data->Replyed->nama_lengkap }}</span>
                                </div>
                            @endif
                        @endif
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
                        <h2>Detail Pesan</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="mb-10 fv-row">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" value="{{ $data->nama_lengkap }}" readonly disabled>
                    </div>
                    <div class="mb-10 fv-row">
                        <label class="form-label">No. Telp</label>
                        <input type="text" class="form-control" value="{{ $data->no_telp }}" readonly disabled>
                    </div>
                    <div class="mb-10 fv-row">
                        <label class="form-label">Email</label>
                        <input type="text" class="form-control" value="{{ $data->email }}" readonly disabled>
                    </div>
                    <div class="mb-10 fv-row">
                        <label class="form-label">Instansi</label>
                        <input type="text" class="form-control" value="{{ $data->instansi }}" readonly disabled>
                    </div>
                    <div class="mb-10 fv-row">
                        <label class="form-label">Subjek</label>
                        <input type="text" class="form-control" value="{{ $data->subjek }}" readonly disabled>
                    </div>
                    <div class="mb-10 fv-row">
                        <label class="form-label">Pesan</label>
                        <textarea class="form-control" rows="5" readonly disabled>{{ str_replace('<br />', '', $data->pesan) }}</textarea>
                    </div>
                </div>
            </div>
            {{-- end::General options --}}

            {{-- begin::Reply editor --}}
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>{{ $data->status == 'Pending' ? 'Balasan' : 'Balasan (Lihat)' }}</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="fv-row">
                        @if ($data->status == 'Pending')
                            <textarea name="balasan" id="balasan" class="form-control @error('balasan') is-invalid @enderror" placeholder="Masukkan balasan pesan" required>{{ old('balasan') }}</textarea>
                            @error('balasan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @else
                            <div class="border rounded p-5 bg-light">
                                {!! Helper::updateImageUrls($data->balasan) !!}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            {{-- end::Reply editor --}}

            {{-- begin::Actions --}}
            @if ($data->status == 'Pending')
                <div class="d-flex justify-content-end">
                    <a href="{{ route('prt.apps.kotak.pesan.index') }}" id="kt_pesan_cancel" class="btn btn-light me-5">
                        <i class="ki-outline ki-arrow-left fs-2"></i>Batal
                    </a>
                    <button type="submit" id="kt_pesan_submit" class="btn btn-primary">
                        <span class="indicator-label">
                            <i class="fa-solid fa-save me-2"></i>{{ $submit }}
                        </span>
                        <span class="indicator-progress">
                            Please wait...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
                    </button>
                </div>
            @else
                <div class="d-flex justify-content-end">
                    <a href="{{ route('prt.apps.kotak.pesan.index') }}" class="btn btn-light">
                        <i class="ki-outline ki-arrow-left fs-2"></i>Kembali
                    </a>
                </div>
            @endif
            {{-- end::Actions --}}
        </div>
        {{-- end::Main column --}}
    </form>
@endsection
{{-- CONTENT::END --}}

{{-- SCRIPTS::BEGIN --}}
@push('scripts')
    @if ($data->status == 'Pending')
        <script src="{{ asset('be/plugins/custom/tinymce/tinymce.bundle.js') }}"></script>
        <script>
            "use strict";

            var KTAppPesanReply = function() {
                var form;
                var submitButton;
                var cancelButton;

                var handleForm = function() {
                    submitButton.addEventListener('click', function(e) {
                        e.preventDefault();

                        var balasan = tinymce.get('balasan').getContent().trim();

                        if (!balasan || balasan === '<p></p>' || balasan === '<p><br></p>') {
                            Swal.fire({
                                text: "Balasan wajib diisi",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "Ok, saya mengerti!",
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                            });
                            return;
                        }

                        tinymce.get('balasan').save();
                        submitButton.setAttribute('data-kt-indicator', 'on');
                        submitButton.disabled = true;
                        form.submit();
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

                var handleTinyMCE = function() {
                    tinymce.init({
                        selector: "#balasan",
                        height: 400,
                        menubar: false,
                        statusbar: false,
                        toolbar_mode: 'sliding',
                        toolbar: [
                            "styleselect fontselect fontsizeselect",
                            "undo redo | bold italic underline | link | alignleft aligncenter alignright",
                            "bullist numlist | outdent indent | blockquote | table"
                        ],
                        plugins: "advlist autolink link lists table paste wordcount",
                        content_style: `
                            body {
                                font-family: 'Inter', sans-serif;
                                font-size: 14px;
                                line-height: 1.6;
                            }
                        `,
                        paste_as_text: true,
                        verify_html: false,
                        valid_elements: 'p,br,strong,em,ul,ol,li,a[href|target|title],table,tr,td,th,tbody,thead,tfoot,blockquote,div,span[style],sub,sup',
                        font_family_formats: "Arial=arial,helvetica,sans-serif; Times New Roman=times new roman,times,serif; Verdana=verdana,arial,helvetica,sans-serif;",
                        font_size_formats: "10pt 12pt 14pt 16pt 18pt 24pt",
                        setup: function(editor) {
                            @if (old('balasan'))
                                editor.setContent({!! json_encode(old('balasan')) !!});
                            @endif
                            editor.on('change', function() {
                                editor.save();
                            });
                        }
                    });
                }

                return {
                    init: function() {
                        form = document.querySelector('#kt_pesan_form');
                        submitButton = document.querySelector('#kt_pesan_submit');
                        cancelButton = document.querySelector('#kt_pesan_cancel');

                        if (!form) {
                            console.error('Form #kt_pesan_form not found');
                            return;
                        }

                        handleForm();
                        handleTinyMCE();
                    }
                };
            }();

            KTUtil.onDOMContentLoaded(function() {
                KTAppPesanReply.init();
            });
        </script>
    @endif
@endpush
{{-- SCRIPTS::END --}}
