<?php
namespace App\Http\Controllers\web\backend\dasahboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //index
    public function index(Request $request)
    {
        return view('admin.home.index');
    }
}
