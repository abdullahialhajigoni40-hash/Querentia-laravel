<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_usage_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('ai_usage_logs', 'estimated_cost')) {
                $table->decimal('estimated_cost', 10, 6)->nullable()->default(0)->after('tokens_used');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ai_usage_logs', function (Blueprint $table) {
            if (Schema::hasColumn('ai_usage_logs', 'estimated_cost')) {
                $table->dropColumn('estimated_cost');
            }
        });
    }
};
