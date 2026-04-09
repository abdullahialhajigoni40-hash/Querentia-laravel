<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journal_images', function (Blueprint $table) {
            $table->string('kind')->default('figure')->after('journal_id');
            $table->unsignedInteger('sort_order')->default(0)->after('kind');
            $table->string('caption')->nullable()->after('original_name');
            $table->string('source')->nullable()->after('caption');
        });
    }

    public function down(): void
    {
        Schema::table('journal_images', function (Blueprint $table) {
            $table->dropColumn(['kind', 'sort_order', 'caption', 'source']);
        });
    }
};
