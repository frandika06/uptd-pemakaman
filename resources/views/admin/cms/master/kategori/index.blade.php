<?php
$profile = \Helper::DataPP();
$auth = \Auth::user();
?>

@extends('layouts.admin')

{{-- SEO::BEGIN --}}
@section('title', 'Kategori CMS')
{{-- SEO::END --}}

{{-- TOOLBAR::BEGIN --}}
@section('toolbar')
    {{-- begin::Page title --}}
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        {{-- begin::Title --}}
        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Kategori CMS</h1>
        {{-- end::Title --}}
        {{-- begin::Breadcrumb --}}
        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
            <li class="breadcrumb-item text-dark">
                <a href="{{ route('auth.home') }}" class="text-dark text-hover-primary">Home</a>
            </li>
            <li class="breadcrumb-item">
                <span class="bullet bg-dark w-5px h-2px"></span>
            </li>
            <li class="breadcrumb-item text-dark">Kategori CMS</li>
        </ul>
        {{-- end::Breadcrumb --}}
    </div>
    {{-- end::Page title --}}
@endsection
{{-- TOOLBAR::BEGIN --}}

{{-- CONTENT::BEGIN --}}
@section('content')
    {{-- begin::Container --}}
    <div class="d-flex flex-column gap-7 gap-lg-10">
    </div>
    {{-- end::Container --}}
@endsection
{{-- CONTENT::END --}}

{{-- SCRIPTS::BEGIN --}}
@section('scripts')
@endsection
{{-- SCRIPTS::END --}}
