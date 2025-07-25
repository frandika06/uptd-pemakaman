@push('styles')
    <style>
        #map {
            height: 300px;
            border-radius: 0.475rem;
            border: 1px solid #e4e6ea;
        }

        .form-control.is-invalid {
            border-color: #f1416c;
        }

        .fv-row .text-muted {
            font-size: 0.9rem;
        }

        .btn-primary .indicator-progress {
            display: none;
        }

        .btn-primary[data-kt-indicator="on"] .indicator-label {
            display: none;
        }

        .btn-primary[data-kt-indicator="on"] .indicator-progress {
            display: inline-flex;
            align-items: center;
        }

        /* Custom search box */
        .custom-search-box {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1000;
            background: white;
            padding: 10px;
            border-radius: 0.475rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            min-width: 250px;
        }

        .custom-search-results {
            max-height: 150px;
            overflow-y: auto;
            margin-top: 5px;
            border: 1px solid #e4e6ea;
            border-radius: 0.375rem;
            background: white;
        }

        .custom-search-results .search-result-item {
            padding: 8px 12px;
            cursor: pointer;
            border-bottom: 1px solid #f1f3f8;
            font-size: 13px;
        }

        .custom-search-results .search-result-item:hover {
            background-color: #f8f9fa;
        }

        .custom-search-results .search-result-item:last-child {
            border-bottom: none;
        }
    </style>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
@endpush

@extends('layouts.admin')

@section('title', $title)
@section('description', '{{ $title }}')

@section('toolbar')
    <div class="d-flex flex-stack flex-row-fluid">
        <div class="d-flex flex-column flex-row-fluid">
            <div class="page-title d-flex align-items-center me-3">
                <h1 class="page-heading d-flex flex-column justify-content-center text-dark fw-bold fs-lg-2x gap-2">
                    <span>{{ $title }}</span>
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
                <li class="breadcrumb-item text-gray-700">
                    <a href="{{ route('tpu.lahan.index') }}" class="text-gray-700 text-hover-primary">Data Lahan</a>
                </li>
                <li class="breadcrumb-item">
                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                </li>
                <li class="breadcrumb-item text-gray-700">{{ isset($data) ? 'Edit' : 'Tambah' }}</li>
            </ul>
        </div>
    </div>
@endsection

