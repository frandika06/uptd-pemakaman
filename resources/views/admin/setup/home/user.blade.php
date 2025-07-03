<?php
$profile = \Helper::DataPP();
$auth = \Auth::user();
?>

@extends('layouts.admin')

{{-- SEO::BEGIN --}}
@section('title', 'Dashboard Pengaturan')
{{-- SEO::END --}}

{{-- TOOLBAR::BEGIN --}}
@section('toolbar')
    {{-- begin::Page title --}}
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        {{-- begin::Title --}}
        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Dashboard Pengaturan</h1>
        {{-- end::Title --}}
        {{-- begin::Breadcrumb --}}
        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
            <li class="breadcrumb-item text-dark">
                <a href="{{ route('auth.home') }}" class="text-dark text-hover-primary">Home</a>
            </li>
            <li class="breadcrumb-item">
                <span class="bullet bg-dark w-5px h-2px"></span>
            </li>
            <li class="breadcrumb-item text-dark">Pengaturan</li>
            <li class="breadcrumb-item">
                <span class="bullet bg-dark w-5px h-2px"></span>
            </li>
            <li class="breadcrumb-item text-dark">Dashboard</li>
        </ul>
        {{-- end::Breadcrumb --}}
    </div>
    {{-- end::Page title --}}
@endsection
{{-- TOOLBAR::END --}}

