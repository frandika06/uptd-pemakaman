<?php
$profile = \Helper::DataPP();
$auth = \Auth::user();
?>

@extends('layouts.admin')

{{-- SEO::BEGIN --}}
@section('title', 'Dashboard')
{{-- SEO::END --}}

{{-- CONTENT::BEGIN --}}
@section('content')
    <div class="card">
        {{-- begin::Body --}}
        <div class="card-body py-20">
            {{-- begin::Wrapper --}}
            <div class="mw-lg-950px mx-auto w-100">
                {{-- begin::Header --}}
                <div class="d-flex justify-content-between flex-column flex-sm-row mb-19">
                    <h4 class="fw-bolder text-gray-800 fs-2qx pe-5 pb-7">Dashboard</h4>
                    {{-- end::Logo --}}
                    <div class="text-sm-end">
                        {{-- begin::Logo --}}
                        <div class="symbol symbol-50px me-5">
                            <img alt="Logo" src="{{ \Helper::pp($profile->foto) }}">
                        </div>
                        {{-- end::Logo --}}
                        {{-- begin::Text --}}
                        <div class="text-sm-end fw-semibold fs-4 text-muted mt-7">
                            <div>{!! \Helper::welcomeBack() !!}</div>
                        </div>
                        {{-- end::Text --}}
                    </div>
                </div>
                {{-- end::Header --}}
                {{-- begin::Body --}}
                <div class="border-bottom">
                    {{-- begin::Image --}}
                    <div class="d-flex flex-row-fluid bgi-no-repeat bgi-position-x-center bgi-size-cover card-rounded h-150px h-lg-250px"
                        style="background-image: url({{ asset('be/media/misc/pattern-4.jpg') }})"></div>
                    {{-- end::Image --}}
                </div>
                {{-- end::Body --}}
            </div>
            {{-- end::Wrapper --}}
        </div>
        {{-- end::Body --}}
    </div>
@endsection
{{-- CONTENT::END --}}