@section('content')
    <form id="kt_lahan_form" class="form d-flex flex-column flex-lg-row" action="{{ isset($data) ? route('tpu.lahan.update', $uuid_enc) : route('tpu.lahan.store') }}" method="POST">
        @csrf
        @isset($data)
            @method('PUT')
        @endisset

        <!-- begin::Aside column -->
        <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
            <!-- begin::Information settings -->
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Informasi</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="d-flex flex-column gap-5">
                        <div class="m-0 p-0">
                            <span class="fw-bold text-gray-600">Dibuat:</span><br />
                            <span class="text-gray-800 fw-bold">
                                {{ isset($data) ? $data->created_at->format('d M Y H:i') : 'Akan diatur otomatis' }}
                            </span>
                        </div>
                        @isset($data)
                            <div class="m-0 p-0">
                                <span class="fw-bold text-gray-600">Diperbarui:</span><br />
                                <span class="text-gray-800 fw-bold">{{ $data->updated_at->format('d M Y H:i') }}</span>
                            </div>
                        @endisset
                    </div>
                </div>
            </div>
            <!-- end::Information settings -->
        </div>
        <!-- end::Aside column -->

        <!-- begin::Main column -->
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <!-- begin::General options -->
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Informasi Umum</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <!-- begin::Input group - TPU -->
                    <div class="mb-10 fv-row">
                        <label class="required form-label">TPU</label>
                        <select class="form-select mb-2 @error('uuid_tpu') is-invalid @enderror" data-control="select2" data-placeholder="Pilih TPU" name="uuid_tpu" id="kt_lahan_tpu"
                            required>
                            <option value="" disabled {{ !old('uuid_tpu') && !isset($data) ? 'selected' : '' }}>Pilih TPU</option>
                            @foreach ($tpus as $tpu)
                                <option value="{{ $tpu->uuid }}" {{ old('uuid_tpu', isset($data) ? $data->uuid_tpu : '') === $tpu->uuid ? 'selected' : '' }}>
                                    {{ $tpu->nama }}
                                </option>
                            @endforeach
                        </select>
                        <div class="text-muted fs-7">Pilih TPU tempat lahan berada.</div>
                        @error('uuid_tpu')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- end::Input group - TPU -->

                    <!-- begin::Input group - Kode Lahan -->
                    <div class="mb-10 fv-row">
                        <label class="required form-label">Kode Lahan</label>
                        <input type="text" name="kode_lahan" class="form-control mb-2 @error('kode_lahan') is-invalid @enderror" placeholder="Masukkan kode lahan"
                            value="{{ old('kode_lahan', isset($data) ? $data->kode_lahan : '') }}" maxlength="255" autocomplete="off" required />
                        <div class="text-muted fs-7">Kode unik untuk mengidentifikasi lahan.</div>
                        @error('kode_lahan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- end::Input group - Kode Lahan -->

                    <!-- begin::Input group - Luas -->
                    <div class="mb-10 fv-row">
                        <label class="required form-label">Luas Lahan (m²)</label>
                        <input type="number" name="luas_m2" class="form-control mb-2 @error('luas_m2') is-invalid @enderror" placeholder="Masukkan luas lahan"
                            value="{{ old('luas_m2', isset($data) ? $data->luas_m2 : '') }}" step="0.01" min="0" autocomplete="off" required />
                        <div class="text-muted fs-7">Luas lahan dalam meter persegi.</div>
                        @error('luas_m2')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- end::Input group - Luas -->
                </div>
            </div>
            <!-- end::General options -->

            <!-- begin::Koordinat -->
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Koordinat Lokasi</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <!-- begin::Map -->
                    <div style="position: relative;">
                        <div id="map"></div>
                        <!-- Custom Search Box -->
                        <div class="custom-search-box">
                            <div class="input-group input-group-sm">
                                <input type="text" id="location-search" class="form-control form-control-sm" placeholder="Cari lokasi di Tangerang..." />
                                <button class="btn btn-primary btn-sm" type="button" id="search-btn">
                                    <i class="ki-outline ki-magnifier fs-4"></i>
                                </button>
                            </div>
                            <div id="search-results" class="custom-search-results" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="text-muted fs-7 mt-3">
                        Klik pada peta atau gunakan pencarian untuk memilih lokasi di Kabupaten Tangerang.
                    </div>
                    <!-- end::Map -->

                    <div class="row mt-5">
                        <!-- begin::Input group - Latitude -->
                        <div class="col-md-6">
                            <div class="mb-10 fv-row">
                                <label class="form-label">Latitude</label>
                                <input type="number" name="latitude" class="form-control mb-2 @error('latitude') is-invalid @enderror" placeholder="Contoh: -6.200000"
                                    value="{{ old('latitude', isset($data) ? $data->latitude : '') }}" step="0.0000001" min="-90" max="90" autocomplete="off" />
                                <div class="text-muted fs-7">Koordinat latitude (-90 sampai 90).</div>
                                @error('latitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!-- end::Input group - Latitude -->

                        <!-- begin::Input group - Longitude -->
                        <div class="col-md-6">
                            <div class="mb-10 fv-row">
                                <label class="form-label">Longitude</label>
                                <input type="number" name="longitude" class="form-control mb-2 @error('longitude') is-invalid @enderror" placeholder="Contoh: 106.816666"
                                    value="{{ old('longitude', isset($data) ? $data->longitude : '') }}" step="0.0000001" min="-180" max="180" autocomplete="off" />
                                <div class="text-muted fs-7">Koordinat longitude (-180 sampai 180).</div>
                                @error('longitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!-- end::Input group - Longitude -->
                    </div>

                    <!-- begin::Current Location Button -->
                    <div class="mb-10">
                        <button type="button" id="btn-get-location" class="btn btn-light-primary btn-sm">
                            <i class="ki-outline ki-geolocation fs-4"></i>Dapatkan Lokasi Saya
                        </button>
                        <div class="text-muted fs-7 mt-2">
                            Klik tombol di atas untuk menggunakan lokasi perangkat Anda saat ini.
                        </div>
                    </div>
                    <!-- end::Current Location Button -->
                </div>
            </div>
            <!-- end::Koordinat -->

            <!-- begin::Catatan -->
            <div class="card card-flush py-4">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Catatan</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <!-- begin::Input group - Catatan -->
                    <div class="mb-10 fv-row">
                        <label class="form-label">Catatan</label>
                        <textarea name="catatan" class="form-control mb-2 @error('catatan') is-invalid @enderror" rows="4"
                            placeholder="Masukkan catatan atau keterangan tambahan tentang lahan...">{{ old('catatan', isset($data) ? $data->catatan : '') }}</textarea>
                        <div class="text-muted fs-7">Catatan atau keterangan tambahan mengenai lahan (opsional).</div>
                        @error('catatan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- end::Input group - Catatan -->
                </div>
            </div>
            <!-- end::Catatan -->

            <!-- begin::Actions -->
            <div class="d-flex justify-content-end">
                <a href="{{ route('tpu.lahan.index') }}" id="kt_lahan_cancel" class="btn btn-light me-5">
                    <i class="ki-outline ki-arrow-left fs-2"></i>Batal
                </a>
                <button type="submit" id="kt_lahan_submit" class="btn btn-primary">
                    <span class="indicator-label">
                        <i class="fa-solid fa-save me-2"></i>{{ isset($data) ? 'Perbarui' : 'Simpan' }}
                    </span>
                    <span class="indicator-progress">
                        Please wait...
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                    </span>
                </button>
            </div>
            <!-- end::Actions -->
        </div>
        <!-- end::Main column -->
    </form>
@endsection

@push('scripts')
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        "use strict";

        document.addEventListener('DOMContentLoaded', function() {
            var KTLahanForm = function() {
                var form;
                var submitButton;
                var cancelButton;
                var validator;
                var map;
                var marker;
                var searchTimeout;

                var initForm = function() {
                    form = document.querySelector('#kt_lahan_form');
                    submitButton = document.querySelector('#kt_lahan_submit');
                    cancelButton = document.querySelector('#kt_lahan_cancel');

                    console.log('Form initialized:', {
                        form: !!form,
                        submitButton: !!submitButton,
                        cancelButton: !!cancelButton
                    });
                }

                var initValidation = function() {
                    // Simple validation without FormValidation dependency
                    validator = {
                        validate: function() {
                            return new Promise(function(resolve) {
                                var isValid = true;
                                var errors = [];

                                // Validate TPU
                                var tpuSelect = document.querySelector('select[name="uuid_tpu"]');
                                if (!tpuSelect.value) {
                                    isValid = false;
                                    errors.push('TPU harus dipilih');
                                    tpuSelect.classList.add('is-invalid');
                                } else {
                                    tpuSelect.classList.remove('is-invalid');
                                }

                                // Validate Kode Lahan
                                var kodeLahanInput = document.querySelector('input[name="kode_lahan"]');
                                if (!kodeLahanInput.value.trim()) {
                                    isValid = false;
                                    errors.push('Kode lahan harus diisi');
                                    kodeLahanInput.classList.add('is-invalid');
                                } else {
                                    kodeLahanInput.classList.remove('is-invalid');
                                }

                                // Validate Luas
                                var luasInput = document.querySelector('input[name="luas_m2"]');
                                var luasValue = parseFloat(luasInput.value);
                                if (!luasInput.value || isNaN(luasValue) || luasValue <= 0) {
                                    isValid = false;
                                    errors.push('Luas lahan harus diisi dan lebih dari 0');
                                    luasInput.classList.add('is-invalid');
                                } else {
                                    luasInput.classList.remove('is-invalid');
                                }

                                // Validate Latitude (optional)
                                var latInput = document.querySelector('input[name="latitude"]');
                                if (latInput.value) {
                                    var latValue = parseFloat(latInput.value);
                                    if (isNaN(latValue) || latValue < -90 || latValue > 90) {
                                        isValid = false;
                                        errors.push('Latitude harus antara -90 dan 90');
                                        latInput.classList.add('is-invalid');
                                    } else {
                                        latInput.classList.remove('is-invalid');
                                    }
                                }

                                // Validate Longitude (optional)
                                var lngInput = document.querySelector('input[name="longitude"]');
                                if (lngInput.value) {
                                    var lngValue = parseFloat(lngInput.value);
                                    if (isNaN(lngValue) || lngValue < -180 || lngValue > 180) {
                                        isValid = false;
                                        errors.push('Longitude harus antara -180 dan 180');
                                        lngInput.classList.add('is-invalid');
                                    } else {
                                        lngInput.classList.remove('is-invalid');
                                    }
                                }

                                if (!isValid && errors.length > 0) {
                                    showError('Validasi gagal: ' + errors.join(', '));
                                }

                                resolve(isValid ? 'Valid' : 'Invalid');
                            });
                        }
                    };
                }

                var initMap = function() {
                    if (typeof L === 'undefined' || !L.map) {
                        console.error('Leaflet library not loaded. Check network or local files.');
                        return;
                    }

                    try {
                        // Initialize map centered on Kabupaten Tangerang
                        map = L.map('map', {
                            preferCanvas: true
                        }).setView([-6.2, 106.46667], 11);

                        // Add OpenStreetMap tiles
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                            maxZoom: 18,
                            minZoom: 10
                        }).addTo(map);

                        // Set bounds to Kabupaten Tangerang area
                        var bounds = [
                            [-6.4, 106.2], // Southwest
                            [-6.0, 106.8] // Northeast
                        ];
                        map.setMaxBounds(bounds);

                        // Initialize marker
                        var defaultLat = parseFloat(document.querySelector('input[name="latitude"]').value) || -6.2;
                        var defaultLng = parseFloat(document.querySelector('input[name="longitude"]').value) || 106.46667;

                        marker = L.marker([defaultLat, defaultLng], {
                            draggable: true
                        }).addTo(map);

                        // Center map on marker if coordinates exist
                        if (document.querySelector('input[name="latitude"]').value && document.querySelector('input[name="longitude"]').value) {
                            map.setView([defaultLat, defaultLng], 15);
                        }

                        // Handle marker drag
                        marker.on('dragend', function(e) {
                            var position = marker.getLatLng();
                            updateCoordinateInputs(position.lat, position.lng);
                        });

                        // Handle map click
                        map.on('click', function(e) {
                            if (isWithinBounds(e.latlng.lat, e.latlng.lng)) {
                                marker.setLatLng(e.latlng);
                                updateCoordinateInputs(e.latlng.lat, e.latlng.lng);
                            } else {
                                showError('Lokasi di luar wilayah Kabupaten Tangerang.');
                            }
                        });

                        // Listen to coordinate input changes
                        document.querySelector('input[name="latitude"]').addEventListener('input', updateMarkerFromInputs);
                        document.querySelector('input[name="longitude"]').addEventListener('input', updateMarkerFromInputs);

                        console.log('Map initialized successfully');
                    } catch (error) {
                        console.error('Error initializing map:', error);
                    }
                }

                var updateCoordinateInputs = function(lat, lng) {
                    document.querySelector('input[name="latitude"]').value = lat.toFixed(7);
                    document.querySelector('input[name="longitude"]').value = lng.toFixed(7);
                }

                var updateMarkerFromInputs = function() {
                    var lat = parseFloat(document.querySelector('input[name="latitude"]').value);
                    var lng = parseFloat(document.querySelector('input[name="longitude"]').value);

                    if (!isNaN(lat) && !isNaN(lng) && isWithinBounds(lat, lng)) {
                        if (marker && map) {
                            marker.setLatLng([lat, lng]);
                            map.setView([lat, lng], 15);
                        }
                    }
                }

                var isWithinBounds = function(lat, lng) {
                    return lat >= -6.4 && lat <= -6.0 && lng >= 106.2 && lng <= 106.8;
                }

                var initSearch = function() {
                    var searchInput = document.getElementById('location-search');
                    var searchBtn = document.getElementById('search-btn');
                    var searchResults = document.getElementById('search-results');

                    if (!searchInput || !searchBtn || !searchResults) {
                        console.warn('Search elements not found');
                        return;
                    }

                    // Auto search on input
                    searchInput.addEventListener('input', function() {
                        clearTimeout(searchTimeout);
                        var query = this.value.trim();

                        if (query.length < 3) {
                            searchResults.style.display = 'none';
                            return;
                        }

                        searchTimeout = setTimeout(function() {
                            performSearch(query);
                        }, 500);
                    });

                    // Search on button click
                    searchBtn.addEventListener('click', function() {
                        var query = searchInput.value.trim();
                        if (query.length >= 3) {
                            performSearch(query);
                        }
                    });

                    // Search on Enter key
                    searchInput.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            var query = this.value.trim();
                            if (query.length >= 3) {
                                performSearch(query);
                            }
                        }
                    });
                }

                var performSearch = function(query) {
                    var searchResults = document.getElementById('search-results');
                    var searchBtn = document.getElementById('search-btn');

                    if (!searchResults || !searchBtn) return;

                    // Show loading
                    searchBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
                    searchResults.innerHTML = '<div class="search-result-item">Mencari...</div>';
                    searchResults.style.display = 'block';

                    // Make API request
                    var url = "{{ route('api.proxy-nominatim') }}?q=" + encodeURIComponent(query + " Kabupaten Tangerang");

                    fetch(url)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            searchBtn.innerHTML = '<i class="ki-outline ki-magnifier fs-4"></i>';
                            displaySearchResults(data);
                        })
                        .catch(error => {
                            console.error('Search error:', error);
                            searchBtn.innerHTML = '<i class="ki-outline ki-magnifier fs-4"></i>';
                            searchResults.innerHTML = '<div class="search-result-item text-danger">Gagal mencari lokasi. Silakan coba lagi.</div>';
                        });
                }

                var displaySearchResults = function(results) {
                    var searchResults = document.getElementById('search-results');
                    if (!searchResults) return;

                    if (!results || results.length === 0) {
                        searchResults.innerHTML = '<div class="search-result-item text-muted">Tidak ada hasil ditemukan.</div>';
                        return;
                    }

                    var html = '';
                    results.slice(0, 5).forEach(function(result) {
                        var lat = parseFloat(result.lat);
                        var lng = parseFloat(result.lon);

                        // Only show results within Kabupaten Tangerang bounds
                        if (isWithinBounds(lat, lng)) {
                            html += '<div class="search-result-item" data-lat="' + lat + '" data-lng="' + lng + '">' +
                                '<strong>' + truncateText(result.display_name.split(',')[0], 30) + '</strong><br>' +
                                '<small class="text-muted">' + truncateText(result.display_name, 60) + '</small>' +
                                '</div>';
                        }
                    });

                    if (html === '') {
                        html = '<div class="search-result-item text-muted">Tidak ada hasil dalam wilayah Kabupaten Tangerang.</div>';
                    }

                    searchResults.innerHTML = html;

                    // Add click handlers to search results
                    searchResults.querySelectorAll('.search-result-item[data-lat]').forEach(function(item) {
                        item.addEventListener('click', function() {
                            var lat = parseFloat(this.dataset.lat);
                            var lng = parseFloat(this.dataset.lng);

                            if (marker && map) {
                                marker.setLatLng([lat, lng]);
                                map.setView([lat, lng], 16);
                                updateCoordinateInputs(lat, lng);
                            }

                            searchResults.style.display = 'none';
                            document.getElementById('location-search').value = this.querySelector('strong').textContent;
                        });
                    });
                }

                var truncateText = function(text, maxLength) {
                    return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
                }

                var showError = function(message) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: message,
                            timer: 3000,
                            showConfirmButton: false
                        });
                    } else {
                        alert(message);
                    }
                }

                var showSuccess = function(message) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert(message);
                    }
                }

                var handleSubmit = function() {
                    // Remove any existing event listeners
                    submitButton.onclick = null;

                    submitButton.addEventListener('click', function(e) {
                        e.preventDefault();
                        console.log('Submit button clicked');

                        if (validator) {
                            validator.validate().then(function(status) {
                                console.log('Validation status:', status);
                                if (status === 'Valid') {
                                    console.log('Form is valid, submitting...');
                                    submitButton.setAttribute('data-kt-indicator', 'on');
                                    submitButton.disabled = true;
                                    form.submit();
                                } else {
                                    console.log('Form validation failed');
                                }
                            });
                        } else {
                            console.log('No validator, submitting directly...');
                            // Fallback if validator is not available
                            submitButton.setAttribute('data-kt-indicator', 'on');
                            submitButton.disabled = true;
                            form.submit();
                        }
                    });
                }

                var handleCancel = function() {
                    if (!cancelButton) return;

                    cancelButton.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                text: "Apakah Anda yakin ingin membatalkan?",
                                icon: "warning",
                                showCancelButton: true,
                                buttonsStyling: false,
                                confirmButtonText: "Ya, batalkan!",
                                cancelButtonText: "Tidak",
                                customClass: {
                                    confirmButton: "btn btn-primary",
                                    cancelButton: "btn btn-active-light"
                                }
                            }).then(function(result) {
                                if (result.value) {
                                    window.location = cancelButton.getAttribute('href');
                                }
                            });
                        } else {
                            if (confirm("Apakah Anda yakin ingin membatalkan?")) {
                                window.location = cancelButton.getAttribute('href');
                            }
                        }
                    });
                }

                var handleGeolocation = function() {
                    const getLocationButton = document.querySelector('#btn-get-location');
                    if (!getLocationButton) return;

                    getLocationButton.addEventListener('click', function() {
                        if (navigator.geolocation) {
                            getLocationButton.innerHTML =
                                '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mendapatkan lokasi...';
                            getLocationButton.disabled = true;

                            navigator.geolocation.getCurrentPosition(
                                function(position) {
                                    var lat = position.coords.latitude;
                                    var lng = position.coords.longitude;

                                    if (isWithinBounds(lat, lng)) {
                                        if (marker && map) {
                                            marker.setLatLng([lat, lng]);
                                            map.setView([lat, lng], 15);
                                            updateCoordinateInputs(lat, lng);
                                        }

                                        getLocationButton.innerHTML = '<i class="ki-outline ki-geolocation fs-4"></i>Dapatkan Lokasi Saya';
                                        getLocationButton.disabled = false;
                                        showSuccess('Lokasi berhasil didapatkan.');
                                    } else {
                                        getLocationButton.innerHTML = '<i class="ki-outline ki-geolocation fs-4"></i>Dapatkan Lokasi Saya';
                                        getLocationButton.disabled = false;
                                        showError('Lokasi perangkat Anda berada di luar wilayah Kabupaten Tangerang.');
                                    }
                                },
                                function(error) {
                                    getLocationButton.innerHTML = '<i class="ki-outline ki-geolocation fs-4"></i>Dapatkan Lokasi Saya';
                                    getLocationButton.disabled = false;

                                    let errorMessage = 'Gagal mendapatkan lokasi. ';
                                    switch (error.code) {
                                        case error.PERMISSION_DENIED:
                                            errorMessage += 'Akses lokasi ditolak oleh pengguna.';
                                            break;
                                        case error.POSITION_UNAVAILABLE:
                                            errorMessage += 'Informasi lokasi tidak tersedia.';
                                            break;
                                        case error.TIMEOUT:
                                            errorMessage += 'Permintaan lokasi timeout.';
                                            break;
                                        default:
                                            errorMessage += 'Terjadi kesalahan yang tidak diketahui.';
                                            break;
                                    }
                                    showError(errorMessage);
                                }, {
                                    enableHighAccuracy: true,
                                    timeout: 10000,
                                    maximumAge: 0
                                }
                            );
                        } else {
                            showError('Geolocation tidak didukung oleh browser ini.');
                        }
                    });
                }

                // Hide search results when clicking outside
                var handleClickOutside = function() {
                    document.addEventListener('click', function(e) {
                        var searchResults = document.getElementById('search-results');
                        var searchBox = document.querySelector('.custom-search-box');

                        if (searchResults && searchBox && !searchBox.contains(e.target)) {
                            searchResults.style.display = 'none';
                        }
                    });
                }

                return {
                    init: function() {
                        try {
                            console.log('Initializing KTLahanForm...');
                            initForm();
                            initValidation();
                            initMap();
                            initSearch();
                            handleSubmit();
                            handleCancel();
                            handleGeolocation();
                            handleClickOutside();
                            console.log('KTLahanForm initialized successfully');
                        } catch (error) {
                            console.error('Error initializing KTLahanForm:', error);
                        }
                    }
                };
            }();

            // Initialize the form
            KTLahanForm.init();
        });
    </script>
@endpush