{{-- CONTENT::BEGIN --}}
@section('content')
    {{-- begin::Container --}}
    <div class="d-flex flex-column gap-7 gap-lg-10">

        {{-- begin::Welcome Section --}}
        <div class="row g-5 g-xl-10">
            <div class="col-12">
                <div class="card h-lg-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h3 class="text-white fw-bold mb-3">Selamat Datang, {{ $auth->nama }}!</h3>
                            <p class="text-white opacity-75 mb-4">
                                Anda login sebagai <strong>{{ $auth->role }}</strong>.
                                @if ($data['personal']['last_login'])
                                    Login terakhir: {{ \Carbon\Carbon::parse($data['personal']['last_login']->created_at)->format('d/m/Y H:i') }}
                                @endif
                            </p>
                            <div class="d-flex align-items-center">
                                <div class="border border-white border-dashed rounded py-2 px-4 me-3">
                                    <span class="fs-6 text-white fw-bold">{{ $data['personal']['login_count_30days'] }}</span>
                                    <span class="fs-7 text-white opacity-75 d-block">Login (30 hari)</span>
                                </div>
                                @if (in_array($auth->role, ['Editor', 'Penulis', 'Kontributor']))
                                    <div class="border border-white border-dashed rounded py-2 px-4 me-3">
                                        <span class="fs-6 text-white fw-bold">{{ $data['content']['my_posts'] }}</span>
                                        <span class="fs-7 text-white opacity-75 d-block">Konten Saya</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="symbol symbol-100px">
                                <img alt="Profile" src="{{ \Helper::pp($profile->foto) }}" class="border border-white">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- end::Welcome Section --}}

        {{-- begin::Row - Personal Statistics --}}
        @if (in_array($auth->role, ['Editor', 'Penulis', 'Kontributor']))
            <div class="row g-5 g-xl-10">
                {{-- begin::Col - My Posts --}}
                <div class="col-sm-6 col-xl-4">
                    <div class="card h-lg-100">
                        <div class="card-body d-flex justify-content-between align-items-start flex-column">
                            <div class="m-0">
                                <i class="ki-outline ki-document fs-2hx text-gray-600"></i>
                            </div>
                            <div class="d-flex flex-column my-7">
                                <span class="fw-semibold fs-3x text-gray-800 lh-1 ls-n2">{{ number_format($data['content']['my_posts']) }}</span>
                                <div class="m-0">
                                    <span class="fw-semibold fs-2x text-muted">Total Konten</span>
                                </div>
                            </div>
                            <span class="badge badge-light-success fs-base">
                                <i class="ki-outline ki-arrow-up fs-5 text-success ms-n1"></i>
                                {{ $data['content']['my_published_posts'] }} Published
                            </span>
                        </div>
                    </div>
                </div>
                {{-- end::Col --}}

                {{-- begin::Col - Published Posts --}}
                <div class="col-sm-6 col-xl-4">
                    <div class="card h-lg-100">
                        <div class="card-body d-flex justify-content-between align-items-start flex-column">
                            <div class="m-0">
                                <i class="ki-outline ki-check-circle fs-2hx text-gray-600"></i>
                            </div>
                            <div class="d-flex flex-column my-7">
                                <span class="fw-semibold fs-3x text-gray-800 lh-1 ls-n2">{{ number_format($data['content']['my_published_posts']) }}</span>
                                <div class="m-0">
                                    <span class="fw-semibold fs-2x text-muted">Published</span>
                                </div>
                            </div>
                            <span class="badge badge-light-primary fs-base">
                                <i class="ki-outline ki-eye fs-5 text-primary ms-n1"></i>
                                Sudah Dipublikasi
                            </span>
                        </div>
                    </div>
                </div>
                {{-- end::Col --}}

                {{-- begin::Col - Draft Posts --}}
                <div class="col-sm-6 col-xl-4">
                    <div class="card h-lg-100">
                        <div class="card-body d-flex justify-content-between align-items-start flex-column">
                            <div class="m-0">
                                <i class="ki-outline ki-notepad-edit fs-2hx text-gray-600"></i>
                            </div>
                            <div class="d-flex flex-column my-7">
                                <span class="fw-semibold fs-3x text-gray-800 lh-1 ls-n2">{{ number_format($data['content']['my_draft_posts']) }}</span>
                                <div class="m-0">
                                    <span class="fw-semibold fs-2x text-muted">Draft</span>
                                </div>
                            </div>
                            <span class="badge badge-light-warning fs-base">
                                <i class="ki-outline ki-pencil fs-5 text-warning ms-n1"></i>
                                Perlu Review
                            </span>
                        </div>
                    </div>
                </div>
                {{-- end::Col --}}
            </div>
            {{-- end::Row --}}
        @endif

        {{-- begin::Row - Activity Charts & Login History --}}
        <div class="row g-5 g-xl-10">
            {{-- begin::Col - Activity Chart --}}
            <div class="col-xl-6">
                <div class="card h-lg-100">
                    <div class="card-header align-items-center border-0 mt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="fw-bold text-dark fs-2">Aktivitas Saya (30 Hari)</span>
                            <span class="text-muted fw-semibold fs-7">Grafik aktivitas harian</span>
                        </h3>
                    </div>
                    <div class="card-body pt-6">
                        @if ($data['personal']['activity_stats']->count() > 0)
                            <div class="d-flex flex-wrap justify-content-between">
                                @foreach ($data['personal']['activity_stats']->take(7) as $stat)
                                    <div class="d-flex flex-column align-items-center mb-4">
                                        <div class="d-flex align-items-end mb-2">
                                            <div class="bg-primary rounded" style="width: 8px; height: {{ max(20, $stat->total * 5) }}px;"></div>
                                        </div>
                                        <span class="fs-8 text-muted">{{ \Carbon\Carbon::parse($stat->date)->format('d/m') }}</span>
                                        <span class="fs-7 fw-bold text-gray-600">{{ $stat->total }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-10">
                                <i class="ki-outline ki-chart-line fs-3x text-muted mb-3"></i>
                                <span class="text-muted d-block">Belum ada aktivitas dalam 30 hari terakhir</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            {{-- end::Col --}}

            {{-- begin::Col - Login History --}}
            <div class="col-xl-6">
                <div class="card h-lg-100">
                    <div class="card-header align-items-center border-0 mt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="fw-bold text-dark fs-2">Riwayat Login Saya</span>
                            <span class="text-muted fw-semibold fs-7">10 login terakhir</span>
                        </h3>
                    </div>
                    <div class="card-body pt-6">
                        @forelse($data['personal']['login_history'] as $login)
                            <div class="d-flex align-items-center mb-6">
                                <div class="symbol symbol-40px me-5">
                                    <div class="symbol-label bg-light-success">
                                        <i class="ki-outline ki-check fs-2 text-success"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold text-gray-800 fs-6">Login Berhasil</span>
                                            <span class="text-muted fw-semibold fs-7">IP: {{ $login->ip ?? 'N/A' }}</span>
                                            @if ($login->agent)
                                                <span class="text-muted fw-semibold fs-8">{{ Str::limit($login->agent, 50) }}</span>
                                            @endif
                                        </div>
                                        <span class="text-muted fw-semibold fs-7">
                                            {{ \Carbon\Carbon::parse($login->created_at)->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-10">
                                <span class="text-muted">Tidak ada riwayat login</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            {{-- end::Col --}}
        </div>
        {{-- end::Row --}}

        {{-- begin::Row - My Activity Log --}}
        <div class="row g-5 g-xl-10">
            <div class="col-12">
                <div class="card">
                    <div class="card-header align-items-center border-0 mt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="fw-bold text-dark fs-2">Log Aktivitas Saya</span>
                            <span class="text-muted fw-semibold fs-7">15 aktivitas terbaru</span>
                        </h3>
                        <div class="card-toolbar">
                            <button class="btn btn-sm btn-light-primary" data-bs-toggle="modal" data-bs-target="#activityFilterModal">
                                <i class="ki-outline ki-filter fs-3"></i>
                                Filter
                            </button>
                        </div>
                    </div>
                    <div class="card-body pt-6">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-3">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>Aktivitas</th>
                                        <th>Detail</th>
                                        <th>IP Address</th>
                                        <th>Waktu</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    @forelse($data['personal']['activities'] as $activity)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-35px me-3">
                                                        <div class="symbol-label bg-light-info">
                                                            <i class="ki-outline ki-activity fs-6 text-info"></i>
                                                        </div>
                                                    </div>
                                                    <span class="fw-bold">{{ $activity->status ?? 'Aktivitas' }}</span>
                                                </div>
                                            </td>
                                            <td>{{ $activity->agent ? Str::limit($activity->agent, 40) : 'N/A' }}</td>
                                            <td>{{ $activity->ip ?? 'N/A' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($activity->created_at)->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-10">
                                                <i class="ki-outline ki-information-4 fs-3x text-muted mb-3"></i>
                                                <div class="text-muted">Belum ada aktivitas yang tercatat</div>
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
        {{-- end::Row --}}

        {{-- begin::Row - Quick Actions --}}
        <div class="row g-5 g-xl-10">
            <div class="col-12">
                <div class="card">
                    <div class="card-header align-items-center border-0 mt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="fw-bold text-dark fs-2">Aksi Cepat</span>
                            <span class="text-muted fw-semibold fs-7">Menu yang sering digunakan</span>
                        </h3>
                    </div>
                    <div class="card-body pt-6">
                        <div class="row g-5">
                            {{-- Profile Settings --}}
                            <div class="col-sm-6 col-lg-3">
                                <a href="{{ route('prt.apps.profile.index') }}" class="card border border-2 border-gray-300 border-hover-primary">
                                    <div class="card-body text-center py-6">
                                        <i class="ki-outline ki-user-edit fs-2x text-primary mb-3"></i>
                                        <h3 class="fs-5 fw-bold text-dark mb-0">Profile Settings</h3>
                                        <span class="text-gray-400 fw-semibold fs-6">Kelola profile Anda</span>
                                    </div>
                                </a>
                            </div>

                            @if (in_array($auth->role, ['Editor', 'Penulis', 'Kontributor']))
                                {{-- Create New Post --}}
                                <div class="col-sm-6 col-lg-3">
                                    <a href="#" class="card border border-2 border-gray-300 border-hover-primary">
                                        <div class="card-body text-center py-6">
                                            <i class="ki-outline ki-document-add fs-2x text-success mb-3"></i>
                                            <h3 class="fs-5 fw-bold text-dark mb-0">Buat Konten</h3>
                                            <span class="text-gray-400 fw-semibold fs-6">Tulis artikel baru</span>
                                        </div>
                                    </a>
                                </div>

                                {{-- My Content --}}
                                <div class="col-sm-6 col-lg-3">
                                    <a href="#" class="card border border-2 border-gray-300 border-hover-primary">
                                        <div class="card-body text-center py-6">
                                            <i class="ki-outline ki-folder fs-2x text-info mb-3"></i>
                                            <h3 class="fs-5 fw-bold text-dark mb-0">Konten Saya</h3>
                                            <span class="text-gray-400 fw-semibold fs-6">Kelola tulisan Anda</span>
                                        </div>
                                    </a>
                                </div>
                            @endif

                            {{-- Help & Support --}}
                            <div class="col-sm-6 col-lg-3">
                                <a href="#" class="card border border-2 border-gray-300 border-hover-primary">
                                    <div class="card-body text-center py-6">
                                        <i class="ki-outline ki-questionnaire-tablet fs-2x text-warning mb-3"></i>
                                        <h3 class="fs-5 fw-bold text-dark mb-0">Bantuan</h3>
                                        <span class="text-gray-400 fw-semibold fs-6">Panduan & FAQ</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- end::Row --}}

    </div>
    {{-- end::Container --}}

    {{-- begin::Modal - Activity Filter --}}
    <div class="modal fade" id="activityFilterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-450px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold">Filter Aktivitas</h2>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-5">
                            <label class="form-label">Rentang Tanggal</label>
                            <select class="form-select" data-control="select2">
                                <option value="7">7 Hari Terakhir</option>
                                <option value="30" selected>30 Hari Terakhir</option>
                                <option value="90">90 Hari Terakhir</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>
                        <div class="mb-5">
                            <label class="form-label">Tipe Aktivitas</label>
                            <select class="form-select" data-control="select2">
                                <option value="all" selected>Semua Aktivitas</option>
                                <option value="login">Login/Logout</option>
                                <option value="content">Kelola Konten</option>
                                <option value="profile">Update Profile</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary">Terapkan Filter</button>
                </div>
            </div>
        </div>
    </div>
    {{-- end::Modal --}}

@endsection
{{-- CONTENT::END --}}

{{-- SCRIPTS::BEGIN --}}
@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Select2 for modals
            $('#activityFilterModal select[data-control="select2"]').select2({
                dropdownParent: $('#activityFilterModal')
            });
        });
    </script>
@endsection
{{-- SCRIPTS::END --}}
