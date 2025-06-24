<?php

namespace App\Http\Controllers\web\configs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NocController extends Controller
{
    // index
    public function index(Request $request, $name)
    {
        $name = strtolower($name);
        $columns = \DB::getSchemaBuilder()->getColumnListing($name);
        $response = [
            "status" => true,
            "ip" => $request->ip(),
            "data" => $columns,
        ];
        return response()->json($response);
    }
}
