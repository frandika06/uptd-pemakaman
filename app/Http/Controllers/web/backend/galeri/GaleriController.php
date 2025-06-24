<?php
namespace App\Http\Controllers\web\backend\galeri;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\PortalGaleri;
use App\Models\PortalGaleriList;
use App\Models\PortalKategori;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GaleriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // auth
        $auth = Auth::user();
        $role = $auth->role;

        // cek filter
        if ($request->session()->exists('filter_status_galeri')) {
            $status = $request->session()->get('filter_status_galeri');
        } else {
            $request->session()->put('filter_status_galeri', 'Published');
            $status = "Published";
        }

        if ($request->ajax()) {
            if (isset($_GET['filter'])) {
                $status = $_GET['filter']['status'];
                $request->session()->put('filter_status_galeri', $status);
            }
            // cek role
            if ($role == "Super Admin" || $role == "Admin" || $role == "Editor") {
                if ($status == "Draft") {
                    $data = PortalGaleri::whereStatus($status)
                        ->whereUuidCreated($auth->uuid)
                        ->orderBy("tanggal", "DESC")
                        ->get();
                } else {
                    $data = PortalGaleri::whereStatus($status)
                        ->orderBy("tanggal", "DESC")
                        ->get();
                }
            } else {
                $data = PortalGaleri::whereStatus($status)
                    ->whereUuidCreated($auth->uuid)
                    ->orderBy("tanggal", "DESC")
                    ->get();
            }
            return Datatables::of($data)
                ->addIndexColumn()
                ->setRowId('uuid')
                ->addColumn('judul', function ($data) {
                    $thumbnails = Helper::thumbnail($data->thumbnails);
                    $uuid_enc   = Helper::encode($data->uuid);
                    $edit       = route('prt.apps.galeri.edit', $uuid_enc);
                    $judulText  = $data->judul ? Str::limit($data->judul, 50, "...") : "[draft] - lanjutkan atau hapus";
                    $judul      = '
                    <div class="trans-list">
                        <img src="' . $thumbnails . '" alt="" class="rounded avatar avatar-xl me-3" draggable="false">
                        <h4><a class="text-underline" href="' . $edit . '">' . $judulText . '</a></h4>
                    </div>';
                    return $judul;
                })
                ->addColumn('views', function ($data) {
                    $views = Helper::toDot($data->views);
                    return $views;
                })
                ->addColumn('jumlah', function ($data) {
                    if (count($data->RelGaleriList) > 0) {
                        $jumlah = Helper::toDot($data->GetJumlahGaleri());
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
                    if ($data->status == "Published") {
                        $publisher = isset($data->Publisher->nama_lengkap) ? $data->Publisher->nama_lengkap : '-';
                    } else {
                        $publisher = '';
                    }
                    return $publisher;
                })
                ->addColumn('tanggal', function ($data) {
                    $tanggal = Helper::TglSimple($data->created_at);
                    return $tanggal;
                })
                ->addColumn('aksi', function ($data) use ($role) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit     = route('prt.apps.galeri.edit', $uuid_enc);
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
        return view('pages.admin.portal_apps.galeri.index', compact(
            'status'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // auth
        $auth = Auth::user();
        // buat draft Galeri
        $uuid_galeri     = Str::uuid();
        $uuid_galeri_enc = Helper::encode($uuid_galeri);
        $value           = [
            "uuid"   => $uuid_galeri,
            "judul"  => "",
            "status" => "Draft",
        ];
        // save
        try {
            $save = PortalGaleri::create($value);
            if ($save) {
                // create log
                $aktifitas = [
                    "tabel" => ["portal_galeri"],
                    "uuid"  => [$uuid_galeri],
                    "value" => [$value],
                ];
                $log = [
                    "apps"      => "Portal Apps",
                    "subjek"    => "Menambahkan Data Draft Galeri" . $uuid_galeri,
                    "aktifitas" => $aktifitas,
                    "device"    => "web",
                ];
                Helper::addToLogAktifitas($request, $log);
                return redirect()->route('prt.apps.galeri.edit', [$uuid_galeri_enc]);
            }
            return abort(500, 'Gagal Membuat Galeri, Silahkan Coba Lagi!.');
        } catch (Exception $e) {
            return abort(500, 'Gagal Membuat Galeri, Silahkan Coba Lagi!.');
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
    public function edit($uuid_enc)
    {
        // auth
        $auth = Auth::user();
        // get kategori
        $kategoriList = PortalKategori::whereType("Galeri")->whereStatus("1")->orderBy("nama")->get();
        // uuid
        $uuid   = Helper::decode($uuid_enc);
        $data   = PortalGaleri::findOrFail($uuid);
        $title  = "Edit Data Galeri";
        $submit = "Simpan";
        return view('pages.admin.portal_apps.galeri.create_edit', compact(
            'auth',
            'uuid_enc',
            'title',
            'submit',
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
        $role = $auth->role;

        // Validasi input tanpa memvalidasi status
        $request->validate([
            "judul"        => "required|string|max:100",
            "deskripsi"    => "required|string|max:160",
            "thumbnails"   => "sometimes|image|mimes:png,jpg,jpeg|max:2048",
            "tanggal"      => "required",
            "kategori"     => "required|string|max:100",
            "status"       => "required|string|max:100",
            "judul_foto.*" => "required|string|max:100",
            "file_foto.*"  => "required|image|mimes:jpeg,png,jpg|max:2048",
        ]);

        // $uuid_galeri
        $uuid_galeri = Helper::decode($uuid_enc);
        $data        = PortalGaleri::findOrFail($uuid_galeri);
        if ($data->thumbnails === null) {
            $request->validate([
                "thumbnails" => "required|image|mimes:png,jpg,jpeg|max:2048",
            ]);
        } else {
            $request->validate([
                "thumbnails" => "sometimes|image|mimes:png,jpg,jpeg|max:2048",
            ]);
        }

        // Validasi status menggunakan helper
        if (! Helper::validateStatus($role, $request->status)) {
            alert()->error('Error!', 'Status tidak valid untuk peran Anda!');
            return back()->withInput($request->all());
        }

        // Validasi jika Penulis atau Kontributor
        if ($role == 'Penulis' || $role == 'Kontributor') {
            if ($data->status != "Draft") {
                alert()->error('Error!', 'Konten Sudah Tidak Bisa Diubah!');
                return back()->withInput($request->all());
            }
        }

        // slug
        if ($data->judul !== $request->judul) {
            $slug      = \Str::slug($request->judul);
            $cekslug   = PortalGaleri::where('uuid', '!=', $uuid_galeri)->whereSlug($slug)->count();
            $inputslug = $cekslug > 0 ? $slug . "-" . Helper::gencode(4) : $slug;
        } else {
            $inputslug = $data->slug;
        }

        // value
        $thn     = date("Y", \strtotime($data->created_at));
        $path    = "galeri/" . $thn . "/" . $uuid_galeri;
        $tanggal = date('Y-m-d H:i:s', strtotime($request->tanggal));
        $value_1 = [
            "judul"     => $request->judul,
            "slug"      => $inputslug,
            "deskripsi" => $request->deskripsi,
            "tanggal"   => $tanggal,
            "kategori"  => $request->kategori,
            "status"    => $request->status,
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

        // save
        $save_1 = $data->update($value_1);
        if ($save_1) {
            // upload galeri list / list foto
            $path_list = $path . "/photos";
            if ($request->has('judul_foto')) {
                foreach ($request->judul_foto as $index => $judul_foto) {
                    if (! empty($judul_foto)) {
                        // Ensure file exists and handle the file upload correctly
                        $field = $request->file("file_foto")[$index] ?? null;
                        if ($field) {
                            // Upload photo using helper function
                            $foto = Helper::UpFotoGaleri($request, $field, $path_list);
                            // Create new record in the PortalGaleriList model
                            PortalGaleriList::create([
                                'uuid'        => Str::uuid(),
                                'uuid_galeri' => $uuid_galeri,
                                'judul'       => $judul_foto,
                                'url'         => $foto['url'],
                                'tipe'        => $foto['tipe'],
                                'size'        => $foto['size'],
                            ]);
                        }
                    }
                }
            }

            // create log
            $aktifitas = [
                "tabel" => ["portal_galeri"],
                "uuid"  => [$uuid_galeri],
                "value" => [$request->judul],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Mengubah Data Galeri UUID= " . $uuid_galeri,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            alert()->success('Success', "Berhasil Mengubah Data!");
            return redirect()->route('prt.apps.galeri.index');
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
        $auth = Auth::user();
        $tags = Helper::decode($request->tags);

        // Jika bulk delete
        if ($request->filled('uuids') && is_array($request->uuids)) {
            $uuidList = array_map(function ($u) {
                return Helper::decode($u);
            }, $request->uuids);

            switch ($tags) {
                case 'list_galeri':
                    return $this->bulkDelListGaleri($request, $auth, $uuidList);
            }
        }

        // Delete tunggal (default)
        $uuid = Helper::decode($request->uuid);
        switch ($tags) {
            case 'galeri':
                return $this->delGaleri($request, $auth, $uuid);
                break;
            case 'list_galeri':
                return $this->delListGaleri($request, $auth, $uuid);
                break;
            default:
                return response()->json(['status' => false, 'message' => 'Tag tidak valid.'], 422);
        }
    }
    private function delGaleri($request, $auth, $uuid)
    {
        // Dapatkan data dari database
        $data  = PortalGaleri::findOrFail($uuid);
        $judul = $data->judul;

        // Lakukan soft delete
        if ($data->status == "Draft" || $data->status == "Pending Review") {
            // drop path
            $tahun = Carbon::parse($data->tanggal)->year;
            $path  = "galeri/{$tahun}/{$data->uuid}";
            Helper::deleteFolderIfExists("directory", $path);
            $save_1 = $data->forceDelete();
        } else {
            // Update uuid_deleted dan status sebelum melakukan soft delete
            $save_1 = $data->delete();
        }

        if ($save_1) {
            // Log aktivitas penghapusan
            $aktifitas = [
                "tabel" => ["portal_galeri"],
                "uuid"  => [$uuid],
                "value" => [$judul],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menghapus Data Galeri UUID= " . $uuid,
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
    private function delListGaleri($request, $auth, $uuid)
    {
        // Dapatkan data dari database
        $data  = PortalGaleriList::findOrFail($uuid);
        $judul = $data->judul;

        // Lakukan soft delete
        if ($data->RelGaleri->status == "Draft" || $data->RelGaleri->status == "Pending Review") {
            // drop path
            if (! empty($data->url) && Storage::disk('public')->exists($data->url)) {
                Storage::disk('public')->delete($data->url);
                $thumbnailPath = str_replace('.', '_thumbnail.', $data->url);
                if (Storage::disk('public')->exists($thumbnailPath)) {
                    Storage::disk('public')->delete($thumbnailPath);
                }
            }
            $save_1 = $data->forceDelete();
        } else {
            // Update uuid_deleted dan status sebelum melakukan soft delete
            $save_1 = $data->delete();
        }

        if ($save_1) {
            // Log aktivitas penghapusan
            $aktifitas = [
                "tabel" => ["portal_galeri_list"],
                "uuid"  => [$uuid],
                "value" => [$judul],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menghapus Data List Galeri / List Foto UUID= " . $uuid,
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
    private function bulkDelListGaleri($request, $auth, array $uuids)
    {
        $deleted = 0;
        $failed  = 0;

        foreach ($uuids as $uuid) {
            try {
                $data  = PortalGaleriList::findOrFail($uuid);
                $judul = $data->judul;

                // Lakukan soft delete
                if ($data->RelGaleri->status == "Draft" || $data->RelGaleri->status == "Pending Review") {
                    // drop path
                    if (! empty($data->url) && Storage::disk('public')->exists($data->url)) {
                        Storage::disk('public')->delete($data->url);
                        $thumbnailPath = str_replace('.', '_thumbnail.', $data->url);
                        if (Storage::disk('public')->exists($thumbnailPath)) {
                            Storage::disk('public')->delete($thumbnailPath);
                        }
                    }
                    $data->forceDelete();
                } else {
                    // Update uuid_deleted dan status sebelum melakukan soft delete
                    $data->delete();
                }
                $deleted++;

                // Logging per item
                $aktifitas = [
                    "tabel" => ["portal_galeri_list"],
                    "uuid"  => [$uuid],
                    "value" => [$judul],
                ];
                Helper::addToLogAktifitas($request, [
                    "apps"      => "Portal Apps",
                    "subjek"    => "Menghapus Massal List Galeri UUID = $uuid",
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
     * UBAH STATUS LIST GALERI / FOTO
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
        $data = PortalGaleriList::findOrFail($uuid);

        // value
        $value_1 = [
            "status" => $status_update,
        ];

        // save
        $save_1 = $data->update($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_galeri_list"],
                "uuid"  => [$uuid],
                "value" => [$data],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Mengubah Status List Galeri / List Foto: " . $data->judul . " - " . $uuid,
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