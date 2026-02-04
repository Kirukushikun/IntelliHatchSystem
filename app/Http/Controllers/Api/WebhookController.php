<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Send form data to external webhook
     */
    public function sendForm(Request $request): JsonResponse
    {
        $request->validate([
            'form_id' => 'required|integer|exists:forms,id',
            'webhook_url' => 'required|url',
        ]);

        try {
            $form = Form::with(['formType', 'user', 'incubator'])->findOrFail($request->form_id);
            
            $payload = [
                'form_id' => $form->id,
                'form_type' => $form->formType ? $form->formType->name : 'Unknown Form Type',
                'form_inputs' => $form->form_inputs,
                'date_submitted' => $form->date_submitted->format('Y-m-d H:i:s'),
                'uploaded_by' => $form->user ? [
                    'id' => $form->user->id,
                    'name' => $form->user->first_name . ' ' . $form->user->last_name,
                ] : null,
                'incubator' => $form->incubator ? [
                    'id' => $form->incubator->id,
                    'name' => $form->incubator->incubatorName,
                ] : null,
                'timestamp' => now()->toISOString(),
            ];

            $response = Http::post($request->webhook_url, $payload);

            if ($response->successful()) {
                Log::info('Form data sent successfully to webhook', [
                    'form_id' => $form->id,
                    'webhook_url' => $request->webhook_url,
                    'response_status' => $response->status()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Form data sent successfully',
                    'webhook_response' => [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]
                ], 200, [], JSON_PRETTY_PRINT);
            } else {
                Log::error('Failed to send form data to webhook', [
                    'form_id' => $form->id,
                    'webhook_url' => $request->webhook_url,
                    'response_status' => $response->status(),
                    'response_body' => $response->body(),
                ]);

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
            Log::error('Error sending form data to webhook', [
                'form_id' => $request->form_id,
                'webhook_url' => $request->webhook_url,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500, [], JSON_PRETTY_PRINT);
        }
    }

    /**
     * Send multiple forms to webhook
     */
    public function sendMultipleForms(Request $request): JsonResponse
    {
        $request->validate([
            'form_ids' => 'required|array',
            'form_ids.*' => 'integer|exists:forms,id',
            'webhook_url' => 'required|url',
        ]);

        try {
            $forms = Form::with(['formType', 'user', 'incubator'])
                ->whereIn('id', $request->form_ids)
                ->get();

            $payload = [
                'forms' => $forms->map(function ($form) {
                    return [
                        'form_id' => $form->id,
                        'form_type' => $form->formType ? $form->formType->name : 'Unknown Form Type',
                        'form_inputs' => $form->form_inputs,
                        'date_submitted' => $form->date_submitted->format('Y-m-d H:i:s'),
                        'uploaded_by' => $form->user ? [
                            'id' => $form->user->id,
                            'name' => $form->user->first_name . ' ' . $form->user->last_name,
                        ] : null,
                        'incubator' => $form->incubator ? [
                            'id' => $form->incubator->id,
                            'name' => $form->incubator->incubatorName,
                        ] : null,
                    ];
                })->toArray(),
                'total_forms' => $forms->count(),
                'timestamp' => now()->toISOString(),
            ];

            $response = Http::post($request->webhook_url, $payload);

            if ($response->successful()) {
                Log::info('Multiple forms sent successfully to webhook', [
                    'form_count' => $forms->count(),
                    'webhook_url' => $request->webhook_url,
                    'response_status' => $response->status()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Multiple forms sent successfully',
                    'forms_sent' => $forms->count(),
                    'webhook_response' => [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]
                ], 200, [], JSON_PRETTY_PRINT);
            } else {
                Log::error('Failed to send multiple forms to webhook', [
                    'form_count' => $forms->count(),
                    'webhook_url' => $request->webhook_url,
                    'response_status' => $response->status(),
                    'response_body' => $response->body(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send multiple forms to webhook',
                    'webhook_response' => [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]
                ], 400, [], JSON_PRETTY_PRINT);
            }

        } catch (\Exception $e) {
            Log::error('Error sending multiple forms to webhook', [
                'form_ids' => $request->form_ids,
                'webhook_url' => $request->webhook_url,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500, [], JSON_PRETTY_PRINT);
        }
    }
}
