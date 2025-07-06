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

        {{-- begin::Row - User Statistics --}}
        <div class="row g-5 g-xl-10">
            {{-- begin::Col - Total Users --}}
            <div class="col-sm-6 col-xl-3">
                <div class="card h-lg-100">
                    <div class="card-body d-flex justify-content-between align-items-start flex-column">
                        <div class="m-0">
                            <i class="ki-outline ki-people fs-2hx text-gray-600"></i>
                        </div>
                        <div class="d-flex flex-column my-7">
                            <span class="fw-semibold fs-3x text-gray-800 lh-1 ls-n2">{{ number_format($data['users']['total']) }}</span>
                            <div class="m-0">
                                <span class="fw-semibold fs-6 text-gray-400">Total Users</span>
                            </div>
                        </div>
                        <span class="badge badge-light-success fs-base">
                            <i class="ki-outline ki-arrow-up fs-5 text-success ms-n1"></i>
                            {{ $data['users']['active'] }} Aktif
                        </span>
                    </div>
                </div>
            </div>
            {{-- end::Col --}}

            {{-- begin::Col - Active Users --}}
            <div class="col-sm-6 col-xl-3">
                <div class="card h-lg-100">
                    <div class="card-body d-flex justify-content-between align-items-start flex-column">
                        <div class="m-0">
                            <i class="ki-outline ki-user-tick fs-2hx text-gray-600"></i>
                        </div>
                        <div class="d-flex flex-column my-7">
                            <span class="fw-semibold fs-3x text-gray-800 lh-1 ls-n2">{{ number_format($data['users']['active']) }}</span>
                            <div class="m-0">
                                <span class="fw-semibold fs-6 text-gray-400">Users Aktif</span>
                            </div>
                        </div>
                        <span class="badge badge-light-primary fs-base">
                            <i class="ki-outline ki-arrow-up fs-5 text-primary ms-n1"></i>
                            {{ $data['users']['online'] }} Online
                        </span>
                    </div>
                </div>
            </div>
            {{-- end::Col --}}

            {{-- begin::Col - Total Content --}}
            <div class="col-sm-6 col-xl-3">
                <div class="card h-lg-100">
                    <div class="card-body d-flex justify-content-between align-items-start flex-column">
                        <div class="m-0">
                            <i class="ki-outline ki-document fs-2hx text-gray-600"></i>
                        </div>
                        <div class="d-flex flex-column my-7">
                            <span class="fw-semibold fs-3x text-gray-800 lh-1 ls-n2">{{ number_format($data['content']['total_posts']) }}</span>
                            <div class="m-0">
                                <span class="fw-semibold fs-6 text-gray-400">Total Konten</span>
                            </div>
                        </div>
                        <span class="badge badge-light-info fs-base">
                            <i class="ki-outline ki-arrow-up fs-5 text-info ms-n1"></i>
                            {{ $data['content']['published_posts'] }} Published
                        </span>
                    </div>
                </div>
            </div>
            {{-- end::Col --}}

            {{-- begin::Col - Messages --}}
            <div class="col-sm-6 col-xl-3">
                <div class="card h-lg-100">
                    <div class="card-body d-flex justify-content-between align-items-start flex-column">
                        <div class="m-0">
                            <i class="ki-outline ki-sms fs-2hx text-gray-600"></i>
                        </div>
                        <div class="d-flex flex-column my-7">
                            <span class="fw-semibold fs-3x text-gray-800 lh-1 ls-n2">{{ number_format($data['content']['total_messages']) }}</span>
                            <div class="m-0">
                                <span class="fw-semibold fs-6 text-gray-400">Total Pesan</span>
                            </div>
                        </div>
                        <span class="badge badge-light-warning fs-base">
                            <i class="ki-outline ki-notification-bing fs-5 text-warning ms-n1"></i>
                            {{ $data['content']['unread_messages'] }} Belum Dibaca
                        </span>
                    </div>
                </div>
            </div>
            {{-- end::Col --}}
        </div>
        {{-- end::Row --}}

        {{-- begin::Row - Charts & Details --}}
        <div class="row g-5 g-xl-10">
            {{-- begin::Col - User by Role Chart --}}
            <div class="col-xl-6">
                <div class="card h-lg-100">
                    <div class="card-header align-items-center border-0 mt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="fw-bold text-dark fs-2">Distribusi Users per Role</span>
                            <span class="text-muted fw-semibold fs-7">Berdasarkan role sistem</span>
                        </h3>
                    </div>
                    <div class="card-body pt-6">
                        @foreach ($data['users']['by_role'] as $role => $count)
                            <div class="d-flex align-items-center mb-6">
                                <span class="bullet bullet-dot bg-primary me-5 h-10px w-10px"></span>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-gray-800 fs-6">{{ $role }}</span>
                                        <span class="fw-bold text-gray-400 fs-6">{{ $count }} users</span>
                                    </div>
                                    <div class="progress h-6px bg-light-primary mt-2">
                                        <div class="progress-bar bg-primary" style="width: {{ $data['users']['total'] > 0 ? ($count / $data['users']['total']) * 100 : 0 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            {{-- end::Col --}}

            {{-- begin::Col - Recent Login Activity --}}
            <div class="col-xl-6">
                <div class="card h-lg-100">
                    <div class="card-header align-items-center border-0 mt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="fw-bold text-dark fs-2">Login Terbaru</span>
                            <span class="text-muted fw-semibold fs-7">10 login terakhir</span>
                        </h3>
                    </div>
                    <div class="card-body pt-6">
                        @forelse($data['recent_logins'] as $login)
                            <div class="d-flex align-items-center mb-6">
                                <div class="symbol symbol-40px me-5">
                                    <div class="symbol-label bg-light-primary">
                                        <i class="ki-outline ki-user fs-2 text-primary"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold text-gray-800 fs-6">
                                                {{ $login->user_name ?? ($login->user_email ?? 'Unknown User') }}
                                            </span>
                                            <span class="text-muted fw-semibold fs-7">{{ $login->ip ?? 'N/A' }}</span>
                                        </div>
                                        <span class="text-muted fw-semibold fs-7">
                                            {{ \Carbon\Carbon::parse($login->created_at)->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-10">
                                <span class="text-muted">Tidak ada data login</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            {{-- end::Col --}}
        </div>
        {{-- end::Row --}}

        {{-- begin::Row - Activity Log --}}
        <div class="row g-5 g-xl-10">
            <div class="col-12">
                <div class="card">
                    <div class="card-header align-items-center border-0 mt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="fw-bold text-dark fs-2">Log Aktivitas Sistem</span>
                            <span class="text-muted fw-semibold fs-7">15 aktivitas terbaru</span>
                        </h3>
                        <div class="card-toolbar">
                            <a href="#" class="btn btn-sm btn-primary">
                                <i class="ki-outline ki-eye fs-3"></i>
                                Lihat Semua
                            </a>
                        </div>
                    </div>
                    <div class="card-body pt-6">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-3">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>User</th>
                                        <th>Aktivitas</th>
                                        <th>IP Address</th>
                                        <th>Waktu</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    @forelse($data['recent_activities'] as $activity)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-35px me-3">
                                                        <div class="symbol-label bg-light-primary">
                                                            <i class="ki-outline ki-user fs-6 text-primary"></i>
                                                        </div>
                                                    </div>
                                                    <span class="fw-bold">{{ $activity->user_name ?? 'Unknown User' }}</span>
                                                </div>
                                            </td>
                                            <td>{{ $activity->subjek ?? 'N/A' }}</td>
                                            <td>{{ $activity->ip ?? 'N/A' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($activity->created_at)->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-10">
                                                <span class="text-muted">Tidak ada data aktivitas</span>
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

        {{-- begin::Row - Security Info --}}
        @if ($data['security']['failed_logins'] > 0)
            <div class="row g-5 g-xl-10">
                <div class="col-12">
                    <div class="alert alert-warning d-flex align-items-center p-5">
                        <i class="ki-outline ki-shield-cross fs-2hx text-warning me-4"></i>
                        <div class="d-flex flex-column">
                            <h4 class="mb-1 text-warning">Peringatan Keamanan</h4>
                            <span>Terdapat <strong>{{ $data['security']['failed_logins'] }}</strong> percobaan login gagal dalam 7 hari terakhir. Pastikan untuk memantau aktivitas
                                yang mencurigakan.</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        {{-- end::Row --}}

    </div>
    {{-- end::Container --}}
@endsection
{{-- CONTENT::END --}}
