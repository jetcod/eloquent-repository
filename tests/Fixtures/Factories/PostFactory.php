<?php

namespace Jetcod\LaravelRepository\Test\Fixtures\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jetcod\LaravelRepository\Test\Fixtures\Models\Post;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(3),
            'body'  => $this->faker->sentence,
        ];
    }
}
