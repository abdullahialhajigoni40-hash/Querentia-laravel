<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('comments')->onDelete('cascade');
            $table->text('content');
            $table->boolean('is_review')->default(false);
            $table->decimal('rating', 3, 2)->nullable(); // For reviews: 1.00 - 5.00
            $table->json('review_criteria')->nullable(); // JSON for structured review
            $table->boolean('is_helpful')->default(false);
            $table->integer('helpful_count')->default(0);
            $table->integer('replies_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};