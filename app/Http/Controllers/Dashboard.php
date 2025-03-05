<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class Dashboard extends Controller
{
    public function index()
    {
        return view('dashboard');
    }
}