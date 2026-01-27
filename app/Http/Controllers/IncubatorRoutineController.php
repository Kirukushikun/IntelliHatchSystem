<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IncubatorRoutineController extends Controller
{
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'employee_name' => 'required|string|max:255',
            'notes' => 'required|string',
            'photos' => 'required|array|min:1',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240'
        ]);

        // Process and store images
        $imageUrls = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                // Generate unique filename
                $filename = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                
                // Store file in public storage
                $path = $photo->storeAs('incubator-photos', $filename, 'public');
                
                // Get public URL
                $url = Storage::url($path);
                $imageUrls[] = $url;
            }
        }

        // Prepare webhook payload
        $payload = [
            'employee_name' => $validated['employee_name'],
            'notes' => $validated['notes'],
            'photos' => $imageUrls,
            'submitted_at' => now()->toISOString(),
            'photo_count' => count($imageUrls)
        ];

        // Send to webhook
        try {
            $webhookUrl = 'https://automation.bfcgroup.ph/webhook-test/4bbc76c4-6040-4034-a289-4466ea77fa13';
            
            $response = Http::post($webhookUrl, $payload);
            
            // Log response for debugging
            Log::info('Webhook response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => $payload
            ]);

            // Return success response
            return back()->with('success', 'Form submitted successfully! ' . count($imageUrls) . ' photos uploaded.');
            
        } catch (\Exception $e) {
            Log::error('Webhook failed', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
            
            return back()->with('error', 'Form submitted but webhook failed. Please contact support.');
        }
    }
}
