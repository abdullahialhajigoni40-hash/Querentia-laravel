<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'post_id' => Post::factory(),
            'parent_id' => null,
            'content' => $this->faker->sentence(),
            'is_review' => false,
            'rating' => null,
            'review_criteria' => null,
            'is_helpful' => false,
            'helpful_count' => 0,
            'replies_count' => 0,
        ];
    }
}
