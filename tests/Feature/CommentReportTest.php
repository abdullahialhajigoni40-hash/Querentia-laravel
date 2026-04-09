<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\CommentReport;
use App\Models\User;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_comment_report(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $user = User::factory()->create();
        $comment = Comment::factory()->create();

        $response = $this->actingAs($user)->postJson(route('comments.report', $comment), [
            'reason' => 'spam',
            'details' => 'This looks like spam.',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('comment_reports', [
            'comment_id' => $comment->id,
            'reporter_id' => $user->id,
            'reason' => 'spam',
            'status' => 'open',
        ]);
    }

    public function test_guest_cannot_submit_comment_report(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $comment = Comment::factory()->create();

        $response = $this->postJson(route('comments.report', $comment), [
            'reason' => 'spam',
        ]);

        $response->assertStatus(401);
    }
}
