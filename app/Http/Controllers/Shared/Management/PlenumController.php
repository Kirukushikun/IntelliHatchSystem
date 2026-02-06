<?php

namespace App\Http\Controllers\Shared\Management;

use App\Http\Controllers\Controller;
use App\Models\Plenum;
use Illuminate\Http\Request;

class PlenumController extends Controller
{
    /**
     * Display plenum machines management page.
     */
    public function index()
    {
        return view('shared.management.plenum-machines');
    }

    /**
     * Store a new plenum machine.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'plenumName' => 'required|string|max:255|unique:plenum-machines,plenumName',
        ]);

        $plenum = Plenum::create([
            'plenumName' => $validated['plenumName'],
            'isActive' => true,
            'creationDate' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Plenum machine created successfully.',
            'plenum' => $plenum
        ]);
    }

    /**
     * Update specified plenum machine.
     */
    public function update(Request $request, Plenum $plenum)
    {
        $validated = $request->validate([
            'plenumName' => 'required|string|max:255|unique:plenum-machines,plenumName,' . $plenum->id,
        ]);

        $plenum->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Plenum machine updated successfully.',
            'plenum' => $plenum
        ]);
    }

    /**
     * Remove the specified plenum machine.
     */
    public function destroy(Plenum $plenum)
    {
        $plenum->delete();

        return response()->json([
            'success' => true,
            'message' => 'Plenum machine deleted successfully.'
        ]);
    }

    /**
     * Toggle status of the specified plenum machine.
     */
    public function toggleStatus(Plenum $plenum)
    {
        $plenum->update([
            'isActive' => !$plenum->isActive,
        ]);

        $status = $plenum->isActive ? 'deactivated' : 'activated';

        return response()->json([
            'success' => true,
            'message' => "Plenum machine {$status} successfully.",
            'plenum' => $plenum
        ]);
    }

    /**
     * Get all plenum machines for API requests.
     */
    public function indexApi()
    {
        $plenums = Plenum::orderBy('plenumName', 'asc')->get();
        
        return response()->json([
            'success' => true,
            'plenums' => $plenums
        ]);
    }
}
