<?php

namespace Tests\Feature\Command;

use App\Jobs\UpdateCycleTime;
use App\Models\Issue;
use App\Models\Transition;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CycleTimeCalculateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testCycleTimeCalculateNothing(): void
    {
        Queue::fake();

        // Call the method with nothing in the database
        $this->artisan('cycletime:calculate')->assertSuccessful();
        $results = Issue::needsNewCycletime();

        // Assert no results and nothing on the queue
        self::assertEquals(0, $results->get()->count());
        Queue::assertNothingPushed();
    }

    public function testOneJobToCalculate(): void
    {
        Queue::fake();

        // add to the queue (done is not null)
        $start = CarbonImmutable::parse('2022-05-23 08:00:00');
        $done = CarbonImmutable::parse('2022-05-27 09:00:00');
        /** @var $issue */
        Issue::factory()->has(Transition::factory(['start' => $start, 'done' => $done]))->create(
            ['issue_id' => 'fake', 'last_jira_update' => CarbonImmutable::now()]
        );
        $results = Issue::needsNewCycletime();
        self::assertEquals(1, $results->get()->count());
        $this->artisan('cycletime:calculate')->assertSuccessful();
        Queue::assertPushed(UpdateCycleTime::class);
    }

    public function testOneJobOnlyToCalculate(): void
    {
        Queue::fake();

        // add to the queue (done is not null)
        $start = CarbonImmutable::parse('2022-05-23 08:00:00');
        $done = CarbonImmutable::parse('2022-05-27 09:00:00');
        /** @var $issue */
        Issue::factory()->has(Transition::factory(['start' => $start, 'done' => $done]))->create(
            ['issue_id' => 'fake', 'last_jira_update' => CarbonImmutable::now()]
        );
        Issue::factory()->has(Transition::factory())->create(
            ['issue_id' => 'fake2', 'last_jira_update' => CarbonImmutable::now()]
        );
        $results = Issue::needsNewCycletime();
        self::assertEquals(1, $results->get()->count());
        $this->artisan('cycletime:calculate')->assertSuccessful();
        Queue::assertPushed(UpdateCycleTime::class);
    }


}
