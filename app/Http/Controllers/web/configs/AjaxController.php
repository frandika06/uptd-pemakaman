<?php
namespace App\Http\Controllers\web\configs;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AjaxController extends Controller
{
    // getCaptcha
    public function getCaptcha()
    {
        // return response()->json(['captcha' => captcha_img("flat")]);
        return response()->json(['captcha' => captcha_img("math")]);
    }
    // changeYear
    public function changeYear(Request $request)
    {
        if ($request->ajax()) {
            // params
            $callback = $request->callback;
            $tahun    = $request->tahun;

            // return
            $response = [
                "callback" => $callback,
            ];
            return response()->json($response, 200);
        }
    }
    // getStatisticContent
    public function getStatisticContent(Request $request)
    {
        if ($request->ajax()) {
            // params
            $model = $request->model;
            $tags  = $request->tags ?? null;
            $data  = [];

            // cek model
            if ($model == "Pesan") {
                $data = [
                    "unread"   => Helper::GetStatistikByModel("Pesan", "Unread", $tags),
                    "read"     => Helper::GetStatistikByModel("Pesan", "Read", $tags),
                    "archived" => Helper::GetStatistikByModel("Pesan", "Archived", $tags),
                    "deleted"  => Helper::GetStatistikByModel("Pesan", "Deleted", $tags),
                ];
            } else {
                $data = [
                    "draft"     => Helper::GetStatistikByModel($model, "Draft", $tags),
                    "pending"   => Helper::GetStatistikByModel($model, "Pending Review", $tags),
                    "published" => Helper::GetStatistikByModel($model, "Published", $tags),
                    "scheduled" => Helper::GetStatistikByModel($model, "Scheduled", $tags),
                    "archived"  => Helper::GetStatistikByModel($model, "Archived", $tags),
                    "deleted"   => Helper::GetStatistikByModel($model, "Deleted", $tags),
                ];
            }
            // return
            $response = [
                "status" => true,
                "data"   => $data,
            ];
            return response()->json($response, 200);
        }
    }
}