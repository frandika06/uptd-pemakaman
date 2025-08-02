<?php
$profile = \Helper::DataPP();
$auth = \Auth::user();
?>

@extends('layouts.admin')

{{-- SEO::BEGIN --}}
@section('title', 'Dashboard TPU')
{{-- SEO::END --}}

{{-- TOOLBAR::BEGIN --}}
@section('toolbar')
    <div class="d-flex flex-column flex-row-fluid">
        <div class="page-title d-flex align-items-center me-3">
            <h1 class="page-heading d-flex flex-column justify-content-center text-dark fw-bold fs-lg-2x gap-2">
                <span>Dashboard TPU</span>
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
            <li class="breadcrumb-item text-gray-700">TPU</li>
            <li class="breadcrumb-item">
                <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
            </li>
            <li class="breadcrumb-item text-gray-700">{{ $data['tpu']->nama ?? 'Dashboard' }}</li>
        </ul>
    </div>
    @if (isset($data['tpu']))
        <div class="d-flex align-items-center">
            <span class="badge badge-light-primary fs-7">{{ $data['tpu']->nama }}</span>
        </div>
    @endif
@endsection
{{-- TOOLBAR::END --}}

{{-- CONTENT::BEGIN --}}
@section('content')
    <div class="d-flex flex-column gap-7 gap-lg-10">
        {{-- Error Message --}}
        @if (isset($error))
            <div class="alert alert-warning d-flex align-items-center p-5">
                <i class="ki-outline ki-shield-cross fs-2hx text-warning me-4"></i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-warning">Terjadi Kesalahan</h4>
                    <span>{{ $error }}</span>
                </div>
            </div>
        @endif

        {{-- No TPU Access Message --}}
        @if (isset($data['no_tpu_access']) && $data['no_tpu_access'])
            <div class="card h-lg-100">
                <div class="card-body text-center py-10">
                    <i class="ki-outline ki-information-2 fs-3x text-warning mb-5"></i>
                    <h3 class="text-gray-800 fw-bold mb-3">Tidak Ada Akses TPU</h3>
                    <p class="text-gray-600 fs-6 mb-5">
                        Anda belum memiliki akses ke TPU manapun. Silakan hubungi administrator untuk mendapatkan akses.
                    </p>
                    <a href="{{ route('tpu.petugas.index') }}" class="btn btn-primary">
                        <i class="ki-outline ki-user fs-2"></i> Kelola Petugas
                    </a>
                </div>
            </div>
        @else
            {{-- Statistik Utama --}}
            <div class="row g-5 g-xl-10">
                @if (in_array($role, ['Super Admin', 'Admin']))
                    <div class="col-sm-6 col-xl-4">
                        <div class="card h-lg-100">
                            <div class="card-body d-flex justify-content-between align-items-start flex-column">
                                <div class="m-0">
                                    <i class="fa-solid fa-signs-post fs-2hx text-gray-600"></i>
                                </div>
                                <div class="d-flex flex-column my-7">
                                    <span class="fw-semibold fs-3x text-gray-800 lh-1 ls-n2">{{ $data['overview']['total_tpu'] ?? 0 }}</span>
                                    <div class="m-0">
                                        <span class="fw-semibold fs-6 text-gray-400">Total TPU</span>
                                    </div>
                                </div>
                                <span class="badge badge-light-success fs-base">
                                    <i class="ki-outline ki-arrow-up fs-5 text-success ms-n1"></i>
                                    {{ $data['overview']['tpu_aktif'] ?? 0 }} Aktif
                                </span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="col-sm-6 col-xl-4">
                        <div class="card h-lg-100">
                            <div class="card-body d-flex justify-content-between align-items-start flex-column">
                                <div class="m-0">
                                    <i class="fa-solid fa-signs-post fs-2hx text-gray-600"></i>
                                </div>
                                <div class="d-flex flex-column my-7">
                                    <span class="fw-semibold fs-3x text-gray-800 lh-1 ls-n2">{{ $data['overview']['total_tpu'] ?? 0 }}</span>
                                    <div class="m-0">
                                        <span class="fw-semibold fs-6 text-gray-400">Total TPU</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="col-sm-6 col-xl-4">
                    <div class="card h-lg-100">
                        <div class="card-body d-flex justify-content-between align-items-start flex-column">
                            <div class="m-0">
                                <i class="ki-outline ki-map fs-2hx text-gray-600"></i>
                            </div>
                            <div class="d-flex flex-column my-7">
                                <span class="fw-semibold fs-3x text-gray-800 lh-1 ls-n2">{{ $data['overview']['total_lahan'] ?? 0 }}</span>
                                <div class="m-0">
                                    <span class="fw-semibold fs-6 text-gray-400">Total Lahan</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-4">
                    <div class="card h-lg-100">
                        <div class="card-body d-flex justify-content-between align-items-start flex-column">
                            <div class="m-0">
                                <i class="fa fa-ribbon fs-2hx text-gray-600"></i>
                            </div>
                            <div class="d-flex flex-column my-7">
                                <span class="fw-semibold fs-3x text-gray-800 lh-1 ls-n2">{{ $data['overview']['total_makam'] ?? 0 }}</span>
                                <div class="m-0">
                                    <span class="fw-semibold fs-6 text-gray-400">Total Makam</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-4">
                    <div class="card h-lg-100">
                        <div class="card-body d-flex justify-content-between align-items-start flex-column">
                            <div class="m-0">
                                <i class="ki-outline ki-user fs-2hx text-gray-600"></i>
                            </div>
                            <div class="d-flex flex-column my-7">
                                <span class="fw-semibold fs-3x text-gray-800 lh-1 ls-n2">{{ $data['overview']['total_petugas'] ?? 0 }}</span>
                                <div class="m-0">
                                    <span class="fw-semibold fs-6 text-gray-400">Total Petugas</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-4">
                    <div class="card h-lg-100">
                        <div class="card-body d-flex justify-content-between align-items-start flex-column">
                            <div class="m-0">
                                <i class="ki-outline ki-cube-2 fs-2hx text-gray-600"></i>
                            </div>
                            <div class="d-flex flex-column my-7">
                                <span class="fw-semibold fs-3x text-gray-800 lh-1 ls-n2">{{ $data['overview']['total_sarpras'] ?? 0 }}</span>
                                <div class="m-0">
                                    <span class="fw-semibold fs-6 text-gray-400">Total Sarpras</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-4">
                    <div class="card h-lg-100">
                        <div class="card-body d-flex justify-content-between align-items-start flex-column">
                            <div class="m-0">
                                <i class="ki-outline ki-chart-pie fs-2hx text-gray-600"></i>
                            </div>
                            <div class="d-flex flex-column my-7">
                                <span class="fw-semibold fs-3x text-gray-800 lh-1 ls-n2">{{ $data['kapasitas']['persentase_kapasitas'] ?? 0 }}%</span>
                                <div class="m-0">
                                    <span class="fw-semibold fs-6 text-gray-400">Kapasitas Makam</span>
                                </div>
                            </div>
                            <div class="w-100">
                                <div class="progress h-6px bg-light-primary">
                                    <div class="progress-bar bg-primary" style="width: {{ $data['kapasitas']['persentase_kapasitas'] ?? 0 }}%"></div>
                                </div>
                                <div class="text-gray-600 fs-8 mt-2">
                                    Terisi: <span class="fw-bold">{{ \Helper::toDot($data['kapasitas']['makam_terisi']) ?? 0 }}</span> |
                                    Sisa: <span class="fw-bold">{{ \Helper::toDot($data['kapasitas']['sisa_kapasitas']) ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Activities --}}
            <div class="row g-5 g-xl-10">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="fw-bold text-dark fs-2">Aktivitas Terbaru</span>
                                <span class="text-muted fw-semibold fs-7">5 makam terbaru yang diinput</span>
                            </h3>
                        </div>
                        <div class="card-body pt-6">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-3">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                            @if (in_array($role, ['Super Admin', 'Admin']))
                                                <th>TPU</th>
                                            @endif
                                            <th>Lahan</th>
                                            <th>Status</th>
                                            <th>Luas (m²)</th>
                                            <th>Kapasitas</th>
                                            <th>Waktu</th>
                                        </tr>
                                    </thead>
                                    <tbody class="fw-semibold text-gray-600">
                                        @forelse($data['recent_makam'] ?? [] as $makam)
                                            <tr>
                                                @if (in_array($role, ['Super Admin', 'Admin']))
                                                    <td>{{ $data['is_filtered'] ? $data['tpu']->nama ?? 'N/A' : $makam->Lahan->Tpu->nama ?? 'N/A' }}</td>
                                                @endif
                                                <td>{{ $makam->Lahan->kode_lahan ?? 'N/A' }}</td>
                                                <td>
                                                    @php
                                                        $statusBadge = 'primary';
                                                        switch (strtolower($makam->status_makam)) {
                                                            case 'kosong':
                                                                $statusBadge = 'success';
                                                                break;
                                                            case 'terisi':
                                                            case 'terisi sebagian':
                                                                $statusBadge = 'warning';
                                                                break;
                                                            case 'penuh':
                                                                $statusBadge = 'danger';
                                                                break;
                                                            case 'rusak':
                                                                $statusBadge = 'dark';
                                                                break;
                                                        }
                                                    @endphp
                                                    <span class="badge badge-light-{{ $statusBadge }}">
                                                        {{ $makam->status_makam ?? 'Unknown' }}
                                                    </span>
                                                </td>
                                                <td>{{ number_format($makam->luas_m2 ?? 0, 2) }}</td>
                                                <td>{{ $makam->kapasitas ?? 0 }} / {{ $makam->makam_terisi ?? 0 }}</td>
                                                <td>{{ \Carbon\Carbon::parse($makam->created_at)->format('d/m/Y H:i') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ in_array($role, ['Super Admin', 'Admin']) ? '6' : '5' }}" class="text-center py-10">
                                                    <span class="text-muted">Tidak ada aktivitas terbaru</span>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
{{-- CONTENT::END --}}

@section('scripts')
    <script>
        $(document).ready(function() {
            console.log('Dashboard TPU loaded successfully');
        });
    </script>
@endsection
