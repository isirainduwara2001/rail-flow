<?php

// app/Http/Controllers/CapeController.php — CAPE API & admin controller

namespace App\Http\Controllers;

use App\Models\CapeRiskLog;
use App\Models\DisasterHistory;
use App\Models\IotData;
use App\Models\ObjectDetection;
use App\Services\CapeEngine;
use App\Services\ContextBuilderService;
use App\Services\EventMemoryService;
use App\Services\LlmReasoningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CapeController extends Controller
{
    protected ContextBuilderService $contextBuilder;
    protected CapeEngine $capeEngine;
    protected LlmReasoningService $llmReasoning;
    protected EventMemoryService $eventMemory;

    public function __construct(
        ContextBuilderService $contextBuilder,
        CapeEngine $capeEngine,
        LlmReasoningService $llmReasoning,
        EventMemoryService $eventMemory
    ) {
        $this->contextBuilder = $contextBuilder;
        $this->capeEngine     = $capeEngine;
        $this->llmReasoning   = $llmReasoning;
        $this->eventMemory    = $eventMemory;
    }

    /**
     * Run a full CAPE risk assessment using the latest data.
     */
    public function assess(Request $request): JsonResponse
    {
        // 1. Get latest IoT sensor data
        $latestIot = IotData::latest()->first();

        if (! $latestIot) {
            return response()->json([
                'error' => 'No IoT data available for assessment.',
            ], 404);
        }

        // 2. Fetch latest detection and disaster records
        $latestDetection = ObjectDetection::latest()->first();
        $latestDisaster  = DisasterHistory::latest()->first();

        // 3. Fetch weather from cache (shared with IotDataController)
        $weather = Cache::get('latest_weather', []);

        // 4. Assemble data array matching /api/iot/latest structure
        $data = [
            'latest'          => $latestIot->toArray(),
            'weather'         => $weather,
            'latestDetection' => $latestDetection ? $latestDetection->toArray() : null,
            'latestDisaster'  => $latestDisaster ? $latestDisaster->toArray() : null,
        ];

        // 5. Remember this reading in event memory
        $memoryData = $data['latest'];
        if ($latestDetection) {
            $memoryData['obstacle'] = $latestDetection->type . ' at ' . $latestDetection->distance . 'm';
        }
        $this->eventMemory->remember($memoryData);

        // 6. Build context labels
        $context = $this->contextBuilder->build($data);

        // 7. Build prompt
        $prompt = $this->capeEngine->buildPrompt($context, $data);

        // 8. Run LLM reasoning (with automatic fallback)
        $result = $this->llmReasoning->reason($prompt, $context);

        // 9. Save to cape_risk_logs
        $log = CapeRiskLog::create([
            'iot_data_id'     => $latestIot->id,
            'context_json'    => $context,
            'prompt_text'     => $prompt,
            'risk_level'      => $result['risk_level'],
            'reasons_json'    => $result['reasons'],
            'actions_json'    => $result['actions'],
            'prediction'      => $result['prediction'],
            'source'          => $result['source'],
            'llm_model'       => $result['source'] === 'llm' ? config('cape.openai_model') : null,
            'response_time_ms' => $result['response_time_ms'],
            'prompt_version'  => 'v1',
        ]);

        // 10. Return complete assessment response
        return response()->json([
            'context'          => $context,
            'prompt'           => $prompt,
            'risk_level'       => $result['risk_level'],
            'reasons'          => $result['reasons'],
            'actions'          => $result['actions'],
            'prediction'       => $result['prediction'],
            'source'           => $result['source'],
            'response_time_ms' => $result['response_time_ms'],
            'assessed_at'      => $log->created_at->toIso8601String(),
        ]);
    }

    /**
     * Display the CAPE research logs page (admin).
     */
    public function logs(Request $request): View
    {
        $logs = CapeRiskLog::latest()->paginate(10);

        // Summary statistics
        $totalAssessments = CapeRiskLog::count();
        $llmCount         = CapeRiskLog::where('source', 'llm')->count();
        $fallbackCount    = CapeRiskLog::where('source', 'fallback')->count();
        $llmPercentage    = $totalAssessments > 0 ? round(($llmCount / $totalAssessments) * 100, 1) : 0;
        $fallbackPercentage = $totalAssessments > 0 ? round(($fallbackCount / $totalAssessments) * 100, 1) : 0;

        $mostCommonRisk = CapeRiskLog::selectRaw('risk_level, COUNT(*) as count')
            ->groupBy('risk_level')
            ->orderByDesc('count')
            ->first();

        return view('cape.logs', compact(
            'logs',
            'totalAssessments',
            'llmCount',
            'fallbackCount',
            'llmPercentage',
            'fallbackPercentage',
            'mostCommonRisk'
        ));
    }

    /**
     * CAPE chat endpoint — ask questions about the current risk assessment.
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'question' => 'required|string|max:500',
        ]);

        $question = $request->input('question');

        $latestLog = CapeRiskLog::latestRisk();

        if (! $latestLog) {
            return response()->json([
                'answer' => 'No CAPE assessments available yet. Please wait for the first automatic assessment to complete.',
            ]);
        }

        $reasons = is_array($latestLog->reasons_json) ? $latestLog->reasons_json : [];
        $actions = is_array($latestLog->actions_json) ? $latestLog->actions_json : [];
        $contextData = is_array($latestLog->context_json) ? $latestLog->context_json : [];

        $apiKey = config('cape.openai_api_key');
        $baseUrl = config('cape.openai_base_url');

        if (empty($apiKey)) {
            return response()->json([
                'answer' => $this->generateOfflineResponse($question, $latestLog->risk_level, $reasons, $actions, $contextData, $latestLog->prediction),
            ]);
        }

        $chatPrompt = "You are CAPE, a railway safety AI assistant for Sri Lanka railways.\n\n";
        $chatPrompt .= "Current risk assessment context:\n";
        $chatPrompt .= "- Risk Level: {$latestLog->risk_level}\n";
        $chatPrompt .= "- Reasons: " . (count($reasons) ? implode(', ', $reasons) : 'None') . "\n";
        $chatPrompt .= "- Actions: " . (count($actions) ? implode(', ', $actions) : 'None') . "\n";
        $chatPrompt .= "- Prediction: {$latestLog->prediction}\n";

        if (!empty($contextData)) {
            $chatPrompt .= "\nSensor context:\n";
            foreach ($contextData as $key => $value) {
                if ($value) {
                    $chatPrompt .= "  - {$key}: {$value}\n";
                }
            }
        }

        $chatPrompt .= "\nUser question: {$question}\n";
        $chatPrompt .= "\nProvide a helpful, concise answer (2-4 sentences) focused on railway safety. Be conversational.";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ])->timeout(15)->post($baseUrl . '/chat/completions', [
                'model'       => config('cape.openai_model'),
                'messages'    => [
                    ['role' => 'system', 'content' => 'You are CAPE, a railway safety AI assistant. Be concise and conversational.'],
                    ['role' => 'user', 'content' => $chatPrompt],
                ],
                'max_tokens'  => 300,
                'temperature' => 0.3,
            ]);

            if ($response->successful()) {
                $body = $response->json();
                $content = $body['choices'][0]['message']['content'] ?? null;

                if ($content) {
                    $content = trim($content);
                    if (str_starts_with($content, '```')) {
                        $content = preg_replace('/^```(?:json)?\s*/i', '', $content);
                        $content = preg_replace('/\s*```$/', '', $content);
                        $content = trim($content);
                    }
                    return response()->json(['answer' => $content]);
                }
            }
        } catch (\Exception $e) {
            Log::warning('CAPE Chat: API failed, using offline response', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'answer' => $this->generateOfflineResponse($question, $latestLog->risk_level, $reasons, $actions, $contextData, $latestLog->prediction ?? ''),
        ]);
    }

    private function generateOfflineResponse(string $question, string $riskLevel, array $reasons, array $actions, array $contextData, string $prediction = ''): string
    {
        $lower = strtolower($question);

        if (str_contains($lower, 'weather') || str_contains($lower, 'rain') || str_contains($lower, 'temperature') || str_contains($lower, 'humidity')) {
            $weatherParts = [];
            if (isset($contextData['weather_context'])) $weatherParts[] = "Weather: {$contextData['weather_context']}";
            if (isset($contextData['rain_status'])) $weatherParts[] = "Rain: {$contextData['rain_status']}";
            if (isset($contextData['speed_status'])) $weatherParts[] = "Speed: {$contextData['speed_status']}";
            if (isset($contextData['light_condition'])) $weatherParts[] = "Light: {$contextData['light_condition']}";

            $weatherInfo = count($weatherParts) ? implode('. ', $weatherParts) : 'No detailed weather data available right now.';
            return "Current conditions — {$weatherInfo}. The overall risk level is {$riskLevel}. " . (count($reasons) ? 'Key concerns: ' . implode('; ', $reasons) : '') . '.';
        }

        if (str_contains($lower, 'risk') || str_contains($lower, 'danger') || str_contains($lower, 'safe') || str_contains($lower, 'status') || str_contains($lower, 'situation')) {
            $riskEmoji = match($riskLevel) {
                'High' => '⚠️',
                'Medium' => '⚡',
                default => '✅',
            };
            return "Risk level: {$riskLevel} {$riskEmoji}. " . (count($reasons) ? 'Reasons: ' . implode('; ', $reasons) . '. ' : '') . (count($actions) ? 'Recommended actions: ' . implode('; ', $actions) . '. ' : '') . "Prediction: {$prediction}.";
        }

        if (str_contains($lower, 'obstacle') || str_contains($lower, 'detect') || str_contains($lower, 'object')) {
            $obstacle = $contextData['obstacle'] ?? 'No obstacle detected';
            $proximity = $contextData['proximity_status'] ?? 'Unknown';
            return "Obstacle status: {$obstacle}. Proximity: {$proximity}. Risk level is {$riskLevel}. " . (count($actions) ? 'Recommended: ' . implode('; ', $actions) : 'Stay alert.');
        }

        if (str_contains($lower, 'speed') || str_contains($lower, 'fast') || str_contains($lower, 'slow')) {
            $speed = $contextData['speed_status'] ?? 'Unknown';
            return "Train speed status: {$speed}. Risk level is {$riskLevel}. " . (count($reasons) ? 'Context: ' . implode('; ', $reasons) : 'Proceed with caution.');
        }

        if (str_contains($lower, 'light') || str_contains($lower, 'dark') || str_contains($lower, 'night') || str_contains($lower, 'visibility')) {
            $light = $contextData['light_condition'] ?? 'Unknown';
            return "Light conditions: {$light}. Risk level is {$riskLevel}. " . (count($reasons) ? 'Factors: ' . implode('; ', $reasons) : 'No additional concerns.');
        }

        if (str_contains($lower, 'flood') || str_contains($lower, 'water') || str_contains($lower, 'river')) {
            $flood = $contextData['flood_context'] ?? 'No flood risk data available';
            return "Flood assessment: {$flood}. Risk level is {$riskLevel}. " . (count($actions) ? 'Actions: ' . implode('; ', $actions) : 'Monitor conditions.');
        }

        if (str_contains($lower, 'hello') || str_contains($lower, 'hi') || str_contains($lower, 'hey')) {
            return "Hello! I'm CAPE, your railway safety assistant. Current risk level is {$riskLevel}. How can I help you?";
        }

        if (str_contains($lower, 'thank')) {
            return "You're welcome! Stay safe on the railways. Current risk level is {$riskLevel}.";
        }

        if (str_contains($lower, 'who are you') || str_contains($lower, 'what are you') || str_contains($lower, 'what is cape')) {
            return "I'm CAPE (Context-Aware Prompt Engine), a railway safety AI. I analyze sensor data, weather, obstacles, and other factors to assess risk levels for Sri Lanka's railway network.";
        }

        $defaultResponse = "Based on the latest assessment, risk level is {$riskLevel}. ";
        if (count($reasons)) {
            $defaultResponse .= "Key factors: " . implode('; ', $reasons) . ". ";
        }
        if (count($actions)) {
            $defaultResponse .= "Recommended: " . implode('; ', $actions) . ". ";
        }
        $defaultResponse .= "Ask me about weather, speed, obstacles, flood risk, or overall safety status.";

        return $defaultResponse;
    }
}
