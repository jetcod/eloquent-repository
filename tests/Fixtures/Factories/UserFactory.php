<?php

namespace Jetcod\LaravelRepository\Test\Fixtures\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jetcod\LaravelRepository\Test\Fixtures\Models\Post;
use Jetcod\LaravelRepository\Test\Fixtures\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'first_name' => $this->faker->name,
            'last_name'  => $this->faker->lastName,
            'email'      => $this->faker->unique()->email,
            'password'   => $this->faker->password,
        ];
    }

    public function userDomain(string $domain): Factory
    {
        return $this->state(function (array $attributes) use ($domain) {
            return [
                'email' => sprintf('%s@%s', $this->faker->userName, $domain),
            ];
        });
    }

    public function configure(): static
    {
        $faker = $this->faker;

        return $this->afterCreating(function (User $user) use ($faker) {
            Post::factory()->count($faker->numberBetween(1, 10))->create([
                'user_id' => $user->id,
            ]);
        });
    }
}
