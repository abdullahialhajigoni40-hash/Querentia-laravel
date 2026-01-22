<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;

class UserProfileSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        
        foreach ($users as $user) {
            UserProfile::create([
                'user_id' => $user->id,
                'title' => $this->getTitle($user->position),
                'bio' => $this->getBio($user),
                'website' => 'https://example.com',
                'linkedin' => 'https://linkedin.com/in/'.$user->first_name,
                'google_scholar' => 'https://scholar.google.com',
                'education' => json_encode([
                    ['institution' => 'University of Example', 'degree' => 'PhD', 'field' => 'Computer Science', 'year' => '2020'],
                    ['institution' => 'Example College', 'degree' => 'MSc', 'field' => 'Data Science', 'year' => '2016'],
                ]),
                'skills' => json_encode(['Machine Learning', 'Data Analysis', 'Python', 'Academic Writing']),
                'total_connections' => rand(5, 100),
                'total_publications' => rand(1, 20),
                'total_reviews' => rand(0, 50),
            ]);
        }
    }

    private function getTitle($position)
    {
        $titles = [
            'student' => 'Graduate Student',
            'researcher' => 'Research Scientist',
            'lecturer' => 'Senior Lecturer',
            'professor' => 'Professor',
            'phd' => 'PhD Candidate',
            'other' => 'Researcher',
        ];
        
        return $titles[$position] ?? 'Researcher';
    }

    private function getBio($user)
    {
        return "{$user->first_name} {$user->last_name} is a passionate researcher with expertise in {$user->research_interests}. Currently working at {$user->institution}, focusing on innovative solutions in their field.";
    }
}