<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class IssueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'summary' => $this->faker->name(),
            'issue_id' => $this->faker->text(5),
            'last_jira_update' => $this->faker->dateTimeInInterval('-1 month', '+20 days'),
            'assignee' => null,
            'project' => explode(',', config('cycletime.jira-categories'))[0],
            'issue_type' => 'Bug',
            'cycletime' => null,
        ];
    }
}
