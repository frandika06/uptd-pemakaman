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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
        $role = $auth->role;

        // cek filter
        if ($request->session()->exists('filter_status_pesan')) {
            $status = $request->session()->get('filter_status_pesan');
        } else {
            $request->session()->put('filter_status_pesan', 'Pending');
            $status = 'Pending';
        }

        if ($request->ajax()) {
            $status = $request->input('filter.status', $status);
            $request->session()->put('filter_status_pesan', $status);

            // Query dasar
            $query = PortalPesan::query();

            // Terapkan filter status
            $query->where('status', $status);

            // Urutkan berdasarkan created_at
            $query->orderBy('created_at', 'ASC');

            return DataTables::of($query)
                ->addIndexColumn()
                ->setRowId('uuid')
                ->addColumn('checkbox', function ($data) {
                    return '<div class="form-check form-check-sm form-check-custom form-check-solid"><input class="form-check-input row-checkbox" type="checkbox" value="' . $data->uuid . '" /></div>';
                })
                ->addColumn('subjek', function ($data) {
                    $uuid_enc   = Helper::encode($data->uuid);
                    $edit       = route('prt.apps.kotak.pesan.edit', [$uuid_enc]);
                    $styleTitle = $data->status == 'Pending' ? 'text-danger fw-bold fst-italic' : '';
                    return '
                        <div class="d-flex align-items-center">
                            <div class="d-flex flex-column">
                                <a href="' . $edit . '" class="text-gray-800 text-hover-primary mb-1 fw-bold fs-6 ' . $styleTitle . '">' . Str::limit($data->subjek, 50, '...') . '</a>
                            </div>
                        </div>
                    ';
                })
                ->addColumn('nama_lengkap', function ($data) {
                    return '<span class="text-gray-600 fw-semibold">' . $data->nama_lengkap . '</span>';
                })
                ->addColumn('email', function ($data) {
                    return '<span class="text-gray-600 fw-semibold">' . $data->email . '</span>';
                })
                ->addColumn('status', function ($data) {
                    $colors = [
                        'Pending'   => 'warning',
                        'Responded' => 'success',
                    ];
                    $color = $colors[$data->status] ?? 'secondary';
                    return '<span class="badge badge-light-' . $color . ' fw-bold fs-7 px-3 py-2">' . $data->status . '</span>';
                })
                ->addColumn('tanggal', function ($data) {
                    $created_at = Helper::TglJam($data->created_at);
                    return '<span class="text-gray-600 fw-semibold">' . $created_at . '</span>';
                })
                ->addColumn('aksi', function ($data) use ($auth) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit     = route('prt.apps.kotak.pesan.edit', [$uuid_enc]);
                    $role     = $auth->role;
                    $actions  = '
                        <div class="d-flex justify-content-center">
                            <a href="' . $edit . '" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" data-bs-toggle="tooltip" title="' . ($data->status == 'Pending' ? 'Balas' : 'Lihat') . '">
                                <i class="ki-outline ki-' . ($data->status == 'Pending' ? 'pencil' : 'eye') . ' fs-2"></i>
                            </a>
                            <a href="javascript:void(0);" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" data-kt-page-table-filter="delete_row" data-delete="' . $data->uuid . '" data-bs-toggle="tooltip" title="Hapus">
                                <i class="ki-outline ki-trash fs-2"></i>
                            </a>
                        </div>
                    ';
                    return $actions;
                })
                ->rawColumns(['checkbox', 'subjek', 'nama_lengkap', 'email', 'status', 'tanggal', 'aksi'])
                ->make(true);
        }

        return view('admin.helpdesk.pesan.index', compact('status'));
    }

    /**
     * Bulk delete messages
     */
    public function bulkDestroy(Request $request)
    {
        try {
            $auth = Auth::user();
            $request->validate([
                'uuids'   => 'required|array|min:1',
                'uuids.*' => 'required|string',
            ]);

            $uuids        = $request->uuids;
            $deletedCount = 0;
            $failedItems  = [];

            foreach ($uuids as $uuid) {
                try {
                    $data = PortalPesan::find($uuid);
                    if (! $data) {
                        $failedItems[] = "Data dengan ID {$uuid} tidak ditemukan";
                        continue;
                    }

                    if ($data->status == 'Pending') {
                        $tahun = Carbon::parse($data->created_at)->year;
                        $path  = "kotak_pesan/{$tahun}/{$data->uuid}";
                        Helper::deleteFolderIfExists('directory', $path);
                        $save_1 = $data->forceDelete();
                    } else {
                        $save_1 = $data->delete();
                    }

                    if ($save_1) {
                        $deletedCount++;
                        $aktifitas = [
                            'tabel' => ['portal_kotak_pesan'],
                            'uuid'  => [$uuid],
                            'value' => [$data->toArray()],
                        ];
                        $log = [
                            'apps'      => 'Portal Apps',
                            'subjek'    => "Menghapus Data Kotak Pesan (Bulk): {$data->subjek} - {$uuid}",
                            'aktifitas' => $aktifitas,
                            'device'    => 'web',
                        ];
                        Helper::addToLogAktifitas($request, $log);
                    } else {
                        $failedItems[] = "Gagal menghapus: {$data->subjek}";
                    }
                } catch (\Exception $e) {
                    $failedItems[] = "Error pada ID {$uuid}: {$e->getMessage()}";
                    continue;
                }
            }

            $message = "Berhasil menghapus {$deletedCount} pesan";
            if (! empty($failedItems)) {
                $message .= ". Gagal menghapus " . count($failedItems) . " item";
                if (count($failedItems) <= 3) {
                    $message .= ": " . implode(', ', $failedItems);
                }
            }

            $summaryLog = [
                'apps'      => 'Portal Apps',
                'subjek'    => "Bulk Delete Kotak Pesan - Berhasil: {$deletedCount}, Gagal: " . count($failedItems),
                'aktifitas' => [
                    'tabel'         => ['portal_kotak_pesan'],
                    'total_request' => count($uuids),
                    'total_deleted' => $deletedCount,
                    'total_failed'  => count($failedItems),
                    'failed_items'  => $failedItems,
                ],
                'device'    => 'web',
            ];
            Helper::addToLogAktifitas($request, $summaryLog);

            return response()->json([
                'status'        => true,
                'message'       => $message,
                'deleted_count' => $deletedCount,
                'failed_count'  => count($failedItems),
                'failed_items'  => $failedItems,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Bulk Delete Error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($uuid_enc)
    {
        $auth = Auth::user();
        $uuid = Helper::decode($uuid_enc);
        $data = PortalPesan::findOrFail($uuid);

        $title  = $data->status == 'Pending' ? "Balas Pesan: {$data->subjek}" : "Lihat Pesan: {$data->subjek}";
        $submit = 'Simpan';

        return view('admin.helpdesk.pesan.create_edit', compact(
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

        // Cek izin
        $auth = Auth::user();

        // Validasi hanya jika status Pending
        if ($data->status == 'Pending') {
            $request->validate([
                'balasan' => 'required|max:10000',
            ]);

            $value_1 = [
                'status'       => 'Responded',
                'uuid_updated' => $auth->uuid,
            ];

            $thn  = date('Y', strtotime($data->created_at));
            $path = "kotak_pesan/{$thn}/{$uuid}";
            if ($request->filled('balasan')) {
                $imgbalasan         = Helper::processTinyMCEBase64Images($request, 'balasan', $path);
                $value_1['balasan'] = $imgbalasan;
            }

            $save_1 = $data->update($value_1);
            if ($save_1) {
                $aktifitas = [
                    'tabel' => ['portal_kotak_pesan'],
                    'uuid'  => [$uuid],
                    'value' => [$data->subjek],
                ];
                $log = [
                    'apps'      => 'Portal Apps',
                    'subjek'    => "Mengubah Data Kotak Pesan UUID= {$uuid}",
                    'aktifitas' => $aktifitas,
                    'device'    => 'web',
                ];
                Helper::addToLogAktifitas($request, $log);

                if ($this->sendReplyEmail($data, $value_1['balasan'])) {
                    alert()->success('Success', 'Berhasil Mengubah Data dan Mengirim Balasan!');
                } else {
                    alert()->warning('Warning', 'Data berhasil diubah, tetapi gagal mengirim email balasan.');
                }
                return redirect()->route('prt.apps.kotak.pesan.index');
            } else {
                alert()->error('Error', 'Gagal Mengubah Data!');
                return back()->withInput($request->all());
            }
        } else {
            alert()->error('Error', 'Pesan sudah dibalas dan tidak dapat diubah lagi.');
            return redirect()->route('prt.apps.kotak.pesan.index');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $auth  = Auth::user();
        $uuid  = $request->uuid; // UUID asli dari request
        $data  = PortalPesan::findOrFail($uuid);
        $judul = $data->subjek;

        if ($data->status == 'Pending') {
            $tahun = Carbon::parse($data->created_at)->year;
            $path  = "kotak_pesan/{$tahun}/{$data->uuid}";
            Helper::deleteFolderIfExists('directory', $path);
            $save_1 = $data->forceDelete();
        } else {
            $save_1 = $data->delete();
        }

        if ($save_1) {
            $aktifitas = [
                'tabel' => ['portal_kotak_pesan'],
                'uuid'  => [$uuid],
                'value' => [$judul],
            ];
            $log = [
                'apps'      => 'Portal Apps',
                'subjek'    => "Menghapus Data Kotak Pesan UUID= {$uuid}",
                'aktifitas' => $aktifitas,
                'device'    => 'web',
            ];
            Helper::addToLogAktifitas($request, $log);

            return response()->json([
                'status'  => true,
                'message' => 'Data Berhasil Dihapus!',
            ], 200);
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'Data Gagal Dihapus!',
            ], 422);
        }
    }

    /**
     * Send reply email
     */
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
            Log::error('Gagal mengirim email balasan', [
                'email' => $data->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
}