<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifiedAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_user_is_redirected_to_verification_notice_when_accessing_network(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/network');

        $response->assertRedirect('/verify-email');
    }

    public function test_verified_user_can_access_network(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/network');

        $response->assertOk();
    }
}
