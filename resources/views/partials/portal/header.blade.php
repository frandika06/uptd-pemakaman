{{-- =====HEADER START======= --}}
<header>
    <div class="header-area homepage4 header header-sticky d-none d-lg-block" id="header">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="header-elements d-flex align-items-center justify-content-between">
                        <div class="site-logo">
                            <a href="#"><img src="{{ asset('logo/logo-color.png') }}" alt="UPTDPemakaman Dinas Perkim Kabupaten Tangerang"></a>
                        </div>

                        <div class="main-menu">
                            <ul>
                                <li><a href="#">Beranda</a></li>

                                <li><a href="#" class="plus">Profile <i class="fa-solid fa-angle-down"></i></a>
                                    <ul class="dropdown-padding">
                                        <li><a href="#">Visi Misi</a></li>
                                        <li><a href="#">Struktur Organisasi</a></li>
                                        <li><a href="#">Regulasi dan SOP</a></li>
                                        <li><a href="#">Laporan & Monitoring</a></li>
                                        <li><a href="#">Kontak dan Alamat</a></li>
                                    </ul>
                                </li>

                                <li><a href="#">Data TPU</a></li>

                                <li><a href="#" class="plus">Layanan <i class="fa-solid fa-angle-down"></i></a>
                                    <ul class="dropdown-padding">
                                        <li><a href="#">Mobil Jenazah</a></li>
                                    </ul>
                                </li>

                                <li><a href="#" class="plus">Informasi <i class="fa-solid fa-angle-down"></i></a>
                                    <ul class="dropdown-padding">
                                        <li><a href="#">Berita & Artikel</a></li>
                                        <li><a href="#">Galeri</a></li>
                                        <li><a href="#">Video</a></li>
                                        <li><a href="#">Unduhan</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>

                        <div class="btn-area">
                            <div class="search-icon header__search header-search-btn">
                                <a href="#"><i class="fa-solid fa-search fs-5 text-dark"></i></a>
                            </div>
                            <a href="{{ route('auth.login.index') }}" class="theme-btn5">Login <i class="fa-solid fa-sign-in"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
{{-- =====HEADER END======= --}}

{{-- ===== MOBILE HEADER STARTS ======= --}}
<div class="mobile-header mobile-header4 d-block d-lg-none">
    <div class="container-fluid">
        <div class="col-12">
            <div class="mobile-header-elements d-flex align-items-center justify-content-between">
                <div class="mobile-logo">
                    <a href="#"><img src="{{ asset('logo/logo-color.png') }}" alt="UPTDPemakaman Dinas Perkim Kabupaten Tangerang"></a>
                </div>
                <div class="mobile-right d-flex gap-2 align-items-center">
                    <div class="search-icon header__search header-search-btn">
                        <a href="#"><i class="fa-solid fa-search fs-5 text-dark"></i></a>
                    </div>
                    <div class="mobile-nav-icon dots-menu" aria-label="Mobile menu toggle" role="button" tabindex="0">
                        <i class="fa-solid fa-bars"></i>
                    </div>
                    <a href="{{ route('auth.login.index') }}" class="theme-btn5 p-2"><i class="fa-solid fa-sign-in"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== MOBILE SIDEBAR STARTS ===== --}}
<div class="mobile-sidebar mobile-sidebar4">
    <div class="logosicon-area">
        <div class="logos">
            <a href="#"><img src="{{ asset('logo/logo-color.png') }}" alt="UPTDPemakaman Dinas Perkim Kabupaten Tangerang"></a>
        </div>
        <div class="menu-close" role="button" tabindex="0" aria-label="Close mobile menu">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
                <path
                    d="M10.5859 12L2.79297 4.20706L4.20718 2.79285L12.0001 10.5857L19.793 2.79285L21.2072 4.20706L13.4143 12L21.2072 19.7928L19.793 21.2071L12.0001 13.4142L4.20718 21.2071L2.79297 19.7928L10.5859 12Z">
                </path>
            </svg>
        </div>
    </div>
    <nav class="mobile-nav mobile-nav1" role="navigation" aria-label="Mobile main navigation">
        <ul class="mobile-nav-list nav-list1">
            <li><a href="#">Beranda</a></li>

            <li><a href="#">Profile</a>
                <ul class="sub-menu">
                    <li><a href="#">Visi dan Misi</a></li>
                    <li><a href="#">Struktur Organisasi</a></li>
                    <li><a href="#">Regulasi dan SOP</a></li>
                    <li><a href="#">Laporan & Monitoring</a></li>
                    <li><a href="#">Kontak dan Alamat</a></li>
                </ul>
            </li>

            <li><a href="#">Data TPU</a></li>

            <li><a href="#">Layanan</a>
                <ul class="sub-menu">
                    <li><a href="#">Mobil Jenazah</a></li>
                </ul>
            </li>

            <li><a href="#">Informasi</a>
                <ul class="sub-menu">
                    <li><a href="#">Berita & Artikel</a></li>
                    <li><a href="#">Galeri</a></li>
                    <li><a href="#">Video</a></li>
                    <li><a href="#">Unduhan</a></li>
                </ul>
            </li>
        </ul>
    </nav>
</div>
{{-- ===== MOBILE SIDEBAR END ===== --}}
