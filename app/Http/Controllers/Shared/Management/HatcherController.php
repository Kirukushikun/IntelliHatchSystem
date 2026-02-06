<?php

namespace App\Http\Controllers\Shared\Management;

use App\Http\Controllers\Controller;
use App\Models\Hatcher;
use Illuminate\Http\Request;

class HatcherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get hatchers data for the view if needed
        $hatchers = Hatcher::orderBy('creationDate', 'desc')->paginate(10);
        return view('shared.management.hatcher-machines', compact('hatchers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'hatcherName' => 'required|string|max:255|unique:hatcher-machines,hatcherName',
        ]);

        Hatcher::create([
            'hatcherName' => $validated['hatcherName'],
            'isActive' => true,
        ]);

        return redirect()->route('admin.hatcher-machines')
            ->with('success', 'Hatcher created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Hatcher $hatcher)
    {
        $validated = $request->validate([
            'hatcherName' => 'required|string|max:255|unique:hatcher-machines,hatcherName,' . $hatcher->id,
        ]);

        $hatcher->update($validated);

        return redirect()->route('admin.hatcher-machines')
            ->with('success', 'Hatcher updated successfully.');
    }

    /**
     * Toggle the status of the specified resource.
     */
    public function toggleStatus(Hatcher $hatcher)
    {
        $hatcher->update([
            'isActive' => !$hatcher->isActive,
        ]);

        $status = $hatcher->isActive ? 'deactivated' : 'activated';
        
        return redirect()->route('admin.hatcher-machines')
            ->with('success', "Hatcher {$status} successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Hatcher $hatcher)
    {
        $hatcherName = $hatcher->hatcherName;
        $hatcher->delete();

        return redirect()->route('admin.hatcher-machines')
            ->with('success', "Hatcher '{$hatcherName}' deleted successfully.");
    }
}
