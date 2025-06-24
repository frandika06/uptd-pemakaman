@extends('layouts.portal')

{{-- SEO::BEGIN --}}
@section('title', 'Beranda - UPTD Pemakaman DPPP Kab. Tangerang')
@section('description', 'Informasi lengkap tentang layanan pemakaman dan TPU di Kabupaten Tangerang.')
@section('keywords', 'pemakaman, layanan TPU, Kabupaten Tangerang')
{{-- SEO::END --}}

{{-- CONTENT::BEGIN --}}
@section('content')
    <!--===== HERO AREA STARTS =======-->
    <div class="hero4-section-area sp4"
        style="background-image: url({{ asset('fe') }}/img/custom/bg-beranda.png); background-position: top; background-repeat: no-repeat; background-size: cover;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-1 col-md-0"></div>
                <div class="col-lg-8 col-md-12">
                    <div class="hero-header min-h-450px">
                        <h5 data-aos="fade-left" data-aos-duration="800">Selamat Datang di Website</h5>
                        <div class="space20"></div>
                        <h1 class="text-anime-style-3">UPTD PEMAKAMAN</h1>
                        <h4 class="text-white text-anime-style-3">Dinas Perumahan Permukiman dan Pemakaman</h4>
                        <h4 class="text-white text-anime-style-3">Kabupaten Tangerang</h4>
                        <div class="space20"></div>
                        <div class="btn-are1" data-aos="fade-left" data-aos-duration="1000">
                            <a href="sidebar-grid.html" class="theme-btn5">Lihat Data TPU <span class="arrow1"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                        width="24" height="24" fill="currentColor">
                                        <path d="M12 13H4V11H12V4L20 12L12 20V13Z"></path>
                                    </svg></span><span class="arrow2"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                                        <path d="M12 13H4V11H12V4L20 12L12 20V13Z"></path>
                                    </svg></span></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--===== HERO AREA ENDS =======-->
    <div class="space100 d-lg-block d-none"></div>
    <div class="space50 d-lg-none d-block"></div>
    <!--===== OTHERS AREA STARTS =======-->
    <div class="others-selider-section" data-aos="fade-up" data-aos-duration="1000">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="slider-section-boxarea owl-carousel">
                        {{-- ITEM --}}
                        <div class="slider-boxarea">
                            <div class="img1">
                                <img src="{{ asset('fe/img/custom/tpu-1.png') }}" alt="">
                            </div>
                            <div class="conetnt-area">
                                <div class="text">
                                    <a href="#">TPU Saga</a>
                                    <div class="space12"></div>
                                    <p>Balaraja</p>
                                </div>
                                <div class="arrow">
                                    <a href="#"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M16.0037 9.41421L7.39712 18.0208L5.98291 16.6066L14.5895 8H7.00373V6H18.0037V17H16.0037V9.41421Z"></path>
                                        </svg></a>
                                </div>
                            </div>
                        </div>
                        {{-- ITEM --}}

                        {{-- ITEM --}}
                        <div class="slider-boxarea">
                            <div class="img1">
                                <img src="{{ asset('fe/img/custom/tpu-2.png') }}" alt="">
                            </div>
                            <div class="conetnt-area">
                                <div class="text">
                                    <a href="#">TPU Sukamulya</a>
                                    <div class="space12"></div>
                                    <p>Cikupa</p>
                                </div>
                                <div class="arrow">
                                    <a href="#"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M16.0037 9.41421L7.39712 18.0208L5.98291 16.6066L14.5895 8H7.00373V6H18.0037V17H16.0037V9.41421Z"></path>
                                        </svg></a>
                                </div>
                            </div>
                        </div>
                        {{-- ITEM --}}

                        {{-- ITEM --}}
                        <div class="slider-boxarea">
                            <div class="img1">
                                <img src="{{ asset('fe/img/custom/tpu-3.png') }}" alt="">
                            </div>
                            <div class="conetnt-area">
                                <div class="text">
                                    <a href="#">TPU Suradita</a>
                                    <div class="space12"></div>
                                    <p>Cisauk</p>
                                </div>
                                <div class="arrow">
                                    <a href="#"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M16.0037 9.41421L7.39712 18.0208L5.98291 16.6066L14.5895 8H7.00373V6H18.0037V17H16.0037V9.41421Z"></path>
                                        </svg></a>
                                </div>
                            </div>
                        </div>
                        {{-- ITEM --}}

                        {{-- ITEM --}}
                        <div class="slider-boxarea">
                            <div class="img1">
                                <img src="{{ asset('fe/img/custom/tpu-4.png') }}" alt="">
                            </div>
                            <div class="conetnt-area">
                                <div class="text">
                                    <a href="#">TPU Bojongloa</a>
                                    <div class="space12"></div>
                                    <p>Cisoka</p>
                                </div>
                                <div class="arrow">
                                    <a href="#"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M16.0037 9.41421L7.39712 18.0208L5.98291 16.6066L14.5895 8H7.00373V6H18.0037V17H16.0037V9.41421Z"></path>
                                        </svg></a>
                                </div>
                            </div>
                        </div>
                        {{-- ITEM --}}

                        {{-- ITEM --}}
                        <div class="slider-boxarea">
                            <div class="img1">
                                <img src="{{ asset('fe/img/custom/tpu-5.png') }}" alt="">
                            </div>
                            <div class="conetnt-area">
                                <div class="text">
                                    <a href="#">TPU Binong</a>
                                    <div class="space12"></div>
                                    <p>Curug</p>
                                </div>
                                <div class="arrow">
                                    <a href="#"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M16.0037 9.41421L7.39712 18.0208L5.98291 16.6066L14.5895 8H7.00373V6H18.0037V17H16.0037V9.41421Z"></path>
                                        </svg></a>
                                </div>
                            </div>
                        </div>
                        {{-- ITEM --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--===== OTHERS AREA ENDS =======-->

    <!--===== ABOUT AREA STARTS =======-->
    <div class="about1-section-area sp1">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="about-images-area">
                        <div class="img2 image-anime reveal">
                            <img src="{{ asset('fe/img/custom/about-2.jpeg') }}" alt="">
                        </div>
                        <div class="img1 image-anime reveal">
                            <img src="{{ asset('fe/img/custom/about-1.jpeg') }}" alt="">
                        </div>
                    </div>
                </div>
                <div class="col-lg-1"></div>
                <div class="col-lg-5">
                    <div class="about-heading heading3">
                        <h5 data-aos="fade-left" data-aos-duration="800">Data TPU</h5>
                        <div class="space20"></div>
                        <h2 class="text-anime-style-3">Statistik Data TPU yang Ada di Kabupaten Tangerang</h2>
                        <div class="space18"></div>
                        <p data-aos="fade-left" data-aos-duration="900">Website ini mendata secara <strong><i>Realtime</i></strong> seluruh TPU yang dikelola oleh Pemerintah
                            Kabupaten Tangerang. Sebagai pusat informasi dan memudahkan masyarakat dalam urusan <strong>Pemakaman</strong> serta menyediakan <strong>Layanan Mobil
                                Jenazah</strong> secara <strong><span class="text-danger">GRATIS</span></strong></p>
                        <div class="space32"></div>
                        <div class="counter-boxes" data-aos="fade-left" data-aos-duration="1000">
                            <div class="row">
                                <div class="col-lg-4 col-md-4 col-6">
                                    <div class="counter-boxarea text-center">
                                        <h2><span class="counter">107</span></h2>
                                        <div class="space12"></div>
                                        <p>TPU</p>
                                    </div>
                                </div>

                                <div class="col-lg-4 col-md-4 col-6">
                                    <div class="counter-boxarea text-center">
                                        <h2><span class="counter">29</span></h2>
                                        <div class="space12"></div>
                                        <p>Kecamatan</p>
                                    </div>
                                </div>

                                <div class="col-lg-4 col-md-4 col-6">
                                    <div class="space20 d-md-none d-block"></div>
                                    <div class="counter-boxarea text-center">
                                        <h2><span class="counter">5</span>%</h2>
                                        <div class="space12"></div>
                                        <p>Makan Terisi</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="space32"></div>
                        <div class="btn-area1" data-aos="fade-left" data-aos-duration="1200">
                            <a href="sidebar-grid.html" class="theme-btn5">Lihat Data TPU <span class="arrow1"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                        width="24" height="24" fill="currentColor">
                                        <path d="M12 13H4V11H12V4L20 12L12 20V13Z"></path>
                                    </svg></span><span class="arrow2"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"
                                        fill="currentColor">
                                        <path d="M12 13H4V11H12V4L20 12L12 20V13Z"></path>
                                    </svg></span></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--===== ABOUT AREA ENDS =======-->
@endsection
{{-- CONTENT::END --}}
