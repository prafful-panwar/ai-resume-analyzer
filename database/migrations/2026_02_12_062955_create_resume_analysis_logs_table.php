<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('resume_analysis_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resume_analysis_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('failed');
            $table->text('error_message')->nullable();
            $table->json('result')->nullable();
            $table->integer('attempt')->default(1);
            $table->string('job_uuid')->nullable();
            $table->longText('exception_trace')->nullable();
            $table->integer('prompt_tokens')->nullable();
            $table->integer('completion_tokens')->nullable();
            $table->integer('total_tokens')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resume_analysis_logs');
    }
};
