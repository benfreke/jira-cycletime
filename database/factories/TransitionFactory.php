<?php

namespace Database\Factories;

use App\Models\Transition;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransitionFactory extends Factory
{
    protected $model = Transition::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'issue_id' => $this->faker->text(5),
            'start' => null,
            'done' => null
        ];
    }
}
