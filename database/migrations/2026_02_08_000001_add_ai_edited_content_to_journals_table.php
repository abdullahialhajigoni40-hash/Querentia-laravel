<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            if (!Schema::hasColumn('journals', 'ai_edited_content')) {
                $table->longText('ai_edited_content')->nullable()->after('ai_generated_content');
            }
        });
    }

    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            if (Schema::hasColumn('journals', 'ai_edited_content')) {
                $table->dropColumn('ai_edited_content');
            }
        });
    }
};
