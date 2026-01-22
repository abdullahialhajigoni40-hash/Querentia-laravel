<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained()->onDelete('cascade');
            $table->longText('content');
            $table->integer('version_number');
            $table->string('ai_provider')->nullable();
            $table->boolean('is_ai_generated')->default(false);
            $table->json('based_on_feedback')->nullable();
            $table->text('change_summary')->nullable();
            $table->integer('word_count')->default(0);
            $table->integer('reading_time')->default(0);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('parent_version_id')->nullable()->constrained('journal_versions')->onDelete('set null');
            $table->timestamps();
            
            $table->unique(['journal_id', 'version_number']);
            $table->index(['journal_id', 'is_ai_generated']);
            $table->index(['created_by', 'created_at']);
            $table->index('ai_provider');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_versions');
    }
};