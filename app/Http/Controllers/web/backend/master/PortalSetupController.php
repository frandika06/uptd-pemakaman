<?php
namespace App\Http\Controllers\web\backend\master;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\PortalSetup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class PortalSetupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $auth = Auth::user();

        // Handle filter for kategori
        $kategori = $request->session()->get('filter_kategori_portal_setup', 'Semua Data');
        $request->session()->put('filter_kategori_portal_setup', $kategori);

        // Handle filter for status
        $status = $request->session()->get('filter_status_portal_setup', '1');
        $request->session()->put('filter_status_portal_setup', $status);

        if ($request->ajax()) {
            if (isset($_GET['filter'])) {
                $kategori = $_GET['filter']['kategori'] ?? $kategori;
                $status   = $_GET['filter']['status'] ?? $status;
                $request->session()->put('filter_kategori_portal_setup', $kategori);
                $request->session()->put('filter_status_portal_setup', $status);
            }

            // Build query
            $query = PortalSetup::query()
                ->select(['uuid', 'nama_pengaturan', 'value_pengaturan', 'kategori', 'sites', 'keterangan', 'status'])
                ->orderBy('kategori', 'ASC')
                ->orderBy('nama_pengaturan', 'ASC');

            // Apply filters
            if ($kategori !== 'Semua Data') {
                $query->where('kategori', $kategori);
            }

            if ($status !== 'Semua Status') {
                $query->where('status', $status);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->setRowId('uuid')
                ->addColumn('nama_pengaturan', function ($data) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit_url = route('prt.apps.mst.portal_setup.edit', $uuid_enc);
                    return '
                        <div class="d-flex align-items-center">
                            <div class="d-flex flex-column">
                                <a href="' . $edit_url . '" class="text-gray-800 text-hover-primary mb-1 fw-bold fs-6">' . e($data->nama_pengaturan) . '</a>
                                <span class="text-muted fw-semibold d-block fs-7">' . e($data->keterangan ?? '-') . '</span>
                            </div>
                        </div>
                    ';
                })
                ->addColumn('value_pengaturan', function ($data) {
                    $value = e($data->value_pengaturan);
                    return strlen($value) > 100
                    ? '<span data-bs-toggle="tooltip" title="' . $value . '">' . Str::limit($value, 100) . '</span>'
                    : $value;
                })
                ->addColumn('kategori', function ($data) {
                    $colors = [
                        'Header'     => 'primary',
                        'Footer'     => 'success',
                        'SEO'        => 'warning',
                        'Hero'       => 'info',
                        'Kontak'     => 'danger',
                        'Organisasi' => 'dark',
                        'Layanan'    => 'secondary',
                    ];
                    $color = $colors[$data->kategori] ?? 'primary';
                    return '<span class="badge badge-light-' . $color . ' fw-bold fs-7 px-3 py-2">' . e($data->kategori) . '</span>';
                })
                ->addColumn('sites', function ($data) {
                    return '<span class="badge badge-light-info fw-bold fs-7 px-3 py-2">' . e($data->sites ?? 'Portal') . '</span>';
                })
                ->addColumn('status', function ($data) use ($auth) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $checked  = $data->status == '1' ? 'checked' : '';
                    $text     = $data->status == '1' ? 'Aktif' : 'Tidak Aktif';
                    $color    = $data->status == '1' ? 'success' : 'danger';

                    $canUpdate = in_array($auth->role, ['Super Admin', 'Admin']);
                    if ($canUpdate) {
                        return '
                            <div class="form-check form-switch form-check-custom form-check-success">
                                <input class="form-check-input" type="checkbox" role="switch"
                                    id="status_' . $data->uuid . '"
                                    data-status="' . $uuid_enc . '"
                                    data-status-value="' . $data->status . '" ' . $checked . '>
                                <label class="form-check-label fw-semibold text-' . $color . ' ms-3"
                                    for="status_' . $data->uuid . '">' . $text . '</label>
                            </div>
                        ';
                    }
                    return '<span class="badge badge-light-' . $color . ' fw-bold fs-7 px-3 py-2">' . $text . '</span>';
                })
                ->addColumn('aksi', function ($data) use ($auth) {
                    $uuid_enc  = Helper::encode($data->uuid);
                    $edit_url  = route('prt.apps.mst.portal_setup.edit', $uuid_enc);
                    $canEdit   = in_array($auth->role, ['Super Admin', 'Admin', 'Editor']);
                    $canDelete = in_array($auth->role, ['Super Admin', 'Admin']);

                    $aksi = '<div class="d-flex justify-content-center">';
                    if ($canEdit) {
                        $aksi .= '
                            <a href="' . $edit_url . '" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" data-bs-toggle="tooltip" title="Edit">
                                <i class="ki-outline ki-pencil fs-2"></i>
                            </a>';
                    }
                    if ($canDelete) {
                        $aksi .= '
                            <a href="javascript:void(0)" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm"
                               data-delete="' . $uuid_enc . '" data-bs-toggle="tooltip" title="Hapus">
                                <i class="ki-outline ki-trash fs-2"></i>
                            </a>';
                    }
                    $aksi .= '</div>';
                    return $aksi;
                })
                ->escapeColumns([])
                ->make(true);
        }

        $getKategori = PortalSetup::select('kategori')
            ->whereNotNull('kategori')
            ->groupBy('kategori')
            ->orderBy('kategori', 'ASC')
            ->pluck('kategori');

        return view('admin.cms.master.portal_setup.index', compact('kategori', 'status', 'getKategori'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.cms.master.portal_setup.create_edit', [
            'title'  => 'Tambah Portal Setup',
            'submit' => 'Simpan',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $auth = Auth::user();

        $request->validate([
            'nama_pengaturan'  => 'required|string|max:100',
            'value_pengaturan' => 'required|string',
            'kategori'         => 'required|string|max:100',
            'sites'            => 'required|string|max:100',
            'keterangan'       => 'nullable|string|max:255',
            'status'           => 'required|in:0,1',
        ]);

        $uuid  = Str::uuid()->toString();
        $input = $request->only(['nama_pengaturan', 'value_pengaturan', 'kategori', 'sites', 'keterangan', 'status']);

        if (PortalSetup::where('nama_pengaturan', $input['nama_pengaturan'])
            ->where('kategori', $input['kategori'])
            ->where('sites', $input['sites'])
            ->exists()
        ) {
            alert()->error('Error!', 'Pengaturan dengan nama, kategori, dan sites yang sama sudah ada!');
            return back()->withInput();
        }

        try {
            $input['uuid']         = $uuid;
            $input['uuid_created'] = $auth->uuid;
            $input['uuid_updated'] = $auth->uuid;

            PortalSetup::create($input);

            Helper::addToLogAktifitas($request, [
                'apps'      => 'Portal Apps',
                'subjek'    => "Menambahkan Portal Setup: {$input['nama_pengaturan']} - {$uuid}",
                'aktifitas' => [
                    'tabel' => ['portal_setup'],
                    'uuid'  => [$uuid],
                    'value' => [$input],
                ],
                'device'    => 'web',
            ]);

            alert()->success('Success!', 'Portal Setup berhasil ditambahkan!');
            return redirect()->route('prt.apps.mst.portal_setup.index');

        } catch (\Exception $e) {
            Log::error('Store Portal Setup Error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            alert()->error('Error!', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($uuid_enc)
    {
        try {
            $uuid = Helper::decode($uuid_enc);
            $data = PortalSetup::where('uuid', $uuid)->firstOrFail();

            return view('admin.cms.master.portal_setup.create_edit', [
                'data'   => $data,
                'title'  => 'Edit Portal Setup',
                'submit' => 'Update',
            ]);

        } catch (\Exception $e) {
            Log::error('Edit Portal Setup Error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            alert()->error('Error!', 'Data Portal Setup tidak ditemukan: ' . $e->getMessage());
            return redirect()->route('prt.apps.mst.portal_setup.index');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $uuid_enc)
    {
        $auth = Auth::user();

        try {
            // Dekode UUID
            $uuid  = Helper::decode($uuid_enc);
            $setup = PortalSetup::where('uuid', $uuid)->first();
            if (! $setup) {
                Log::error('Update Portal Setup Error', ['message' => 'Data tidak ditemukan', 'uuid' => $uuid_enc]);
                alert()->error('Error!', 'Data pengaturan tidak ditemukan.');
                return redirect()->route('prt.apps.mst.portal_setup.index');
            }

            // Validasi input
            $validator = Validator::make($request->all(), [
                'nama_pengaturan'  => 'required|string|max:100',
                'value_pengaturan' => 'required|string',
                'kategori'         => 'required|string|max:100',
                'sites'            => 'required|string|max:100',
                'keterangan'       => 'nullable|string|max:255',
                'status'           => 'required|in:0,1',
            ]);

            if ($validator->fails()) {
                Log::warning('Update Portal Setup Validation Failed', [
                    'uuid'   => $uuid,
                    'errors' => $validator->errors()->toArray(),
                    'input'  => $request->all(),
                ]);
                alert()->error('Error!', 'Validasi gagal. Silakan periksa input Anda.');
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Update data
            $setup->update([
                'nama_pengaturan'  => $request->nama_pengaturan,
                'value_pengaturan' => $request->value_pengaturan,
                'kategori'         => $request->kategori,
                'sites'            => $request->sites,
                'keterangan'       => $request->keterangan,
                'status'           => $request->status,
                'uuid_updated'     => $auth->uuid,
            ]);

            Helper::addToLogAktifitas($request, [
                'apps'      => 'Portal Apps',
                'subjek'    => "Memperbarui Portal Setup: {$request->nama_pengaturan} - {$uuid}",
                'aktifitas' => [
                    'tabel' => ['portal_setup'],
                    'uuid'  => [$uuid],
                    'value' => [$request->all()],
                ],
                'device'    => 'web',
            ]);

            alert()->success('Success!', 'Pengaturan berhasil diperbarui.');
            return redirect()->route('prt.apps.mst.portal_setup.index');

        } catch (\Exception $e) {
            Log::error('Update Portal Setup Error', [
                'uuid'    => $uuid_enc,
                'input'   => $request->all(),
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            alert()->error('Error!', 'Terjadi kesalahan saat memperbarui pengaturan: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Update status (AJAX)
     */
    public function status(Request $request)
    {
        $auth = Auth::user();

        $request->validate([
            'uuid'   => 'required|string',
            'status' => 'required|in:0,1',
        ]);

        try {
            $uuid = Helper::decode($request->uuid);
            $data = PortalSetup::where('uuid', $uuid)->firstOrFail();

            $canUpdate = in_array($auth->role, ['Super Admin', 'Admin']);
            if (! $canUpdate) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Tidak memiliki izin untuk mengubah status!',
                ], 403);
            }

            $data->update([
                'status'       => $request->status,
                'uuid_updated' => $auth->uuid,
            ]);

            Helper::addToLogAktifitas($request, [
                'apps'      => 'Portal Apps',
                'subjek'    => "Mengubah Status Portal Setup: {$data->nama_pengaturan} - {$uuid}",
                'aktifitas' => [
                    'tabel' => ['portal_setup'],
                    'uuid'  => [$uuid],
                    'value' => [['status' => $request->status]],
                ],
                'device'    => 'web',
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Status berhasil diupdate!',
            ]);

        } catch (\Exception $e) {
            Log::error('Status Update Portal Setup Error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage (AJAX)
     */
    public function destroy(Request $request)
    {
        $auth = Auth::user();

        $request->validate([
            'uuid' => 'required|string',
        ]);

        try {
            $uuid = Helper::decode($request->uuid);
            $data = PortalSetup::where('uuid', $uuid)->firstOrFail();

            $canDelete = in_array($auth->role, ['Super Admin', 'Admin']);
            if (! $canDelete) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Tidak memiliki izin untuk menghapus!',
                ], 403);
            }

            $nama_pengaturan = $data->nama_pengaturan;
            $data->delete();

            Helper::addToLogAktifitas($request, [
                'apps'      => 'Portal Apps',
                'subjek'    => "Menghapus Portal Setup: {$nama_pengaturan} - {$uuid}",
                'aktifitas' => [
                    'tabel' => ['portal_setup'],
                    'uuid'  => [$uuid],
                    'value' => [$data->toArray()],
                ],
                'device'    => 'web',
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Portal Setup berhasil dihapus!',
            ]);

        } catch (\Exception $e) {
            Log::error('Delete Portal Setup Error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk delete portal setups
     */
    public function bulkDestroy(Request $request)
    {
        $auth = Auth::user();

        $request->validate([
            'uuids'   => 'required|array|min:1',
            'uuids.*' => 'required|string',
        ]);

        try {
            $uuids        = $request->uuids;
            $deletedCount = 0;
            $failedItems  = [];

            foreach ($uuids as $uuid_enc) {
                try {
                    $uuid = Helper::decode($uuid_enc);
                    $data = PortalSetup::where('uuid', $uuid)->first();

                    if (! $data) {
                        $failedItems[] = "Data dengan ID {$uuid_enc} tidak ditemukan";
                        continue;
                    }

                    $canDelete = in_array($auth->role, ['Super Admin', 'Admin']);
                    if (! $canDelete) {
                        $failedItems[] = "Tidak memiliki izin untuk menghapus: {$data->nama_pengaturan}";
                        continue;
                    }

                    $nama_pengaturan = $data->nama_pengaturan;
                    if ($data->delete()) {
                        $deletedCount++;

                        Helper::addToLogAktifitas($request, [
                            'apps'      => 'Portal Apps',
                            'subjek'    => "Menghapus Portal Setup (Bulk): {$nama_pengaturan} - {$uuid}",
                            'aktifitas' => [
                                'tabel' => ['portal_setup'],
                                'uuid'  => [$uuid],
                                'value' => [$data->toArray()],
                            ],
                            'device'    => 'web',
                        ]);
                    } else {
                        $failedItems[] = "Gagal menghapus: {$nama_pengaturan}";
                    }

                } catch (\Exception $e) {
                    $failedItems[] = "Error pada ID {$uuid_enc}: " . $e->getMessage();
                    continue;
                }
            }

            $message = "Berhasil menghapus {$deletedCount} pengaturan";
            if (! empty($failedItems)) {
                $message .= ". Gagal menghapus " . count($failedItems) . " item";
                if (count($failedItems) <= 3) {
                    $message .= ": " . implode(", ", $failedItems);
                }
            }

            Helper::addToLogAktifitas($request, [
                'apps'      => 'Portal Apps',
                'subjek'    => "Bulk Delete Portal Setup - Berhasil: {$deletedCount}, Gagal: " . count($failedItems),
                'aktifitas' => [
                    'tabel'         => ['portal_setup'],
                    'total_request' => count($uuids),
                    'total_deleted' => $deletedCount,
                    'failed_items'  => $failedItems,
                ],
                'device'    => 'web',
            ]);

            return response()->json([
                'status'        => true,
                'message'       => $message,
                'deleted_count' => $deletedCount,
                'failed_count'  => count($failedItems),
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk Delete Portal Setup Error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk status update
     */
    public function bulkStatus(Request $request)
    {
        $auth = Auth::user();

        $request->validate([
            'uuids'   => 'required|array|min:1',
            'uuids.*' => 'required|string',
            'status'  => 'required|in:0,1',
        ]);

        try {
            $uuids        = $request->uuids;
            $newStatus    = $request->status;
            $updatedCount = 0;
            $failedItems  = [];

            foreach ($uuids as $uuid_enc) {
                try {
                    $uuid = Helper::decode($uuid_enc);
                    $data = PortalSetup::where('uuid', $uuid)->first();

                    if (! $data) {
                        $failedItems[] = "Data dengan ID {$uuid_enc} tidak ditemukan";
                        continue;
                    }

                    $canUpdate = in_array($auth->role, ['Super Admin', 'Admin']);
                    if (! $canUpdate) {
                        $failedItems[] = "Tidak memiliki izin untuk mengubah: {$data->nama_pengaturan}";
                        continue;
                    }

                    if ($data->update(['status' => $newStatus, 'uuid_updated' => $auth->uuid])) {
                        $updatedCount++;

                        Helper::addToLogAktifitas($request, [
                            'apps'      => 'Portal Apps',
                            'subjek'    => "Bulk Update Status Portal Setup: {$data->nama_pengaturan} - {$uuid}",
                            'aktifitas' => [
                                'tabel' => ['portal_setup'],
                                'uuid'  => [$uuid],
                                'value' => [['status' => $newStatus]],
                            ],
                            'device'    => 'web',
                        ]);
                    } else {
                        $failedItems[] = "Gagal mengubah status: {$data->nama_pengaturan}";
                    }

                } catch (\Exception $e) {
                    $failedItems[] = "Error pada ID {$uuid_enc}: " . $e->getMessage();
                    continue;
                }
            }

            $statusText = $newStatus == '1' ? 'mengaktifkan' : 'menonaktifkan';
            $message    = "Berhasil {$statusText} {$updatedCount} pengaturan";
            if (! empty($failedItems)) {
                $message .= ". Gagal {$statusText} " . count($failedItems) . " item";
            }

            Helper::addToLogAktifitas($request, [
                'apps'      => 'Portal Apps',
                'subjek'    => "Bulk Update Status Portal Setup - Berhasil: {$updatedCount}, Gagal: " . count($failedItems),
                'aktifitas' => [
                    'tabel'         => ['portal_setup'],
                    'total_request' => count($uuids),
                    'total_updated' => $updatedCount,
                    'failed_items'  => $failedItems,
                ],
                'device'    => 'web',
            ]);

            return response()->json([
                'status'        => true,
                'message'       => $message,
                'updated_count' => $updatedCount,
                'failed_count'  => count($failedItems),
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk Status Update Portal Setup Error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan saat mengubah status: ' . $e->getMessage(),
            ], 500);
        }
    }
}
