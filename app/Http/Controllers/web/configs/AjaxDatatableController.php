<?php
namespace App\Http\Controllers\web\configs;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\PortalEsertifikatList;
use App\Models\PortalGaleriList;
use App\Models\PortalTanosList;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AjaxDatatableController extends Controller
{
    //dataGaleriList
    public function dataGaleriList(Request $request)
    {
        // auth
        $auth = Auth::user();
        $role = $auth->role;
        if ($request->ajax()) {
            $uuid_galeri_enc = $_GET['filter']['uuid_galeri'];
            $uuid_galeri     = Helper::decode($uuid_galeri_enc);
        }
        $data = PortalGaleriList::whereUuidGaleri($uuid_galeri)->orderBy("no_urut", "ASC")->get();
        return DataTables::of($data)
            ->addIndexColumn()
            ->setRowId('uuid')
            ->addColumn('checkbox', function ($data) {
                $uuid_enc = Helper::encode($data->uuid);
                return '<input type="checkbox" class="row-checkbox" value="' . $uuid_enc . '">';
            })
            ->addColumn('judul', function ($data) {
                $url   = Helper::thumbnail($data->url);
                $view  = Helper::urlImg($data->url);
                $judul = '
                    <div class="trans-list">
                        <img src="' . $url . '" alt="" class="rounded avatar avatar-xl me-3" draggable="false">
                        <h4><a class="text-underline" target="_BLANK" href="' . $view . '">' . $data->judul . '</a></h4>
                    </div>';
                return $judul;
            })
            ->addColumn('penulis', function ($data) {
                $penulis = isset($data->Penulis->nama_lengkap) ? $data->Penulis->nama_lengkap : '-';
                return $penulis;
            })
            ->addColumn('publisher', function ($data) {
                $publisher = isset($data->Publisher->nama_lengkap) ? $data->Publisher->nama_lengkap : '-';
                return $publisher;
            })
            ->addColumn('downloads', function ($data) {
                $downloads = Helper::toDot($data->downloads);
                return $downloads;
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
            ->addColumn('aksi', function ($data) use ($role) {
                $uuid_enc = Helper::encode($data->uuid);
                $view     = Helper::urlImg($data->url);
                $aksi     = '
                        <div class="d-flex">
                        <a target="_BLANK" href="' . $view . '" class="btn btn-primary shadow btn-xs sharp me-1"><i class="fas fa-eye"></i></a>
                        <a href="javascript:void(0);" class="btn btn-danger shadow btn-xs sharp" data-delete="' . $uuid_enc . '"><i class="fa fa-trash"></i></a>
                        </div>
                        ';
                return $aksi;
            })
            ->escapeColumns([''])
            ->make(true);
    }

    //dataEsertifikatList
    public function dataEsertifikatList(Request $request)
    {
        // auth
        $auth = Auth::user();
        $role = $auth->role;
        if ($request->ajax()) {
            $uuid_esertifikat_enc = $_GET['filter']['uuid_esertifikat'];
            $uuid_esertifikat     = Helper::decode($uuid_esertifikat_enc);
        }
        $data = PortalEsertifikatList::whereUuidEsertifikat($uuid_esertifikat)->orderBy("nama_lengkap", "ASC")->get();
        return DataTables::of($data)
            ->addIndexColumn()
            ->setRowId('uuid')
            ->addColumn('checkbox', function ($data) {
                $uuid_enc = Helper::encode($data->uuid);
                return '<input type="checkbox" class="row-checkbox" value="' . $uuid_enc . '">';
            })
            ->addColumn('nama_lengkap', function ($data) {
                $view         = Helper::urlImg($data->url);
                $nama_lengkap = '
                    <div class="trans-list">
                        <h4><a class="text-underline" target="_BLANK" href="' . $view . '">' . $data->nama_lengkap . '</a></h4>
                    </div>';
                return $nama_lengkap;
            })
            ->addColumn('views', function ($data) {
                $views = Helper::toDot($data->views);
                return $views;
            })
            ->addColumn('size', function ($data) {
                $size = isset($data->size) ? Helper::SizeDisk($data->size) : '-';
                return $size;
            })
            ->addColumn('downloads', function ($data) {
                $downloads = Helper::toDot($data->downloads);
                return $downloads;
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
            ->addColumn('aksi', function ($data) use ($role) {
                $uuid_enc = Helper::encode($data->uuid);
                $view     = Helper::urlImg($data->url);
                $aksi     = '
                        <div class="d-flex">
                        <a target="_BLANK" href="' . $view . '" class="btn btn-primary shadow btn-xs sharp me-1"><i class="fas fa-eye"></i></a>
                        <a href="javascript:void(0);" class="btn btn-danger shadow btn-xs sharp" data-delete="' . $uuid_enc . '"><i class="fa fa-trash"></i></a>
                        </div>
                        ';
                return $aksi;
            })
            ->escapeColumns([''])
            ->make(true);
    }

    // dataAnggotaTanos
    public function dataAnggotaTanos(Request $request)
    {
        // auth
        $auth = Auth::user();
        $role = $auth->role;
        if ($request->ajax()) {
            $uuid_tanos_enc = $_GET['filter']['uuid_tanos'];
            $uuid_tanos     = Helper::decode($uuid_tanos_enc);
        }
        $data = PortalTanosList::whereUuidTanos($uuid_tanos)->orderBy("no_urut", "ASC")->get();
        return DataTables::of($data)
            ->addIndexColumn()
            ->setRowId('uuid')
            ->addColumn('checkbox', function ($data) {
                $uuid_enc = Helper::encode($data->uuid);
                return '<input type="checkbox" class="row-checkbox" value="' . $uuid_enc . '">';
            })
            ->addColumn('penulis', function ($data) {
                $penulis = isset($data->Penulis->nama_lengkap) ? $data->Penulis->nama_lengkap : '-';
                return $penulis;
            })
            ->addColumn('publisher', function ($data) {
                $publisher = isset($data->Publisher->nama_lengkap) ? $data->Publisher->nama_lengkap : '-';
                return $publisher;
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
            ->addColumn('aksi', function ($data) use ($role) {
                $uuid_enc = Helper::encode($data->uuid);
                $aksi     = '
                        <div class="d-flex">
                        <a href="javascript:void(0);" class="btn btn-danger shadow btn-xs sharp" data-delete="' . $uuid_enc . '"><i class="fa fa-trash"></i></a>
                        </div>
                        ';
                return $aksi;
            })
            ->escapeColumns([''])
            ->make(true);
    }
}
