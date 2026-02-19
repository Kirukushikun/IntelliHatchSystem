<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class WebhookController extends Controller
{
    /**
     * Extract photos from form inputs
     */
    private function extractPhotos($formInputs): array
    {
        $photos = [];
        
        if (is_array($formInputs)) {
            foreach ($formInputs as $key => $value) {
                if (is_array($value)) {
                    // Extract photos from nested arrays (like cleaning_photos, plenum_photos, etc.)
                    foreach ($value as $nestedKey => $nestedValue) {
                        if (is_array($nestedValue)) {
                            // Handle arrays of photo URLs
                            foreach ($nestedValue as $photoItem) {
                                if (is_string($photoItem) && !empty($photoItem)) {
                                    $photos[] = $photoItem;
                                }
                            }
                        } elseif (is_string($nestedValue) && !empty($nestedValue)) {
                            // Handle single photo URLs
                            $photos[] = $nestedValue;
                        }
                    }
                } elseif (is_string($value) && !empty($value)) {
                    // Handle direct photo fields
                    $photos[] = $value;
                }
            }
        }
        
        return array_unique($photos); // Remove duplicates
    }

    /**
     * Send incubator routine form data to external webhook
     */
    public function sendIncubatorRoutineForm(Request $request): JsonResponse
    {
        $request->validate([
            'form_id' => 'required|integer|exists:forms,id',
            'webhook_url' => 'nullable|url',
        ]);

        $webhookUrl = $request->webhook_url ?? config('services.webhook.url');

        try {
            $form = Form::with(['formType', 'user', 'incubator'])->findOrFail($request->form_id);
            
            $payload = [
                'form' => [
                    'form_id' => $form->id,
                    'form_name' => $form->formType ? $form->formType->name : 'Unknown Form Type',
                ],
                'records' => $form->form_inputs,
                'date_submitted' => $form->date_submitted->format('Y-m-d H:i:s'),
                'uploaded_by' => $form->user ? [
                    'id' => $form->user->id,
                    'name' => $form->user->first_name . ' ' . $form->user->last_name,
                ] : null,
                'incubator' => $form->incubator ? [
                    'id' => $form->incubator->id,
                    'name' => $form->incubator->incubatorName,
                ] : null,
                'message' => [
                    'form_name' => $form->formType ? $form->formType->name : 'Unknown Form Type',
                    'shift' => $form->form_inputs['shift'] ?? null,
                    'machine_name' => $form->incubator ? $form->incubator->incubatorName : null,
                    'submitted_by' => $form->user ? ($form->user->first_name . ' ' . $form->user->last_name) : null,
                    'date_time' => $form->date_submitted->format('Y-m-d H:i:s'),
                    'photos' => $this->extractPhotos($form->form_inputs),
                ],
                'timestamp' => now()->toISOString(),
            ];

            $response = Http::post($webhookUrl, $payload);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Form data sent successfully',
                    'webhook_response' => [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]
                ], 200, [], JSON_PRETTY_PRINT);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send form data to webhook',
                    'webhook_response' => [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]
                ], 400, [], JSON_PRETTY_PRINT);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500, [], JSON_PRETTY_PRINT);
        }
    }
}
