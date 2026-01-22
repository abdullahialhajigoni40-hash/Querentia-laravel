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
    <!-- Journal Header -->
    <div class="journal-header">
        <div class="journal-title">Journal of Natural Sciences Research</div>
        <div class="issn">ISSN 2224-3186 (Paper)   ISSN 2225-0921 (Online)</div>
        <div class="volume">Vol.7, No.10, 2017</div>
        <div class="url">www.iiste.org</div>
    </div>

    <!-- Paper Title -->
    <div class="paper-title">{{ $journal->title }}</div>

    <!-- Authors -->
    <div class="authors">
        @if(isset($journal->raw_content['authors']))
            {!! nl2br(htmlspecialchars($journal->raw_content['authors'])) !!}
        @else
            {{ $journal->user->full_name }}
        @endif
    </div>

    <!-- Affiliations -->
    <div class="affiliations">
        {{ $journal->user->institution }}
        @if($journal->user->department)
            • {{ $journal->user->department }}
        @endif
    </div>

    <!-- Abstract -->
    <div class="section">
        <div class="section-title">Abstract</div>
        <div class="abstract">
            {{ $journal->raw_content['abstract'] ?? '' }}
        </div>
    </div>

    <!-- Keywords -->
    <div class="keywords">
        <strong>Keywords:</strong> 
        @if(isset($journal->raw_content['keywords']))
            {{ $journal->raw_content['keywords'] }}
        @else
            {{ $journal->raw_content['area_of_study'] ?? '' }}
        @endif
    </div>

    <!-- Introduction -->
    <div class="section">
        <div class="section-title">1.0 INTRODUCTION</div>
        <div>
            {{ $journal->raw_content['introduction'] ?? '' }}
        </div>
    </div>

    <!-- Methodology -->
    <div class="section">
        <div class="section-title">2.0 METHODOLOGY</div>
        <div>
            {{ $journal->raw_content['methodology'] ?? '' }}
        </div>
    </div>

    <!-- Results -->
    <div class="section">
        <div class="section-title">3.0 RESULTS</div>
        <div>
            {{ $journal->raw_content['results'] ?? '' }}
        </div>
    </div>

    <!-- Discussion -->
    <div class="section">
        <div class="section-title">4.0 DISCUSSION</div>
        <div>
            {{ $journal->raw_content['discussion'] ?? '' }}
        </div>
    </div>

    <!-- Conclusion -->
    <div class="section">
        <div class="section-title">5.0 CONCLUSION</div>
        <div>
            {{ $journal->raw_content['conclusion'] ?? '' }}
        </div>
    </div>

    <!-- References -->
    <div class="section">
        <div class="section-title">REFERENCES</div>
        <div>
            @if(isset($journal->raw_content['references']))
                {!! nl2br(htmlspecialchars($journal->raw_content['references'])) !!}
            @endif
        </div>
    </div>

    <!-- Page Numbers -->
    <div class="page-number">
        <span class="page">Page 1</span>
    </div>
</body>
</html>