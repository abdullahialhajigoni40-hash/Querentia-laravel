<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Journal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SubmissionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get user's journals with submission data
        $journals = $user->journals()
            ->withCount('reviews')
            ->with(['reviews' => function($query) {
                $query->where('status', 'completed');
            }])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Calculate statistics
        $stats = [
            'total' => $journals->count(),
            'draft' => $journals->where('status', 'draft')->count(),
            'under_review' => $journals->where('status', 'under_review')->count(),
            'published' => $journals->where('status', 'published')->count(),
            'rejected' => $journals->where('status', 'rejected')->count(),
        ];
        
        $acceptanceRate = $stats['total'] > 0 ? round(($stats['published'] / $stats['total']) * 100, 1) : 0;
        $stats['acceptance_rate'] = $acceptanceRate;
        
        // Group journals by status
        $activeJournals = $journals->whereIn('status', ['draft', 'under_review']);
        $publishedJournals = $journals->where('status', 'published');
        $rejectedJournals = $journals->where('status', 'rejected');
        
        return view('submissions.index', compact(
            'journals',
            'stats',
            'activeJournals',
            'publishedJournals',
            'rejectedJournals'
        ));
    }
    
    public function create()
    {
        // Redirect to AI Journal Editor since journal creation is handled there
        return redirect()->route('create_journal')
            ->with('info', 'Journal creation has been moved to the AI Journal Editor for a better writing experience.');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:100',
            'area_of_study' => 'required|string|max:100',
            'keywords' => 'nullable|string|max:500',
            'abstract' => 'nullable|string|max:1000',
            'license' => 'nullable|string|max:50',
            'status' => 'required|in:draft,under_review',
        ]);
        
        try {
            DB::beginTransaction();
            
            $journal = Journal::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'content' => $request->content,
                'area_of_study' => $request->area_of_study,
                'keywords' => $request->keywords,
                'abstract' => $request->abstract,
                'license' => $request->license,
                'status' => $request->status,
                'slug' => \Illuminate\Support\Str::slug($request->title) . '-' . time(),
            ]);
            
            DB::commit();
            
            return redirect()->route('submissions.index')
                ->with('success', 'Journal created successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withInput()
                ->with('error', 'Failed to create journal. Please try again.');
        }
    }
    
    public function show($id)
    {
        $journal = Journal::where('id', $id)
            ->where('user_id', Auth::id())
            ->with(['reviews' => function($query) {
                $query->with('reviewer')->orderBy('created_at', 'desc');
            }])
            ->firstOrFail();
        
        return view('submissions.show', compact('journal'));
    }
    
    public function edit($id)
    {
        $journal = Journal::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        // Only allow editing of draft journals
        if ($journal->status !== 'draft') {
            return back()->with('error', 'Only draft journals can be edited.');
        }
        
        return view('submissions.edit', compact('journal'));
    }
    
    public function update(Request $request, $id)
    {
        $journal = Journal::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        // Only allow editing of draft journals
        if ($journal->status !== 'draft') {
            return back()->with('error', 'Only draft journals can be edited.');
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:100',
            'area_of_study' => 'required|string|max:100',
            'keywords' => 'nullable|string|max:500',
            'abstract' => 'nullable|string|max:1000',
            'license' => 'nullable|string|max:50',
        ]);
        
        $journal->update([
            'title' => $request->title,
            'content' => $request->content,
            'area_of_study' => $request->area_of_study,
            'keywords' => $request->keywords,
            'abstract' => $request->abstract,
            'license' => $request->license,
            'slug' => \Illuminate\Support\Str::slug($request->title) . '-' . time(),
        ]);
        
        return redirect()->route('submissions.show', $journal->id)
            ->with('success', 'Journal updated successfully!');
    }
    
    public function destroy($id)
    {
        $journal = Journal::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        // Only allow deletion of draft journals
        if ($journal->status !== 'draft') {
            return back()->with('error', 'Only draft journals can be deleted.');
        }
        
        $journal->delete();
        
        return redirect()->route('submissions.index')
            ->with('success', 'Journal deleted successfully!');
    }
    
    public function submitForReview($id)
    {
        $journal = Journal::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        // Only allow submission of draft journals
        if ($journal->status !== 'draft') {
            return back()->with('error', 'Only draft journals can be submitted for review.');
        }
        
        $journal->update([
            'status' => 'under_review',
            'submitted_at' => now(),
        ]);
        
        return redirect()->route('submissions.show', $journal->id)
            ->with('success', 'Journal submitted for review!');
    }
    
    public function publish($id)
    {
        $journal = Journal::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        // Only allow publishing of journals that are under review or have been reviewed
        if (!in_array($journal->status, ['under_review', 'reviewed'])) {
            return back()->with('error', 'Journal must be reviewed before publishing.');
        }
        
        $journal->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
        
        return redirect()->route('submissions.show', $journal->id)
            ->with('success', 'Journal published successfully!');
    }
    
    public function withdraw($id)
    {
        $journal = Journal::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        // Only allow withdrawal of journals under review
        if ($journal->status !== 'under_review') {
            return back()->with('error', 'Only journals under review can be withdrawn.');
        }
        
        $journal->update([
            'status' => 'draft',
            'submitted_at' => null,
        ]);
        
        return redirect()->route('submissions.show', $journal->id)
            ->with('success', 'Journal withdrawn from review.');
    }
}
