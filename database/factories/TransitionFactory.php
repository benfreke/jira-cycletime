<?php

namespace Database\Factories;

use App\Models\Transition;
use Carbon\Carbon;
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
            'done' => null,
        ];
    }

    /**
     * Indicate that the user is suspended.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function pastDates()
    {
        return $this->state(function (array $attributes) {
            return [
                'start' => Carbon::now()->subDays(10),
                'done' => Carbon::now()->subDays(5),
            ];
        });
    }
    /**
     * Indicate that the user is suspended.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function futureDates()
    {
        return $this->state(function (array $attributes) {
            return [
                'start' => Carbon::now()->addDays(5),
                'done' => Carbon::now()->subDays(10),
            ];
        });
    }
}
