<?php

namespace Database\Factories;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence('5'),
            'content' => $this->faker->sentence('50'),
            'user_id' => User::inRandomOrder()->first()->id,
            'status' => $this->faker->randomElement(TaskStatus::toSelectArray()),
            'published' => $this->faker->boolean('50'),
            'published_at' => $this->faker->dateTimeBetween('-20 days', now())
        ];
    }
}
