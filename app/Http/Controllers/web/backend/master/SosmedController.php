<?php

namespace App\Http\Controllers\web\backend\master;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\PortalSosmed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SosmedController extends Controller
{
    // index
    public function index()
    {
        // auth
        $auth = Auth::user();

        // cek data sosmed
        $cekSosmed = PortalSosmed::first();
        if ($cekSosmed === null) {
            // create
            $sosmed = [
                "Facebook",
                "Twitter",
                "TikTok",
                "Instagram",
                "YouTube",
            ];
            $url = [
                "https://www.facebook.com/",
                "https://www.twitter.com/",
                "https://www.tiktok.com/",
                "https://www.instagram.com/",
                "https://www.youtube.com/",
            ];
            $csosmed = count($sosmed);
            for ($i = 0; $i < $csosmed; $i++) {
                // value
                $value_1 = [
                    "uuid" => Str::uuid(),
                    "sosmed" => $sosmed[$i],
                    "url" => $url[$i],
                ];
                // save
                PortalSosmed::create($value_1);
            }
        }

        // update
        $data = PortalSosmed::all();
        return view('pages.admin.portal_apps.sosmed.create_edit', compact(
            'data'
        ));
    }

    // update
    public function update(Request $request)
    {
        // auth
        $auth = Auth::user();

        // uuid
        $uuids = $request->uuid;
        $sosmed = $request->sosmed;
        $url = $request->url;
        $cuuid = count($uuids);
        for ($i = 0; $i < $cuuid; $i++) {
            // value
            $uuid = $uuids[$i];
            // cek data
            $data = PortalSosmed::whereUuid($uuid)->first();
            if ($data === null) {
                continue;
            }
            $value_1 = [
                "sosmed" => $sosmed[$i],
                "url" => $url[$i],
            ];
            $save_1 = $data->update($value_1);
            // create log
            $aktifitas = [
                "tabel" => ["portal_sosmed"],
                "uuid" => [$uuid],
                "value" => [$value_1],
            ];
            $log = [
                "apps" => "Portal Apps",
                "subjek" => "Mengubah Data Master Sosial Media UUID= " . $uuid,
                "aktifitas" => $aktifitas,
                "device" => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
        }

        // save
        if ($save_1) {
            // alert success
            alert()->success('Success', "Berhasil Mengubah Data!");
            return \redirect()->route('prt.apps.mst.sosmed.index');
        } else {
            alert()->error('Error', "Gagal Mengubah Data!");
            return \back()->withInput($request->all());
        }
    }
}
