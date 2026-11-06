<?php

namespace Database\Factories;

use App\Models\Committee;
use App\Models\Evidence;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Evidences in volume for the tests of the lists: a pending latest version by default.
 *
 *     Evidence::factory()->count(30)->for($student, 'user')->for($committee)->create();
 *
 * @extends Factory<Evidence>
 */
class EvidenceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => 'Evidencia '.$this->faker->unique()->numerify('#####'),
            'description' => '<p>'.$this->faker->sentence(8).'</p>',
            'hours' => 1,
            'status' => 'PENDING',
            'last' => true,
            'user_id' => User::factory(),
            'committee_id' => fn (): int => Committee::create(['name' => 'Comité '.$this->faker->unique()->numerify('####')])->id,
        ];
    }

    public function status(string $status): static
    {
        return $this->state(fn (): array => ['status' => $status]);
    }
}
