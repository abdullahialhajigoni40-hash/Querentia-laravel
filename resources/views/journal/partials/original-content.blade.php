<!-- resources/views/journal/partials/original-content.blade.php -->

<!-- Header Section (Centered) -->
<div class="journal-header">
    <div class="journal-title">{{ $journal->title }}</div>
    
    <!-- Authors -->
    @if($journal->authors && is_array($journal->authors) && count($journal->authors) > 0)
        <div class="journal-authors">
            @foreach($journal->authors as $index => $author)
                @if(is_array($author))
                    <span>
                        {{ $author['name'] ?? 'Author ' . ($index + 1) }}
                        @if(isset($author['corresponding']) && $author['corresponding'])
                            <sup>*</sup>
                        @endif
                    </span>
                    @if($index < count($journal->authors) - 1)<span>, </span>@endif
                @else
                    {{ $author }}
                    @if($index < count($journal->authors) - 1)<span>, </span>@endif
                @endif
            @endforeach
        </div>
        
        <!-- Affiliations -->
        <div class="journal-affiliation">
            @foreach($journal->authors as $author)
                @if(is_array($author) && isset($author['affiliation']))
                    <div>{{ $author['affiliation'] }}</div>
                @endif
            @endforeach
        </div>
    @elseif($journal->user)
        <div class="journal-authors">{{ $journal->user->full_name ?? $journal->user->name }}</div>
        @if($journal->user->institution)
            <div class="journal-affiliation">{{ $journal->user->institution }}</div>
        @endif
    @endif
</div>

<!-- Body Content (Left-aligned) -->
<div class="journal-body">
    @if($journal->abstract)
    <div class="section-content">
        <h2 class="section-title">Abstract</h2>
        <div class="abstract">{{ $journal->abstract }}</div>
    </div>
    @endif

    @if($journal->introduction)
    <div class="section-content">
        <h2 class="section-title">Introduction</h2>
        <p>{!! nl2br(e($journal->introduction)) !!}</p>
    </div>
    @endif

    @if($journal->area_of_study)
    <div class="section-content">
        <h2 class="section-title">Area of Study</h2>
        <p>{{ $journal->area_of_study }}</p>
    </div>
    @endif

    @if($journal->additional_notes)
    <div class="section-content">
        <h2 class="section-title">Additional Notes</h2>
        <p>{!! nl2br(e($journal->additional_notes)) !!}</p>
    </div>
    @endif

    @if($journal->methodology)
    <div class="section-content">
        <h2 class="section-title">Methodology</h2>
        <p>{!! nl2br(e($journal->methodology)) !!}</p>
    </div>
    @endif

    @if($journal->results_discussion)
    <div class="section-content">
        <h2 class="section-title">Results & Discussion</h2>
        <p>{!! nl2br(e($journal->results_discussion)) !!}</p>
    </div>
    @endif

    @if($journal->conclusion)
    <div class="section-content">
        <h2 class="section-title">Conclusion</h2>
        <p>{!! nl2br(e($journal->conclusion)) !!}</p>
    </div>
    @endif

    @if($journal->references)
    <div class="section-content">
        <h2 class="section-title">References</h2>
        <div class="references">
            @if(is_array($journal->references))
                @foreach($journal->references as $ref)
                    @if(is_array($ref))
                        <li>
                            @if(isset($ref['author']))
                                {{ $ref['author'] }}
                            @endif
                            @if(isset($ref['year']))
                                ({{ $ref['year'] }})
                            @endif
                            @if(isset($ref['title']))
                                <em>{{ $ref['title'] }}</em>
                            @endif
                            @if(isset($ref['journal']))
                                {{ $ref['journal'] }}
                            @endif
                            @if(isset($ref['volume']) && isset($ref['issue']))
                                {{ $ref['volume'] }}({{ $ref['issue'] }})
                            @endif
                            @if(isset($ref['pages']))
                                {{ $ref['pages'] }}
                            @endif
                        </li>
                    @else
                        <li>{{ $ref }}</li>
                    @endif
                @endforeach
            @else
                {!! nl2br(e($journal->references)) !!}
            @endif
        </div>
    </div>
    @endif
    
    <!-- Show AI content if exists and no toggle -->
    @if($journal->ai_generated_content && !isset($showOriginal))
    <div class="page-break"></div>
    <div class="section-content">
        <h2 class="section-title" style="color: #4f46e5;">
            <i class="fas fa-robot mr-2"></i>AI-Generated Content
        </h2>
        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
            {!! nl2br(e($journal->ai_generated_content)) !!}
        </div>
    </div>
    @endif
</div>