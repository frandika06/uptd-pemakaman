<?php
namespace App\Http\Controllers\portal;

use App\Http\Controllers\Controller;

class HomePortalController extends Controller
{
    // index
    public function index()
    {
        return \view('portal.home.index');
    }
}
