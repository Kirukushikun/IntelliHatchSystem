<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class InsightsController extends Controller
{
    public function index()
    {
        return view('admin.insights');
    }
}
