@php
    $defaultTitle = 'Website UPTD Pemakaman Dinas Perumahan Permukiman dan Pemakaman Kabupaten Tangerang';
    $defaultDescription =
        'Selamat Datang di Website UPTD Pemakaman Dinas Perumahan Permukiman dan Pemakaman Kabupaten Tangerang, Pusat Informasi Tentang Tempat Pemakaman Umum (TPU) dan Layanan Mobil Ambulan Jenazah.';
    $defaultKeywords = 'UPTD Pemakaman, Dinas Perumahan Permukiman dan Pemakaman Kabupaten Tangerang, TPU Pemakaman Kabupaten Tangerang';
    $title = trim($__env->yieldContent('title')) ?: $defaultTitle;
    $description = trim($__env->yieldContent('description')) ?: $defaultDescription;
    $keywords = trim($__env->yieldContent('keywords')) ?: $defaultKeywords;
    $url = url()->current();
    $locale = str_replace('_', '-', app()->getLocale());
@endphp

<title>{{ $title }}</title>

<meta name="description" content="{{ $description }}">
<meta name="keywords" content="{{ $keywords }}">
<meta name="author" content="UPTD Pemakaman Dinas Perumahan Permukiman dan Pemakaman Kabupaten Tangerang">
<meta name="robots" content="index, follow" />
<meta name="application-name" content="{{ $defaultTitle }}">

<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="csrf-token" content="{{ csrf_token() }}" />

<meta property="og:locale" content="{{ $locale }}" />
<meta property="og:type" content="website" />
<meta property="og:title" content="{{ $title }}" />
<meta property="og:description" content="{{ $description }}" />
<meta property="og:url" content="{{ $url }}" />
<meta property="og:site_name" content="{{ $defaultTitle }}" />
<link rel="canonical" href="{{ $url }}" />

{{-- Optional: Twitter Cards --}}
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="{{ $title }}" />
<meta name="twitter:description" content="{{ $description }}" />

{{-- Favicon --}}
<link rel="icon" href="{{ asset('favicon.ico') }}" />
