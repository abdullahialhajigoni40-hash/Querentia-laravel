<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

class PDFGenerationService
{
    public function generateJournalPDF($journal, $version = null)
    {
        $content = $version ? $version->content : $journal->ai_generated_content;
        
        $html = $this->formatJournalHTML($journal, $content);
        
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Save to storage
        $filename = 'journal_' . $journal->id . '_' . time() . '.pdf';
        $path = storage_path('app/public/journals/' . $filename);
        
        file_put_contents($path, $dompdf->output());
        
        return [
            'path' => $path,
            'filename' => $filename,
            'url' => asset('storage/journals/' . $filename)
        ];
    }
    
    private function formatJournalHTML($journal, $content)
    {
        // Convert markdown/text to HTML with academic formatting
        // Use the exact template from the example PDF
        
        return view('pdf.journal-template', [
            'journal' => $journal,
            'content' => $content,
        ])->render();
    }
}