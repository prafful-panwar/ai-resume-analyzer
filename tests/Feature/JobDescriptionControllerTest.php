<?php

use App\Models\JobDescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('guest cannot access job descriptions index', function (): void {
    $response = $this->get(route('job-descriptions.index'));
    $response->assertRedirect(route('login'));
});

test('user can access job descriptions index', function (): void {
    $user = User::factory()->create();
    $job1 = JobDescription::factory()->create(['user_id' => $user->id]);
    $job2 = JobDescription::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->get(route('job-descriptions.index'));

    $response->assertStatus(200)
        ->assertInertia(
            fn (Assert $page): Assert => $page
                ->component('JobDescriptions/Index')
                ->has('jobDescriptions', 2)
        );
});

test('user can view the create form', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('job-descriptions.create'));

    $response->assertStatus(200)
        ->assertInertia(
            fn (Assert $page): Assert => $page
                ->component('JobDescriptions/Form')
        );
});

test('user can store a new job description', function (): void {
    $user = User::factory()->create();

    $data = [
        'job_role' => 'Software Engineer',
        'experience_min' => 2,
        'experience_max' => 5,
        'description' => 'This is a description',
        'requirements' => ['PHP', 'Laravel', 'Vue.js'],
    ];

    $response = $this->actingAs($user)
        ->post(route('job-descriptions.store'), $data);

    $response->assertRedirect(route('job-descriptions.index'))
        ->assertSessionHas('success', 'Job description created successfully');

    $this->assertDatabaseHas('job_descriptions', [
        'user_id' => $user->id,
        'job_role' => 'Software Engineer',
        'experience_min' => 2,
        'experience_max' => 5,
        'description' => 'This is a description',
    ]);
});

test('user can view the edit form for their own job description', function (): void {
    $user = User::factory()->create();
    $job = JobDescription::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->get(route('job-descriptions.edit', $job));

    $response->assertStatus(200)
        ->assertInertia(
            fn (Assert $page): Assert => $page
                ->component('JobDescriptions/Form')
                ->has(
                    'jobDescription',
                    fn (Assert $page): Assert => $page
                        ->where('id', $job->id)
                        ->where('job_role', $job->job_role)
                        ->etc()
                )
        );
});

test('user cannot view the edit form for another user job description', function (): void {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $job = JobDescription::factory()->create(['user_id' => $user2->id]);

    $response = $this->actingAs($user1)
        ->get(route('job-descriptions.edit', $job));

    $response->assertStatus(403);
});

test('user can update their own job description', function (): void {
    $user = User::factory()->create();
    $job = JobDescription::factory()->create(['user_id' => $user->id]);

    $data = [
        'job_role' => 'Senior Software Engineer',
        'experience_min' => 4,
        'experience_max' => 8,
        'description' => 'Updated description',
        'requirements' => ['PHP', 'Laravel', 'Vue.js', 'React'],
    ];

    $response = $this->actingAs($user)
        ->put(route('job-descriptions.update', $job), $data);

    $response->assertRedirect(route('job-descriptions.index'))
        ->assertSessionHas('success', 'Job description updated successfully.');

    $this->assertDatabaseHas('job_descriptions', [
        'id' => $job->id,
        'job_role' => 'Senior Software Engineer',
        'experience_min' => 4,
        'experience_max' => 8,
    ]);
});

test('user cannot update another user job description', function (): void {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $job = JobDescription::factory()->create(['user_id' => $user2->id]);

    $data = [
        'job_role' => 'Senior Software Engineer',
        'experience_min' => 4,
        'experience_max' => 8,
        'description' => 'Updated description',
        'requirements' => ['PHP'],
    ];

    $response = $this->actingAs($user1)
        ->put(route('job-descriptions.update', $job), $data);

    $response->assertStatus(403);
});

test('user can delete their own job description', function (): void {
    $user = User::factory()->create();
    $job = JobDescription::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->delete(route('job-descriptions.destroy', $job));

    $response->assertRedirect(route('job-descriptions.index'))
        ->assertSessionHas('success', 'Job description deleted successfully.');

    $this->assertDatabaseMissing('job_descriptions', [
        'id' => $job->id,
    ]);
});

test('user cannot delete another user job description', function (): void {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $job = JobDescription::factory()->create(['user_id' => $user2->id]);

    $response = $this->actingAs($user1)
        ->delete(route('job-descriptions.destroy', $job));

    $response->assertStatus(403);

    $this->assertDatabaseHas('job_descriptions', [
        'id' => $job->id,
    ]);
});
