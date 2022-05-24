<?php

namespace Tests\Feature\Jobs;

use App\Jobs\UpdateTransitionDone;
use App\Models\Issue;
use App\Models\Transition;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTransitionDoneTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testSettingDoneFromNull()
    {
        // Arrange
        CarbonImmutable::setTestNow();
        /** @var Issue $issue */
        $issue = Issue::factory()->has(Transition::factory())->create(
            ['issue_id' => 'fake']
        );
        $timeToSet = CarbonImmutable::now();
        // Act
        static::assertNull($issue->transition->done);
        $job = new UpdateTransitionDone($issue->transition, $timeToSet);
        $job->handle();
        $issue->transition->refresh();

        // Assert
        static::assertNotNull($issue->transition->done);
        static::assertTrue($timeToSet->isSameDay($issue->transition->done));
        static::assertTrue($timeToSet->isSameHour($issue->transition->done));
        static::assertTrue($timeToSet->isSameMinute($issue->transition->done));
        static::assertTrue($timeToSet->isSameSecond($issue->transition->done));
    }

    /**
     * @return void
     */
    public function testUpdateExistingStart()
    {
        // Arrange
        /** @var Issue $issueNoStart */
        $issue = Issue::factory()->has(Transition::factory(['done' => CarbonImmutable::now()]))->create(
            ['issue_id' => 'fake']
        );
        $timeToSet = CarbonImmutable::now()->addDays(3);
        // Act
        static::assertNotNull($issue->transition->done);
        $job = new UpdateTransitionDone($issue->transition, $timeToSet);
        $job->handle();
        $issue->transition->refresh();

        // Assert
        static::assertTrue($timeToSet->isSameDay($issue->transition->done));
        static::assertTrue($timeToSet->isSameHour($issue->transition->done));
        static::assertTrue($timeToSet->isSameMinute($issue->transition->done));
        static::assertTrue($timeToSet->isSameSecond($issue->transition->done));
    }
}
