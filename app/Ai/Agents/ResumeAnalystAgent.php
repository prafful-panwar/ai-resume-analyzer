<?php

namespace App\Ai\Agents;

use App\Models\JobDescription;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

#[Timeout(300)]
class ResumeAnalystAgent implements Agent, Conversational, HasStructuredOutput
{
    use Promptable;

    public function __construct(
        protected JobDescription $jobDescription,
        protected string $resumeText
    ) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $requirements = is_array($this->jobDescription->requirements) && $this->jobDescription->requirements !== []
            ? implode(', ', $this->jobDescription->requirements)
            : 'Not specified';

        return "Analyze the resume against the job description and provide a matching analysis in JSON format.
CRITICAL: YOUR ENTIRE RESPONSE MUST BE A SINGLE VALID JSON OBJECT. DO NOT INCLUDE ANY EXPLANATION, CONVERSATIONAL TEXT, OR MARKDOWN BACKTICKS.

Job Role: {$this->jobDescription->job_role}
Required Experience: {$this->jobDescription->experience_min}-{$this->jobDescription->experience_max} years
Job Description: {$this->jobDescription->description}
Required Skills: {$requirements}

Resume:
{$this->resumeText}

Provide your analysis in the following JSON format:
{
    \"candidate_name\": \"extracted name\",
    \"candidate_experience_years\": number,
    \"match_score\": 0-100,
    \"matched_skills\": [\"skill1\", \"skill2\"],
    \"missing_skills\": [\"skill1\", \"skill2\"],
    \"experience_match\": \"matches|below|above\",
    \"strengths\": [\"strength1\", \"strength2\"],
    \"concerns\": [\"concern1\", \"concern2\"],
    \"recommendation\": \"perfect_match|strong_match|good_match|partial_match|weak_match|poor_match\",
    \"summary\": \"brief summary\"
}";
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return iterable<int, mixed>
     */
    public function messages(): iterable
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'candidate_name' => $schema->string()->description('The name of the candidate extracted from the resume.'),
            'candidate_experience_years' => $schema->number()->description('Total years of relevant experience.'),
            'match_score' => $schema->integer()->description('A score from 0 to 100 indicating how well the candidate matches the job.'),
            'matched_skills' => $schema->array()->items($schema->string())->description('List of skills from the job description that the candidate possesses.'),
            'missing_skills' => $schema->array()->items($schema->string())->description('List of required skills missing from the resume.'),
            'experience_match' => $schema->string()->enum(['matches', 'below', 'above'])->description('How the candidate\'s experience compares to requirements.'),
            'strengths' => $schema->array()->items($schema->string())->description('Key strengths of the candidate relative to the job.'),
            'concerns' => $schema->array()->items($schema->string())->description('Potential concerns or red flags.'),
            'recommendation' => $schema->string()->enum(['perfect_match', 'strong_match', 'good_match', 'partial_match', 'weak_match', 'poor_match'])->description('Final recommendation based on the rubric.'),
            'summary' => $schema->string()->description('A brief summary of the analysis.'),
        ];
    }
}
