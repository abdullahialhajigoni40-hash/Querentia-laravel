<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('institution');
            $table->string('department')->nullable();
            $table->enum('position', ['student', 'researcher', 'lecturer', 'professor', 'phd', 'other']);
            $table->json('research_interests')->nullable();
            $table->string('profile_picture')->nullable();
            $table->text('bio')->nullable();
            $table->enum('subscription_tier', ['free', 'basic', 'pro'])->default('free');
            $table->timestamp('subscription_ends_at')->nullable();
            $table->boolean('is_verified_researcher')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
