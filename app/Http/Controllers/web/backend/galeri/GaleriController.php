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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GaleriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $auth = Auth::user();
        $role = $auth->role;

        // Cek filter status
        $status = $request->session()->get('filter_status_galeri', 'Published');
        if ($request->ajax() && isset($_GET['filter']['status'])) {
            $status = $_GET['filter']['status'];
            $request->session()->put('filter_status_galeri', $status);
        }

        if ($request->ajax()) {
            $query = PortalGaleri::query()->orderBy("tanggal", "DESC");

            // Apply status filter
            if ($status !== '') {
                $query->where('status', $status);
            }

            // Batasi data berdasarkan role
            if (! in_array($role, ['Super Admin', 'Admin', 'Editor'])) {
                $query->where('uuid_created', $auth->uuid);
            }

            // Ambil semua data untuk client-side processing
            $data = $query->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->setRowId('uuid')
                ->addColumn('checkbox', function ($data) {
                    return '
                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                            <input class="form-check-input row-checkbox" type="checkbox" value="' . $data->uuid . '" />
                        </div>';
                })
                ->addColumn('judul_raw', function ($data) {
                    return $data->judul ?: '[draft]';
                })
                ->addColumn('judul_html', function ($data) {
                    $uuid_enc   = Helper::encode($data->uuid);
                    $edit_url   = route('prt.apps.galeri.edit', $uuid_enc);
                    $thumbnails = Helper::thumbnail($data->thumbnails);
                    $judulText  = $data->judul ? Str::limit($data->judul, 60, "...") : '[draft] - lanjutkan atau hapus';
                    return '
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-50px me-5">
                                <img src="' . $thumbnails . '" class="h-75 align-self-end" alt="Thumbnail" style="object-fit: cover; border-radius: 8px;" />
                            </div>
                            <div class="d-flex flex-column">
                                <a href="' . $edit_url . '" class="text-gray-800 text-hover-primary mb-1 fw-bold fs-6">' . $judulText . '</a>
                                <span class="text-muted fw-semibold d-block fs-7">' . Str::slug($data->judul) . '</span>
                            </div>
                        </div>';
                })
                ->addColumn('kategori_raw', function ($data) {
                    return $data->kategori ?: 'Tidak ada kategori';
                })
                ->addColumn('kategori_html', function ($data) {
                    $categories = explode(',', $data->kategori ? $data->kategori : '');
                    $badges     = '';
                    foreach ($categories as $category) {
                        $badges .= '<span class="badge badge-light-primary fw-bold fs-7 px-3 py-2 me-1">' . trim($category) . '</span>';
                    }
                    return $badges ?: '<span class="badge badge-light-secondary fw-bold fs-7 px-3 py-2">Tidak ada kategori</span>';
                })
                ->addColumn('views_raw', function ($data) {
                    return $data->views;
                })
                ->addColumn('views_html', function ($data) {
                    return '<div class="text-center"><span class="fw-bold text-gray-800">' . Helper::toDot($data->views) . '</span></div>';
                })
                ->addColumn('jumlah_raw', function ($data) {
                    return count($data->RelGaleriList) > 0 ? $data->GetJumlahGaleri() : 0;
                })
                ->addColumn('jumlah_html', function ($data) {
                    $jumlah = count($data->RelGaleriList) > 0 ? Helper::toDot($data->GetJumlahGaleri()) : 0;
                    $color  = $jumlah > 0 ? 'primary' : 'secondary';
                    return '<div class="d-flex align-items-center justify-content-center">
                                <span class="badge badge-light-' . $color . ' fs-base fs-3">' . $jumlah . '</span>
                            </div>';
                })
                ->addColumn('penulis_raw', function ($data) {
                    return $data->Penulis->nama_lengkap ?? '-';
                })
                ->addColumn('penulis_html', function ($data) {
                    return '<span class="text-gray-600 fw-semibold">' . ($data->Penulis->nama_lengkap ?? '-') . '</span>';
                })
                ->addColumn('publisher_raw', function ($data) {
                    return $data->status === 'Published' ? ($data->Publisher->nama_lengkap ?? '-') : '-';
                })
                ->addColumn('publisher_html', function ($data) {
                    return $data->status === 'Published'
                    ? '<span class="text-success fw-semibold">' . ($data->Publisher->nama_lengkap ?? '-') . '</span>'
                    : '<span class="text-muted">-</span>';
                })
                ->addColumn('status_raw', function ($data) {
                    return $data->status;
                })
                ->addColumn('status_html', function ($data) {
                    $colors = [
                        'Draft'     => 'warning', 'Pending Review' => 'info', 'Published' => 'success',
                        'Scheduled' => 'primary', 'Archived'       => 'dark', 'Deleted'   => 'danger',
                    ];
                    $color = isset($colors[$data->status]) ? $colors[$data->status] : 'secondary';
                    return '<span class="badge badge-light-' . $color . ' fw-bold fs-7 px-3 py-2">' . $data->status . '</span>';
                })
                ->addColumn('tanggal_raw', function ($data) {
                    return Helper::TglSimple($data->created_at);
                })
                ->addColumn('tanggal_html', function ($data) {
                    return '<span class="text-gray-600 fw-semibold">' . Helper::TglSimple($data->created_at) . '</span>';
                })
                ->addColumn('aksi', function ($data) use ($auth, $role) {
                    $uuid_enc = Helper::encode($data->uuid);
                    $edit_url = route('prt.apps.galeri.edit', $uuid_enc);
                    $canEdit  = in_array($role, ['Super Admin', 'Admin', 'Editor']) || (isset($data->uuid_created) && $data->uuid_created === $auth->uuid);
                    if ($canEdit) {
                        return '
                            <div class="d-flex justify-content-center">
                                <a href="' . $edit_url . '" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" data-bs-toggle="tooltip" title="Edit">
                                    <i class="ki-outline ki-pencil fs-2"></i>
                                </a>
                                <a href="javascript:void(0);" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" data-delete="' . $uuid_enc . '" data-bs-toggle="tooltip" title="Hapus">
                                    <i class="ki-outline ki-trash fs-2"></i>
                                </a>
                            </div>';
                    }
                    return '
                        <div class="d-flex justify-content-center">
                            <span class="btn btn-icon btn-bg-light btn-sm me-1 disabled" data-bs-toggle="tooltip" title="Edit (Tidak diizinkan)">
                                <i class="ki-outline ki-pencil fs-2 text-muted"></i>
                            </span>
                            <span class="btn btn-icon btn-bg-light btn-sm disabled" data-bs-toggle="tooltip" title="Hapus (Tidak diizinkan)">
                                <i class="ki-outline ki-trash fs-2 text-muted"></i>
                            </span>
                        </div>';
                })
                ->escapeColumns([])
                ->make(true);
        }

        return view('admin.cms.konten.media.galeri.index', compact('status'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        try {
            $auth            = Auth::user();
            $uuid_galeri     = Str::uuid();
            $uuid_galeri_enc = Helper::encode($uuid_galeri);
            $value           = [
                'uuid'         => $uuid_galeri,
                'uuid_created' => $auth->uuid,
                'judul'        => '',
                'status'       => 'Draft',
            ];

            $save = PortalGaleri::create($value);
            if ($save) {
                $aktifitas = [
                    'tabel' => ['portal_galeri'],
                    'uuid'  => [$uuid_galeri],
                    'value' => [$value],
                ];
                Helper::addToLogAktifitas($request, [
                    'apps'      => 'Portal Apps',
                    'subjek'    => 'Menambahkan Data Draft Galeri: ' . $uuid_galeri,
                    'aktifitas' => $aktifitas,
                    'device'    => 'web',
                ]);
                return redirect()->route('prt.apps.galeri.edit', $uuid_galeri_enc)->with('success', 'Berhasil membuat draft galeri!');
            }
            return redirect()->route('prt.apps.galeri.index')->with('error', 'Gagal membuat galeri!');
        } catch (Exception $e) {
            Log::error('Create Galeri Error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->route('prt.apps.galeri.index')->with('error', 'Terjadi kesalahan saat membuat galeri: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $auth        = Auth::user();
            $role        = $auth->role;
            $uuid_galeri = Str::uuid();
            $uuid_enc    = Helper::encode($uuid_galeri);

            // Validasi input
            $request->validate([
                'judul'        => 'required|string|max:100',
                'deskripsi'    => 'required|string|max:160',
                'thumbnails'   => 'required|image|mimes:png,jpg,jpeg|max:2048',
                'tanggal'      => 'required|date',
                'kategori'     => 'required|string|max:100',
                'status'       => 'required|string|in:Draft,Pending Review,Published,Scheduled,Archived',
                'judul_foto.*' => 'nullable|string|max:100',
                'file_foto.*'  => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            // Validasi status dan izin
            if (! Helper::validateStatus($role, $request->status)) {
                return back()->with('error', 'Status tidak valid untuk peran Anda!')->withInput();
            }

            // Generate slug
            $slug       = Str::slug($request->judul);
            $slugExists = PortalGaleri::where('slug', $slug)->exists();
            $slug       = $slugExists ? $slug . '-' . Helper::gencode(4) : $slug;

            // Prepare data
            $thn   = Carbon::now()->year;
            $path  = "galeri/{$thn}/{$uuid_galeri}";
            $value = [
                'uuid'         => $uuid_galeri,
                'uuid_created' => $auth->uuid,
                'judul'        => $request->judul,
                'slug'         => $slug,
                'deskripsi'    => $request->deskripsi,
                'tanggal'      => Carbon::parse($request->tanggal),
                'kategori'     => $request->kategori,
                'status'       => $request->status,
                'uuid_updated' => $auth->uuid,
            ];

            // Handle thumbnail
            if ($request->hasFile('thumbnails')) {
                $img = Helper::UpThumbnails($request, 'thumbnails', $path);
                if ($img === '0') {
                    return back()->with('error', 'Thumbnail tidak sesuai format!')->withInput();
                }
                $value['thumbnails'] = $img;
            }

            // Create galeri
            $save = PortalGaleri::create($value);
            if ($save) {
                // Handle galeri list
                $path_list = "{$path}/photos";
                if ($request->has('judul_foto') && $request->hasFile('file_foto')) {
                    foreach ($request->judul_foto as $index => $judul_foto) {
                        if ($judul_foto && isset($request->file('file_foto')[$index])) {
                            $field = $request->file('file_foto')[$index];
                            $foto  = Helper::UpFotoGaleri($request, $field, $path_list);
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

                // Log aktivitas
                $aktifitas = [
                    'tabel' => ['portal_galeri'],
                    'uuid'  => [$uuid_galeri],
                    'value' => [$request->judul],
                ];
                Helper::addToLogAktifitas($request, [
                    'apps'      => 'Portal Apps',
                    'subjek'    => "Membuat Data Galeri: {$request->judul} - {$uuid_galeri}",
                    'aktifitas' => $aktifitas,
                    'device'    => 'web',
                ]);

                return redirect()->route('prt.apps.galeri.index')->with('success', 'Berhasil membuat galeri: ' . $request->judul . '!');
            }
            return back()->with('error', 'Gagal membuat galeri!')->withInput();
        } catch (Exception $e) {
            Log::error('Store Galeri Error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Terjadi kesalahan saat membuat galeri: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($uuid_enc)
    {
        $auth         = Auth::user();
        $kategoriList = PortalKategori::where('type', 'Galeri')->where('status', '1')->orderBy('nama')->get();
        $uuid         = Helper::decode($uuid_enc);
        $data         = PortalGaleri::with('RelGaleriList')->findOrFail($uuid);
        $title        = 'Edit Data Galeri';
        $submit       = 'Simpan';
        return view('admin.cms.konten.media.galeri.create_edit', compact('auth', 'uuid_enc', 'title', 'submit', 'kategoriList', 'data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $uuid_enc)
    {
        try {
            $auth        = Auth::user();
            $role        = $auth->role;
            $uuid_galeri = Helper::decode($uuid_enc);
            $data        = PortalGaleri::findOrFail($uuid_galeri);

            // Validasi input
            $rules = [
                'judul'             => 'required|string|max:100',
                'deskripsi'         => 'required|string|max:160',
                'thumbnails'        => ($data->thumbnails ? 'sometimes' : 'required') . '|image|mimes:png,jpg,jpeg|max:2048',
                'tanggal'           => 'required|date',
                'kategori'          => 'required|string|max:100',
                'status'            => 'required|string|in:Draft,Pending Review,Published,Scheduled,Archived',
                'judul_foto.*'      => 'nullable|string|max:100',
                'file_foto.*'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'judul_foto_list.*' => 'nullable|string|max:100',
                'uuid_foto.*'       => 'nullable|string|exists:portal_galeri_list,uuid',
            ];
            $request->validate($rules);

            // Validasi status dan izin
            if (! Helper::validateStatus($role, $request->status)) {
                return back()->with('error', 'Status tidak valid untuk peran Anda!')->withInput();
            }
            if (in_array($role, ['Penulis', 'Kontributor']) && $data->status !== 'Draft') {
                return back()->with('error', 'Konten sudah tidak bisa diubah!')->withInput();
            }

            // Generate slug
            $slug = $data->judul !== $request->judul ? Str::slug($request->judul) : $data->slug;
            if ($data->judul !== $request->judul) {
                $slugExists = PortalGaleri::where('uuid', '!=', $uuid_galeri)->where('slug', $slug)->exists();
                $slug       = $slugExists ? $slug . '-' . Helper::gencode(4) : $slug;
            }

            // Prepare data
            $thn   = Carbon::parse($data->created_at)->year;
            $path  = "galeri/{$thn}/{$uuid_galeri}";
            $value = [
                'judul'        => $request->judul,
                'slug'         => $slug,
                'deskripsi'    => $request->deskripsi,
                'tanggal'      => Carbon::parse($request->tanggal),
                'kategori'     => $request->kategori,
                'status'       => $request->status,
                'uuid_updated' => $auth->uuid,
            ];

            // Handle thumbnail
            if ($request->hasFile('thumbnails')) {
                if ($data->thumbnails && Storage::disk('public')->exists($data->thumbnails)) {
                    Storage::disk('public')->delete([$data->thumbnails, str_replace('.', '_thumbnail.', $data->thumbnails)]);
                }
                $img = Helper::UpThumbnails($request, 'thumbnails', $path);
                if ($img === '0') {
                    return back()->with('error', 'Thumbnail tidak sesuai format!')->withInput();
                }
                $value['thumbnails'] = $img;
            } elseif ($request->filled('thumbnails_remove')) {
                if ($data->thumbnails && Storage::disk('public')->exists($data->thumbnails)) {
                    Storage::disk('public')->delete([$data->thumbnails, str_replace('.', '_thumbnail.', $data->thumbnails)]);
                }
                $value['thumbnails'] = null;
            }

            // Update galeri
            $save = $data->update($value);
            if ($save) {
                // Handle galeri list - Update existing photos
                if ($request->has('judul_foto_list') && $request->has('uuid_foto')) {
                    foreach ($request->judul_foto_list as $index => $judul_foto) {
                        if (isset($request->uuid_foto[$index]) && ! empty($judul_foto)) {
                            $uuid_foto = $request->uuid_foto[$index];
                            $foto      = PortalGaleriList::where('uuid', $uuid_foto)
                                ->where('uuid_galeri', $uuid_galeri)
                                ->first();
                            if ($foto) {
                                // Validasi izin untuk mengedit
                                $canEdit = in_array($role, ['Super Admin', 'Admin', 'Editor']) || ($data->uuid_created == $auth->uuid);
                                if ($canEdit) {
                                    $foto->update(['judul' => $judul_foto]);
                                    // Log aktivitas untuk pengeditan judul
                                    $aktifitas = [
                                        'tabel' => ['portal_galeri_list'],
                                        'uuid'  => [$uuid_foto],
                                        'value' => [$judul_foto],
                                    ];
                                    Helper::addToLogAktifitas($request, [
                                        'apps'      => 'Portal Apps',
                                        'subjek'    => "Mengubah Judul Foto Galeri: {$judul_foto} - {$uuid_foto}",
                                        'aktifitas' => $aktifitas,
                                        'device'    => 'web',
                                    ]);
                                }
                            }
                        }
                    }
                }

                // Handle galeri list - Add new photos
                $path_list = "{$path}/photos";
                if ($request->has('judul_foto') && $request->hasFile('file_foto')) {
                    foreach ($request->judul_foto as $index => $judul_foto) {
                        if ($judul_foto && isset($request->file('file_foto')[$index])) {
                            $field = $request->file('file_foto')[$index];
                            $foto  = Helper::UpFotoGaleri($request, $field, $path_list);
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

                // Log aktivitas untuk update galeri
                $aktifitas = [
                    'tabel' => ['portal_galeri'],
                    'uuid'  => [$uuid_galeri],
                    'value' => [$request->judul],
                ];
                Helper::addToLogAktifitas($request, [
                    'apps'      => 'Portal Apps',
                    'subjek'    => "Mengubah Data Galeri: {$request->judul} - {$uuid_galeri}",
                    'aktifitas' => $aktifitas,
                    'device'    => 'web',
                ]);

                alert()->success('Success', 'Berhasil Mengubah Data!');
                return redirect()->route('prt.apps.galeri.index')->with('success', 'Berhasil mengubah galeri: ' . $request->judul . '!');
            }
            return back()->with('error', 'Gagal mengubah galeri!')->withInput();
        } catch (Exception $e) {
            alert()->error('Error', 'Gagal Mengubah Data!');
            Log::error('Update Galeri Error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Terjadi kesalahan saat mengubah galeri: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        try {
            $auth = Auth::user();
            $tags = $request->tags;

            if ($request->filled('uuids') && is_array($request->uuids)) {
                if ($tags === 'galeri') {
                    return $this->bulkDestroy($request);
                }
                return response()->json(['status' => false, 'message' => 'Tag tidak valid untuk penghapusan massal.'], 422);
            }

            $uuid = Helper::decode($request->uuid);
            if ($tags === 'galeri') {
                return $this->delGaleri($request, $auth, $uuid);
            } elseif ($tags === 'list_galeri') {
                return $this->delListGaleri($request, $auth, $uuid);
            }
            return response()->json(['status' => false, 'message' => 'Tag tidak valid.'], 422);
        } catch (Exception $e) {
            Log::error('Destroy Error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['status' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a single galeri item.
     */
    private function delGaleri($request, $auth, $uuid)
    {
        try {
            $data = PortalGaleri::find($uuid);
            if (! $data) {
                return response()->json(['status' => false, 'message' => "Data dengan ID {$uuid} tidak ditemukan"], 404);
            }

            $role      = $auth->role;
            $canDelete = in_array($role, ['Super Admin', 'Admin', 'Editor']) || ($data->uuid_created == $auth->uuid);
            if (! $canDelete) {
                return response()->json(['status' => false, 'message' => "Tidak memiliki izin untuk menghapus: {$data->judul}"], 403);
            }

            $tahun = Carbon::parse($data->tanggal)->year;
            $path  = "galeri/{$tahun}/{$data->uuid}";
            $judul = $data->judul;

            if (in_array($data->status, ['Draft', 'Pending Review'])) {
                Helper::deleteFolderIfExists('directory', $path);
                $save = $data->forceDelete();
            } else {
                $data->update(['uuid_deleted' => $auth->uuid, 'status' => 'Deleted']);
                $save = $data->delete();
            }

            if ($save) {
                $aktifitas = [
                    'tabel' => ['portal_galeri'],
                    'uuid'  => [$uuid],
                    'value' => [$judul],
                ];
                Helper::addToLogAktifitas($request, [
                    'apps'      => 'Portal Apps',
                    'subjek'    => "Menghapus Data Galeri: {$judul} - {$uuid}",
                    'aktifitas' => $aktifitas,
                    'device'    => 'web',
                ]);
                return response()->json(['status' => true, 'message' => "Berhasil menghapus galeri: {$judul}!"]);
            }
            return response()->json(['status' => false, 'message' => "Gagal menghapus galeri: {$judul}"]);
        } catch (Exception $e) {
            Log::error('Delete Galeri Error', ['uuid' => $uuid, 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['status' => false, 'message' => "Terjadi kesalahan saat menghapus galeri: {$e->getMessage()}"]);
        }
    }

    /**
     * Delete a single galeri list item.
     */
    private function delListGaleri($request, $auth, $uuid)
    {
        try {
            $data = PortalGaleriList::find($uuid);
            if (! $data) {
                return response()->json(['status' => false, 'message' => "Data dengan ID {$uuid} tidak ditemukan"]);
            }

            $role      = $auth->role;
            $canDelete = in_array($role, ['Super Admin', 'Admin', 'Editor']) || ($data->RelGaleri->uuid_created == $auth->uuid);
            if (! $canDelete) {
                return response()->json(['status' => false, 'message' => "Tidak memiliki izin untuk menghapus: {$data->judul}"]);
            }

            $judul = $data->judul;
            if (in_array($data->RelGaleri->status, ['Draft', 'Pending Review'])) {
                if (! empty($data->url) && Storage::disk('public')->exists($data->url)) {
                    Storage::disk('public')->delete([$data->url, str_replace('.', '_thumbnail.', $data->url)]);
                }
                $save = $data->forceDelete();
            } else {
                $data->update(['uuid_deleted' => $auth->uuid, 'status' => 'Deleted']);
                $save = $data->delete();
            }

            if ($save) {
                $aktifitas = [
                    'tabel' => ['portal_galeri_list'],
                    'uuid'  => [$uuid],
                    'value' => [$judul],
                ];
                Helper::addToLogAktifitas($request, [
                    'apps'      => 'Portal Apps',
                    'subjek'    => "Menghapus Data List Galeri: {$judul} - {$uuid}",
                    'aktifitas' => $aktifitas,
                    'device'    => 'web',
                ]);
                return response()->json(['status' => true, 'message' => "Berhasil menghapus foto: {$judul}!"]);
            }
            return response()->json(['status' => false, 'message' => "Gagal menghapus foto: {$judul}"]);
        } catch (Exception $e) {
            Log::error('Delete List Galeri Error', ['uuid' => $uuid, 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['status' => false, 'message' => "Terjadi kesalahan saat menghapus foto: {$e->getMessage()}"]);
        }
    }

    /**
     * Bulk delete galeri items.
     */
    private function bulkDestroy(Request $request)
    {
        try {
            $auth  = Auth::user();
            $rules = [
                'uuids'   => 'required|array|min:1',
                'uuids.*' => 'required|string',
            ];
            $request->validate($rules);

            $uuids        = $request->uuids;
            $deletedCount = 0;
            $failedItems  = [];

            foreach ($uuids as $uuid) {
                try {
                    $data = PortalGaleri::find($uuid);
                    if (! $data) {
                        $failedItems[] = "Data dengan ID {$uuid} tidak ditemukan";
                        continue;
                    }

                    $role      = $auth->role;
                    $canDelete = in_array($role, ['Super Admin', 'Admin', 'Editor']) || ($data->uuid_created == $auth->uuid);
                    if (! $canDelete) {
                        $failedItems[] = "Tidak memiliki izin untuk menghapus: {$data->judul}";
                        continue;
                    }

                    $tahun = Carbon::parse($data->tanggal)->year;
                    $path  = "galeri/{$tahun}/{$data->uuid}";
                    $judul = $data->judul;

                    if (in_array($data->status, ['Draft', 'Pending Review'])) {
                        Helper::deleteFolderIfExists('directory', $path);
                        $save = $data->forceDelete();
                    } else {
                        $data->update(['uuid_deleted' => $auth->uuid, 'status' => 'Deleted']);
                        $save = $data->delete();
                    }

                    if ($save) {
                        $deletedCount++;
                        $aktifitas = [
                            'tabel' => ['portal_galeri'],
                            'uuid'  => [$uuid],
                            'value' => [$data->toArray()],
                        ];
                        Helper::addToLogAktifitas($request, [
                            'apps'      => 'Portal Apps',
                            'subjek'    => "Menghapus Data Galeri (Bulk): {$judul} - {$uuid}",
                            'aktifitas' => $aktifitas,
                            'device'    => 'web',
                        ]);
                    } else {
                        $failedItems[] = "Gagal menghapus: {$judul}";
                    }
                } catch (Exception $e) {
                    $failedItems[] = "Error pada ID {$uuid}: {$e->getMessage()}";
                }
            }

            $message = "Berhasil menghapus {$deletedCount} galeri";
            if (! empty($failedItems)) {
                $message .= ". Gagal menghapus " . count($failedItems) . " item";
                if (count($failedItems) <= 3) {
                    $message .= ": " . implode(", ", $failedItems);
                }
            }

            $summaryLog = [
                'apps'      => 'Portal Apps',
                'subjek'    => "Bulk Delete Galeri - Berhasil: {$deletedCount}, Gagal: " . count($failedItems),
                'aktifitas' => [
                    'tabel'         => ['portal_galeri'],
                    'total_request' => count($uuids),
                    'total_deleted' => $deletedCount,
                    'total_failed'  => count($failedItems),
                    'failed_items'  => $failedItems,
                ],
                'device'    => 'web',
            ];
            Helper::addToLogAktifitas($request, $summaryLog);

            if ($deletedCount > 0) {
                return response()->json([
                    'status'        => true,
                    'message'       => $message,
                    'deleted_count' => $deletedCount,
                    'failed_count'  => count($failedItems),
                    'failed_items'  => $failedItems,
                ]);
            }
            return response()->json([
                'status'        => false,
                'message'       => $message,
                'deleted_count' => $deletedCount,
                'failed_count'  => count($failedItems),
                'failed_items'  => $failedItems,
            ]);
        } catch (Exception $e) {
            Log::error('Bulk Delete Galeri Error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['status' => false, 'message' => 'Terjadi kesalahan saat menghapus galeri: ' . $e->getMessage()]);
        }
    }
}