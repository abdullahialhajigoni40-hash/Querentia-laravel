<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.5;
            margin: 2cm;
        }
        .journal-header {
            text-align: center;
            margin-bottom: 2cm;
        }
        .journal-title {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 0.5cm;
        }
        .issn {
            font-size: 10pt;
            margin-bottom: 0.5cm;
        }
        .volume {
            font-size: 10pt;
            margin-bottom: 0.5cm;
        }
        .url {
            font-size: 10pt;
            margin-bottom: 1cm;
        }
        .paper-title {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin: 1cm 0;
        }
        .authors {
            text-align: center;
            font-size: 11pt;
            margin-bottom: 1cm;
        }
        .affiliations {
            text-align: center;
            font-size: 10pt;
            margin-bottom: 1.5cm;
        }
        .section {
            margin-top: 1cm;
            margin-bottom: 0.5cm;
        }
        .section-title {
            font-weight: bold;
            font-size: 12pt;
            margin-bottom: 0.5cm;
        }
        .abstract {
            text-align: justify;
            margin-bottom: 0.5cm;
        }
        .keywords {
            font-style: italic;
            margin-bottom: 1cm;
        }
        .reference {
            margin-left: 1cm;
            text-indent: -1cm;
            font-size: 10pt;
            margin-bottom: 0.3cm;
        }
        .page-number {
            position: fixed;
            bottom: 1cm;
            right: 2cm;
            font-size: 10pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 1cm 0;
            font-size: 10pt;
        }
        th, td {
            border: 1px solid #000;
            padding: 0.3cm;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .table-caption {
            text-align: center;
            font-style: italic;
            margin-top: 0.3cm;
            font-size: 10pt;
        }
    </style>
</head>
<body>
    @php
        $toText = function ($value): string {
            if (is_null($value)) {
                return '';
            }

            if (is_bool($value)) {
                return $value ? '1' : '0';
            }

            if (is_array($value)) {
                $flat = [];
                $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($value));
                foreach ($iterator as $v) {
                    if (is_scalar($v) || is_null($v)) {
                        $flat[] = (string) $v;
                    }
                }
                $text = implode("\n", $flat);
            } else {
                $text = (string) $value;
            }

            $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $text = strip_tags($text);
            
            // Clean markdown formatting
            $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $text); // Remove **bold**
            $text = preg_replace('/\*\*(.*?)\*\*/', '$2', $text); // Remove **bold**
            $text = preg_replace('/\*(.*?)\*/', '$1', $text);     // Remove *italic*
            $text = preg_replace('/#{1,6}\s*(.*)/', '$1', $text);   // Remove # headers
            $text = preg_replace('/\[([^\]]+)\]\([^\)]+\)/', '$1', $text); // Remove [text](url) links
            $text = preg_replace('/`([^`]+)`/', '$1', $text);       // Remove `code`
            $text = preg_replace('/```([\s\S]*?)```/', '$1', $text); // Remove ```code blocks```
            
            // Clean up extra whitespace
            $text = preg_replace('/\n{3,}/', '\n\n', $text);     // Reduce multiple newlines
            $text = trim($text);
            
            return $text;
        };
    @endphp

    <div class="paper-title">
        <?php echo $toText($journal->title); ?>
    </div>

    <!-- Authors -->
    <div class="authors">
        <?php if(!empty($content['authors'])): ?>
            <?php echo nl2br(htmlspecialchars($toText($content['authors']))); ?>
        <?php else: ?>
            <?php echo $toText($journal->user->full_name); ?>
        <?php endif; ?>
    </div>

    <!-- Affiliations -->
    <div class="affiliations">
        <?php echo $toText($journal->user->institution); ?>
        <?php if($journal->user->department): ?>
            • <?php echo $toText($journal->user->department); ?>
        <?php endif; ?>
    </div>



    <!-- Keywords -->
    <?php if(!empty($content['keywords'] ?? null)): ?>
        <div class="keywords">
            <strong>Keywords:</strong> <?php echo nl2br(htmlspecialchars($toText($content['keywords']))); ?>
        </div>
    <?php endif; ?>

    <!-- Abstract -->
    <?php if(!empty($content['abstract'] ?? null)): ?>
        <div class="section">
            <div class="section-title">ABSTRACT</div>
            <div class="abstract">
                <?php if(!empty($content['abstract_html'])): ?>
                    <?php echo $content['abstract_html']; ?>
                <?php else: ?>
                    <?php echo nl2br(htmlspecialchars($toText($content['abstract']))); ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Introduction -->
    <?php if(!empty($content['introduction'] ?? null)): ?>
        <div class="section">
            <div class="section-title">INTRODUCTION</div>
            <div>
                <?php if(!empty($content['introduction_html'])): ?>
                    <?php echo $content['introduction_html']; ?>
                <?php else: ?>
                    <?php echo nl2br(htmlspecialchars($toText($content['introduction']))); ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Area of Study -->
    <?php if(!empty($content['area_of_study'] ?? null)): ?>
        <div class="section">
            <div class="section-title">AREA OF STUDY</div>
            <div>
                <?php if(!empty($content['area_of_study_html'])): ?>
                    <?php echo $content['area_of_study_html']; ?>
                <?php else: ?>
                    <?php echo nl2br(htmlspecialchars($toText($content['area_of_study']))); ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Methodology -->
    <?php if(!empty($content['methodology'] ?? null)): ?>
        <div class="section">
            <div class="section-title">METHODOLOGY</div>
            <div>
                <?php if(!empty($content['methodology_html'])): ?>
                    <?php echo $content['methodology_html']; ?>
                <?php else: ?>
                    <?php echo nl2br(htmlspecialchars($toText($content['methodology']))); ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Results & Discussion -->
    <?php if(!empty($content['results_discussion'] ?? null)): ?>
        <div class="section">
            <div class="section-title">RESULTS & DISCUSSION</div>
            <div>
                <?php if(!empty($content['results_discussion_html'])): ?>
                    <?php echo $content['results_discussion_html']; ?>
                <?php else: ?>
                    <?php echo nl2br(htmlspecialchars($toText($content['results_discussion']))); ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Conclusion -->
    <?php if(!empty($content['conclusion'] ?? null)): ?>
        <div class="section">
            <div class="section-title">CONCLUSION</div>
            <div>
                <?php if(!empty($content['conclusion_html'])): ?>
                    <?php echo $content['conclusion_html']; ?>
                <?php else: ?>
                    <?php echo nl2br(htmlspecialchars($toText($content['conclusion']))); ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Additional Notes -->
    <?php if(!empty($content['additional_notes'] ?? null)): ?>
        <div class="section">
            <div class="section-title">ADDITIONAL NOTES</div>
            <div>
                <?php if(!empty($content['additional_notes_html'])): ?>
                    <?php echo $content['additional_notes_html']; ?>
                <?php else: ?>
                    <?php echo nl2br(htmlspecialchars($toText($content['additional_notes']))); ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php
        $hasBody = !empty($content['abstract'] ?? null)
            || !empty($content['introduction'] ?? null)
            || !empty($content['area_of_study'] ?? null)
            || !empty($content['methodology'] ?? null)
            || !empty($content['results_discussion'] ?? null)
            || !empty($content['references'] ?? null)
            || !empty($content['conclusion'] ?? null);
    ?>

    <?php if(!$hasBody && !empty($content['ai_generated_content'] ?? $journal->ai_generated_content ?? null)): ?>
        <div class="section">
            <div class="section-title"></div>
            <div>
                <?php if(!empty($content['ai_generated_content_html'])): ?>
                    <?php echo $content['ai_generated_content_html']; ?>
                <?php else: ?>
                    <?php echo nl2br(htmlspecialchars($toText($content['ai_generated_content'] ?? $journal->ai_generated_content ?? ''))); ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- References 
    <div class="section">
        <div class="section-title">REFERENCES</div>
            <div>
                <?php $refs = $content['references'] ?? $journal->references ?? null; ?>
                <?php if(!empty($content['references_html'])): ?>
                    <?php echo $content['references_html']; ?>
                <?php else: ?>
                    <?php if(!empty($refs)): ?>
                        <?php echo nl2br(htmlspecialchars($toText($refs))); ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
    </div>-->

    <!-- Page Numbers 
    <div class="page-number">
        <span class="page">Page 1</span>
    </div>-->
</body>
</html>