<?php

namespace App\Http\Controllers\Shared\Management;

use App\Http\Controllers\Controller;
use App\Models\Incubator;
use Illuminate\Http\Request;

class IncubatorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get incubators data for the view if needed
        $incubators = Incubator::orderBy('creationDate', 'desc')->paginate(10);
        return view('shared.management.incubator-machines', compact('incubators'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'incubatorName' => 'required|string|max:255|unique:incubator-machines,incubatorName',
        ]);

        Incubator::create([
            'incubatorName' => $validated['incubatorName'],
            'isActive' => true,
        ]);

        return redirect()->route('admin.incubator-machines')
            ->with('success', 'Incubator created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Incubator $incubator)
    {
        $validated = $request->validate([
            'incubatorName' => 'required|string|max:255|unique:incubator-machines,incubatorName,' . $incubator->id,
        ]);

        $incubator->update($validated);

        return redirect()->route('admin.incubator-machines')
            ->with('success', 'Incubator updated successfully.');
    }

    /**
     * Toggle the status of the specified resource.
     */
    public function toggleStatus(Incubator $incubator)
    {
        $incubator->update([
            'isActive' => !$incubator->isActive,
        ]);

        $status = $incubator->isActive ? 'deactivated' : 'activated';
        
        return redirect()->route('admin.incubator-machines')
            ->with('success', "Incubator {$status} successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Incubator $incubator)
    {
        $incubatorName = $incubator->incubatorName;
        $incubator->delete();

        return redirect()->route('admin.incubator-machines')
            ->with('success', "Incubator '{$incubatorName}' deleted successfully.");
    }
}
