<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Group;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        
        if (!$user) {
            $this->command->info('No users found. Please create a user first.');
            return;
        }
        
        $groups = [
            [
                'name' => 'AI Research Discussion',
                'description' => 'A collaborative space for researchers working on artificial intelligence, machine learning, and data science projects. Share insights, discuss challenges, and find collaborators for your AI research.',
                'type' => 'public',
                'status' => 'active',
            ],
            [
                'name' => 'Academic Writing Support',
                'description' => 'Get feedback on your academic papers, discuss writing strategies, and share resources for improving scholarly communication. Perfect for researchers at all career stages.',
                'type' => 'public',
                'status' => 'active',
            ],
            [
                'name' => 'Research Methods Workshop',
                'description' => 'A private group for discussing advanced research methodologies, statistical analysis, and experimental design. Share best practices and get peer feedback on your research approach.',
                'type' => 'private',
                'status' => 'active',
            ],
        ];
        
        foreach ($groups as $groupData) {
            $group = Group::create(array_merge($groupData, [
                'creator_id' => $user->id,
                'slug' => \Illuminate\Support\Str::slug($groupData['name']),
                'members_count' => 1,
                'messages_count' => rand(5, 25),
                'last_message_at' => now()->subHours(rand(1, 24)),
            ]));
            
            // Add some sample messages
            $sampleMessages = [
                "Welcome to the group! Feel free to introduce yourself and share your research interests.",
                "Looking forward to collaborating with everyone here. This is a great initiative!",
                "Does anyone have experience with qualitative research methods? I'd love to hear your thoughts.",
                "Just published a new paper on machine learning applications in healthcare. Happy to share insights!",
                "Great discussion happening here. Let's keep the momentum going!",
            ];
            
            for ($i = 0; $i < rand(2, 5); $i++) {
                $group->messages()->create([
                    'user_id' => $user->id,
                    'content' => $sampleMessages[array_rand($sampleMessages)],
                    'type' => 'text',
                ]);
            }
            
            $group->updateMessageCount();
        }
        
        $this->command->info('Sample groups created successfully!');
    }
}
