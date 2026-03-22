<?php

use App\Services\ResumeScoringService;

test('it accurately computes scores with a 60/30/10 weight', function (): void {
    $service = new ResumeScoringService;

    // 2 primary, 1 secondary, 1 generic
    $data = [
        'matched_primary_skills' => ['Java', 'Spring'],
        'missing_primary_skills' => [],
        'matched_secondary_skills' => ['AWS'],
        'missing_secondary_skills' => [],
        'matched_generic_skills' => ['OOP'],
        'missing_generic_skills' => [],
        'summary' => 'Analysis done.',
    ];

    $result = $service->calculateScore($data);

    // 100% of primary (60) + 100% of secondary (30) + 100% of generic (10) = 100
    expect($result['score'])->toBe(100)
        ->and($result['summary'])->toBe('Analysis done.');
});

test('it handles partial matches accurately', function (): void {
    $service = new ResumeScoringService;

    // 1 of 2 primary = 50% of 60 = 30
    // 1 of 2 secondary = 50% of 30 = 15
    // 0 of 1 generic = 0% of 10 = 0
    // Total raw = 45 = but WAIT, there is a missing primary! So it should cap at 40!
    $data = [
        'matched_primary_skills' => ['Java'],
        'missing_primary_skills' => ['Spring'],
        'matched_secondary_skills' => ['AWS'],
        'missing_secondary_skills' => ['Docker'],
        'matched_generic_skills' => [],
        'missing_generic_skills' => ['OOP'],
        'summary' => 'Analysis done.',
    ];

    $result = $service->calculateScore($data);

    expect($result['score'])->toBe(40)
        ->and($result['summary'])->toContain('Major concern: Candidate is missing core required skills.');
});

test('it calculates score dynamically if a category has no skills required', function (): void {
    $service = new ResumeScoringService;

    // 2 primary (60), 0 secondary, 2 generic (10)
    // Available weight: 70
    // Matched: 1/2 primary (30), 1/2 generic (5)
    // Total Raw: 35 / 70 = 50%
    // BUT missing primary, so capped at 40.

    // Let's test one without missing primary cap
    $data = [
        'matched_primary_skills' => ['Java', 'Spring'],
        'missing_primary_skills' => [],
        'matched_secondary_skills' => [],
        'missing_secondary_skills' => [],
        'matched_generic_skills' => ['OOP'],
        'missing_generic_skills' => ['SOLID'],
        'summary' => 'Analysis done.',
    ];

    $result = $service->calculateScore($data);

    // 60 primary + 5 generic = 65 out of 70 available
    // 65 / 70 * 100 = 92.8 -> 93
    expect($result['score'])->toBe(93);
});

test('it returns 100% if no skills are required', function (): void {
    $service = new ResumeScoringService;

    $data = [
        'matched_primary_skills' => [],
        'missing_primary_skills' => [],
        'matched_secondary_skills' => [],
        'missing_secondary_skills' => [],
        'matched_generic_skills' => [],
        'missing_generic_skills' => [],
    ];

    $result = $service->calculateScore($data);

    expect($result['score'])->toBe(100);
});

test('it filters out common AI placeholder strings', function (): void {
    $service = new ResumeScoringService;

    // The AI might return ["none"] or ["N/A"] when array should be empty
    $data = [
        'matched_primary_skills' => ['Java'],
        'missing_primary_skills' => ['none', 'N/A', 'not specified in resume', '-'],
        'matched_secondary_skills' => ['AWS', 'nothing'],
        'missing_secondary_skills' => [],
        'matched_generic_skills' => [],
        'missing_generic_skills' => [],
    ];

    $result = $service->calculateScore($data);

    // Because 'none', 'N/A', etc. are filtered out, missing_primary_skills is empty.
    // 1 matched primary, 0 missing primary. Weight 60 -> 60
    // 1 matched secondary ('nothing' is filtered out), 0 missing. Weight 30 -> 30
    // 0 generic required -> 0
    // Total raw weight: 90/90 = 100%
    expect($result['score'])->toBe(100)
        ->and($result['missing_primary_skills'])->toBeEmpty()
        ->and($result['recommendation'])->toBe('strong_match');
});

test('it handles non-string values gracefully during placeholder filtering', function (): void {
    $service = new ResumeScoringService;

    // AI hallucinates a number or structure instead of string
    $data = [
        'matched_primary_skills' => ['Java', 123, null],
        'missing_primary_skills' => [],
        'matched_secondary_skills' => [],
        'missing_secondary_skills' => [],
        'matched_generic_skills' => [],
        'missing_generic_skills' => [],
    ];

    $result = $service->calculateScore($data);

    // Should retain 'Java', 123, and null because they aren't placeholder strings.
    // Count is 3. Weight 60 -> score 60.
    // Actually, 'score' will be 100 because it's 3/3 * 60 = 60/60 * 100%
    expect($result['score'])->toBe(100)
        ->and($result['matched_primary_skills'])->toHaveCount(3);
});

test('it maps score accurately to recommendation tiers', function (): void {
    $service = new ResumeScoringService;

    // Test poor match (0-40) - 40% cap due to missing primary
    $poorData = [
        'matched_primary_skills' => ['Java'],
        'missing_primary_skills' => ['Spring'],
        'matched_secondary_skills' => [],
        'missing_secondary_skills' => [],
        'matched_generic_skills' => [],
        'missing_generic_skills' => [],
    ];
    $poorResult = $service->calculateScore($poorData);
    expect($poorResult['score'])->toBe(40)
        ->and($poorResult['recommendation'])->toBe('poor_match');

    // Test average match (41-69)
    $averageData = [
        'matched_primary_skills' => ['Java'],
        'missing_primary_skills' => [],  // weight 60, score 60
        'matched_secondary_skills' => [],
        'missing_secondary_skills' => ['AWS'], // weight 30, score 0
        'matched_generic_skills' => [],
        'missing_generic_skills' => ['OOP'], // weight 10, score 0
    ];
    // Total raw: 60/100 -> 60%
    $averageResult = $service->calculateScore($averageData);
    expect($averageResult['score'])->toBe(60)
        ->and($averageResult['recommendation'])->toBe('average_match');

    // Test good match (70-89)
    $goodData = [
        'matched_primary_skills' => ['Java'],
        'missing_primary_skills' => [], // weight 60, score 60
        'matched_secondary_skills' => ['AWS'],
        'missing_secondary_skills' => ['Docker'], // 1/2 * 30 = 15
        'matched_generic_skills' => ['OOP'],
        'missing_generic_skills' => [], // weight 10, score 10
    ];
    // Total raw: 60 + 15 + 10 = 85
    $goodResult = $service->calculateScore($goodData);
    expect($goodResult['score'])->toBe(85)
        ->and($goodResult['recommendation'])->toBe('good_match');

    // Test strong match (90-100)
    $strongData = [
        'matched_primary_skills' => ['Java'],
        'missing_primary_skills' => [], // 60
        'matched_secondary_skills' => ['AWS'],
        'missing_secondary_skills' => [], // 30
        'matched_generic_skills' => ['OOP'],
        'missing_generic_skills' => [], // 10
    ];
    // Total raw: 100
    $strongResult = $service->calculateScore($strongData);
    expect($strongResult['score'])->toBe(100)
        ->and($strongResult['recommendation'])->toBe('strong_match');
});
