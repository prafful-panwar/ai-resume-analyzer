<?php

namespace App\Ai\Agents;

use App\Models\JobDescription;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider('ollama')]
#[Model('qwen2.5:14b')]
#[Timeout(300)]
class ResumeAnalystAgent implements Agent, Conversational, HasStructuredOutput
{
    use Promptable;

    public function __construct(
        public private(set) JobDescription $jobDescription,
        public private(set) string $resumeText
    ) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        /** @var array<int, string> $requirementsArray */
        $requirementsArray = (array) $this->jobDescription->requirements;

        $requirements = empty($requirementsArray)
            ? 'Not specified'
            : implode(', ', $requirementsArray);

        return "Analyze the resume against the job description and provide a matching analysis in JSON format.
CRITICAL: YOUR ENTIRE RESPONSE MUST BE A SINGLE VALID JSON OBJECT. DO NOT INCLUDE ANY EXPLANATION, CONVERSATIONAL TEXT, OR MARKDOWN BACKTICKS.
IF AN ARRAY IS EMPTY, YOU MUST RETURN AN EMPTY ARRAY `[]`. DO NOT RETURN STRINGS LIKE \"none\", \"n/a\", OR \"not specified\".

Job Role: {$this->jobDescription->job_role}
Required Experience: {$this->jobDescription->experience_min}-{$this->jobDescription->experience_max} years
Job Description: {$this->jobDescription->description}
Required Skills: {$requirements}

SKILL CLASSIFICATION RULES:
- Primary Skills: The absolute core, non-negotiable hard skills required for this specific role. For a Developer, this means programming languages and core frameworks (e.g., PHP, Laravel). For an HR role, this means core HR functions (e.g., Talent Acquisition, Sourcing). For a Manager, this means core leadership skills.
- Secondary Skills: Important but not strictly deal-breaking skills (e.g., tools, secondary frameworks, specific software).
- Generic Skills: Common soft skills or generic methodologies (e.g., Communication, Problem-solving, Agile, Teamwork).

Resume:
{$this->resumeText}

Provide your analysis in the following JSON format:
{
    \"candidate_name\": \"extracted name\",
    \"candidate_experience_years\": number,
    \"matched_primary_skills\": [\"must-have primary skill that candidate has\"],
    \"missing_primary_skills\": [\"must-have primary skill that candidate lacks\"],
    \"matched_secondary_skills\": [\"nice-to-have secondary skill that candidate has\"],
    \"missing_secondary_skills\": [\"nice-to-have secondary skill that candidate lacks\"],
    \"matched_generic_skills\": [\"soft/generic skill that candidate has\"],
    \"missing_generic_skills\": [\"soft/generic skill that candidate lacks\"],
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
            'matched_primary_skills' => $schema->array()->items($schema->string())->description('List of core, must-have skills from the job description that the candidate possesses.'),
            'missing_primary_skills' => $schema->array()->items($schema->string())->description('List of core, must-have skills missing from the resume.'),
            'matched_secondary_skills' => $schema->array()->items($schema->string())->description('List of secondary, nice-to-have skills that the candidate possesses.'),
            'missing_secondary_skills' => $schema->array()->items($schema->string())->description('List of secondary, nice-to-have skills missing from the resume.'),
            'matched_generic_skills' => $schema->array()->items($schema->string())->description('List of generic/soft skills (e.g. OOP, SOLID) that the candidate possesses.'),
            'missing_generic_skills' => $schema->array()->items($schema->string())->description('List of generic/soft skills missing from the resume.'),
            'experience_match' => $schema->string()->enum(['matches', 'below', 'above'])->description('How the candidate\'s experience compares to requirements.'),
            'strengths' => $schema->array()->items($schema->string())->description('Key strengths of the candidate relative to the job.'),
            'concerns' => $schema->array()->items($schema->string())->description('Potential concerns or red flags.'),
            'recommendation' => $schema->string()->enum(['perfect_match', 'strong_match', 'good_match', 'partial_match', 'weak_match', 'poor_match'])->description('Final recommendation based on the rubric.'),
            'summary' => $schema->string()->description('A brief summary of the analysis.'),
        ];
    }
}
