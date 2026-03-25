<?php

namespace App\Http\Controllers\Shared\Management;

use App\Http\Controllers\Controller;
use App\Models\GetSet;
use Illuminate\Http\Request;

class GetSetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $getSets = GetSet::orderBy('creationDate', 'desc')->paginate(10);
        return view('shared.management.get-sets', compact('getSets'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'getSetName' => 'required|string|max:255|unique:get-sets,getSetName',
        ]);

        GetSet::create([
            'getSetName' => $validated['getSetName'],
            'isActive' => true,
        ]);

        return redirect()->route('admin.get-sets')
            ->with('success', 'GetSet created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GetSet $getSet)
    {
        $validated = $request->validate([
            'getSetName' => 'required|string|max:255|unique:get-sets,getSetName,' . $getSet->id,
        ]);

        $getSet->update($validated);

        return redirect()->route('admin.get-sets')
            ->with('success', 'GetSet updated successfully.');
    }

    /**
     * Toggle the status of the specified resource.
     */
    public function toggleStatus(GetSet $getSet)
    {
        $getSet->update([
            'isActive' => !$getSet->isActive,
        ]);

        $status = $getSet->isActive ? 'deactivated' : 'activated';

        return redirect()->route('admin.get-sets')
            ->with('success', "GetSet {$status} successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GetSet $getSet)
    {
        $getSetValue = $getSet->getSetName;
        $getSet->delete();

        return redirect()->route('admin.get-sets')
            ->with('success', "GetSet '{$getSetValue}' deleted successfully.");
    }
}
