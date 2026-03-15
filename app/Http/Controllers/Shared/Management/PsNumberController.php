<?php

namespace App\Http\Controllers\Shared\Management;

use App\Http\Controllers\Controller;
use App\Models\PsNumber;
use Illuminate\Http\Request;

class PsNumberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $psNumbers = PsNumber::orderBy('creationDate', 'desc')->paginate(10);
        return view('shared.management.ps-numbers', compact('psNumbers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'psNumber' => 'required|string|max:255|unique:ps-numbers,psNumber',
        ]);

        PsNumber::create([
            'psNumber' => $validated['psNumber'],
            'isActive' => true,
        ]);

        return redirect()->route('admin.ps-numbers')
            ->with('success', 'PS Number created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PsNumber $psNumber)
    {
        $validated = $request->validate([
            'psNumber' => 'required|string|max:255|unique:ps-numbers,psNumber,' . $psNumber->id,
        ]);

        $psNumber->update($validated);

        return redirect()->route('admin.ps-numbers')
            ->with('success', 'PS Number updated successfully.');
    }

    /**
     * Toggle the status of the specified resource.
     */
    public function toggleStatus(PsNumber $psNumber)
    {
        $psNumber->update([
            'isActive' => !$psNumber->isActive,
        ]);

        $status = $psNumber->isActive ? 'deactivated' : 'activated';

        return redirect()->route('admin.ps-numbers')
            ->with('success', "PS Number {$status} successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PsNumber $psNumber)
    {
        $psNumberValue = $psNumber->psNumber;
        $psNumber->delete();

        return redirect()->route('admin.ps-numbers')
            ->with('success', "PS Number '{$psNumberValue}' deleted successfully.");
    }
}
