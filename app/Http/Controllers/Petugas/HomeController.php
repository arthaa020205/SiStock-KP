<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return view('petugas.home');
    }
}