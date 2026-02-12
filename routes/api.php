<?php

use App\Http\Controllers\Api\ResumeAnalysisController;
use Illuminate\Support\Facades\Route;

// Original resume analysis endpoint (no authentication required)
Route::post('/analyze-resume', [ResumeAnalysisController::class, 'analyze']);
