<?php

namespace Tests\Feature\Jobs;

use App\Jobs\UpdateTransitionStart;
use App\Models\Issue;
use App\Models\Transition;
use Carbon\CarbonImmutable;
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
    public function testSettingStartFromNull()
    {
        // Arrange
        /** @var Issue $issueNoStart */
        $issue = Issue::factory()->has(Transition::factory())->create(
            ['issue_id' => 'fake']
        );
        $timeToSet = CarbonImmutable::now()->subDays();
        // Act
        static::assertNull($issue->transition->start);
        $job = new UpdateTransitionStart($issue->transition, $timeToSet);
        $job->handle();
        $issue->transition->refresh();

        // Assert
        static::assertNotNull($issue->transition->start);
        static::assertTrue($issue->transition->updated_at->greaterThan($timeToSet));
        static::assertTrue($timeToSet->isSameDay($issue->transition->start));
        static::assertTrue($timeToSet->isSameHour($issue->transition->start));
        static::assertTrue($timeToSet->isSameMinute($issue->transition->start));
        static::assertTrue($timeToSet->isSameSecond($issue->transition->start));
    }

    /**
     * @return void
     */
    public function testUpdateExistingStart()
    {
        // Arrange
        /** @var Issue $issue */
        $issue = Issue::factory()->has(Transition::factory(['start' => CarbonImmutable::now()]))->create(
            ['issue_id' => 'fake']
        );
        $timeToSet = CarbonImmutable::now()->subDays(3);
        // Act
        static::assertNotNull($issue->transition->start);
        static::assertNotNull($issue->transition->updated_at);
        $job = new UpdateTransitionStart($issue->transition, $timeToSet);
        $job->handle();
        $issue->transition->refresh();

        // Assert
        static::assertTrue($timeToSet->isSameDay($issue->transition->start));
        static::assertTrue($timeToSet->isSameHour($issue->transition->start));
        static::assertTrue($timeToSet->isSameMinute($issue->transition->start));
        static::assertTrue($timeToSet->isSameSecond($issue->transition->start));
        static::assertTrue($timeToSet->lessThan($issue->transition->updated_at));
    }
}
