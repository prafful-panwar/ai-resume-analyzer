<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnalyzeResumeRequest;
use Exception;
use Illuminate\Http\JsonResponse;

use function Laravel\Ai\agent;

class ResumeAnalysisController extends Controller
{
    /**
     * Analyze resume text using AI to extract structured data.
     */
    public function analyze(AnalyzeResumeRequest $request): JsonResponse
    {
        try {
            $resumeText = $request->input('resume_text');

            // System prompt for AI
            $systemPrompt = 'You are an expert HR recruiter. Extract structured data from resume. Return only valid JSON.';

            // Use Laravel AI SDK with anonymous agent
            $response = agent(
                instructions: $systemPrompt,
            )->prompt($resumeText);

            // Get the AI response content
            $aiResponse = $response->text;

            // Try to decode JSON response from AI
            $structuredData = json_decode($aiResponse, true);

            // If JSON decode fails, return raw response
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'raw_response' => $aiResponse,
                        'note' => 'AI did not return valid JSON. Showing raw response.',
                    ],
                ], 200);
            }

            return response()->json([
                'success' => true,
                'data' => $structuredData,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to analyze resume',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
