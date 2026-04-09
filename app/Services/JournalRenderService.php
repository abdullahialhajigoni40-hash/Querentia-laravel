<?php

namespace App\Services;

use App\Models\Journal;
use App\Models\JournalImage;

class JournalRenderService
{
    public function preparePreviewContent(Journal $journal, bool $preferAiOnly = false): array
    {
        $aiContent = [];
        $aiSource = $journal->ai_edited_content ?: $journal->ai_generated_content;
        if (!empty($aiSource)) {
            $decoded = json_decode($aiSource, true);
            if (is_array($decoded)) {
                $aiContent = $decoded;
            } else {
                $aiContent = $this->parseAIGeneratedContent((string) $aiSource);
            }
        }

        $fields = [
            'abstract', 'introduction', 'area_of_study', 'additional_notes',
            'methodology', 'results_discussion', 'conclusion', 'references',
        ];

        $result = [
            'title' => $journal->title,
            'authors' => $journal->authors ?? [],
            'ai_generated_content' => $aiSource,
        ];

        foreach ($fields as $f) {
            $aiKeyFallback = str_replace('results_discussion', 'results', $f);
            $value = $aiContent[$f] ?? ($aiContent[$aiKeyFallback] ?? null);

            if (!$preferAiOnly && is_null($value)) {
                $value = $journal->$f ?? null;
            }

            $result[$f] = $value;
            $result[$f . '_html'] = $this->renderMarkdownToHtml((string) ($value ?? ''));
        }

        if (!empty($journal->methodology_blocks) && is_array($journal->methodology_blocks)) {
            $parts = [];
            foreach ($journal->methodology_blocks as $block) {
                if (!is_array($block)) {
                    continue;
                }
                $title = isset($block['title']) ? trim((string) $block['title']) : '';
                $content = isset($block['content']) ? trim((string) $block['content']) : '';
                if ($title === '' && $content === '') {
                    continue;
                }
                if ($title !== '') {
                    $parts[] = "### {$title}\n\n" . $content;
                } else {
                    $parts[] = $content;
                }
            }

            $extra = trim(implode("\n\n", array_filter($parts, fn ($p) => trim((string) $p) !== '')));
            if ($extra !== '') {
                $base = trim((string) ($result['methodology'] ?? ''));
                $result['methodology'] = trim($base !== '' ? ($base . "\n\n" . $extra) : $extra);
                $result['methodology_html'] = $this->renderMarkdownToHtml((string) $result['methodology']);
            }
        }

        $result['keywords'] = $aiContent['keywords'] ?? ($result['area_of_study'] ?? '');
        $result['keywords_html'] = $this->renderMarkdownToHtml((string) ($result['keywords'] ?? ''));

        if (!empty($aiContent) && is_array($aiContent)) {
            $parts = [];
            $order = ['abstract', 'introduction', 'area_of_study', 'methodology', 'results_discussion', 'conclusion', 'references'];
            foreach ($order as $k) {
                if (!empty($aiContent[$k])) {
                    $parts[] = "## " . ucfirst(str_replace('_', ' ', $k)) . "\n\n" . trim((string) $aiContent[$k]);
                }
            }
            $full = implode("\n\n", $parts);
        } else {
            $full = (string) (($journal->ai_edited_content ?: $journal->ai_generated_content) ?? ($aiContent['full'] ?? ''));
        }

        $result['ai_generated_content_html'] = $this->renderMarkdownToHtml($full);
        $result['discussion_html'] = $this->renderMarkdownToHtml((string) ($result['results_discussion'] ?? $aiContent['discussion'] ?? ''));

        return $result;
    }

