@push('styles')
    <style>
        /* Bulk actions toolbar animation */
        [data-kt-faq-table-toolbar="selected"] {
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(-10px);
        }

        [data-kt-faq-table-toolbar="selected"]:not(.d-none) {
            opacity: 1;
            transform: translateY(0);
        }

        /* Bulk action buttons styling */
        .btn-group .btn {
            border-radius: 0;
        }

        .btn-group .btn:first-child {
            border-top-left-radius: 0.475rem;
            border-bottom-left-radius: 0.475rem;
        }

        .btn-group .btn:last-child {
            border-top-right-radius: 0.475rem;
            border-bottom-right-radius: 0.475rem;
        }

        /* Bulk toolbar background */
        [data-kt-faq-table-toolbar="selected"] {
            border: 1px solid rgba(33, 150, 243, 0.25);
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

        /* FAQ Counter badge styling */
        .faq-counter {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
            font-weight: 600;
            border-radius: 12px;
            padding: 4px 8px;
            font-size: 11px;
            min-width: 20px;
            text-align: center;
        }
    </style>
@endpush

@extends('layouts.admin')

{{-- SEO::BEGIN --}}
@section('title', 'FAQ Management')
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
                    <span>FAQ Management</span>
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
                <li class="breadcrumb-item text-gray-700">Konten Internal</li>
                {{-- end::Item --}}
                {{-- begin::Item --}}
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                {{-- end::Item --}}
                {{-- begin::Item --}}
                <li class="breadcrumb-item text-gray-700">FAQ</li>
                {{-- end::Item --}}
            </ul>
            {{-- end::Breadcrumb --}}
        </div>
        {{-- end::Toolbar container --}}
        {{-- begin::Actions --}}
        <div class="d-flex align-self-center flex-center flex-shrink-0">
            {{-- begin::Export button --}}
            <button type="button" class="btn btn-sm btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                <i class="ki-outline ki-exit-down fs-2"></i>
                Export Report
            </button>
            {{-- begin::Export Menu --}}
            <div id="kt_datatable_example_export_menu"
                class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-200px py-4" data-kt-menu="true">
                {{-- begin::Menu item --}}
                <div class="menu-item px-3">
                    <a href="#" class="menu-link px-3" data-kt-export="copy">
                        Copy to clipboard
                    </a>
                </div>
                {{-- end::Menu item --}}
                {{-- begin::Menu item --}}
                <div class="menu-item px-3">
                    <a href="#" class="menu-link px-3" data-kt-export="excel">
                        Export as Excel
                    </a>
                </div>
                {{-- end::Menu item --}}
                {{-- begin::Menu item --}}
                <div class="menu-item px-3">
                    <a href="#" class="menu-link px-3" data-kt-export="csv">
                        Export as CSV
                    </a>
                </div>
                {{-- end::Menu item --}}
                {{-- begin::Menu item --}}
                <div class="menu-item px-3">
                    <a href="#" class="menu-link px-3" data-kt-export="pdf">
                        Export as PDF
                    </a>
                </div>
                {{-- end::Menu item --}}
            </div>
            {{-- end::Export Menu --}}
            {{-- Hidden export buttons container --}}
            <div id="kt_datatable_example_buttons" class="d-none"></div>
            {{-- end::Export button --}}
            {{-- begin::Primary button --}}
            <a href="{{ route('prt.apps.faq.create') }}" class="btn btn-sm btn-primary d-flex flex-center ms-3 px-4 py-3">
                <i class="ki-outline ki-plus fs-2"></i>
                <span>Tambah FAQ</span>
            </a>
            {{-- end::Primary button --}}
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
                    <span class="card-label fw-bold fs-3 mb-1">Data FAQ</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">Kelola daftar FAQ untuk website</span>
                </h4>
            </div>
            {{-- end::Card title --}}
            {{-- begin::Card toolbar --}}
            <div class="card-toolbar">
                {{-- begin::Search --}}
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-outline ki-magnifier fs-1 position-absolute ms-6"></i>
                    <input type="text" data-kt-faq-table-filter="search" class="form-control form-control-solid w-250px ps-14" placeholder="Cari FAQ..." />
                </div>
                {{-- end::Search --}}
            </div>
            {{-- end::Card toolbar --}}
        </div>
        {{-- end::Card header --}}

        {{-- begin::Card body --}}
        <div class="card-body py-4">
            {{-- begin::Bulk Actions --}}
            <div class="d-flex justify-content-between align-items-center d-none bg-light-primary rounded p-3 mb-5" data-kt-faq-table-toolbar="selected">
                <div class="fw-bold text-primary">
                    <i class="ki-outline ki-check-square fs-2 me-2"></i>
                    <span data-kt-faq-table-select="selected_count"></span> item dipilih
                </div>

                <div class="d-flex align-items-center gap-2">
                    {{-- begin::Bulk Status Actions --}}
                    <div class="btn-group me-2" role="group" aria-label="Status Actions">
                        <button type="button" class="btn btn-sm btn-light-success" data-kt-faq-table-select="activate_selected" data-bs-toggle="tooltip" title="Aktifkan yang dipilih">
                            <i class="ki-outline ki-check fs-6 me-1"></i>
                            Aktifkan
                        </button>
                        <button type="button" class="btn btn-sm btn-light-warning" data-kt-faq-table-select="deactivate_selected" data-bs-toggle="tooltip"
                            title="Nonaktifkan yang dipilih">
                            <i class="ki-outline ki-cross fs-6 me-1"></i>
                            Nonaktifkan
                        </button>
                    </div>
                    {{-- end::Bulk Status Actions --}}

                    {{-- begin::Bulk Delete Action --}}
                    <button type="button" class="btn btn-sm btn-light-danger me-2" data-kt-faq-table-select="delete_selected" data-bs-toggle="tooltip" title="Hapus yang dipilih">
                        <i class="ki-outline ki-trash fs-6 me-1"></i>
                        Hapus
                    </button>
                    {{-- end::Bulk Delete Action --}}

                    {{-- begin::Cancel Selection --}}
                    <button type="button" class="btn btn-sm btn-light" data-kt-faq-table-select="cancel_selection" data-bs-toggle="tooltip" title="Batalkan pilihan">
                        <i class="ki-outline ki-cross fs-6"></i>
                    </button>
                    {{-- end::Cancel Selection --}}
                </div>
            </div>
            {{-- end::Bulk Actions --}}

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
                            <th class="w-10px pe-2">
                                <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                    <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#datatable .row-checkbox" value="1" />
                                </div>
                            </th>
                            <th width="30px">#</th>
                            <th>Judul & Deskripsi FAQ</th>
                            <th width="10%">FAQ</th>
                            <th width="12%">Author</th>
                            <th width="12%">Publisher</th>
                            <th width="12%">Tanggal</th>
                            <th width="10%">Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="w-10px pe-2"></th>
                            <th width="30px">#</th>
                            <th>Judul & Deskripsi FAQ</th>
                            <th width="10%">FAQ</th>
                            <th width="12%">Author</th>
                            <th width="12%">Publisher</th>
                            <th width="12%">Tanggal</th>
                            <th width="10%">Status</th>
                            <th>Aksi</th>
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
        var KTDatatablesFaq = function() {
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
                        [1, 'asc']
                    ], // Order by index
                    "language": {
                        "searchPlaceholder": 'Search...',
                        "sSearch": '',
                        "lengthMenu": '_MENU_ entries per page',
                        "processing": '<div class="d-flex align-items-center"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</div>',
                        "loadingRecords": '<div class="d-flex align-items-center"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</div>',
                        "paginate": {
                            next: '<i class="ki-outline ki-arrow-right"></i>',
                            previous: '<i class="ki-outline ki-arrow-left"></i>',
                            first: '<i class="ki-outline ki-double-arrow-left"></i>',
                            last: '<i class="ki-outline ki-double-arrow-right"></i>'
                        },
                        "emptyTable": "Tidak ada data FAQ",
                        "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_",
                        "infoEmpty": "Menampilkan 0 sampai 0 dari 0",
                        "infoFiltered": "(disaring dari _MAX_ total)",
                        "zeroRecords": "Tidak ditemukan data FAQ yang sesuai"
                    },
                    "ajax": {
                        "url": "{!! route('prt.apps.faq.index') !!}",
                        "type": 'GET',
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
                            data: null,
                            orderable: false,
                            searchable: false,
                            exportable: false,
                            render: function(data, type, row) {
                                return '<div class="form-check form-check-sm form-check-custom form-check-solid"><input class="form-check-input row-checkbox" type="checkbox" value="' +
                                    row.uuid + '" /></div>';
                            }
                        },
                        {
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false,
                            title: '#'
                        },
                        {
                            data: 'judul',
                            name: 'judul',
                            render: function(data, type, row) {
                                if (type === 'export') {
                                    return data;
                                }
                                return data;
                            }
                        },
                        {
                            data: 'jumlah',
                            name: 'jumlah',
                            orderable: false,
                            searchable: false,
                        },
                        {
                            data: 'penulis',
                            name: 'penulis',
                        },
                        {
                            data: 'publisher',
                            name: 'publisher',
                        },
                        {
                            data: 'tanggal',
                            name: 'tanggal',
                        },
                        {
                            data: 'status',
                            name: 'status',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                if (type === 'export') {
                                    return row.status_raw || 'Aktif';
                                }
                                return data;
                            }
                        },
                        {
                            data: 'aksi',
                            name: 'aksi',
                            orderable: false,
                            searchable: false,
                            exportable: false,
                        }
                    ],
                    "columnDefs": [{
                        className: "text-center",
                        targets: [0, 1, 3, 7, 8]
                    }],
                    "drawCallback": function(settings) {
                        $('#datatable_processing').hide();

                        if (typeof KTApp !== 'undefined' && KTApp.initBootstrapTooltips) {
                            KTApp.initBootstrapTooltips();
                        }

                        handleBulkActions();
                    },
                    "preDrawCallback": function(settings) {},
                    "initComplete": function(settings, json) {
                        $('#datatable_processing').hide();
                        handleBulkActions();
                    }
                });
            }

            // Hook export buttons
            var exportButtons = function() {
                const documentTitle = 'Data FAQ';

                var buttons = new $.fn.dataTable.Buttons(datatable, {
                    buttons: [{
                            extend: 'copyHtml5',
                            title: function() {
                                return documentTitle;
                            },
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6, 7] // Exclude checkbox and actions columns
                            }
                        },
                        {
                            extend: 'excelHtml5',
                            title: function() {
                                return documentTitle;
                            },
                            filename: function() {
                                const date = new Date().toISOString().slice(0, 10);
                                return `faq-data-${date}`;
                            },
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6, 7]
                            }
                        },
                        {
                            extend: 'csvHtml5',
                            title: function() {
                                return documentTitle;
                            },
                            filename: function() {
                                const date = new Date().toISOString().slice(0, 10);
                                return `faq-data-${date}`;
                            },
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6, 7]
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            title: function() {
                                return documentTitle;
                            },
                            filename: function() {
                                const date = new Date().toISOString().slice(0, 10);
                                return `faq-data-${date}`;
                            },
                            orientation: 'landscape',
                            pageSize: 'A4',
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6, 7]
                            },
                            customize: function(doc) {
                                doc.content[1].table.widths = ['8%', '35%', '8%', '15%', '15%', '12%', '7%'];
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
                const filterSearch = document.querySelector('[data-kt-faq-table-filter="search"]');
                if (filterSearch) {
                    filterSearch.addEventListener('keyup', function(e) {
                        datatable.search(e.target.value).draw();
                    });
                }
            }

            // Handle bulk actions
            var handleBulkActions = function() {
                const checkboxes = document.querySelectorAll('#datatable .row-checkbox');
                const bulkToolbar = document.querySelector('[data-kt-faq-table-toolbar="selected"]');
                const countElement = document.querySelector('[data-kt-faq-table-select="selected_count"]');

                let checkedCount = 0;
                checkboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        checkedCount++;
                    }
                });

                if (checkedCount > 0) {
                    countElement.textContent = checkedCount;
                    bulkToolbar.classList.remove('d-none');

                    setTimeout(() => {
                        if (typeof KTApp !== 'undefined' && KTApp.initBootstrapTooltips) {
                            KTApp.initBootstrapTooltips();
                        }
                    }, 100);
                } else {
                    bulkToolbar.classList.add('d-none');
                }
            }

            // Filter functions
            var handleFilter = function() {
                // No filter needed for simple status toggle
            }

            // Handle events
            var handleEvents = function() {
                // Handle checkbox events
                $(document).on('change', '.row-checkbox', function() {
                    handleBulkActions();
                });

                // Handle select all checkbox
                $(document).on('change', '[data-kt-check="true"]', function() {
                    const isChecked = $(this).is(':checked');
                    $('.row-checkbox').prop('checked', isChecked);
                    handleBulkActions();
                });

                // Prevent status toggle checkbox from affecting bulk selection
                $(document).on('click', '[data-status]', function(e) {
                    e.stopPropagation();
                });

                // Handle cancel selection
                $(document).on('click', '[data-kt-faq-table-select="cancel_selection"]', function() {
                    $('.row-checkbox').prop('checked', false);
                    $('[data-kt-check="true"]').prop('checked', false);

                    const bulkToolbar = document.querySelector('[data-kt-faq-table-toolbar="selected"]');
                    bulkToolbar.classList.add('d-none');
                });

                // Handle bulk status - Activate
                $(document).on('click', '[data-kt-faq-table-select="activate_selected"]', function() {
                    handleBulkStatus('1', 'mengaktifkan');
                });

                // Handle bulk status - Deactivate
                $(document).on('click', '[data-kt-faq-table-select="deactivate_selected"]', function() {
                    handleBulkStatus('0', 'menonaktifkan');
                });

                // Handle bulk delete
                $(document).on('click', '[data-kt-faq-table-select="delete_selected"]', function() {
                    const checkedBoxes = document.querySelectorAll('#datatable .row-checkbox:checked');
                    if (checkedBoxes.length === 0) {
                        Swal.fire({
                            title: "Peringatan",
                            text: "Pilih minimal satu item untuk dihapus",
                            icon: "warning"
                        });
                        return;
                    }

                    const selectedIds = Array.from(checkedBoxes).map(cb => cb.value);

                    Swal.fire({
                        title: "Hapus Data Terpilih",
                        text: `Apakah Anda yakin ingin menghapus ${selectedIds.length} FAQ yang dipilih?`,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#f1416c",
                        cancelButtonColor: "#7e8299",
                        confirmButtonText: "Ya, hapus semua!",
                        cancelButtonText: "Batal"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Menghapus data...',
                                text: 'Mohon tunggu',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            $.ajax({
                                url: "{!! route('prt.apps.faq.destroy.bulk') !!}",
                                type: 'POST',
                                data: {
                                    uuids: selectedIds,
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

                // Handle delete action
                $(document).on('click', "[data-delete]", function() {
                    let uuid = $(this).attr('data-delete');

                    Swal.fire({
                        title: "Hapus Data",
                        text: "Apakah Anda Yakin?",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#f1416c",
                        cancelButtonColor: "#7e8299",
                        confirmButtonText: "Ya, hapus!",
                        cancelButtonText: "Batal"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{!! route('prt.apps.faq.destroy') !!}",
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

                // Handle status toggle
                $(document).on('click', "[data-status]", function() {
                    let uuid = $(this).attr("data-status");
                    let status = $(this).attr("data-status-value");
                    $.ajax({
                        url: "{!! route('prt.apps.faq.status') !!}",
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
            }

            // Bulk status handler function
            var handleBulkStatus = function(status, actionText) {
                const checkedBoxes = document.querySelectorAll('#datatable .row-checkbox:checked');
                if (checkedBoxes.length === 0) {
                    Swal.fire({
                        title: "Peringatan",
                        text: "Pilih minimal satu item untuk diubah statusnya",
                        icon: "warning"
                    });
                    return;
                }

                const selectedIds = Array.from(checkedBoxes).map(cb => cb.value);

                Swal.fire({
                    title: `${actionText.charAt(0).toUpperCase() + actionText.slice(1)} Status`,
                    text: `Apakah Anda yakin ingin ${actionText} ${selectedIds.length} FAQ yang dipilih?`,
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonColor: status === '1' ? "#50cd89" : "#ffc700",
                    cancelButtonColor: "#7e8299",
                    confirmButtonText: `Ya, ${actionText}!`,
                    cancelButtonText: "Batal"
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: `${actionText.charAt(0).toUpperCase() + actionText.slice(1)} status...`,
                            text: 'Mohon tunggu',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: "{!! route('prt.apps.faq.status.bulk') !!}",
                            type: 'POST',
                            data: {
                                uuids: selectedIds,
                                status: status,
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(res) {
                                datatable.ajax.reload(null, false);
                                const bulkToolbar = document.querySelector('[data-kt-faq-table-toolbar="selected"]');
                                bulkToolbar.classList.add('d-none');
                                $('[data-kt-check="true"]').prop('checked', false);

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
                                    text: xhr.responseJSON?.message || `Terjadi kesalahan saat ${actionText} data`,
                                    icon: "error",
                                });
                            }
                        });
                    }
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
                    handleEvents();
                }
            };
        }();

        // On document ready
        $(document).ready(function() {
            KTDatatablesFaq.init();
        });
    </script>
@endpush
{{-- SCRIPTS::END --}}
