<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('connected_user_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'accepted', 'rejected', 'blocked'])->default('pending');
            $table->text('message')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'connected_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_connections');
    }
};