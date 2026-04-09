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
        Schema::create('peer_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained()->onDelete('cascade');
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
            $table->text('comments')->nullable();
            $table->json('annotations')->nullable();
            $table->decimal('rating', 3, 1)->nullable(); // 0.0 to 5.0
            $table->string('status')->default('pending'); // pending, in_progress, completed
            $table->boolean('is_anonymous')->default(false);
            $table->datetime('submitted_at')->nullable();
            $table->datetime('due_date');
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['reviewer_id', 'status']);
            $table->index(['journal_id', 'status']);
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peer_reviews');
    }
};
