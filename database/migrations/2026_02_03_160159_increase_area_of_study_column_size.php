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
        // Columns already modified via raw SQL
        // area_of_study, additional_notes -> TEXT
        // methodology, results_discussion, conclusion -> LONGTEXT
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->string('area_of_study')->change();
            $table->string('additional_notes')->change();
            $table->string('methodology')->change();
            $table->string('results_discussion')->change();
            $table->string('conclusion')->change();
        });
    }
};
