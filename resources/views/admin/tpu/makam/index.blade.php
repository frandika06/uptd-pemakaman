@push('styles')
    <style>
        /* Bulk actions toolbar animation */
        [data-kt-makam-table-toolbar="selected"] {
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(-10px);
        }

        [data-kt-makam-table-toolbar="selected"]:not(.d-none) {
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
        [data-kt-makam-table-toolbar="selected"] {
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

        /* Search highlight styling */
        .search-highlight {
            background-color: #fff2cc;
            padding: 1px 2px;
            border-radius: 2px;
        }
    </style>
@endpush

@extends('layouts.admin')

{{-- SEO::BEGIN --}}
@section('title', 'Data Makam')
{{-- SEO::END --}}

{{-- TOOLBAR::BEGIN --}}
@section('toolbar')
    <div class="d-flex flex-stack flex-row-fluid">
        {{-- begin::Toolbar container --}}
        <div class="d-flex flex-column flex-row-fluid">
            {{-- begin::Page title --}}
            <div class="page-title d-flex align-items-center me-3">
                <h1 class="page-heading d-flex flex-column justify-content-center text-dark fw-bold fs-lg-2x gap-2">
                    <span>Data Makam</span>
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
                <li class="breadcrumb-item text-gray-700">Manajemen TPU</li>
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                <li class="breadcrumb-item text-gray-700">Data Makam</li>
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
                    Filter: <span id="filter-text" class="ms-1">Semua TPU</span>
                </a>
                {{-- begin::Filter Menu --}}
                <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true" id="kt_menu_filter">
                    <div class="px-7 py-5">
                        <div class="fs-5 text-dark fw-bold">Filter Options</div>
                    </div>
                    <div class="separator border-gray-200"></div>
                    <div class="px-7 py-5">
                        {{-- Filter TPU --}}
                        <div class="mb-10">
                            <label class="form-label fw-semibold">TPU:</label>
                            <div>
                                <select class="form-select form-select-solid" name="filter[tpu]" id="q_tpu" data-control="select2" data-placeholder="Pilih TPU"
                                    data-allow-clear="true">
                                    <option value="Semua TPU">Semua TPU</option>
                                    @foreach ($tpus as $tpu)
                                        <option value="{{ $tpu->nama }}">{{ $tpu->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        {{-- Filter Lahan --}}
                        <div class="mb-10">
                            <label class="form-label fw-semibold">Lahan:</label>
                            <div>
                                <select class="form-select form-select-solid" name="filter[lahan]" id="q_lahan" data-control="select2" data-placeholder="Pilih Lahan"
                                    data-allow-clear="true">
                                    <option value="Semua Lahan">Semua Lahan</option>
                                    <!-- Options will be populated via AJAX -->
                                </select>
                            </div>
                        </div>
                        {{-- Filter Status --}}
                        <div class="mb-10">
                            <label class="form-label fw-semibold">Status Makam:</label>
                            <div>
                                <select class="form-select form-select-solid" name="filter[status]" id="q_status" data-control="select2" data-placeholder="Pilih Status"
                                    data-allow-clear="true">
                                    <option value="Semua Status">Semua Status</option>
                                    @foreach ($stsmakam as $sts)
                                        <option value="{{ $sts->nama }}">{{ $sts->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        {{-- Actions --}}
                        <div class="d-flex justify-content-end">
                            <button type="reset" class="btn btn-sm btn-light btn-active-light-primary me-2" data-kt-menu-dismiss="true" onclick="resetFilter()">Reset</button>
                            <button type="submit" class="btn btn-sm btn-primary" data-kt-menu-dismiss="true" onclick="applyFilter()">Apply</button>
                        </div>
                    </div>
                </div>
                {{-- end::Filter Menu --}}
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
            {{-- begin::Primary button --}}
            <a href="{{ route('tpu.makam.create') }}" class="btn btn-sm btn-primary d-flex flex-center ms-3 px-4 py-3">
                <i class="ki-outline ki-plus fs-2"></i>
                <span>Tambah Data Makam</span>
            </a>
            {{-- end::Primary button --}}
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
                    <span class="card-label fw-bold fs-3 mb-1">Data Makam</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">Menampilkan TPU: <strong id="titleType">Semua TPU</strong></span>
                </h4>
            </div>
            <div class="card-toolbar">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-outline ki-magnifier fs-1 position-absolute ms-6"></i>
                    <input type="text" id="search_input" class="form-control form-control-solid w-250px ps-14" placeholder="Cari data makam..." />
                </div>
            </div>
        </div>
        <div class="card-body py-4">
            <div class="d-flex justify-content-between align-items-center d-none bg-light-primary rounded p-3 mb-5" data-kt-makam-table-toolbar="selected">
                <div class="fw-bold text-primary">
                    <i class="ki-outline ki-check-square fs-2 me-2"></i>
                    <span data-kt-makam-table-select="selected_count"></span> item dipilih
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-light-danger me-2" data-kt-makam-table-select="delete_selected" data-bs-toggle="tooltip"
                        title="Hapus yang dipilih">
                        <i class="ki-outline ki-trash fs-6 me-1"></i>
                        Hapus
                    </button>
                    <button type="button" class="btn btn-sm btn-light" data-kt-makam-table-select="cancel_selection" data-bs-toggle="tooltip" title="Batalkan pilihan">
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
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_makam_table">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th class="w-10px pe-2">
                                <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                    <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_makam_table .form-check-input" value="1" />
                                </div>
                            </th>
                            <th width="30px">#</th>
                            <th width="25%">Lahan / TPU</th>
                            <th width="20%">Dimensi</th>
                            <th width="15%">Kapasitas</th>
                            <th width="15%">Status</th>
                            <th width="25%">Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
{{-- CONTENT::END --}}

{{-- SCRIPTS::BEGIN --}}
@push('scripts')
    <script>
        "use strict";

        var KTMakamList = function() {
            var datatable;
            var table;
            var originalData = []; // Store original data untuk client-side search

            // Initialize DataTable
            var initMakamTable = function() {
                table = document.querySelector('#kt_makam_table');
                if (!table) return;

                datatable = $(table).DataTable({
                    responsive: true,
                    searchDelay: 0, // Set to 0 karena search akan manual
                    processing: true,
                    serverSide: true, // Tetap server-side untuk filter
                    searching: false, // Disable built-in search DataTables
                    order: [
                        [4, 'desc'] // Order by kapasitas (kolom index 4) - kolom yang aman
                    ],
                    stateSave: false,
                    select: {
                        style: 'multi',
                        selector: 'td:first-child .form-check-input',
                        className: 'row-selected'
                    },
                    language: {
                        searchPlaceholder: 'Cari data makam...',
                        sSearch: '',
                        lengthMenu: '_MENU_ entries per page',
                        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data makam",
                        infoEmpty: "Menampilkan 0 sampai 0 dari 0 data makam",
                        infoFiltered: "(disaring dari _MAX_ total data makam)",
                        loadingRecords: '<div class="d-flex align-items-center"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</div>',
                        processing: '<div class="d-flex align-items-center"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</div>',
                        zeroRecords: "Tidak ditemukan data yang sesuai",
                        paginate: {
                            next: '<i class="ki-outline ki-arrow-right"></i>',
                            previous: '<i class="ki-outline ki-arrow-left"></i>',
                            first: '<i class="ki-outline ki-double-arrow-left"></i>',
                            last: '<i class="ki-outline ki-double-arrow-right"></i>'
                        },
                        emptyTable: "Tidak ada data makam"
                    },
                    ajax: {
                        url: "{{ route('tpu.makam.index') }}",
                        type: 'GET',
                        data: function(d) {
                            // Server-side filter data
                            d.filter = {
                                tpu: $('#q_tpu').val() || 'Semua TPU',
                                lahan: $('#q_lahan').val() || 'Semua Lahan',
                                status: $('#q_status').val() || 'Semua Status'
                            };
                            // Tidak kirim search query ke server
                            d.search.value = '';
                        },
                        beforeSend: function() {
                            $('#datatable_processing').show();
                        },
                        complete: function() {
                            $('#datatable_processing').hide();
                        },
                        error: function(xhr) {
                            $('#datatable_processing').hide();
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Gagal memuat data. Silakan refresh halaman.',
                                confirmButtonText: 'OK'
                            });
                        },
                        dataSrc: function(json) {
                            // Simpan data original untuk client-side search
                            originalData = json.data || [];
                            return json.data;
                        }
                    },
                    columns: [{
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
                            searchable: false
                        },
                        {
                            data: 'lahan_info',
                            name: 'lahan_info',
                            orderable: true, // Enable ordering dengan orderColumn di controller
                            searchable: false
                        },
                        {
                            data: 'dimensi',
                            name: 'panjang_m',
                            orderable: true,
                            searchable: false
                        },
                        {
                            data: 'kapasitas',
                            name: 'kapasitas',
                            orderable: true,
                            searchable: false
                        },
                        {
                            data: 'status_makam',
                            name: 'status_makam',
                            orderable: true,
                            searchable: false
                        },
                        {
                            data: 'keterangan',
                            name: 'keterangan',
                            orderable: true,
                            searchable: false
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        }
                    ],
                    columnDefs: [{
                        className: "text-center",
                        targets: [0, 1, 3, 4, 7] // Checkbox, Index, Dimensi, Kapasitas, Aksi
                    }],
                    drawCallback: function(settings) {
                        $('#datatable_processing').hide();
                        if (typeof KTApp !== 'undefined' && KTApp.initBootstrapTooltips) {
                            KTApp.initBootstrapTooltips();
                        }
                        handleBulkActions();
                    },
                    initComplete: function(settings, json) {
                        $('#datatable_processing').hide();
                        handleBulkActions();
                    }
                });

                exportButtons();
            }

            // Export buttons
            var exportButtons = function() {
                const documentTitle = 'Data Makam';

                var buttons = new $.fn.dataTable.Buttons(datatable, {
                    buttons: [{
                            extend: 'copyHtml5',
                            title: function() {
                                const currentFilter = $('#q_tpu').val() || 'Semua TPU';
                                return documentTitle + ' - ' + currentFilter;
                            },
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6] // Index, Lahan/TPU, Dimensi, Kapasitas, Status, Keterangan
                            }
                        },
                        {
                            extend: 'excelHtml5',
                            title: function() {
                                const currentFilter = $('#q_tpu').val() || 'Semua TPU';
                                return documentTitle + ' - ' + currentFilter;
                            },
                            filename: function() {
                                const date = new Date().toISOString().slice(0, 10);
                                const filter = ($('#q_tpu').val() || 'Semua TPU').toLowerCase().replace(/\s+/g, '-');
                                return `data-makam-${filter}-${date}`;
                            },
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6] // Index, Lahan/TPU, Dimensi, Kapasitas, Status, Keterangan
                            }
                        },
                        {
                            extend: 'csvHtml5',
                            title: function() {
                                const currentFilter = $('#q_tpu').val() || 'Semua TPU';
                                return documentTitle + ' - ' + currentFilter;
                            },
                            filename: function() {
                                const date = new Date().toISOString().slice(0, 10);
                                const filter = ($('#q_tpu').val() || 'Semua TPU').toLowerCase().replace(/\s+/g, '-');
                                return `data-makam-${filter}-${date}`;
                            },
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6] // Index, Lahan/TPU, Dimensi, Kapasitas, Status, Keterangan
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            title: function() {
                                const currentFilter = $('#q_tpu').val() || 'Semua TPU';
                                return documentTitle + ' - ' + currentFilter;
                            },
                            filename: function() {
                                const date = new Date().toISOString().slice(0, 10);
                                const filter = ($('#q_tpu').val() || 'Semua TPU').toLowerCase().replace(/\s+/g, '-');
                                return `data-makam-${filter}-${date}`;
                            },
                            orientation: 'landscape',
                            pageSize: 'A4',
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6] // Index, Lahan/TPU, Dimensi, Kapasitas, Status, Keterangan
                            },
                            customize: function(doc) {
                                doc.content[1].table.widths = ['10%', '25%', '20%', '15%', '15%', '25%'];
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
                            Swal.fire({
                                icon: 'error',
                                title: 'Export Gagal!',
                                text: 'Terjadi kesalahan saat melakukan export',
                            });
                        }
                    });
                });
            }

            // Client-side search functionality
            var initSearch = function() {
                const searchInput = document.querySelector('#search_input');
                let searchTimeout;

                searchInput.addEventListener('keyup', function(e) {
                    const searchTerm = e.target.value.toLowerCase().trim();

                    // Clear previous timeout
                    clearTimeout(searchTimeout);

                    // Debounce search untuk performance
                    searchTimeout = setTimeout(() => {
                        performClientSideSearch(searchTerm);
                    }, 300);
                });

                // Reset search when input is cleared
                searchInput.addEventListener('input', function(e) {
                    if (e.target.value === '') {
                        clearTimeout(searchTimeout);
                        performClientSideSearch('');
                    }
                });
            }

            // Perform client-side search
            var performClientSideSearch = function(searchTerm) {
                if (!searchTerm) {
                    // Show all rows if no search term
                    datatable.rows().every(function() {
                        $(this.node()).show();
                    });
                    updateTableInfo();
                    return;
                }

                let visibleCount = 0;

                datatable.rows().every(function() {
                    const row = this;
                    const rowData = row.data();
                    const rowNode = $(row.node());

                    // Fields to search in (tanpa makam_info)
                    const searchableText = [
                        stripHtml(rowData.lahan_info || ''),
                        stripHtml(rowData.dimensi || ''),
                        stripHtml(rowData.kapasitas || ''),
                        stripHtml(rowData.status_makam || ''),
                        stripHtml(rowData.keterangan || '')
                    ].join(' ').toLowerCase();

                    if (searchableText.includes(searchTerm)) {
                        rowNode.show();
                        visibleCount++;

                        // Highlight search term
                        highlightSearchTerm(rowNode, searchTerm);
                    } else {
                        rowNode.hide();
                    }
                });

                updateTableInfo(visibleCount);
            }

            // Strip HTML tags from text
            var stripHtml = function(html) {
                const tmp = document.createElement("DIV");
                tmp.innerHTML = html;
                return tmp.textContent || tmp.innerText || "";
            }

            // Highlight search terms
            var highlightSearchTerm = function(rowNode, searchTerm) {
                if (!searchTerm) return;

                rowNode.find('td').each(function() {
                    const $td = $(this);
                    if ($td.find('input, button, select').length > 0) return; // Skip action columns

                    let html = $td.html();
                    const text = $td.text().toLowerCase();

                    if (text.includes(searchTerm)) {
                        // Remove previous highlights
                        html = html.replace(/<mark class="search-highlight">(.*?)<\/mark>/gi, '$1');

                        // Add new highlights (case insensitive)
                        const regex = new RegExp(`(${escapeRegExp(searchTerm)})`, 'gi');
                        html = html.replace(regex, '<mark class="search-highlight">$1</mark>');

                        $td.html(html);
                    }
                });
            }

            // Escape regex special characters
            var escapeRegExp = function(string) {
                return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }

            // Update table info
            var updateTableInfo = function(visibleCount) {
                const info = datatable.page.info();
                const $info = $('.dataTables_info');

                if (typeof visibleCount !== 'undefined') {
                    $info.html(`Menampilkan ${visibleCount} dari ${info.recordsTotal} data makam (difilter)`);
                } else {
                    $info.html(`Menampilkan ${info.start + 1} sampai ${info.end} dari ${info.recordsTotal} data makam`);
                }
            }

            // Filter functionality - SERVER SIDE
            var handleFilter = function() {
                // Inisialisasi Select2 untuk semua dropdown filter
                $('#q_tpu, #q_lahan, #q_status').select2({
                    minimumResultsForSearch: Infinity
                });

                // Fungsi untuk reload DataTables
                var reloadDataTable = function() {
                    $('#datatable_processing').show();
                    $('#kt_makam_table tbody').empty();

                    // Clear search input ketika filter berubah
                    document.querySelector('#search_input').value = '';

                    datatable.ajax.reload(function(json) {
                        $('#datatable_processing').hide();
                        // Reset originalData dengan data baru
                        originalData = json.data || [];
                    }, false);
                };

                // Fungsi untuk memperbarui label filter
                window.applyFilter = function() {
                    var selectedTpu = document.getElementById('q_tpu').value;
                    document.getElementById('filter-text').textContent = selectedTpu;
                    document.getElementById('titleType').textContent = selectedTpu;
                    if (typeof KTMenu !== 'undefined') {
                        KTMenu.dismissMenu(document.querySelector('#kt_menu_filter'));
                    }
                };

                // Fungsi untuk reset filter
                window.resetFilter = function() {
                    // Reset dropdown ke nilai default
                    $('#q_tpu').val('Semua TPU').trigger('change.select2');
                    $('#q_lahan').empty().append('<option value="Semua Lahan">Semua Lahan</option>').val('Semua Lahan').trigger('change.select2');
                    $('#q_status').val('Semua Status').trigger('change.select2');
                    document.getElementById('filter-text').textContent = 'Semua TPU';
                    document.getElementById('titleType').textContent = 'Semua TPU';
                    reloadDataTable();
                    if (typeof KTMenu !== 'undefined') {
                        KTMenu.dismissMenu(document.querySelector('#kt_menu_filter'));
                    }
                };

                // Event listener untuk dropdown TPU
                $('#q_tpu').on('change', function() {
                    var q_tpu = $(this).val();
                    reloadDataTable();
                    // Populate lahan options based on TPU
                    if (q_tpu !== 'Semua TPU') {
                        $.ajax({
                            url: "{{ route('tpu.makam.lahan-by-tpu') }}",
                            type: 'GET',
                            data: {
                                tpu: q_tpu
                            },
                            success: function(res) {
                                let lahanSelect = $('#q_lahan');
                                lahanSelect.empty().append('<option value="Semua Lahan">Semua Lahan</option>');
                                if (res.status && res.data) {
                                    res.data.forEach(lahan => {
                                        lahanSelect.append(`<option value="${lahan.kode_lahan}">${lahan.kode_lahan}</option>`);
                                    });
                                }
                                lahanSelect.val('Semua Lahan').trigger('change.select2');
                                reloadDataTable();
                            },
                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: 'Gagal memuat data lahan.',
                                    confirmButtonText: 'OK'
                                });
                            }
                        });
                    } else {
                        $('#q_lahan').empty().append('<option value="Semua Lahan">Semua Lahan</option>').val('Semua Lahan').trigger('change.select2');
                        reloadDataTable();
                    }
                });

                // Event listener untuk dropdown Lahan
                $('#q_lahan').on('change', function() {
                    reloadDataTable();
                });

                // Event listener untuk dropdown Status
                $('#q_status').on('change', function() {
                    reloadDataTable();
                });
            }

            // Bulk actions
            var handleBulkActions = function() {
                const checkboxes = document.querySelectorAll('#kt_makam_table .row-checkbox');
                const bulkToolbar = document.querySelector('[data-kt-makam-table-toolbar="selected"]');
                const countElement = document.querySelector('[data-kt-makam-table-select="selected_count"]');
                let checkedCount = 0;
                checkboxes.forEach(checkbox => {
                    if (checkbox.checked) checkedCount++;
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

            // Event handlers
            var handleEvents = function() {
                $(document).on('change', '.row-checkbox', function() {
                    handleBulkActions();
                });
                $(document).on('change', '[data-kt-check="true"]', function() {
                    const isChecked = $(this).is(':checked');
                    $('.row-checkbox').prop('checked', isChecked);
                    handleBulkActions();
                });
                $(document).on('click', '[data-kt-makam-table-select="cancel_selection"]', function() {
                    $('.row-checkbox').prop('checked', false);
                    $('[data-kt-check="true"]').prop('checked', false);
                    const bulkToolbar = document.querySelector('[data-kt-makam-table-toolbar="selected"]');
                    bulkToolbar.classList.add('d-none');
                });
                $(document).on('click', '[data-kt-makam-table-select="delete_selected"]', function() {
                    const checkedBoxes = document.querySelectorAll('#kt_makam_table .row-checkbox:checked');
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
                        text: `Apakah Anda yakin ingin menghapus ${selectedIds.length} data makam yang dipilih?`,
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
                                url: "{{ route('tpu.makam.destroy.bulk') }}",
                                type: 'POST',
                                data: {
                                    uuids: selectedIds,
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function(res) {
                                    datatable.ajax.reload(null, false);
                                    const bulkToolbar = document.querySelector('[data-kt-makam-table-toolbar="selected"]');
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
                                        text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus data',
                                        icon: "error",
                                    });
                                }
                            });
                        }
                    });
                });
                $(document).on('click', ".btn-delete", function() {
                    let uuid = $(this).attr('data-kt-delete-id');
                    Swal.fire({
                        title: "Hapus Data",
                        text: `Apakah Anda yakin ingin menghapus data makam?`,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#f1416c",
                        cancelButtonColor: "#7e8299",
                        confirmButtonText: "Ya, hapus!",
                        cancelButtonText: "Batal"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('tpu.makam.destroy') }}",
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
            }

            return {
                init: function() {
                    initMakamTable();
                    initSearch(); // CLIENT-SIDE search
                    handleFilter(); // SERVER-SIDE filter
                    handleEvents(); // Event handlers
                }
            }
        }();

        $(document).ready(function() {
            KTMakamList.init();
        });
    </script>
@endpush
{{-- SCRIPTS::END --}}
