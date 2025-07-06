@extends('layouts.admin')

{{-- SEO::BEGIN --}}
@section('title', 'Master Users ' . $role)
@section('description', 'Master Users ' . $role)
{{-- SEO::END --}}

{{-- TOOLBAR::BEGIN --}}
@section('toolbar')
    <div class="d-flex flex-stack flex-row-fluid">
        {{-- begin::Toolbar container --}}
        <div class="d-flex flex-column flex-row-fluid">
            <div class="page-title d-flex align-items-center me-3">
                <h1 class="page-heading d-flex flex-column justify-content-center text-dark fw-bold fs-lg-2x gap-2">
                    <span>Master Users {{ $role }}</span>
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
                <li class="breadcrumb-item text-gray-700">Pengaturan</li>
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                <li class="breadcrumb-item text-gray-700">Master Data</li>
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                <li class="breadcrumb-item text-gray-700">Users {{ $role }}</li>
            </ul>
        </div>
        {{-- end::Toolbar container --}}
        {{-- begin::Actions --}}
        <div class="d-flex align-self-center flex-center flex-shrink-0">
            <a href="{{ route('prt.apps.mst.users.create', [$tags]) }}" class="btn btn-sm btn-primary d-flex flex-center ms-3 px-4 py-3">
                <i class="ki-outline ki-plus fs-2"></i>
                <span>Tambah User</span>
            </a>
        </div>
        {{-- end::Actions --}}
    </div>
@endsection
{{-- TOOLBAR::END --}}

{{-- CONTENT::BEGIN --}}
@section('content')
    <div class="card">
        {{-- begin::Card header --}}
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h4 class="card-label fw-bold fs-3 mb-1">Data Master Users {{ $role }}</h4>
            </div>
        </div>
        {{-- end::Card header --}}

        {{-- begin::Card body --}}
        <div class="card-body py-4">
            <div class="table-responsive position-relative">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="datatable">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th width="30px">#</th>
                            <th>Nama</th>
                            <th>Kontak</th>
                            <th>Email</th>
                            <th>Jabatan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                    </tbody>
                    <tfoot>
                        <tr>
                            <th width="30px">#</th>
                            <th>Nama</th>
                            <th>Kontak</th>
                            <th>Email</th>
                            <th>Jabatan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        {{-- end::Card body --}}
    </div>
@endsection
{{-- CONTENT::END --}}

{{-- STYLES::BEGIN --}}
@push('styles')
    <style>
        /* Ensure table container has relative positioning */
        .dataTables_wrapper {
            position: relative;
        }

        /* Custom processing indicator */
        #datatable_processing {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 200px;
            margin-left: -100px;
            margin-top: -20px;
            text-align: center;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            z-index: 1001;
        }

        /* Hide default processing */
        .dataTables_processing {
            display: none !important;
        }

        /* Custom spinner */
        .custom-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(0, 0, 0, .3);
            border-radius: 50%;
            border-top-color: #000;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
@endpush
{{-- STYLES::END --}}

