<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            
            // User relationship
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Basic information
            $table->string('title');
            $table->string('slug')->unique();
            
            // Content sections
            $table->text('abstract')->nullable();
            $table->json('authors')->nullable();
            $table->text('introduction')->nullable();
            $table->string('area_of_study')->nullable();
            $table->text('additional_notes')->nullable();
            $table->text('methodology')->nullable();
            $table->text('results_discussion')->nullable();
            $table->text('conclusion')->nullable();
            $table->json('references')->nullable();
            
            // Annexes and figures
            $table->json('annexes')->nullable();
            $table->json('maps_figures')->nullable();
            
            // AI processing fields
            $table->longText('ai_generated_content')->nullable();
            $table->json('original_sections')->nullable();
            $table->json('raw_content')->nullable();
            
            // Journal metadata
            $table->string('journal_name')->nullable();
            $table->string('journal_template')->default('natural-sciences');
            
            // Status and workflow
            $table->enum('status', [
                'draft', 
                'ai_processing', 
                'ai_draft', 
                'under_review', 
                'revised', 
                'published',
                'ai_failed',
                'archived'
            ])->default('draft');
            
            // AI usage tracking
            $table->string('ai_provider_used')->nullable();
            $table->integer('ai_usage_count')->default(0);
            $table->decimal('ai_percentage', 5, 2)->default(0);
            
            // Will add current_version_id in separate migration after journal_versions exists
            
            // Timestamps
            $table->timestamp('published_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('posted_for_review_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('slug');
            $table->index('ai_provider_used');
            $table->index('posted_for_review_at');
            $table->index(['area_of_study', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};