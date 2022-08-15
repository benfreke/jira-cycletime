<?php

namespace Tests\Feature\Jobs;

use App\Jobs\UpdateCycleTime;
use App\Models\Issue;
use App\Models\Transition;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateCycleTimeTest extends TestCase
{
    use RefreshDatabase;

    public function dataSetCycleTime(): array
    {
        return [
            'One full week' => [
                CarbonImmutable::parse('2022-05-23 08:00:00'),
                CarbonImmutable::parse('2022-05-27 09:00:00'),
                5,
            ],
            'Over a weekend' => [
                CarbonImmutable::parse('2022-05-27 08:00:00'),
                CarbonImmutable::parse('2022-05-30 09:00:00'),
                2,
            ],
            'Over an AU public holiday' => [
                CarbonImmutable::parse('2022-04-21 08:00:00'),
                CarbonImmutable::parse('2022-04-27 09:00:00'),
                4,
            ],
        ];
    }

    /**
     * @dataProvider dataSetCycleTime
     *
     * @param  CarbonImmutable  $start
     * @param  CarbonImmutable  $done
     * @param  int  $cycletime
     *
     * @return void
     */
    public function testSetCycleTime(CarbonImmutable $start, CarbonImmutable $done, int $cycletime)
    {
        // Arrange
        /** @var  $issue */
        $issue = Issue::factory()->has(Transition::factory(['start' => $start, 'done' => $done]))->create(
            ['issue_id' => 'fake', 'last_jira_update' => CarbonImmutable::now()]
        );

        // Act
        $job = new UpdateCycleTime($issue->id);
        $job->handle();
        $issue->refresh();

        // Assert
        self::assertEquals($cycletime, $issue->cycletime);
    }
}
