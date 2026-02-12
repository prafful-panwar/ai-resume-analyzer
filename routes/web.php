<?php

use App\Http\Controllers\JobDescriptionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResumeAnalysisController;
use App\Http\Controllers\ResumeMatchingController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

use App\Http\Controllers\DashboardController;

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/resume-analyzer', function () {
    return Inertia::render('ResumeAnalyzer');
})->middleware(['auth', 'verified'])->name('resume-analyzer');

Route::middleware(['auth', 'verified'])->group(function () {
    // Job Descriptions
    Route::resource('job-descriptions', JobDescriptionController::class);

    // Resume Matching
    Route::get('/resume-matching', [ResumeMatchingController::class, 'index'])->name('resume-matching');
    Route::post('/resume-matching', [ResumeMatchingController::class, 'analyze'])->name('resume-matching.analyze');

    // Resume Analyses
    Route::get('/resume-analyses', [ResumeAnalysisController::class, 'index'])->name('resume-analyses.index');
    Route::get('/resume-analyses/{resumeAnalysis}', [ResumeAnalysisController::class, 'show'])->name('resume-analyses.show');
    Route::get('/resume-analyses/{resumeAnalysis}/download', [ResumeAnalysisController::class, 'download'])->name('resume-analyses.download');
    Route::post('/resume-analyses/{resumeAnalysis}/retry', [ResumeAnalysisController::class, 'retry'])->name('resume-analyses.retry');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
