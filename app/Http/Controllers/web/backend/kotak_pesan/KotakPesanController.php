<?php
namespace App\Http\Controllers\web\backend\kotak_pesan;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Mail\helpdesk\BalasPesanMail;
use App\Models\PortalPesan;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KotakPesanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // auth
        $auth = Auth::user();

        // cek filter
        if ($request->session()->exists('filter_status_pesan')) {
            $status = $request->session()->get('filter_status_pesan');
        } else {
            $request->session()->put('filter_status_pesan', 'Pending');
            $status = "Pending";
        }

        if ($request->ajax()) {
            if (isset($_GET['filter'])) {
                $status = $_GET['filter']['status'];
                $request->session()->put('filter_status_pesan', $status);
            } else {
                $status = $request->session()->get('filter_status_pesan');
            }

            $data = PortalPesan::whereStatus($status)->orderBy("created_at", "ASC")->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->setRowId('uuid')
                ->addColumn('subjek', function ($data) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit     = route('prt.apps.kotak.pesan.edit', $uuid_enc);
                    if ($data->status == "Pending") {
                        $styleTitle = "text-danger fw-bold fst-italic";
                    } else {
                        $styleTitle = "";
                    }
                    $subjek = '
                    <div class="trans-list">
                        <h4><a class="text-underline ' . $styleTitle . '" href="' . $edit . '">' . Str::limit($data->subjek, 50, "...") . '</a></h4>
                    </div>';
                    return $subjek;
                })
                ->addColumn('tanggal', function ($data) {
                    $created_at = Helper::TglJam($data->created_at);
                    return $created_at;
                })
                ->addColumn('aksi', function ($data) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit     = route('prt.apps.kotak.pesan.edit', [$uuid_enc]);
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
        return view('pages.admin.portal_apps.kotak_pesan.index', compact(
            'status'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
        // uuid
        $uuid = Helper::decode($uuid_enc);
        $data = PortalPesan::findOrFail($uuid);
        // get kategori
        $title  = "Baca Pesan : " . $data->subjek;
        $submit = "Simpan";
        return view('pages.admin.portal_apps.kotak_pesan.create_edit', compact(
            'uuid_enc',
            'title',
            'submit',
            'auth',
            'data'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $uuid_enc)
    {
        $uuid = Helper::decode($uuid_enc);
        $data = PortalPesan::findOrFail($uuid);

        // Validasi input tanpa memvalidasi status
        $request->validate([
            "balasan" => "required|max:10000",
        ]);

        $value_1 = [
            "status" => "Responded",
        ];

        // balasan
        $thn  = date("Y", \strtotime($data->created_at));
        $path = "kotak_pesan/" . $thn . "/" . $uuid;
        if ($request->filled('balasan')) {
            $imgbalasan         = Helper::UpdateImgPostWithCompress($request, "balasan", $path);
            $value_1['balasan'] = $imgbalasan;
        }

        // save
        $save_1 = $data->update($value_1);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_kotak_pesan"],
                "uuid"  => [$uuid],
                "value" => [$request->subjek],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Mengubah Data Kotak Pesan UUID= " . $uuid,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            // Mengirim email balasan ke pengunjung
            if ($this->sendReplyEmail($data, $value_1['balasan'])) {
                alert()->success('Success', "Berhasil Mengubah Data dan Mengirim Balasan!");
            } else {
                alert()->warning('Warning', "Data berhasil diubah, tetapi gagal mengirim email balasan.");
            }
            // alert()->success('Success', "Berhasil Mengubah Data!");
            return redirect()->route('prt.apps.kotak.pesan.index');
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
        $data  = PortalPesan::findOrFail($uuid);
        $judul = $data->judul;

        // Lakukan soft delete
        if ($data->status == "Pending") {
            // drop path
            $tahun = Carbon::parse($data->created_at)->year;
            $path  = "kotak_pesan/{$tahun}/{$data->uuid}";
            Helper::deleteFolderIfExists("directory", $path);
            $save_1 = $data->forceDelete();
        } else {
            // Update uuid_deleted dan status sebelum melakukan soft delete
            $save_1 = $data->delete();
        }

        if ($save_1) {
            // Log aktivitas penghapusan
            $aktifitas = [
                "tabel" => ["portal_kotak_pesan"],
                "uuid"  => [$uuid],
                "value" => [$judul],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menghapus Data Kotak Pesan UUID= " . $uuid,
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

    // sendReplyEmail
    private function sendReplyEmail($data, $balasan)
    {
        $emailData = (object) [
            'nama_lengkap' => $data->nama_lengkap,
            'subjek'       => $data->subjek,
            'pesan'        => $data->pesan,
            'balasan'      => $balasan,
        ];

        try {
            Mail::to($data->email)->send(new BalasPesanMail($emailData));
            return true;
        } catch (\Exception $e) {
            \Log::error("Gagal mengirim email balasan: " . $e->getMessage());
            return false;
        }
    }
}
