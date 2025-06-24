<?php
namespace App\Http\Controllers\web\backend\duta_sma;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\PortalDutaSma;
use App\Models\PortalKategori;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DutaSMAController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // auth
        $auth = Auth::user();

        // cek filter
        if ($request->session()->exists('filter_tahun_duta_sma')) {
            $tahun = $request->session()->get('filter_tahun_duta_sma');
        } else {
            $request->session()->put('filter_tahun_duta_sma', date('Y'));
            $tahun = date('Y');
        }

        if ($request->ajax()) {
            if (isset($_GET['filter'])) {
                $tahun = $_GET['filter']['tahun'];
                $request->session()->put('filter_tahun_duta_sma', $tahun);
            } else {
                $tahun = $request->session()->get('filter_tahun_duta_sma');
            }

            $data = PortalDutaSma::whereTahun($tahun)->orderBy("kategori", "ASC")->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->setRowId('uuid')
                ->addColumn('kategori', function ($data) {
                    $thumbnails = Helper::pp($data->thumbnails);
                    $uuid_enc   = Helper::encode($data->uuid);
                    $edit       = route('prt.apps.dutasma.edit', [$uuid_enc]);
                    $kategori   = '
                    <div class="trans-list">
                        <img src="' . $thumbnails . '" alt="" class="rounded avatar avatar-xl me-3" draggable="false">
                        <h4><a class="text-underline" href="' . $edit . '">' . $data->kategori . '</a></h4>
                    </div>';
                    return $kategori;
                })
                ->addColumn('nama_peserta', function ($data) {
                    $nama_peserta = '<ul class="m-0 p-0"><li>' . $data->nama_peserta_1 . '</li>';
                    $nama_peserta .= '<li>' . $data->nama_peserta_2 . '</li><ul>';
                    return $nama_peserta;
                })
                ->addColumn('nama_sekolah', function ($data) {
                    $nama_sekolah = '<ul class="m-0 p-0"><li>' . $data->nama_sekolah_1 . '</li>';
                    $nama_sekolah .= '<li>' . $data->nama_sekolah_2 . '</li><ul>';
                    return $nama_sekolah;
                })
                ->addColumn('views', function ($data) {
                    $views = Helper::toDot($data->views);
                    return $views;
                })
                ->addColumn('penulis', function ($data) {
                    $penulis = isset($data->Penulis->nama_lengkap) ? $data->Penulis->nama_lengkap : '-';
                    return $penulis;
                })
                ->addColumn('publisher', function ($data) {
                    $publisher = isset($data->Publisher->nama_lengkap) ? $data->Publisher->nama_lengkap : '-';
                    return $publisher;
                })
                ->addColumn('status', function ($data) use ($auth) {
                    $uuid   = Helper::encode($data->uuid);
                    $status = $data->status;
                    if ($status == "1") {
                        $toogle = "checked";
                        $text   = "Aktif";
                    } else {
                        $toogle = "";
                        $text   = "Tidak Aktif";
                    }
                    // role
                    $role = $auth->role;
                    if ($role == "Super Admin" || $role == "Admin") {
                        $status = '
                            <div class="form-check form-switch form-switch-custom form-switch-primary mb-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="status_' . $data->uuid . '" data-onclick="ubah-status" data-status="' . $uuid . '" data-status-value="' . $status . '" ' . $toogle . '>
                                <label class="form-check-label" for="status_' . $data->uuid . '">' . $text . '</label>
                            </div>
                        ';
                    } else {
                        $status = '<label class="form-check-label" for="status">' . $text . '</label>';
                    }
                    return $status;
                })
                ->addColumn('aksi', function ($data) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit     = route('prt.apps.dutasma.edit', [$uuid_enc]);
                    $aksi     = '
                        <div class="d-flex">
                            <a href="' . $edit . '" class="btn btn-primary shadow btn-xs sharp me-1"><i class="fas fa-pencil-alt"></i></a>
                            <a href="javascript:void(0);" class="btn btn-danger shadow btn-xs sharp" data-delete="' . $uuid_enc . '"><i class="fa fa-trash"></i></a>
                        </div>
                    ';
                    return $aksi;
                })
                ->escapeColumns([''])
                ->make(true);
        }

        // get tahun duta sma
        $tahunDutaSMA = PortalDutaSma::select("tahun")->groupBy("tahun")->orderBy("tahun", "DESC")->get();
        return view('pages.admin.portal_apps.duta_sma.index', compact(
            'tahun',
            'tahunDutaSMA',
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // auth
        $auth = Auth::user();
        // get kategori
        $kategoriList = PortalKategori::whereType("Duta SMA")->whereStatus("1")->orderBy("nama")->get();
        $title        = "Tambah Data Duta SMA";
        $submit       = "Simpan";
        return view('pages.admin.portal_apps.duta_sma.create_edit', compact(
            'title',
            'submit',
            'auth',
            'kategoriList'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // auth
        $auth = Auth::user();

        // Validasi input tanpa memvalidasi status
        $request->validate([
            "kategori"        => "required|string|max:100",
            "thumbnails"      => "required|image|mimes:png,jpg,jpeg|max:2048",
            "avatar_1"        => "required|image|mimes:png,jpg,jpeg|max:2048",
            "avatar_2"        => "required|image|mimes:png,jpg,jpeg|max:2048",
            "tahun"           => "required|string|max:100",
            "nama_peserta_1"  => "required|string|max:100",
            "nama_peserta_2"  => "required|string|max:100",
            "nama_sekolah_1"  => "required|string|max:100",
            "nama_sekolah_2"  => "required|string|max:100",
            "predikat_1"      => "sometimes|nullable|string|max:100",
            "predikat_2"      => "sometimes|nullable|string|max:100",
            "link_ig_1"       => "sometimes|nullable|url|max:300",
            "link_ig_2"       => "sometimes|nullable|url|max:300",
            "link_fb_1"       => "sometimes|nullable|url|max:300",
            "link_fb_2"       => "sometimes|nullable|url|max:300",
            "link_tiktok_1"   => "sometimes|nullable|url|max:300",
            "link_tiktok_2"   => "sometimes|nullable|url|max:300",
            "link_twitter_1"  => "sometimes|nullable|url|max:300",
            "link_twitter_2"  => "sometimes|nullable|url|max:300",
            "link_youtube_1"  => "sometimes|nullable|url|max:300",
            "link_youtube_2"  => "sometimes|nullable|url|max:300",
            "link_linkedin_1" => "sometimes|nullable|url|max:300",
            "link_linkedin_2" => "sometimes|nullable|url|max:300",
        ]);

        // value
        $uuid  = Str::uuid();
        $tahun = $request->tahun;
        $path  = "duta_sma/" . $tahun . "/" . $uuid;

        // generate judul dan slug
        $kategori  = $request->kategori;
        $judul     = $kategori . " Tahun " . $tahun;
        $slug      = Str::slug($judul);
        $cekslug   = PortalDutaSma::whereSlug($slug)->count();
        $inputslug = $cekslug > 0 ? $slug . "-" . Helper::gencode(4) : $slug;

        // value 1
        $value_1 = [
            "uuid"            => $uuid,
            "judul"           => $judul,
            "slug"            => $inputslug,
            "nama_peserta_1"  => $request->nama_peserta_1,
            "nama_sekolah_1"  => $request->nama_sekolah_1,
            "predikat_1"      => $request->predikat_1,
            "nama_peserta_2"  => $request->nama_peserta_2,
            "nama_sekolah_2"  => $request->nama_sekolah_2,
            "predikat_2"      => $request->predikat_2,
            "tahun"           => $tahun,
            "kategori"        => $kategori,
            "link_ig_1"       => $request->link_ig_1,
            "link_ig_2"       => $request->link_ig_2,
            "link_fb_1"       => $request->link_fb_1,
            "link_fb_2"       => $request->link_fb_2,
            "link_tiktok_1"   => $request->link_tiktok_1,
            "link_tiktok_2"   => $request->link_tiktok_2,
            "link_twitter_1"  => $request->link_twitter_1,
            "link_twitter_2"  => $request->link_twitter_2,
            "link_youtube_1"  => $request->link_youtube_1,
            "link_youtube_2"  => $request->link_youtube_2,
            "link_linkedin_1" => $request->link_linkedin_1,
            "link_linkedin_2" => $request->link_linkedin_2,
        ];

        // thumbnails
        if ($request->hasFile('thumbnails')) {
            $img = Helper::Upthumbnails($request, "thumbnails", $path);
            if ($img == "0") {
                alert()->error('Error!', 'Gagal Menyimpan Data, Thumbnails Duta SMA Tidak Sesuai Format!');
                return back();
            }
            $value_1['thumbnails'] = $img;
        }

        // avatar_1
        if ($request->hasFile('avatar_1')) {
            $img = Helper::UpFoto($request, "avatar_1", $path);
            if ($img == "0") {
                alert()->error('Error!', 'Gagal Menyimpan Data, Avatar Peserta 1 Duta SMA Tidak Sesuai Format!');
                return back();
            }
            $value_1['avatar_1'] = $img;
        }

        // avatar_2
        if ($request->hasFile('avatar_2')) {
            $img = Helper::UpFoto($request, "avatar_2", $path);
            if ($img == "0") {
                alert()->error('Error!', 'Gagal Menyimpan Data, Avatar Peserta 2 Duta SMA Tidak Sesuai Format!');
                return back();
            }
            $value_1['avatar_2'] = $img;
        }

        // deskripsi_1
        if ($request->filled('deskripsi_1')) {
            $imgdeskripsi_1         = Helper::UpImgPostWithCompress($request, "deskripsi_1", $path);
            $value_1['deskripsi_1'] = $imgdeskripsi_1;
        }

        // deskripsi_2
        if ($request->filled('deskripsi_2')) {
            $imgdeskripsi_2         = Helper::UpImgPostWithCompress($request, "deskripsi_2", $path);
            $value_1['deskripsi_2'] = $imgdeskripsi_2;
        }

        // save
        $save_1 = PortalDutaSma::create($value_1);
        if ($save_1) {
            // create log
            $value_aktifitas = array_filter($value_1, function ($key) {
                return ($key !== 'deskripsi_1' && $key !== 'deskripsi_2');
            }, ARRAY_FILTER_USE_KEY);
            $aktifitas = [
                "tabel" => ["portal_duta_sma"],
                "uuid"  => [$uuid],
                "value" => [$value_aktifitas],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menambahkan Data Duta SMA UUID= " . $uuid,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            alert()->success('Success', "Berhasil Menambahkan Data!");
            return redirect()->route('prt.apps.dutasma.index');
        } else {
            alert()->error('Error', "Gagal Menambahkan Data!");
            return back()->withInput($request->all());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($uuid_enc)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($uuid_enc)
    {
        // auth
        $auth = Auth::user();
        // uuid
        $uuid = Helper::decode($uuid_enc);
        $data = PortalDutaSma::findOrFail($uuid);
        // get kategori
        $kategoriList = PortalKategori::whereType("Duta SMA")->whereStatus("1")->orderBy("nama")->get();
        $title        = "Edit Data Duta SMA";
        $submit       = "Simpan";
        return view('pages.admin.portal_apps.duta_sma.create_edit', compact(
            'uuid_enc',
            'title',
            'submit',
            'auth',
            'kategoriList',
            'data'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $uuid_enc)
    {
        // auth
        $auth = Auth::user();

        // Validasi input tanpa memvalidasi status
        $request->validate([
            "kategori"        => "required|string|max:100",
            "thumbnails"      => "sometimes|image|mimes:png,jpg,jpeg|max:2048",
            "avatar_1"        => "sometimes|image|mimes:png,jpg,jpeg|max:2048",
            "avatar_2"        => "sometimes|image|mimes:png,jpg,jpeg|max:2048",
            "tahun"           => "required|string|max:100",
            "tahun"           => "required|string|max:100",
            "nama_peserta_1"  => "required|string|max:100",
            "nama_peserta_2"  => "required|string|max:100",
            "nama_sekolah_1"  => "required|string|max:100",
            "nama_sekolah_2"  => "required|string|max:100",
            "predikat_1"      => "sometimes|nullable|string|max:100",
            "predikat_2"      => "sometimes|nullable|string|max:100",
            "link_ig_1"       => "sometimes|nullable|url|max:300",
            "link_ig_2"       => "sometimes|nullable|url|max:300",
            "link_fb_1"       => "sometimes|nullable|url|max:300",
            "link_fb_2"       => "sometimes|nullable|url|max:300",
            "link_tiktok_1"   => "sometimes|nullable|url|max:300",
            "link_tiktok_2"   => "sometimes|nullable|url|max:300",
            "link_twitter_1"  => "sometimes|nullable|url|max:300",
            "link_twitter_2"  => "sometimes|nullable|url|max:300",
            "link_youtube_1"  => "sometimes|nullable|url|max:300",
            "link_youtube_2"  => "sometimes|nullable|url|max:300",
            "link_linkedin_1" => "sometimes|nullable|url|max:300",
            "link_linkedin_2" => "sometimes|nullable|url|max:300",
        ]);

        // $uuid
        $uuid = Helper::decode($uuid_enc);
        $data = PortalDutaSma::findOrFail($uuid);

        // value
        $tahun = $request->tahun;
        $path  = "duta_sma/" . $tahun . "/" . $uuid;

        // generate judul dan slug
        $kategori = $request->kategori;
        $judul    = $kategori . " Tahun " . $tahun;

        // slug
        if ($data->judul !== $judul) {
            $slug      = Str::slug($judul);
            $cekslug   = PortalDutaSma::where('uuid', '!=', $uuid)->whereSlug($slug)->count();
            $inputslug = $cekslug > 0 ? $slug . "-" . Helper::gencode(4) : $slug;
        } else {
            $inputslug = $data->slug;
        }

        // value 1
        $value_1 = [
            "judul"           => $judul,
            "slug"            => $inputslug,
            "nama_peserta_1"  => $request->nama_peserta_1,
            "nama_sekolah_1"  => $request->nama_sekolah_1,
            "predikat_1"      => $request->predikat_1,
            "nama_peserta_2"  => $request->nama_peserta_2,
            "nama_sekolah_2"  => $request->nama_sekolah_2,
            "predikat_2"      => $request->predikat_2,
            "tahun"           => $tahun,
            "kategori"        => $kategori,
            "link_ig_1"       => $request->link_ig_1,
            "link_ig_2"       => $request->link_ig_2,
            "link_fb_1"       => $request->link_fb_1,
            "link_fb_2"       => $request->link_fb_2,
            "link_tiktok_1"   => $request->link_tiktok_1,
            "link_tiktok_2"   => $request->link_tiktok_2,
            "link_twitter_1"  => $request->link_twitter_1,
            "link_twitter_2"  => $request->link_twitter_2,
            "link_youtube_1"  => $request->link_youtube_1,
            "link_youtube_2"  => $request->link_youtube_2,
            "link_linkedin_1" => $request->link_linkedin_1,
            "link_linkedin_2" => $request->link_linkedin_2,
        ];

        // thumbnails
        if ($request->hasFile('thumbnails')) {
            if (! empty($data->thumbnails) && Storage::disk('public')->exists($data->thumbnails)) {
                Storage::disk('public')->delete($data->thumbnails);
                $thumbnailPath = str_replace('.', '_thumbnail.', $data->thumbnails);
                if (Storage::disk('public')->exists($thumbnailPath)) {
                    Storage::disk('public')->delete($thumbnailPath);
                }
            }

            $img = Helper::UpThumbnails($request, "thumbnails", $path);
            if ($img == "0") {
                alert()->error('Error!', 'Gagal Menyimpan Data, Thumbnails Tidak Sesuai Format!');
                return back();
            }
            $value_1['thumbnails'] = $img;
        }

        // avatar_1
        if ($request->hasFile('avatar_1')) {
            if (! empty($data->avatar_1) && Storage::disk('public')->exists($data->avatar_1)) {
                Storage::disk('public')->delete($data->avatar_1);
                $thumbnailPath = str_replace('.', '_avatar.', $data->avatar_1);
                if (Storage::disk('public')->exists($thumbnailPath)) {
                    Storage::disk('public')->delete($thumbnailPath);
                }
            }

            $img = Helper::UpFoto($request, "avatar_1", $path);
            if ($img == "0") {
                alert()->error('Error!', 'Gagal Menyimpan Data, Avatar Peserta 1 Duta SMA Tidak Sesuai Format!');
                return back();
            }
            $value_1['avatar_1'] = $img;
        }

        // avatar_2
        if ($request->hasFile('avatar_2')) {
            if (! empty($data->avatar_2) && Storage::disk('public')->exists($data->avatar_2)) {
                Storage::disk('public')->delete($data->avatar_2);
                $thumbnailPath = str_replace('.', '_avatar.', $data->avatar_2);
                if (Storage::disk('public')->exists($thumbnailPath)) {
                    Storage::disk('public')->delete($thumbnailPath);
                }
            }

            $img = Helper::UpFoto($request, "avatar_2", $path);
            if ($img == "0") {
                alert()->error('Error!', 'Gagal Menyimpan Data, Avatar Peserta 2 Duta SMA Tidak Sesuai Format!');
                return back();
            }
            $value_1['avatar_2'] = $img;
        }

        // deskripsi_1
        if ($request->filled('deskripsi_1')) {
            $imgdeskripsi_1         = Helper::UpdateImgPostWithCompress($request, "deskripsi_1", $path);
            $value_1['deskripsi_1'] = $imgdeskripsi_1;
        }

        // deskripsi_2
        if ($request->filled('deskripsi_2')) {
            $imgdeskripsi_2         = Helper::UpdateImgPostWithCompress($request, "deskripsi_2", $path);
            $value_1['deskripsi_2'] = $imgdeskripsi_2;
        }

        // save
        $save_1 = $data->update($value_1);
        if ($save_1) {
            // create log
            $value_aktifitas = array_filter($value_1, function ($key) {
                return ($key !== 'deskripsi_1' && $key !== 'deskripsi_2');
            }, ARRAY_FILTER_USE_KEY);
            $aktifitas = [
                "tabel" => ["portal_duta_sma"],
                "uuid"  => [$uuid],
                "value" => [$value_aktifitas],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Mengubah Data Duta SMA UUID= " . $uuid,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            alert()->success('Success', "Berhasil Mengubah Data!");
            return redirect()->route('prt.apps.dutasma.index');
        } else {
            alert()->error('Error', "Gagal Mengubah Data!");
            return back()->withInput($request->all());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        // Auth user
        $auth = Auth::user();

        // Decode UUID dari request
        $uuid = Helper::decode($request->uuid);

        // Dapatkan data dari database
        $data = PortalDutaSma::findOrFail($uuid);

        // Lakukan soft delete
        $save_1 = $data->delete();

        if ($save_1) {
            // Log aktivitas penghapusan
            $aktifitas = [
                "tabel" => ["portal_duta_sma"],
                "uuid"  => [$uuid],
                "value" => [],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menghapus Data Duta SMA UUID= " . $uuid,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);

            // Return response success
            $msg      = "Data Berhasil Dihapus!";
            $response = [
                "status"  => true,
                "message" => $msg,
            ];
            return response()->json($response, 200);
        } else {
            // Return response gagal
            $msg      = "Data Gagal Dihapus!";
            $response = [
                "status"  => false,
                "message" => $msg,
            ];
            return response()->json($response, 422);
        }
    }

    /**
     * Status Aktif
     */
    public function status(Request $request)
    {
        // auth
        $auth = Auth::user();

        // uuid
        $uuid   = Helper::decode($request->uuid);
        $status = $request->status;
        if ($status == "0") {
            $status_update = "1";
        } else {
            $status_update = "0";
        }

        // data
        $data = PortalDutaSma::findOrFail($uuid);

        // value
        $value_1 = [
            "status" => $status_update,
        ];

        // save
        $save_1 = $data->update($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_duta_sma"],
                "uuid"  => [$uuid],
                "value" => [$value_1],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Mengubah Status Duta SMA: " . $data->nama_lengkap . " - " . $uuid,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            // alert success
            $msg      = "Status Berhasil Diubah!";
            $response = [
                "status"  => true,
                "message" => $msg,
            ];
            return response()->json($response, 200);
        } else {
            // success
            $msg      = "Status Gagal Diubah!";
            $response = [
                "status"  => false,
                "message" => $msg,
            ];
            return response()->json($response, 422);
        }
    }
}
