@push('styles')
    <style>
        /* Bulk actions toolbar animation */
        [data-kt-category-table-toolbar="selected"] {
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(-10px);
        }

        [data-kt-category-table-toolbar="selected"]:not(.d-none) {
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
        [data-kt-category-table-toolbar="selected"] {
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

{{-- SEO::BEGIN --}}
@section('title', 'Master Kategori')
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
                    <span>Master Kategori</span>
                </h1>
                {{-- end::Title --}}
            </div>
            {{-- end::Page title --}}
            {{-- begin::Breadcrumb --}}
            <ul class="breadcrumb breadcrumb-separatorless fw-semibold mb-3 fs-7">
                {{-- begin::Item --}}
                <li class="breadcrumb-item text-gray-700 fw-bold lh-1">
                    <a href="{{ route('auth.home') }}" class="text-gray-700 text-hover-primary">
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
                <li class="breadcrumb-item text-gray-700">Master Data</li>
                {{-- end::Item --}}
                {{-- begin::Item --}}
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                {{-- end::Item --}}
                {{-- begin::Item --}}
                <li class="breadcrumb-item text-gray-700">Kategori</li>
                {{-- end::Item --}}
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
                    Filter: <span id="filter-text" class="ms-1">{{ $type }}</span>
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
                            <label class="form-label fw-semibold">Tipe Kategori:</label>
                            <div>
                                <select class="form-select form-select-solid" name="q_type_kategori" id="q_type_kategori" data-control="select2" data-placeholder="Pilih Tipe"
                                    data-allow-clear="true">
                                    <option @if ($type == 'Semua Data') selected @endif value="Semua Data">Semua Data</option>
                                    @if (\count($getType) > 0)
                                        @foreach ($getType as $item)
                                            <option @if ($type == $item->type) selected @endif value="{{ $item->type }}">{{ $item->type }}</option>
                                        @endforeach
                                    @endif
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
            <a href="#" class="btn btn-sm btn-outline btn-outline-dashed btn-outline-info btn-active-light-info me-3 px-4 py-3" data-kt-menu-trigger="click"
                data-kt-menu-placement="bottom-end">
                <i class="ki-outline ki-exit-up fs-2"></i>
                <span class="d-none d-sm-inline">Export</span>
            </a>
            {{-- begin::Export Menu --}}
            <div id="kt_datatable_example_export_menu"
                class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-200px py-4" data-kt-menu="true">
                {{-- begin::Menu item --}}
                <div class="menu-item px-3">
                    <a href="#" class="menu-link px-3" data-kt-export="copy">
                        <i class="ki-outline ki-copy fs-6 me-2"></i>Copy to clipboard
                    </a>
                </div>
                {{-- end::Menu item --}}
                {{-- begin::Menu item --}}
                <div class="menu-item px-3">
                    <a href="#" class="menu-link px-3" data-kt-export="excel">
                        <i class="ki-outline ki-exit-down fs-6 me-2"></i>Export as Excel
                    </a>
                </div>
                {{-- end::Menu item --}}
                {{-- begin::Menu item --}}
                <div class="menu-item px-3">
                    <a href="#" class="menu-link px-3" data-kt-export="csv">
                        <i class="ki-outline ki-exit-down fs-6 me-2"></i>Export as CSV
                    </a>
                </div>
                {{-- end::Menu item --}}
                {{-- begin::Menu item --}}
                <div class="menu-item px-3">
                    <a href="#" class="menu-link px-3" data-kt-export="pdf">
                        <i class="ki-outline ki-exit-down fs-6 me-2"></i>Export as PDF
                    </a>
                </div>
                {{-- end::Menu item --}}
            </div>
            {{-- end::Export Menu --}}
            {{-- Hidden export buttons container --}}
            <div id="kt_datatable_example_buttons" class="d-none"></div>
            {{-- end::Export button --}}
            {{-- begin::Primary button --}}
            <a href="{{ route('prt.apps.mst.tags.create') }}" class="btn btn-sm btn-primary d-flex flex-center ms-3 px-4 py-3">
                <i class="ki-outline ki-plus fs-2"></i>
                <span>Tambah Kategori</span>
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
                    <span class="card-label fw-bold fs-3 mb-1">Data Master Kategori</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">Menampilkan kategori: <strong id="titleType">{{ $type }}</strong></span>
                </h4>
            </div>
            {{-- end::Card title --}}
            {{-- begin::Card toolbar --}}
            <div class="card-toolbar">
                {{-- begin::Search --}}
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-outline ki-magnifier fs-1 position-absolute ms-6"></i>
                    <input type="text" data-kt-category-table-filter="search" class="form-control form-control-solid w-250px ps-14" placeholder="Cari kategori..." />
                </div>
                {{-- end::Search --}}
            </div>
            {{-- end::Card toolbar --}}
        </div>
        {{-- end::Card header --}}

        {{-- begin::Card body --}}
        <div class="card-body py-4">
            {{-- begin::Bulk Actions --}}
            <div class="d-flex justify-content-between align-items-center d-none bg-light-primary rounded p-3 mb-5" data-kt-category-table-toolbar="selected">
                <div class="fw-bold text-primary">
                    <i class="ki-outline ki-check-square fs-2 me-2"></i>
                    <span data-kt-category-table-select="selected_count"></span> item dipilih
                </div>

                <div class="d-flex align-items-center gap-2">
                    {{-- begin::Bulk Status Actions --}}
                    <div class="btn-group me-2" role="group" aria-label="Status Actions">
                        <button type="button" class="btn btn-sm btn-light-success" data-kt-category-table-select="activate_selected" data-bs-toggle="tooltip"
                            title="Aktifkan yang dipilih">
                            <i class="ki-outline ki-check fs-6 me-1"></i>
                            Aktifkan
                        </button>
                        <button type="button" class="btn btn-sm btn-light-warning" data-kt-category-table-select="deactivate_selected" data-bs-toggle="tooltip"
                            title="Nonaktifkan yang dipilih">
                            <i class="ki-outline ki-cross fs-6 me-1"></i>
                            Nonaktifkan
                        </button>
                    </div>
                    {{-- end::Bulk Status Actions --}}

                    {{-- begin::Bulk Delete Action --}}
                    <button type="button" class="btn btn-sm btn-light-danger me-2" data-kt-category-table-select="delete_selected" data-bs-toggle="tooltip"
                        title="Hapus yang dipilih">
                        <i class="ki-outline ki-trash fs-6 me-1"></i>
                        Hapus
                    </button>
                    {{-- end::Bulk Delete Action --}}

                    {{-- begin::Cancel Selection --}}
                    <button type="button" class="btn btn-sm btn-light" data-kt-category-table-select="cancel_selection" data-bs-toggle="tooltip" title="Batalkan pilihan">
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
                            <th width="40%">Nama</th>
                            <th width="20%">Tipe</th>
                            <th>Sub</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="w-10px pe-2"></th>
                            <th width="30px">#</th>
                            <th width="40%">Nama</th>
                            <th width="20%">Tipe</th>
                            <th>Sub</th>
                            <th>Status</th>
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
        // Global datatable variable
        var table;

        $(document).ready(function() {
            // Initialize Select2
            $('#q_type_kategori').select2();

            // Debug: Check if elements exist
            console.log('DataTable element:', $('#datatable').length);
            console.log('Processing indicator:', $('#datatable_processing').length);

            // Filter functions
            window.applyFilter = function() {
                var selectedType = document.getElementById('q_type_kategori').value;
                document.getElementById('filter-text').textContent = selectedType;
                console.log('Applying filter:', selectedType);

                // Show processing indicator
                $('#datatable_processing').show();

                // Reload table with new filter
                table.ajax.reload(function(json) {
                    $('#datatable_processing').hide();
                    $('#titleType').html(selectedType);
                    console.log('Table reloaded with filter:', selectedType);
                }, false);
            }

            window.resetFilter = function() {
                document.getElementById('q_type_kategori').value = 'Semua Data';
                document.getElementById('filter-text').textContent = 'Semua Data';
                console.log('Resetting filter to: Semua Data');

                // Show processing indicator
                $('#datatable_processing').show();

                // Reload table
                table.ajax.reload(function(json) {
                    $('#datatable_processing').hide();
                    $('#titleType').html('Semua Data');
                    console.log('Table reset to show all data');
                }, false);
            }

            // Handle filter change
            $('[name="q_type_kategori"]').change(function() {
                var q_type_kategori = $(this).val();
                console.log('Filter changed to:', q_type_kategori);

                // Show processing indicator
                $('#datatable_processing').show();

                // Clear table body and reload
                $('#datatable tbody').empty();
                table.ajax.reload(function(json) {
                    // Hide processing indicator after reload
                    $('#datatable_processing').hide();
                    console.log('Table reloaded after filter change');
                }, false); // false = don't reset paging

                $('#titleType').html(q_type_kategori);
            });

            // Initialize DataTable
            table = $('#datatable').DataTable({
                "processing": true,
                "serverSide": true,
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
                    [2, 'asc']
                ], // Order by nama (kolom ke-3, index 2)
                "dom": 'Bfrtip', // Include buttons in DOM
                "buttons": [], // Will be configured separately
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
                    "emptyTable": "Tidak ada data kategori",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ kategori",
                    "infoEmpty": "Menampilkan 0 sampai 0 dari 0 kategori",
                    "infoFiltered": "(disaring dari _MAX_ total kategori)",
                    "zeroRecords": "Tidak ditemukan data yang sesuai"
                },
                "ajax": {
                    "url": "{!! route('prt.apps.mst.tags.index') !!}",
                    "type": 'GET',
                    "data": function(data) {
                        data.filter = {
                            'type': $('[name="q_type_kategori"]').val() || 'Semua Data',
                        };
                        // Console log untuk debugging
                        console.log('DataTable request data:', data);
                    },
                    "dataSrc": function(json) {
                        // Console log untuk debugging response
                        console.log('DataTable response:', json);
                        return json.data;
                    },
                    "error": function(xhr, error, thrown) {
                        console.error('DataTable AJAX Error:', xhr.responseText);
                        console.error('Error:', error);
                        console.error('Thrown:', thrown);

                        // Hide processing indicator on error
                        $('#datatable_processing').hide();

                        // Show user-friendly error
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
                        exportable: false, // Don't include checkbox in export
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
                        data: 'nama',
                        name: 'nama',
                        title: 'Nama Kategori',
                        render: function(data, type, row) {
                            // For export, return plain text
                            if (type === 'export') {
                                return data;
                            }
                            // For display, return HTML
                            return data;
                        }
                    },
                    {
                        data: 'type',
                        name: 'type',
                        title: 'Tipe'
                    },
                    {
                        data: 'kategori_sub',
                        name: 'kategori_sub',
                        orderable: false,
                        searchable: false,
                        title: 'Sub Kategori'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false,
                        title: 'Status',
                        render: function(data, type, row) {
                            // For export, return plain text
                            if (type === 'export') {
                                return row.status_raw || 'Aktif'; // Assuming you pass status_raw from controller
                            }
                            // For display, return HTML
                            return data;
                        }
                    },
                    {
                        data: 'aksi',
                        name: 'aksi',
                        orderable: false,
                        searchable: false,
                        exportable: false, // Don't include actions in export
                        title: 'Aksi'
                    }
                ],
                "columnDefs": [{
                    className: "text-center",
                    targets: [0, 1, 4, 5, 6]
                }],
                "drawCallback": function(settings) {
                    // Hide processing indicator when draw is complete
                    $('#datatable_processing').hide();

                    // Reinitialize tooltips and other UI components after table redraw
                    if (typeof KTApp !== 'undefined' && KTApp.initBootstrapTooltips) {
                        KTApp.initBootstrapTooltips();
                    }

                    // Handle bulk actions visibility
                    handleBulkActions();

                    // Console log untuk debugging
                    console.log('DataTable draw completed, rows:', this.api().rows().count());
                },
                "preDrawCallback": function(settings) {
                    // Show processing indicator before draw
                    console.log('DataTable pre-draw started');
                },
                "initComplete": function(settings, json) {
                    // Hide processing indicator when initialization is complete
                    $('#datatable_processing').hide();
                    console.log('DataTable initialization completed');

                    // Handle initial bulk actions state
                    handleBulkActions();

                    // Initialize export buttons after DataTable is ready
                    initExportButtons();
                }
            });

            // Initialize export buttons
            function initExportButtons() {
                const documentTitle = 'Master Data Kategori';
                const currentFilter = $('[name="q_type_kategori"]').val() || 'Semua Data';

                // Create buttons instance
                var buttons = new $.fn.dataTable.Buttons(table, {
                    buttons: [{
                            extend: 'copyHtml5',
                            title: documentTitle + ' - ' + currentFilter,
                            exportOptions: {
                                columns: ':not([data-no-export])', // Exclude columns with data-no-export
                                modifier: {
                                    search: 'applied',
                                    order: 'applied'
                                }
                            }
                        },
                        {
                            extend: 'excelHtml5',
                            title: documentTitle + ' - ' + currentFilter,
                            filename: function() {
                                const date = new Date().toISOString().slice(0, 10);
                                const filter = currentFilter.toLowerCase().replace(/\s+/g, '-');
                                return `master-kategori-${filter}-${date}`;
                            },
                            exportOptions: {
                                columns: ':not([data-no-export])',
                                modifier: {
                                    search: 'applied',
                                    order: 'applied'
                                }
                            }
                        },
                        {
                            extend: 'csvHtml5',
                            title: documentTitle + ' - ' + currentFilter,
                            filename: function() {
                                const date = new Date().toISOString().slice(0, 10);
                                const filter = currentFilter.toLowerCase().replace(/\s+/g, '-');
                                return `master-kategori-${filter}-${date}`;
                            },
                            exportOptions: {
                                columns: ':not([data-no-export])',
                                modifier: {
                                    search: 'applied',
                                    order: 'applied'
                                }
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            title: documentTitle + ' - ' + currentFilter,
                            filename: function() {
                                const date = new Date().toISOString().slice(0, 10);
                                const filter = currentFilter.toLowerCase().replace(/\s+/g, '-');
                                return `master-kategori-${filter}-${date}`;
                            },
                            orientation: 'landscape',
                            pageSize: 'A4',
                            exportOptions: {
                                columns: ':not([data-no-export])',
                                modifier: {
                                    search: 'applied',
                                    order: 'applied'
                                }
                            },
                            customize: function(doc) {
                                // Customize PDF appearance
                                doc.content[1].table.widths = ['10%', '40%', '20%', '15%', '15%']; // Adjust column widths
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
                            // Update button titles with current filter before export
                            updateExportTitles();

                            // Show loading indicator
                            Swal.fire({
                                title: `Exporting ${exportValue.toUpperCase()}...`,
                                text: 'Mohon tunggu',
                                allowOutsideClick: false,
                                timer: 2000,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            // Trigger click event on hidden datatable export buttons
                            setTimeout(() => {
                                target.click();

                                // Show success message after a brief delay
                                setTimeout(() => {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Export Berhasil!',
                                        text: `Data berhasil di-export ke ${exportValue.toUpperCase()}`,
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                }, 500);
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

            // Update export button titles with current filter
            function updateExportTitles() {
                const currentFilter = $('[name="q_type_kategori"]').val() || 'Semua Data';
                const documentTitle = 'Master Data Kategori - ' + currentFilter;

                // Update all export buttons with current filter
                table.buttons().each(function(button, index) {
                    if (button.inst.s.buttons[index].title) {
                        button.inst.s.buttons[index].title = documentTitle;
                    }
                });
            }

            // Handle bulk actions
            function handleBulkActions() {
                const checkboxes = document.querySelectorAll('#datatable .row-checkbox');
                const bulkToolbar = document.querySelector('[data-kt-category-table-toolbar="selected"]');
                const countElement = document.querySelector('[data-kt-category-table-select="selected_count"]');

                let checkedCount = 0;
                checkboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        checkedCount++;
                    }
                });

                if (checkedCount > 0) {
                    countElement.textContent = checkedCount;
                    bulkToolbar.classList.remove('d-none');

                    // Initialize tooltips for bulk action buttons
                    setTimeout(() => {
                        if (typeof KTApp !== 'undefined' && KTApp.initBootstrapTooltips) {
                            KTApp.initBootstrapTooltips();
                        }
                    }, 100);
                } else {
                    bulkToolbar.classList.add('d-none');
                }
            }

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

            // Handle search
            const filterSearch = document.querySelector('[data-kt-category-table-filter="search"]');
            if (filterSearch) {
                filterSearch.addEventListener('keyup', function(e) {
                    table.search(e.target.value).draw();
                });
            }

            // Handle cancel selection
            $(document).on('click', '[data-kt-category-table-select="cancel_selection"]', function() {
                // Uncheck all checkboxes
                $('.row-checkbox').prop('checked', false);
                $('[data-kt-check="true"]').prop('checked', false);

                // Hide bulk toolbar
                const bulkToolbar = document.querySelector('[data-kt-category-table-toolbar="selected"]');
                bulkToolbar.classList.add('d-none');

                console.log('Selection cancelled');
            });

            // Handle bulk status - Activate
            $(document).on('click', '[data-kt-category-table-select="activate_selected"]', function() {
                handleBulkStatus('1', 'mengaktifkan');
            });

            // Handle bulk status - Deactivate
            $(document).on('click', '[data-kt-category-table-select="deactivate_selected"]', function() {
                handleBulkStatus('0', 'menonaktifkan');
            });

            // Bulk status handler function
            function handleBulkStatus(status, actionText) {
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
                const statusText = status === '1' ? 'aktif' : 'nonaktif';

                Swal.fire({
                    title: `${actionText.charAt(0).toUpperCase() + actionText.slice(1)} Status`,
                    text: `Apakah Anda yakin ingin ${actionText} ${selectedIds.length} kategori yang dipilih?`,
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonColor: status === '1' ? "#50cd89" : "#ffc700",
                    cancelButtonColor: "#7e8299",
                    confirmButtonText: `Ya, ${actionText}!`,
                    cancelButtonText: "Batal"
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        Swal.fire({
                            title: `${actionText.charAt(0).toUpperCase() + actionText.slice(1)} status...`,
                            text: 'Mohon tunggu',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: "{!! route('prt.apps.mst.tags.status.bulk') !!}",
                            type: 'POST',
                            data: {
                                uuids: selectedIds,
                                status: status,
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(res) {
                                $('#datatable').DataTable().ajax.reload(null, false);
                                const bulkToolbar = document.querySelector('[data-kt-category-table-toolbar="selected"]');
                                bulkToolbar.classList.add('d-none');

                                // Uncheck select all
                                $('[data-kt-check="true"]').prop('checked', false);

                                Swal.fire({
                                    title: "Success",
                                    text: res.message,
                                    icon: "success",
                                });
                            },
                            error: function(xhr) {
                                $('#datatable').DataTable().ajax.reload(null, false);
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

            // Handle bulk delete
            $(document).on('click', '[data-kt-category-table-select="delete_selected"]', function() {
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
                    text: `Apakah Anda yakin ingin menghapus ${selectedIds.length} kategori yang dipilih?`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#f1416c",
                    cancelButtonColor: "#7e8299",
                    confirmButtonText: "Ya, hapus semua!",
                    cancelButtonText: "Batal"
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        Swal.fire({
                            title: 'Menghapus data...',
                            text: 'Mohon tunggu',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: "{!! route('prt.apps.mst.tags.destroy.bulk') !!}",
                            type: 'POST',
                            data: {
                                uuids: selectedIds,
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(res) {
                                $('#datatable').DataTable().ajax.reload(null, false);
                                const bulkToolbar = document.querySelector('[data-kt-category-table-toolbar="selected"]');
                                bulkToolbar.classList.add('d-none');

                                // Uncheck select all
                                $('[data-kt-check="true"]').prop('checked', false);

                                Swal.fire({
                                    title: "Success",
                                    text: res.message,
                                    icon: "success",
                                });
                            },
                            error: function(xhr) {
                                $('#datatable').DataTable().ajax.reload(null, false);
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
                            url: "{!! route('prt.apps.mst.tags.destroy') !!}",
                            type: 'POST',
                            data: {
                                uuid: uuid,
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(res) {
                                $('#datatable').DataTable().ajax.reload(null, false);
                                Swal.fire({
                                    title: "Success",
                                    text: res.message,
                                    icon: "success",
                                });
                            },
                            error: function(xhr) {
                                $('#datatable').DataTable().ajax.reload(null, false);
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
                    url: "{!! route('prt.apps.mst.tags.status') !!}",
                    type: 'POST',
                    data: {
                        uuid: uuid,
                        status: status,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        $('#datatable').DataTable().ajax.reload();
                        Swal.fire({
                            title: "Success",
                            text: res.message,
                            icon: "success",
                        });
                    },
                    error: function(xhr) {
                        $('#datatable').DataTable().ajax.reload();
                        Swal.fire({
                            title: "Error",
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan',
                            icon: "error",
                        });
                    }
                });
            });

            // Update export buttons when filter changes
            $('[name="q_type_kategori"]').change(function() {
                setTimeout(() => {
                    if (table.buttons) {
                        updateExportTitles();
                    }
                }, 500);
            });
        });
    </script>
@endpush
{{-- SCRIPTS::END --}}
