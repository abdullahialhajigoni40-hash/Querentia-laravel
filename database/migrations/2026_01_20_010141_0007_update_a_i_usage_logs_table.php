<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only alter if table exists
        if (!Schema::hasTable('ai_usage_logs')) {
            // Create the table if it doesn't exist
            Schema::create('ai_usage_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
                $table->string('provider'); // deepseek, openai, etc
                $table->string('model')->nullable();
                $table->text('prompt')->nullable();
                $table->text('response')->nullable();
                $table->integer('tokens_used')->default(0);
                $table->decimal('cost', 8, 6)->default(0);
                $table->decimal('response_time', 8, 3)->nullable();
                $table->boolean('success')->default(true);
                $table->text('error_message')->nullable();
                $table->string('task_type')->nullable();
                $table->timestamps();
                
                // Indexes
                $table->index(['user_id', 'created_at']);
                $table->index(['provider', 'success']);
                $table->index('task_type');
                $table->index('created_at');
            });
        } else {
            // Table exists, add missing columns
            Schema::table('ai_usage_logs', function (Blueprint $table) {
                // Add missing columns
                if (!Schema::hasColumn('ai_usage_logs', 'response_time')) {
                    $table->decimal('response_time', 8, 3)->nullable();
                }
                
                if (!Schema::hasColumn('ai_usage_logs', 'success')) {
                    $table->boolean('success')->default(true);
                }
                
                if (!Schema::hasColumn('ai_usage_logs', 'error_message')) {
                    $table->text('error_message')->nullable();
                }
                
                if (!Schema::hasColumn('ai_usage_logs', 'task_type')) {
                    $table->string('task_type')->nullable();
                }
            });
            
            // Add indexes if they don't exist
            Schema::table('ai_usage_logs', function (Blueprint $table) {
                try {
                    $table->index(['user_id', 'created_at']);
                } catch (\Exception $e) {
                    // Index may already exist
                }
                try {
                    $table->index(['provider', 'success']);
                } catch (\Exception $e) {
                    // Index may already exist
                }
                try {
                    $table->index('task_type');
                } catch (\Exception $e) {
                    // Index may already exist
                }
                try {
                    $table->index('created_at');
                } catch (\Exception $e) {
                    // Index may already exist
                }
            });
        }
    }

    public function down(): void
    {
        // Only alter if table exists
        if (!Schema::hasTable('ai_usage_logs')) {
            return;
        }

        Schema::table('ai_usage_logs', function (Blueprint $table) {
            // Remove columns safely
            $columns = ['response_time', 'success', 'error_message', 'task_type'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('ai_usage_logs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
        
        // Try to drop indexes (they may not exist)
        Schema::table('ai_usage_logs', function (Blueprint $table) {
            try {
                $table->dropIndex(['user_id', 'created_at']);
            } catch (\Exception $e) {
                // Index may not exist
            }
            try {
                $table->dropIndex(['provider', 'success']);
            } catch (\Exception $e) {
                // Index may not exist
            }
            try {
                $table->dropIndex(['task_type']);
            } catch (\Exception $e) {
                // Index may not exist
            }
            try {
                $table->dropIndex(['created_at']);
            } catch (\Exception $e) {
                // Index may not exist
            }
        });
    }
};