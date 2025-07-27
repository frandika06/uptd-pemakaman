<?php
namespace App\Http\Controllers\web\backend\tpu;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\TpuDatas;
use App\Models\TpuDokumen;
use App\Models\TpuKategoriDokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class TpuDatasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Auth
        $auth = Auth::user();

        // Inisialisasi filter dari session atau default
        $status    = $request->session()->get('filter_status_tpu', 'Semua Data');
        $jenis_tpu = $request->session()->get('filter_jenis_tpu', 'Semua Jenis');

        if ($request->ajax()) {
            // Ambil filter dari GET jika ada
            if (isset($_GET['filter']) && is_array($_GET['filter'])) {
                $status    = $_GET['filter']['status'] ?? $status;
                $jenis_tpu = $_GET['filter']['jenis_tpu'] ?? $jenis_tpu;
                // Simpan ke session
                $request->session()->put('filter_status_tpu', $status);
                $request->session()->put('filter_jenis_tpu', $jenis_tpu);
            }

            // Query berdasarkan role dan filter
            $query = TpuDatas::query();

            // Filter berdasarkan role user
            if ($auth->role === 'Admin TPU' || $auth->role === 'Petugas TPU') {
                // Filter hanya TPU yang terkait dengan user
                if ($auth->RelPetugasTpu && $auth->RelPetugasTpu->uuid_tpu) {
                    $query->where('uuid', $auth->RelPetugasTpu->uuid_tpu);
                } else {
                                               // Jika tidak ada relasi TPU, kembalikan data kosong
                    $query->whereNull('uuid'); // This will return empty result
                }
            }
            // Super Admin dan Admin bisa melihat semua TPU

            // Filter berdasarkan status
            if ($status != 'Semua Data') {
                $query->where('status', $status);
            }

            // Filter berdasarkan jenis TPU
            if ($jenis_tpu != 'Semua Jenis') {
                $query->where('jenis_tpu', $jenis_tpu);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->setRowId('uuid')
                ->addColumn('nama', function ($data) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit_url = route('tpu.datas.edit', $uuid_enc);

                    // Count dokumen jika relasi tersedia
                    $dokumen_count = 0;
                    try {
                        if (method_exists($data, 'Dokumens')) {
                            $dokumen_count = $data->Dokumens->count();
                        }
                    } catch (\Exception $e) {
                        // Ignore error if relation doesn't exist yet
                    }

                    $dokumen_badge = $dokumen_count > 0 ?
                    '<div class="mt-1"><span class="badge badge-light-info fs-8"><i class="ki-outline ki-document fs-8 me-1"></i>' . $dokumen_count . ' Dokumen</span></div>' : '';

                    return '
                    <div class="d-flex align-items-center">
                        <div class="d-flex flex-column">
                            <a href="' . $edit_url . '" class="text-gray-800 text-hover-primary mb-1 fw-bold fs-6">' . $data->nama . '</a>
                            <span class="text-muted fw-semibold d-block fs-7">' . Str::slug($data->nama) . '</span>
                            ' . $dokumen_badge . '
                        </div>
                    </div>
                ';
                })
                ->addColumn('alamat', function ($data) {
                    return '<span class="text-gray-600 fw-semibold d-block fs-7">' . $data->alamat . '</span>';
                })
                ->addColumn('kecamatan', function ($data) {
                    return '<span class="text-gray-600 fw-semibold d-block fs-7">' . $data->kecamatan . '</span>';
                })
                ->addColumn('kelurahan', function ($data) {
                    return '<span class="text-gray-600 fw-semibold d-block fs-7">' . $data->kelurahan . '</span>';
                })
                ->addColumn('jenis_tpu', function ($data) {
                    $colors = [
                        'muslim'     => 'primary',
                        'non_muslim' => 'warning',
                        'gabungan'   => 'success',
                    ];
                    $color = $colors[$data->jenis_tpu] ?? 'secondary';
                    return '<span class="badge badge-light-' . $color . ' fw-bold fs-7 px-3 py-2">' . $data->jenis_tpu . '</span>';
                })
                ->addColumn('status', function ($data) {
                    $colors = [
                        'Aktif'       => 'success',
                        'Tidak Aktif' => 'danger',
                        'Penuh'       => 'warning',
                    ];
                    $color = $colors[$data->status] ?? 'secondary';
                    return '<span class="badge badge-light-' . $color . ' fw-bold fs-7 px-3 py-2">' . $data->status . '</span>';
                })
                ->addColumn('aksi', function ($data) use ($auth) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit_url = route('tpu.datas.edit', $uuid_enc);
                    $role     = $auth->role;

                    // Super Admin dan Admin memiliki akses penuh
                    if ($role == 'Super Admin' || $role == 'Admin') {
                        $aksi = '
                        <div class="d-flex justify-content-center">
                            <a href="' . $edit_url . '" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" data-bs-toggle="tooltip" title="Edit">
                                <i class="ki-outline ki-pencil fs-2"></i>
                            </a>
                            <a href="javascript:void(0);" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" data-delete="' . $uuid_enc . '" data-bs-toggle="tooltip" title="Hapus">
                                <i class="ki-outline ki-trash fs-2"></i>
                            </a>
                        </div>
                    ';
                    } elseif ($role == 'Admin TPU') {
                        // Admin TPU bisa edit/hapus TPU mereka sendiri
                        if ($auth->RelPetugasTpu && $auth->RelPetugasTpu->uuid_tpu === $data->uuid) {
                            $aksi = '
                            <div class="d-flex justify-content-center">
                                <a href="' . $edit_url . '" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" data-bs-toggle="tooltip" title="Edit">
                                    <i class="ki-outline ki-pencil fs-2"></i>
                                </a>
                                <a href="javascript:void(0);" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" data-delete="' . $uuid_enc . '" data-bs-toggle="tooltip" title="Hapus">
                                    <i class="ki-outline ki-trash fs-2"></i>
                                </a>
                            </div>
                        ';
                        } else {
                            $aksi = '
                            <div class="d-flex justify-content-center">
                                <span class="btn btn-icon btn-bg-light btn-sm me-1 disabled" data-bs-toggle="tooltip" title="Edit (Tidak diizinkan)">
                                    <i class="ki-outline ki-pencil fs-2 text-muted"></i>
                                </span>
                                <span class="btn btn-icon btn-bg-light btn-sm disabled" data-bs-toggle="tooltip" title="Hapus (Tidak diizinkan)">
                                    <i class="ki-outline ki-trash fs-2 text-muted"></i>
                                </span>
                            </div>
                        ';
                        }
                    } else {
                        // Petugas TPU - readonly (button disabled)
                        $aksi = '
                        <div class="d-flex justify-content-center">
                            <span class="btn btn-icon btn-bg-light btn-sm me-1 disabled" data-bs-toggle="tooltip" title="Edit (Tidak diizinkan)">
                                <i class="ki-outline ki-pencil fs-2 text-muted"></i>
                            </span>
                            <span class="btn btn-icon btn-bg-light btn-sm disabled" data-bs-toggle="tooltip" title="Hapus (Tidak diizinkan)">
                                <i class="ki-outline ki-trash fs-2 text-muted"></i>
                            </span>
                        </div>
                    ';
                    }
                    return $aksi;
                })
                ->escapeColumns([])
                ->make(true);
        }

        // Get all status and jenis_tpu options (filter berdasarkan akses user)
        $statusQuery   = TpuDatas::select('status')->distinct()->orderBy('status', 'ASC');
        $jenisTpuQuery = TpuDatas::select('jenis_tpu')->distinct()->orderBy('jenis_tpu', 'ASC');

        // Filter untuk Admin TPU dan Petugas TPU
        if ($auth->role === 'Admin TPU' || $auth->role === 'Petugas TPU') {
            if ($auth->RelPetugasTpu && $auth->RelPetugasTpu->uuid_tpu) {
                $statusQuery->where('uuid', $auth->RelPetugasTpu->uuid_tpu);
                $jenisTpuQuery->where('uuid', $auth->RelPetugasTpu->uuid_tpu);
            } else {
                // Jika tidak ada relasi TPU, kembalikan data kosong
                $statusQuery->whereNull('uuid');
                $jenisTpuQuery->whereNull('uuid');
            }
        }

        $getStatus   = $statusQuery->get();
        $getJenisTpu = $jenisTpuQuery->get();

        return view('admin.tpu.data.index', compact(
            'status',
            'jenis_tpu',
            'getStatus',
            'getJenisTpu'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title      = 'Tambah Data TPU';
        $submit     = 'Simpan';
        $kecamatans = Helper::getKecamatanList(3603); // ID Kabupaten Tangerang

                                      // Get kategori dokumen untuk TPU (optional - jika table ada)
        $kategoriDokumen = collect(); // Default empty collection
        try {
            $kategoriDokumen = TpuKategoriDokumen::where('status', '1')
                ->where('tipe', 'dokumen-tpu')
                ->orderBy('nama', 'ASC')
                ->get();
        } catch (\Exception $e) {
            // Ignore if table doesn't exist yet
        }

        return view('admin.tpu.data.create_edit', compact(
            'title',
            'submit',
            'kecamatans',
            'kategoriDokumen'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Auth
        $auth = Auth::user();

        // Validate basic data
        $request->validate([
            'nama'         => 'required|string|max:100',
            'alamat'       => 'required|string|max:255',
            'kecamatan_id' => 'required|string|max:10',
            'kelurahan_id' => 'required|string|max:10',
            'jenis_tpu'    => 'required|in:muslim,non_muslim,gabungan',
            'latitude'     => 'nullable|numeric|between:-90,90',
            'longitude'    => 'nullable|numeric|between:-180,180',
            'status'       => 'required|in:Aktif,Tidak Aktif,Penuh',
        ]);

        // Validate dokumen if any (optional)
        if ($request->has('dokumen_kategori')) {
            foreach ($request->dokumen_kategori as $index => $kategori) {
                if (! empty($kategori)) {
                    $request->validate([
                        "dokumen_file.$index"      => 'required|file|max:10240', // 10MB
                        "dokumen_nama.$index"      => 'required|string|max:100',
                        "dokumen_deskripsi.$index" => 'nullable|string|max:500',
                    ]);
                }
            }
        }

        // Ambil data kecamatan dan kelurahan
        $kecamatanList = Helper::getKecamatanList(3603);
        $kecamatan     = collect($kecamatanList['data'])->firstWhere('id', $request->kecamatan_id);
        $kelurahanList = Helper::getDesaList($request->kecamatan_id);
        $kelurahan     = collect($kelurahanList['data'])->firstWhere('id', $request->kelurahan_id);

        if (! $kecamatan || ! $kelurahan) {
            alert()->error('Error!', 'Kecamatan atau kelurahan tidak valid!');
            return back()->withInput($request->all());
        }

        // UUID
        $uuid = Str::uuid();

        // Cek duplikasi TPU
        $cekTpu = TpuDatas::where('nama', $request->nama)
            ->where('kecamatan_id', $request->kecamatan_id)
            ->where('kelurahan_id', $request->kelurahan_id)
            ->first();
        if ($cekTpu !== null) {
            alert()->error('Error!', 'Nama TPU sudah ada di kecamatan dan kelurahan ini!');
            return back()->withInput($request->all());
        }

        // Value
        $value = [
            'uuid'         => $uuid,
            'nama'         => $request->nama,
            'alamat'       => $request->alamat,
            'kecamatan_id' => $request->kecamatan_id,
            'kelurahan_id' => $request->kelurahan_id,
            'kecamatan'    => $kecamatan['name'],
            'kelurahan'    => $kelurahan['name'],
            'jenis_tpu'    => $request->jenis_tpu,
            'latitude'     => $request->latitude,
            'longitude'    => $request->longitude,
            'status'       => $request->status,
            'uuid_created' => $auth->uuid,
            'uuid_updated' => $auth->uuid,
        ];

        // Save
        $save = TpuDatas::create($value);
        if ($save) {
            // Process dokumen uploads if any (optional)
            if ($request->has('dokumen_kategori')) {
                $this->processDokumenUploads($request, $uuid, 'TPU');
            }

            // Create log
            $aktifitas = [
                'tabel' => ['tpu_datas'],
                'uuid'  => [$uuid],
                'value' => [$value],
            ];
            $log = [
                'apps'      => 'TPU Admin',
                'subjek'    => 'Menambahkan Data TPU: ' . $request->nama . ' - ' . $uuid,
                'aktifitas' => $aktifitas,
                'device'    => 'web',
            ];
            Helper::addToLogAktifitas($request, $log);
            // Alert success
            alert()->success('Success', 'Berhasil Menambahkan Data TPU!');
            return redirect()->route('tpu.datas.index');
        } else {
            alert()->error('Error', 'Gagal Menambahkan Data TPU!');
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
        // UUID
        $uuid       = Helper::decode($uuid_enc);
        $data       = TpuDatas::findOrFail($uuid);
        $title      = 'Edit Data TPU';
        $submit     = 'Simpan';
        $kecamatans = Helper::getKecamatanList(3603);           // ID Kabupaten Tangerang
        $kelurahans = Helper::getDesaList($data->kecamatan_id); // Get kelurahan based on kecamatan_id

        // Get kategori dokumen untuk TPU (optional)
        $kategoriDokumen = collect();
        $existingDokumen = collect();
        try {
            $kategoriDokumen = TpuKategoriDokumen::where('status', '1')
                ->where('tipe', 'dokumen-tpu')
                ->orderBy('nama', 'ASC')
                ->get();

            // Get existing dokumen
            if (method_exists($data, 'Dokumens')) {
                $existingDokumen = $data->Dokumens;
            }
        } catch (\Exception $e) {
            // Ignore if table/relation doesn't exist yet
        }

        return view('admin.tpu.data.create_edit', compact(
            'uuid_enc',
            'title',
            'submit',
            'data',
            'kecamatans',
            'kelurahans',
            'kategoriDokumen',
            'existingDokumen'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $uuid_enc)
    {
        // Auth
        $auth = Auth::user();

        // Validate basic data
        $request->validate([
            'nama'         => 'required|string|max:100',
            'alamat'       => 'required|string|max:255',
            'kecamatan_id' => 'required|string|max:10',
            'kelurahan_id' => 'required|string|max:10',
            'jenis_tpu'    => 'required|in:muslim,non_muslim,gabungan',
            'latitude'     => 'nullable|numeric|between:-90,90',
            'longitude'    => 'nullable|numeric|between:-180,180',
            'status'       => 'required|in:Aktif,Tidak Aktif,Penuh',
        ]);

        // Validate dokumen if any (optional)
        if ($request->has('dokumen_kategori')) {
            foreach ($request->dokumen_kategori as $index => $kategori) {
                if (! empty($kategori)) {
                    // File is required only for new uploads (when no existing_dokumen_id)
                    $fileRequired = empty($request->existing_dokumen_id[$index] ?? '') ? 'required|' : 'nullable|';
                    $request->validate([
                        "dokumen_file.$index"      => $fileRequired . 'file|max:10240',
                        "dokumen_nama.$index"      => 'required|string|max:100',
                        "dokumen_deskripsi.$index" => 'nullable|string|max:500',
                    ]);
                }
            }
        }

        // UUID
        $uuid = Helper::decode($uuid_enc);
        $data = TpuDatas::findOrFail($uuid);

        // Ambil data kecamatan dan kelurahan
        $kecamatanList = Helper::getKecamatanList(3603);
        $kecamatan     = collect($kecamatanList['data'])->firstWhere('id', $request->kecamatan_id);
        $kelurahanList = Helper::getDesaList($request->kecamatan_id);
        $kelurahan     = collect($kelurahanList['data'])->firstWhere('id', $request->kelurahan_id);

        if (! $kecamatan || ! $kelurahan) {
            alert()->error('Error!', 'Kecamatan atau kelurahan tidak valid!');
            return back()->withInput($request->all());
        }

        // Value
        $value = [
            'nama'         => $request->nama,
            'alamat'       => $request->alamat,
            'kecamatan_id' => $request->kecamatan_id,
            'kelurahan_id' => $request->kelurahan_id,
            'kecamatan'    => $kecamatan['name'],
            'kelurahan'    => $kelurahan['name'],
            'jenis_tpu'    => $request->jenis_tpu,
            'latitude'     => $request->latitude,
            'longitude'    => $request->longitude,
            'status'       => $request->status,
            'uuid_updated' => $auth->uuid,
        ];

        // Save
        $save = $data->update($value);
        if ($save) {
            // Process dokumen updates/uploads (optional)
            if ($request->has('dokumen_kategori')) {
                $this->processDokumenUploads($request, $uuid, 'TPU');
            }

            // Process dokumen deletions (optional)
            if ($request->has('deleted_dokumen_ids')) {
                $this->processDeletedDokumen($request->deleted_dokumen_ids, $uuid);
            }

            // Create log
            $aktifitas = [
                'tabel' => ['tpu_datas'],
                'uuid'  => [$uuid],
                'value' => [$value],
            ];
            $log = [
                'apps'      => 'TPU Admin',
                'subjek'    => 'Mengubah Data TPU: ' . $request->nama . ' - ' . $uuid,
                'aktifitas' => $aktifitas,
                'device'    => 'web',
            ];
            Helper::addToLogAktifitas($request, $log);
            // Alert success
            alert()->success('Success', 'Berhasil Mengubah Data TPU!');
            return redirect()->route('tpu.datas.index');
        } else {
            alert()->error('Error', 'Gagal Mengubah Data TPU!');
            return back()->withInput($request->all());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        // Auth
        $auth = Auth::user();

        // UUID
        $uuid = Helper::decode($request->uuid);

        // Data
        $data = TpuDatas::findOrFail($uuid);

        // Delete associated dokumen files (optional)
        try {
            if (method_exists($data, 'Dokumens')) {
                $dokumens = $data->Dokumens;
                foreach ($dokumens as $dokumen) {
                    if ($dokumen->url && Storage::disk('public')->exists($dokumen->url)) {
                        Storage::disk('public')->delete($dokumen->url);
                    }
                }
            }
        } catch (\Exception $e) {
            // Ignore if relation doesn't exist
        }

        // Save
        $save = $data->delete();
        if ($save) {
            // Create log
            $aktifitas = [
                'tabel' => ['tpu_datas'],
                'uuid'  => [$uuid],
                'value' => [$data->toArray()],
            ];
            $log = [
                'apps'      => 'TPU Admin',
                'subjek'    => 'Menghapus Data TPU: ' . $data->nama . ' - ' . $uuid,
                'aktifitas' => $aktifitas,
                'device'    => 'web',
            ];
            Helper::addToLogAktifitas($request, $log);
            // Alert success
            $msg      = 'Data TPU Berhasil Dihapus!';
            $response = [
                'status'  => true,
                'message' => $msg,
            ];
            return response()->json($response, 200);
        } else {
            $msg      = 'Data TPU Gagal Dihapus!';
            $response = [
                'status'  => false,
                'message' => $msg,
            ];
            return response()->json($response, 422);
        }
    }

    /**
     * Bulk delete TPU data
     */
    public function bulkDestroy(Request $request)
    {
        try {
            // Auth
            $auth = Auth::user();

            // Validate request
            $request->validate([
                'uuids'   => 'required|array|min:1',
                'uuids.*' => 'required|string',
            ]);

            $uuids        = $request->uuids;
            $deletedCount = 0;
            $failedItems  = [];

            // Loop through each UUID and delete
            foreach ($uuids as $uuid_enc) {
                try {
                    $uuid = $uuid_enc;
                    $data = TpuDatas::findOrFail($uuid);

                    $role      = $auth->role;
                    $canDelete = false;

                    // Super Admin dan Admin bisa menghapus semua
                    if ($role == 'Super Admin' || $role == 'Admin') {
                        $canDelete = true;
                    } elseif ($role == 'Admin TPU') {
                        // Admin TPU hanya bisa menghapus TPU mereka sendiri
                        if ($auth->RelPetugasTpu && $auth->RelPetugasTpu->uuid_tpu === $data->uuid) {
                            $canDelete = true;
                        }
                    }
                    // Petugas TPU tidak bisa menghapus

                    if (! $canDelete) {
                        $failedItems[] = 'Tidak memiliki izin untuk menghapus: ' . $data->nama;
                        continue;
                    }

                    // Delete associated dokumen files (optional)
                    try {
                        if (method_exists($data, 'Dokumens')) {
                            $dokumens = $data->Dokumens;
                            foreach ($dokumens as $dokumen) {
                                if ($dokumen->url && Storage::disk('public')->exists($dokumen->url)) {
                                    Storage::disk('public')->delete($dokumen->url);
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        // Ignore if relation doesn't exist
                    }

                    if ($data->delete()) {
                        $deletedCount++;

                        $aktifitas = [
                            'tabel' => ['tpu_datas'],
                            'uuid'  => [$uuid],
                            'value' => [$data->toArray()],
                        ];
                        $log = [
                            'apps'      => 'TPU Admin',
                            'subjek'    => 'Menghapus Data TPU (Bulk): ' . $data->nama . ' - ' . $uuid,
                            'aktifitas' => $aktifitas,
                            'device'    => 'web',
                        ];
                        Helper::addToLogAktifitas($request, $log);
                    } else {
                        $failedItems[] = 'Gagal menghapus: ' . $data->nama;
                    }
                } catch (\Exception $e) {
                    $failedItems[] = 'Error pada ID ' . $uuid_enc . ': ' . $e->getMessage();
                    continue;
                }
            }

            $message = 'Berhasil menghapus ' . $deletedCount . ' data TPU';
            if (! empty($failedItems)) {
                $message .= '. Gagal menghapus ' . count($failedItems) . ' item';
            }

            $summaryLog = [
                'apps'      => 'TPU Admin',
                'subjek'    => 'Bulk Delete Data TPU - Berhasil: ' . $deletedCount . ', Gagal: ' . count($failedItems),
                'aktifitas' => [
                    'tabel'         => ['tpu_datas'],
                    'total_request' => count($uuids),
                    'total_deleted' => $deletedCount,
                    'total_failed'  => count($failedItems),
                    'failed_items'  => $failedItems,
                ],
                'device'    => 'web',
            ];
            Helper::addToLogAktifitas($request, $summaryLog);

            $response = [
                'status'        => true,
                'message'       => $message,
                'deleted_count' => $deletedCount,
                'failed_count'  => count($failedItems),
                'failed_items'  => $failedItems,
            ];

            return response()->json($response, 200);
        } catch (\Exception $e) {
            \Log::error('Bulk Delete Error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            $response = [
                'status'  => false,
                'message' => 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage(),
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Process dokumen uploads (optional method)
     */
    private function processDokumenUploads($request, $uuid_modul, $nama_modul)
    {
        // Only process if TpuDokumen model exists
        if (! class_exists('App\Models\TpuDokumen')) {
            return;
        }

        $auth = Auth::user();

        if (! $request->has('dokumen_kategori')) {
            return;
        }

        foreach ($request->dokumen_kategori as $index => $kategori_uuid) {
            if (empty($kategori_uuid)) {
                continue;
            }

            $existing_id = $request->existing_dokumen_id[$index] ?? null;
            $nama_file   = $request->dokumen_nama[$index] ?? '';
            $deskripsi   = $request->dokumen_deskripsi[$index] ?? '';
            $file        = $request->file("dokumen_file.$index");

            // Skip if no file and no existing dokumen (invalid entry)
            if (! $file && ! $existing_id) {
                continue;
            }

            try {
                if ($existing_id) {
                    // Update existing dokumen
                    $dokumen = TpuDokumen::find($existing_id);
                    if ($dokumen && $dokumen->uuid_modul == $uuid_modul) {
                        $updateData = [
                            'kategori'     => $kategori_uuid,
                            'nama_file'    => $nama_file,
                            'deskripsi'    => $deskripsi,
                            'uuid_updated' => $auth->uuid,
                        ];

                        // If new file uploaded, replace the old one
                        if ($file) {
                            // Delete old file
                            if ($dokumen->url && Storage::disk('public')->exists($dokumen->url)) {
                                Storage::disk('public')->delete($dokumen->url);
                            }

                            // Upload new file
                            $uploadResult = Helper::UpFileUnduhan($request, "dokumen_file.$index", "tpu/dokumen");
                            if ($uploadResult !== "0" && is_array($uploadResult)) {
                                $updateData['url']  = $uploadResult['url'];
                                $updateData['tipe'] = $uploadResult['tipe'];
                                $updateData['size'] = $uploadResult['size'];
                            }
                        }

                        $dokumen->update($updateData);
                    }
                } else {
                    // Create new dokumen
                    if ($file) {
                        $uploadResult = Helper::UpFileUnduhan($request, "dokumen_file.$index", "tpu/dokumen");
                        if ($uploadResult !== "0" && is_array($uploadResult)) {
                            TpuDokumen::create([
                                'uuid'         => Str::uuid(),
                                'uuid_modul'   => $uuid_modul,
                                'nama_modul'   => $nama_modul,
                                'kategori'     => $kategori_uuid,
                                'nama_file'    => $nama_file,
                                'deskripsi'    => $deskripsi,
                                'url'          => $uploadResult['url'],
                                'tipe'         => $uploadResult['tipe'],
                                'size'         => $uploadResult['size'],
                                'uuid_created' => $auth->uuid,
                                'uuid_updated' => $auth->uuid,
                            ]);
                        }
                    }
                }
            } catch (\Exception $e) {
                // Log error but don't stop the process
                \Log::error('Error processing dokumen upload: ' . $e->getMessage());
            }
        }
    }

    /**
     * Process deleted dokumen (optional method)
     */
    private function processDeletedDokumen($deletedIds, $uuid_modul)
    {
        if (! class_exists('App\Models\TpuDokumen') || empty($deletedIds)) {
            return;
        }

        try {
            $deletedIdsArray = explode(',', $deletedIds);
            foreach ($deletedIdsArray as $deletedId) {
                if (! empty($deletedId)) {
                    $dokumen = TpuDokumen::find($deletedId);
                    if ($dokumen && $dokumen->uuid_modul == $uuid_modul) {
                        // Delete file from storage
                        if ($dokumen->url && Storage::disk('public')->exists($dokumen->url)) {
                            Storage::disk('public')->delete($dokumen->url);
                        }
                        $dokumen->delete();
                    }
                }
            }
        } catch (\Exception $e) {
            // Log error but don't stop the process
            \Log::error('Error processing deleted dokumen: ' . $e->getMessage());
        }
    }
}