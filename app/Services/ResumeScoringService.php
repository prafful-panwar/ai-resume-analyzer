<?php

namespace App\Services;

class ResumeScoringService
{
    /**
     * @param  array<string, mixed>  $aiData
     * @return array<string, mixed>
     */
    public function calculateScore(array $aiData): array
    {
        $matchedPrimary = $this->filterPlaceholders((array) ($aiData['matched_primary_skills'] ?? []));
        $missingPrimary = $this->filterPlaceholders((array) ($aiData['missing_primary_skills'] ?? []));
        $matchedSecondary = $this->filterPlaceholders((array) ($aiData['matched_secondary_skills'] ?? []));
        $missingSecondary = $this->filterPlaceholders((array) ($aiData['missing_secondary_skills'] ?? []));
        $matchedGeneric = $this->filterPlaceholders((array) ($aiData['matched_generic_skills'] ?? []));
        $missingGeneric = $this->filterPlaceholders((array) ($aiData['missing_generic_skills'] ?? []));

        $totalPrimary = count($matchedPrimary) + count($missingPrimary);
        $totalSecondary = count($matchedSecondary) + count($missingSecondary);
        $totalGeneric = count($matchedGeneric) + count($missingGeneric);

        $weightPrimary = $totalPrimary > 0 ? 60 : 0;
        $weightSecondary = $totalSecondary > 0 ? 30 : 0;
        $weightGeneric = $totalGeneric > 0 ? 10 : 0;

        $totalAvailableWeight = $weightPrimary + $weightSecondary + $weightGeneric;

        if ($totalAvailableWeight === 0) {
            return $this->formatResult(100, $matchedPrimary, $missingPrimary, 'No specific skills required. Defaulting to 100%.');
        }

        $scorePrimary = $totalPrimary > 0 ? (count($matchedPrimary) / $totalPrimary) * $weightPrimary : 0;
        $scoreSecondary = $totalSecondary > 0 ? (count($matchedSecondary) / $totalSecondary) * $weightSecondary : 0;
        $scoreGeneric = $totalGeneric > 0 ? (count($matchedGeneric) / $totalGeneric) * $weightGeneric : 0;

        $rawScore = ($scorePrimary + $scoreSecondary + $scoreGeneric) / $totalAvailableWeight * 100;

        $hasMissingPrimary = count($missingPrimary) > 0;
        $finalScore = $rawScore;
        $summary = $aiData['summary'] ?? 'Calculation complete.';

        if ($hasMissingPrimary) {
            $finalScore = min(40, $rawScore);
            $summary = 'Major concern: Candidate is missing core required skills. '.$summary;
        }

        return $this->formatResult((int) round($finalScore), $matchedPrimary, $missingPrimary, $summary);
    }

    /**
     * @param  array<mixed>  $matchedPrimary
     * @param  array<mixed>  $missingPrimary
     * @return array<string, mixed>
     */
    private function formatResult(int $score, array $matchedPrimary, array $missingPrimary, string $summary): array
    {
        if ($score >= 90) {
            $recommendation = 'strong_match';
        } elseif ($score >= 70) {
            $recommendation = 'good_match';
        } elseif ($score >= 41) {
            $recommendation = 'average_match';
        } else {
            $recommendation = 'poor_match';
        }

        return [
            'score' => $score,
            'recommendation' => $recommendation,
            'matched_primary_skills' => $matchedPrimary,
            'missing_primary_skills' => $missingPrimary,
            'summary' => $summary,
        ];
    }

    /**
     * @param  array<mixed>  $skills
     * @return array<mixed>
     */
    private function filterPlaceholders(array $skills): array
    {
        $filtered = [];
        foreach ($skills as $skill) {
            if (! is_string($skill)) {
                continue;
            }
            $lowercase = strtolower(trim($skill));
            if (! in_array($lowercase, ['none', 'n/a', 'not specified', 'not specified in resume', 'nothing', '-'])) {
                $filtered[] = $skill;
            }
        }

        return $filtered;
    }
}
