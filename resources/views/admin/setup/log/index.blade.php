@push('styles')
    <style>
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

        /* Ensure table container has relative positioning */
        .dataTables_wrapper {
            position: relative;
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

        /* Hide default export buttons */
        .dt-buttons {
            display: none !important;
        }
    </style>
@endpush

@extends('layouts.admin')

{{-- SEO::BEGIN --}}
@section('title', 'Audit Trail')
{{-- SEO::END --}}

{{-- TOOLBAR::BEGIN --}}
@section('toolbar')
    <div class="d-flex flex-stack flex-row-fluid">
        {{-- begin::Toolbar container --}}
        <div class="d-flex flex-column flex-row-fluid">
            {{-- begin::Page title --}}
            <div class="page-title d-flex align-items-center me-3">
                <h1 class="page-heading d-flex flex-column justify-content-center text-dark fw-bold fs-lg-2x gap-2">
                    <span>Audit Trail</span>
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
                <li class="breadcrumb-item text-gray-700">Audit Trail</li>
            </ul>
            {{-- end::Breadcrumb --}}
        </div>
        {{-- end::Toolbar container --}}
        {{-- begin::Actions --}}
        <div class="d-flex align-self-center flex-center flex-shrink-0">
            {{-- begin::Filter dropdown --}}
            <div class="me-3">
                <a href="#" class="btn btn-sm btn-flex btn-outline btn-color-gray-700 btn-active-color-primary bg-body h-40px fs-7 fw-bold" data-kt-menu-trigger="click"
                    data-kt-menu-placement="bottom-end">
                    <i class="ki-outline ki-filter fs-6 text-muted me-1"></i>
                    Filter: <span id="filter-text" class="ms-1">{{ $logType }}</span>
                </a>
                {{-- begin::Menu --}}
                <div class="menu menu-sub menu-sub-dropdown w-250px w-md-300px" data-kt-menu="true" id="kt_menu_filter">
                    {{-- begin::Header --}}
                    <div class="px-7 py-5">
                        <div class="fs-5 text-dark fw-bold">Filter Options</div>
                    </div>
                    {{-- end::Header --}}
                    {{-- begin::Separator --}}
                    <div class="separator border-gray-200"></div>
                    {{-- end::Separator --}}
                    {{-- begin::Content --}}
                    <div class="px-7 py-5">
                        {{-- begin::Input group --}}
                        <div class="mb-10">
                            <label class="form-label fw-semibold">Tipe Log:</label>
                            <div>
                                <select class="form-select form-select-solid" name="q_log_type" id="q_log_type" data-control="select2" data-placeholder="Pilih Tipe"
                                    data-allow-clear="true">
                                    @foreach ($logTypes as $type)
                                        <option @if ($logType == $type) selected @endif value="{{ $type }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        {{-- end::Input group --}}
                        {{-- begin::Actions --}}
                        <div class="d-flex justify-content-end">
                            <button type="reset" class="btn btn-sm btn-light btn-active-light-primary me-2" data-kt-menu-dismiss="true" onclick="resetFilter()">Reset</button>
                            <button type="submit" class="btn btn-sm btn-primary" data-kt-menu-dismiss="true" onclick="applyFilter()">Apply</button>
                        </div>
                        {{-- end::Actions --}}
                    </div>
                    {{-- end::Content --}}
                </div>
                {{-- end::Menu --}}
            </div>
            {{-- end::Filter dropdown --}}
            {{-- begin::Export button --}}
            <button type="button" class="btn btn-sm btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                <i class="ki-outline ki-exit-down fs-2"></i>
                Export Report
            </button>
            {{-- begin::Export Menu --}}
            <div id="kt_datatable_example_export_menu"
                class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-200px py-4" data-kt-menu="true">
                <div class="menu-item px-3">
                    <a href="#" class="menu-link px-3" data-kt-export="copy">Copy to clipboard</a>
                </div>
                <div class="menu-item px-3">
                    <a href="#" class="menu-link px-3" data-kt-export="excel">Export as Excel</a>
                </div>
                <div class="menu-item px-3">
                    <a href="#" class="menu-link px-3" data-kt-export="csv">Export as CSV</a>
                </div>
                <div class="menu-item px-3">
                    <a href="#" class="menu-link px-3" data-kt-export="pdf">Export as PDF</a>
                </div>
            </div>
            {{-- end::Export Menu --}}
            {{-- Hidden export buttons container --}}
            <div id="kt_datatable_example_buttons" class="d-none"></div>
            {{-- end::Export button --}}
        </div>
        {{-- end::Actions --}}
    </div>
@endsection
{{-- TOOLBAR::END --}}

{{-- CONTENT::BEGIN --}}
@section('content')
    {{-- begin::Card --}}
    <div class="card">
        {{-- begin::Card header --}}
        <div class="card-header border-0 pt-6">
            {{-- begin::Card title --}}
            <div class="card-title">
                <h4 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold fs-3 mb-1">Audit Trail</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">Menampilkan log: <strong id="titleType">{{ $logType }}</strong></span>
                </h4>
            </div>
            {{-- end::Card title --}}
            {{-- begin::Card toolbar --}}
            <div class="card-toolbar">
                {{-- begin::Search --}}
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-outline ki-magnifier fs-1 position-absolute ms-6"></i>
                    <input type="text" data-kt-audit-table-filter="search" class="form-control form-control-solid w-250px ps-14" placeholder="Cari log..." />
                </div>
                {{-- end::Search --}}
            </div>
            {{-- end::Card toolbar --}}
        </div>
        {{-- end::Card header --}}

        {{-- begin::Card body --}}
        <div class="card-body py-4">
            {{-- begin::Table --}}
            <div class="table-responsive position-relative">
                {{-- Custom Processing Indicator --}}
                <div id="datatable_processing" style="display: none;">
                    <div class="d-flex align-items-center">
                        <div class="custom-spinner me-2"></div>
                        <span>Loading data...</span>
                    </div>
                </div>

                <table class="table align-middle table-row-dashed fs-6 gy-5" id="datatable">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th width="30px">#</th>
                            <th width="15%">Tipe</th>
                            @if (in_array($auth->role, ['Super Admin', 'Admin']))
                                <th width="25%">Pengguna</th>
                            @endif
                            <th width="20%">Tanggal</th>
                            <th width="20%">Aktivitas</th>
                            <th>Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Tipe</th>
                            @if (in_array($auth->role, ['Super Admin', 'Admin']))
                                <th>Pengguna</th>
                            @endif
                            <th>Tanggal</th>
                            <th>Aktivitas</th>
                            <th>Deskripsi</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            {{-- end::Table --}}
        </div>
        {{-- end::Card body --}}
    </div>
    {{-- end::Card --}}
@endsection
{{-- CONTENT::END --}}

{{-- SCRIPTS::BEGIN --}}
@push('scripts')
    <script>
        "use strict";

        // Class definition
        var KTDatatablesAuditTrail = function() {
            // Shared variables
            var table;
            var datatable;

            // Private functions
            var initDatatable = function() {
                // Initialize DataTable
                datatable = $('#datatable').DataTable({
                    "processing": true,
                    "serverSide": false,
                    "responsive": true,
                    "paging": true,
                    "searching": true,
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
                        [{{ in_array($auth->role, ['Super Admin', 'Admin']) ? 3 : 2 }}, 'desc'] // Order by Tanggal
                    ],
                    "language": {
                        "searchPlaceholder": 'Cari log...',
                        "sSearch": '',
                        "lengthMenu": '_MENU_ entri per halaman',
                        "processing": '<div class="d-flex align-items-center"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</div>',
                        "loadingRecords": '<div class="d-flex align-items-center"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</div>',
                        "paginate": {
                            next: '<i class="ki-outline ki-arrow-right"></i>',
                            previous: '<i class="ki-outline ki-arrow-left"></i>',
                            first: '<i class="ki-outline ki-double-arrow-left"></i>',
                            last: '<i class="ki-outline ki-double-arrow-right"></i>'
                        },
                        "emptyTable": "Tidak ada data",
                        "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_",
                        "infoEmpty": "Menampilkan 0 sampai 0 dari 0",
                        "infoFiltered": "(disaring dari _MAX_ total)",
                        "zeroRecords": "Tidak ditemukan data yang sesuai"
                    },
                    "ajax": {
                        "url": "{!! route('setup.apps.log.index') !!}",
                        "type": 'GET',
                        "data": function(data) {
                            data.filter = {
                                'type': $('[name="q_log_type"]').val() || 'Semua Log',
                            };
                        },
                        "dataSrc": function(json) {
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
                            data: 'type',
                            name: 'type'
                        },
                        @if (in_array($auth->role, ['Super Admin', 'Admin']))
                            {
                                data: 'user_name',
                                name: 'user_name',
                                render: function(data, type, row) {
                                    return data || '-';
                                }
                            },
                        @endif {
                            data: 'created_at',
                            name: 'created_at'
                        },
                        {
                            data: 'activity',
                            name: 'activity'
                        },
                        {
                            data: 'description',
                            name: 'description'
                        }
                    ],
                    "columnDefs": [{
                        className: "text-center",
                        targets: [0, 1, {{ in_array($auth->role, ['Super Admin', 'Admin']) ? 3 : 2 }}]
                    }],
                    "drawCallback": function(settings) {
                        $('#datatable_processing').hide();

                        if (typeof KTApp !== 'undefined' && KTApp.initBootstrapTooltips) {
                            KTApp.initBootstrapTooltips();
                        }
                    },
                    "preDrawCallback": function(settings) {},
                    "initComplete": function(settings, json) {
                        $('#datatable_processing').hide();
                    }
                });
            }

            // Hook export buttons
            var exportButtons = function() {
                const documentTitle = 'Audit Trail Log';

                var buttons = new $.fn.dataTable.Buttons(datatable, {
                    buttons: [{
                            extend: 'copyHtml5',
                            title: function() {
                                const currentFilter = $('[name="q_log_type"]').val() || 'Semua Log';
                                return documentTitle + ' - ' + currentFilter;
                            },
                            exportOptions: {
                                columns: [0, 1,
                                    @if (in_array($auth->role, ['Super Admin', 'Admin']))
                                        2,
                                    @endif
                                    3, 4
                                ]
                            }
                        },
                        {
                            extend: 'excelHtml5',
                            title: function() {
                                const currentFilter = $('[name="q_log_type"]').val() || 'Semua Log';
                                return documentTitle + ' - ' + currentFilter;
                            },
                            filename: function() {
                                const date = new Date().toISOString().slice(0, 10);
                                const filter = ($('[name="q_log_type"]').val() || 'Semua Log').toLowerCase().replace(/\s+/g, '-');
                                return `audit-trail-${filter}-${date}`;
                            },
                            exportOptions: {
                                columns: [0, 1,
                                    @if (in_array($auth->role, ['Super Admin', 'Admin']))
                                        2,
                                    @endif
                                    3, 4
                                ]
                            }
                        },
                        {
                            extend: 'csvHtml5',
                            title: function() {
                                const currentFilter = $('[name="q_log_type"]').val() || 'Semua Log';
                                return documentTitle + ' - ' + currentFilter;
                            },
                            filename: function() {
                                const date = new Date().toISOString().slice(0, 10);
                                const filter = ($('[name="q_log_type"]').val() || 'Semua Log').toLowerCase().replace(/\s+/g, '-');
                                return `audit-trail-${filter}-${date}`;
                            },
                            exportOptions: {
                                columns: [0, 1,
                                    @if (in_array($auth->role, ['Super Admin', 'Admin']))
                                        2,
                                    @endif
                                    3, 4
                                ]
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            title: function() {
                                const currentFilter = $('[name="q_log_type"]').val() || 'Semua Log';
                                return documentTitle + ' - ' + currentFilter;
                            },
                            filename: function() {
                                const date = new Date().toISOString().slice(0, 10);
                                const filter = ($('[name="q_log_type"]').val() || 'Semua Log').toLowerCase().replace(/\s+/g, '-');
                                return `audit-trail-${filter}-${date}`;
                            },
                            orientation: 'landscape',
                            pageSize: 'A4',
                            exportOptions: {
                                columns: [0, 1,
                                    @if (in_array($auth->role, ['Super Admin', 'Admin']))
                                        2,
                                    @endif
                                    3, 4
                                ]
                            },
                            customize: function(doc) {
                                doc.content[1].table.widths = ['10%', '15%',
                                    @if (in_array($auth->role, ['Super Admin', 'Admin']))
                                        '25%',
                                    @endif
                                    '20%', '30%'
                                ];
                                doc.styles.tableHeader.fontSize = 9;
                                doc.styles.tableBodyOdd.fontSize = 8;
                                doc.styles.tableBodyEven.fontSize = 8;
                                doc.defaultStyle.fontSize = 8;
                            }
                        }
                    ]
                }).container().appendTo($('#kt_datatable_example_buttons'));

                // Hook dropdown menu click event to datatable export buttons
                const exportButtons = document.querySelectorAll('#kt_datatable_example_export_menu [data-kt-export]');
                exportButtons.forEach(exportButton => {
                    exportButton.addEventListener('click', e => {
                        e.preventDefault();

                        // Get clicked export value
                        const exportValue = e.target.getAttribute('data-kt-export');
                        const target = document.querySelector('.dt-buttons .buttons-' + exportValue);

                        if (target) {
                            // Show loading indicator
                            Swal.fire({
                                title: `Exporting ${exportValue.toUpperCase()}...`,
                                text: 'Mohon tunggu',
                                allowOutsideClick: false,
                                timer: 1500,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            // Trigger click event on hidden datatable export buttons
                            setTimeout(() => {
                                target.click();

                                // Show success message
                                setTimeout(() => {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Export Berhasil!',
                                        text: `Data berhasil di-export ke ${exportValue.toUpperCase()}`,
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                }, 300);
                            }, 100);
                        } else {
                            console.error('Export button not found:', exportValue);
                            Swal.fire({
                                icon: 'error',
                                title: 'Export Gagal!',
                                text: 'Terjadi kesalahan saat melakukan export',
                            });
                        }
                    });
                });
            }

            // Search Datatable
            var handleSearchDatatable = function() {
                const filterSearch = document.querySelector('[data-kt-audit-table-filter="search"]');
                if (filterSearch) {
                    filterSearch.addEventListener('keyup', function(e) {
                        datatable.search(e.target.value).draw();
                    });
                }
            }

            // Filter functions
            var handleFilter = function() {
                // Initialize Select2
                $('#q_log_type').select2();

                // Global filter functions
                window.applyFilter = function() {
                    var selectedType = document.getElementById('q_log_type').value;
                    document.getElementById('filter-text').textContent = selectedType;

                    $('#datatable_processing').show();
                    datatable.ajax.reload(function(json) {
                        $('#datatable_processing').hide();
                        $('#titleType').html(selectedType);
                    }, false);
                }

                window.resetFilter = function() {
                    document.getElementById('q_log_type').value = 'Semua Log';
                    document.getElementById('filter-text').textContent = 'Semua Log';

                    $('#datatable_processing').show();
                    datatable.ajax.reload(function(json) {
                        $('#datatable_processing').hide();
                        $('#titleType').html('Semua Log');
                    }, false);
                }

                // Handle filter change
                $('[name="q_log_type"]').change(function() {
                    var q_log_type = $(this).val();

                    $('#datatable_processing').show();
                    $('#datatable tbody').empty();
                    datatable.ajax.reload(function(json) {
                        $('#datatable_processing').hide();
                    }, false);

                    $('#titleType').html(q_log_type);
                });
            }

            // Public methods
            return {
                init: function() {
                    table = document.querySelector('#datatable');
                    if (!table) {
                        return;
                    }

                    initDatatable();
                    exportButtons();
                    handleSearchDatatable();
                    handleFilter();
                }
            };
        }();

        // On document ready
        $(document).ready(function() {
            KTDatatablesAuditTrail.init();
        });
    </script>
@endpush
{{-- SCRIPTS::END --}}
