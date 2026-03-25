<?php

// app/Services/CapeEngine.php — Step 2: Context → weighted prompt assembly + fallback risk calculator

namespace App\Services;

class CapeEngine
{
    protected EventMemoryService $eventMemory;

    public function __construct(EventMemoryService $eventMemory)
    {
        $this->eventMemory = $eventMemory;
    }

    /**
     * Build a dynamic, weighted prompt string for the LLM.
     *
     * Higher-priority context factors appear first.
     *
     * @param  array  $context  — output of ContextBuilderService::build()
     * @param  array  $rawData  — the raw sensor/weather data
     * @return string
     */
    public function buildPrompt(array $context, array $rawData): string
    {
        $weights = config('cape.prompt_weights');

        // Map context keys to their weight categories
        $weightMapping = [
            'obstacle'         => 'obstacle',
            'flood_context'    => 'flood',
            'light_condition'  => 'light',
            'proximity_status' => 'obstacle',
            'speed_status'     => 'speed',
            'weather_context'  => 'weather',
            'humidity_context' => 'weather',
            'rain_context'     => 'weather',
        ];

        // Build weighted entries
        $entries = [];
        foreach ($context as $key => $value) {
            if ($value === null) {
                continue;
            }

            $category = $weightMapping[$key] ?? 'weather';
            $weight   = $weights[$category] ?? 1;
            $label    = $this->getWeightLabel($weight);

            $entries[] = [
                'weight' => $weight,
                'label'  => $label,
                'text'   => $value,
                'key'    => $key,
            ];
        }

        // Sort by weight descending (highest priority first)
        usort($entries, fn($a, $b) => $b['weight'] <=> $a['weight']);

        // Assemble prompt header
        $prompt = "Railway safety assessment — Sri Lanka.\n";

        // Add each context factor with its priority label
        foreach ($entries as $entry) {
            $prompt .= "[{$entry['label']}] {$entry['text']}\n";
        }

        // Add event memory summary
        $memorySummary = $this->eventMemory->getSummary();
        $prompt .= "\nRecent history: {$memorySummary}\n";

        // Add raw data summary for additional context
        $latest = $rawData['latest'] ?? [];
        if (! empty($latest)) {
            $prompt .= "\nRaw sensor values: ";
            $prompt .= "Speed={$latest['speed']}km/h, ";
            $prompt .= "Temp={$latest['temperature']}°C, ";
            $prompt .= "Humidity={$latest['humidity']}%, ";
            $prompt .= "Lux={$latest['lux']}, ";
            $prompt .= "Rain={$latest['rain_percentage']}%, ";
            $prompt .= "Front={$latest['sf_front_distance']}m, ";
            $prompt .= "Side={$latest['sf_side_distance']}m.\n";
        }

        // Add response instruction
        $prompt .= "\nAnalyze the above risk factors comprehensively. ";
        $prompt .= "Respond ONLY as valid JSON with this exact structure:\n";
        $prompt .= '{"risk_level": "Low|Medium|High", "reasons": ["reason1", "reason2"], ';
        $prompt .= '"actions": ["action1", "action2"], "prediction": "string describing potential future risk evolution"}';

        return $prompt;
    }

    /**
     * Get human-readable priority label from numeric weight.
     */
    private function getWeightLabel(int $weight): string
    {
        return match (true) {
            $weight >= 5 => 'CRITICAL',
            $weight >= 4 => 'HIGH',
            $weight >= 3 => 'WARNING',
            $weight >= 2 => 'CAUTION',
            default      => 'INFO',
        };
    }

    /**
     * Pure rule-based fallback risk calculation.
     *
     * Returns the same structure as the LLM would.
     *
     * @param  array  $context  — output of ContextBuilderService::build()
     * @return array
     */
    public function calculateLocalRisk(array $context): array
    {
        $reasons = [];
        $actions = [];
        $riskLevel = 'Low';

        $obstacle       = $context['obstacle'] ?? null;
        $floodContext    = $context['flood_context'] ?? null;
        $lightCondition = $context['light_condition'] ?? '';
        $proximityStatus = $context['proximity_status'] ?? '';
        $humidityContext = $context['humidity_context'] ?? '';
        $rainContext     = $context['rain_context'] ?? '';
        $speedStatus    = $context['speed_status'] ?? '';

        $dangerThreshold = config('cape.proximity_danger_threshold');

        // --- HIGH risk conditions ---

        // Obstacle within danger threshold
        if ($obstacle !== null && str_contains($obstacle, 'DANGER ZONE')) {
            $riskLevel = 'High';
            $reasons[] = 'Object detected within danger zone proximity';
            $actions[] = 'Initiate emergency braking protocol';
            $actions[] = 'Sound warning horn continuously';
        }

        // Flood risk High or Critical
        if ($floodContext !== null && (str_contains($floodContext, 'High') || str_contains($floodContext, 'Critical'))) {
            $riskLevel = 'High';
            $reasons[] = 'Elevated flood risk in operational area';
            $actions[] = 'Reduce speed and prepare for stop';
            $actions[] = 'Contact control center for route assessment';
        }

        // --- MEDIUM risk conditions (only upgrade if not already High) ---

        if ($riskLevel !== 'High') {
            // Obstacle present but not in danger zone
            if ($obstacle !== null) {
                $riskLevel = 'Medium';
                $reasons[] = 'Object detected near track';
                $actions[] = 'Reduce speed and increase vigilance';
            }

            // Very low light
            if (str_contains($lightCondition, 'Very low light')) {
                $riskLevel = 'Medium';
                $reasons[] = 'Very low ambient light — reduced visibility';
                $actions[] = 'Activate all headlights and marker lights';
            }

            // High humidity combined with rain
            if (str_contains($humidityContext, 'High humidity') && str_contains($rainContext, 'Heavy rain')) {
                $riskLevel = 'Medium';
                $reasons[] = 'High humidity combined with heavy rain — poor conditions';
                $actions[] = 'Reduce speed due to reduced traction';
            }

            // Proximity alert
            if (str_contains($proximityStatus, 'PROXIMITY ALERT')) {
                $riskLevel = 'Medium';
                $reasons[] = 'Proximity sensors detecting nearby objects';
                $actions[] = 'Proceed with caution, reduce speed';
            }
        }

        // --- LOW risk (default) ---
        if ($riskLevel === 'Low') {
            $reasons[] = 'All parameters within normal operating ranges';
            $actions[] = 'Continue normal operations';
            $actions[] = 'Maintain standard monitoring protocols';
        }

        return [
            'risk_level' => $riskLevel,
            'reasons'    => $reasons,
            'actions'    => $actions,
            'prediction' => $this->generateLocalPrediction($context, $riskLevel),
        ];
    }

    /**
     * Generate a simple prediction string from context analysis.
     */
    private function generateLocalPrediction(array $context, string $riskLevel): string
    {
        $predictions = [];

        if (str_contains($context['rain_context'] ?? '', 'Heavy rain')) {
            $predictions[] = 'Continued rain may worsen track conditions';
        }

        if (str_contains($context['light_condition'] ?? '', 'Very low light')) {
            $predictions[] = 'Visibility will remain limited';
        }

        if (! empty($context['obstacle'])) {
            $predictions[] = 'Obstacle may persist — re-assessment recommended';
        }

        if (str_contains($context['flood_context'] ?? '', 'High') || str_contains($context['flood_context'] ?? '', 'Critical')) {
            $predictions[] = 'Flood risk may escalate — monitor water levels';
        }

        if (empty($predictions)) {
            return 'Conditions stable, no immediate risk escalation expected.';
        }

        return implode('. ', $predictions) . '.';
    }
}
