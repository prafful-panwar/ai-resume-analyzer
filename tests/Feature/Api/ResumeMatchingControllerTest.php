<?php

use App\Models\JobDescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('user cannot analyze resume against another users job description', function (): void {
    Storage::fake('local');

    $owner = User::factory()->create();
    $attacker = User::factory()->create();

    $jobDescription = JobDescription::factory()->create(['user_id' => $owner->id]);

    $response = $this->actingAs($attacker)
        ->post(route('resume-matching.analyze'), [
            'job_description_id' => $jobDescription->id,
            'resume' => UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf'),
        ]);

    // FormRequest Rule::exists with user_id check fails first and redirects back
    $response->assertStatus(302);
    $response->assertSessionHasErrors(['job_description_id']);
});

test('guest cannot access analyze endpoint', function (): void {
    $response = $this->post(route('resume-matching.analyze'), []);

    $response->assertRedirect(route('login'));
});
