<?php

namespace Tests\Unit\Jobs;

use App\Jobs\UpdateTransitionStart;
use App\Models\Issue;
use App\Models\Transition;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTransitionStartTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testSettingStart()
    {
        // Arrange
        /** @var Issue $issue */
        $issue = Issue::factory()->has(Transition::factory())->create(
            ['issue_id' => 'PLAN-30', 'last_jira_update' => Carbon::now()->subMinutes(1)]
        );

        // Act
        $job = new UpdateTransitionStart($issue->transition);
        $job->handle();
        $issue->transition->refresh();

        // Assert
        static::assertNotNull($issue->transition->start);
    }
}
