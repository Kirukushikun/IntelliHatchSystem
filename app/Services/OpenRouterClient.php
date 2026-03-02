<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenRouterClient
{
    private const BASE_URL = 'https://openrouter.ai/api/v1';
    private const DEFAULT_MAX_RETRIES = 3;
    private const DEFAULT_RETRY_DELAY_MS = 1000;
    private const RETRYABLE_STATUS_CODES = [429, 500, 502, 503, 504];

    private string $apiKey;
    private int $maxRetries;
    private int $retryDelayMs;

    public function __construct(
        int $maxRetries = self::DEFAULT_MAX_RETRIES,
        int $retryDelayMs = self::DEFAULT_RETRY_DELAY_MS
    ) {
        $this->apiKey = config('services.openrouter.key');
        $this->maxRetries = $maxRetries;
        $this->retryDelayMs = $retryDelayMs;
    }

    /**
     * Send a chat completion request with retry logic.
     *
     * @param  array  $messages  Array of ['role' => '...', 'content' => '...']
     * @param  string  $model    OpenRouter model identifier
     * @param  array  $options  Additional options (temperature, max_tokens, etc.)
     * @return array  Parsed response data
     *
     * @throws Exception
     */
    public function chat(array $messages, string $model = 'openai/gpt-4o', array $options = []): array
    {
        $payload = array_merge([
            'model'    => $model,
            'messages' => $messages,
            'stream'   => false,
        ], $options);

        return $this->sendWithRetry('chat/completions', $payload);
    }

    /**
     * Convenience method: send a simple user message with an optional system prompt.
     */
    public function ask(string $userMessage, string $systemPrompt = '', string $model = 'openai/gpt-4o', array $options = []): string
    {
        $messages = [];

        if ($systemPrompt) {
            $messages[] = ['role' => 'system', 'content' => $systemPrompt];
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage];

        $response = $this->chat($messages, $model, $options);

        return $response['choices'][0]['message']['content'] ?? '';
    }

    /**
     * Internal: perform the HTTP request with exponential backoff retry.
     */
    private function sendWithRetry(string $endpoint, array $payload): array
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt <= $this->maxRetries) {
            try {
                $response = $this->makeRequest($endpoint, $payload);

                if ($response->successful()) {
                    return $response->json();
                }

                // Non-retryable client error (e.g. 400, 401, 403)
                if (! in_array($response->status(), self::RETRYABLE_STATUS_CODES)) {
                    throw new Exception(
                        "OpenRouter API error [{$response->status()}]: " . $response->body()
                    );
                }

                $lastException = new Exception(
                    "OpenRouter API error [{$response->status()}]: " . $response->body()
                );

                Log::warning('OpenRouter request failed, retrying...', [
                    'attempt'    => $attempt + 1,
                    'max'        => $this->maxRetries,
                    'status'     => $response->status(),
                    'endpoint'   => $endpoint,
                ]);

            } catch (Exception $e) {
                // Re-throw immediately for non-retryable errors
                if (! $this->isRetryable($e)) {
                    throw $e;
                }

                $lastException = $e;

                Log::warning('OpenRouter request threw exception, retrying...', [
                    'attempt' => $attempt + 1,
                    'max'     => $this->maxRetries,
                    'error'   => $e->getMessage(),
                ]);
            }

            $attempt++;

            if ($attempt <= $this->maxRetries) {
                $delay = $this->retryDelayMs * (2 ** ($attempt - 1)); // Exponential backoff
                usleep($delay * 1000);
            }
        }

        throw new Exception(
            "OpenRouter request failed after {$this->maxRetries} retries. Last error: " . $lastException?->getMessage()
        );
    }

    private function makeRequest(string $endpoint, array $payload): Response
    {
        return Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type'  => 'application/json',
        ])
            ->timeout(60)
            ->post(self::BASE_URL . "/{$endpoint}", $payload);
    }

    private function isRetryable(Exception $e): bool
    {
        // Retry on connection/timeout errors
        return str_contains($e->getMessage(), 'cURL error')
            || str_contains($e->getMessage(), 'timeout')
            || str_contains($e->getMessage(), 'Connection refused');
    }
}