@push('styles')
    <style>
        /* Bulk actions toolbar animation */
        [data-kt-sarpras-table-toolbar="selected"] {
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(-10px);
        }

        [data-kt-sarpras-table-toolbar="selected"]:not(.d-none) {
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
        [data-kt-sarpras-table-toolbar="selected"] {
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
    </style>
@endpush

@extends('layouts.admin')

@section('title', 'Data Sarpras')

@section('toolbar')
    <div class="d-flex flex-stack flex-row-fluid">
        <div class="d-flex flex-column flex-row-fluid">
            <div class="page-title d-flex align-items-center me-3">
                <h1 class="page-heading d-flex flex-column justify-content-center text-dark fw-bold fs-lg-2x gap-2">
                    <span>Data Sarpras</span>
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
                <li class="breadcrumb-item text-gray-700">Manajemen TPU</li>
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                <li class="breadcrumb-item text-gray-700">Data Sarpras</li>
            </ul>
        </div>
        <div class="d-flex align-self-center flex-center flex-shrink-0">
            <div class="me-3">
                <a href="#" class="btn btn-sm btn-flex btn-outline btn-color-gray-700 btn-active-color-primary bg-body h-40px fs-7 fw-bold" data-kt-menu-trigger="click"
                    data-kt-menu-placement="bottom-end">
                    <i class="ki-outline ki-filter fs-6 text-muted me-1"></i>
                    Filter: <span id="filter-text" class="ms-1">
                        @if ($hide_tpu_filter && $tpus->first())
                            {{ $tpus->first()->nama }}
                        @else
                            {{ $filter_tpu }}
                        @endif
                    </span>
                </a>
                <div class="menu menu-sub menu-sub-dropdown w-250px w-md-300px" data-kt-menu="true" id="kt_menu_filter">
                    <div class="px-7 py-5">
                        <div class="fs-5 text-dark fw-bold">Filter Options</div>
                    </div>
                    <div class="separator border-gray-200"></div>
                    <div class="px-7 py-5">
                        @if (!$hide_tpu_filter)
                            <div class="mb-10">
                                <label class="form-label fw-semibold">TPU:</label>
                                <div>
                                    <select class="form-select form-select-solid" name="q_tpu" id="q_tpu" data-control="select2" data-placeholder="Pilih TPU"
                                        data-allow-clear="true">
                                        <option value="Semua TPU" {{ $filter_tpu == 'Semua TPU' ? 'selected' : '' }}>Semua TPU</option>
                                        @foreach ($tpus as $tpu)
                                            <option value="{{ $tpu->nama }}" {{ $filter_tpu == $tpu->nama ? 'selected' : '' }}>{{ $tpu->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif
                        <div class="mb-10">
                            <label class="form-label fw-semibold">Lahan:</label>
                            <div>
                                <select class="form-select form-select-solid" name="q_lahan" id="q_lahan" data-control="select2" data-placeholder="Pilih Lahan"
                                    data-allow-clear="true">
                                    <option value="Semua Lahan" {{ $filter_lahan == 'Semua Lahan' ? 'selected' : '' }}>Semua Lahan</option>
                                    @foreach ($lahans as $lahan)
                                        <option value="{{ $lahan->kode_lahan }}" {{ $filter_lahan == $lahan->kode_lahan ? 'selected' : '' }}>{{ $lahan->kode_lahan }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-10">
                            <label class="form-label fw-semibold">Jenis Sarpras:</label>
                            <div>
                                <select class="form-select form-select-solid" name="q_jenis_sarpras" id="q_jenis_sarpras" data-control="select2" data-placeholder="Pilih Jenis Sarpras"
                                    data-allow-clear="true">
                                    <option value="Semua Jenis Sarpras" {{ $filter_jenis_sarpras == 'Semua Jenis Sarpras' ? 'selected' : '' }}>Semua Jenis Sarpras</option>
                                    @foreach ($jenis_sarpras as $jenis)
                                        <option value="{{ $jenis->nama }}" {{ $filter_jenis_sarpras == $jenis->nama ? 'selected' : '' }}>{{ $jenis->nama }}</option>
                                    @endforeach
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
            <button type="button" class="btn btn-sm btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                <i class="ki-outline ki-exit-down fs-2"></i>
                Export Report
            </button>
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
            <div id="kt_datatable_example_buttons" class="d-none"></div>
            {{-- Petugas TPU sekarang bisa akses tombol Tambah Data Sarpras --}}
            <a href="{{ route('tpu.sarpras.create') }}" class="btn btn-sm btn-primary d-flex flex-center ms-3 px-4 py-3">
                <i class="ki-outline ki-plus fs-2"></i>
                <span>Tambah Data Sarpras</span>
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h4 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold fs-3 mb-1">Data Sarpras</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">
                        Menampilkan TPU:
                        <strong id="titleType">
                            @if ($hide_tpu_filter && $tpus->first())
                                {{ $tpus->first()->nama }}
                            @else
                                {{ $filter_tpu }}
                            @endif
                        </strong>
                    </span>
                </h4>
            </div>
            <div class="card-toolbar">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-outline ki-magnifier fs-1 position-absolute ms-6"></i>
                    <input type="text" data-kt-sarpras-table-filter="search" class="form-control form-control-solid w-250px ps-14" placeholder="Cari data sarpras..." />
                </div>
            </div>
        </div>
        <div class="card-body py-4">
            {{-- Bulk actions toolbar - Petugas TPU sekarang bisa akses --}}
            <div class="d-flex justify-content-between align-items-center d-none bg-light-primary rounded p-3 mb-5" data-kt-sarpras-table-toolbar="selected">
                <div class="fw-bold text-primary">
                    <i class="ki-outline ki-check-square fs-2 me-2"></i>
                    <span data-kt-sarpras-table-select="selected_count"></span> item dipilih
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-light-danger me-2" data-kt-sarpras-table-select="delete_selected" data-bs-toggle="tooltip"
                        title="Hapus yang dipilih">
                        <i class="ki-outline ki-trash fs-6 me-1"></i>
                        Hapus
                    </button>
                    <button type="button" class="btn btn-sm btn-light" data-kt-sarpras-table-select="cancel_selection" data-bs-toggle="tooltip" title="Batalkan pilihan">
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
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_sarpras_table">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            {{-- Checkbox column - Petugas TPU sekarang bisa akses --}}
                            <th class="w-10px pe-2">
                                <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                    <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_sarpras_table .form-check-input"
                                        value="1" />
                                </div>
                            </th>
                            <th width="30px">#</th>
                            <th width="25%">Nama</th>
                            <th width="30%">Kode Lahan</th>
                            <th width="15%">Luas (m²)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="w-10px pe-2"></th>
                            <th width="30px">#</th>
                            <th width="25%">Nama</th>
                            <th width="30%">Kode Lahan</th>
                            <th width="15%">Luas (m²)</th>
                            <th>Aksi</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        "use strict";

        var KTSarprasList = function() {
            var datatable;
            var table;

            var initSarprasTable = function() {
                table = document.querySelector('#kt_sarpras_table');
                if (!table) return;

                datatable = $(table).DataTable({
                    responsive: true,
                    searchDelay: 500,
                    processing: true,
                    serverSide: false,
                    order: [
                        [1, 'asc']
                    ],
                    stateSave: false,
                    select: {
                        style: 'multi',
                        selector: 'td:first-child .form-check-input',
                        className: 'row-selected'
                    },
                    language: {
                        searchPlaceholder: 'Cari data sarpras...',
                        sSearch: '',
                        lengthMenu: '_MENU_ entries per page',
                        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data sarpras",
                        infoEmpty: "Menampilkan 0 sampai 0 dari 0 data sarpras",
                        infoFiltered: "(disaring dari _MAX_ total data sarpras)",
                        loadingRecords: '<div class="d-flex align-items-center"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</div>',
                        processing: '<div class="d-flex align-items-center"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</div>',
                        zeroRecords: "Tidak ditemukan data yang sesuai",
                        paginate: {
                            next: '<i class="ki-outline ki-arrow-right"></i>',
                            previous: '<i class="ki-outline ki-arrow-left"></i>',
                            first: '<i class="ki-outline ki-double-arrow-left"></i>',
                            last: '<i class="ki-outline ki-double-arrow-right"></i>'
                        },
                        emptyTable: "Tidak ada data sarpras"
                    },
                    ajax: {
                        url: "{{ route('tpu.sarpras.index') }}",
                        type: 'GET',
                        data: function(d) {
                            d.filter = {
                                tpu: $('[name="q_tpu"]').val() || @json($hide_tpu_filter && $tpus->first() ? $tpus->first()->nama : 'Semua TPU'),
                                lahan: $('[name="q_lahan"]').val() || 'Semua Lahan',
                                jenis_sarpras: $('[name="q_jenis_sarpras"]').val() || 'Semua Jenis Sarpras'
                            };
                        },
                        beforeSend: function() {
                            $('#datatable_processing').show();
                        },
                        complete: function() {
                            $('#datatable_processing').hide();
                        }
                    },
                    columns: [
                        {{-- Checkbox column - semua role bisa akses --}} {
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
                            data: 'nama',
                            name: 'nama'
                        },
                        {
                            data: 'kode_lahan',
                            name: 'kode_lahan'
                        },
                        {
                            data: 'luas_m2',
                            name: 'luas_m2'
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
                        targets: [0, 1, 5]
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

            var exportButtons = function() {
                const documentTitle = 'Data Sarpras';

                var buttons = new $.fn.dataTable.Buttons(datatable, {
                    buttons: [{
                            extend: 'copyHtml5',
                            title: function() {
                                const currentTpu = $('[name="q_tpu"]').val() || @json($hide_tpu_filter && $tpus->first() ? $tpus->first()->nama : 'Semua TPU');
                                return documentTitle + ' - ' + currentTpu;
                            },
                            exportOptions: {
                                columns: [1, 2, 3, 4]
                            }
                        },
                        {
                            extend: 'excelHtml5',
                            title: function() {
                                const currentTpu = $('[name="q_tpu"]').val() || @json($hide_tpu_filter && $tpus->first() ? $tpus->first()->nama : 'Semua TPU');
                                return documentTitle + ' - ' + currentTpu;
                            },
                            filename: function() {
                                const date = new Date().toISOString().slice(0, 10);
                                const tpu = ($('[name="q_tpu"]').val() || @json($hide_tpu_filter && $tpus->first() ? $tpus->first()->nama : 'Semua TPU')).toLowerCase().replace(/\s+/g, '-');
                                return `data-sarpras-${tpu}-${date}`;
                            },
                            exportOptions: {
                                columns: [1, 2, 3, 4]
                            }
                        },
                        {
                            extend: 'csvHtml5',
                            title: function() {
                                const currentTpu = $('[name="q_tpu"]').val() || @json($hide_tpu_filter && $tpus->first() ? $tpus->first()->nama : 'Semua TPU');
                                return documentTitle + ' - ' + currentTpu;
                            },
                            filename: function() {
                                const date = new Date().toISOString().slice(0, 10);
                                const tpu = ($('[name="q_tpu"]').val() || @json($hide_tpu_filter && $tpus->first() ? $tpus->first()->nama : 'Semua TPU')).toLowerCase().replace(/\s+/g, '-');
                                return `data-sarpras-${tpu}-${date}`;
                            },
                            exportOptions: {
                                columns: [1, 2, 3, 4]
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            title: function() {
                                const currentTpu = $('[name="q_tpu"]').val() || @json($hide_tpu_filter && $tpus->first() ? $tpus->first()->nama : 'Semua TPU');
                                return documentTitle + ' - ' + currentTpu;
                            },
                            filename: function() {
                                const date = new Date().toISOString().slice(0, 10);
                                const tpu = ($('[name="q_tpu"]').val() || @json($hide_tpu_filter && $tpus->first() ? $tpus->first()->nama : 'Semua TPU')).toLowerCase().replace(/\s+/g, '-');
                                return `data-sarpras-${tpu}-${date}`;
                            },
                            orientation: 'landscape',
                            pageSize: 'A4',
                            exportOptions: {
                                columns: [1, 2, 3, 4]
                            },
                            customize: function(doc) {
                                doc.content[1].table.widths = ['10%', '20%', '30%', '10%'];
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

            var handleSearchDatatable = function() {
                const filterSearch = document.querySelector('[data-kt-sarpras-table-filter="search"]');
                filterSearch.addEventListener('keyup', function(e) {
                    datatable.search(e.target.value).draw();
                });
            }

            var handleFilter = function() {
                $('#q_tpu').select2();
                $('#q_lahan').select2();
                $('#q_jenis_sarpras').select2();

                @if (!$hide_tpu_filter)
                    $('#q_tpu').on('change', function() {
                        var tpu = $(this).val();
                        $.ajax({
                            url: "{{ route('tpu.sarpras.lahans') }}",
                            type: 'GET',
                            data: {
                                tpu: tpu
                            },
                            success: function(res) {
                                if (res.status) {
                                    var lahanSelect = $('#q_lahan');
                                    lahanSelect.empty();
                                    lahanSelect.append('<option value="Semua Lahan">Semua Lahan</option>');
                                    res.data.forEach(function(lahan) {
                                        lahanSelect.append('<option value="' + lahan.kode_lahan + '">' + lahan.kode_lahan + '</option>');
                                    });
                                    lahanSelect.val('Semua Lahan').trigger('change');
                                    document.getElementById('filter-text').textContent = tpu;
                                    document.getElementById('titleType').textContent = tpu;
                                    datatable.ajax.reload(function(json) {
                                        $('#datatable_processing').hide();
                                    }, false);
                                }
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Gagal memuat data lahan.',
                                });
                            }
                        });
                    });
                @endif

                window.applyFilter = function() {
                    var selectedTpu = document.getElementById('q_tpu') ? document.getElementById('q_tpu').value : @json($hide_tpu_filter && $tpus->first() ? $tpus->first()->nama : 'Semua TPU');
                    document.getElementById('filter-text').textContent = selectedTpu;
                    document.getElementById('titleType').textContent = selectedTpu;
                    if (typeof KTMenu !== 'undefined') {
                        KTMenu.dismissMenu(document.querySelector('#kt_menu_filter'));
                    }
                    $('#datatable_processing').show();
                    datatable.ajax.reload(function(json) {
                        $('#datatable_processing').hide();
                    }, false);
                }

                window.resetFilter = function() {
                    if (document.getElementById('q_tpu')) {
                        document.getElementById('q_tpu').value = 'Semua TPU';
                        $('#q_tpu').trigger('change');
                    }
                    document.getElementById('q_lahan').value = 'Semua Lahan';
                    document.getElementById('q_jenis_sarpras').value = 'Semua Jenis Sarpras';
                    $('#q_lahan').trigger('change');
                    $('#q_jenis_sarpras').trigger('change');
                    document.getElementById('filter-text').textContent = @json($hide_tpu_filter && $tpus->first() ? $tpus->first()->nama : 'Semua TPU');
                    document.getElementById('titleType').textContent = @json($hide_tpu_filter && $tpus->first() ? $tpus->first()->nama : 'Semua TPU');
                    $('#datatable_processing').show();
                    datatable.ajax.reload(function(json) {
                        $('#datatable_processing').hide();
                    }, false);
                }

                $('[name="q_lahan"], [name="q_jenis_sarpras"]').change(function() {
                    $('#datatable_processing').show();
                    $('#kt_sarpras_table tbody').empty();
                    datatable.ajax.reload(function(json) {
                        $('#datatable_processing').hide();
                    }, false);
                });
            }

            var handleBulkActions = function() {
                const checkboxes = document.querySelectorAll('#kt_sarpras_table .row-checkbox');
                const bulkToolbar = document.querySelector('[data-kt-sarpras-table-toolbar="selected"]');
                const countElement = document.querySelector('[data-kt-sarpras-table-select="selected_count"]');
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

            var handleEvents = function() {
                $(document).on('change', '.row-checkbox', function() {
                    handleBulkActions();
                });
                $(document).on('change', '[data-kt-check="true"]', function() {
                    const isChecked = $(this).is(':checked');
                    $('.row-checkbox').prop('checked', isChecked);
                    handleBulkActions();
                });
                $(document).on('click', '[data-kt-sarpras-table-select="cancel_selection"]', function() {
                    $('.row-checkbox').prop('checked', false);
                    $('[data-kt-check="true"]').prop('checked', false);
                    const bulkToolbar = document.querySelector('[data-kt-sarpras-table-toolbar="selected"]');
                    bulkToolbar.classList.add('d-none');
                });
                $(document).on('click', '[data-kt-sarpras-table-select="delete_selected"]', function() {
                    const checkedBoxes = document.querySelectorAll('#kt_sarpras_table .row-checkbox:checked');
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
                        text: `Apakah Anda yakin ingin menghapus ${selectedIds.length} data sarpras yang dipilih?`,
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
                                url: "{{ route('tpu.sarpras.destroy.bulk') }}",
                                type: 'POST',
                                data: {
                                    uuids: selectedIds,
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function(res) {
                                    datatable.ajax.reload(null, false);
                                    const bulkToolbar = document.querySelector('[data-kt-sarpras-table-toolbar="selected"]');
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
                    let uuid = $(this).attr('data-uuid');
                    let name = $(this).attr('data-name');
                    Swal.fire({
                        title: "Hapus Data",
                        text: `Apakah Anda yakin ingin menghapus sarpras "${name}"?`,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#f1416c",
                        cancelButtonColor: "#7e8299",
                        confirmButtonText: "Ya, hapus!",
                        cancelButtonText: "Batal"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('tpu.sarpras.destroy') }}",
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
                    initSarprasTable();
                    handleSearchDatatable();
                    handleFilter();
                    handleEvents();
                }
            }
        }();

        $(document).ready(function() {
            KTSarprasList.init();
            @if (!$hide_tpu_filter)
                // Trigger initial lahan fetch on page load if TPU is selected
                if ($('#q_tpu').val() !== 'Semua TPU') {
                    $('#q_tpu').trigger('change');
                }
            @endif
        });
    </script>
@endpush
