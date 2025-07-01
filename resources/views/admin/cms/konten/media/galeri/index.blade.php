@push('styles')
    <style>
        /* Bulk actions toolbar animation */
        [data-kt-galeri-table-toolbar="selected"] {
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(-10px);
        }

        [data-kt-galeri-table-toolbar="selected"]:not(.d-none) {
            opacity: 1;
            transform: translateY(0);
        }

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

        [data-kt-galeri-table-toolbar="selected"] {
            border: 1px solid rgba(33, 150, 243, 0.25);
        }

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

        .dataTables_wrapper {
            position: relative;
        }

        .dataTables_processing {
            display: none !important;
        }

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

        .dt-buttons {
            display: none !important;
        }

        .stats-card {
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .stats-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stats-draft {
            background: linear-gradient(135deg, #FFA726, #FB8C00);
        }

        .stats-pending-review {
            background: linear-gradient(135deg, #42A5F5, #1E88E5);
        }

        .stats-published {
            background: linear-gradient(135deg, #66BB6A, #43A047);
        }

        .stats-scheduled {
            background: linear-gradient(135deg, #AB47BC, #8E24AA);
        }

        .stats-archived {
            background: linear-gradient(135deg, #78909C, #546E7A);
        }

        .stats-deleted {
            background: linear-gradient(135deg, #EF5350, #D32F2F);
        }

        .galeri-thumbnail {
            width: 45px;
            height: 45px;
            object-fit: cover;
            border-radius: 6px;
            border: 2px solid #e1e5e9;
        }

        .form-control-search:focus {
            border-color: #009ef7;
            box-shadow: 0 0 5px rgba(0, 158, 247, 0.3);
        }
    </style>
@endpush

@extends('layouts.admin')

@section('title', 'Galeri')

@section('toolbar')
    <div class="d-flex flex-stack flex-row-fluid">
        <div class="d-flex flex-column flex-row-fluid">
            <div class="page-title d-flex align-items-center me-3">
                <h1 class="page-heading d-flex flex-column justify-content-center text-dark fw-bold fs-lg-2x gap-2">
                    <span>Galeri</span>
                </h1>
            </div>
            <ul class="breadcrumb breadcrumb-separatorless fw-semibold mb-3 fs-7">
                <li class="breadcrumb-item text-gray-700 fw-bold lh-1">
                    <a href="{{ route('prt.apps.index') }}" class="text-gray-700 text-hover-primary">
                        <i class="ki-outline ki-home fs-6"></i>
                    </a>
                </li>
                <li class="breadcrumb-item"><i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i></li>
                <li class="breadcrumb-item text-gray-700">Konten</li>
                <li class="breadcrumb-item"><i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i></li>
                <li class="breadcrumb-item text-gray-700">Konten Media</li>
                <li class="breadcrumb-item"><i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i></li>
                <li class="breadcrumb-item text-gray-700">Galeri</li>
            </ul>
        </div>
        <div class="d-flex align-self-center flex-center flex-shrink-0">
            <div class="me-3">
                <a href="#" class="btn btn-sm btn-flex btn-outline btn-color-gray-700 btn-active-color-primary bg-body h-40px fs-7 fw-bold" data-kt-menu-trigger="click"
                    data-kt-menu-placement="bottom-end">
                    <i class="ki-outline ki-filter fs-6 text-muted me-1"></i>
                    Filter: <span id="filter-text" class="ms-1">{{ $status }}</span>
                </a>
                <div class="menu menu-sub menu-sub-dropdown w-250px w-md-300px" data-kt-menu="true" id="kt_menu_filter">
                    <div class="px-7 py-5">
                        <div class="fs-5 text-dark fw-bold">Filter Options</div>
                    </div>
                    <div class="separator border-gray-200"></div>
                    <div class="px-7 py-5">
                        <div class="mb-10">
                            <label class="form-label fw-semibold">Status Galeri:</label>
                            <select class="form-select form-select-solid" name="q_status_galeri" id="q_status_galeri" data-control="select2" data-placeholder="Pilih Status"
                                data-allow-clear="true">
                                <option value="">All</option>
                                <option @selected($status == 'Draft') value="Draft">Draft</option>
                                <option @selected($status == 'Pending Review') value="Pending Review">Pending Review</option>
                                <option @selected($status == 'Published') value="Published">Published</option>
                                <option @selected($status == 'Scheduled') value="Scheduled">Scheduled</option>
                                <option @selected($status == 'Archived') value="Archived">Archived</option>
                            </select>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="reset" class="btn btn-sm btn-light btn-active-light-primary me-2" data-kt-menu-dismiss="true" onclick="resetFilter()">Reset</button>
                            <button type="button" class="btn btn-sm btn-primary" data-kt-menu-dismiss="true" onclick="applyFilter()">Apply</button>
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                <i class="ki-outline ki-exit-down fs-2"></i> Export Report
            </button>
            <div id="kt_datatable_example_export_menu"
                class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-200px py-4" data-kt-menu="true">
                <div class="menu-item px-3"><a href="#" class="menu-link px-3" data-kt-export="copy">Copy to clipboard</a></div>
                <div class="menu-item px-3"><a href="#" class="menu-link px-3" data-kt-export="excel">Export as Excel</a></div>
                <div class="menu-item px-3"><a href="#" class="menu-link px-3" data-kt-export="csv">Export as CSV</a></div>
                <div class="menu-item px-3"><a href="#" class="menu-link px-3" data-kt-export="pdf">Export as PDF</a></div>
            </div>
            <div id="kt_datatable_example_buttons" class="d-none"></div>
            <a href="{{ route('prt.apps.galeri.create') }}" class="btn btn-sm btn-primary d-flex flex-center ms-3 px-4 py-3">
                <i class="ki-outline ki-plus fs-2"></i> <span>Tambah Galeri</span>
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        @foreach (['Draft' => 'warning', 'Pending Review' => 'info', 'Published' => 'success', 'Scheduled' => 'primary', 'Archived' => 'dark', 'Deleted' => 'danger'] as $label => $color)
            <div class="col-xxl-2 col-lg-4 col-sm-6">
                <div class="card stats-card bg-body hoverable card-xl-stretch mb-xl-8">
                    <div class="card-body">
                        <div class="text-gray-900 fw-bold fs-2 mb-2 mt-5" id="stats_{{ Str::slug($label) }}">{{ Helper::GetStatistikByModel('Galeri', $label) }}</div>
                        <div class="fw-semibold text-gray-400">{{ $label }}</div>
                        <div class="stats-icon stats-{{ Str::slug($label) }} position-absolute top-0 end-0 mt-3 me-3">
                            <i
                                class="ki-outline ki-{{ $label == 'Draft' ? 'document' : ($label == 'Pending Review' ? 'toggle-off-circle' : ($label == 'Published' ? 'verify' : ($label == 'Scheduled' ? 'calendar' : ($label == 'Archived' ? 'archive' : 'trash')))) }} fs-2 text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h4 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold fs-3 mb-1">Data Galeri</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">Menampilkan status: <strong id="titleStatus">{{ $status }}</strong></span>
                </h4>
            </div>
            <div class="card-toolbar">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-outline ki-magnifier fs-1 position-absolute ms-6"></i>
                    <input type="text" data-kt-galeri-table-filter="search" class="form-control form-control-solid form-control-search w-250px ps-14" placeholder="Cari..." />
                </div>
            </div>
        </div>
        <div class="card-body py-4">
            <div class="d-flex justify-content-between align-items-center d-none bg-light-primary rounded p-3 mb-5" data-kt-galeri-table-toolbar="selected">
                <div class="fw-bold text-primary">
                    <i class="ki-outline ki-check-square fs-2 me-2"></i>
                    <span data-kt-galeri-table-select="selected_count"></span> item dipilih
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-light-danger me-2" data-kt-galeri-table-select="delete_selected" data-bs-toggle="tooltip"
                        title="Hapus yang dipilih">
                        <i class="ki-outline ki-trash fs-6 me-1"></i> Hapus
                    </button>
                    <button type="button" class="btn btn-sm btn-light" data-kt-galeri-table-select="cancel_selection" data-bs-toggle="tooltip" title="Batalkan pilihan">
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
                            <th width="10%">Views</th>
                            <th width="10%">Foto</th>
                            <th width="12%">Author</th>
                            <th width="12%">Publisher</th>
                            <th width="12%">Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold"></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        "use strict";
        var KTDatatablesGaleri = function() {
            var table, datatable;

            var initDatatable = function() {
                datatable = $('#datatable').DataTable({
                    processing: true,
                    serverSide: false, // Changed to client-side processing
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
                        [10, 25, 50, 100, "All"]
                    ],
                    order: [
                        [7, 'desc'] // Sort by tanggal (newest first)
                    ],
                    language: {
                        searchPlaceholder: 'Cari...',
                        sSearch: '',
                        lengthMenu: '_MENU_ entri per halaman',
                        processing: '<div class="d-flex align-items-center"><span class="spinner-border spinner-border-sm me-2"></span>Memuat...</div>',
                        paginate: {
                            next: '<i class="ki-outline ki-arrow-right"></i>',
                            previous: '<i class="ki-outline ki-arrow-left"></i>',
                            first: '<i class="ki-outline ki-double-arrow-left"></i>',
                            last: '<i class="ki-outline ki-double-arrow-right"></i>'
                        },
                        emptyTable: "Tidak ada data galeri",
                        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ galeri",
                        infoEmpty: "Menampilkan 0 sampai 0 dari 0 galeri",
                        infoFiltered: "(disaring dari _MAX_ total galeri)",
                        zeroRecords: "Tidak ditemukan data yang sesuai"
                    },
                    ajax: {
                        url: "{{ route('prt.apps.galeri.index') }}",
                        type: 'GET',
                        data: function(d) {
                            d.filter = {
                                status: $('[name="q_status_galeri"]').val() || 'Published'
                            };
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
                        }
                    },
                    columns: [{
                            data: 'checkbox',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'judul_raw',
                            name: 'judul',
                            render: function(data, type, row) {
                                if (type === 'display') {
                                    return row.judul_html;
                                }
                                return data;
                            }
                        },
                        {
                            data: 'views_raw',
                            name: 'views',
                            render: function(data, type, row) {
                                if (type === 'display') {
                                    return row.views_html;
                                }
                                return data;
                            }
                        },
                        {
                            data: 'jumlah_raw',
                            name: 'jumlah',
                            render: function(data, type, row) {
                                if (type === 'display') {
                                    return row.jumlah_html;
                                }
                                return data;
                            }
                        },
                        {
                            data: 'penulis_raw',
                            name: 'penulis',
                            render: function(data, type, row) {
                                if (type === 'display') {
                                    return row.penulis_html;
                                }
                                return data;
                            }
                        },
                        {
                            data: 'publisher_raw',
                            name: 'publisher',
                            render: function(data, type, row) {
                                if (type === 'display') {
                                    return row.publisher_html;
                                }
                                return data;
                            }
                        },
                        {
                            data: 'tanggal_raw',
                            name: 'tanggal',
                            render: function(data, type, row) {
                                if (type === 'display') {
                                    return row.tanggal_html;
                                }
                                return data;
                            }
                        },
                        {
                            data: 'aksi',
                            name: 'aksi',
                            orderable: false,
                            searchable: false
                        }
                    ],
                    columnDefs: [{
                        className: "text-center",
                        targets: [0, 1, 3, 4, 8]
                    }],
                    drawCallback: function() {
                        $('#datatable_processing').hide();
                        handleBulkActions();
                        $('[data-bs-toggle="tooltip"]').tooltip();
                    }
                });
            };

            var exportButtons = function() {
                const documentTitle = 'Data Galeri';
                var buttons = new $.fn.dataTable.Buttons(datatable, {
                    buttons: [{
                            extend: 'copyHtml5',
                            title: function() {
                                return documentTitle + ' - ' + ($('[name="q_status_galeri"]').val() || 'Published');
                            },
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6, 7]
                            }
                        },
                        {
                            extend: 'excelHtml5',
                            title: function() {
                                return documentTitle + ' - ' + ($('[name="q_status_galeri"]').val() || 'Published');
                            },
                            filename: function() {
                                return 'galeri-' + ($('[name="q_status_galeri"]').val() || 'Published').toLowerCase().replace(/\s+/g, '-') + '-' + new Date()
                                    .toISOString().slice(0, 10);
                            },
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6, 7]
                            }
                        },
                        {
                            extend: 'csvHtml5',
                            title: function() {
                                return documentTitle + ' - ' + ($('[name="q_status_galeri"]').val() || 'Published');
                            },
                            filename: function() {
                                return 'galeri-' + ($('[name="q_status_galeri"]').val() || 'Published').toLowerCase().replace(/\s+/g, '-') + '-' + new Date()
                                    .toISOString().slice(0, 10);
                            },
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6, 7]
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            title: function() {
                                return documentTitle + ' - ' + ($('[name="q_status_galeri"]').val() || 'Published');
                            },
                            filename: function() {
                                return 'galeri-' + ($('[name="q_status_galeri"]').val() || 'Published').toLowerCase().replace(/\s+/g, '-') + '-' + new Date()
                                    .toISOString().slice(0, 10);
                            },
                            orientation: 'landscape',
                            pageSize: 'A4',
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6, 7]
                            },
                            customize: function(doc) {
                                doc.content[1].table.widths = ['8%', '30%', '10%', '10%', '14%', '14%', '14%'];
                                doc.styles.tableHeader.fontSize = 9;
                                doc.styles.tableBodyOdd.fontSize = 8;
                                doc.styles.tableBodyEven.fontSize = 8;
                                doc.defaultStyle.fontSize = 8;
                            }
                        }
                    ]
                }).container().appendTo($('#kt_datatable_example_buttons'));
                document.querySelectorAll('#kt_datatable_example_export_menu [data-kt-export]').forEach(exportButton => {
                    exportButton.addEventListener('click', e => {
                        e.preventDefault();
                        const exportValue = e.target.getAttribute('data-kt-export');
                        const target = document.querySelector('.dt-buttons .buttons-' + exportValue);
                        Swal.fire({
                            title: `Exporting ${exportValue.toUpperCase()}...`,
                            text: 'Mohon tunggu',
                            allowOutsideClick: false,
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
                    });
                });
            };

            var handleSearchDatatable = function() {
                const filterSearch = document.querySelector('[data-kt-galeri-table-filter="search"]');
                if (filterSearch) {
                    filterSearch.addEventListener('keyup', function(e) {
                        datatable.search(e.target.value).draw();
                    });
                }
            };

            var handleBulkActions = function() {
                const checkboxes = document.querySelectorAll('#datatable .row-checkbox');
                const bulkToolbar = document.querySelector('[data-kt-galeri-table-toolbar="selected"]');
                const countElement = document.querySelector('[data-kt-galeri-table-select="selected_count"]');
                let checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
                countElement.textContent = checkedCount;
                bulkToolbar.classList.toggle('d-none', checkedCount === 0);
            };

            var handleFilter = function() {
                $('#q_status_galeri').select2({
                    minimumResultsForSearch: Infinity,
                    allowClear: true
                });
                window.applyFilter = function() {
                    var selectedStatus = document.getElementById('q_status_galeri').value || 'Published';
                    document.getElementById('filter-text').textContent = selectedStatus;
                    $('#datatable_processing').show();
                    datatable.ajax.reload(function() {
                        $('#datatable_processing').hide();
                        $('#titleStatus').html(selectedStatus);
                    }, false);
                };
                window.resetFilter = function() {
                    document.getElementById('q_status_galeri').value = 'Published';
                    document.getElementById('filter-text').textContent = 'Published';
                    $('#datatable_processing').show();
                    datatable.ajax.reload(function() {
                        $('#datatable_processing').hide();
                        $('#titleStatus').html('Published');
                    }, false);
                };
                $('[name="q_status_galeri"]').change(function() {
                    var q_status_galeri = $(this).val() || 'Published';
                    $('#datatable_processing').show();
                    $('#datatable tbody').empty();
                    datatable.ajax.reload(function() {
                        $('#datatable_processing').hide();
                        $('#titleStatus').html(q_status_galeri);
                    }, false);
                });
            };

            var handleEvents = function() {
                $(document).on('change', '.row-checkbox, [data-kt-check="true"]', function() {
                    handleBulkActions();
                });
                $(document).on('click', '[data-kt-galeri-table-select="cancel_selection"]', function() {
                    $('.row-checkbox, [data-kt-check="true"]').prop('checked', false);
                    handleBulkActions();
                });
                $(document).on('click', '[data-kt-galeri-table-select="delete_selected"]', function() {
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
                        text: `Apakah Anda yakin ingin menghapus ${selectedIds.length} galeri yang dipilih?`,
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
                                url: "{{ route('prt.apps.galeri.destroy') }}",
                                type: 'POST',
                                data: {
                                    uuids: selectedIds,
                                    tags: 'galeri',
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function(res) {
                                    datatable.ajax.reload(null, false);
                                    document.querySelector('[data-kt-galeri-table-toolbar="selected"]').classList.add('d-none');
                                    $('[data-kt-check="true"]').prop('checked', false);
                                    Swal.fire({
                                        title: "Success",
                                        text: res.message,
                                        icon: "success"
                                    });
                                    getStatsCounter();
                                },
                                error: function(xhr) {
                                    datatable.ajax.reload(null, false);
                                    Swal.fire({
                                        title: "Error",
                                        text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus data',
                                        icon: "error"
                                    });
                                }
                            });
                        }
                    });
                });
                $(document).on('click', '[data-delete]', function() {
                    let uuid = $(this).attr('data-delete');
                    Swal.fire({
                        title: "Hapus Data",
                        text: "Apakah Anda yakin?",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#f1416c",
                        cancelButtonColor: "#7e8299",
                        confirmButtonText: "Ya, hapus!",
                        cancelButtonText: "Batal"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('prt.apps.galeri.destroy') }}",
                                type: 'POST',
                                data: {
                                    uuid: uuid,
                                    tags: 'galeri',
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function(res) {
                                    datatable.ajax.reload(null, false);
                                    Swal.fire({
                                        title: "Success",
                                        text: res.message,
                                        icon: "success"
                                    });
                                    getStatsCounter();
                                },
                                error: function(xhr) {
                                    datatable.ajax.reload(null, false);
                                    Swal.fire({
                                        title: "Error",
                                        text: xhr.responseJSON?.message || 'Terjadi kesalahan',
                                        icon: "error"
                                    });
                                }
                            });
                        }
                    });
                });
            };

            var getStatsCounter = function() {
                $.ajax({
                    url: "{{ route('ajax.get.stats.content') }}",
                    type: 'POST',
                    data: {
                        model: "Galeri",
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        ['draft', 'pending-review', 'published', 'scheduled', 'archived', 'deleted'].forEach(status => {
                            if ($(`#stats_${status}`).length) $(`#stats_${status}`).html(res.data[status] || 0);
                        });
                    },
                    error: function(xhr) {
                        console.log('Error getting statistics:', xhr.responseJSON);
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
            KTDatatablesGaleri.init();
        });
    </script>
@endpush
