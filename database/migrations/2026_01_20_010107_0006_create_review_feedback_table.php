<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('journal_id')->constrained()->onDelete('cascade');
            $table->foreignId('post_id')->nullable()->constrained('network_posts')->onDelete('cascade');
            $table->text('comment');
            $table->text('suggestions')->nullable();
            $table->tinyInteger('rating')->nullable()->unsigned();
            $table->string('type')->default('general');
            $table->string('status')->default('pending');
            $table->integer('helpful_count')->default(0);
            $table->boolean('addressed')->default(false);
            $table->timestamp('addressed_at')->nullable();
            $table->timestamps();
            
            $table->index(['journal_id', 'status']);
            $table->index(['user_id', 'created_at']);
            $table->index(['post_id', 'type']);
            $table->index(['addressed', 'created_at']);
            $table->index('rating');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_feedback');
    }
};