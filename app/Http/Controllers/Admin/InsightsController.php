<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormType;

class InsightsController extends Controller
{
    public function index()
    {
        return view('admin.insights');
    }

    public function show(int $formTypeId)
    {
        abort_if(! FormType::where('id', $formTypeId)->exists(), 404);

        return view('admin.insights-detail', compact('formTypeId'));
    }
}

