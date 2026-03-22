<?php

namespace App\Services;

/**
 * Service class for scoring a resume against a job description.
 *
 * Follows SOLID principles and handles deterministic similarity calculations
 * based on classified skills from the AI output.
 */
class ResumeScoringService
{
    /**
     * Calculate the resume match score based on categorized skills.
     *
     * Rules:
     * - Primary skills: 60% weight
     * - Secondary skills: 30% weight
     * - Generic skills: 10% weight
     * - Missing primary skill penalty: Caps score at 40%
     *
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

        // Adjust weights dynamically if a category has 0 skills required
        $weightPrimary = $totalPrimary > 0 ? 60 : 0;
        $weightSecondary = $totalSecondary > 0 ? 30 : 0;
        $weightGeneric = $totalGeneric > 0 ? 10 : 0;

        $totalAvailableWeight = $weightPrimary + $weightSecondary + $weightGeneric;

        // If no skills are required at all, default to 100% (rare edge case)
        if ($totalAvailableWeight === 0) {
            return $this->formatResult(100, $matchedPrimary, $missingPrimary, 'No specific skills required. Defaulting to 100%.');
        }

        // Calculate weighted scores
        $scorePrimary = $totalPrimary > 0 ? (count($matchedPrimary) / $totalPrimary) * $weightPrimary : 0;
        $scoreSecondary = $totalSecondary > 0 ? (count($matchedSecondary) / $totalSecondary) * $weightSecondary : 0;
        $scoreGeneric = $totalGeneric > 0 ? (count($matchedGeneric) / $totalGeneric) * $weightGeneric : 0;

        // Calculate raw score (normalized to 100 in case total available weight < 100)
        $rawScore = ($scorePrimary + $scoreSecondary + $scoreGeneric) / $totalAvailableWeight * 100;

        // Mandatory Skill Enforcement / Language Mismatch Rule
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
     * Format the output to ensure deterministic structure.
     *
     * @param  array<int, string>  $matchedPrimary
     * @param  array<int, string>  $missingPrimary
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
                $filtered[] = $skill;

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
