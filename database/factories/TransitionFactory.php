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

    public function setValidStartDone(): TransitionFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'start' => Carbon::now()->subDays(10),
                'done' => Carbon::now()->subDays(5),
            ];
        });
    }

    public function setUpdatedAtPast(): TransitionFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'updated_at' => Carbon::now()->subDays(5),
            ];
        });
    }

    public function setUpdatedAtFuture(): TransitionFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'updated_at' => Carbon::now()->addDays(5),
            ];
        });
    }

}
