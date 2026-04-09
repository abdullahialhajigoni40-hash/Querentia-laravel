<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->string('source_document_disk')->nullable()->after('ai_edited_content');
            $table->string('source_document_path')->nullable()->after('source_document_disk');
            $table->string('source_document_original_name')->nullable()->after('source_document_path');
            $table->string('source_document_mime')->nullable()->after('source_document_original_name');
            $table->unsignedBigInteger('source_document_size')->nullable()->after('source_document_mime');

            $table->longText('source_extracted_text')->nullable()->after('source_document_size');
            $table->unsignedInteger('source_word_count')->nullable()->after('source_extracted_text');
            $table->unsignedInteger('source_page_count')->nullable()->after('source_word_count');

            $table->string('ingestion_status')->nullable()->after('source_page_count');
            $table->unsignedTinyInteger('ingestion_progress')->default(0)->after('ingestion_status');
            $table->text('ingestion_error')->nullable()->after('ingestion_progress');
        });
    }

    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->dropColumn([
                'source_document_disk',
                'source_document_path',
                'source_document_original_name',
                'source_document_mime',
                'source_document_size',
                'source_extracted_text',
                'source_word_count',
                'source_page_count',
                'ingestion_status',
                'ingestion_progress',
                'ingestion_error',
            ]);
        });
    }
};
