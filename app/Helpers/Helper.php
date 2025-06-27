<?php
namespace App\Helpers;

use App\Models\PortalBanner;
use App\Models\PortalDataDirektur;
use App\Models\PortalFAQ;
use App\Models\PortalGaleri;
use App\Models\PortalKategori;
use App\Models\PortalLinks;
use App\Models\PortalPage;
use App\Models\PortalPesan;
use App\Models\PortalPost;
use App\Models\PortalSetup;
use App\Models\PortalTanos;
use App\Models\PortalUnduhan;
use App\Models\PortalVideo;
use App\Models\SysLogAktifitas;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class Helper
{
    // Daftar model dan URL routing
    protected static $varModels = [
        'Postingan' => PortalPost::class,
        'Halaman'   => PortalPage::class,
        'Banner'    => PortalBanner::class,
        'Galeri'    => PortalGaleri::class,
        'Video'     => PortalVideo::class,
        'Unduhan'   => PortalUnduhan::class,
        'FAQ'       => PortalFAQ::class,
        'Pesan'     => PortalPesan::class,
    ];

    /*
    |--------------------------------------------------------------------------
    | FUNCTION GLOBAL
    |--------------------------------------------------------------------------
     */
    // Untuk Encode String
    public static function encode($string)
    {
        $encode = date('Ymd') . $string;
        $encode = rtrim(strtr(base64_encode($encode), '+/', '-_'), '=');
        return $encode;
    }

    // Untuk Decode String
    public static function decode($string)
    {
        $decode = base64_decode(str_pad(strtr($string, '-_', '+/'), strlen($string) % 4, '=', STR_PAD_RIGHT));
        $decode = substr($decode, 8);
        return $decode;
    }

    // Untuk Crypt String
    public static function crypt($string)
    {
        $data = Crypt::encryptString($string);
        return $data;
    }

    // Untuk Dcrypt String
    public static function dcrypt($string)
    {
        $data = Crypt::decryptString($string);
        return $data;
    }

    // Untuk Kode String
    public static function gencode($bytes)
    {
        return bin2hex(random_bytes($bytes));
    }

    // Untuk Kode String
    public static function genzero($num, $value)
    {
        $data = sprintf("%0" . $num . "d", $value);
        return $data;
    }

    // Untuk Jenis Kelamin
    public static function getJKL($jkl)
    {
        if ($jkl == "L") {
            $gender = "Laki-laki";
        } else {
            $gender = "Perempuan";
        }
        return $gender;
    }

    // Untuk URL Image
    public static function urlImg($url)
    {
        if (empty($url)) {
            return $url;
        } else {
            if (Storage::disk('public')->exists($url)) {
                $url = asset('storage/' . $url);
            }
        }
        return $url;
    }

    // Untuk URL Image Avatar
    public static function urlAvatar($user)
    {
        $avatar = asset('custom/no-img-avatar.png');
        $foto   = $user->foto;
        if (empty($foto)) {
            $avatar = $avatar;
        } else {
            if (Storage::disk('public')->exists($foto)) {
                $avatar = asset('storage/' . $foto);
            } else {
                $avatar = $avatar;
            }
        }
        return $avatar;
    }

    // Untuk Mengubah ke format Rupiah
    public static function toRP($value)
    {
        if ($value != "" && $value !== null && $value != 0) {
            return "Rp. " . number_format($value, 0, ',', '.');
        }
        return "Rp. 0";
    }

    // Untuk format angka dengan titik
    public static function toDot($value)
    {
        $data = number_format($value, 0, ',', '.');
        return $data;
    }

    // Konvert Size Disk
    public static function SizeDisk($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }
        return $bytes;
    }

    // penyebut untuk terbilang
    public static function penyebut($nilai)
    {
        $nilai = abs($nilai);
        $huruf = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];
        $temp  = "";
        if ($nilai < 12) {
            $temp = " " . $huruf[$nilai];
        } elseif ($nilai < 20) {
            $temp = self::penyebut($nilai - 10) . " belas";
        } elseif ($nilai < 100) {
            $temp = self::penyebut($nilai / 10) . " puluh" . self::penyebut($nilai % 10);
        } elseif ($nilai < 200) {
            $temp = " seratus" . self::penyebut($nilai - 100);
        } elseif ($nilai < 1000) {
            $temp = self::penyebut($nilai / 100) . " ratus" . self::penyebut($nilai % 100);
        } elseif ($nilai < 2000) {
            $temp = " seribu" . self::penyebut($nilai - 1000);
        } elseif ($nilai < 1000000) {
            $temp = self::penyebut($nilai / 1000) . " ribu" . self::penyebut($nilai % 1000);
        } elseif ($nilai < 1000000000) {
            $temp = self::penyebut($nilai / 1000000) . " juta" . self::penyebut($nilai % 1000000);
        } elseif ($nilai < 1000000000000) {
            $temp = self::penyebut($nilai / 1000000000) . " milyar" . self::penyebut(fmod($nilai, 1000000000));
        } elseif ($nilai < 1000000000000000) {
            $temp = self::penyebut($nilai / 1000000000000) . " trilyun" . self::penyebut(fmod($nilai, 1000000000000));
        }
        return $temp;
    }

    // terbilang
    public static function terbilang($nilai)
    {
        if ($nilai < 0) {
            $hasil = "minus " . trim(self::penyebut($nilai));
        } else {
            $hasil = trim(self::penyebut($nilai));
        }
        return $hasil;
    }

    // cek user online
    public static function isUserOnline($uuid)
    {
        return Cache::has('user-is-online-' . $uuid);
    }

    // generate bg login
    public static function getRandomBgLogin()
    {
        return "login_" . rand(1, 5) . ".png";
    }

    // get profile picture
    public static function pp($url)
    {
        if ($url != "" && $url !== null) {
            // cek avatar
            $avatarPath = str_replace('.', '_avatar.', $url);
            if (Storage::disk('public')->exists($avatarPath)) {
                $url = asset('storage/' . $avatarPath);
            } elseif (Storage::disk('public')->exists($url)) {
                $url = asset('storage/' . $url);
            } else {
                $url = asset('custom/no-img-avatar.png');
            }
            return $url;
        } else {
            return asset('custom/no-img-avatar.png');
        }
    }

    // get thumbnail
    public static function thumbnail($url)
    {
        if ($url != "" && $url !== null) {
            // cek thumbnail
            $thumbnailPath = str_replace('.', '_thumbnail.', $url);
            if (Storage::disk('public')->exists($thumbnailPath)) {
                $url = asset('storage/' . $thumbnailPath);
            } elseif (Storage::disk('public')->exists($url)) {
                $url = asset('storage/' . $url);
            } else {
                $url = asset('custom/no-img-post.png');
            }
            return $url;
        } else {
            return asset('custom/no-img-post.png');
        }
    }

    // get thumbnailUnduhan
    public static function thumbnailUnduhan($url, $extension)
    {
        if ($url != "" && $url !== null) {
            $thumbnailPath = str_replace('.', '_thumbnail.', $url);
            if (Storage::disk('public')->exists($thumbnailPath)) {
                $url = asset('storage/' . $thumbnailPath);
            } elseif (Storage::disk('public')->exists($url)) {
                $url = asset('storage/' . $url);
            } else {
                $url = self::getFileIcons($extension);
            }
        } else {
            $url = self::getFileIcons($extension);
        }
        return $url;
    }

    // get DataPP
    public static function DataPP()
    {
        $auth = Auth::user();
        return $auth->RelPortalActor;
    }

    // get file icons
    public static function getFileIcons($extension)
    {
        if (isset($extension) && $extension !== null) {
            $pathIcons    = "assets-admin/dist/icons/files/" . $extension . ".png";
            $defaultIcons = "assets-admin/dist/icons/files/paper-clip.png";
            // Memeriksa apakah file ada
            if (file_exists(public_path($pathIcons))) {
                return asset($pathIcons);
            } else {
                return asset($defaultIcons);
            }
        } else {
            return asset("assets-admin/dist/icons/files/eksternal.png");
        }
    }

    public static function getFileUnduhanKategori($tipe)
    {
        // Konversi tipe menjadi huruf kecil
        $fileExtension = strtolower($tipe);
        // Kategori file berdasarkan tipe ekstensi
        $categories = [
            'gambar'   => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'tiff', 'webp'],
            'dokumen'  => ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'ods', 'odp', 'rtf', 'pdf', 'txt', 'csv', 'xml', 'json', 'md'],
            'suara'    => ['mp3', 'wav', 'ogg', 'm4a', 'flac', 'aac'],
            'video'    => ['mp4', 'mkv', 'avi', 'mov', 'wmv', 'flv', 'webm', '3gp', 'mpeg'],
            'kompresi' => ['zip', 'rar', 'tar', 'gz', '7z', 'bz2', 'xz'],
            'lainnya'  => ['iso'],
        ];

        foreach ($categories as $category => $extensions) {
            if (in_array($fileExtension, $extensions)) {
                return ucfirst($category);
            }
        }

        return 'Tidak diketahui';
    }

    // get backgroundGambarHero
    public static function backgroundGambarHero($model, $url)
    {
        if (! empty($url) && is_string($url)) {
            if (Storage::disk('public')->exists($url)) {
                return asset('storage/' . $url);
            }
        }

        return ($model === "Versi 2")
        ? asset('custom/hero/hero-versi-2.png')
        : asset('custom/hero/hero-default.jpg');
    }

    // get backgroundVideoHero
    public static function backgroundVideoHero($url)
    {
        if (! empty($url) && is_string($url)) {
            if (Storage::disk('public')->exists($url)) {
                return asset('storage/' . $url);
            }
        }

        return asset('custom/hero/videoplayback.mp4');
    }

    // get illustrationHero
    public static function illustrationHero($model, $url)
    {
        if (! empty($url) && is_string($url)) {
            if (Storage::disk('public')->exists($url)) {
                return asset('storage/' . $url);
            }
        }

        return ($model === "Versi 4")
        ? asset('custom/illustration/illustration-tilt.png')
        : asset('custom/illustration/illustration-default-min.jpg');
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCTION DATE
    |--------------------------------------------------------------------------
     */
    // Tanggal Simple
    public static function TglSimple($tgl)
    {
        $tgll       = "$tgl";
        $day        = date('D', strtotime($tgll));
        $dayOne     = date('d', strtotime($tgll));
        $array_hari = [
            'Sun' => 'Minggu',
            'Mon' => 'Senin',
            'Tue' => 'Selasa',
            'Wed' => 'Rabu',
            'Thu' => 'Kamis',
            'Fri' => 'Jum\'at',
            'Sat' => 'Sabtu',
        ];
        $blnn        = date('m', strtotime($tgll));
        $array_bulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05'   => 'Mei', '06'      => 'Juni',
            '07' => 'Juli', '08'    => 'Agustus', '09'  => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];
        $thnn = date('y', strtotime($tgll));
        return $dayOne . "/" . $blnn . "/" . $thnn;
    }

    // Hari, Tanggal
    public static function hariTgl($tgl)
    {
        $tgll       = "$tgl";
        $day        = date('D', strtotime($tgll));
        $dayOne     = date('d', strtotime($tgll));
        $array_hari = [
            'Sun' => 'Minggu', 'Mon' => 'Senin', 'Tue' => 'Selasa',
            'Wed' => 'Rabu', 'Thu'   => 'Kamis', 'Fri' => 'Jum\'at', 'Sat' => 'Sabtu',
        ];
        $blnn        = date('m', strtotime($tgll));
        $array_bulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05'   => 'Mei', '06'      => 'Juni',
            '07' => 'Juli', '08'    => 'Agustus', '09'  => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];
        $thnn = date('Y', strtotime($tgll));
        return $array_hari[$day] . ", " . $dayOne . " " . $array_bulan[$blnn] . " " . $thnn;
    }

    // Hari, Tanggal dengan Jam
    public static function hariTglJam($tgl)
    {
        $tgll       = "$tgl";
        $day        = date('D', strtotime($tgll));
        $dayOne     = date('d', strtotime($tgll));
        $array_hari = [
            'Sun' => 'Minggu', 'Mon' => 'Senin', 'Tue' => 'Selasa',
            'Wed' => 'Rabu', 'Thu'   => 'Kamis', 'Fri' => 'Jum\'at', 'Sat' => 'Sabtu',
        ];
        $blnn        = date('m', strtotime($tgll));
        $array_bulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05'   => 'Mei', '06'      => 'Juni',
            '07' => 'Juli', '08'    => 'Agustus', '09'  => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];
        $thnn = date('Y', strtotime($tgll));
        $Jam  = date('H:i:s', strtotime($tgll));
        return $array_hari[$day] . ", " . $dayOne . " " . $array_bulan[$blnn] . " " . $thnn . " - " . $Jam;
    }

    // Tanggal Bulan Tahun (TTD)
    public static function tglBlnThn($tgl)
    {
        $tgll        = "$tgl";
        $dayOne      = date('d', strtotime($tgll));
        $blnn        = date('m', strtotime($tgll));
        $array_bulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05'   => 'Mei', '06'      => 'Juni',
            '07' => 'Juli', '08'    => 'Agustus', '09'  => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];
        $thnn = date('Y', strtotime($tgll));
        return $dayOne . " " . $array_bulan[$blnn] . " " . $thnn;
    }

    // tanggal jam
    public static function TglJam($tgl)
    {
        $tgll        = "$tgl";
        $dayOne      = date('d', strtotime($tgll));
        $blnn        = date('m', strtotime($tgll));
        $array_bulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05'   => 'Mei', '06'      => 'Juni',
            '07' => 'Juli', '08'    => 'Agustus', '09'  => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];
        $thnn = date('Y', strtotime($tgll));
        $jam  = date('H:i', strtotime($tgll));
        return $dayOne . " " . $array_bulan[$blnn] . " " . $thnn . ", " . $jam;
    }

    // Bulan
    public static function intToMonth($int)
    {
        $array_bulan = [
            '1'  => 'Januari', '2'  => 'Februari', '3'  => 'Maret',
            '4'  => 'April', '5'    => 'Mei', '6'       => 'Juni',
            '7'  => 'Juli', '8'     => 'Agustus', '9'   => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];

        return $array_bulan[$int];
    }

    // Array Bulan
    public static function arMonth()
    {
        $array_bulan = [
            '1'  => 'Januari', '2'  => 'Februari', '3'  => 'Maret',
            '4'  => 'April', '5'    => 'Mei', '6'       => 'Juni',
            '7'  => 'Juli', '8'     => 'Agustus', '9'   => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];

        return $array_bulan;
    }

    // Array Bulan Short
    public static function arMonth2($bln)
    {
        $array_bulan = [
            '1'  => 'Jan', '2'  => 'Feb', '3'  => 'Mar',
            '4'  => 'Apr', '5'  => 'Mei', '6'  => 'Jun',
            '7'  => 'Jul', '8'  => 'Aug', '9'  => 'Sep',
            '10' => 'Oct', '11' => 'Nov', '12' => 'Dec',
        ];

        return $array_bulan[$bln];
    }

    // Bulan to romawi
    public static function bln2Romawi($bln)
    {
        $array_bulan = [
            '1'  => 'I', '2'   => 'II', '3'   => 'III',
            '4'  => 'IV', '5'  => 'V', '6'    => 'VI',
            '7'  => 'VII', '8' => 'VIII', '9' => 'IX',
            '10' => 'X', '11'  => 'XI', '12'  => 'XII',
        ];

        return $array_bulan[$bln];
    }

    // Jam dan Menit
    public static function jamMenit($timestamp)
    {
        $jamMenit = date('H:i', strtotime($timestamp));
        return $jamMenit;
    }

    // Hitung Berapa Hari Ke Sekarang
    public static function hitungHariSekarang($tgl)
    {
        $date = Carbon::parse($tgl);
        $now  = Carbon::now('Asia/Jakarta');
        $diff = $date->diffInDays($now);
        return (int) $diff;
    }

    // Hitung Berapa Jam Ke Sekarang
    public static function hitungJamSekarang($tgl)
    {
        $date = Carbon::parse($tgl);
        $now  = Carbon::now('Asia/Jakarta');
        $diff = $date->diffInHours($now);
        return (int) $diff;
    }

    // add time carbon
    public static function addTimeCarbon($startDate, $amount, $type)
    {
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $startDate);

        if ($type == 'days') {
            $date->addDays($amount);
        } elseif ($type == 'hours') {
            $date->addHours($amount);
        } elseif ($type == 'minutes') {
            $date->addMinutes($amount);
        } else {
            throw new \InvalidArgumentException('Invalid type provided. Use "days", "hours", or "minutes".');
        }

        return $date->format('Y-m-d H:i:s');
    }

    // Ucapan Waktu
    public static function Greeting()
    {
        $waktu = gmdate("H:i", time() + 7 * 3600);
        $t     = explode(":", $waktu);
        $jam   = $t[0];
        $menit = $t[1];

        if ($jam >= 00 && $jam < 10) {
            if ($menit > 00 && $menit < 60) {
                $ucapan = "Selamat Pagi";
            } else {
                $ucapan = "";
            }
        } elseif ($jam >= 10 && $jam < 15) {
            if ($menit > 00 && $menit < 60) {
                $ucapan = "Selamat Siang";
            } else {
                $ucapan = "";
            }
        } elseif ($jam >= 15 && $jam < 18) {
            if ($menit > 00 && $menit < 60) {
                $ucapan = "Selamat Sore";
            } else {
                $ucapan = "";
            }
        } elseif ($jam >= 18 && $jam <= 24) {
            if ($menit > 00 && $menit < 60) {
                $ucapan = "Selamat Malam";
            } else {
                $ucapan = "";
            }
        } else {
            $ucapan = "";
        }
        return $ucapan;
    }

    // Panggilan
    public static function Panggilan($jkl)
    {
        if ($jkl == "L" || $jkl == "Laki-laki") {
            $panggilan = "Bapak ";
        } elseif ($jkl == "P" || $jkl == "Perempuan") {
            $panggilan = "Ibu ";
        } else {
            $panggilan = "";
        }
        return $panggilan;
    }

    // welcomeBack
    public static function welcomeBack()
    {
        $auth         = Auth::user();
        $nama_lengkap = $auth->RelPortalActor->nama_lengkap;
        $waktu        = self::Greeting();
        $welcome      = $waktu . " <strong>" . $nama_lengkap . "</strong>";

        return $welcome;
    }

    // getBulanLetterFromParameter
    public static function getBulanLetterFromParameter($bulan)
    {
        switch ($bulan) {
            case '01':return "Januari";
            case '02':return "Februari";
            case '03':return "Maret";
            case '04':return "April";
            case '05':return "Mei";
            case '06':return "Juni";
            case '07':return "Juli";
            case '08':return "Agustus";
            case '09':return "September";
            case '10':return "Oktober";
            case '11':return "November";
            case '12':return "Desember";
            default: return "NONE";
        }
    }

    // getBulanLetter
    public static function getBulanLetter()
    {
        return self::getBulanLetterFromParameter(date('m'));
    }

    // getRomawi
    public static function getRomawi()
    {
        return self::bln2Romawi(date('m'));
    }

    // getFilterTahun
    public static function getFilterTahun()
    {
        if (Session::exists('filter_tahun')) {
            $tahun = Session::get('filter_tahun');
        } else {
            $tahun = date('Y');
            Session::put('filter_tahun', $tahun);
        }
        return $tahun;
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCTION API - Indonesia Region API
    |--------------------------------------------------------------------------
     */
    // KWID PROVINSI LIST
    public static function getProvinsiList()
    {
        $response = Http::get('https://kwid.codingers.id/api/v2/list-prov');

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    // KWID KABUPATEN LIST
    public static function getKabupatenList($province_id)
    {
        $response = Http::get("https://kwid.codingers.id/api/v2/list-kab/{$province_id}");

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    // KWID KECAMATAN LIST
    public static function getKecamatanList($regency_id)
    {
        $response = Http::get("https://kwid.codingers.id/api/v2/list-kec/{$regency_id}");

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    // KWID DESA LIST
    public static function getDesaList($district_id)
    {
        $response = Http::get("https://kwid.codingers.id/api/v2/list-desa/{$district_id}");

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    // KWID PROVINSI DETAIL
    public static function getProvinsiDetail($id)
    {
        $response = Http::get("https://kwid.codingers.id/api/v2/detail-prov/{$id}");

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    // KWID KABUPATEN DETAIL
    public static function getKabupatenDetail($id)
    {
        $response = Http::get("https://kwid.codingers.id/api/v2/detail-kab/{$id}");

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    // KWID KECAMATAN DETAIL
    public static function getKecamatanDetail($id)
    {
        $response = Http::get("https://kwid.codingers.id/api/v2/detail-kec/{$id}");

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    // KWID DESA DETAIL
    public static function getDesaDetail($id)
    {
        $response = Http::get("https://kwid.codingers.id/api/v2/detail-desa/{$id}");

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCTION UPLOAD - Updated for Laravel 12 & Intervention Image v3
    |--------------------------------------------------------------------------
     */

    // Untuk Upload Gambar
    public static function UpImg($request, $field, $path)
    {
        $file = $request->file($field);

        // Validasi apakah file ada dan valid
        if ($file && $file->isValid()) {
            $filename          = $file->getClientOriginalName();
            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            $fileExtension     = strtolower($file->getClientOriginalExtension());

            if (! in_array($fileExtension, $allowedExtensions)) {
                return "0"; // File tidak diizinkan
            }

            if (! self::isValidImage($file)) {
                return "0"; // Bukan gambar valid
            }

            // Nama file baru
            $fileName = Str::uuid() . "-" . rand(1000, 9999999999) . "." . $fileExtension;
            $filePath = $path . "/" . $fileName;

            // Pastikan folder tersedia
            if (! is_dir(storage_path('app/public/' . $path))) {
                Storage::disk('public')->makeDirectory($path);
            }

            // Buka dan kompres gambar original maksimal 500KB
            $image   = Image::read($file);
            $quality = 90;

            do {
                $encoded = $image->toJpeg($quality);
                $size    = strlen((string) $encoded);
                $quality -= 5;
            } while ($size > 500 * 1024 && $quality > 10);

            // Simpan gambar original terkompresi
            $fullPath = storage_path('app/public/' . $filePath);
            file_put_contents($fullPath, $encoded);

            // Buat versi _thumbnail
            self::createThumbnail($image, $path, $fileName, $fileExtension);

            return $filePath;
        }

        return "0"; // File tidak valid
    }

    // Untuk Upload Gambar Post
    public static function UpImgPost($request, $field, $paths)
    {
        $detail = $request->input($field);
        libxml_use_internal_errors(true);

        $dom = new \DomDocument('1.0', 'UTF-8');
        $dom->loadHTML(mb_convert_encoding($detail, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $xpath = new \DOMXPath($dom);

        // 🛡️ Hapus semua <script>
        foreach ($dom->getElementsByTagName('script') as $script) {
            $script->parentNode->removeChild($script);
        }

        // 🛡️ Hapus atribut onX & javascript: href
        foreach ($xpath->query('//*') as $node) {
            if ($node->hasAttributes()) {
                $attrsToRemove = [];
                foreach ($node->attributes as $attr) {
                    if (preg_match('/^on/i', $attr->name)) {
                        $attrsToRemove[] = $attr->name;
                    }

                    if (strtolower($attr->name) === 'href' && preg_match('/^javascript:/i', $attr->value)) {
                        $attrsToRemove[] = $attr->name;
                    }
                }
                foreach ($attrsToRemove as $attrName) {
                    $node->removeAttribute($attrName);
                }
            }
        }

        // 🔧 Tangani gambar base64
        $images = $dom->getElementsByTagName('img');
        foreach ($images as $k => $img) {
            $data = $img->getAttribute('src');
            if (strstr($data, "data:image")) {
                list($type, $data) = explode(';', $data);
                list(, $data)      = explode(',', $data);

                $data   = base64_decode($data);
                $folder = trim($paths, '/');
                Storage::disk('public')->makeDirectory($folder);

                $image_name = $folder . "/" . self::gencode(3) . "-" . date('dmy') . "-" . rand(1000, 9999999) . "-" . $k . '.png';
                $path       = storage_path() . "/app/public/" . $image_name;

                // Simpan gambar
                file_put_contents($path, $data);

                // Resize dan kompres jika diperlukan
                self::resizeImageIfNeeded($path);

                $url_img = "/storage/" . $image_name;

                // Bersihkan atribut lama
                $img->removeAttribute('data-filename');
                $img->removeAttribute('style');
                $img->removeAttribute('src');

                // ✅ Tambahkan src aman
                $img->setAttribute('src', $url_img);
            }
        }

        return html_entity_decode($dom->saveHTML(), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    // Untuk Update Gambar Post
    public static function UpdateImgPost($request, $field, $paths)
    {
        $detail = $request->input($field);
        libxml_use_internal_errors(true);

        $dom = new \DomDocument('1.0', 'UTF-8');
        $dom->loadHTML(mb_convert_encoding($detail, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $xpath = new \DOMXPath($dom);

        // 🛡️ Hapus semua <script>
        foreach ($dom->getElementsByTagName('script') as $script) {
            $script->parentNode->removeChild($script);
        }

        // 🛡️ Hapus atribut onX & javascript: href
        foreach ($xpath->query('//*') as $node) {
            if ($node->hasAttributes()) {
                $attrsToRemove = [];
                foreach ($node->attributes as $attr) {
                    if (preg_match('/^on/i', $attr->name)) {
                        $attrsToRemove[] = $attr->name;
                    }

                    if (strtolower($attr->name) === 'href' && preg_match('/^javascript:/i', $attr->value)) {
                        $attrsToRemove[] = $attr->name;
                    }
                }
                foreach ($attrsToRemove as $attrName) {
                    $node->removeAttribute($attrName);
                }
            }
        }

        // 🔧 Tangani <img>
        $images = $dom->getElementsByTagName('img');

        foreach ($images as $k => $img) {
            $data = $img->getAttribute('src');

            // Jika gambar masih berupa base64 (upload baru)
            if (strstr($data, "data:image")) {
                list($type, $data) = explode(';', $data);
                list(, $data)      = explode(',', $data);
                $data              = base64_decode($data);

                $folder = trim($paths, '/'); // Pastikan path tanpa /
                Storage::disk('public')->makeDirectory($folder);

                $image_name = $folder . "/" . self::gencode(3) . "-" . date('dmy') . "-" . rand(1000, 9999999) . "-" . $k . '.png';
                $path       = storage_path("app/public/" . $image_name);

                // Simpan gambar ke storage
                file_put_contents($path, $data);

                // Resize dan kompresi gambar jika diperlukan
                self::resizeImageIfNeeded($path);

                $url_img = "/storage/" . $image_name;
            } else {
                // Jika gambar sudah berupa path (misalnya hasil edit ulang di Summernote)
                $url_img = $data;

                // Bersihkan domain backend dari src jika ada
                $backendUrl = url('/');
                if (strpos($url_img, $backendUrl) !== false) {
                    $url_img = str_replace($backendUrl, '', $url_img);
                }
            }

            // Update atribut gambar
            $img->removeAttribute('data-filename');
            $img->removeAttribute('style');
            $img->setAttribute('src', $url_img);
        }

        return html_entity_decode($dom->saveHTML(), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    // Untuk Update Gambar Post dengan Indexing
    public static function UpdateImgIndexing($field, $paths)
    {
        $detail = $field;
        libxml_use_internal_errors(true);

        $dom = new \DomDocument('1.0', 'UTF-8');
        $dom->loadHTML(mb_convert_encoding($detail, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $xpath = new \DOMXPath($dom);

        // 🛡️ Hapus semua <script>
        foreach ($dom->getElementsByTagName('script') as $script) {
            $script->parentNode->removeChild($script);
        }

        // 🛡️ Hapus atribut seperti onerror, onclick, dll serta href="javascript:..."
        foreach ($xpath->query('//*') as $node) {
            if ($node->hasAttributes()) {
                $attrsToRemove = [];
                foreach ($node->attributes as $attr) {
                    if (preg_match('/^on/i', $attr->name)) {
                        $attrsToRemove[] = $attr->name;
                    }

                    if (strtolower($attr->name) === 'href' && preg_match('/^javascript:/i', $attr->value)) {
                        $attrsToRemove[] = $attr->name;
                    }
                }
                foreach ($attrsToRemove as $attrName) {
                    $node->removeAttribute($attrName);
                }
            }
        }

        // 🔧 Tangani gambar
        $images    = $dom->getElementsByTagName('img');
        $maxImages = 10;
        $imgCount  = 0;

        foreach ($images as $k => $img) {
            $data = $img->getAttribute('src');

            if (strstr($data, "data:image") && $imgCount < $maxImages) {
                try {
                    list($typeInfo, $base64Data) = explode(';', $data);
                    list(, $base64Data)          = explode(',', $base64Data);

                    if (! base64_decode($base64Data, true)) {
                        continue;
                    }

                    $rawData = base64_decode($base64Data);

                    $folder = trim($paths, '/');
                    Storage::disk('public')->makeDirectory($folder);

                    $imageName = $folder . "/" . self::gencode(3) . "-" . date('dmy') . "-" . rand(1000, 9999999) . "-" . $k . '.webp';
                    $path      = storage_path("app/public/" . $imageName);

                    $image = Image::read($rawData);

                    if ($image->width() > 1024) {
                        $image->resize(1024, null, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        });
                    }

                    $image->toWebp(85)->save($path);

                    $url_img = "/storage/" . $imageName;

                    $img->removeAttribute('data-filename');
                    $img->removeAttribute('style'); // Optional: hapus style langsung dari Summernote
                    $img->removeAttribute('src');
                    $img->setAttribute('src', $url_img);

                    $imgCount++;
                } catch (\Exception $e) {
                    \Log::error("Gagal proses gambar indexing: " . $e->getMessage());
                    continue;
                }
            }
        }

        return html_entity_decode($dom->saveHTML(), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    // Untuk Upload Gambar Post (dengan auto-kompres & atribut tambahan)
    public static function UpImgPostWithCompress($request, $field, $paths)
    {
        $detail = $request->input($field);
        libxml_use_internal_errors(true);
        $dom = new \DomDocument('1.0', 'UTF-8');

        // Konversi aman untuk HTML + emoji
        $dom->loadHTML(mb_convert_encoding($detail, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $xpath = new \DOMXPath($dom);

        // 🔥 1. Hapus semua <script> tag
        foreach ($dom->getElementsByTagName('script') as $script) {
            $script->parentNode->removeChild($script);
        }

        // 🔥 2. Hapus semua atribut onX (onerror, onclick, dll) dari semua elemen
        foreach ($xpath->query('//*') as $node) {
            if ($node->hasAttributes()) {
                $attrsToRemove = [];
                foreach ($node->attributes as $attr) {
                    if (preg_match('/^on/i', $attr->name)) {
                        $attrsToRemove[] = $attr->name;
                    }
                    // Hapus juga href javascript:... (XSS vector)
                    if (strtolower($attr->name) === 'href' && preg_match('/^javascript:/i', $attr->value)) {
                        $attrsToRemove[] = $attr->name;
                    }
                }
                foreach ($attrsToRemove as $attrName) {
                    $node->removeAttribute($attrName);
                }
            }
        }

        // 🔧 3. Tangani gambar
        $images    = $dom->getElementsByTagName('img');
        $maxImages = 10;
        $imgCount  = 0;

        foreach ($images as $k => $img) {
            $data = $img->getAttribute('src');

            if (strstr($data, "data:image") && $imgCount < $maxImages) {
                try {
                    list($typeInfo, $base64Data) = explode(';', $data);
                    list(, $base64Data)          = explode(',', $base64Data);

                    if (! base64_decode($base64Data, true)) {
                        continue;
                    }

                    $rawData = base64_decode($base64Data);
                    $folder  = trim($paths, '/');
                    Storage::disk('public')->makeDirectory($folder);
                    $imageName = $folder . "/" . self::gencode(3) . "-" . date('dmy') . "-" . rand(1000, 9999999) . "-" . $k . '.webp';
                    $path      = storage_path("app/public/" . $imageName);

                    $image = Image::read($rawData);
                    if ($image->width() > 1024) {
                        $image->resize(1024, null, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        });
                    }

                    $image->toWebp(85)->save($path);

                    $img->removeAttribute('src');
                    $img->removeAttribute('data-filename');
                    $img->removeAttribute('style');

                    $img->setAttribute('src', "/storage/" . $imageName);
                    $img->setAttribute('class', 'img-fluid lazy-image');
                    $img->setAttribute('draggable', 'false');
                    $img->setAttribute('loading', 'lazy');
                    $img->setAttribute('onload', "this.classList.add('loaded')");
                    $img->setAttribute('onerror', "this.src='/images/banner/no-img-post.png'; this.classList.add('loaded')");

                    $imgCount++;
                } catch (\Exception $e) {
                    \Log::error("Gagal upload compress image WebP (store): " . $e->getMessage());
                    continue;
                }
            }
        }

        return html_entity_decode($dom->saveHTML(), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    // Update Gambar Post dengan Kompresi
    public static function UpdateImgPostWithCompress($request, $field, $paths)
    {
        $detail = $request->input($field);
        libxml_use_internal_errors(true);

        $dom = new \DomDocument('1.0', 'UTF-8');
        $dom->loadHTML(mb_convert_encoding($detail, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $xpath = new \DOMXPath($dom);

        // 🛡️ Hapus semua <script>
        foreach ($dom->getElementsByTagName('script') as $script) {
            $script->parentNode->removeChild($script);
        }

        // 🛡️ Hapus atribut berbahaya (onerror, onclick, javascript:href, dll)
        foreach ($xpath->query('//*') as $node) {
            if ($node->hasAttributes()) {
                $attrsToRemove = [];
                foreach ($node->attributes as $attr) {
                    if (preg_match('/^on/i', $attr->name)) {
                        $attrsToRemove[] = $attr->name;
                    }

                    if (strtolower($attr->name) === 'href' && preg_match('/^javascript:/i', $attr->value)) {
                        $attrsToRemove[] = $attr->name;
                    }
                }
                foreach ($attrsToRemove as $attrName) {
                    $node->removeAttribute($attrName);
                }
            }
        }

        // 🔧 Tangani <img>
        $images    = $dom->getElementsByTagName('img');
        $maxImages = 10;
        $imgCount  = 0;

        foreach ($images as $k => $img) {
            $data = $img->getAttribute('src');

            if (strstr($data, "data:image") && $imgCount < $maxImages) {
                try {
                    list($typeInfo, $base64Data) = explode(';', $data);
                    list(, $base64Data)          = explode(',', $base64Data);

                    if (! base64_decode($base64Data, true)) {
                        continue;
                    }

                    $rawData = base64_decode($base64Data);
                    $folder  = trim($paths, '/');
                    Storage::disk('public')->makeDirectory($folder);

                    $imageName = $folder . "/" . self::gencode(3) . "-" . date('dmy') . "-" . rand(1000, 9999999) . "-" . $k . '.webp';
                    $path      = storage_path("app/public/" . $imageName);

                    $image = Image::read($rawData);
                    if ($image->width() > 1024) {
                        $image->resize(1024, null, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        });
                    }

                    $image->toWebp(85)->save($path);

                    $url_img = "/storage/" . $imageName;
                    $imgCount++;
                } catch (\Exception $e) {
                    \Log::error("Gagal upload compress image WebP (update): " . $e->getMessage());
                    continue;
                }
            } else {
                // Gambar lama (bukan base64)
                $url_img    = $data;
                $backendUrl = url('/');
                if (strpos($url_img, $backendUrl) !== false) {
                    $url_img = str_replace($backendUrl, '', $url_img);
                }
            }

            // Bersihkan atribut lama
            $img->removeAttribute('src');
            $img->removeAttribute('data-filename');
            $img->removeAttribute('style');

            // ✅ Tambahkan atribut Nuxt-friendly
            $img->setAttribute('src', $url_img);
            $img->setAttribute('class', 'img-fluid lazy-image');
            $img->setAttribute('draggable', 'false');
            $img->setAttribute('loading', 'lazy');
            $img->setAttribute('onload', "this.classList.add('loaded')");
            $img->setAttribute('onerror', "this.src='/images/banner/no-img-post.png'; this.classList.add('loaded')");
        }

        return html_entity_decode($dom->saveHTML(), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Upload dan proses gambar dari TinyMCE (untuk Create)
     */
    public static function UpImgPostTinyMCE($request, $field, $paths)
    {
        $detail = $request->input($field);
        if (empty($detail)) {
            return $detail;
        }

        libxml_use_internal_errors(true);
        $dom = new \DomDocument('1.0', 'UTF-8');

        // Load HTML dengan encoding yang proper untuk TinyMCE
        $dom->loadHTML(mb_convert_encoding($detail, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $xpath = new \DOMXPath($dom);

        // 🛡️ Security: Hapus semua <script> tags
        foreach ($dom->getElementsByTagName('script') as $script) {
            $script->parentNode->removeChild($script);
        }

        // 🛡️ Security: Hapus atribut berbahaya (onX events, javascript:href)
        foreach ($xpath->query('//*') as $node) {
            if ($node->hasAttributes()) {
                $attrsToRemove = [];
                foreach ($node->attributes as $attr) {
                    // Remove onX attributes (onerror, onclick, etc)
                    if (preg_match('/^on/i', $attr->name)) {
                        $attrsToRemove[] = $attr->name;
                    }
                    // Remove javascript: href
                    if (strtolower($attr->name) === 'href' && preg_match('/^javascript:/i', $attr->value)) {
                        $attrsToRemove[] = $attr->name;
                    }
                }
                foreach ($attrsToRemove as $attrName) {
                    $node->removeAttribute($attrName);
                }
            }
        }

        // 🔧 Process Images from TinyMCE
        $images    = $dom->getElementsByTagName('img');
        $maxImages = 10;
        $imgCount  = 0;

        foreach ($images as $k => $img) {
            $src = $img->getAttribute('src');

            // Handle TinyMCE blob URLs and base64 images
            if ((strpos($src, 'blob:') === 0) || (strpos($src, 'data:image') === 0)) {
                if ($imgCount >= $maxImages) {
                    continue;
                }

                try {
                    if (strpos($src, 'data:image') === 0) {
                        // Handle base64 images
                        list($typeInfo, $base64Data) = explode(';', $src);
                        list(, $base64Data)          = explode(',', $base64Data);

                        if (! base64_decode($base64Data, true)) {
                            continue;
                        }

                        $rawData = base64_decode($base64Data);
                    } else {
                        // Handle blob URLs (skip for now as they're temporary)
                        continue;
                    }

                    $folder = trim($paths, '/');
                    Storage::disk('public')->makeDirectory($folder);

                    $imageName = $folder . "/" . self::gencode(3) . "-" . date('dmy') . "-" . rand(1000, 9999999) . "-" . $k . '.webp';
                    $path      = storage_path("app/public/" . $imageName);

                    // Compress and resize image
                    $image = Image::read($rawData);
                    if ($image->width() > 1024) {
                        $image->resize(1024, null, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        });
                    }

                    $image->toWebp(85)->save($path);

                    // Clean old attributes
                    $img->removeAttribute('src');
                    $img->removeAttribute('data-mce-src');      // TinyMCE specific
                    $img->removeAttribute('data-mce-selected'); // TinyMCE specific
                    $img->removeAttribute('style');

                    // Set new attributes
                    $img->setAttribute('src', "/storage/" . $imageName);
                    $img->setAttribute('class', 'img-fluid lazy-image');
                    $img->setAttribute('draggable', 'false');
                    $img->setAttribute('loading', 'lazy');
                    $img->setAttribute('alt', 'Gambar konten');

                    $imgCount++;
                } catch (\Exception $e) {
                    \Log::error("TinyMCE Image Upload Error (Create): " . $e->getMessage());
                    continue;
                }
            }
        }

        return html_entity_decode($dom->saveHTML(), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Update dan proses gambar dari TinyMCE (untuk Update)
     */
    public static function UpdateImgPostTinyMCE($request, $field, $paths)
    {
        $detail = $request->input($field);
        if (empty($detail)) {
            return $detail;
        }

        libxml_use_internal_errors(true);
        $dom = new \DomDocument('1.0', 'UTF-8');
        $dom->loadHTML(mb_convert_encoding($detail, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $xpath = new \DOMXPath($dom);

        // 🛡️ Security filtering
        foreach ($dom->getElementsByTagName('script') as $script) {
            $script->parentNode->removeChild($script);
        }

        foreach ($xpath->query('//*') as $node) {
            if ($node->hasAttributes()) {
                $attrsToRemove = [];
                foreach ($node->attributes as $attr) {
                    if (preg_match('/^on/i', $attr->name)) {
                        $attrsToRemove[] = $attr->name;
                    }
                    if (strtolower($attr->name) === 'href' && preg_match('/^javascript:/i', $attr->value)) {
                        $attrsToRemove[] = $attr->name;
                    }
                }
                foreach ($attrsToRemove as $attrName) {
                    $node->removeAttribute($attrName);
                }
            }
        }

        // 🔧 Process Images
        $images    = $dom->getElementsByTagName('img');
        $maxImages = 10;
        $imgCount  = 0;

        foreach ($images as $k => $img) {
            $src    = $img->getAttribute('src');
            $newSrc = $src;

            // Handle new base64 images from TinyMCE
            if (strpos($src, 'data:image') === 0 && $imgCount < $maxImages) {
                try {
                    list($typeInfo, $base64Data) = explode(';', $src);
                    list(, $base64Data)          = explode(',', $base64Data);

                    if (! base64_decode($base64Data, true)) {
                        continue;
                    }

                    $rawData = base64_decode($base64Data);
                    $folder  = trim($paths, '/');
                    Storage::disk('public')->makeDirectory($folder);

                    $imageName = $folder . "/" . self::gencode(3) . "-" . date('dmy') . "-" . rand(1000, 9999999) . "-" . $k . '.webp';
                    $path      = storage_path("app/public/" . $imageName);

                    // Compress and resize
                    $image = Image::read($rawData);
                    if ($image->width() > 1024) {
                        $image->resize(1024, null, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        });
                    }

                    $image->toWebp(85)->save($path);

                    $newSrc = "/storage/" . $imageName;
                    $imgCount++;
                } catch (\Exception $e) {
                    \Log::error("TinyMCE Image Update Error: " . $e->getMessage());
                    continue;
                }
            } else {
                // Handle existing images (clean domain if present)
                $backendUrl = url('/');
                if (strpos($newSrc, $backendUrl) !== false) {
                    $newSrc = str_replace($backendUrl, '', $newSrc);
                }
            }

            // Clean TinyMCE specific attributes
            $img->removeAttribute('data-mce-src');
            $img->removeAttribute('data-mce-selected');
            $img->removeAttribute('data-mce-style');
            $img->removeAttribute('style');

            // Set clean attributes
            $img->setAttribute('src', $newSrc);
            $img->setAttribute('class', 'img-fluid lazy-image');
            $img->setAttribute('draggable', 'false');
            $img->setAttribute('loading', 'lazy');

            // Set alt if not exists
            if (! $img->hasAttribute('alt') || empty($img->getAttribute('alt'))) {
                $img->setAttribute('alt', 'Gambar konten');
            }
        }

        return html_entity_decode($dom->saveHTML(), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Process TinyMCE content dan convert base64 images ke uploaded files
     * Seperti approach Summernote
     */
    public static function processTinyMCEBase64Images($request, $field, $paths)
    {
        $detail = $request->input($field);
        if (empty($detail)) {
            return $detail;
        }

        libxml_use_internal_errors(true);
        $dom = new \DomDocument('1.0', 'UTF-8');
        $dom->loadHTML(mb_convert_encoding($detail, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $xpath = new \DOMXPath($dom);

        // 🛡️ Security: Hapus semua <script> tags
        foreach ($dom->getElementsByTagName('script') as $script) {
            $script->parentNode->removeChild($script);
        }

        // 🛡️ Security: Hapus atribut berbahaya
        foreach ($xpath->query('//*') as $node) {
            if ($node->hasAttributes()) {
                $attrsToRemove = [];
                foreach ($node->attributes as $attr) {
                    // Remove onX attributes (onerror, onclick, etc)
                    if (preg_match('/^on/i', $attr->name)) {
                        $attrsToRemove[] = $attr->name;
                    }
                    // Remove javascript: href
                    if (strtolower($attr->name) === 'href' && preg_match('/^javascript:/i', $attr->value)) {
                        $attrsToRemove[] = $attr->name;
                    }
                }
                foreach ($attrsToRemove as $attrName) {
                    $node->removeAttribute($attrName);
                }
            }
        }

        // 🔧 Process Base64 Images (seperti approach Summernote)
        $images    = $dom->getElementsByTagName('img');
        $maxImages = 10;
        $imgCount  = 0;

        foreach ($images as $k => $img) {
            $src = $img->getAttribute('src');

            // Process base64 images
            if (strpos($src, 'data:image') === 0 && $imgCount < $maxImages) {
                try {
                    // Extract base64 data
                    list($typeInfo, $base64Data) = explode(';', $src);
                    list(, $base64Data)          = explode(',', $base64Data);

                    if (! base64_decode($base64Data, true)) {
                        continue;
                    }

                    $rawData = base64_decode($base64Data);
                    $folder  = trim($paths, '/');
                    Storage::disk('public')->makeDirectory($folder);

                    $imageName = $folder . "/" . self::gencode(3) . "-" . date('dmy') . "-" . rand(1000, 9999999) . "-" . $k . '.webp';
                    $path      = storage_path("app/public/" . $imageName);

                    // Process dengan Intervention Image
                    $image = Image::read($rawData);

                    // Auto resize jika terlalu besar
                    if ($image->width() > 1920) {
                        $image->resize(1920, null, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        });
                    }

                    // Compress ke WebP untuk efisiensi
                    $image->toWebp(85)->save($path);

                    // Update img src dengan URL yang benar
                    $img->setAttribute('src', "/storage/" . $imageName);

                    // Ensure proper styling
                    $img->setAttribute('style', 'width: 100%; height: auto; max-width: 100%; display: block; margin: 15px auto;');
                    $img->setAttribute('width', '100%');
                    $img->setAttribute('height', 'auto');
                    $img->setAttribute('loading', 'lazy');

                    if (! $img->hasAttribute('alt') || empty($img->getAttribute('alt'))) {
                        $img->setAttribute('alt', 'Gambar konten');
                    }

                    $imgCount++;
                } catch (\Exception $e) {
                    \Log::error("Gagal proses base64 image TinyMCE: " . $e->getMessage());
                    continue;
                }
            } else {
                // Untuk gambar yang sudah ada (edit mode), pastikan styling konsisten
                $currentSrc = $img->getAttribute('src');

                // Clean backend URL jika ada
                $backendUrl = url('/');
                if (strpos($currentSrc, $backendUrl) !== false) {
                    $currentSrc = str_replace($backendUrl, '', $currentSrc);
                    $img->setAttribute('src', $currentSrc);
                }

                // Apply consistent styling
                $img->setAttribute('style', 'width: 100%; height: auto; max-width: 100%; display: block; margin: 15px auto;');
                $img->setAttribute('width', '100%');
                $img->setAttribute('height', 'auto');

                if (! $img->hasAttribute('loading')) {
                    $img->setAttribute('loading', 'lazy');
                }

                if (! $img->hasAttribute('alt') || empty($img->getAttribute('alt'))) {
                    $img->setAttribute('alt', 'Gambar konten');
                }
            }
        }

        return html_entity_decode($dom->saveHTML(), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Update image URLs untuk display di frontend dari TinyMCE content
     */
    public static function updateImageUrlsTinyMCE($content)
    {
        if (empty($content)) {
            return $content;
        }

        libxml_use_internal_errors(true);
        $dom = new \DomDocument('1.0', 'UTF-8');
        $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $images = $dom->getElementsByTagName('img');

        foreach ($images as $img) {
            $src = $img->getAttribute('src');

            // Convert relative paths to full URLs
            if (strpos($src, '/storage/') === 0) {
                $fullUrl = url($src);
                $img->setAttribute('src', $fullUrl);
            }

            // Ensure proper attributes for frontend display
            $currentClass = $img->getAttribute('class');
            $newClass     = trim($currentClass . ' img-fluid');
            $img->setAttribute('class', $newClass);

            // Consistent styling
            $img->setAttribute('style', 'width: 100%; height: auto; max-width: 100%; display: block; margin: 15px auto;');

            if (! $img->hasAttribute('loading')) {
                $img->setAttribute('loading', 'lazy');
            }

            if (! $img->hasAttribute('alt') || empty($img->getAttribute('alt'))) {
                $img->setAttribute('alt', 'Gambar konten');
            }
        }

        return html_entity_decode($dom->saveHTML(), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Clean TinyMCE content untuk security
     */
    public static function cleanTinyMCEContent($content)
    {
        if (empty($content)) {
            return $content;
        }

        libxml_use_internal_errors(true);
        $dom = new \DomDocument('1.0', 'UTF-8');
        $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $xpath = new \DOMXPath($dom);

        // Remove dangerous elements
        $dangerousElements = ['script', 'object', 'embed', 'iframe', 'form', 'input', 'button'];
        foreach ($dangerousElements as $tagName) {
            foreach ($dom->getElementsByTagName($tagName) as $element) {
                $element->parentNode->removeChild($element);
            }
        }

        // Remove dangerous attributes
        foreach ($xpath->query('//*') as $node) {
            if ($node->hasAttributes()) {
                $attrsToRemove = [];
                foreach ($node->attributes as $attr) {
                    // Remove event handlers
                    if (preg_match('/^on/i', $attr->name)) {
                        $attrsToRemove[] = $attr->name;
                    }
                    // Remove javascript: URLs
                    if (strtolower($attr->name) === 'href' && preg_match('/^javascript:/i', $attr->value)) {
                        $attrsToRemove[] = $attr->name;
                    }
                    // Remove TinyMCE specific attributes for clean output
                    if (strpos($attr->name, 'data-mce-') === 0) {
                        $attrsToRemove[] = $attr->name;
                    }
                }
                foreach ($attrsToRemove as $attrName) {
                    $node->removeAttribute($attrName);
                }
            }
        }

        return html_entity_decode($dom->saveHTML(), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    // Merubah URL gambar dalam konten dengan menambahkan URL aplikasi
    public static function updateImageUrls(?string $content): string
    {
        if (empty($content)) {
            return ""; // Jika null atau kosong, kembalikan string kosong
        }

        // Ambil URL aplikasi dari konfigurasi atau fallback ke `url('/')`
        $appUrl = config('app.url') ?? url('/');

        // Pastikan URL berakhir dengan slash
        $appUrl = rtrim($appUrl, '/') . '/';

        // Regex pattern untuk mencari img src yang menggunakan path relatif `/storage/...`
        $pattern = '/<img[^>]+src=["\'](\/storage\/[^"\']+)["\']/i';

        // Ganti path relatif dengan URL aplikasi lengkap
        $updatedContent = preg_replace_callback($pattern, function ($matches) use ($appUrl) {
            $relativePath = ltrim($matches[1], '/'); // Hilangkan leading slash agar tidak ada double slash
            $absolutePath = $appUrl . $relativePath;
            return str_replace($matches[1], $absolutePath, $matches[0]);
        }, $content);

        return $updatedContent;
    }

    // Fungsi untuk resize dan kompresi gambar jika ukurannya lebih dari 1MB
    private static function resizeImageIfNeeded($filePath, $maxFileSizeInMB = 1)
    {
        $targetFileSize = $maxFileSizeInMB * 1024 * 1024; // Konversi ukuran file ke byte

        if (! file_exists($filePath)) {
            return;
        }

        // Cek ukuran file, jika lebih besar dari target file size maka lakukan resize dan kompresi
        if (filesize($filePath) > $targetFileSize) {
            try {
                $image = Image::read($filePath);

                // Resize gambar dengan lebar maksimum 1920px untuk mengurangi ukuran file
                if ($image->width() > 1920) {
                    $image->resize(1920, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }

                // Kompres gambar dengan kualitas yang sesuai agar di bawah target file size
                $quality = 90;
                do {
                    $encoded = $image->toJpeg($quality);
                    $quality -= 5; // Kurangi kualitas jika ukuran masih di atas target file size
                } while (strlen($encoded) > $targetFileSize && $quality > 10);

                // Simpan gambar yang telah dikompresi
                file_put_contents($filePath, $encoded);
            } catch (\Exception $e) {
                \Log::error("Error resizing image: " . $e->getMessage());
            }
        }
    }

    // Untuk Upload Pdf
    public static function UpPdf($request, $field, $path)
    {
        $file = $request->file($field);

        // Validasi apakah file ada dan valid
        if ($file->isValid()) {
            // Validasi tipe file
            $filename          = $file->getClientOriginalName();
            $name              = ucwords(pathinfo($filename, PATHINFO_FILENAME));
            $allowedExtensions = ['pdf'];
            $fileExtension     = strtolower($file->getClientOriginalExtension());

            if (! in_array($fileExtension, $allowedExtensions)) {
                return "0"; // File tidak diizinkan
            }

            // Jika file adalah gambar, lakukan pemeriksaan tambahan
            if (in_array($fileExtension, ['jpg', 'jpeg', 'png'])) {
                if (! self::isValidImage($file)) {
                    return "0"; // File bukan gambar yang valid
                }
            }

            // Generate nama file yang aman
            $fileName  = Str::uuid() . "-" . rand(1000, 9999999999) . "." . $fileExtension;
            $file_save = $path . "/" . $fileName;
            if (! is_dir(storage_path('app/public/' . $path))) {
                Storage::disk('public')->makeDirectory($path);
            }

            // Simpan file
            Storage::disk('public')->putFileAs($path, $file, $fileName);

            // Return data file yang berhasil diunggah
            return $file_save;
        } else {
            return "0"; // File tidak valid
        }
    }

    // Untuk Upload File & Gambar
    public static function UpFileUnduhan($request, $field, $path)
    {
        $file = $request->file($field);

        // Validasi apakah file ada dan valid
        if ($file->isValid()) {
            // Validasi tipe file
            $filename = $file->getClientOriginalName();
            $name     = ucwords(pathinfo($filename, PATHINFO_FILENAME));

            // Ekstensi yang diizinkan (Office, suara, gambar, video, dokumen, kompresi)
            $allowedExtensions = [
                // Ekstensi gambar
                'jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'tiff', 'webp',
                // Ekstensi dokumen Office
                'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'ods', 'odp', 'rtf',
                // Ekstensi dokumen lainnya
                'pdf', 'txt', 'csv', 'xml', 'json', 'md',
                // Ekstensi file suara
                'mp3', 'wav', 'ogg', 'm4a', 'flac', 'aac',
                // Ekstensi file video
                'mp4', 'mkv', 'avi', 'mov', 'wmv', 'flv', 'webm', '3gp', 'mpeg',
                // Ekstensi file kompresi
                'zip', 'rar', 'tar', 'gz', '7z', 'bz2', 'xz',
                // Ekstensi lainnya yang mungkin diizinkan
                'iso',
            ];

            // Ekstensi yang diblokir (ekstensi file program)
            $blockedExtensions = ['exe', 'php', 'html', 'js', 'sh', 'bat', 'py', 'pl', 'cgi', 'rb', 'jar', 'apk', 'msi', 'com'];

            $fileExtension = strtolower($file->getClientOriginalExtension());

            // Cek apakah file berekstensi terblokir
            if (in_array($fileExtension, $blockedExtensions)) {
                return "0"; // File tidak diizinkan
            }

            // Cek apakah file berekstensi yang diizinkan
            if (! in_array($fileExtension, $allowedExtensions)) {
                return "0"; // File tidak diizinkan
            }

            // Jika file adalah gambar, lakukan pemeriksaan tambahan
            if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff', 'svg'])) {
                if (! self::isValidImage($file)) {
                    return "0"; // File bukan gambar yang valid
                }
            }

            // Generate nama file yang aman
            $fileName  = Str::uuid() . "-" . rand(1000, 9999999999) . "." . $fileExtension;
            $file_save = $path . "/" . $fileName;
            if (! is_dir(storage_path('app/public/' . $path))) {
                Storage::disk('public')->makeDirectory($path);
            }

            // Simpan file
            Storage::disk('public')->putFileAs($path, $file, $fileName);

            // Return data file yang berhasil diunggah
            return [
                "judul_file" => $name,
                "nama_file"  => $fileName,
                "tipe"       => $fileExtension,
                "size"       => $file->getSize(),
                "url"        => $file_save,
            ];
        } else {
            return "0"; // File tidak valid
        }
    }

    // Untuk Upload File & Gambar
    public static function UpImgPdf($request, $field, $path)
    {
        $file = $request->file($field);

        // Validasi apakah file ada dan valid
        if ($file->isValid()) {
            // Validasi tipe file
            $filename          = $file->getClientOriginalName();
            $name              = ucwords(pathinfo($filename, PATHINFO_FILENAME));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
            $fileExtension     = strtolower($file->getClientOriginalExtension());

            if (! in_array($fileExtension, $allowedExtensions)) {
                return "0"; // File tidak diizinkan
            }

            // Jika file adalah gambar, lakukan pemeriksaan tambahan
            if (in_array($fileExtension, ['jpg', 'jpeg', 'png'])) {
                if (! self::isValidImage($file)) {
                    return "0"; // File bukan gambar yang valid
                }
            }

            // Generate nama file yang aman
            $fileName  = Str::uuid() . "-" . rand(1000, 9999999999) . "." . $fileExtension;
            $file_save = $path . "/" . $fileName;
            if (! is_dir(storage_path('app/public/' . $path))) {
                Storage::disk('public')->makeDirectory($path);
            }

            // Simpan file
            Storage::disk('public')->putFileAs($path, $file, $fileName);

            // Return data file yang berhasil diunggah
            return [
                "judul_file" => $name,
                "nama_file"  => $fileName,
                "tipe"       => $fileExtension,
                "size"       => $file->getSize(),
                "url"        => $file_save,
            ];
        } else {
            return "0"; // File tidak valid
        }
    }

    // Untuk Upload File PDF
    public static function UpFilePdf($request, $field, $path)
    {
        $file = $request->file($field);

        // Validasi apakah file ada dan valid
        if ($file->isValid()) {
            // Validasi tipe file
            $filename          = $file->getClientOriginalName();
            $name              = ucwords(pathinfo($filename, PATHINFO_FILENAME));
            $allowedExtensions = ['pdf'];
            $fileExtension     = strtolower($file->getClientOriginalExtension());

            if (! in_array($fileExtension, $allowedExtensions)) {
                return "0"; // File tidak diizinkan
            }

            // Generate nama file yang aman
            $fileName  = Str::uuid() . "-" . rand(1000, 9999999999) . "." . $fileExtension;
            $file_save = $path . "/" . $fileName;
            if (! is_dir(storage_path('app/public/' . $path))) {
                Storage::disk('public')->makeDirectory($path);
            }

            // Simpan file
            Storage::disk('public')->putFileAs($path, $file, $fileName);

            // Return data file yang berhasil diunggah
            return [
                "judul_file" => $name,
                "nama_file"  => $fileName,
                "tipe"       => $fileExtension,
                "size"       => $file->getSize(),
                "url"        => $file_save,
            ];
        } else {
            return "0"; // File tidak valid
        }
    }

    // Untuk Upload File Video
    public static function UpVideo($request, $field, $path)
    {
        $file = $request->file($field);

        // Validasi apakah file ada dan valid
        if ($file->isValid()) {
            // Validasi tipe file
            $filename          = $file->getClientOriginalName();
            $name              = ucwords(pathinfo($filename, PATHINFO_FILENAME));
            $allowedExtensions = ['mp4', 'webm', 'ogg'];
            $fileExtension     = strtolower($file->getClientOriginalExtension());

            if (! in_array($fileExtension, $allowedExtensions)) {
                return "0"; // File tidak diizinkan
            }

            // Generate nama file yang aman
            $fileName  = Str::uuid() . "-" . rand(1000, 9999999999) . "." . $fileExtension;
            $file_save = $path . "/" . $fileName;
            if (! is_dir(storage_path('app/public/' . $path))) {
                Storage::disk('public')->makeDirectory($path);
            }

            // Simpan file
            Storage::disk('public')->putFileAs($path, $file, $fileName);

            // Return data file yang berhasil diunggah
            return [
                "judul_file" => $name,
                "nama_file"  => $fileName,
                "tipe"       => $fileExtension,
                "size"       => $file->getSize(),
                "url"        => $file_save,
            ];
        } else {
            return "0"; // File tidak valid
        }
    }

    // Upload file thumbnails
    public static function UpThumbnails($request, $field, $path)
    {
        $file = $request->file($field);

        // Validasi apakah file ada dan valid
        if ($file && $file->isValid()) {
            $filename          = $file->getClientOriginalName();
            $name              = ucwords(pathinfo($filename, PATHINFO_FILENAME));
            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            $fileExtension     = strtolower($file->getClientOriginalExtension());

            if (! in_array($fileExtension, $allowedExtensions)) {
                return "0"; // File tidak diizinkan
            }

            // Jika file adalah gambar, lakukan pemeriksaan tambahan
            if (! self::isValidImage($file)) {
                return "0"; // File bukan gambar yang valid
            }

            // Generate nama file yang aman
            $fileName     = Str::uuid() . "-" . rand(1000, 9999999999) . "." . $fileExtension;
            $fileSavePath = $path . "/" . $fileName;
            if (! is_dir(storage_path('app/public/' . $path))) {
                Storage::disk('public')->makeDirectory($path);
            }

            // Resize gambar asli menjadi 1200 x 628 px menggunakan fit untuk menyesuaikan gambar
            $image = Image::read($file)->cover(1200, 628);

            // Simpan gambar yang di-resize
            $image->save(storage_path('app/public/' . $fileSavePath));

            // Membuat thumbnail dari gambar yang diunggah
            self::createThumbnail($image, $path, $fileName, $fileExtension);

            // Return data file yang berhasil diunggah
            return $fileSavePath;
        } else {
            return "0"; // File tidak valid
        }
    }

    // Upload file thumbnails untuk Ebook
    public static function UpThumbnailsEbook($request, $field, $path)
    {
        return self::handleThumbnailUpload($request, $field, $path, 800, 1000);
    }

    // Upload file thumbnails untuk Emagazine
    public static function UpThumbnailsEmagazine($request, $field, $path)
    {
        return self::handleThumbnailUpload($request, $field, $path, 600, 800);
    }

    // Upload file foto untuk Data Direktur
    public static function UpFotoDirektur($request, $field, $path)
    {
        return self::handleThumbnailUpload($request, $field, $path, 317, 448);
    }

    // Upload File Background Hero
    public static function UpBackgoundHero($request, $field, $path)
    {
        return self::handleThumbnailUpload($request, $field, $path, 1920, 935);
    }

    // Upload File Illustration Hero Versi 1
    public static function UpIllustrationHeroVersi1($request, $field, $path)
    {
        return self::handleThumbnailUpload($request, $field, $path, 1080, 1080);
    }

    // Upload File Illustration Hero Versi 4
    public static function UpIllustrationHeroVersi4($request, $field, $path)
    {
        return self::handleThumbnailUpload($request, $field, $path, 400, 600);
    }

    // Fungsi umum untuk menangani upload dan resize gambar
    private static function handleThumbnailUpload($request, $field, $path, $width, $height)
    {
        $file = $request->file($field);

        if ($file && $file->isValid()) {
            $filename          = $file->getClientOriginalName();
            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            $fileExtension     = strtolower($file->getClientOriginalExtension());

            if (! in_array($fileExtension, $allowedExtensions)) {
                return "0"; // File tidak diizinkan
            }

            if (! self::isValidImage($file)) {
                return "0"; // File bukan gambar yang valid
            }

            $fileName     = Str::uuid() . "-" . rand(1000, 9999999999) . "." . $fileExtension;
            $fileSavePath = $path . "/" . $fileName;

            if (! is_dir(storage_path('app/public/' . $path))) {
                Storage::disk('public')->makeDirectory($path);
            }

            // ✅ Kompres gambar original (tanpa resize) hingga ukuran <= 500KB
            $originalImage     = Image::read($file);
            $compressedQuality = 90;

            do {
                $encoded = $originalImage->toJpeg($compressedQuality);
                $compressedQuality -= 5;
            } while (strlen($encoded) > 500 * 1024 && $compressedQuality > 10);

            // Simpan file original yang telah dikompres
            file_put_contents(storage_path('app/public/' . $fileSavePath), $encoded);

            // ✅ Buat thumbnail dari hasil kompresi (bukan file asli mentah)
            $thumbnailImage = $originalImage->cover($width, $height);
            self::createThumbnail($thumbnailImage, $path, $fileName, $fileExtension);

            return $fileSavePath;
        }
        return "0"; // File tidak valid
    }

    // Buat thumbnails versi 300 untuk SEO
    private static function createThumbnail($image, $path, $originalFileName, $fileExtension)
    {
        $thumbnailFileName = str_replace('.' . $fileExtension, '_thumbnail.' . $fileExtension, $originalFileName);
        $thumbnailPath     = $path . '/' . $thumbnailFileName;
        $fullPath          = storage_path('app/public/' . $thumbnailPath);

        try {
            // Optional resize jika gambar terlalu besar (misal lebar > 600px)
            if ($image->width() > 600) {
                $image = $image->resize(600, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize(); // cegah upscale
                });
            }

            $compressedQuality = 90;
            $encoded           = null;

            // Kompresi hingga ukuran di bawah 100KB atau kualitas minimum
            do {
                $encoded = $image->toJpeg($compressedQuality);
                $compressedQuality -= 5;
            } while (strlen($encoded) > 100 * 1024 && $compressedQuality > 10);

            // Simpan hasil encode ke file
            file_put_contents($fullPath, $encoded);
        } catch (\Exception $e) {
            \Log::error("Gagal membuat thumbnail: " . $e->getMessage());
        }
    }

    // Upload file Foto
    public static function UpFoto($request, $field, $path)
    {
        $file = $request->file($field);

        if ($file && $file->isValid()) {
            $filename          = $file->getClientOriginalName();
            $name              = ucwords(pathinfo($filename, PATHINFO_FILENAME));
            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            $fileExtension     = strtolower($file->getClientOriginalExtension());

            if (! in_array($fileExtension, $allowedExtensions)) {
                return "0"; // File tidak diizinkan
            }

            if (! self::isValidImage($file)) {
                return "0"; // File bukan gambar yang valid
            }

            $fileName     = Str::uuid() . "-" . rand(1000, 9999999999) . "." . $fileExtension;
            $fileSavePath = $path . "/" . $fileName;

            if (! is_dir(storage_path('app/public/' . $path))) {
                Storage::disk('public')->makeDirectory($path);
            }

            // Buka gambar
            $img    = Image::read($file);
            $width  = $img->width();
            $height = $img->height();

            // Tentukan sisi pendek
            $squareSize = min($width, $height);

            // Hitung posisi crop dari tengah
            $x = intval(($width - $squareSize) / 2);
            $y = intval(($height - $squareSize) / 2);

            // Crop gambar menjadi persegi
            $img = $img->crop($squareSize, $squareSize, $x, $y);

            // Encode dan simpan (tanpa resize)
            $img->save(storage_path('app/public/' . $fileSavePath));

            // Buat avatar dari gambar yang sudah di-crop
            self::createAvatar($img, $path, $fileName, $fileExtension);

            return $fileSavePath;
        } else {
            return "0"; // File tidak valid
        }
    }

    private static function createAvatar($image, $path, $originalFileName, $fileExtension)
    {
        // Set nama file untuk avatar
        $thumbnailFileName = str_replace('.' . $fileExtension, '_avatar.' . $fileExtension, $originalFileName);
        $thumbnailPath     = $path . '/' . $thumbnailFileName;
        $fullPath          = storage_path('app/public/' . $thumbnailPath);

        // Fit 64x64 sekali saja
        $resized = $image->cover(64, 64);

        $compressedQuality = 90;
        $encoded           = null;

        // Kompresi bertahap hingga di bawah 300KB
        do {
            $encoded = $resized->toJpeg($compressedQuality);
            $compressedQuality -= 5;
        } while (strlen($encoded) > 300 * 1024 && $compressedQuality > 10);

        // Simpan avatar ke file
        file_put_contents($fullPath, $encoded);
    }

    // Upload file infografis
    public static function UpInfografis($request, $field, $path)
    {
        $file = $request->file($field);

        // Validasi apakah file ada dan valid
        if ($file && $file->isValid()) {
            $filename          = $file->getClientOriginalName();
            $name              = ucwords(pathinfo($filename, PATHINFO_FILENAME));
            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            $fileExtension     = strtolower($file->getClientOriginalExtension());

            if (! in_array($fileExtension, $allowedExtensions)) {
                return "0"; // File tidak diizinkan
            }

            // Jika file adalah gambar, lakukan pemeriksaan tambahan
            if (! self::isValidImage($file)) {
                return "0"; // File bukan gambar yang valid
            }

            // Generate nama file yang aman
            $fileName     = Str::uuid() . "-" . rand(1000, 9999999999) . "." . $fileExtension;
            $fileSavePath = $path . "/" . $fileName;

            if (! is_dir(storage_path('app/public/' . $path))) {
                Storage::disk('public')->makeDirectory($path);
            }

            // Simpan gambar asli tanpa resize
            $img = Image::read($file);

            // Simpan gambar asli
            $img->save(storage_path('app/public/' . $fileSavePath));

            // Membuat thumbnail dari gambar yang diunggah
            self::createThumnailInfografis($file, $path, $fileName, $fileExtension);

            // Return data file yang berhasil diunggah
            return [
                "url"  => $fileSavePath,
                "tipe" => $fileExtension,
                "size" => $file->getSize(),
            ];
        } else {
            return "0"; // File tidak valid
        }
    }

    // Membuat thumbnail dengan ukuran < 300KB
    private static function createThumnailInfografis($file, $path, $originalFileName, $fileExtension)
    {
        $thumbnailFileName = str_replace('.' . $fileExtension, '_thumbnail.' . $fileExtension, $originalFileName);
        $thumbnailPath     = $path . '/' . $thumbnailFileName;
        $fullPath          = storage_path('app/public/' . $thumbnailPath);

        // Baca gambar asli
        $image = Image::read($file);

        $compressedQuality = 90;
        $encoded           = null;

        // Kompresi bertahap sampai ukuran di bawah 300KB atau kualitas minimum
        do {
            $encoded = $image->toJpeg($compressedQuality);
            $compressedQuality -= 5;
        } while (strlen($encoded) > 300 * 1024 && $compressedQuality > 10);

        // Simpan thumbnail ke file
        file_put_contents($fullPath, $encoded);
    }

    // Upload file FotoGaleri
    public static function UpFotoGaleri($request, $field, $path)
    {
        $file = $field;

        // Validasi apakah file ada dan valid
        if ($file && $file->isValid()) {
            $filename          = $file->getClientOriginalName();
            $name              = ucwords(pathinfo($filename, PATHINFO_FILENAME));
            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            $fileExtension     = strtolower($file->getClientOriginalExtension());

            if (! in_array($fileExtension, $allowedExtensions)) {
                return "0"; // File tidak diizinkan
            }

            // Jika file adalah gambar, lakukan pemeriksaan tambahan
            if (! self::isValidImage($file)) {
                return "0"; // File bukan gambar yang valid
            }

            // Generate nama file yang aman
            $fileName     = Str::uuid() . "-" . rand(1000, 9999999999) . "." . $fileExtension;
            $fileSavePath = $path . "/" . $fileName;

            if (! is_dir(storage_path('app/public/' . $path))) {
                Storage::disk('public')->makeDirectory($path);
            }

            // Simpan gambar asli tanpa resize
            $img = Image::read($file);

            // Simpan gambar asli
            $img->save(storage_path('app/public/' . $fileSavePath));

            // Membuat thumbnail dari gambar yang diunggah
            self::createThumbnail($img, $path, $fileName, $fileExtension);

            // Return data file yang berhasil diunggah
            return [
                "url"  => $fileSavePath,
                "tipe" => $fileExtension,
                "size" => $file->getSize(),
            ];
        } else {
            return "0"; // File tidak valid
        }
    }

    // Cek valid gambar
    private static function isValidImage($file)
    {
        // Memeriksa apakah file adalah gambar yang valid dengan menggunakan getimagesize()
        $validImageTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG];
        $imageInfo       = @getimagesize($file->getPathname());

        return $imageInfo !== false && in_array($imageInfo[2], $validImageTypes);
    }

    // mengatasi path traversal
    public static function sanitize_input($input)
    {
        $input = str_replace('/', '', $input);  // hapus tanda / pada input
        $input = str_replace('\\', '', $input); // hapus tanda \ pada input
        $input = str_replace('..', '', $input); // hapus tanda .. pada input
        $input = str_replace('|', '', $input);  // hapus tanda | pada input
        return $input;
    }

    // untuk drop folder dengan storage
    public static function deleteFolderIfExists(string $tags, string $path): bool
    {
        // Pastikan path ada di storage sebelum melakukan operasi
        if (! Storage::disk('public')->exists($path)) {
            return false; // Mengembalikan false jika path tidak ditemukan
        }

        // Handle penghapusan berdasarkan tipe tag
        if ($tags === 'directory') {
            return Storage::disk('public')->deleteDirectory($path);
        }

        if ($tags === 'file') {
            // Hapus file utama
            $deleted = Storage::disk('public')->delete($path);

            // Coba hapus thumbnail jika ada
            $thumbnailPath = str_replace('.', '_thumbnail.', $path);
            Storage::disk('public')->delete($thumbnailPath);

            // Coba hapus avatar jika ada
            $avatarPath = str_replace('.', '_avatar.', $path);
            Storage::disk('public')->delete($avatarPath);

            return $deleted;
        }

        return false; // Mengembalikan false jika tag tidak sesuai
    }

    // Fungsi untuk mendapatkan ID video dari URL
    public static function getYouTubeVideoID($url)
    {
        preg_match('/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches);
        return $matches[1] ?? null;
    }

    // Fungsi untuk mendapatkan URL thumbnail resolusi default
    public static function getYouTubeThumbnailUrl($url, $quality = 'default')
    {
        // Dapatkan ID video
        $videoID = self::getYouTubeVideoID($url);

        // Jika ID video valid, kembalikan URL thumbnail
        if ($videoID) {
            switch ($quality) {
                case 'mqdefault':
                    return "https://img.youtube.com/vi/{$videoID}/mqdefault.jpg"; // Thumbnail resolusi menengah
                case 'hqdefault':
                    return "https://img.youtube.com/vi/{$videoID}/hqdefault.jpg"; // Thumbnail resolusi tinggi
                case 'maxresdefault':
                    return "https://img.youtube.com/vi/{$videoID}/maxresdefault.jpg"; // Thumbnail resolusi maksimum
                case 'default':
                default:
                    return "https://img.youtube.com/vi/{$videoID}/default.jpg"; // Thumbnail resolusi default
            }
        }

        return self::urlImg($url); // Kembalikan null jika ID tidak valid
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCTION OTHERS
    |--------------------------------------------------------------------------
     */
    // Add Log Activity
    public static function addToLogAktifitas($request, $detail)
    {
        if (Auth::check()) {
            // User
            $user = Auth::user();
            // Log
            $log                 = [];
            $log['uuid_profile'] = $user->RelPortalActor->uuid;
            $log['apps']         = $detail['apps'];
            $log['role']         = $user->role;
            $log['subjek']       = $detail['subjek'];
            $log['method']       = $request->method();
            $log['ip']           = $request->ip();
            $log['agent']        = $request->header('user-agent');
            $log['url']          = $request->fullUrl();
            $log['aktifitas']    = json_encode($detail['aktifitas']);
            $log['device']       = $detail['device'];
            if (isset($detail['dashboard'])) {
                $log['dashboard'] = $detail['dashboard'];
            }
        } else {
            // Log
            $log                 = [];
            $log['uuid_profile'] = $detail['uuid_profile'];
            $log['apps']         = $detail['apps'];
            $log['role']         = $detail['role'];
            $log['subjek']       = $detail['subjek'];
            $log['method']       = $request->method();
            $log['ip']           = $request->ip();
            $log['agent']        = $request->header('user-agent');
            $log['url']          = $request->fullUrl();
            $log['aktifitas']    = json_encode($detail['aktifitas']);
            $log['device']       = $detail['device'];
            if (isset($detail['dashboard'])) {
                $log['dashboard'] = $detail['dashboard'];
            }
        }
        SysLogAktifitas::create($log);
    }

    // Cek tanggal saat ini berada dalam range atau tidak
    // Untuk cek tanggal saat ini masa pendaftaran atau bukan
    public static function isCurrentDateInRange($startDate, $endDate)
    {
        $currentDate = Carbon::now();
        $startDate   = Carbon::parse($startDate);
        $endDate     = Carbon::parse($endDate);
        return $currentDate->between($startDate, $endDate);
    }

    // Cek apakah hari ini sebelum tanggal yang ditentukan
    public static function isCurrentDateBefore($date)
    {
        $currentDate = Carbon::now();
        $date        = Carbon::parse($date);
        return $currentDate->lessThan($date);
    }

    // Cek apakah hari ini setelah tanggal yang ditentukan
    public static function isCurrentDateAfter($date)
    {
        $currentDate = Carbon::now();
        $date        = Carbon::parse($date);
        return $currentDate->greaterThan($date);
    }

    // Cek apakah tanggal saat ini sama atau lebih dari tanggal yang ditentukan
    public static function isCurrentDateSameOrAfter($date)
    {
        $currentDate = Carbon::now();
        $date        = Carbon::parse($date);
        return $currentDate->greaterThanOrEqualTo($date);
    }

    // get inisial nama
    public static function getInitials($nama)
    {
        $words = explode(' ', $nama);
        if (count($words) > 1) {
            // Ambil huruf pertama dari dua kata pertama
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        } else {
            // Ambil huruf pertama dan terakhir dari satu kata
            return strtoupper(substr($words[0], 0, 1) . substr($words[0], -1));
        }
    }

    // validasi status post berdasarkan role
    public static function validateStatus($role, $status)
    {
        $allowedStatuses = [];

        // Tentukan status yang diizinkan berdasarkan role user
        switch ($role) {
            case 'Super Admin':
            case 'Admin':
            case 'Editor':
                $allowedStatuses = ['Draft', 'Pending Review', 'Published', 'Scheduled', 'Archived'];
                break;

            default: // Penulis atau Kontributor
                $allowedStatuses = ['Draft', 'Pending Review'];
                break;
        }

        // Return true jika status diizinkan, false jika tidak
        return in_array($status, $allowedStatuses);
    }

    // generate deskripsi dari postingan
    public static function generateDescription(string $htmlContent): string
    {
        // Hapus tag HTML
        $textContent = strip_tags($htmlContent);
        // Hapus newline atau karakter enter (jika ada)
        $textWithoutNewline = preg_replace("/\r\n|\r|\n/", ' ', $textContent);
        // Batasi panjang deskripsi untuk SEO (150-160 karakter idealnya)
        return Str::limit(trim($textWithoutNewline), 160, '...');
    }

    // GetStatistikByModel
    public static function GetStatistikByModel($model, $status, $tags = null)
    {
        $auth      = Auth::user();
        $role      = $auth->role;
        $data      = 0;
        $varModels = self::$varModels[$model] ?? null;

        if (! $varModels) {
            return self::toDot($data); // Mengembalikan 0 jika model tidak ditemukan
        }

        // Setup query dasar
        $query = $varModels::whereStatus($status);

        // jika halaman
        if ($model === "Halaman") {
            $kategori = Helper::decode($tags);
            $query->whereKategori($kategori);
        }

        // Tambahkan withTrashed jika status adalah "Deleted"
        if ($status === "Deleted") {
            $query->withTrashed();
        }

        // Jika model adalah "Pesan", langsung hitung datanya
        if ($model === "Pesan") {
            return self::toDot($query->count());
        }

        // Logika berdasarkan role pengguna
        if ($role === "Super Admin" || $role === "Admin" || $role === "Editor") {
            if ($status == "Draft") {
                $data = $query->whereUuidCreated($auth->uuid)->count();
            } else {
                $data = $query->count();
            }
        } else {
            $data = $query->whereUuidCreated($auth->uuid)->count();
        }

        return self::toDot($data);
    }

    // GetListKategori
    public static function GetListKategori($type)
    {
        $data = PortalKategori::whereType($type)->orderBy("nama", "ASC")->whereStatus("1")->get();
        return $data;
    }

    // GetNoUrutTanos
    public static function GetNoUrutTanos($uuid_kategori, $tahun)
    {
        $lastNoUrut = PortalTanos::whereUuidKategori($uuid_kategori)
            ->whereYear("tanggal", $tahun)
            ->max('no_urut');
        $no_urut = $lastNoUrut ? $lastNoUrut + 1 : 1;

        return $no_urut;
    }

    // GetNoUrutDataDirektur
    public static function GetNoUrutDataDirektur()
    {
        $lastNoUrut = PortalDataDirektur::max('no_urut');
        $no_urut    = $lastNoUrut ? $lastNoUrut + 1 : 1;

        return $no_urut;
    }

    // GetNoUrutHalaman
    public static function GetNoUrutHalaman($tags)
    {
        $kategori   = self::decode($tags);
        $lastNoUrut = PortalPage::whereKategori($kategori)->max('no_urut');
        $no_urut    = $lastNoUrut ? $lastNoUrut + 1 : 1;

        return $no_urut;
    }

    // GetNoUrutLinks
    public static function GetNoUrutLinks($tags)
    {
        $kategori   = self::decode($tags);
        $lastNoUrut = PortalLinks::whereKategori($kategori)->max('no_urut');
        $no_urut    = $lastNoUrut ? $lastNoUrut + 1 : 1;

        return $no_urut;
    }

    // GetPendingMessages
    public static function GetPendingMessages()
    {
        return PortalPesan::where('status', 'Pending')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    // GetHeroModel
    public static function GetHeroModel()
    {
        return PortalSetup::whereNamaPengaturan('model_hero_section')
            ->whereSites('Portal')
            ->whereStatus('1')
            ->firstOrFail();
    }
}
