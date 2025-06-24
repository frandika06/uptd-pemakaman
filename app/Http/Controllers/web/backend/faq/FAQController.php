<?php
namespace App\Http\Controllers\web\backend\faq;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\PortalFAQ;
use App\Models\PortalFAQList;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FAQController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // auth
        $auth = Auth::user();

        if ($request->ajax()) {
            $data = PortalFAQ::orderBy("created_at", "DESC")->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->setRowId('uuid')
                ->addColumn('judul', function ($data) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit     = route('prt.apps.faq.edit', $uuid_enc);
                    if ($data->judul == "") {
                        $judul = '<a class="text-underline" href="' . $edit . '">[draft] - lanjutkan atau hapus</a>';
                    } else {
                        $judul = '<a class="text-underline" href="' . $edit . '">' . Str::limit($data->judul, 30, "...") . '</a>';
                    }
                    return $judul;
                })
                ->addColumn('jumlah', function ($data) {
                    if (count($data->RelFAQList) > 0) {
                        $jumlah = Helper::toDot($data->GetJumlahFAQ());
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
                    if ($data->status == "1") {
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
                ->addColumn('aksi', function ($data) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit     = route('prt.apps.faq.edit', [$uuid_enc]);
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
        return view('pages.admin.portal_apps.faq.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // auth
        $auth = Auth::user();
        // buat draft faq
        $uuid_faq     = Str::uuid();
        $uuid_faq_enc = Helper::encode($uuid_faq);
        $value        = [
            "uuid"   => $uuid_faq,
            "judul"  => "",
            "status" => "0",
        ];
        // save
        try {
            $save = PortalFAQ::create($value);
            if ($save) {
                // create log
                $aktifitas = [
                    "tabel" => ["portal_faq"],
                    "uuid"  => [$uuid_faq],
                    "value" => [$value],
                ];
                $log = [
                    "apps"      => "Portal Apps",
                    "subjek"    => "Menambahkan Data Draft FAQ" . $uuid_faq,
                    "aktifitas" => $aktifitas,
                    "device"    => "web",
                ];
                Helper::addToLogAktifitas($request, $log);
                return redirect()->route('prt.apps.faq.edit', [$uuid_faq_enc]);
            }
            return abort(500, 'Gagal Membuat FAQ, Silahkan Coba Lagi!.');
        } catch (Exception $e) {
            return abort(500, 'Gagal Membuat FAQ, Silahkan Coba Lagi!.');
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
        // uuid
        $uuid     = Helper::decode($uuid_enc);
        $data     = PortalFAQ::findOrFail($uuid);
        $list_faq = $data->RelFAQList;
        $title    = "Edit Data FAQ";
        $submit   = "Simpan";
        return view('pages.admin.portal_apps.faq.create_edit', compact(
            'uuid_enc',
            'title',
            'submit',
            'data',
            'list_faq'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $uuid_enc)
    {
        // auth
        $auth = Auth::user();

        // Validasi input sesuai kolom form
        $request->validate([
            "judul"         => "required|string|max:300",
            "deskripsi"     => "required|string|max:160",
            "thumbnails"    => "sometimes|image|mimes:png,jpg,jpeg|max:2048",
            "tanggal"       => "required",
            'status'        => 'required|string|max:1',
            'pertanyaan.*'  => 'required|string|max:150',
            'status_list.*' => 'required|string|max:1',
            "jawaban.*"     => "required",
        ]);

        // Decode UUID dan dapatkan data yang akan diupdate
        $uuid_faq = Helper::decode($uuid_enc);
        $data     = PortalFAQ::findOrFail($uuid_faq);
        if ($data->thumbnails === null) {
            $request->validate([
                "thumbnails" => "required|image|mimes:png,jpg,jpeg|max:2048",
            ]);
        } else {
            $request->validate([
                "thumbnails" => "sometimes|image|mimes:png,jpg,jpeg|max:2048",
            ]);
        }

        // slug
        if ($data->judul !== $request->judul) {
            $slug      = Str::slug($request->judul);
            $cekslug   = PortalFAQ::where('uuid', '!=', $uuid_faq)->whereSlug($slug)->count();
            $inputslug = $cekslug > 0 ? $slug . "-" . Helper::gencode(4) : $slug;
        } else {
            $inputslug = $data->slug;
        }

        // value untuk update PortalFAQ
        $thn     = date("Y", \strtotime($data->created_at));
        $path    = "faq/" . $thn . "/" . $uuid_faq;
        $tanggal = date('Y-m-d H:i:s', strtotime($request->tanggal));
        $value_1 = [
            'judul'     => $request->judul,
            'slug'      => $inputslug,
            "deskripsi" => $request->deskripsi,
            "tanggal"   => $tanggal,
            'status'    => $request->status,
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

        // Save update ke database
        $save_1 = $data->update($value_1);
        if ($save_1) {
            // add faq_list
            $thn  = date("Y", \strtotime($data->created_at));
            $path = "faq/" . $thn . "/" . $uuid_faq;
            if ($request->has('pertanyaan')) {
                // trucnate all faq before create
                PortalFAQList::where("uuid_portal_faq", $uuid_faq)->forceDelete();
                foreach ($request->pertanyaan as $index => $pertanyaan) {
                    if (! empty($pertanyaan)) {
                        PortalFAQList::create(
                            [
                                'uuid'            => Str::uuid(),
                                'uuid_portal_faq' => $uuid_faq,
                                'pertanyaan'      => $pertanyaan,
                                'jawaban'         => Helper::UpdateImgIndexing($request->jawaban[$index], $path),
                                'status'          => $request->status_list[$index],
                            ]
                        );
                    }
                }
            }
            // create log
            $aktifitas = [
                "tabel" => ["portal_faq"],
                "uuid"  => [$uuid_faq],
                "value" => [$value_1],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Mengubah Data FAQ: " . $request->judul . " - " . $uuid_faq,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            alert()->success('Success', "Berhasil Mengubah Data!");
            return redirect()->route('prt.apps.faq.index');
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
        // auth
        $auth = Auth::user();

        // uuid
        $uuid = Helper::decode($request->uuid);

        // data
        $data = PortalFAQ::findOrFail($uuid);

        // save
        // drop path
        $tahun = Carbon::parse($data->tanggal)->year;
        $path  = "faq/{$tahun}/{$data->uuid}";
        Helper::deleteFolderIfExists("directory", $path);
        $save_1 = $data->forceDelete();
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_faq"],
                "uuid"  => [$uuid],
                "value" => [$data],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menghapus Data FAQ: " . $data->judul . " - " . $uuid,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            // alert success
            $msg      = "Data Berhasil Dihapus!";
            $response = [
                "status"  => true,
                "message" => $msg,
            ];
            return response()->json($response, 200);
        } else {
            // success
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
        $data = PortalFAQ::findOrFail($uuid);

        // value
        $value_1 = [
            "status" => $status_update,
        ];

        // save
        $save_1 = $data->update($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_faq"],
                "uuid"  => [$uuid],
                "value" => [$data],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Mengubah Status FAQ: " . $data->judul . " - " . $uuid,
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