    public function buildFinalJournalHtml(Journal $journal, bool $preferAiOnly = false): string
    {
        $content = $this->preparePreviewContent($journal, $preferAiOnly);

        $authorsHtml = $this->renderAuthorsHtml($content['authors'] ?? [], $journal);
        $affiliationsHtml = $this->renderAffiliationsHtml($content['authors'] ?? [], $journal);

        $abstractHtml = $content['abstract_html'] ?? '';
        $keywordsText = trim((string) ($content['keywords'] ?? ''));

        $introHtml = $content['introduction_html'] ?? '';
        $areaHtml = $content['area_of_study_html'] ?? '';
        $methodHtml = $content['methodology_html'] ?? '';
        $resultsHtml = $content['results_discussion_html'] ?? '';
        $conclusionHtml = $content['conclusion_html'] ?? '';
        $referencesHtml = $content['references_html'] ?? '';

        $figuresHtml = $this->renderFiguresHtml($journal);

        $title = htmlspecialchars((string) ($journal->title ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $out = '';
        $out .= '<div class="journal-header">';
        $out .= '<div class="journal-title">' . $title . '</div>';
        $out .= $authorsHtml;
        $out .= $affiliationsHtml;
        $out .= '</div>';

        if (trim($abstractHtml) !== '' || $keywordsText !== '') {
            $out .= '<div class="section-content">';
            $out .= '<h2 class="section-title">Abstract</h2>';
            if (trim($abstractHtml) !== '') {
                $out .= '<div class="abstract">' . $abstractHtml . '</div>';
            }
            if ($keywordsText !== '') {
                $out .= '<div class="keywords"><strong>Keywords:</strong> ' . htmlspecialchars($keywordsText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</div>';
            }
            $out .= '</div>';
        }

        $out .= $this->wrapNumberedSection('1.0', 'Introduction', $introHtml);
        $out .= $this->wrapNumberedSection('2.0', 'Area of Study', $areaHtml);
        $out .= $this->wrapNumberedSection('3.0', 'Methodology', $methodHtml);

        $resultsBlock = '';
        if (trim($resultsHtml) !== '') {
            $resultsBlock .= $resultsHtml;
        }
        if (trim($figuresHtml) !== '') {
            $resultsBlock .= $figuresHtml;
        }
        $out .= $this->wrapNumberedSection('4.0', 'Results', $resultsBlock);

        $out .= $this->wrapNumberedSection('5.0', 'Conclusion', $conclusionHtml);
        $out .= $this->wrapNumberedSection('6.0', 'References', $referencesHtml);

        return $out;
    }

    private function wrapNumberedSection(string $number, string $title, string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<div class="section-content">'
            . '<h2 class="section-title">' . $number . ' ' . $safeTitle . '</h2>'
            . '<div>' . $html . '</div>'
            . '</div>';
    }

    private function renderAuthorsHtml($authors, Journal $journal): string
    {
        if (is_array($authors) && count($authors) > 0) {
            $names = [];
            foreach ($authors as $idx => $author) {
                if (is_array($author)) {
                    $name = trim((string) ($author['name'] ?? ''));
                    if ($name === '') {
                        $name = 'Author ' . ($idx + 1);
                    }
                    $cor = !empty($author['corresponding']);
                    $names[] = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ($cor ? '<sup>*</sup>' : '');
                } else {
                    $names[] = htmlspecialchars((string) $author, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                }
            }
            return '<div class="journal-authors">' . implode(', ', array_filter($names)) . '</div>';
        }

        if ($journal->user) {
            $name = $journal->user->full_name ?? $journal->user->name;
            return '<div class="journal-authors">' . htmlspecialchars((string) $name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</div>';
        }

        return '';
    }

    private function renderAffiliationsHtml($authors, Journal $journal): string
    {
        $aff = [];
        if (is_array($authors) && count($authors) > 0) {
            foreach ($authors as $author) {
                if (is_array($author) && !empty($author['affiliation'])) {
                    $a = trim((string) $author['affiliation']);
                    if ($a !== '') {
                        $aff[] = $a;
                    }
                }
            }
        }

        $aff = array_values(array_unique(array_filter($aff)));
        if (count($aff) > 0) {
            $items = array_map(fn ($a) => '<div>' . htmlspecialchars($a, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</div>', $aff);
            return '<div class="journal-affiliation">' . implode('', $items) . '</div>';
        }

        if ($journal->user && $journal->user->institution) {
            return '<div class="journal-affiliation">' . htmlspecialchars((string) $journal->user->institution, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</div>';
        }

        return '';
    }

    private function renderFiguresHtml(Journal $journal): string
    {
        $figures = $journal->figures()->get();
        if ($figures->isEmpty()) {
            return '';
        }

        $out = '';
        $n = 1;

        foreach ($figures as $fig) {
            /** @var JournalImage $fig */
            $url = (string) ($fig->url ?? '');
            $caption = trim((string) ($fig->caption ?? ''));
            $mime = (string) ($fig->mime_type ?? '');

            $out .= '<div class="figure-block" style="margin-top: 1rem;">';
            $out .= '<div style="font-weight: 700; margin-bottom: 0.25rem;">Figure ' . $n . '</div>';

            if (stripos($mime, 'pdf') !== false || str_ends_with(strtolower($url), '.pdf')) {
                $out .= '<div><a href="' . htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" target="_blank">View PDF</a></div>';
            } else {
                $out .= '<div><img src="' . htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" style="max-width: 100%; height: auto;"></div>';
            }

            if ($caption !== '') {
                $out .= '<div style="font-style: italic; margin-top: 0.25rem;">' . htmlspecialchars($caption, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</div>';
            }

            $out .= '</div>';
            $n++;
        }

        return $out;
    }

    private function parseAIGeneratedContent(string $aiContent): array
    {
        $sections = [];
        $normalized = str_replace(["\r\n", "\r"], "\n", $aiContent);

        $headingPattern = '/^\s*(?:\*\*+\s*)?(?:\#{1,6}\s*)?(?:\d+(?:\.\d+)*\s+)?(ABSTRACT|INTRODUCTION|KEYWORDS?|AREA\s+OF\s+STUDY|METHODOLOGY|MATERIALS\s*(?:&|AND)?\s*METHODS|RESULTS\s*(?:&|AND)?\s*DISCUSSION|RESULTS|DISCUSSION|CONCLUSION|REFERENCES|BIBLIOGRAPHY)\s*(?:\*\*+\s*)?\:??\s*$/im';

        if (!preg_match_all($headingPattern, $normalized, $matches, PREG_OFFSET_CAPTURE)) {
            return $sections;
        }

        $hits = [];
        for ($i = 0; $i < count($matches[1]); $i++) {
            $raw = strtoupper(trim($matches[1][$i][0] ?? ''));
            $offset = (int) ($matches[1][$i][1] ?? 0);

            $key = null;
            if (preg_match('/^ABSTRACT$/', $raw)) {
                $key = 'abstract';
            } elseif (preg_match('/^INTRODUCTION$/', $raw)) {
                $key = 'introduction';
            } elseif (preg_match('/^(KEYWORDS?|AREA\s+OF\s+STUDY)$/', $raw)) {
                $key = 'area_of_study';
            } elseif (preg_match('/^(METHODOLOGY|MATERIALS\s*(?:&|AND)?\s*METHODS)$/', $raw)) {
                $key = 'methodology';
            } elseif (preg_match('/^(RESULTS\s*(?:&|AND)?\s*DISCUSSION)$/', $raw)) {
                $key = 'results_discussion';
            } elseif (preg_match('/^RESULTS$/', $raw)) {
                $key = 'results';
            } elseif (preg_match('/^DISCUSSION$/', $raw)) {
                $key = 'discussion';
            } elseif (preg_match('/^CONCLUSION$/', $raw)) {
                $key = 'conclusion';
            } elseif (preg_match('/^(REFERENCES|BIBLIOGRAPHY)$/', $raw)) {
                $key = 'references';
            }

            if ($key) {
                $hits[] = ['key' => $key, 'offset' => $offset];
            }
        }

        if (empty($hits)) {
            return $sections;
        }

        usort($hits, fn ($a, $b) => $a['offset'] <=> $b['offset']);

        $length = strlen($normalized);
        for ($i = 0; $i < count($hits); $i++) {
            $start = $hits[$i]['offset'];
            $end = ($i + 1 < count($hits)) ? $hits[$i + 1]['offset'] : $length;

            $chunk = substr($normalized, $start, max(0, $end - $start));
            $chunk = preg_replace($headingPattern, '', $chunk, 1);
            $chunk = trim($chunk);

            if ($chunk === '') {
                continue;
            }

            $chunk = preg_replace('/^#+\s*/m', '', $chunk);
            $chunk = preg_replace('/\*\*(.*?)\*\*/', '$1', $chunk);
            $chunk = preg_replace('/\*(.*?)\*/', '$1', $chunk);
            $chunk = trim($chunk);

            if ($chunk === '') {
                continue;
            }

            $k = $hits[$i]['key'];
            $sections[$k] = $chunk;
        }

        if (!empty($sections['results_discussion'])) {
            return $sections;
        }

        if (!empty($sections['results']) || !empty($sections['discussion'])) {
            $parts = [];
            if (!empty($sections['results'])) {
                $parts[] = trim((string) $sections['results']);
            }
            if (!empty($sections['discussion'])) {
                $parts[] = trim((string) $sections['discussion']);
            }
            $sections['results_discussion'] = trim(implode("\n\n", $parts));
        }

        unset($sections['results'], $sections['discussion']);

        return $sections;
    }

    private function renderMarkdownToHtml(string $md): string
    {
        $md = trim($md);
        if ($md === '') {
            return '';
        }

        if (preg_match('/<\s*(p|br|div|span|strong|em|b|i|u|ul|ol|li|h[1-6]|blockquote|table|thead|tbody|tr|td|th|img)\b/i', $md)) {
            $md = html_entity_decode($md, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            $allowed = '<p><br><strong><em><b><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><table><thead><tbody><tr><td><th><img>';
            $html = strip_tags($md, $allowed);

            $html = preg_replace_callback('/<img\s+[^>]*>/i', function ($m) {
                if (preg_match('/\ssrc\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s>]+))/i', $m[0], $srcMatch)) {
                    $src = $srcMatch[2] ?? ($srcMatch[3] ?? ($srcMatch[4] ?? ''));
                    $src = htmlspecialchars($src, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    return '<img src="' . $src . '">';
                }
                return '';
            }, $html);

            $html = preg_replace('/<!--([\s\S]*?)-->/', '', $html);

            return $html;
        }

        $md = str_replace(["\r\n", "\r"], "\n", $md);

        $md = preg_replace_callback('/```([\s\S]*?)```/m', function ($m) {
            return "<pre><code>" . htmlspecialchars(trim($m[1]), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</code></pre>";
        }, $md);

        $md = preg_replace_callback('/`([^`]+)`/', function ($m) {
            return '<code>' . htmlspecialchars($m[1], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</code>';
        }, $md);

        $lines = explode("\n", $md);
        $htmlLines = [];
        $inList = false;
        $listType = '';

        $flushList = function () use (&$htmlLines, &$inList, &$listType) {
            if ($inList) {
                $htmlLines[] = $listType === 'ol' ? '</ol>' : '</ul>';
                $inList = false;
                $listType = '';
            }
        };

        foreach ($lines as $line) {
            $trim = rtrim($line);

            if (preg_match('/^\s*[-\*]\s+(.+)/', $trim, $m)) {
                if (!$inList || $listType !== 'ul') {
                    $flushList();
                    $inList = true;
                    $listType = 'ul';
                    $htmlLines[] = '<ul>';
                }
                $htmlLines[] = '<li>' . $this->inlineMarkdown(htmlspecialchars($m[1], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')) . '</li>';
                continue;
            }

            if (preg_match('/^\s*\d+\.\s+(.+)/', $trim, $m)) {
                if (!$inList || $listType !== 'ol') {
                    $flushList();
                    $inList = true;
                    $listType = 'ol';
                    $htmlLines[] = '<ol>';
                }
                $htmlLines[] = '<li>' . $this->inlineMarkdown(htmlspecialchars($m[1], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')) . '</li>';
                continue;
            }

            if (trim($trim) === '') {
                $flushList();
                $htmlLines[] = '';
                continue;
            }

            if (preg_match('/^\s*#{1,6}\s*(.+)$/', $trim, $m)) {
                $flushList();
                $htmlLines[] = '<strong>' . $this->inlineMarkdown(htmlspecialchars($m[1], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')) . '</strong>';
                continue;
            }

            $flushList();
            $safe = htmlspecialchars($trim, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $htmlLines[] = '<p>' . $this->inlineMarkdown($safe) . '</p>';
        }

        $flushList();

        $html = implode("\n", array_filter($htmlLines, fn ($l) => $l !== ''));

        $html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $html);

        return $html;
    }

    private function inlineMarkdown(string $text): string
    {
        $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $text);
        return $text;
    }
}
