<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class JournalFactory extends Factory
{
    public function definition(): array
    {
        $statuses = ['draft', 'in_review', 'published', 'archived'];
        
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(6),
            'slug' => $this->faker->slug(),
            'abstract' => $this->faker->paragraph(3),
            'authors' => json_encode([
                ['name' => $this->faker->name(), 'affiliation' => $this->faker->company()],
                ['name' => $this->faker->name(), 'affiliation' => $this->faker->company()],
            ]),
            'introduction' => $this->faker->paragraphs(3, true),
            'area_of_study' => $this->faker->randomElement(['Computer Science', 'Medicine', 'Engineering', 'Social Sciences']),
            'additional_notes' => $this->faker->paragraph(),
            'materials_methods' => $this->faker->paragraphs(2, true),
            'results_discussion' => $this->faker->paragraphs(4, true),
            'conclusion' => $this->faker->paragraph(),
            'references' => json_encode([
                ['citation' => $this->faker->sentence()],
                ['citation' => $this->faker->sentence()],
            ]),
            'status' => $this->faker->randomElement($statuses),
            'journal_name' => $this->faker->randomElement(['Nature', 'Science', 'IEEE Transactions', 'Journal of Medicine']),
            'ai_provider_used' => $this->faker->randomElement(['deepseek', 'chatgpt', 'gemini', null]),
            'ai_usage_count' => $this->faker->numberBetween(0, 10),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}