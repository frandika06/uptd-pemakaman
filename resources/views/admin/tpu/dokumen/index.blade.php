@push('styles')
    <style>
        /* Bulk actions toolbar animation */
        [data-kt-dokumen-table-toolbar="selected"] {
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(-10px);
        }

        [data-kt-dokumen-table-toolbar="selected"]:not(.d-none) {
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
        [data-kt-dokumen-table-toolbar="selected"] {
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
@section('title', 'Data Pendukung ' . $nama_modul)
{{-- SEO::END --}}

{{-- TOOLBAR::BEGIN --}}
@section('toolbar')
    <div class="d-flex flex-stack flex-row-fluid">
        {{-- begin::Toolbar container --}}
        <div class="d-flex flex-column flex-row-fluid">
            {{-- begin::Page title --}}
            <div class="page-title d-flex align-items-center me-3">
                <h1 class="page-heading d-flex flex-column justify-content-center text-dark fw-bold fs-lg-2x gap-2">
                    <span>Data Pendukung {{ $nama_modul }}</span>
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
                <li class="breadcrumb-item text-gray-700">Data Pendukung {{ $nama_modul }}</li>
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
            <div id="kt_datatable_example_export_menu" class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-200px py-4" data-kt-menu="true">
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
            <div id="kt_datatable_buttons" class="d-none"></div>
            {{-- end::Export button --}}
            @if (auth()->user()->id)
                {{-- begin::Primary button --}}
                <a href="{{ route('tpu.dokumen.create', $nama_modul) }}" class="btn btn-sm btn-primary d-flex flex-center ms-3 px-4 py-3">
                    <i class="ki-outline ki-plus fs-2"></i>
                    <span>Tambah Dokumen {{ $nama_modul }}</span>
                </a>
                {{-- end::Primary button --}}
            @endif
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
                <h4 class="card-label fw-bold fs-3 mb-1">Data Pendukung {{ $nama_modul }}</h4>
                <span class="text-muted mt-1 fw-semibold fs-7">
                    Menampilkan dokumen untuk {{ $nama_modul }}
                </span>
            </div>
            <div class="card-toolbar">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-outline ki-magnifier fs-1 position-absolute ms-6"></i>
                    <input type="text" id="search_input" class="form-control form-control-solid w-250px ps-14" placeholder="Cari dokumen {{ $nama_modul }}..." />
                </div>
            </div>
        </div>
        <div class="card-body py-4">
            <div class="d-flex justify-content-between align-items-center d-none bg-light-primary rounded p-3 mb-5" data-kt-dokumen-table-toolbar="selected">
                <div class="fw-bold text-primary">
                    <i class="ki-outline ki-check-square fs-2 me-2"></i>
                    <span data-kt-dokumen-table-select="selected_count"></span> item dipilih
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if (auth()->user()->id !== 'Petugas TPU')
                        <button type="button" class="btn btn-sm btn-light-danger me-2" data-kt-dokumen-table-select="delete_selected" data-bs-toggle="tooltip" title="Hapus yang dipilih">
                            <i class="ki-outline ki-trash fs-6 me-1"></i>
                            Hapus
                        </button>
                    @endif
                    <button type="button" class="btn btn-sm btn-light" data-kt-dokumen-table-select="cancel_selection" data-bs-toggle="tooltip" title="Batalkan pilihan">
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
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_dokumen_table">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th class="w-10px pe-2">
                                <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                    <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_dokumen_table .form-check-input" value="1" />
                                </div>
                            </th>
                            <th width="30px">#</th>
                            <th class="min-w-200px">Nama File</th>
                            <th class="min-w-125px">Kategori</th>
                            <th class="min-w-150px">{{ $nama_modul === 'TPU' ? 'TPU' : ($nama_modul === 'Lahan' ? 'Lahan (TPU)' : 'Sarpras (Lahan)') }}</th>
                            <th class="min-w-125px">Deskripsi</th>
                            <th class="text-end">Aksi</th>
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

        var KTDokumenList = function() {
            var datatable;
            var table;
            var originalData = []; // Store original data untuk client-side search

            // Initialize DataTable
            var initDokumenTable = function() {
                table = document.querySelector('#kt_dokumen_table');
                if (!table) return;

                datatable = $(table).DataTable({
                    responsive: true,
                    searchDelay: 0, // Set to 0 karena search akan manual
                    processing: true,
                    serverSide: true,
                    searching: false, // Disable built-in search DataTables
                    order: [
                        [2, 'desc'] // Order by nama_file
                    ],
                    stateSave: false,
                    select: {
                        style: 'multi',
                        selector: 'td:first-child .form-check-input',
                        className: 'row-selected'
                    },
                    language: {
                        searchPlaceholder: 'Cari dokumen {{ $nama_modul }}...',
                        sSearch: '',
                        lengthMenu: '_MENU_ entries per page',
                        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ dokumen",
                        infoEmpty: "Menampilkan 0 sampai 0 dari 0 dokumen",
                        infoFiltered: "(disaring dari _MAX_ total dokumen)",
                        loadingRecords: '<div class="d-flex align-items-center"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</div>',
                        processing: '<div class="d-flex align-items-center"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</div>',
                        zeroRecords: "Tidak ditemukan dokumen yang sesuai",
                        paginate: {
                            next: '<i class="ki-outline ki-arrow-right"></i>',
                            previous: '<i class="ki-outline ki-arrow-left"></i>',
                            first: '<i class="ki-outline ki-double-arrow-left"></i>',
                            last: '<i class="ki-outline ki-double-arrow-right"></i>'
                        },
                        emptyTable: "Tidak ada dokumen"
                    },
                    ajax: {
                        url: "{{ route('tpu.dokumen.index', $nama_modul) }}",
                        type: 'GET',
                        data: function(d) {
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
                    columns: [
                        {
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
                            data: 'nama_file',
                            name: 'nama_file',
                            orderable: true,
                            searchable: false
                        },
                        {
                            data: 'kategori',
                            name: 'kategori',
                            orderable: true,
                            searchable: false
                        },
                        {
                            data: 'modul_data',
                            name: 'modul_data',
                            orderable: true,
                            searchable: false
                        },
                        {
                            data: 'deskripsi',
                            name: 'deskripsi',
                            orderable: true,
                            searchable: false
                        },
                        {
                            data: 'actions',
                            name: 'actions',
                            orderable: false,
                            searchable: false
                        }
                    ],
                    columnDefs: [{
                        className: "text-center",
                        targets: [0, 1, 6] // Checkbox, Index, Aksi
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
                const documentTitle = 'Data Pendukung {{ $nama_modul }}';

                var buttons = new $.fn.dataTable.Buttons(datatable, {
                    buttons: [{
                            extend: 'copyHtml5',
                            title: documentTitle,
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5] // Index, Nama File, Kategori, Modul Data, Deskripsi
                            }
                        },
                        {
                            extend: 'excelHtml5',
                            title: documentTitle,
                            filename: function() {
                                const date = new Date().toISOString().slice(0, 10);
                                return `data-dokumen-${date}`;
                            },
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5]
                            }
                        },
                        {
                            extend: 'csvHtml5',
                            title: documentTitle,
                            filename: function() {
                                const date = new Date().toISOString().slice(0, 10);
                                return `data-dokumen-${date}`;
                            },
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5]
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            title: documentTitle,
                            filename: function() {
                                const date = new Date().toISOString().slice(0, 10);
                                return `data-dokumen-${date}`;
                            },
                            orientation: 'landscape',
                            pageSize: 'A4',
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5]
                            },
                            customize: function(doc) {
                                doc.content[1].table.widths = ['10%', '25%', '20%', '20%', '25%'];
                                doc.styles.tableHeader.fontSize = 9;
                                doc.styles.tableBodyOdd.fontSize = 8;
                                doc.styles.tableBodyEven.fontSize = 8;
                                doc.defaultStyle.fontSize = 8;
                            }
                        }
                    ]
                }).container().appendTo($('#kt_datatable_buttons'));

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

                    // Fields to search in
                    const searchableText = [
                        stripHtml(rowData.nama_file || ''),
                        stripHtml(rowData.kategori || ''),
                        stripHtml(rowData.modul_data || ''),
                        stripHtml(rowData.deskripsi || '')
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
                    $info.html(`Menampilkan ${visibleCount} dari ${info.recordsTotal} dokumen (difilter)`);
                } else {
                    $info.html(`Menampilkan ${info.start + 1} sampai ${info.end} dari ${info.recordsTotal} dokumen`);
                }
            }

            // Bulk actions
            var handleBulkActions = function() {
                const checkboxes = document.querySelectorAll('#kt_dokumen_table .row-checkbox');
                const bulkToolbar = document.querySelector('[data-kt-dokumen-table-toolbar="selected"]');
                const countElement = document.querySelector('[data-kt-dokumen-table-select="selected_count"]');
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
                $(document).on('click', '[data-kt-dokumen-table-select="cancel_selection"]', function() {
                    $('.row-checkbox').prop('checked', false);
                    $('[data-kt-check="true"]').prop('checked', false);
                    const bulkToolbar = document.querySelector('[data-kt-dokumen-table-toolbar="selected"]');
                    bulkToolbar.classList.add('d-none');
                });
                $(document).on('click', '[data-kt-dokumen-table-select="delete_selected"]', function() {
                    const checkedBoxes = document.querySelectorAll('#kt_dokumen_table .row-checkbox:checked');
                    if (checkedBoxes.length === 0) {
                        Swal.fire({
                            title: "Peringatan",
                            text: "Pilih minimal satu dokumen untuk dihapus",
                            icon: "warning"
                        });
                        return;
                    }
                    const selectedIds = Array.from(checkedBoxes).map(cb => cb.value);
                    Swal.fire({
                        title: "Hapus Dokumen Terpilih",
                        text: `Apakah Anda yakin ingin menghapus ${selectedIds.length} dokumen yang dipilih?`,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#f1416c",
                        cancelButtonColor: "#7e8299",
                        confirmButtonText: "Ya, hapus semua!",
                        cancelButtonText: "Batal"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Menghapus dokumen...',
                                text: 'Mohon tunggu',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            $.ajax({
                                url: "{{ route('tpu.dokumen.destroy.bulk') }}",
                                type: 'POST',
                                data: {
                                    uuids: selectedIds,
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function(res) {
                                    datatable.ajax.reload(null, false);
                                    const bulkToolbar = document.querySelector('[data-kt-dokumen-table-toolbar="selected"]');
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
                                        text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus dokumen',
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
                        title: "Hapus Dokumen",
                        text: `Apakah Anda yakin ingin menghapus dokumen ini?`,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#f1416c",
                        cancelButtonColor: "#7e8299",
                        confirmButtonText: "Ya, hapus!",
                        cancelButtonText: "Batal"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('tpu.dokumen.destroy') }}",
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
                    initDokumenTable();
                    initSearch(); // CLIENT-SIDE search
                    handleEvents(); // Event handlers
                }
            }
        }();

        $(document).ready(function() {
            KTDokumenList.init();
        });
    </script>
@endpush
{{-- SCRIPTS::END --}}
