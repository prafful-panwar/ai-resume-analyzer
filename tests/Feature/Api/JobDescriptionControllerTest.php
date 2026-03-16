<?php

use App\Models\JobDescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can list their job descriptions via API', function (): void {
    $user = User::factory()->create();
    JobDescription::factory()->count(3)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->getJson('/api/job-descriptions');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'user_id',
                    'job_role',
                    'experience_min',
                    'experience_max',
                    'description',
                    'requirements',
                ],
            ],
        ])
        ->assertJsonCount(3, 'data');
});

test('user can create a job description via API', function (): void {
    $user = User::factory()->create();

    $data = [
        'job_role' => 'Backend Developer',
        'experience_min' => 2,
        'experience_max' => 5,
        'description' => 'API development',
        'requirements' => ['PHP', 'MySQL', 'Redis'],
    ];

    $response = $this->actingAs($user)->postJson('/api/job-descriptions', $data);

    $response->assertStatus(201)
        ->assertJsonFragment([
            'success' => true,
            'message' => 'Job description created successfully',
            'job_role' => 'Backend Developer',
        ]);

    $this->assertDatabaseHas('job_descriptions', [
        'user_id' => $user->id,
        'job_role' => 'Backend Developer',
    ]);
});

test('user can view a specific job description via API', function (): void {
    $user = User::factory()->create();
    $job = JobDescription::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->getJson('/api/job-descriptions/'.$job->id);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'id' => $job->id,
            'job_role' => $job->job_role,
        ]);
});

test('user cannot view another users job description via API', function (): void {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $job = JobDescription::factory()->create(['user_id' => $user2->id]);

    $response = $this->actingAs($user1)->getJson('/api/job-descriptions/'.$job->id);

    $response->assertStatus(403);
});

test('user can update their own job description via API', function (): void {
    $user = User::factory()->create();
    $job = JobDescription::factory()->create(['user_id' => $user->id]);

    $data = [
        'job_role' => 'Lead Backend Developer',
        'experience_min' => 5,
        'experience_max' => 8,
        'description' => 'API development lead',
        'requirements' => ['PHP', 'MySQL', 'Redis', 'Docker'],
    ];

    $response = $this->actingAs($user)->putJson('/api/job-descriptions/'.$job->id, $data);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'success' => true,
            'job_role' => 'Lead Backend Developer',
        ]);

    $this->assertDatabaseHas('job_descriptions', [
        'id' => $job->id,
        'job_role' => 'Lead Backend Developer',
    ]);
});

test('user can delete their own job description via API', function (): void {
    $user = User::factory()->create();
    $job = JobDescription::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->deleteJson('/api/job-descriptions/'.$job->id);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'success' => true,
            'message' => 'Job description deleted successfully',
        ]);

    $this->assertDatabaseMissing('job_descriptions', [
        'id' => $job->id,
    ]);
});

test('job description creation fails via API with missing data', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/job-descriptions', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['job_role', 'experience_min', 'experience_max', 'description']);
});
