<?php

use App\Http\Controllers\Api\JobDescriptionController;
use App\Http\Controllers\Api\ResumeAnalysisController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('job-descriptions', JobDescriptionController::class)->names('api.job-descriptions');
});

// Original resume analysis endpoint (no authentication required)
Route::post('/analyze-resume', [ResumeAnalysisController::class, 'analyze']);
