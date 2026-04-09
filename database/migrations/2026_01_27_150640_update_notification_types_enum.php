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
        Schema::table('notifications', function (Blueprint $table) {
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
                'mention',
                // New notification types
                'connection_request',
                'review_completed',
                'ai_processing_complete',
                'comment_received',
                'like_received'
            ])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
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
            ])->change();
        });
    }
};
