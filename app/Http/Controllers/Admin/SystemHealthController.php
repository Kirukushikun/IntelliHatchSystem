<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SystemHealth;
use Illuminate\Http\Request;

class SystemHealthController extends Controller
{
    public function show(Request $request, SystemHealth $health)
    {
        abort_unless(SystemHealth::canView($request->user()), 403);

        // The page always answers 200 — it is read when something is already broken.
        return view('admin.system-health', ['report' => $health->report()]);
    }

    public function json(Request $request, SystemHealth $health)
    {
        abort_unless(SystemHealth::canView($request->user()), 403);

        $report = $health->report();

        // 503 on a failure, 200 on a warning: usable as a check without parsing.
        return response()
            ->json($report, $report['status'] === SystemHealth::FAIL ? 503 : 200)
            ->header('Cache-Control', 'no-store');
    }
}
