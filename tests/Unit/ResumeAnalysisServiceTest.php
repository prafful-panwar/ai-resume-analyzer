<?php

namespace Tests\Unit;

use App\Ai\Agents\ResumeAnalystAgent;
use App\Models\JobDescription;
use App\Models\ResumeAnalysis;
use App\Models\User;
use App\Services\ResumeAnalysisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\Usage;
use Laravel\Ai\Responses\StructuredAgentResponse;
use Mockery;
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Parser;
use Tests\TestCase;

class ResumeAnalysisServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_perform_analysis_successful(): void
    {
        Storage::fake('local');

        // Mock PDF Parser
        $documentMock = Mockery::mock(Document::class);
        $documentMock->shouldReceive('getText')->andReturn('Resume Content for John Doe'); // @phpstan-ignore-line

        $parserMock = Mockery::mock(Parser::class);
        $parserMock->shouldReceive('parseFile')->andReturn($documentMock); // @phpstan-ignore-line

        $this->app->instance(Parser::class, $parserMock);

        // Mock Agent Response
        $expectedData = [
            'candidate_name' => 'John Doe',
            'candidate_experience_years' => 5,
            'match_score' => 85,
            'matched_skills' => ['PHP', 'Laravel'],
            'missing_skills' => ['Vue.js'],
            'experience_match' => 'matches',
            'strengths' => ['Backend development'],
            'concerns' => [],
            'recommendation' => 'strong_match',
            'summary' => 'A good candidate.',
        ];

        // Create a real StructuredAgentResponse object
        $usage = new Usage(promptTokens: 100, completionTokens: 200);
        $meta = new Meta('1', 'model');
        $realResponse = new StructuredAgentResponse('123', $expectedData, 'json text', $usage, $meta);

        // Mock the Agent class
        $agentMock = Mockery::mock(ResumeAnalystAgent::class);

        // Expect prompt to be called.
        /** @phpstan-ignore-next-line */
        $agentMock->shouldReceive('prompt')
            ->withAnyArgs()
            ->andReturn($realResponse);

        // Bind the mock to the container so app()->makeWith(...) returns it
        $this->app->bind(ResumeAnalystAgent::class, fn () => $agentMock);

        $user = User::factory()->create();
        $jobDescription = JobDescription::factory()->create(['user_id' => $user->id]);

        $file = UploadedFile::fake()->create('resume.pdf', 100);
        $path = $file->store('resumes', 'local');

        $analysis = ResumeAnalysis::create([
            'user_id' => $user->id,
            'job_description_id' => $jobDescription->id,
            'resume_file_path' => $path,
            'original_filename' => 'resume.pdf',
            'status' => 'pending',
        ]);

        $service = new ResumeAnalysisService;
        $result = $service->performAnalysis($analysis);

        $this->assertEquals('completed', $result->status);
        $this->assertNotNull($result->result);
        /** @var array<string, mixed> $resultData */
        $resultData = $result->result;
        $this->assertEquals('John Doe', $resultData['candidate_name']);
        $this->assertEquals(85, $resultData['match_score']);
    }
}
