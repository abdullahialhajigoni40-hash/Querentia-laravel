<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('plan', ['basic', 'pro'])->default('basic');
            $table->enum('status', ['active', 'canceled', 'expired', 'pending'])->default('pending');
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('NGN');
            $table->string('paystack_reference')->unique();
            $table->string('paystack_customer_code')->nullable();
            $table->json('paystack_response')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};