{{-- SCRIPTS::BEGIN --}}
@push('scripts')
    <script>
        "use strict";

        var KTUsersMaster = function() {
            var table;
            var datatable;

            var initDatatable = function() {
                datatable = $('#datatable').DataTable({
                    "processing": true,
                    "serverSide": true,
                    "responsive": true,
                    "paging": true,
                    "searching": false, // Disable search
                    "ordering": true,
                    "info": true,
                    "autoWidth": false,
                    "stateSave": false,
                    "pageLength": 10,
                    "lengthMenu": [
                        [10, 25, 50, 100, -1],
                        [10, 25, 50, 100, "All"]
                    ],
                    "order": [
                        [1, 'asc'] // Order by nama_lengkap
                    ],
                    "language": {
                        "lengthMenu": '_MENU_ entries per page',
                        "processing": '<div class="d-flex align-items-center"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</div>',
                        "paginate": {
                            next: '<i class="ki-outline ki-arrow-right"></i>',
                            previous: '<i class="ki-outline ki-arrow-left"></i>',
                            first: '<i class="ki-outline ki-double-arrow-left"></i>',
                            last: '<i class="ki-outline ki-double-arrow-right"></i>'
                        },
                        "emptyTable": "Tidak ada data user",
                        "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ user",
                        "infoEmpty": "Menampilkan 0 sampai 0 dari 0 user",
                        "infoFiltered": "(disaring dari _MAX_ total user)",
                        "zeroRecords": "Tidak ditemukan data yang sesuai"
                    },
                    "ajax": {
                        "url": "{!! route('prt.apps.mst.users.index', [$tags]) !!}",
                        "type": 'GET',
                        "dataSrc": function(json) {
                            console.log('DataTable response:', json);
                            return json.data;
                        },
                        "error": function(xhr, error, thrown) {
                            console.error('DataTable AJAX Error:', xhr.responseText);
                            $('#datatable_processing').hide();

                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Gagal memuat data. Silakan refresh halaman.',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    "columns": [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false,
                            title: '#'
                        },
                        {
                            data: 'nama_lengkap',
                            name: 'nama_lengkap',
                            title: 'Nama'
                        },
                        {
                            data: 'kontak',
                            name: 'kontak',
                            title: 'Kontak'
                        },
                        {
                            data: 'email',
                            name: 'email',
                            title: 'Email'
                        },
                        {
                            data: 'jabatan',
                            name: 'jabatan',
                            title: 'Jabatan'
                        },
                        {
                            data: 'status',
                            name: 'status',
                            title: 'Status'
                        },
                        {
                            data: 'aksi',
                            name: 'aksi',
                            orderable: false,
                            searchable: false,
                            title: 'Aksi'
                        }
                    ],
                    "columnDefs": [{
                        className: "text-center",
                        targets: [0, 5, 6]
                    }],
                    "drawCallback": function(settings) {
                        $('#datatable_processing').hide();
                        if (typeof KTApp !== 'undefined' && KTApp.initBootstrapTooltips) {
                            KTApp.initBootstrapTooltips();
                        }
                    },
                    "preDrawCallback": function(settings) {
                        console.log('DataTable pre-draw started');
                    },
                    "initComplete": function(settings, json) {
                        $('#datatable_processing').hide();
                        console.log('DataTable initialization completed');
                    }
                });
            };

            var handleEvents = function() {
                $(document).on('click', '[data-delete]', function() {
                    let uuid = $(this).attr('data-delete');

                    Swal.fire({
                        title: "Hapus Data",
                        text: "Apakah Anda yakin ingin menghapus user ini?",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#f1416c",
                        cancelButtonColor: "#7e8299",
                        confirmButtonText: "Ya, hapus!",
                        cancelButtonText: "Batal"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{!! route('prt.apps.mst.users.destroy', [$tags]) !!}",
                                type: 'POST',
                                data: {
                                    uuid: uuid,
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function(res) {
                                    datatable.ajax.reload(null, false);
                                    Swal.fire({
                                        title: "Success",
                                        text: res.message,
                                        icon: "success",
                                    });
                                },
                                error: function(xhr) {
                                    datatable.ajax.reload(null, false);
                                    Swal.fire({
                                        title: "Error",
                                        text: xhr.responseJSON?.message || 'Terjadi kesalahan',
                                        icon: "error",
                                    });
                                }
                            });
                        }
                    });
                });

                $(document).on('click', '[data-status]', function() {
                    let uuid = $(this).attr('data-status');
                    let status = $(this).attr('data-status-value');

                    $.ajax({
                        url: "{!! route('prt.apps.mst.users.status', [$tags]) !!}",
                        type: 'POST',
                        data: {
                            uuid: uuid,
                            status: status,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            datatable.ajax.reload();
                            Swal.fire({
                                title: "Success",
                                text: res.message,
                                icon: "success",
                            });
                        },
                        error: function(xhr) {
                            datatable.ajax.reload();
                            Swal.fire({
                                title: "Error",
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan',
                                icon: "error",
                            });
                        }
                    });
                });
            };

            return {
                init: function() {
                    table = document.querySelector('#datatable');
                    if (!table) {
                        return;
                    }
                    initDatatable();
                    handleEvents();
                }
            };
        }();

        $(document).ready(function() {
            KTUsersMaster.init();
        });
    </script>
@endpush
{{-- SCRIPTS::END --}}
