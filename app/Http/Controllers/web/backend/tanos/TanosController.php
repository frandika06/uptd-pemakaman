<?php
namespace App\Http\Controllers\web\backend\tanos;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\PortalKategori;
use App\Models\PortalKategoriSub;
use App\Models\PortalTanos;
use App\Models\PortalTanosList;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TanosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $tags_enc)
    {
        // auth
        $auth          = Auth::user();
        $role          = $auth->role;
        $slug          = Helper::decode($tags_enc); // slug kategori tanos
        $tagsTanos     = PortalKategori::whereSlug($slug)->whereType("Tanos")->firstOrFail();
        $uuid_kategori = $tagsTanos->uuid;
        $tagsSubTanos  = $tagsTanos->RelKategoriSub;

        // cek filter
        if ($request->session()->exists('filter_nama_kategori_sub')) {
            $nama_kategori_sub = $request->session()->get('filter_nama_kategori_sub');
            $tahun             = $request->session()->get('filter_tahun_tanos');
        } else {
            $tahun = date('Y');
            $request->session()->put('filter_nama_kategori_sub', 'Semua Data');
            $request->session()->put('filter_tahun_tanos', $tahun);
            $nama_kategori_sub = "Semua Data";
        }

        if ($request->ajax()) {
            if (isset($_GET['filter'])) {
                $tahun             = $_GET['filter']['tahun'];
                $nama_kategori_sub = $_GET['filter']['nama_kategori_sub'];
                $request->session()->put('filter_nama_kategori_sub', $nama_kategori_sub);
            }
            // cek type
            if ($nama_kategori_sub == "Semua Data") {
                $data = PortalTanos::whereUuidKategori($uuid_kategori)
                    ->whereYear("tanggal", $tahun)
                    ->orderBy("no_urut", "ASC")
                    ->get();
            } else {
                $kategori_sub = PortalKategoriSub::whereUuidKategori($uuid_kategori)
                    ->whereNama($nama_kategori_sub)
                    ->firstOrFail();
                $uuid_kategori_sub = $kategori_sub->uuid;
                $data              = PortalTanos::whereUuidKategori($uuid_kategori)
                    ->whereUuidKategoriSub($uuid_kategori_sub)
                    ->whereYear("tanggal", $tahun)
                    ->orderBy("no_urut", "ASC")
                    ->get();
            }
            return Datatables::of($data)
                ->addIndexColumn()
                ->setRowId('uuid')
                ->addColumn('nama_group', function ($data) use ($tags_enc) {
                    $uuid_enc   = Helper::encode($data->uuid);
                    $edit       = route('prt.apps.tanos.edit', [$tags_enc, $uuid_enc]);
                    $judulText  = $data->nama_group ? Str::limit($data->nama_group, 50, "...") : "[draft] - lanjutkan atau hapus";
                    $nama_group = '<a class="text-underline" href="' . $edit . '">' . $judulText . '</a>';
                    return $nama_group;
                })
                ->addColumn('views', function ($data) {
                    $views = Helper::toDot($data->views);
                    return $views;
                })
                ->addColumn('jumlah', function ($data) {
                    if (count($data->RelTanosList) > 0) {
                        $jumlah = Helper::toDot($data->GetJumlahTanos());
                    } else {
                        $jumlah = 0;
                    }
                    return $jumlah;
                })
                ->addColumn('penulis', function ($data) {
                    $penulis = isset($data->Penulis->nama_lengkap) ? $data->Penulis->nama_lengkap : '-';
                    return $penulis;
                })
                ->addColumn('publisher', function ($data) {
                    $publisher = isset($data->Publisher->nama_lengkap) ? $data->Publisher->nama_lengkap : '-';
                    return $publisher;
                })
                ->addColumn('tanggal', function ($data) {
                    $tanggal = Helper::TglSimple($data->created_at);
                    return $tanggal;
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
                    if ($role == "Super Admin" || $role == "Admin" || $role == "Editor") {
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
                ->addColumn('aksi', function ($data) use ($tags_enc) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit     = route('prt.apps.tanos.edit', [$tags_enc, $uuid_enc]);
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

        // get tahun tanos
        $tahunTanos = PortalTanos::selectRaw('YEAR(tanggal) as tahun')
            ->groupBy('tahun')
            ->orderByDesc('tahun')
            ->pluck('tahun')
            ->toArray();
        return view('pages.admin.portal_apps.tanos.index', compact(
            'tags_enc',
            'tagsTanos',
            'tagsSubTanos',
            'tahunTanos',
            'nama_kategori_sub',
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, $tags_enc)
    {
        // auth
        $auth          = Auth::user();
        $slug          = Helper::decode($tags_enc); // slug kategori tanos
        $tagsTanos     = PortalKategori::whereSlug($slug)->whereType("Tanos")->firstOrFail();
        $uuid_kategori = $tagsTanos->uuid;
        $tagsSubTanos  = $tagsTanos->RelKategoriSub;
        // buat draft Tanos
        $uuid_tanos     = Str::uuid();
        $uuid_tanos_enc = Helper::encode($uuid_tanos);
        $value          = [
            "uuid"              => $uuid_tanos,
            "uuid_kategori"     => $uuid_kategori,
            "uuid_kategori_sub" => null,
            "no_urut"           => Helper::GetNoUrutTanos($uuid_kategori, date('Y')),
            "nama_group"        => "",
            "tanggal"           => Carbon::now(),
            "sumber"            => "Link",
            "status"            => "0",
        ];
        // save
        try {
            $save = PortalTanos::create($value);
            if ($save) {
                // create log
                $aktifitas = [
                    "tabel" => ["portal_tanos"],
                    "uuid"  => [$uuid_tanos],
                    "value" => [$value],
                ];
                $log = [
                    "apps"      => "Portal Apps",
                    "subjek"    => "Menambahkan Data Draft Tanos" . $uuid_tanos,
                    "aktifitas" => $aktifitas,
                    "device"    => "web",
                ];
                Helper::addToLogAktifitas($request, $log);
                return redirect()->route('prt.apps.tanos.edit', [$tags_enc, $uuid_tanos_enc]);
            }
            return abort(500, 'Gagal Membuat Tanos, Silahkan Coba Lagi!.');
        } catch (Exception $e) {
            return abort(500, 'Gagal Membuat Tanos, Silahkan Coba Lagi!.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
    public function edit($tags_enc, $uuid_enc)
    {
        // auth
        $auth = Auth::user();
                                                    // get kategori
        $slug          = Helper::decode($tags_enc); // slug kategori tanos
        $tagsTanos     = PortalKategori::whereSlug($slug)->whereType("Tanos")->firstOrFail();
        $uuid_kategori = $tagsTanos->uuid;
        $tagsSubTanos  = $tagsTanos->RelKategoriSub;
        // uuid
        $uuid   = Helper::decode($uuid_enc);
        $data   = PortalTanos::findOrFail($uuid);
        $title  = "Edit Data Tanos";
        $submit = "Simpan";
        return view('pages.admin.portal_apps.tanos.create_edit', compact(
            'auth',
            'uuid_enc',
            'uuid_kategori',
            'title',
            'submit',
            'tags_enc',
            'tagsTanos',
            'tagsSubTanos',
            'data'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $tags_enc, $uuid_enc)
    {
        // auth
        $auth = Auth::user();
        $role = $auth->role;

                                                // get kategori
        $slug      = Helper::decode($tags_enc); // slug kategori tanos
        $tagsTanos = PortalKategori::whereSlug($slug)->whereType("Tanos")->firstOrFail();

        // Validasi input tanpa memvalidasi status
        $request->validate([
            "no_urut"        => "required|numeric|min:1",
            "judul"          => "required|string|max:300",
            "nama_group"     => "required|string|max:100",
            "tanggal"        => "required",
            "kategori"       => "required|string|max:100",
            "sumber"         => "required|string|max:100",
            "nama_anggota.*" => "required|string|max:100",
            "asal_sekolah.*" => "required|string|max:100",
            "thumbnails"     => "sometimes|image|mimes:png,jpg,jpeg|max:2048",
            "deskripsi"      => "sometimes|nullable",
        ]);

        // tanos
        $uuid_tanos = Helper::decode($uuid_enc);
        $data       = PortalTanos::findOrFail($uuid_tanos);
        if ($data->thumbnails === null) {
            $request->validate([
                "thumbnails" => "required|image|mimes:png,jpg,jpeg|max:2048",
            ]);
        } else {
            $request->validate([
                "thumbnails" => "sometimes|image|mimes:png,jpg,jpeg|max:2048",
            ]);
        }

        // cek kategori sub
        $uuid_kategori_sub = $request->kategori;
        $kategori          = PortalKategoriSub::findOrFail($uuid_kategori_sub);

        // value
        $tanggal = date('Y-m-d H:i:s', strtotime($request->tanggal));
        $thn     = date("Y", \strtotime($tanggal));
        $path    = "tanos/" . $thn . "/" . $uuid_tanos;
        $sumber  = $request->sumber;

        // generate judul dan slug
        // $judul     = $request->nama_group . " Pemenang Kategori " . $kategori->nama . " pada Bidang Lomba " . $tagsTanos->nama . " TANOS Tahun " . $thn;
        // slug
        if ($data->judul !== $request->judul) {
            $judul     = $request->judul;
            $slug      = Str::slug($judul);
            $cekslug   = PortalTanos::where('uuid', '!=', $uuid_tanos)->whereSlug($slug)->count();
            $inputslug = $cekslug > 0 ? $slug . "-" . Helper::gencode(4) : $slug;
        } else {
            $inputslug = $data->slug;
        }

        // value 1
        $value_1 = [
            "uuid_kategori_sub" => $uuid_kategori_sub,
            "no_urut"           => $request->no_urut,
            "judul"             => $judul,
            "slug"              => $inputslug,
            "nama_group"        => $request->nama_group,
            "tanggal"           => $tanggal,
            "sumber"            => $sumber,
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

        // deskripsi
        if ($request->filled('deskripsi')) {
            $imgpost              = Helper::UpdateImgPostWithCompress($request, "deskripsi", $path);
            $value_1['deskripsi'] = $imgpost;
        }

        // cek sumber Unduhan
        if ($sumber == "Link") {
            // SUMBER = Link
            $request->validate([
                "url" => "required|url",
            ]);
            $value_1['url'] = $request->url;
        } else {
            // SUMBER = UPLOAD
            $request->validate([
                'file_lampiran' => 'sometimes|nullable|file|mimes:jpg,jpeg,png,gif,bmp,svg,tiff,webp,' .
                'doc,docx,xls,xlsx,ppt,pptx,odt,ods,odp,rtf,pdf,txt,csv,xml,json,md,' .
                'mp3,wav,ogg,m4a,flac,aac,' .
                'mp4,mkv,avi,mov,wmv,flv,webm,3gp,mpeg,' .
                'zip,rar,tar,gz,7z,bz2,xz,iso|max:204800', // 200 MB
            ]);
            // file_lampiran
            if ($request->hasFile('file_lampiran')) {
                if (! empty($data->url) && Storage::disk('public')->exists($data->url)) {
                    Storage::disk('public')->delete($data->url);
                }
                $img = Helper::UpFileUnduhan($request, "file_lampiran", $path);
                if ($img == "0") {
                    alert()->error('Error!', 'Gagal Menyimpan Data, File Unduhan Tidak Sesuai Format!');
                    return back();
                }
                $value_1['url']  = $img['url'];
                $value_1['tipe'] = $img['tipe'];
                $value_1['size'] = $img['size'];
            }
        }

        // save
        $save_1 = $data->update($value_1);
        if ($save_1) {
            // add anggota tanos
            if ($request->has('nama_anggota')) {
                foreach ($request->nama_anggota as $index => $nama_anggota) {
                    if (! empty($nama_anggota) && ! empty($request->asal_sekolah[$index])) {
                        // Use updateOrCreate to avoid duplicate entries and add UUID for new entries
                        PortalTanosList::updateOrCreate(
                            [
                                'uuid_tanos'   => $uuid_tanos,
                                'nama_anggota' => $nama_anggota,
                            ],
                            [
                                'uuid'         => Str::uuid(),
                                'asal_sekolah' => $request->asal_sekolah[$index],
                            ]
                        );
                    }
                }
            }

            // create log
            $aktifitas = [
                "tabel" => ["portal_tanos"],
                "uuid"  => [$uuid_tanos],
                "value" => [$data],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Mengubah Data Tanos: " . $data->nama_group . " - UUID= " . $uuid_tanos,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            alert()->success('Success', "Berhasil Mengubah Data!");
            return redirect()->route('prt.apps.tanos.index', [$tags_enc]);
        } else {
            alert()->error('Error', "Gagal Mengubah Data!");
            return back()->withInput($request->all());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $tags_enc)
    {
        $auth = Auth::user();
        $tags = Helper::decode($request->tags);

        // Jika bulk delete
        if ($request->filled('uuids') && is_array($request->uuids)) {
            $uuidList = array_map(function ($u) {
                return Helper::decode($u);
            }, $request->uuids);

            switch ($tags) {
                case 'anggota_tanos':
                    return $this->bulkDelListAnggotaTanos($request, $auth, $uuidList);
            }
        }

        // Delete tunggal (default)
        $uuid = Helper::decode($request->uuid);
        switch ($tags) {
            case 'Tanos':
                return $this->delTanos($request, $tags_enc, $auth, $uuid);
                break;
            case 'anggota_tanos':
                return $this->delAnggotaTanos($request, $tags_enc, $auth, $uuid);
                break;
            default:
                return response()->json(['status' => false, 'message' => 'Tag tidak valid.'], 422);
        }
    }
    private function delTanos($request, $tags_enc, $auth, $uuid)
    {
        // Dapatkan data dari database
        $data       = PortalTanos::findOrFail($uuid);
        $nama_group = $data->nama_group;
        if ($data->uuid_kategori_sub === null) {
            $save_1 = $data->forceDelete();
        } else {
            $save_1 = $data->delete();
        }
        if ($save_1) {
            // Log aktivitas penghapusan
            $aktifitas = [
                "tabel" => ["portal_tanos"],
                "uuid"  => [$uuid],
                "value" => [$data],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menghapus Data Tanos : " . $nama_group . " - UUID= " . $uuid,
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
    private function delAnggotaTanos($request, $tags_enc, $auth, $uuid)
    {
        // Dapatkan data dari database
        $data         = PortalTanosList::findOrFail($uuid);
        $nama_anggota = $data->nama_anggota;

        // Lakukan soft delete
        $save_1 = $data->delete();
        if ($save_1) {
            // Log aktivitas penghapusan
            $aktifitas = [
                "tabel" => ["portal_tanos_list"],
                "uuid"  => [$uuid],
                "value" => [$data],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menghapus Data Anggota Tanos: " . $nama_anggota . " - UUID= " . $uuid,
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
    private function bulkDelListAnggotaTanos($request, $auth, array $uuids)
    {
        $deleted = 0;
        $failed  = 0;

        foreach ($uuids as $uuid) {
            try {
                $data = PortalTanosList::findOrFail($uuid);

                // Lakukan soft delete
                $data->delete();
                $deleted++;

                // Logging per item
                $aktifitas = [
                    "tabel" => ["portal_tanos_list"],
                    "uuid"  => [$uuid],
                    "value" => [$data],
                ];
                Helper::addToLogAktifitas($request, [
                    "apps"      => "Portal Apps",
                    "subjek"    => "Menghapus Massal List Anggota Tanos UUID = $uuid",
                    "aktifitas" => $aktifitas,
                    "device"    => "web",
                ]);
            } catch (\Exception $e) {
                $failed++;
            }
        }

        return response()->json([
            'status'  => true,
            'message' => "$deleted data berhasil dihapus, $failed gagal.",
        ]);
    }

    /**
     * UBAH STATUS LIST Tanos
     */
    public function status(Request $request, $tags_enc)
    {
        // Auth user
        $auth = Auth::user();

        // Decode UUID dari request
        $uuid = Helper::decode($request->uuid);
        $tags = Helper::decode($request->tags);

        switch ($tags) {
            case 'Tanos':
                return $this->statusTanos($request, $tags_enc, $auth, $uuid);
                break;
            case 'anggota_tanos':
                return $this->statusAnggotaTanos($request, $tags_enc, $auth, $uuid);
                break;
            default:
                // Return response gagal
                $msg      = "Data Gagal Dihapus!";
                $response = [
                    "status"  => false,
                    "message" => $msg,
                ];
                return response()->json($response, 422);
                break;
        }
    }
    // statusTanos
    public function statusTanos(Request $request, $tags_enc, $auth, $uuid)
    {
        $status = $request->status;
        if ($status == "0") {
            $status_update = "1";
        } else {
            $status_update = "0";
        }

        // data
        $data = PortalTanos::findOrFail($uuid);

        // value
        $value_1 = [
            "status" => $status_update,
        ];

        // save
        $save_1 = $data->update($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_tanos"],
                "uuid"  => [$uuid],
                "value" => [$data],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Mengubah Status Tanos: " . $data->nama_group . " - " . $uuid,
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
    // statusAnggotaTanos
    public function statusAnggotaTanos(Request $request, $tags_enc, $auth, $uuid)
    {
        $status = $request->status;
        if ($status == "0") {
            $status_update = "1";
        } else {
            $status_update = "0";
        }

        // data
        $data = PortalTanosList::findOrFail($uuid);

        // value
        $value_1 = [
            "status" => $status_update,
        ];

        // save
        $save_1 = $data->update($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_tanos_list"],
                "uuid"  => [$uuid],
                "value" => [$data],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Mengubah Status Anggota Tanos: " . $data->nama_anggota . " - " . $uuid,
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
