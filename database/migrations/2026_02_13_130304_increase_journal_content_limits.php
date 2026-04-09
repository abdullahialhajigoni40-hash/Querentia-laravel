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
        Schema::table('journals', function (Blueprint $table) {
            $table->longText('abstract')->nullable()->change();
            $table->longText('introduction')->nullable()->change();
            $table->longText('methodology')->nullable()->change();
            $table->longText('results_discussion')->nullable()->change();
            $table->longText('conclusion')->nullable()->change();
            $table->longText('additional_notes')->nullable()->change();
            $table->longText('ai_generated_content')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->text('abstract')->change();
            $table->text('introduction')->change();
            $table->text('methodology')->change();
            $table->text('results_discussion')->change();
            $table->text('conclusion')->change();
            $table->text('additional_notes')->change();
            $table->text('ai_generated_content')->change();
        });
    }
};
