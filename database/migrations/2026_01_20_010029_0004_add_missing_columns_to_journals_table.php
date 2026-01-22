<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            // Add missing content columns
            if (!Schema::hasColumn('journals', 'formatted_content')) {
                $table->longText('formatted_content')->nullable();
            }
            
            if (!Schema::hasColumn('journals', 'html_content')) {
                $table->longText('html_content')->nullable();
            }
            
            if (!Schema::hasColumn('journals', 'pdf_path')) {
                $table->string('pdf_path')->nullable();
            }
            
            if (!Schema::hasColumn('journals', 'materials_methods')) {
                $table->text('materials_methods')->nullable();
            }
            
            // Add extra indexes if needed
            if (!Schema::hasIndex('journals', ['user_id', 'created_at'])) {
                $table->index(['user_id', 'created_at']);
            }
            
            if (!Schema::hasIndex('journals', ['status', 'updated_at'])) {
                $table->index(['status', 'updated_at']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            // Remove columns
            $columns = [
                'formatted_content',
                'html_content',
                'pdf_path',
                'materials_methods',
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('journals', $column)) {
                    $table->dropColumn($column);
                }
            }
            
            // Drop indexes
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['status', 'updated_at']);
        });
    }
};