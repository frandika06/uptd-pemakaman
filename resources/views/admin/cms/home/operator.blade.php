<?php
$profile = \Helper::DataPP();
$auth = \Auth::user();
?>

@extends('layouts.admin')

{{-- SEO::BEGIN --}}
@section('title', 'Dashboard CMS')
{{-- SEO::END --}}

{{-- TOOLBAR::BEGIN --}}
@section('toolbar')
    {{-- begin::Page title --}}
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
            <li class="breadcrumb-item text-gray-700">Dashboard</li>
        </ul>
    </div>
    {{-- end::Page title --}}
@endsection
{{-- TOOLBAR::BEGIN --}}

{{-- CONTENT::BEGIN --}}
@section('content')
    {{-- begin::Container --}}
    <div class="d-flex flex-column gap-7 gap-lg-10">
        {{-- begin::Row - Statistics --}}
        <div class="row g-5 g-xl-10">
            {{-- begin::Col - Total Users --}}
            <div class="col-sm-6 col-xl-3">
                {{-- begin::Card widget --}}
                <div class="card h-lg-100">
                    {{-- begin::Body --}}
                    <div class="card-body d-flex justify-content-between align-items-start flex-column">
                        {{-- begin::Icon --}}
                        <div class="m-0">
                            <i class="ki-outline ki-people fs-2hx text-gray-600"></i>
                        </div>
                        {{-- end::Icon --}}
                        {{-- begin::Section --}}
                        <div class="d-flex flex-column my-7">
                            {{-- begin::Number --}}
                            <span class="fw-semibold fs-3x text-gray-800 lh-1 ls-n2">{{ $statistik['users'] }}</span>
                            {{-- end::Number --}}
                            {{-- begin::Follower --}}
                            <div class="m-0">
                                <span class="fw-semibold fs-6 text-gray-400">Total Users</span>
                            </div>
                            {{-- end::Follower --}}
                        </div>
                        {{-- end::Section --}}
                        {{-- begin::Badge --}}
                        <span class="badge badge-light-success fs-base">
                            <i class="ki-outline ki-arrow-up fs-5 text-success ms-n1"></i>
                            Active
                        </span>
                        {{-- end::Badge --}}
                    </div>
                    {{-- end::Body --}}
                </div>
                {{-- end::Card widget --}}
            </div>
            {{-- end::Col --}}

            {{-- begin::Col - Total Content --}}
            <div class="col-sm-6 col-xl-3">
                {{-- begin::Card widget --}}
                <div class="card h-lg-100">
                    {{-- begin::Body --}}
                    <div class="card-body d-flex justify-content-between align-items-start flex-column">
                        {{-- begin::Icon --}}
                        <div class="m-0">
                            <i class="ki-outline ki-abstract-26 fs-2hx text-gray-600"></i>
                        </div>
                        {{-- end::Icon --}}
                        {{-- begin::Section --}}
                        <div class="d-flex flex-column my-7">
                            {{-- begin::Number --}}
                            <span class="fw-semibold fs-3x text-gray-800 lh-1 ls-n2">{{ $statistik['konten'] }}</span>
                            {{-- end::Number --}}
                            {{-- begin::Follower --}}
                            <div class="m-0">
                                <span class="fw-semibold fs-6 text-gray-400">Total Konten</span>
                            </div>
                            {{-- end::Follower --}}
                        </div>
                        {{-- end::Section --}}
                        {{-- begin::Badge --}}
                        <span class="badge badge-light-primary fs-base">
                            <i class="ki-outline ki-arrow-up fs-5 text-primary ms-n1"></i>
                            All Content
                        </span>
                        {{-- end::Badge --}}
                    </div>
                    {{-- end::Body --}}
                </div>
                {{-- end::Card widget --}}
            </div>
            {{-- end::Col --}}

            {{-- begin::Col - Published --}}
            <div class="col-sm-6 col-xl-3">
                {{-- begin::Card widget --}}
                <div class="card h-lg-100">
                    {{-- begin::Body --}}
                    <div class="card-body d-flex justify-content-between align-items-start flex-column">
                        {{-- begin::Icon --}}
                        <div class="m-0">
                            <i class="ki-outline ki-check-circle fs-2hx text-gray-600"></i>
                        </div>
                        {{-- end::Icon --}}
                        {{-- begin::Section --}}
                        <div class="d-flex flex-column my-7">
                            {{-- begin::Number --}}
                            <span class="fw-semibold fs-3x text-gray-800 lh-1 ls-n2">{{ $statistik['published'] }}</span>
                            {{-- end::Number --}}
                            {{-- begin::Follower --}}
                            <div class="m-0">
                                <span class="fw-semibold fs-6 text-gray-400">Published</span>
                            </div>
                            {{-- end::Follower --}}
                        </div>
                        {{-- end::Section --}}
                        {{-- begin::Badge --}}
                        <span class="badge badge-light-success fs-base">
                            <i class="ki-outline ki-arrow-up fs-5 text-success ms-n1"></i>
                            {{ $statistik['konten'] > 0 ? round((str_replace(',', '', $statistik['published']) / str_replace(',', '', $statistik['konten'])) * 100) : 0 }}%
                        </span>
                        {{-- end::Badge --}}
                    </div>
                    {{-- end::Body --}}
                </div>
                {{-- end::Card widget --}}
            </div>
            {{-- end::Col --}}

            {{-- begin::Col - Archived --}}
            <div class="col-sm-6 col-xl-3">
                {{-- begin::Card widget --}}
                <div class="card h-lg-100">
                    {{-- begin::Body --}}
                    <div class="card-body d-flex justify-content-between align-items-start flex-column">
                        {{-- begin::Icon --}}
                        <div class="m-0">
                            <i class="ki-outline ki-archive fs-2hx text-gray-600"></i>
                        </div>
                        {{-- end::Icon --}}
                        {{-- begin::Section --}}
                        <div class="d-flex flex-column my-7">
                            {{-- begin::Number --}}
                            <span class="fw-semibold fs-3x text-gray-800 lh-1 ls-n2">{{ $statistik['archived'] }}</span>
                            {{-- end::Number --}}
                            {{-- begin::Follower --}}
                            <div class="m-0">
                                <span class="fw-semibold fs-6 text-gray-400">Archived</span>
                            </div>
                            {{-- end::Follower --}}
                        </div>
                        {{-- end::Section --}}
                        {{-- begin::Badge --}}
                        <span class="badge badge-light-warning fs-base">
                            <i class="ki-outline ki-arrow-down fs-5 text-warning ms-n1"></i>
                            {{ $statistik['konten'] > 0 ? round((str_replace(',', '', $statistik['archived']) / str_replace(',', '', $statistik['konten'])) * 100) : 0 }}%
                        </span>
                        {{-- end::Badge --}}
                    </div>
                    {{-- end::Body --}}
                </div>
                {{-- end::Card widget --}}
            </div>
            {{-- end::Col --}}
        </div>
        {{-- end::Row --}}

        {{-- begin::Row --}}
        <div class="row g-5 g-xl-10">
            {{-- begin::Col --}}
            <div class="col-xl-12">
                {{-- begin::Content Management Table --}}
                <div class="card">
                    {{-- begin::Header --}}
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">Manajemen Konten</span>
                            <span class="text-muted mt-1 fw-semibold fs-7">{{ count($data) }} kategori konten</span>
                        </h3>
                    </div>
                    {{-- end::Header --}}
                    {{-- begin::Body --}}
                    <div class="card-body py-3">
                        {{-- begin::Table container --}}
                        <div class="table-responsive">
                            {{-- begin::Table --}}
                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="kt_content_table">
                                {{-- begin::Table head --}}
                                <thead>
                                    <tr class="fw-bold text-muted">
                                        <th class="min-w-150px">Kategori</th>
                                        <th class="min-w-140px">Published</th>
                                        <th class="min-w-120px">Archived</th>
                                        <th class="min-w-100px text-end">Actions</th>
                                    </tr>
                                </thead>
                                {{-- end::Table head --}}
                                {{-- begin::Table body --}}
                                <tbody>
                                    @foreach ($data as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-45px me-5">
                                                        <div class="symbol-label bg-light-primary">
                                                            @switch($item['kategori'])
                                                                @case('Postingan')
                                                                    <i class="ki-outline ki-notepad-edit text-primary fs-2"></i>
                                                                @break

                                                                @case('Halaman')
                                                                    <i class="ki-outline ki-document text-primary fs-2"></i>
                                                                @break

                                                                @case('Banner')
                                                                    <i class="ki-outline ki-picture text-primary fs-2"></i>
                                                                @break

                                                                @case('Galeri')
                                                                    <i class="fa-solid fa-images text-primary fs-2"></i>
                                                                @break

                                                                @case('Video')
                                                                    <i class="fa-solid fa-video text-primary fs-2"></i>
                                                                @break

                                                                @case('Unduhan')
                                                                    <i class="ki-outline ki-file-down text-primary fs-2"></i>
                                                                @break

                                                                @case('FAQ')
                                                                    <i class="ki-outline ki-question-2 text-primary fs-2"></i>
                                                                @break

                                                                @default
                                                                    <i class="ki-outline ki-abstract-26 text-primary fs-2"></i>
                                                            @endswitch
                                                        </div>
                                                    </div>
                                                    <div class="d-flex justify-content-start flex-column">
                                                        <a href="{{ $item['url'] }}" class="text-dark fw-bold text-hover-primary fs-6">{{ $item['kategori'] }}</a>
                                                        <span class="text-muted fw-semibold text-muted d-block fs-7">Manajemen {{ strtolower($item['kategori']) }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex flex-column w-100 me-2">
                                                    <div class="d-flex flex-stack mb-2">
                                                        <span class="text-muted me-2 fs-7 fw-semibold">{{ $item['published'] }}</span>
                                                    </div>
                                                    <div class="progress h-6px w-100">
                                                        <div class="progress-bar bg-success" role="progressbar" style="width: 65%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex flex-column w-100 me-2">
                                                    <div class="d-flex flex-stack mb-2">
                                                        <span class="text-muted me-2 fs-7 fw-semibold">{{ $item['archived'] }}</span>
                                                    </div>
                                                    <div class="progress h-6px w-100">
                                                        <div class="progress-bar bg-warning" role="progressbar" style="width: 35%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-end flex-shrink-0">
                                                    <a href="{{ $item['url'] }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm">
                                                        <i class="ki-outline ki-arrow-right fs-2"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                {{-- end::Table body --}}
                            </table>
                            {{-- end::Table --}}
                        </div>
                        {{-- end::Table container --}}
                    </div>
                    {{-- end::Body --}}
                </div>
                {{-- end::Content Management Table --}}
            </div>
            {{-- end::Col --}}
        </div>
        {{-- end::Row --}}
    </div>
    {{-- end::Container --}}
@endsection
{{-- CONTENT::END --}}

{{-- SCRIPTS::BEGIN --}}
@section('scripts')
@endsection
{{-- SCRIPTS::END --}}
