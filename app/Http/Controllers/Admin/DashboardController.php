<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the main dashboard.
     */
    public function index()
    {
        return view('admin.dashboard');
    }

    /**
     * Display the incubator routine dashboard.
     */
    public function incubatorRoutine()
    {
        $incubatorForms = Form::whereHas('formType', function($query) {
            $query->where('form_name', 'like', '%incubator%');
        })
        ->with(['formType', 'user'])
        ->orderBy('created_at', 'desc')
        ->paginate(10);

        return view('admin.incubator-routine-dashboard', compact('incubatorForms'));
    }

    /**
     * Display the blower air hatcher dashboard.
     */
    public function blowerAirHatcher()
    {
        return view('admin.blower-air-hatcher-dashboard');
    }

    /**
     * Display the blower air incubator dashboard.
     */
    public function blowerAirIncubator()
    {
        return view('admin.blower-air-incubator-dashboard');
    }
}
