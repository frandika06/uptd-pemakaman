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
    <div class="d-flex flex-column flex-row-fluid">
        <div class="page-title d-flex align-items-center me-3">
            <h1 class="page-heading d-flex flex-column justify-content-center text-dark fw-bold fs-lg-2x gap-2">
                <span>Dashboard</span>
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
            <li class="breadcrumb-item text-gray-700">Pengaturan</li>
            <li class="breadcrumb-item">
                <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
            </li>
            <li class="breadcrumb-item text-gray-700">Dashboard</li>
        </ul>
    </div>
@endsection
{{-- TOOLBAR::END --}}

{{-- CONTENT::BEGIN --}}
@section('content')
    <div class="d-flex flex-column gap-7 gap-lg-10">
        {{-- begin::Welcome Section --}}
        <div class="row g-5 g-xl-10">
            <div class="col-12">
                <div class="card h-lg-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h3 class="text-white fw-bold mb-3">Selamat Datang, {{ $auth->RelPortalActor->nama_lengkap ?? 'Pengguna' }}!</h3>
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
                            <div class="symbol symbol-100px symbol-circle">
                                <img alt="Profile" src="{{ \Helper::pp($profile->foto ?? '') }}" class="border border-white object-fit-cover" />
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
                <div class="col-sm-6 col-xl-4">
                    <div class="card h-lg-100">
                        <div class="card-body d-flex justify-content-between align-items-start flex-column">
                            <div class="m-0">
                                <i class="ki-outline ki-document fs-2hx text-gray-600"></i>
                            </div>
                            <div class="d-flex flex-column my-7">
                                <span class="fw-semibold fs-3x text-gray-800 lh-1 ls-n2">{{ number_format($data['content']['my_posts']) }}</span>
                                <div class="m-0">
                                    <span class="fw-semibold fs-6 text-gray-400">Total Konten</span>
                                </div>
                            </div>
                            <span class="badge badge-light-success fs-base">
                                <i class="ki-outline ki-arrow-up fs-5 text-success ms-n1"></i>
                                {{ $data['content']['my_published_posts'] }} Published
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card h-lg-100">
                        <div class="card-body d-flex justify-content-between align-items-start flex-column">
                            <div class="m-0">
                                <i class="ki-outline ki-check-circle fs-2hx text-gray-600"></i>
                            </div>
                            <div class="d-flex flex-column my-7">
                                <span class="fw-semibold fs-3x text-gray-800 lh-1 ls-n2">{{ number_format($data['content']['my_published_posts']) }}</span>
                                <div class="m-0">
                                    <span class="fw-semibold fs-6 text-gray-400">Published</span>
                                </div>
                            </div>
                            <span class="badge badge-light-primary fs-base">
                                <i class="ki-outline ki-eye fs-5 text-primary ms-n1"></i>
                                Sudah Dipublikasi
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card h-lg-100">
                        <div class="card-body d-flex justify-content-between align-items-start flex-column">
                            <div class="m-0">
                                <i class="ki-outline ki-notepad-edit fs-2hx text-gray-600"></i>
                            </div>
                            <div class="d-flex flex-column my-7">
                                <span class="fw-semibold fs-3x text-gray-800 lh-1 ls-n2">{{ number_format($data['content']['my_draft_posts']) }}</span>
                                <div class="m-0">
                                    <span class="fw-semibold fs-6 text-gray-400">Draft</span>
                                </div>
                            </div>
                            <span class="badge badge-light-warning fs-base">
                                <i class="ki-outline ki-pencil fs-5 text-warning ms-n1"></i>
                                Perlu Review
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        {{-- end::Row --}}

        {{-- begin::Row - Activity Charts & Login History --}}
        <div class="row g-5 g-xl-10">
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
                                <div class="symbol symbol-40px symbol-circle me-5">
                                    <img src="{{ \Helper::pp($login['user_foto'] ?? '') }}" alt="{{ htmlspecialchars($login['user_name']) }}" class="object-fit-cover" />
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold text-gray-800 fs-6">{{ $login['status'] }}</span>
                                            <span class="text-muted fw-semibold fs-7">IP: {{ $login['ip'] ?? '-' }}</span>
                                            @if ($login['agent'])
                                                <span class="text-muted fw-semibold fs-8">{{ \Illuminate\Support\Str::limit($login['agent'], 50) }}</span>
                                            @endif
                                        </div>
                                        <span class="text-muted fw-semibold fs-7">
                                            {{ \Carbon\Carbon::parse($login['created_at'])->format('d/m/Y H:i') }}
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
                            <a href="{{ route('setup.apps.log.index') }}" class="btn btn-sm btn-primary">
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
                                        <th width="30%">User</th>
                                        <th width="30%">Aktivitas</th>
                                        <th width="20%">IP Address</th>
                                        <th width="20%">Waktu</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    @forelse($data['personal']['activities'] as $activity)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-40px symbol-circle me-3">
                                                        <img src="{{ \Helper::pp($activity['user_foto'] ?? '') }}" alt="{{ htmlspecialchars($activity['user_name']) }}"
                                                            class="object-fit-cover" />
                                                    </div>
                                                    <span class="fw-bold">{{ $activity['user_name'] }}</span>
                                                </div>
                                            </td>
                                            <td>{{ $activity['subjek'] ?? '-' }}</td>
                                            <td>{{ $activity['ip'] ?? '-' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($activity['created_at'])->format('d/m/Y H:i') }}</td>
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
    </div>
@endsection
{{-- CONTENT::END --}}
