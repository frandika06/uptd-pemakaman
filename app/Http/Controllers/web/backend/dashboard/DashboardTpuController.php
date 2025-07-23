<?php
namespace App\Http\Controllers\web\backend\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardTpuController extends Controller
{
    //index
    public function index(Request $request)
    {
        return view('admin.home.index');
    }
}