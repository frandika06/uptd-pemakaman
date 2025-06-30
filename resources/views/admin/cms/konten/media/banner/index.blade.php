@push('styles')
    <style>
        /* Bulk actions toolbar animation */
        [data-kt-banner-table-toolbar="selected"] {
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(-10px);
        }

        [data-kt-banner-table-toolbar="selected"]:not(.d-none) {
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
        [data-kt-banner-table-toolbar="selected"] {
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

        /* Banner Counter badge styling */
        .banner-counter {
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
@section('title', 'Banner Management')
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
                    <span>Banner Management</span>
                </h1>
                {{-- end::Title --}}
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
                <li class="breadcrumb-item text-gray-700">Konten</li>
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                <li class="breadcrumb-item text-gray-700">Konten Media</li>
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                <li class="breadcrumb-item text-gray-700">Banner</li>
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
                    Filter: <span id="filter-text" class="ms-1">{{ isset($kategori) ? $kategori : 'Semua Data' }}</span>
                </a>
                <div class="menu menu-sub menu-sub-dropdown w-250px w-md-300px" data-kt-menu="true" id="kt_menu_filter">
                    <div class="px-7 py-5">
                        <div class="fs-5 text-dark fw-bold">Filter Options</div>
                    </div>
                    <div class="separator border-gray-200"></div>
                    <div class="px-7 py-5">
                        <div class="mb-10">
                            <label class="form-label fw-semibold">Tipe Kategori Banner:</label>
                            <div>
                                <select class="form-select form-select-solid" name="q_kategori_banner" id="q_kategori_banner" data-control="select2" data-placeholder="Pilih Kategori"
                                    data-allow-clear="true">
                                    <option @if (!isset($kategori) || $kategori == 'Semua Data') selected @endif value="Semua Data">Semua Data</option>
                                    @if (isset($kategoriList) && \count($kategoriList) > 0)
                                        @foreach ($kategoriList as $item)
                                            <option @if (isset($kategori) && $kategori == $item->nama) selected @endif value="{{ $item->nama }}">{{ $item->nama }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="reset" class="btn btn-sm btn-light btn-active-light-primary me-2" data-kt-menu-dismiss="true" onclick="resetFilter()">Reset</button>
                            <button type="submit" class="btn btn-sm btn-primary" data-kt-menu-dismiss="true" onclick="applyFilter()">Apply</button>
                        </div>
                    </div>
                </div>
            </div>
            {{-- end::Filter dropdown --}}
            {{-- begin::Export button --}}
            <button type="button" class="btn btn-sm btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                <i class="ki-outline ki-exit-down fs-2"></i>
                Export Report
            </button>
            <div id="kt_datatable_example_export_menu"
                class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-200px py-4" data-kt-menu="true">
                <div class="menu-item px-3">
                    <a href="#" class="menu-link px-3" data-kt-export="copy">
                        Copy to clipboard
                    </a>
                </div>
                <div class="menu-item px-3">
                    <a href="#" class="menu-link px-3" data-kt-export="excel">
                        Export as Excel
                    </a>
                </div>
                <div class="menu-item px-3">
                    <a href="#" class="menu-link px-3" data-kt-export="csv">
                        Export as CSV
                    </a>
                </div>
                <div class="menu-item px-3">
                    <a href="#" class="menu-link px-3" data-kt-export="pdf">
                        Export as PDF
                    </a>
                </div>
            </div>
            <div id="kt_datatable_example_buttons" class="d-none"></div>
            <a href="{{ route('prt.apps.banner.create') }}" class="btn btn-sm btn-primary d-flex flex-center ms-3 px-4 py-3">
                <i class="ki-outline ki-plus fs-2"></i>
                <span>Tambah Banner</span>
            </a>
        </div>
        {{-- end::Actions --}}
    </div>
@endsection
{{-- TOOLBAR::END --}}

{{-- CONTENT::BEGIN --}}
@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h4 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold fs-3 mb-1">Data Banner</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">Menampilkan kategori: <strong id="titleKategori">{{ isset($kategori) ? $kategori : 'Semua Data' }}</strong></span>
                </h4>
            </div>
            <div class="card-toolbar">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-outline ki-magnifier fs-1 position-absolute ms-6"></i>
                    <input type="text" data-kt-banner-table-filter="search" class="form-control form-control-solid w-250px ps-14" placeholder="Cari Banner..." />
                </div>
            </div>
        </div>
        <div class="card-body py-4">
            <div class="d-flex justify-content-between align-items-center d-none bg-light-primary rounded p-3 mb-5" data-kt-banner-table-toolbar="selected">
                <div class="fw-bold text-primary">
                    <i class="ki-outline ki-check-square fs-2 me-2"></i>
                    <span data-kt-banner-table-select="selected_count"></span> item dipilih
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="btn-group me-2" role="group" aria-label="Status Actions">
                        <button type="button" class="btn btn-sm btn-light-success" data-kt-banner-table-select="activate_selected" data-bs-toggle="tooltip"
                            title="Aktifkan yang dipilih">
                            <i class="ki-outline ki-check fs-6 me-1"></i>
                            Aktifkan
                        </button>
                        <button type="button" class="btn btn-sm btn-light-warning" data-kt-banner-table-select="deactivate_selected" data-bs-toggle="tooltip"
                            title="Nonaktifkan yang dipilih">
                            <i class="ki-outline ki-cross fs-6 me-1"></i>
                            Nonaktifkan
                        </button>
                    </div>
                    <button type="button" class="btn btn-sm btn-light-danger me-2" data-kt-banner-table-select="delete_selected" data-bs-toggle="tooltip"
                        title="Hapus yang dipilih">
                        <i class="ki-outline ki-trash fs-6 me-1"></i>
                        Hapus
                    </button>
                    <button type="button" class="btn btn-sm btn-light" data-kt-banner-table-select="cancel_selection" data-bs-toggle="tooltip" title="Batalkan pilihan">
                        <i class="ki-outline ki-cross fs-6"></i>
                    </button>
                </div>
            </div>
            <div class="table-responsive position-relative">
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
                            <th>Judul</th>
                            <th width="10%">Author</th>
                            <th width="10%">Publisher</th>
                            <th width="10%">Tanggal</th>
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
                            <th>Judul</th>
                            <th width="10%">Author</th>
                            <th width="10%">Publisher</th>
                            <th width="10%">Tanggal</th>
                            <th width="10%">Status</th>
                            <th>Aksi</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection
{{-- CONTENT::END --}}

@push('scripts')
    <script>
        "use strict";

        var KTDatatablesBanner = function() {
            var table;
            var datatable;

            var initDatatable = function() {
                datatable = $('#datatable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    paging: true,
                    searching: true,
                    ordering: true,
                    info: true,
                    autoWidth: false,
                    stateSave: false,
                    pageLength: 10,
                    lengthMenu: [
                        [10, 25, 50, 100, -1],
                        [10, 25, 50, 100, "Semua"]
                    ],
                    order: [
                        [1, 'asc']
                    ],
                    language: {
                        searchPlaceholder: 'Cari...',
                        sSearch: '',
                        lengthMenu: '_MENU_ entri per halaman',
                        processing: '<div class="d-flex align-items-center"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</div>',
                        loadingRecords: '<div class="d-flex align-items-center"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</div>',
                        paginate: {
                            next: '<i class="ki-outline ki-arrow-right"></i>',
                            previous: '<i class="ki-outline ki-arrow-left"></i>',
                            first: '<i class="ki-outline ki-double-arrow-left"></i>',
                            last: '<i class="ki-outline ki-double-arrow-right"></i>'
                        },
                        emptyTable: "Tidak ada data Banner",
                        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                        infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                        infoFiltered: "(disaring dari total _MAX_ data)",
                        zeroRecords: "Data tidak ditemukan"
                    },
                    ajax: {
                        url: "{{ route('prt.apps.banner.index') }}",
                        type: 'GET',
                        data: function(data) {
                            data.filter = {
                                kategori: $('[name="q_kategori_banner"]').val() || 'Hero'
                            };
                        },
                        dataSrc: 'data',
                        error: function(xhr, error, thrown) {
                            console.error('DataTable AJAX Error:', xhr.responseText);
                            $('#datatable_processing').hide();
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: 'Tidak dapat memuat data banner. Silakan refresh halaman.',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    columns: [{
                            data: null,
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                return `<div class="form-check form-check-sm form-check-custom form-check-solid">
                        <input class="form-check-input row-checkbox" type="checkbox" value="${row.uuid}" />
                    </div>`;
                            }
                        },
                        {
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            title: '#',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'judul',
                            name: 'judul',
                            title: 'Judul'
                        },
                        {
                            data: 'penulis',
                            name: 'penulis',
                            title: 'Penulis'
                        },
                        {
                            data: 'publisher',
                            name: 'publisher',
                            title: 'Publisher'
                        },
                        {
                            data: 'tanggal',
                            name: 'tanggal',
                            title: 'Tanggal'
                        },
                        {
                            data: 'status',
                            name: 'status',
                            orderable: false,
                            searchable: false,
                            title: 'Status',
                            render: function(data, type, row) {
                                if (type === 'export') {
                                    return row.status == 1 ? 'Aktif' : 'Tidak Aktif';
                                }
                                return data;
                            }
                        },
                        {
                            data: 'aksi',
                            name: 'aksi',
                            orderable: false,
                            searchable: false,
                            title: 'Aksi'
                        }
                    ],
                    columnDefs: [{
                        className: "text-center",
                        targets: [0, 1, 3, 4, 5, 6, 7]
                    }],
                    drawCallback: function() {
                        $('#datatable_processing').hide();
                        if (typeof KTApp !== 'undefined' && KTApp.initBootstrapTooltips) {
                            KTApp.initBootstrapTooltips();
                        }
                        handleBulkActions();
                    },
                    initComplete: function() {
                        $('#datatable_processing').hide();
                        handleBulkActions();
                    }
                });
            };


            var exportButtons = function() {
                const documentTitle = 'Data Banner';

                var buttons = new $.fn.dataTable.Buttons(datatable, {
                    buttons: [{
                            extend: 'copyHtml5',
                            title: function() {
                                const currentFilter = $('[name="q_kategori_banner"]').val() || 'Semua Data';
                                return documentTitle + ' - ' + currentFilter;
                            },
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6]
                            }
                        },
                        {
                            extend: 'excelHtml5',
                            title: function() {
                                const currentFilter = $('[name="q_kategori_banner"]').val() || 'Semua Data';
                                return documentTitle + ' - ' + currentFilter;
                            },
                            filename: function() {
                                const date = new Date().toISOString().slice(0, 10);
                                const filter = ($('[name="q_kategori_banner"]').val() || 'Semua Data').toLowerCase().replace(/\s+/g, '-');
                                return `banner-data-${filter}-${date}`;
                            },
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6]
                            }
                        },
                        {
                            extend: 'csvHtml5',
                            title: function() {
                                const currentFilter = $('[name="q_kategori_banner"]').val() || 'Semua Data';
                                return documentTitle + ' - ' + currentFilter;
                            },
                            filename: function() {
                                const date = new Date().toISOString().slice(0, 10);
                                const filter = ($('[name="q_kategori_banner"]').val() || 'Semua Data').toLowerCase().replace(/\s+/g, '-');
                                return `banner-data-${filter}-${date}`;
                            },
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6]
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            title: function() {
                                const currentFilter = $('[name="q_kategori_banner"]').val() || 'Semua Data';
                                return documentTitle + ' - ' + currentFilter;
                            },
                            filename: function() {
                                const date = new Date().toISOString().slice(0, 10);
                                const filter = ($('[name="q_kategori_banner"]').val() || 'Semua Data').toLowerCase().replace(/\s+/g, '-');
                                return `banner-data-${filter}-${date}`;
                            },
                            orientation: 'landscape',
                            pageSize: 'A4',
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6]
                            },
                            customize: function(doc) {
                                doc.content[1].table.widths = ['8%', '35%', '12%', '12%', '12%', '12%'];
                                doc.styles.tableHeader.fontSize = 9;
                                doc.styles.tableBodyOdd.fontSize = 8;
                                doc.styles.tableBodyEven.fontSize = 8;
                                doc.defaultStyle.fontSize = 8;
                            }
                        }
                    ]
                }).container().appendTo($('#kt_datatable_example_buttons'));

                const exportButtons = document.querySelectorAll('#kt_datatable_example_export_menu [data-kt-export]');
                exportButtons.forEach(exportButton => {
                    exportButton.addEventListener('click', e => {
                        e.preventDefault();
                        const exportValue = e.target.getAttribute('data-kt-export');
                        const target = document.querySelector('.dt-buttons .buttons-' + exportValue);

                        if (target) {
                            Swal.fire({
                                title: `Exporting ${exportValue.toUpperCase()}...`,
                                text: 'Mohon tunggu',
                                allowOutsideClick: false,
                                timer: 1500,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            setTimeout(() => {
                                target.click();
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
            };

            var handleSearchDatatable = function() {
                const filterSearch = document.querySelector('[data-kt-banner-table-filter="search"]');
                if (filterSearch) {
                    filterSearch.addEventListener('keyup', function(e) {
                        datatable.ajax.reload();
                    });
                }
            };

            var handleBulkActions = function() {
                const checkboxes = document.querySelectorAll('#datatable .row-checkbox');
                const bulkToolbar = document.querySelector('[data-kt-banner-table-toolbar="selected"]');
                const countElement = document.querySelector('[data-kt-banner-table-select="selected_count"]');

                let checkedCount = 0;
                checkboxes.forEach(checkbox => {
                    if (checkbox.checked) checkedCount++;
                });

                if (checkedCount > 0) {
                    countElement.textContent = checkedCount;
                    bulkToolbar.classList.remove('d-none');
                    if (typeof KTApp !== 'undefined' && KTApp.initBootstrapTooltips) {
                        KTApp.initBootstrapTooltips();
                    }
                } else {
                    bulkToolbar.classList.add('d-none');
                }
            };

            var handleFilter = function() {
                $('#q_kategori_banner').select2();

                window.applyFilter = function() {
                    var selectedKategori = document.getElementById('q_kategori_banner').value;
                    document.getElementById('filter-text').textContent = selectedKategori;
                    $('#titleKategori').html(selectedKategori);

                    $('#datatable_processing').show();
                    datatable.ajax.reload(function(json) {
                        $('#datatable_processing').hide();
                    }, false);
                };

                window.resetFilter = function() {
                    document.getElementById('q_kategori_banner').value = 'Semua Data';
                    document.getElementById('filter-text').textContent = 'Semua Data';
                    $('#titleKategori').html('Semua Data');

                    $('#datatable_processing').show();
                    datatable.ajax.reload(function(json) {
                        $('#datatable_processing').hide();
                    }, false);
                };

                $('[name="q_kategori_banner"]').change(function() {
                    var q_kategori_banner = $(this).val();
                    $('#titleKategori').html(q_kategori_banner);

                    $('#datatable_processing').show();
                    datatable.ajax.reload(function(json) {
                        $('#datatable_processing').hide();
                    }, false);
                });
            };

            var handleEvents = function() {
                $(document).on('change', '.row-checkbox', function() {
                    handleBulkActions();
                });

                $(document).on('change', '[data-kt-check="true"]', function() {
                    const isChecked = $(this).is(':checked');
                    $('.row-checkbox').prop('checked', isChecked);
                    handleBulkActions();
                });

                $(document).on('click', '[data-kt-banner-table-select="cancel_selection"]', function() {
                    $('.row-checkbox').prop('checked', false);
                    $('[data-kt-check="true"]').prop('checked', false);
                    const bulkToolbar = document.querySelector('[data-kt-banner-table-toolbar="selected"]');
                    bulkToolbar.classList.add('d-none');
                });

                $(document).on('click', '[data-kt-banner-table-select="activate_selected"]', function() {
                    handleBulkStatus('1', 'mengaktifkan');
                });

                $(document).on('click', '[data-kt-banner-table-select="deactivate_selected"]', function() {
                    handleBulkStatus('0', 'menonaktifkan');
                });

                $(document).on('click', '[data-kt-banner-table-select="delete_selected"]', function() {
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
                        text: `Apakah Anda yakin ingin menghapus ${selectedIds.length} Banner yang dipilih?`,
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
                                url: "{{ route('prt.apps.banner.destroy.bulk') }}",
                                type: 'POST',
                                data: {
                                    ids: selectedIds,
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

                $(document).on('click', "[data-delete]", function() {
                    let id = $(this).attr('data-delete');
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
                                url: "{{ route('prt.apps.banner.destroy', '') }}" + '/' + id,
                                type: 'DELETE',
                                data: {
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

                $(document).on('click', "[data-status]", function() {
                    let id = $(this).attr("data-status");
                    let status = $(this).attr("data-status-value");
                    $.ajax({
                        url: "{{ route('prt.apps.banner.status') }}",
                        type: 'POST',
                        data: {
                            id: id,
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
                    text: `Apakah Anda yakin ingin ${actionText} ${selectedIds.length} Banner yang dipilih?`,
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
                            url: "{{ route('prt.apps.banner.status.bulk') }}",
                            type: 'POST',
                            data: {
                                ids: selectedIds,
                                status: status,
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(res) {
                                datatable.ajax.reload(null, false);
                                const bulkToolbar = document.querySelector('[data-kt-banner-table-toolbar="selected"]');
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
            };

            return {
                init: function() {
                    table = document.querySelector('#datatable');
                    if (!table) return;

                    initDatatable();
                    exportButtons();
                    handleSearchDatatable();
                    handleFilter();
                    handleEvents();
                }
            };
        }();

        $(document).ready(function() {
            KTDatatablesBanner.init();
        });
    </script>
@endpush
