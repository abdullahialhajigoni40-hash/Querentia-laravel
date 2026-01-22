<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', [
                'post_liked', 
                'comment_liked', 
                'comment_replied', 
                'post_reviewed',
                'comment_helpful',
                'review_requested',
                'connection_requested',
                'connection_accepted',
                'journal_published',
                'mention'
            ]);
            $table->json('data'); // JSON data with relevant IDs and info
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};