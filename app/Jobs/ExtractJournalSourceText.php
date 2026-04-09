<?php

namespace App\Jobs;

use App\Models\Journal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExtractJournalSourceText implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $journalId;

    public function __construct(int $journalId)
    {
        $this->journalId = $journalId;
    }

    public function handle(): void
    {
        $journal = Journal::find($this->journalId);
        if (!$journal) {
            return;
        }

        $journal->update([
            'ingestion_status' => 'processing',
            'ingestion_progress' => 5,
            'ingestion_error' => null,
        ]);

        try {
            if (empty($journal->source_document_disk) || empty($journal->source_document_path)) {
                throw new \RuntimeException('No source document found');
            }

            $disk = $journal->source_document_disk;
            $path = $journal->source_document_path;

            if (!Storage::disk($disk)->exists($path)) {
                throw new \RuntimeException('Source document missing on disk');
            }

            $journal->update(['ingestion_progress' => 15]);

            $mime = strtolower((string) ($journal->source_document_mime ?? ''));
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            $text = '';
            $pageCount = null;

            if ($ext === 'txt' || $mime === 'text/plain') {
                $text = (string) Storage::disk($disk)->get($path);
                $journal->update(['ingestion_progress' => 70]);
            } elseif ($ext === 'docx' || str_contains($mime, 'officedocument.wordprocessingml')) {
                $tmp = tempnam(sys_get_temp_dir(), 'docx_');
                file_put_contents($tmp, Storage::disk($disk)->get($path));
                $journal->update(['ingestion_progress' => 35]);

                $text = $this->extractDocxText($tmp);
                @unlink($tmp);
                $journal->update(['ingestion_progress' => 75]);
            } elseif ($ext === 'pdf' || $mime === 'application/pdf') {
                if (!class_exists('Smalot\\PdfParser\\Parser')) {
                    throw new \RuntimeException('PDF extraction not available. Install a PDF text extraction library (e.g. smalot/pdfparser) to enable this.');
                }

                $tmp = tempnam(sys_get_temp_dir(), 'pdf_');
                file_put_contents($tmp, Storage::disk($disk)->get($path));
                $journal->update(['ingestion_progress' => 35]);

                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($tmp);
                $text = (string) $pdf->getText();

                // Best-effort page count if available
                try {
                    $details = $pdf->getDetails();
                    if (is_array($details) && isset($details['Pages'])) {
                        $pageCount = (int) $details['Pages'];
                    }
                } catch (\Throwable $t) {
                    // ignore
                }

                @unlink($tmp);
                $journal->update(['ingestion_progress' => 80]);
            } else {
                throw new \RuntimeException('Unsupported source document type. Please upload PDF, DOCX, or TXT.');
            }

            $normalized = trim(preg_replace('/\s+/', ' ', str_replace("\0", '', $text)));
            $wordCount = $normalized === '' ? 0 : count(array_filter(explode(' ', $normalized), fn ($w) => $w !== ''));

            $journal->update([
                'source_extracted_text' => $text,
                'source_word_count' => $wordCount,
                'source_page_count' => $pageCount,
                'ingestion_status' => 'completed',
                'ingestion_progress' => 100,
                'ingestion_error' => null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Source extraction failed', [
                'journal_id' => $journal->id,
                'error' => $e->getMessage(),
            ]);

            $journal->update([
                'ingestion_status' => 'failed',
                'ingestion_progress' => 100,
                'ingestion_error' => $e->getMessage(),
            ]);
        }
    }

    private function extractDocxText(string $filePath): string
    {
        $zip = new \ZipArchive();
        $opened = $zip->open($filePath);
        if ($opened !== true) {
            throw new \RuntimeException('Failed to open DOCX file');
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false) {
            throw new \RuntimeException('DOCX file missing document.xml');
        }

        // Replace some DOCX tags with whitespace/newlines, then strip remaining tags
        $xml = preg_replace('/<w:p[^>]*>/', "\n", $xml);
        $xml = preg_replace('/<w:tab\/>/', "\t", $xml);
        $xml = preg_replace('/<w:br\/>/', "\n", $xml);

        $text = strip_tags($xml);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');

        return trim($text);
    }
}
