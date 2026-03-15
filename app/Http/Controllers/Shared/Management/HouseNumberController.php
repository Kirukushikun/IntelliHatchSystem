<?php

namespace App\Http\Controllers\Shared\Management;

use App\Http\Controllers\Controller;
use App\Models\HouseNumber;
use Illuminate\Http\Request;

class HouseNumberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $houseNumbers = HouseNumber::orderBy('creationDate', 'desc')->paginate(10);
        return view('shared.management.house-numbers', compact('houseNumbers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'houseNumber' => 'required|string|max:255|unique:house-numbers,houseNumber',
        ]);

        HouseNumber::create([
            'houseNumber' => $validated['houseNumber'],
            'isActive' => true,
        ]);

        return redirect()->route('admin.house-numbers')
            ->with('success', 'House Number created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HouseNumber $houseNumber)
    {
        $validated = $request->validate([
            'houseNumber' => 'required|string|max:255|unique:house-numbers,houseNumber,' . $houseNumber->id,
        ]);

        $houseNumber->update($validated);

        return redirect()->route('admin.house-numbers')
            ->with('success', 'House Number updated successfully.');
    }

    /**
     * Toggle the status of the specified resource.
     */
    public function toggleStatus(HouseNumber $houseNumber)
    {
        $houseNumber->update([
            'isActive' => !$houseNumber->isActive,
        ]);

        $status = $houseNumber->isActive ? 'deactivated' : 'activated';

        return redirect()->route('admin.house-numbers')
            ->with('success', "House Number {$status} successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HouseNumber $houseNumber)
    {
        $houseNumberValue = $houseNumber->houseNumber;
        $houseNumber->delete();

        return redirect()->route('admin.house-numbers')
            ->with('success', "House Number '{$houseNumberValue}' deleted successfully.");
    }
}
