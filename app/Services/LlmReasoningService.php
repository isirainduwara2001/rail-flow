<?php

// app/Services/LlmReasoningService.php — Step 3: Prompt → LLM reasoning (with fallback)

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LlmReasoningService
{
    protected CapeEngine $capeEngine;

    public function __construct(CapeEngine $capeEngine)
    {
        $this->capeEngine = $capeEngine;
    }

    /**
     * Send prompt to Gemini (via OpenAI-compatible endpoint) and return parsed risk assessment.
     *
     * NEVER throws — always returns a valid array (LLM or fallback).
     *
     * @param  string  $prompt
     * @param  array   $context  — for fallback calculation
     * @return array
     */
    public function reason(string $prompt, array $context = []): array
    {
        $startTime = microtime(true);

        try {
            $apiKey = config('cape.openai_api_key');

            if (empty($apiKey)) {
                Log::warning('CAPE: GEMINI_API_KEY not configured, using fallback.');
                return $this->fallback($context, $startTime, 'API key not configured');
            }

            $baseUrl = config('cape.openai_base_url');

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ])->timeout(30)->post($baseUrl . '/chat/completions', [
                'model'       => config('cape.openai_model'),
                'messages'    => [
                    [
                        'role'    => 'system',
                        'content' => 'You are a railway safety AI. Respond only in valid JSON.',
                    ],
                    [
                        'role'    => 'user',
                        'content' => $prompt,
                    ],
                ],
                'max_tokens'  => 500,
                'temperature' => 0.2,
            ]);

            $responseTimeMs = (int) round((microtime(true) - $startTime) * 1000);

            if (! $response->successful()) {
                Log::error('CAPE: Gemini API returned HTTP ' . $response->status(), [
                    'body' => $response->body(),
                ]);
                return $this->fallback($context, $startTime, 'HTTP ' . $response->status());
            }

            $body = $response->json();

            $content = $body['choices'][0]['message']['content'] ?? null;

            if (! $content) {
                Log::error('CAPE: Gemini response missing content.', ['body' => $body]);
                return $this->fallback($context, $startTime, 'Empty response content');
            }

            // Clean content — remove markdown code fences if present
            $content = trim($content);
            if (str_starts_with($content, '```')) {
                $content = preg_replace('/^```(?:json)?\s*/i', '', $content);
                $content = preg_replace('/\s*```$/', '', $content);
            }

            $parsed = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('CAPE: Failed to parse LLM JSON response.', [
                    'content'    => $content,
                    'json_error' => json_last_error_msg(),
                ]);
                return $this->fallback($context, $startTime, 'JSON parse error');
            }

            // Validate required keys
            $requiredKeys = ['risk_level', 'reasons', 'actions', 'prediction'];
            foreach ($requiredKeys as $key) {
                if (! array_key_exists($key, $parsed)) {
                    Log::error("CAPE: LLM response missing key '{$key}'.", ['parsed' => $parsed]);
                    return $this->fallback($context, $startTime, "Missing key: {$key}");
                }
            }

            // Validate risk_level value
            $validLevels = ['Low', 'Medium', 'High'];
            if (! in_array($parsed['risk_level'], $validLevels)) {
                Log::warning('CAPE: Invalid risk_level from LLM, normalizing.', [
                    'risk_level' => $parsed['risk_level'],
                ]);
                // Try to normalize
                $parsed['risk_level'] = ucfirst(strtolower($parsed['risk_level']));
                if (! in_array($parsed['risk_level'], $validLevels)) {
                    $parsed['risk_level'] = 'Medium'; // Safe default
                }
            }

            // Ensure arrays
            if (! is_array($parsed['reasons'])) {
                $parsed['reasons'] = [$parsed['reasons']];
            }
            if (! is_array($parsed['actions'])) {
                $parsed['actions'] = [$parsed['actions']];
            }

            return [
                'risk_level'       => $parsed['risk_level'],
                'reasons'          => $parsed['reasons'],
                'actions'          => $parsed['actions'],
                'prediction'       => $parsed['prediction'],
                'source'           => 'llm',
                'response_time_ms' => $responseTimeMs,
            ];

        } catch (\Exception $e) {
            Log::error('CAPE: Exception during LLM reasoning.', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return $this->fallback($context, $startTime, $e->getMessage());
        }
    }

    /**
     * Build fallback result using the rule-based engine.
     */
    private function fallback(array $context, float $startTime, string $reason): array
    {
        $responseTimeMs = (int) round((microtime(true) - $startTime) * 1000);

        Log::info("CAPE: Using fallback risk calculation. Reason: {$reason}");

        $result = $this->capeEngine->calculateLocalRisk($context);

        return [
            'risk_level'       => $result['risk_level'],
            'reasons'          => $result['reasons'],
            'actions'          => $result['actions'],
            'prediction'       => $result['prediction'],
            'source'           => 'fallback',
            'response_time_ms' => $responseTimeMs,
        ];
    }
}
