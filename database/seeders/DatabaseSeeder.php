<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Journal;
use App\Models\Post;
use App\Models\Review;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'first_name' => 'Abdullahi Alhaji',
            'last_name' => 'Goni',
            'email' => 'abdullahialhajigoni2021@gmail.com',
            'password' => bcrypt('123456789'),
            'institution' => 'Querentia University',
            'position' => 'student',
            'research_interests' => json_encode(['AI', 'Education', 'Research Methods']),
            'is_verified_researcher' => true,
        ]);

        // Create sample users
        $users = User::factory(10)->create();

        // Create sample journals
        foreach ($users as $user) {
            $journal = Journal::create([
                'user_id' => $user->id,
                'title' => 'Research on ' . fake()->words(3, true),
                'abstract' => fake()->paragraphs(3, true),
                'status' => 'published',
                'area_of_study' => fake()->randomElement(['Computer Science', 'Biology', 'Physics', 'Chemistry', 'Mathematics']),
            ]);

            // Create post for the journal
            Post::create([
                'user_id' => $user->id,
                'journal_id' => $journal->id,
                'type' => 'journal',
                'content' => 'Sharing my latest research. Would appreciate your feedback!',
                'request_review' => true,
            ]);

            // Create some reviews
            foreach ($users->where('id', '!=', $user->id)->random(3) as $reviewer) {
                Review::create([
                    'journal_id' => $journal->id,
                    'reviewer_id' => $reviewer->id,
                    'rating' => rand(3, 5),
                    'comment' => fake()->paragraph(),
                    'status' => 'submitted',
                ]);
            }
        }
    }
}