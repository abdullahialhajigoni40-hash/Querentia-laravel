<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_usage_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('ai_usage_logs', 'journal_id')) {
                $table->unsignedBigInteger('journal_id')->nullable()->after('user_id');
                $table->index('journal_id');
                $table->foreign('journal_id')->references('id')->on('journals')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ai_usage_logs', function (Blueprint $table) {
            if (Schema::hasColumn('ai_usage_logs', 'journal_id')) {
                $table->dropForeign(['journal_id']);
                $table->dropIndex(['journal_id']);
                $table->dropColumn('journal_id');
            }
        });
    }
};
