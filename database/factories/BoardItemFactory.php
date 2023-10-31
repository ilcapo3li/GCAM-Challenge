<?php

namespace Database\Factories;

use App\Enums\Status;
use App\Enums\TaskType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BoardItem>
 */
class BoardItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->realText(),
            'code' => 'GC-'.now()->micro,
            'type_id' => TaskType::TASK->value,
            'status_id' => Status::TODO->value,
        ];
    }

    /**
     * Indicate that the model's issue type should be epic.
     */
    public function epic(): static
    {
        return $this->state(fn (array $attributes) => [
            'type_id' => TaskType::EPIC->value,
        ]);
    }

    /**
     * Indicate that the model's issue type should be sub task.
     */
    public function subTask(): static
    {
        return $this->state(fn (array $attributes) => [
            'type_id' => TaskType::SUB_TASK->value,
        ]);
    }
}
