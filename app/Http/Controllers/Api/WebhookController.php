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
        Log::info('Webhook sendForm request received', [
            'form_id' => $request->form_id,
            'webhook_url' => $request->webhook_url,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $request->validate([
            'form_id' => 'required|integer|exists:forms,id',
            'webhook_url' => 'nullable|url',
        ]);

        $webhookUrl = $request->webhook_url ?? env('WEBHOOK_URL');

        Log::info('Validation passed, preparing to send form to webhook', [
            'form_id' => $request->form_id,
            'final_webhook_url' => $webhookUrl,
        ]);

        try {
            $form = Form::with(['formType', 'user', 'incubator'])->findOrFail($request->form_id);
            
            Log::info('Form data retrieved successfully', [
                'form_id' => $form->id,
                'form_type' => $form->formType ? $form->formType->name : 'Unknown Form Type',
                'has_user' => !is_null($form->user),
                'has_incubator' => !is_null($form->incubator),
            ]);
            
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

            Log::info('Payload prepared for webhook', [
                'form_id' => $form->id,
                'payload_size' => strlen(json_encode($payload)),
                'webhook_url' => $webhookUrl,
            ]);

            $response = Http::post($webhookUrl, $payload);

            if ($response->successful()) {
                Log::info('Webhook response successful', [
                    'form_id' => $form->id,
                    'webhook_url' => $webhookUrl,
                    'response_status' => $response->status(),
                    'response_body' => $response->body(),
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
                Log::error('Webhook response failed', [
                    'form_id' => $form->id,
                    'webhook_url' => $webhookUrl,
                    'response_status' => $response->status(),
                    'response_body' => $response->body(),
                    'payload_sent' => $payload,
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
            Log::error('Exception occurred in sendForm', [
                'form_id' => $request->form_id,
                'webhook_url' => $webhookUrl,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'exception_class' => get_class($e),
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
        Log::info('Webhook sendMultipleForms request received', [
            'form_ids' => $request->form_ids,
            'form_count' => count($request->form_ids ?? []),
            'webhook_url' => $request->webhook_url,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $request->validate([
            'form_ids' => 'required|array',
            'form_ids.*' => 'integer|exists:forms,id',
            'webhook_url' => 'nullable|url',
        ]);

        $webhookUrl = $request->webhook_url ?? env('WEBHOOK_URL');

        Log::info('Validation passed for multiple forms', [
            'form_ids' => $request->form_ids,
            'final_webhook_url' => $webhookUrl,
        ]);

        try {
            $forms = Form::with(['formType', 'user', 'incubator'])
                ->whereIn('id', $request->form_ids)
                ->get();

            Log::info('Multiple forms retrieved successfully', [
                'requested_form_ids' => $request->form_ids,
                'found_form_count' => $forms->count(),
                'forms_found' => $forms->pluck('id')->toArray(),
            ]);

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

            Log::info('Multiple forms payload prepared', [
                'total_forms' => $forms->count(),
                'payload_size' => strlen(json_encode($payload)),
                'webhook_url' => $webhookUrl,
            ]);

            $response = Http::post($webhookUrl, $payload);

            if ($response->successful()) {
                Log::info('Multiple forms webhook response successful', [
                    'form_count' => $forms->count(),
                    'webhook_url' => $webhookUrl,
                    'response_status' => $response->status(),
                    'response_body' => $response->body(),
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
                Log::error('Multiple forms webhook response failed', [
                    'form_count' => $forms->count(),
                    'webhook_url' => $webhookUrl,
                    'response_status' => $response->status(),
                    'response_body' => $response->body(),
                    'payload_sent' => $payload,
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
            Log::error('Exception occurred in sendMultipleForms', [
                'form_ids' => $request->form_ids,
                'webhook_url' => $webhookUrl,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'exception_class' => get_class($e),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500, [], JSON_PRETTY_PRINT);
        }
    }
}
