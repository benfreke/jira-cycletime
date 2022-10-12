<?php

namespace Tests\Feature\Models;

use App\Models\Issue;
use App\Models\Transition;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IssueTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function testGetLastUpdatedDate()
    {
        $issueClass = new Issue();
        // Test null when no date is set
        self::assertNull($issueClass->getLastUpdatedDate());

        // Check for a known time in the past
        Issue::factory()->create([
            'last_jira_update' => Carbon::now()->subHours(5)->subMinute(),
        ]);
        self::assertEquals(5, $issueClass->getLastUpdatedDate());

        Issue::factory()->create([
            'last_jira_update' => Carbon::now()->subHours(5)->addMinute(),
        ]);
        self::assertEquals(4, $issueClass->getLastUpdatedDate());

        Issue::factory()->create([
            'last_jira_update' => Carbon::now()
        ]);
        self::assertSame(0, $issueClass->getLastUpdatedDate());
    }

    /**
     * Creates 3 Issues, 2 of which have a more recent transition
     *
     * @return void
     */
    public function testGetNeedsCalculating()
    {
        // Arrange
        Carbon::setTestNow(Carbon::create(2022, 6, 15, 12, 30));
        Issue::factory()->has(Transition::factory()->setValidStartDone()->setUpdatedAtPast())->create(
            ['issue_id' => 'past', 'last_jira_update' => Carbon::now()->subMinute()]
        );
        Issue::factory()->has(Transition::factory()->setValidStartDone()->setUpdatedAtPast())->create(
            ['issue_id' => 'past2', 'last_jira_update' => Carbon::now()->subMinutes(2)]
        );
        Issue::factory()->has(Transition::factory()->setValidStartDone()->setUpdatedAtFuture())->create(
            ['issue_id' => 'future', 'last_jira_update' => Carbon::now()->addMinute()]
        );

        // Assert
        self::assertEquals(1, Issue::needsNewCycleTime()->count());
        unset($results);

        // Now change a transition to be later than the issue updated at
        Issue::factory()->has(Transition::factory()->setValidStartDone()->setUpdatedAtFuture())->create(
            ['issue_id' => 'future2', 'last_jira_update' => Carbon::now()->addMinute()]
        );

        self::assertEquals(2, Issue::needsNewCycletime()->count());
    }
}
