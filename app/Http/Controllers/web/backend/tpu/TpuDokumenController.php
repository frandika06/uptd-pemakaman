<?php
namespace App\Http\Controllers\web\backend\tpu;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\TpuDatas;
use App\Models\TpuDokumen;
use App\Models\TpuKategoriDokumen;
use App\Models\TpuLahan;
use App\Models\TpuSarpras;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class TpuDokumenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $nama_modul)
    {
        // Validasi nama_modul
        if (! in_array($nama_modul, ['TPU', 'Lahan', 'Sarpras'])) {
            alert()->error('Error', 'Modul tidak valid.');
            return redirect()->route('tpu.dashboard.index');
        }

        $auth = Auth::user();

        if ($request->ajax()) {
            $data = TpuDokumen::with(['Kategori'])
                ->where('nama_modul', $nama_modul);

            // Filter berdasarkan permission Admin TPU
            if ($auth->role === 'Admin TPU') {
                if ($nama_modul === 'TPU') {
                    $data->whereHas('Tpu', function ($q) use ($auth) {
                        $q->where('uuid', $auth->RelPetugasTpu->uuid_tpu);
                    });
                } elseif ($nama_modul === 'Lahan') {
                    $data->whereHas('Lahan.Tpu', function ($q) use ($auth) {
                        $q->where('uuid', $auth->RelPetugasTpu->uuid_tpu);
                    });
                } elseif ($nama_modul === 'Sarpras') {
                    $data->whereHas('Sarpras.Lahan.Tpu', function ($q) use ($auth) {
                        $q->where('uuid', $auth->RelPetugasTpu->uuid_tpu);
                    });
                }
            }

            $data = $data->orderBy('created_at', 'DESC')->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->setRowId('uuid')
                ->addColumn('checkbox', function ($data) {
                    return '<input type="checkbox" class="form-check-input bulk-select" value="' . $data->uuid . '">';
                })
                ->addColumn('nama_file', function ($data) use ($nama_modul) {
                    $uuid_enc     = Helper::encode($data->uuid);
                    $edit_url     = route('tpu.dokumen.edit', [$nama_modul, $uuid_enc]);
                    $download_url = route('tpu.dokumen.download', $uuid_enc);

                    return '
                        <div class="d-flex align-items-center">
                            <div class="d-flex flex-column">
                                <a href="' . $edit_url . '" class="text-gray-800 text-hover-primary mb-1 fw-bold fs-6">' . $data->nama_file . '</a>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge badge-light-info fs-7">' . strtoupper($data->tipe) . '</span>
                                    <span class="text-muted fs-7">' . Helper::formatFileSize($data->size) . '</span>
                                    <a href="' . $download_url . '" class="text-primary fs-7" title="Download">
                                        <i class="ki-outline ki-cloud-download fs-6"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    ';
                })
                ->addColumn('kategori', function ($data) {
                    return $data->Kategori ? $data->Kategori->nama : '-';
                })
                ->addColumn('modul_data', function ($data) use ($nama_modul) {
                    if ($nama_modul === 'TPU' && $data->Tpu) {
                        return $data->Tpu->nama;
                    } elseif ($nama_modul === 'Lahan' && $data->Lahan) {
                        return $data->Lahan->nama . ' (' . $data->Lahan->Tpu->nama . ')';
                    } elseif ($nama_modul === 'Sarpras' && $data->Sarpras) {
                        return $data->Sarpras->nama . ' (' . $data->Sarpras->Lahan->nama . ')';
                    }
                    return '-';
                })
                ->addColumn('deskripsi', function ($data) {
                    return $data->deskripsi ? Str::limit($data->deskripsi, 50) : '-';
                })
                ->addColumn('actions', function ($data) use ($auth, $nama_modul) {
                    $uuid_enc     = Helper::encode($data->uuid);
                    $edit_url     = route('tpu.dokumen.edit', [$nama_modul, $uuid_enc]);
                    $download_url = route('tpu.dokumen.download', $uuid_enc);

                    $actions = '
                        <div class="d-flex align-items-center gap-2">
                            <a href="' . $download_url . '" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary" title="Download">
                                <i class="ki-outline ki-cloud-download fs-2"></i>
                            </a>
                    ';

                    // Edit permission
                    if ($auth->role !== 'Petugas TPU') {
                        $actions .= '
                            <a href="' . $edit_url . '" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary" title="Edit">
                                <i class="ki-outline ki-pencil fs-2"></i>
                            </a>
                        ';
                    }

                    // Delete permission
                    if ($auth->role !== 'Petugas TPU') {
                        $actions .= '
                            <button type="button" class="btn btn-sm btn-icon btn-bg-light btn-active-color-danger btn-delete"
                                data-uuid="' . $uuid_enc . '" data-name="' . $data->nama_file . '" title="Hapus">
                                <i class="ki-outline ki-trash fs-2"></i>
                            </button>
                        ';
                    }

                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['checkbox', 'nama_file', 'modul_data', 'actions'])
                ->make(true);
        }

        $title = 'Data Dokumen ' . $nama_modul;

        return view('admin.tpu.dokumen.index', compact('title', 'nama_modul'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($nama_modul)
    {
        // Validasi nama_modul
        if (! in_array($nama_modul, ['TPU', 'Lahan', 'Sarpras'])) {
            alert()->error('Error', 'Modul tidak valid.');
            return redirect()->route('tpu.dashboard.index');
        }

        $auth = Auth::user();

        // Petugas TPU tidak bisa create
        if ($auth->role === 'Petugas TPU') {
            alert()->error('Error', 'Unauthorized action.');
            return redirect()->route('tpu.dokumen.index', $nama_modul);
        }

        // Get data untuk dropdown
        $kategoris = TpuKategoriDokumen::where('status', '1')->orderBy('nama', 'ASC')->get();

        $moduls = collect();
        if ($nama_modul === 'TPU') {
            $moduls = TpuDatas::where('status', 'Aktif');
            if ($auth->role === 'Admin TPU') {
                $moduls->where('uuid', $auth->RelPetugasTpu->uuid_tpu);
            }
        } elseif ($nama_modul === 'Lahan') {
            $moduls = TpuLahan::with(['Tpu']);
            if ($auth->role === 'Admin TPU') {
                $moduls->whereHas('Tpu', function ($q) use ($auth) {
                    $q->where('uuid', $auth->RelPetugasTpu->uuid_tpu);
                });
            }
        } elseif ($nama_modul === 'Sarpras') {
            $moduls = TpuSarpras::with(['Lahan.Tpu']);
            if ($auth->role === 'Admin TPU') {
                $moduls->whereHas('Lahan.Tpu', function ($q) use ($auth) {
                    $q->where('uuid', $auth->RelPetugasTpu->uuid_tpu);
                });
            }
        }

        $view_data = [
            'title'      => 'Tambah Dokumen ' . $nama_modul,
            'submit'     => 'Simpan',
            'nama_modul' => $nama_modul,
            'kategoris'  => $kategoris,
            'moduls'     => $moduls->get(),
        ];

        return view('admin.tpu.dokumen.create_edit', $view_data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $nama_modul)
    {
        // Validasi nama_modul
        if (! in_array($nama_modul, ['TPU', 'Lahan', 'Sarpras'])) {
            alert()->error('Error', 'Modul tidak valid.');
            return redirect()->route('tpu.dashboard.index');
        }

        $auth = Auth::user();

        // Petugas TPU tidak bisa create
        if ($auth->role === 'Petugas TPU') {
            alert()->error('Error', 'Unauthorized action.');
            return redirect()->route('tpu.dokumen.index', $nama_modul);
        }

        // Validation
        $request->validate([
            'uuid_modul' => 'required|string',
            'kategori'   => 'required|string',
            'nama_file'  => 'required|string|max:255',
            'deskripsi'  => 'nullable|string',
            'file'       => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif',
        ]);

        try {
            DB::beginTransaction();

            $uuid_modul_dec = Helper::decode($request->uuid_modul);
            $kategori_dec   = Helper::decode($request->kategori);

            // Verify permission untuk Admin TPU
            if ($auth->role === 'Admin TPU') {
                if ($nama_modul === 'TPU') {
                    $tpu = TpuDatas::findOrFail($uuid_modul_dec);
                    if ($tpu->uuid !== $auth->RelPetugasTpu->uuid_tpu) {
                        alert()->error('Error', 'Unauthorized action.');
                        return redirect()->route('tpu.dokumen.index', $nama_modul);
                    }
                } elseif ($nama_modul === 'Lahan') {
                    $lahan = TpuLahan::with(['Tpu'])->findOrFail($uuid_modul_dec);
                    if ($lahan->Tpu->uuid !== $auth->RelPetugasTpu->uuid_tpu) {
                        alert()->error('Error', 'Unauthorized action.');
                        return redirect()->route('tpu.dokumen.index', $nama_modul);
                    }
                } elseif ($nama_modul === 'Sarpras') {
                    $sarpras = TpuSarpras::with(['Lahan.Tpu'])->findOrFail($uuid_modul_dec);
                    if ($sarpras->Lahan->Tpu->uuid !== $auth->RelPetugasTpu->uuid_tpu) {
                        alert()->error('Error', 'Unauthorized action.');
                        return redirect()->route('tpu.dokumen.index', $nama_modul);
                    }
                }
            }

            // Handle file upload
            $file     = $request->file('file');
            $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $filePath = 'uploads/tpu/dokumen/' . strtolower($nama_modul) . '/' . $fileName;

            Storage::disk('public')->putFileAs('uploads/tpu/dokumen/' . strtolower($nama_modul), $file, $fileName);

            // Create document record
            $uuid  = Str::uuid();
            $value = [
                'uuid'         => $uuid,
                'uuid_modul'   => $uuid_modul_dec,
                'nama_modul'   => $nama_modul,
                'kategori'     => $kategori_dec,
                'nama_file'    => $request->nama_file,
                'deskripsi'    => $request->deskripsi,
                'url'          => $filePath,
                'tipe'         => $file->getClientOriginalExtension(),
                'size'         => $file->getSize(),
                'uuid_created' => $auth->uuid,
                'uuid_updated' => $auth->uuid,
            ];

            $save = TpuDokumen::create($value);

            if ($save) {
                // Create log
                $aktifitas = [
                    'tabel' => ['tpu_dokumens'],
                    'uuid'  => [$uuid],
                    'value' => [$value],
                ];
                $log = [
                    'apps'      => 'TPU Admin',
                    'subjek'    => 'Menambah Dokumen ' . $nama_modul . ': ' . $request->nama_file . ' - ' . $uuid,
                    'aktifitas' => $aktifitas,
                    'device'    => 'web',
                ];
                Helper::addToLogAktifitas($request, $log);

                DB::commit();
                alert()->success('Success', 'Berhasil Menambah Dokumen ' . $nama_modul . '!');
                return redirect()->route('tpu.dokumen.index', $nama_modul);
            } else {
                // Delete uploaded file if database save failed
                Storage::disk('public')->delete($filePath);
                DB::rollback();
                alert()->error('Error', 'Gagal Menambah Dokumen ' . $nama_modul . '!');
                return back()->withInput();
            }
        } catch (Exception $e) {
            DB::rollback();
            alert()->error('Error', 'Terjadi kesalahan: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($nama_modul, $uuid_enc)
    {
        // Validasi nama_modul
        if (! in_array($nama_modul, ['TPU', 'Lahan', 'Sarpras'])) {
            alert()->error('Error', 'Modul tidak valid.');
            return redirect()->route('tpu.dashboard.index');
        }

        $auth = Auth::user();

        // Petugas TPU tidak bisa edit
        if ($auth->role === 'Petugas TPU') {
            alert()->error('Error', 'Unauthorized action.');
            return redirect()->route('tpu.dokumen.index', $nama_modul);
        }

        $uuid_dec = Helper::decode($uuid_enc);
        $data     = TpuDokumen::with(['Kategori', 'Tpu', 'Lahan.Tpu', 'Sarpras.Lahan.Tpu'])->findOrFail($uuid_dec);

        // Verify permission untuk Admin TPU
        if ($auth->role === 'Admin TPU') {
            if ($nama_modul === 'TPU' && $data->Tpu && $data->Tpu->uuid !== $auth->RelPetugasTpu->uuid_tpu) {
                alert()->error('Error', 'Unauthorized action.');
                return redirect()->route('tpu.dokumen.index', $nama_modul);
            } elseif ($nama_modul === 'Lahan' && $data->Lahan && $data->Lahan->Tpu && $data->Lahan->Tpu->uuid !== $auth->RelPetugasTpu->uuid_tpu) {
                alert()->error('Error', 'Unauthorized action.');
                return redirect()->route('tpu.dokumen.index', $nama_modul);
            } elseif ($nama_modul === 'Sarpras' && $data->Sarpras && $data->Sarpras->Lahan && $data->Sarpras->Lahan->Tpu && $data->Sarpras->Lahan->Tpu->uuid !== $auth->RelPetugasTpu->uuid_tpu) {
                alert()->error('Error', 'Unauthorized action.');
                return redirect()->route('tpu.dokumen.index', $nama_modul);
            }
        }

        // Get data untuk dropdown
        $kategoris = TpuKategoriDokumen::where('status', '1')->orderBy('nama', 'ASC')->get();

        $moduls = collect();
        if ($nama_modul === 'TPU') {
            $moduls = TpuDatas::where('status', 'Aktif');
            if ($auth->role === 'Admin TPU') {
                $moduls->where('uuid', $auth->RelPetugasTpu->uuid_tpu);
            }
        } elseif ($nama_modul === 'Lahan') {
            $moduls = TpuLahan::with(['Tpu']);
            if ($auth->role === 'Admin TPU') {
                $moduls->whereHas('Tpu', function ($q) use ($auth) {
                    $q->where('uuid', $auth->RelPetugasTpu->uuid_tpu);
                });
            }
        } elseif ($nama_modul === 'Sarpras') {
            $moduls = TpuSarpras::with(['Lahan.Tpu']);
            if ($auth->role === 'Admin TPU') {
                $moduls->whereHas('Lahan.Tpu', function ($q) use ($auth) {
                    $q->where('uuid', $auth->RelPetugasTpu->uuid_tpu);
                });
            }
        }

        $view_data = [
            'title'      => 'Edit Dokumen ' . $nama_modul,
            'submit'     => 'Simpan',
            'data'       => $data,
            'uuid_enc'   => $uuid_enc,
            'nama_modul' => $nama_modul,
            'kategoris'  => $kategoris,
            'moduls'     => $moduls->get(),
        ];

        return view('admin.tpu.dokumen.create_edit', $view_data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $nama_modul, $uuid_enc)
    {
        // Validasi nama_modul
        if (! in_array($nama_modul, ['TPU', 'Lahan', 'Sarpras'])) {
            alert()->error('Error', 'Modul tidak valid.');
            return redirect()->route('tpu.dashboard.index');
        }

        $auth = Auth::user();

        // Petugas TPU tidak bisa update
        if ($auth->role === 'Petugas TPU') {
            alert()->error('Error', 'Unauthorized action.');
            return redirect()->route('tpu.dokumen.index', $nama_modul);
        }

        $uuid_dec = Helper::decode($uuid_enc);
        $data     = TpuDokumen::with(['Tpu', 'Lahan.Tpu', 'Sarpras.Lahan.Tpu'])->findOrFail($uuid_dec);

        // Verify permission untuk Admin TPU
        if ($auth->role === 'Admin TPU') {
            if ($nama_modul === 'TPU' && $data->Tpu && $data->Tpu->uuid !== $auth->RelPetugasTpu->uuid_tpu) {
                alert()->error('Error', 'Unauthorized action.');
                return redirect()->route('tpu.dokumen.index', $nama_modul);
            } elseif ($nama_modul === 'Lahan' && $data->Lahan && $data->Lahan->Tpu && $data->Lahan->Tpu->uuid !== $auth->RelPetugasTpu->uuid_tpu) {
                alert()->error('Error', 'Unauthorized action.');
                return redirect()->route('tpu.dokumen.index', $nama_modul);
            } elseif ($nama_modul === 'Sarpras' && $data->Sarpras && $data->Sarpras->Lahan && $data->Sarpras->Lahan->Tpu && $data->Sarpras->Lahan->Tpu->uuid !== $auth->RelPetugasTpu->uuid_tpu) {
                alert()->error('Error', 'Unauthorized action.');
                return redirect()->route('tpu.dokumen.index', $nama_modul);
            }
        }

        // Validation
        $request->validate([
            'uuid_modul' => 'required|string',
            'kategori'   => 'required|string',
            'nama_file'  => 'required|string|max:255',
            'deskripsi'  => 'nullable|string',
            'file'       => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif',
        ]);

        try {
            DB::beginTransaction();

            $uuid_modul_dec = Helper::decode($request->uuid_modul);
            $kategori_dec   = Helper::decode($request->kategori);

            // Verify permission untuk modul yang dipilih (jika Admin TPU)
            if ($auth->role === 'Admin TPU') {
                if ($nama_modul === 'TPU') {
                    $tpu = TpuDatas::findOrFail($uuid_modul_dec);
                    if ($tpu->uuid !== $auth->RelPetugasTpu->uuid_tpu) {
                        alert()->error('Error', 'Unauthorized action.');
                        return redirect()->route('tpu.dokumen.index', $nama_modul);
                    }
                } elseif ($nama_modul === 'Lahan') {
                    $lahan = TpuLahan::with(['Tpu'])->findOrFail($uuid_modul_dec);
                    if ($lahan->Tpu->uuid !== $auth->RelPetugasTpu->uuid_tpu) {
                        alert()->error('Error', 'Unauthorized action.');
                        return redirect()->route('tpu.dokumen.index', $nama_modul);
                    }
                } elseif ($nama_modul === 'Sarpras') {
                    $sarpras = TpuSarpras::with(['Lahan.Tpu'])->findOrFail($uuid_modul_dec);
                    if ($sarpras->Lahan->Tpu->uuid !== $auth->RelPetugasTpu->uuid_tpu) {
                        alert()->error('Error', 'Unauthorized action.');
                        return redirect()->route('tpu.dokumen.index', $nama_modul);
                    }
                }
            }

            $value = [
                'uuid_modul'   => $uuid_modul_dec,
                'kategori'     => $kategori_dec,
                'nama_file'    => $request->nama_file,
                'deskripsi'    => $request->deskripsi,
                'uuid_updated' => $auth->uuid,
            ];

            // Handle file upload if new file provided
            if ($request->hasFile('file')) {
                $file     = $request->file('file');
                $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $filePath = 'uploads/tpu/dokumen/' . strtolower($nama_modul) . '/' . $fileName;

                // Delete old file
                if ($data->url && Storage::disk('public')->exists($data->url)) {
                    Storage::disk('public')->delete($data->url);
                }

                // Upload new file
                Storage::disk('public')->putFileAs('uploads/tpu/dokumen/' . strtolower($nama_modul), $file, $fileName);

                $value['url']  = $filePath;
                $value['tipe'] = $file->getClientOriginalExtension();
                $value['size'] = $file->getSize();
            }

            $save = $data->update($value);

            if ($save) {
                // Create log
                $aktifitas = [
                    'tabel' => ['tpu_dokumens'],
                    'uuid'  => [$uuid_dec],
                    'value' => [$value],
                ];
                $log = [
                    'apps'      => 'TPU Admin',
                    'subjek'    => 'Mengubah Dokumen ' . $nama_modul . ': ' . $request->nama_file . ' - ' . $uuid_dec,
                    'aktifitas' => $aktifitas,
                    'device'    => 'web',
                ];
                Helper::addToLogAktifitas($request, $log);

                DB::commit();
                alert()->success('Success', 'Berhasil Mengubah Dokumen ' . $nama_modul . '!');
                return redirect()->route('tpu.dokumen.index', $nama_modul);
            } else {
                DB::rollback();
                alert()->error('Error', 'Gagal Mengubah Dokumen ' . $nama_modul . '!');
                return back()->withInput();
            }
        } catch (Exception $e) {
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

        // Petugas TPU tidak bisa delete
        if ($auth->role === 'Petugas TPU') {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        $request->validate([
            'uuid' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $uuid_dec = Helper::decode($request->uuid);
            $data     = TpuDokumen::with(['Tpu', 'Lahan.Tpu', 'Sarpras.Lahan.Tpu'])->findOrFail($uuid_dec);

            // Verify permission untuk Admin TPU
            if ($auth->role === 'Admin TPU') {
                $hasPermission = false;
                if ($data->nama_modul === 'TPU' && $data->Tpu && $data->Tpu->uuid === $auth->RelPetugasTpu->uuid_tpu) {
                    $hasPermission = true;
                } elseif ($data->nama_modul === 'Lahan' && $data->Lahan && $data->Lahan->Tpu && $data->Lahan->Tpu->uuid === $auth->RelPetugasTpu->uuid_tpu) {
                    $hasPermission = true;
                } elseif ($data->nama_modul === 'Sarpras' && $data->Sarpras && $data->Sarpras->Lahan && $data->Sarpras->Lahan->Tpu && $data->Sarpras->Lahan->Tpu->uuid === $auth->RelPetugasTpu->uuid_tpu) {
                    $hasPermission = true;
                }

                if (! $hasPermission) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Unauthorized action.',
                    ], 403);
                }
            }

            $value = $data->toArray();
            $save  = $data->delete();

            if ($save) {
                // Delete file
                if ($data->url && Storage::disk('public')->exists($data->url)) {
                    Storage::disk('public')->delete($data->url);
                }

                // Create log
                $aktifitas = [
                    'tabel' => ['tpu_dokumens'],
                    'uuid'  => [$uuid_dec],
                    'value' => [$value],
                ];
                $log = [
                    'apps'      => 'TPU Admin',
                    'subjek'    => 'Menghapus Dokumen ' . $data->nama_modul . ': ' . $data->nama_file . ' - ' . $uuid_dec,
                    'aktifitas' => $aktifitas,
                    'device'    => 'web',
                ];
                Helper::addToLogAktifitas($request, $log);

                DB::commit();
                return response()->json([
                    'status'  => true,
                    'message' => 'Dokumen Berhasil Dihapus!',
                ]);
            } else {
                DB::rollback();
                return response()->json([
                    'status'  => false,
                    'message' => 'Gagal Menghapus Dokumen!',
                ]);
            }
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Download document file.
     */
    public function download($uuid_enc)
    {
        $auth     = Auth::user();
        $uuid_dec = Helper::decode($uuid_enc);
        $data     = TpuDokumen::with(['Tpu', 'Lahan.Tpu', 'Sarpras.Lahan.Tpu'])->findOrFail($uuid_dec);

        // Verify permission untuk Admin TPU
        if ($auth->role === 'Admin TPU') {
            $hasPermission = false;
            if ($data->nama_modul === 'TPU' && $data->Tpu && $data->Tpu->uuid === $auth->RelPetugasTpu->uuid_tpu) {
                $hasPermission = true;
            } elseif ($data->nama_modul === 'Lahan' && $data->Lahan && $data->Lahan->Tpu && $data->Lahan->Tpu->uuid === $auth->RelPetugasTpu->uuid_tpu) {
                $hasPermission = true;
            } elseif ($data->nama_modul === 'Sarpras' && $data->Sarpras && $data->Sarpras->Lahan && $data->Sarpras->Lahan->Tpu && $data->Sarpras->Lahan->Tpu->uuid === $auth->RelPetugasTpu->uuid_tpu) {
                $hasPermission = true;
            }

            if (! $hasPermission) {
                alert()->error('Error', 'Unauthorized action.');
                return redirect()->route('tpu.dokumen.index', $data->nama_modul);
            }
        }

        if (! $data->url || ! Storage::disk('public')->exists($data->url)) {
            alert()->error('Error', 'File tidak ditemukan.');
            return redirect()->route('tpu.dokumen.index', $data->nama_modul);
        }

        return Storage::disk('public')->download($data->url, $data->nama_file . '.' . $data->tipe);
    }

    /**
     * Bulk destroy documents.
     */
    public function bulkDestroy(Request $request)
    {
        $auth = Auth::user();

        // Petugas TPU tidak bisa bulk delete
        if ($auth->role === 'Petugas TPU') {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        $request->validate([
            'uuids'   => 'required|array',
            'uuids.*' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $deletedCount = 0;
            $errors       = [];

            foreach ($request->uuids as $uuid_enc) {
                try {
                    $uuid_dec = Helper::decode($uuid_enc);
                    $data     = TpuDokumen::with(['Tpu', 'Lahan.Tpu', 'Sarpras.Lahan.Tpu'])->findOrFail($uuid_dec);

                    // Verify permission untuk Admin TPU
                    if ($auth->role === 'Admin TPU') {
                        $hasPermission = false;
                        if ($data->nama_modul === 'TPU' && $data->Tpu && $data->Tpu->uuid === $auth->RelPetugasTpu->uuid_tpu) {
                            $hasPermission = true;
                        } elseif ($data->nama_modul === 'Lahan' && $data->Lahan && $data->Lahan->Tpu && $data->Lahan->Tpu->uuid === $auth->RelPetugasTpu->uuid_tpu) {
                            $hasPermission = true;
                        } elseif ($data->nama_modul === 'Sarpras' && $data->Sarpras && $data->Sarpras->Lahan && $data->Sarpras->Lahan->Tpu && $data->Sarpras->Lahan->Tpu->uuid === $auth->RelPetugasTpu->uuid_tpu) {
                            $hasPermission = true;
                        }

                        if (! $hasPermission) {
                            $errors[] = 'Tidak memiliki izin untuk menghapus: ' . $data->nama_file;
                            continue;
                        }
                    }

                    $value = $data->toArray();
                    $save  = $data->delete();

                    if ($save) {
                        // Delete file
                        if ($data->url && Storage::disk('public')->exists($data->url)) {
                            Storage::disk('public')->delete($data->url);
                        }

                        // Create log
                        $aktifitas = [
                            'tabel' => ['tpu_dokumens'],
                            'uuid'  => [$uuid_dec],
                            'value' => [$value],
                        ];
                        $log = [
                            'apps'      => 'TPU Admin',
                            'subjek'    => 'Bulk Delete Dokumen ' . $data->nama_modul . ': ' . $data->nama_file . ' - ' . $uuid_dec,
                            'aktifitas' => $aktifitas,
                            'device'    => 'web',
                        ];
                        Helper::addToLogAktifitas($request, $log);

                        $deletedCount++;
                    } else {
                        $errors[] = 'Gagal menghapus: ' . $data->nama_file;
                    }
                } catch (Exception $e) {
                    $errors[] = 'Error: ' . $e->getMessage();
                }
            }

            DB::commit();

            $message = "Berhasil menghapus {$deletedCount} dokumen.";
            if (! empty($errors)) {
                $message .= ' Errors: ' . implode(', ', $errors);
            }

            return response()->json([
                'status'  => true,
                'message' => $message,
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ]);
        }
    }
}