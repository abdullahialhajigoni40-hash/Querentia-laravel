<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Notification;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first user for testing
        $user = User::first();
        
        if (!$user) {
            $this->command->info('No users found. Please create a user first.');
            return;
        }
        
        // Create sample notifications
        $notifications = [
            [
                'type' => 'connection_request',
                'title' => 'New Connection Request',
                'message' => 'Dr. Sarah Johnson wants to connect with you',
                'data' => [
                    'sender_id' => 2,
                    'connection_id' => 1,
                    'sender_name' => 'Dr. Sarah Johnson',
                ],
                'created_at' => now()->subHours(2),
            ],
            [
                'type' => 'review_completed',
                'title' => 'Review Completed',
                'message' => 'Prof. Michael Chen has completed the review of your paper "AI in Education"',
                'data' => [
                    'reviewer_id' => 3,
                    'journal_id' => 44,
                    'review_id' => 1,
                    'rating' => 4.5,
                    'reviewer_name' => 'Prof. Michael Chen',
                ],
                'created_at' => now()->subDay(),
            ],
            [
                'type' => 'ai_processing_complete',
                'title' => 'AI Processing Complete',
                'message' => 'Querentia AI has finished processing your journal draft',
                'data' => [
                    'journal_id' => 44,
                ],
                'created_at' => now()->subDays(3),
                'read_at' => now()->subDays(2), // This one is read
            ],
            [
                'type' => 'connection_accepted',
                'title' => 'Connection Accepted',
                'message' => 'Dr. Emily Davis accepted your connection request',
                'data' => [
                    'receiver_id' => 4,
                    'connection_id' => 2,
                    'receiver_name' => 'Dr. Emily Davis',
                ],
                'created_at' => now()->subHours(6),
            ],
        ];
        
        foreach ($notifications as $notification) {
            Notification::create(array_merge($notification, [
                'user_id' => $user->id,
            ]));
        }
        
        $this->command->info('Sample notifications created successfully!');
    }
}
