@push('styles')
    <style>
        /* Bulk actions toolbar animation */
        [data-kt-post-table-toolbar="selected"] {
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(-10px);
        }

        [data-kt-post-table-toolbar="selected"]:not(.d-none) {
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
        [data-kt-post-table-toolbar="selected"] {
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

        /* Statistics cards styling */
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
    </style>
@endpush

@extends('layouts.admin')

@section('title', 'Video | ' . env('APP_NAME'))

@section('toolbar')
    <div class="d-flex flex-stack flex-row-fluid">
        <div class="d-flex flex-column flex-row-fluid">
            <div class="page-title d-flex align-items-center me-3">
                <h1 class="page-heading d-flex flex-column justify-content-center text-dark fw-bold fs-lg-2x gap-2">
                    <span>Video</span>
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
                <li class="breadcrumb-item text-gray-700">Konten</li>
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                <li class="breadcrumb-item text-gray-700">Media</li>
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                <li class="breadcrumb-item text-gray-700">Video</li>
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
                            <label class="form-label fw-semibold">Status Video:</label>
                            <div>
                                <select class="form-select form-select-solid" name="q_status_post" id="q_status_post" data-control="select2" data-placeholder="Pilih Status"
                                    data-allow-clear="true">
                                    <option @if ($status == 'Draft') selected @endif value="Draft">Draft</option>
                                    <option @if ($status == 'Pending Review') selected @endif value="Pending Review">Pending Review</option>
                                    <option @if ($status == 'Published') selected @endif value="Published">Published</option>
                                    <option @if ($status == 'Scheduled') selected @endif value="Scheduled">Scheduled</option>
                                    <option @if ($status == 'Archived') selected @endif value="Archived">Archived</option>
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
            <a href="{{ route('prt.apps.video.create') }}" class="btn btn-sm btn-primary d-flex flex-center ms-3 px-4 py-3">
                <i class="ki-outline ki-plus fs-2"></i>
                <span>Tambah Video</span>
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
                        <div class="text-gray-900 fw-bold fs-2 mb-2 mt-5" id="stats_{{ Str::slug($label) }}">{{ \App\Helpers\Helper::GetStatistikByModel('Video', $label) }}</div>
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
                    <span class="card-label fw-bold fs-3 mb-1">Data Video</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">Menampilkan status: <strong id="titleStatus">{{ $status }}</strong></span>
                </h4>
            </div>
            <div class="card-toolbar">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-outline ki-magnifier fs-1 position-absolute ms-6"></i>
                    <input type="text" data-kt-post-table-filter="search" class="form-control form-control-solid w-250px ps-14" placeholder="Cari video..." />
                </div>
            </div>
        </div>
        <div class="card-body py-4">
            <div class="d-flex justify-content-between align-items-center d-none bg-light-primary rounded p-3 mb-5" data-kt-post-table-toolbar="selected">
                <div class="fw-bold text-primary">
                    <i class="ki-outline ki-check-square fs-2 me-2"></i>
                    <span data-kt-post-table-select="selected_count"></span> item dipilih
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-light-danger me-2" data-kt-post-table-select="delete_selected" data-bs-toggle="tooltip"
                        title="Hapus yang dipilih">
                        <i class="ki-outline ki-trash fs-6 me-1"></i>
                        Hapus
                    </button>
                    <button type="button" class="btn btn-sm btn-light" data-kt-post-table-select="cancel_selection" data-bs-toggle="tooltip" title="Batalkan pilihan">
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
                            <th width="10%">Author</th>
                            <th width="10%">Publisher</th>
                            <th width="10%">Tanggal</th>
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
                            <th width="10%">Views</th>
                            <th width="10%">Author</th>
                            <th width="10%">Publisher</th>
                            <th width="10%">Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('be/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script>
        "use strict";

        var KTDatatablesVideos = function() {
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
                        [10, 25, 50, 100, "All"]
                    ],
                    order: [
                        [1, 'asc']
                    ],
                    language: {
                        searchPlaceholder: 'Search...',
                        sSearch: '',
                        lengthMenu: '_MENU_ entries per page',
                        processing: '<div class="d-flex align-items-center"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</div>',
                        loadingRecords: '<div class="d-flex align-items-center"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</div>',
                        paginate: {
                            next: '<i class="ki-outline ki-arrow-right"></i>',
                            previous: '<i class="ki-outline ki-arrow-left"></i>',
                            first: '<i class="ki-outline ki-double-arrow-left"></i>',
                            last: '<i class="ki-outline ki-double-arrow-right"></i>'
                        },
                        emptyTable: "Tidak ada data video",
                        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ video",
                        infoEmpty: "Menampilkan 0 sampai 0 dari 0 video",
                        infoFiltered: "(disaring dari _MAX_ total video)",
                        zeroRecords: "Tidak ditemukan data yang sesuai"
                    },
                    ajax: {
                        url: "{{ route('prt.apps.video.index') }}",
                        type: 'GET',
                        data: function(data) {
                            data.filter = {
                                'status': $('[name="q_status_post"]').val() || 'Published',
                            };
                        },
                        dataSrc: function(json) {
                            return json.data;
                        },
                        error: function(xhr, error, thrown) {
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
                            data: 'judul',
                            name: 'judul'
                        },
                        {
                            data: 'views',
                            name: 'views'
                        },
                        {
                            data: 'penulis',
                            name: 'penulis'
                        },
                        {
                            data: 'publisher',
                            name: 'publisher'
                        },
                        {
                            data: 'tanggal',
                            name: 'tanggal'
                        },
                        {
                            data: 'aksi',
                            name: 'aksi',
                            orderable: false,
                            searchable: false,
                            exportable: false
                        }
                    ],
                    columnDefs: [{
                        className: "text-center",
                        targets: [0, 1, 3, 7]
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
                        getStatsCounter();
                    }
                });
            }

            var exportButtons = function() {
                const documentTitle = 'Data Video';
                var buttons = new $.fn.dataTable.Buttons(datatable, {
                    buttons: [{
                            extend: 'copyHtml5',
                            title: function() {
                                const currentFilter = $('[name="q_status_post"]').val() || 'Published';
                                return documentTitle + ' - ' + currentFilter;
                            },
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6]
                            }
                        },
                        {
                            extend: 'excelHtml5',
                            title: function() {
                                const currentFilter = $('[name="q_status_post"]').val() || 'Published';
                                return documentTitle + ' - ' + currentFilter;
                            },
                            filename: function() {
                                const date = new Date().toISOString().slice(0, 10);
                                const filter = ($('[name="q_status_post"]').val() || 'Published').toLowerCase().replace(/\s+/g, '-');
                                return `video-${filter}-${date}`;
                            },
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6]
                            }
                        },
                        {
                            extend: 'csvHtml5',
                            title: function() {
                                const currentFilter = $('[name="q_status_post"]').val() || 'Published';
                                return documentTitle + ' - ' + currentFilter;
                            },
                            filename: function() {
                                const date = new Date().toISOString().slice(0, 10);
                                const filter = ($('[name="q_status_post"]').val() || 'Published').toLowerCase().replace(/\s+/g, '-');
                                return `video-${filter}-${date}`;
                            },
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6]
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            title: function() {
                                const currentFilter = $('[name="q_status_post"]').val() || 'Published';
                                return documentTitle + ' - ' + currentFilter;
                            },
                            filename: function() {
                                const date = new Date().toISOString().slice(0, 10);
                                const filter = ($('[name="q_status_post"]').val() || 'Published').toLowerCase().replace(/\s+/g, '-');
                                return `video-${filter}-${date}`;
                            },
                            orientation: 'landscape',
                            pageSize: 'A4',
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6]
                            },
                            customize: function(doc) {
                                doc.content[1].table.widths = ['8%', '30%', '15%', '12%', '12%', '12%'];
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
            }

            var handleSearchDatatable = function() {
                const filterSearch = document.querySelector('[data-kt-post-table-filter="search"]');
                if (filterSearch) {
                    filterSearch.addEventListener('keyup', function(e) {
                        datatable.search(e.target.value).draw();
                    });
                }
            }

            var handleBulkActions = function() {
                const checkboxes = document.querySelectorAll('#datatable .row-checkbox');
                const bulkToolbar = document.querySelector('[data-kt-post-table-toolbar="selected"]');
                const countElement = document.querySelector('[data-kt-post-table-select="selected_count"]');
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

            var handleFilter = function() {
                $('#q_status_post').select2();
                window.applyFilter = function() {
                    var selectedStatus = document.getElementById('q_status_post').value;
                    document.getElementById('filter-text').textContent = selectedStatus;
                    $('#datatable_processing').show();
                    datatable.ajax.reload(function(json) {
                        $('#datatable_processing').hide();
                        $('#titleStatus').html(selectedStatus);
                        getStatsCounter();
                    }, false);
                }
                window.resetFilter = function() {
                    document.getElementById('q_status_post').value = 'Published';
                    document.getElementById('filter-text').textContent = 'Published';
                    $('#datatable_processing').show();
                    datatable.ajax.reload(function(json) {
                        $('#datatable_processing').hide();
                        $('#titleStatus').html('Published');
                        getStatsCounter();
                    }, false);
                }
                $('[name="q_status_post"]').change(function() {
                    var q_status_post = $(this).val();
                    $('#datatable_processing').show();
                    $('#datatable tbody').empty();
                    datatable.ajax.reload(function(json) {
                        $('#datatable_processing').hide();
                        $('#titleStatus').html(q_status_post);
                        getStatsCounter();
                    }, false);
                });
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
                $(document).on('click', '[data-kt-post-table-select="cancel_selection"]', function() {
                    $('.row-checkbox').prop('checked', false);
                    $('[data-kt-check="true"]').prop('checked', false);
                    const bulkToolbar = document.querySelector('[data-kt-post-table-toolbar="selected"]');
                    bulkToolbar.classList.add('d-none');
                });
                $(document).on('click', '[data-kt-post-table-select="delete_selected"]', function() {
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
                        text: `Apakah Anda yakin ingin menghapus ${selectedIds.length} video yang dipilih?`,
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
                                url: "{{ route('prt.apps.video.destroy.bulk') }}",
                                type: 'POST',
                                data: {
                                    uuids: selectedIds,
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function(res) {
                                    datatable.ajax.reload(null, false);
                                    const bulkToolbar = document.querySelector('[data-kt-post-table-toolbar="selected"]');
                                    bulkToolbar.classList.add('d-none');
                                    $('[data-kt-check="true"]').prop('checked', false);
                                    Swal.fire({
                                        title: "Success",
                                        text: res.message,
                                        icon: "success",
                                    });
                                    getStatsCounter();
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
                                url: "{{ route('prt.apps.video.destroy') }}",
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
                                    getStatsCounter();
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

            var getStatsCounter = function() {
                $.ajax({
                    url: "{{ route('ajax.get.stats.content') }}",
                    type: 'POST',
                    data: {
                        model: "Video",
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        if ($('#stats_draft').length) $('#stats_draft').html(res.data.draft);
                        if ($('#stats_pending').length) $('#stats_pending').html(res.data.pending);
                        if ($('#stats_published').length) $('#stats_published').html(res.data.published);
                        if ($('#stats_scheduled').length) $('#stats_scheduled').html(res.data.scheduled);
                        if ($('#stats_archived').length) $('#stats_archived').html(res.data.archived);
                        if ($('#stats_deleted').length) $('#stats_deleted').html(res.data.deleted);
                    },
                    error: function(xhr) {
                        console.log('Error getting statistics:', xhr.responseJSON);
                    }
                });
            };

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

        $(document).ready(function() {
            KTDatatablesVideos.init();
        });
    </script>
@endpush
