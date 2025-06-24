<?php
namespace App\Http\Controllers\web\backend\master;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\PortalHeroSetup;
use App\Models\PortalSetup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SetupController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | MODEL HERO
    |--------------------------------------------------------------------------
    */
    public function indexModelHero()
    {
        $data = PortalSetup::whereNamaPengaturan('model_hero_section')
            ->whereSites('Portal')
            ->whereStatus('1')
            ->firstOrFail();
        return view('pages.admin.portal_apps.setup.model.create_edit', compact(
            'data'
        ));
    }
    public function updateModelHero(Request $request)
    {
        $request->validate([
            'model' => 'required|string',
        ]);

        $data = PortalSetup::whereNamaPengaturan('model_hero_section')
            ->whereSites('Portal')
            ->whereStatus('1')
            ->firstOrFail();

        $data->update(
            ['value_pengaturan' => $request->model]
        );

        return response()->json([
            'message' => 'Model berhasil diaktifkan!',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | HERO SECTION
    |--------------------------------------------------------------------------
    */
    public function indexHeroSection()
    {
        $data = PortalSetup::whereNamaPengaturan('model_hero_section')
            ->whereSites('Portal')
            ->whereStatus('1')
            ->with('RelHeroSection')
            ->firstOrFail();
        return view('pages.admin.portal_apps.setup.hero.create_edit', compact(
            'data'
        ));
    }
    public function updateHeroSection(Request $request)
    {
        $request->validate([
            "heading_1"          => "nullable|sometimes|max:255",
            "heading_2"          => "nullable|sometimes|max:255",
            "deskripsi"          => "nullable|sometimes|max:500",
            "judul_tombol_aksi"  => "nullable|sometimes|max:100",
            "url_tombol_aksi"    => "nullable|sometimes|max:255",
            "judul_tombol_video" => "nullable|sometimes|max:100",
            "url_tombol_video"   => "nullable|sometimes|max:255",
            "background_gambar"  => "nullable|sometimes|image|mimes:jpeg,png,jpg|max:2048",
            "background_video"   => "nullable|sometimes|file|mimes:mp4,webm,ogg|max:204800",
            "illustration"       => "nullable|sometimes|image|mimes:jpeg,png,jpg|max:2048",
        ]);

        $setup = PortalSetup::whereNamaPengaturan('model_hero_section')
            ->whereSites('Portal')
            ->whereStatus('1')
            ->with('RelHeroSection')
            ->firstOrFail();
        $uuid = $setup->RelHeroSection->uuid;
        $data = PortalHeroSetup::findOrFail($uuid);

        // cek value
        $path = "hero_section/" . date('Y') . "/" . $uuid;
        if ($setup->value_pengaturan === "Versi 1") {
            $value = [
                "heading_1"         => $request->heading_1,
                "heading_2"         => $request->heading_2,
                "deskripsi"         => $request->deskripsi,
                "judul_tombol_aksi" => $request->judul_tombol_aksi,
                "url_tombol_aksi"   => $request->url_tombol_aksi,
            ];
            // illustration
            if ($request->hasFile('illustration')) {
                $img = Helper::UpIllustrationHeroVersi1($request, "illustration", $path);
                if ($img == "0") {
                    alert()->error('Error!', 'Gagal Menyimpan Data, Illustration Tidak Sesuai Format!');
                    return back();
                }
                $value['illustration'] = $img;
            }
        } elseif ($setup->value_pengaturan === "Versi 2") {
            $value = [
                "heading_1"         => $request->heading_1,
                "heading_2"         => $request->heading_2,
                "deskripsi"         => $request->deskripsi,
                "judul_tombol_aksi" => $request->judul_tombol_aksi,
                "url_tombol_aksi"   => $request->url_tombol_aksi,
            ];
        } elseif ($setup->value_pengaturan === "Versi 3") {
            $value = [
                "heading_1"         => $request->heading_1,
                "heading_2"         => $request->heading_2,
                "deskripsi"         => $request->deskripsi,
                "judul_tombol_aksi" => $request->judul_tombol_aksi,
                "url_tombol_aksi"   => $request->url_tombol_aksi,
            ];
        } elseif ($setup->value_pengaturan === "Versi 4") {
            $value = [
                "heading_1"          => $request->heading_1,
                "heading_2"          => $request->heading_2,
                "deskripsi"          => $request->deskripsi,
                "judul_tombol_aksi"  => $request->judul_tombol_aksi,
                "url_tombol_aksi"    => $request->url_tombol_aksi,
                "judul_tombol_video" => $request->judul_tombol_video,
                "url_tombol_video"   => $request->url_tombol_video,
            ];
            // illustration
            if ($request->hasFile('illustration')) {
                $img = Helper::UpIllustrationHeroVersi4($request, "illustration", $path);
                if ($img == "0") {
                    alert()->error('Error!', 'Gagal Menyimpan Data, Illustration Tidak Sesuai Format!');
                    return back();
                }
                $value['illustration'] = $img;
            }
        } else {
            return \abort(500);
        }

        // background_gambar
        if ($request->hasFile('background_gambar')) {
            $img = Helper::UpBackgoundHero($request, "background_gambar", $path);
            if ($img == "0") {
                alert()->error('Error!', 'Gagal Menyimpan Data, Background Gambar Tidak Sesuai Format!');
                return back();
            }
            $value['background_gambar'] = $img;
        }
        // background_video
        if ($request->hasFile('background_video')) {
            $img = Helper::UpVideo($request, "background_video", $path);
            if ($img == "0") {
                alert()->error('Error!', 'Gagal Menyimpan Data, File Video Tidak Sesuai Format!');
                return back();
            }
            $value['background_video'] = $img['url'];
        }

        // save
        $save_1 = $data->update($value);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_hero_setup"],
                "uuid"  => [$uuid],
                "value" => [$value],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Mengubah Data Hero Section UUID= " . $uuid,
                "aktifitas" => $aktifitas,
                "device"    => "web",
            ];
            Helper::addToLogAktifitas($request, $log);
            alert()->success('Success', "Berhasil Mengubah Data!");
            return redirect()->route('prt.apps.mst.setup.hero.section.update');
        } else {
            alert()->error('Error', "Gagal Mengubah Data!");
            return back()->withInput($request->all());
        }
    }
    public function destroyHeroSection(Request $request)
    {
        // uuid
        $uuid = Helper::decode($request->uuid);
        $tags = $request->tags;
        // data
        $data = PortalHeroSetup::findOrFail($uuid);

        // cek tags
        if ($tags === "background_gambar") {
            if (Storage::exists($data->background_gambar)) {
                Storage::delete($data->background_gambar);
            }
            $value = ["background_gambar" => null];
        } elseif ($tags === "background_video") {
            if (Storage::exists($data->background_video)) {
                Storage::delete($data->background_video);
            }
            $value = ["background_video" => null];
        } elseif ($tags === "illustration") {
            if (Storage::exists($data->illustration)) {
                Storage::delete($data->illustration);
            }
            $value = ["illustration" => null];
        } else {
            // gagal
            $msg      = "Data Gagal Dihapus!";
            $response = [
                "status"  => false,
                "message" => $msg,
            ];
            return response()->json($response, 422);
        }

        // save
        $save_1 = $data->update($value);
        if ($save_1) {
            // create log
            $aktifitas = [
                "tabel" => ["portal_hero_setup"],
                "uuid"  => [$uuid],
                "value" => [$data],
            ];
            $log = [
                "apps"      => "Portal Apps",
                "subjek"    => "Menghapus Data " . $tags . " pada Hero Section UUID= " . $uuid,
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
            // gagal
            $msg      = "Data Gagal Dihapus!";
            $response = [
                "status"  => false,
                "message" => $msg,
            ];
            return response()->json($response, 422);
        }
    }

}
