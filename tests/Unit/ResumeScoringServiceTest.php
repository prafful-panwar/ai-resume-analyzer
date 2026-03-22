<?php

use App\Services\ResumeScoringService;

test('it accurately computes scores with a 60/30/10 weight', function (): void {
    $service = new ResumeScoringService;

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

    expect($result['score'])->toBe(100)
        ->and($result['summary'])->toBe('Analysis done.');
});

test('it handles partial matches accurately', function (): void {
    $service = new ResumeScoringService;

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

    $data = [
        'matched_primary_skills' => ['Java'],
        'missing_primary_skills' => ['none', 'N/A', 'not specified in resume', '-'],
        'matched_secondary_skills' => ['AWS', 'nothing'],
        'missing_secondary_skills' => [],
        'matched_generic_skills' => [],
        'missing_generic_skills' => [],
    ];

    $result = $service->calculateScore($data);

    expect($result['score'])->toBe(100)
        ->and($result['missing_primary_skills'])->toBeEmpty()
        ->and($result['recommendation'])->toBe('strong_match');
});

test('it handles non-string values gracefully during placeholder filtering', function (): void {
    $service = new ResumeScoringService;

    $data = [
        'matched_primary_skills' => ['Java', 123, null],
        'missing_primary_skills' => [],
        'matched_secondary_skills' => [],
        'missing_secondary_skills' => [],
        'matched_generic_skills' => [],
        'missing_generic_skills' => [],
    ];

    $result = $service->calculateScore($data);

    expect($result['score'])->toBe(100)
        ->and($result['matched_primary_skills'])->toHaveCount(1);
});

test('it maps score accurately to recommendation tiers', function (): void {
    $service = new ResumeScoringService;

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

    $averageData = [
        'matched_primary_skills' => ['Java'],
        'missing_primary_skills' => [],
        'matched_secondary_skills' => [],
        'missing_secondary_skills' => ['AWS'],
        'matched_generic_skills' => [],
        'missing_generic_skills' => ['OOP'],
    ];

    $averageResult = $service->calculateScore($averageData);
    expect($averageResult['score'])->toBe(60)
        ->and($averageResult['recommendation'])->toBe('average_match');

    $goodData = [
        'matched_primary_skills' => ['Java'],
        'missing_primary_skills' => [],
        'matched_secondary_skills' => ['AWS'],
        'missing_secondary_skills' => ['Docker'],
        'matched_generic_skills' => ['OOP'],
        'missing_generic_skills' => [],
    ];

    $goodResult = $service->calculateScore($goodData);
    expect($goodResult['score'])->toBe(85)
        ->and($goodResult['recommendation'])->toBe('good_match');

    $strongData = [
        'matched_primary_skills' => ['Java'],
        'missing_primary_skills' => [],
        'matched_secondary_skills' => ['AWS'],
        'missing_secondary_skills' => [],
        'matched_generic_skills' => ['OOP'],
        'missing_generic_skills' => [],
    ];

    $strongResult = $service->calculateScore($strongData);
    expect($strongResult['score'])->toBe(100)
        ->and($strongResult['recommendation'])->toBe('strong_match');
});
