<?php
namespace App\Http\Controllers\web\backend\tpu;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\TpuDokumen;
use App\Models\TpuKategoriDokumen;
use App\Models\TpuLahan;
use App\Models\TpuRefJenisSarpras;
use App\Models\TpuSarpras;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class TpuSarprasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $auth = Auth::user();

        // Inisialisasi filter dari session atau default
        $filter_tpu           = $request->session()->get('filter_sarpras_tpu', 'Semua TPU');
        $filter_lahan         = $request->session()->get('filter_sarpras_lahan', 'Semua Lahan');
        $filter_jenis_sarpras = $request->session()->get('filter_sarpras_jenis', 'Semua Jenis Sarpras');

        if ($request->ajax()) {
            $query = TpuSarpras::query()->with(['Lahan.Tpu', 'JenisSarpras']);

            // Filter berdasarkan role
            if (in_array($auth->role, ['Admin TPU', 'Petugas TPU'])) {
                $query->whereHas('Lahan.Tpu', function ($q) use ($auth) {
                    $q->where('uuid', $auth->RelPetugasTpu->uuid_tpu);
                });
            }

            // Filter berdasarkan TPU
            if ($request->filled('filter.tpu') && $request->input('filter.tpu') !== 'Semua TPU') {
                $query->whereHas('Lahan.Tpu', function ($q) use ($request) {
                    $q->where('nama', $request->input('filter.tpu'));
                });
                $request->session()->put('filter_sarpras_tpu', $request->input('filter.tpu'));
            } elseif ($request->input('filter.tpu') === 'Semua TPU') {
                $request->session()->put('filter_sarpras_tpu', 'Semua TPU');
            }

            // Filter berdasarkan Lahan
            if ($request->filled('filter.lahan') && $request->input('filter.lahan') !== 'Semua Lahan') {
                $query->whereHas('Lahan', function ($q) use ($request) {
                    $q->where('kode_lahan', $request->input('filter.lahan'));
                });
                $request->session()->put('filter_sarpras_lahan', $request->input('filter.lahan'));
            } elseif ($request->input('filter.lahan') === 'Semua Lahan') {
                $request->session()->put('filter_sarpras_lahan', 'Semua Lahan');
            }

            // Filter berdasarkan Jenis Sarpras
            if ($request->filled('filter.jenis_sarpras') && $request->input('filter.jenis_sarpras') !== 'Semua Jenis Sarpras') {
                $query->where('jenis_sarpras', $request->input('filter.jenis_sarpras'));
                $request->session()->put('filter_sarpras_jenis', $request->input('filter.jenis_sarpras'));
            } elseif ($request->input('filter.jenis_sarpras') === 'Semua Jenis Sarpras') {
                $request->session()->put('filter_sarpras_jenis', 'Semua Jenis Sarpras');
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->setRowId('uuid')
                ->addColumn('nama', function ($data) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit_url = route('tpu.sarpras.edit', $uuid_enc);

                    // Count dokumen jika relasi tersedia (following Lahan pattern)
                    $dokumen_count = 0;
                    try {
                        if (method_exists($data, 'Dokumens')) {
                            $dokumen_count = $data->Dokumens->count();
                        }
                    } catch (\Exception $e) {
                        // Ignore error if relation doesn't exist yet
                    }

                    $dokumen_badge = $dokumen_count > 0 ?
                    '<span class="badge badge-light-info fs-8 mt-1"><i class="ki-outline ki-document fs-8 me-1"></i>' . $dokumen_count . ' Dokumen</span>' : '';

                    return '
                    <div class="d-flex align-items-center">
                        <div class="d-flex flex-column">
                            <a href="' . $edit_url . '" class="text-gray-800 text-hover-primary mb-1 fw-bold fs-6">' . $data->nama . '</a>
                            <span class="badge badge-light-info fw-bold fs-7 px-3 py-2">' . ($data->JenisSarpras ? $data->JenisSarpras->nama : '-') . '</span>
                            ' . $dokumen_badge . '
                        </div>
                    </div>
                ';
                })
                ->addColumn('nama_tpu', function ($data) {
                    return '<span class="badge badge-light-primary fw-bold fs-7 px-3 py-2">' . ($data->Lahan && $data->Lahan->Tpu ? $data->Lahan->Tpu->nama : '-') . '</span>';
                })
                ->addColumn('kode_lahan', function ($data) {
                    if ($data->Lahan) {
                        $jenis_tpu = $data->Lahan->Tpu ? $data->Lahan->Tpu->jenis_tpu : '';
                        switch ($jenis_tpu) {
                            case 'muslim':
                                $jenis_color = 'primary';
                                break;
                            case 'non_muslim':
                                $jenis_color = 'warning';
                                break;
                            case 'gabungan':
                                $jenis_color = 'success';
                                break;
                            default:
                                $jenis_color = 'secondary';
                        }

                        return '
                        <div class="d-flex flex-column">
                            <span class="text-gray-800 fw-bold fs-6">' . $data->Lahan->kode_lahan . '</span>
                            <span class="text-muted fw-semibold fs-7">' . ($data->Lahan->Tpu ? $data->Lahan->Tpu->nama : '-') . '</span>
                            <span class="badge badge-light-' . $jenis_color . ' fw-bold fs-8 mt-1">' . ucfirst(str_replace('_', ' ', $jenis_tpu)) . '</span>
                        </div>';
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('luas_m2', function ($data) {
                    return '<span class="text-gray-600 fw-semibold d-block fs-7">' . ($data->luas_m2 ? number_format($data->luas_m2, 2) . ' m²' : '-') . '</span>';
                })
                ->addColumn('actions', function ($data) use ($auth) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit_url = route('tpu.sarpras.edit', $uuid_enc);
                    $role     = $auth->role;

                    // Logika untuk aksi buttons - Petugas TPU sekarang bisa edit dan delete
                    $canEdit   = false;
                    $canDelete = false;

                    if ($role == 'Super Admin' || $role == 'Admin') {
                        $canEdit   = true;
                        $canDelete = true;
                    } elseif ($role == 'Admin TPU' || $role == 'Petugas TPU') {
                        // Admin TPU dan Petugas TPU bisa edit dan delete sarpras di TPU mereka
                        $isSameTPU = ($auth->RelPetugasTpu && $data->Lahan && $data->Lahan->Tpu && $auth->RelPetugasTpu->uuid_tpu === $data->Lahan->Tpu->uuid);
                        $canEdit   = $isSameTPU;
                        $canDelete = $isSameTPU;
                    }

                    $actions = '<div class="d-flex justify-content-center">';

                    // Edit button
                    if ($canEdit) {
                        $actions .= '<a href="' . $edit_url . '" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" data-bs-toggle="tooltip" title="Edit">
                                <i class="ki-outline ki-pencil fs-2"></i>
                            </a>';
                    } else {
                        $actions .= '<span class="btn btn-icon btn-bg-light btn-sm me-1 disabled" data-bs-toggle="tooltip" title="Edit (Tidak diizinkan)">
                                <i class="ki-outline ki-pencil fs-2 text-muted"></i>
                            </span>';
                    }

                    // Delete button
                    if ($canDelete) {
                        $actions .= '<a href="javascript:void(0);" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm btn-delete"
                               data-uuid="' . $uuid_enc . '"
                               data-name="' . htmlspecialchars($data->nama) . '"
                               data-bs-toggle="tooltip"
                               title="Hapus">
                                <i class="ki-outline ki-trash fs-2"></i>
                            </a>';
                    } else {
                        $actions .= '<span class="btn btn-icon btn-bg-light btn-sm disabled" data-bs-toggle="tooltip" title="Hapus (Tidak diizinkan)">
                                <i class="ki-outline ki-trash fs-2 text-muted"></i>
                            </span>';
                    }

                    $actions .= '</div>';

                    return $actions;
                })
                ->rawColumns(['nama', 'nama_tpu', 'kode_lahan', 'luas_m2', 'actions'])
                ->make(true);
        }

        // Mengambil data tpus dan jenis sarpras
        $tpus   = collect();
        $lahans = collect();

        if (in_array($auth->role, ['Admin TPU', 'Petugas TPU'])) {
            $tpus = TpuLahan::with('Tpu')
                ->whereHas('Tpu', function ($q) use ($auth) {
                    $q->where('uuid', $auth->RelPetugasTpu->uuid_tpu);
                })
                ->get()
                ->pluck('Tpu')
                ->unique();

            $lahans = TpuLahan::whereHas('Tpu', function ($q) use ($auth) {
                $q->where('uuid', $auth->RelPetugasTpu->uuid_tpu);
            })
                ->get(['uuid', 'kode_lahan']);
        } else {
            $tpus = TpuLahan::with('Tpu')->get()->pluck('Tpu')->unique();
            // Untuk Super Admin dan Admin, lahan akan diambil secara dinamis via AJAX
            $lahans = TpuLahan::get(['uuid', 'kode_lahan']);
        }

        $data = [
            'title'                => 'Data Sarpras',
            'tpus'                 => $tpus,
            'lahans'               => $lahans,
            'jenis_sarpras'        => TpuRefJenisSarpras::where('status', '1')->orderBy('nama', 'ASC')->get(),
            'user_role'            => $auth->role,
            'hide_tpu_filter'      => in_array($auth->role, ['Admin TPU', 'Petugas TPU']),
            'filter_tpu'           => $filter_tpu,
            'filter_lahan'         => $filter_lahan,
            'filter_jenis_sarpras' => $filter_jenis_sarpras,
        ];

        return view('admin.tpu.sarpras.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $auth = Auth::user();

        $lahans = TpuLahan::with('Tpu');

        if ($auth->role === 'Admin TPU' || $auth->role === 'Petugas TPU') {
            $lahans->whereHas('Tpu', function ($q) use ($auth) {
                $q->where('uuid', $auth->RelPetugasTpu->uuid_tpu);
            });
        }

                                      // Get kategori dokumen untuk Sarpras (optional) - following Lahan pattern
        $kategoriDokumen = collect(); // Default empty collection
        try {
            $kategoriDokumen = TpuKategoriDokumen::where('status', '1')
                ->where('tipe', 'dokumen-sarpras')
                ->orderBy('nama', 'ASC')
                ->get();
        } catch (\Exception $e) {
            // Ignore if table doesn't exist yet
        }

        $data = [
            'title'           => 'Tambah Data Sarpras',
            'submit'          => 'Simpan',
            'lahans'          => $lahans->get(),
            'jenis_sarpras'   => TpuRefJenisSarpras::where('status', '1')->orderBy('nama', 'ASC')->get(),
            'kategoriDokumen' => $kategoriDokumen,
        ];

        return view('admin.tpu.sarpras.create_edit', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $auth = Auth::user();

        $request->validate([
            'uuid_lahan'    => 'required|exists:tpu_lahans,uuid',
            'nama'          => 'required|string|max:255',
            'jenis_sarpras' => 'nullable|exists:tpu_ref_jenis_sarpras,nama',
            'luas_m2'       => 'nullable|numeric|min:0',
            'deskripsi'     => 'nullable|string',
        ], [
            'uuid_lahan.required'  => 'Lahan harus dipilih',
            'uuid_lahan.exists'    => 'Lahan yang dipilih tidak valid',
            'nama.required'        => 'Nama sarpras harus diisi',
            'nama.unique'          => 'Nama sarpras sudah digunakan',
            'jenis_sarpras.exists' => 'Jenis sarpras yang dipilih tidak valid',
            'luas_m2.numeric'      => 'Luas harus berupa angka',
            'luas_m2.min'          => 'Luas tidak boleh kurang dari 0',
        ]);

        // Validate dokumen if any (optional) - following Lahan pattern
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

        try {
            DB::beginTransaction();

            // Check permission for lahan - Admin TPU dan Petugas TPU
            $lahan = TpuLahan::with('Tpu')->findOrFail($request->uuid_lahan);
            if (($auth->role === 'Admin TPU' || $auth->role === 'Petugas TPU') && $lahan->Tpu && $lahan->Tpu->uuid !== $auth->RelPetugasTpu->uuid_tpu) {
                alert()->error('Error', 'Anda tidak memiliki izin untuk menambahkan sarpras pada lahan ini.');
                return back()->withInput();
            }

            $uuid  = Str::uuid();
            $value = [
                'uuid'          => $uuid,
                'uuid_lahan'    => $request->uuid_lahan,
                'nama'          => $request->nama,
                'jenis_sarpras' => $request->jenis_sarpras,
                'luas_m2'       => $request->luas_m2,
                'deskripsi'     => $request->deskripsi,
                'uuid_created'  => $auth->uuid,
                'uuid_updated'  => $auth->uuid,
            ];

            $save = TpuSarpras::create($value);

            if ($save) {
                // Process dokumen uploads if any (optional) - following Lahan pattern
                if ($request->has('dokumen_kategori')) {
                    $this->processDokumenUploads($request, $uuid, 'Sarpras');
                }

                // Create log
                $aktifitas = [
                    'tabel' => ['tpu_sarpras'],
                    'uuid'  => [$uuid],
                    'value' => [$value],
                ];
                $log = [
                    'apps'      => 'TPU Admin',
                    'subjek'    => 'Menambahkan Data Sarpras: ' . $request->nama . ' - ' . $uuid,
                    'aktifitas' => $aktifitas,
                    'device'    => 'web',
                ];
                Helper::addToLogAktifitas($request, $log);

                DB::commit();
                alert()->success('Success', 'Berhasil Menambahkan Data Sarpras!');
                return redirect()->route('tpu.sarpras.index');
            } else {
                DB::rollback();
                alert()->error('Error', 'Gagal Menambahkan Data Sarpras!');
                return back()->withInput();
            }
        } catch (\Exception $e) {
            DB::rollback();
            alert()->error('Error', 'Terjadi kesalahan: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($uuid_enc)
    {
        $auth = Auth::user();

        $uuid_dec = Helper::decode($uuid_enc);
        $data     = TpuSarpras::with(['Lahan.Tpu'])->findOrFail($uuid_dec);

        // Check permission untuk Admin TPU dan Petugas TPU
        if (($auth->role === 'Admin TPU' || $auth->role === 'Petugas TPU') && $data->Lahan && $data->Lahan->Tpu && $data->Lahan->Tpu->uuid !== $auth->RelPetugasTpu->uuid_tpu) {
            alert()->error('Error', 'Anda tidak memiliki izin untuk mengedit sarpras ini.');
            return redirect()->route('tpu.sarpras.index');
        }

        $lahans = TpuLahan::with('Tpu');

        if ($auth->role === 'Admin TPU' || $auth->role === 'Petugas TPU') {
            $lahans->whereHas('Tpu', function ($q) use ($auth) {
                $q->where('uuid', $auth->RelPetugasTpu->uuid_tpu);
            });
        }

        // Get kategori dokumen untuk Sarpras (optional) - following Lahan pattern
        $kategoriDokumen = collect();
        $existingDokumen = collect();
        try {
            $kategoriDokumen = TpuKategoriDokumen::where('status', '1')
                ->where('tipe', 'dokumen-sarpras')
                ->orderBy('nama', 'ASC')
                ->get();

            // Get existing dokumen
            if (method_exists($data, 'Dokumens')) {
                $existingDokumen = $data->Dokumens;
            }
        } catch (\Exception $e) {
            // Ignore if table/relation doesn't exist yet
        }

        $view_data = [
            'title'           => 'Edit Data Sarpras',
            'submit'          => 'Simpan',
            'data'            => $data,
            'uuid_enc'        => $uuid_enc,
            'lahans'          => $lahans->get(),
            'jenis_sarpras'   => TpuRefJenisSarpras::where('status', '1')->orderBy('nama', 'ASC')->get(),
            'kategoriDokumen' => $kategoriDokumen,
            'existingDokumen' => $existingDokumen,
        ];

        return view('admin.tpu.sarpras.create_edit', $view_data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $uuid_enc)
    {
        $auth = Auth::user();

        $uuid_dec = Helper::decode($uuid_enc);
        $data     = TpuSarpras::with(['Lahan.Tpu'])->findOrFail($uuid_dec);

        // Check permission untuk Admin TPU dan Petugas TPU
        if (($auth->role === 'Admin TPU' || $auth->role === 'Petugas TPU') && $data->Lahan && $data->Lahan->Tpu && $data->Lahan->Tpu->uuid !== $auth->RelPetugasTpu->uuid_tpu) {
            alert()->error('Error', 'Anda tidak memiliki izin untuk mengedit sarpras ini.');
            return redirect()->route('tpu.sarpras.index');
        }

        $request->validate([
            'uuid_lahan'    => 'required|exists:tpu_lahans,uuid',
            'nama'          => [
                'required',
                'string',
                'max:255',
            ],
            'jenis_sarpras' => 'nullable|exists:tpu_ref_jenis_sarpras,nama',
            'luas_m2'       => 'nullable|numeric|min:0',
            'deskripsi'     => 'nullable|string',
        ], [
            'uuid_lahan.required'  => 'Lahan harus dipilih',
            'uuid_lahan.exists'    => 'Lahan yang dipilih tidak valid',
            'nama.required'        => 'Nama sarpras harus diisi',
            'nama.unique'          => 'Nama sarpras sudah digunakan',
            'jenis_sarpras.exists' => 'Jenis sarpras yang dipilih tidak valid',
            'luas_m2.numeric'      => 'Luas harus berupa angka',
            'luas_m2.min'          => 'Luas tidak boleh kurang dari 0',
        ]);

        // Validate dokumen if any (optional) - following Lahan pattern
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

        try {
            DB::beginTransaction();

            // Check permission for new lahan if changed
            if ($request->uuid_lahan !== $data->uuid_lahan) {
                $lahan = TpuLahan::with('Tpu')->findOrFail($request->uuid_lahan);
                if (($auth->role === 'Admin TPU' || $auth->role === 'Petugas TPU') && $lahan->Tpu && $lahan->Tpu->uuid !== $auth->RelPetugasTpu->uuid_tpu) {
                    alert()->error('Error', 'Anda tidak memiliki izin untuk memindahkan sarpras ke lahan ini.');
                    return back()->withInput();
                }
            }

            $value = [
                'uuid_lahan'    => $request->uuid_lahan,
                'nama'          => $request->nama,
                'jenis_sarpras' => $request->jenis_sarpras,
                'luas_m2'       => $request->luas_m2,
                'deskripsi'     => $request->deskripsi,
                'uuid_updated'  => $auth->uuid,
            ];

            $save = $data->update($value);

            if ($save) {
                // Process dokumen updates/uploads (optional) - following Lahan pattern
                if ($request->has('dokumen_kategori')) {
                    $this->processDokumenUploads($request, $uuid_dec, 'Sarpras');
                }

                // Process dokumen deletions (optional) - following Lahan pattern
                if ($request->has('deleted_dokumen_ids')) {
                    $this->processDeletedDokumen($request->deleted_dokumen_ids, $uuid_dec);
                }

                // Create log
                $aktifitas = [
                    'tabel' => ['tpu_sarpras'],
                    'uuid'  => [$uuid_dec],
                    'value' => [$value],
                ];
                $log = [
                    'apps'      => 'TPU Admin',
                    'subjek'    => 'Mengubah Data Sarpras: ' . $request->nama . ' - ' . $uuid_dec,
                    'aktifitas' => $aktifitas,
                    'device'    => 'web',
                ];
                Helper::addToLogAktifitas($request, $log);

                DB::commit();
                alert()->success('Success', 'Berhasil Mengubah Data Sarpras!');
                return redirect()->route('tpu.sarpras.index');
            } else {
                DB::rollback();
                alert()->error('Error', 'Gagal Mengubah Data Sarpras!');
                return back()->withInput();
            }
        } catch (\Exception $e) {
            DB::rollback();
            alert()->error('Error', 'Terjadi kesalahan: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $auth = Auth::user();

        $request->validate([
            'uuid' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $uuid_dec = Helper::decode($request->uuid);
            $data     = TpuSarpras::with(['Lahan.Tpu'])->findOrFail($uuid_dec);

            // Check permission untuk Admin TPU dan Petugas TPU
            if (($auth->role === 'Admin TPU' || $auth->role === 'Petugas TPU') && $data->Lahan && $data->Lahan->Tpu && $data->Lahan->Tpu->uuid !== $auth->RelPetugasTpu->uuid_tpu) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Anda tidak memiliki izin untuk menghapus sarpras ini.',
                ], 403);
            }

            // Delete associated dokumen files (optional) - following Lahan pattern
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

            $value = $data->toArray();
            $save  = $data->delete();

            if ($save) {
                // Create log
                $aktifitas = [
                    'tabel' => ['tpu_sarpras'],
                    'uuid'  => [$uuid_dec],
                    'value' => [$value],
                ];
                $log = [
                    'apps'      => 'TPU Admin',
                    'subjek'    => 'Menghapus Data Sarpras: ' . $data->nama . ' - ' . $uuid_dec,
                    'aktifitas' => $aktifitas,
                    'device'    => 'web',
                ];
                Helper::addToLogAktifitas($request, $log);

                DB::commit();
                return response()->json([
                    'status'  => true,
                    'message' => 'Data Sarpras Berhasil Dihapus!',
                ], 200);
            } else {
                DB::rollback();
                return response()->json([
                    'status'  => false,
                    'message' => 'Data Sarpras Gagal Dihapus!',
                ], 422);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove multiple resources from storage.
     */
    public function bulkDestroy(Request $request)
    {
        $auth = Auth::user();

        $request->validate([
            'uuids'   => 'required|array|min:1',
            'uuids.*' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $deletedCount = 0;
            $failedItems  = [];

            foreach ($request->uuids as $uuid) {
                try {
                    $data = TpuSarpras::with(['Lahan.Tpu'])->findOrFail($uuid);

                    // Check permission untuk Admin TPU dan Petugas TPU
                    if (($auth->role === 'Admin TPU' || $auth->role === 'Petugas TPU') && $data->Lahan && $data->Lahan->Tpu && $data->Lahan->Tpu->uuid !== $auth->RelPetugasTpu->uuid_tpu) {
                        $failedItems[] = 'Tidak memiliki izin untuk menghapus: ' . $data->nama;
                        continue;
                    }

                    // Delete associated dokumen files (optional) - following Lahan pattern
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

                    $value = $data->toArray();
                    if ($data->delete()) {
                        $deletedCount++;

                        // Create log
                        $aktifitas = [
                            'tabel' => ['tpu_sarpras'],
                            'uuid'  => [$uuid],
                            'value' => [$value],
                        ];
                        $log = [
                            'apps'      => 'TPU Admin',
                            'subjek'    => 'Menghapus Data Sarpras (Bulk): ' . $data->nama . ' - ' . $uuid,
                            'aktifitas' => $aktifitas,
                            'device'    => 'web',
                        ];
                        Helper::addToLogAktifitas($request, $log);
                    } else {
                        $failedItems[] = 'Gagal menghapus: ' . $data->nama;
                    }
                } catch (\Exception $e) {
                    $failedItems[] = 'Error pada ID ' . $uuid . ': ' . $e->getMessage();
                    continue;
                }
            }

            $message = 'Berhasil menghapus ' . $deletedCount . ' sarpras';
            if (! empty($failedItems)) {
                $message .= '. Gagal menghapus ' . count($failedItems) . ' item';
            }

            // Create summary log
            $summaryLog = [
                'apps'      => 'TPU Admin',
                'subjek'    => 'Bulk Delete Data Sarpras - Berhasil: ' . $deletedCount . ', Gagal: ' . count($failedItems),
                'aktifitas' => [
                    'tabel'         => ['tpu_sarpras'],
                    'total_request' => count($request->uuids),
                    'total_deleted' => $deletedCount,
                    'total_failed'  => count($failedItems),
                    'failed_items'  => $failedItems,
                ],
                'device'    => 'web',
            ];
            Helper::addToLogAktifitas($request, $summaryLog);

            DB::commit();
            return response()->json([
                'status'        => true,
                'message'       => $message,
                'deleted_count' => $deletedCount,
                'failed_count'  => count($failedItems),
                'failed_items'  => $failedItems,
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    // getLahansByTpu
    public function getLahansByTpu(Request $request)
    {
        $auth     = Auth::user();
        $tpu_name = $request->query('tpu', 'Semua TPU');

        $query = TpuLahan::query()->select('uuid', 'kode_lahan');

        if (in_array($auth->role, ['Admin TPU', 'Petugas TPU'])) {
            $query->whereHas('Tpu', function ($q) use ($auth) {
                $q->where('uuid', $auth->RelPetugasTpu->uuid_tpu);
            });
        } elseif ($tpu_name !== 'Semua TPU') {
            $query->whereHas('Tpu', function ($q) use ($tpu_name) {
                $q->where('nama', $tpu_name);
            });
        }

        $lahans = $query->get();

        return response()->json([
            'status' => true,
            'data'   => $lahans,
        ]);
    }

    /**
     * Process dokumen uploads (optional method) - following Lahan pattern exactly
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

                            // Upload new file - using sarpras specific path
                            $uploadResult = Helper::UpFileUnduhan($request, "dokumen_file.$index", "tpu/sarpras/dokumen");
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
                        $uploadResult = Helper::UpFileUnduhan($request, "dokumen_file.$index", "tpu/sarpras/dokumen");
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
                \Log::error('Error processing dokumen upload for sarpras: ' . $e->getMessage());
            }
        }
    }

    /**
     * Process deleted dokumen (optional method) - following Lahan pattern exactly
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
            \Log::error('Error processing deleted dokumen for sarpras: ' . $e->getMessage());
        }
    }
}
