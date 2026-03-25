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
        $logs = CapeRiskLog::latest()->paginate(50);

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

        // Fetch latest CAPE risk log
        $latestLog = CapeRiskLog::latestRisk();

        if (! $latestLog) {
            return response()->json([
                'answer' => 'No CAPE assessments available yet. Please run an assessment first.',
            ]);
        }

        // Build chat prompt with current risk context
        $chatPrompt = "You are CAPE, a railway safety AI assistant for Sri Lanka railways.\n\n";
        $chatPrompt .= "Current risk assessment context:\n";
        $chatPrompt .= "- Risk Level: {$latestLog->risk_level}\n";
        $chatPrompt .= "- Reasons: " . implode(', ', $latestLog->reasons_json ?? []) . "\n";
        $chatPrompt .= "- Actions: " . implode(', ', $latestLog->actions_json ?? []) . "\n";
        $chatPrompt .= "- Prediction: {$latestLog->prediction}\n";
        $chatPrompt .= "- Assessment Source: {$latestLog->source}\n";

        // Add context labels
        if (is_array($latestLog->context_json)) {
            $chatPrompt .= "\nDetailed context:\n";
            foreach ($latestLog->context_json as $key => $value) {
                if ($value) {
                    $chatPrompt .= "  - {$key}: {$value}\n";
                }
            }
        }

        $chatPrompt .= "\nUser question: {$question}\n";
        $chatPrompt .= "\nProvide a helpful, concise answer focused on railway safety. ";
        $chatPrompt .= "If the question is unrelated to railway safety, politely redirect.";

        try {
            $apiKey = config('cape.openai_api_key');

            if (empty($apiKey)) {
                return response()->json([
                    'answer' => "I'm currently operating in offline mode (no API key configured). "
                        . "Based on the latest assessment, the risk level is {$latestLog->risk_level}. "
                        . "Key factors: " . implode(', ', $latestLog->reasons_json ?? ['No details available']) . ".",
                ]);
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
                        'content' => 'You are CAPE, a railway safety AI assistant. Be concise and helpful.',
                    ],
                    [
                        'role'    => 'user',
                        'content' => $chatPrompt,
                    ],
                ],
                'max_tokens'  => 300,
                'temperature' => 0.3,
            ]);

            if ($response->successful()) {
                $answer = $response->json('choices.0.message.content') ?? 'Unable to generate response.';
                return response()->json(['answer' => $answer]);
            }

            Log::error('CAPE Chat: API error', ['status' => $response->status()]);

        } catch (\Exception $e) {
            Log::error('CAPE Chat: Exception', ['message' => $e->getMessage()]);
        }

        // Fallback response
        return response()->json([
            'answer' => "I'm experiencing connectivity issues. Based on the latest assessment: "
                . "Risk level is {$latestLog->risk_level}. "
                . implode('. ', $latestLog->reasons_json ?? []) . ".",
        ]);
    }
}
