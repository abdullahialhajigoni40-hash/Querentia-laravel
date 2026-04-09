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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('avatar')->nullable();
            $table->enum('type', ['public', 'private'])->default('public');
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->integer('members_count')->default(1);
            $table->integer('messages_count')->default(0);
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
            
            $table->index(['creator_id', 'status']);
            $table->index(['type', 'status']);
            $table->index(['last_message_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
