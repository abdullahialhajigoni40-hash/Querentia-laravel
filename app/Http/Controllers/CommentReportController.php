<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\AdminAuditLog;
use App\Models\CommentReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class CommentReportController extends Controller
{
    public function index(Request $request): View
    {
        $reports = CommentReport::with(['comment.user', 'reporter', 'resolver'])
            ->orderByRaw("CASE WHEN status = 'open' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('admin.comment-reports.index', [
            'reports' => $reports,
        ]);
    }

    public function store(Request $request, Comment $comment): Response
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:100'],
            'details' => ['nullable', 'string', 'max:2000'],
        ]);

        CommentReport::create([
            'comment_id' => $comment->id,
            'reporter_id' => Auth::id(),
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
            'status' => 'open',
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('status', 'report-submitted');
    }

    public function resolve(Request $request, CommentReport $report): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:open,resolved,dismissed'],
        ]);

        $report->update([
            'status' => $validated['status'],
            'resolved_by' => Auth::id(),
            'resolved_at' => now(),
        ]);

        AdminAuditLog::create([
            'admin_user_id' => Auth::id(),
            'action' => 'comment_report_status_changed',
            'target_type' => CommentReport::class,
            'target_id' => $report->id,
            'metadata' => [
                'status' => $validated['status'],
                'comment_id' => $report->comment_id,
            ],
            'ip_address' => $request->ip(),
        ]);

        return back();
    }
}
