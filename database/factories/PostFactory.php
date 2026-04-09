<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'journal_id' => null,
            'type' => 'discussion',
            'content' => $this->faker->paragraph(),
            'visibility' => 'public',
            'request_review' => false,
            'poll_options' => null,
            'likes_count' => 0,
            'comments_count' => 0,
            'shares_count' => 0,
            'reviews_count' => 0,
        ];
    }
}
