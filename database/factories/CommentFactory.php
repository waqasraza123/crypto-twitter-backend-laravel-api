<?php

namespace Database\Factories;

use App\Models\Blog;
use App\Models\Comment;
use App\Models\User;
use App\Models\Tweet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'comment' => $this->faker->paragraph(1),
            'commentable_type' => $this->faker->randomElement([Blog::class, Tweet::class]),
            'commentable_id' => function (array $comment) {
                    return $comment['commentable_type']::inRandomOrder()->first()->id;
                },
            'user_id' => User::inRandomOrder()->first()->id,
        ];
    }
}
