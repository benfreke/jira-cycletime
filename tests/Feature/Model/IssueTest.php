<?php

namespace Tests\Feature\Model;

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
    }

    public function testTransitionRelationship()
    {
        // Test for a null Transition
        $issueClass = Issue::factory()->create(['issue_id' => 'abc-123']);
        self::assertNull($issueClass->transition);

        // Test for a non-null transition
        Transition::factory()
            ->for($issueClass)
            ->create(['issue_id' => 'abc-123']);
        // Need to reload so issue knows about the new relationship
        $issueClass->load('transition');
        self::assertNotNull($issueClass->transition);
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
        Issue::factory()->has(Transition::factory()->pastDates())->create(
            ['issue_id' => 'past', 'last_jira_update' => Carbon::now()->subMinutes(1)]
        );
        Issue::factory()->has(Transition::factory()->pastDates())->create(
            ['issue_id' => 'past2', 'last_jira_update' => Carbon::now()->subMinutes(2)]
        );
        Issue::factory()->has(Transition::factory()->pastDates())->create(
            ['issue_id' => 'future', 'last_jira_update' => Carbon::now()->addMinute()]
        );

        // Act
        $results = Issue::needsNewCycleTime();

        // Assert
        self::assertEquals(1, $results->count());
        unset($results);

        // Now change a transition to be later than the issue updated at

        Issue::factory()->has(Transition::factory()->pastDates())->create(
            ['issue_id' => 'future2', 'last_jira_update' => Carbon::now()->addMinute()]
        );

        $results = Issue::needsNewCycletime();
        self::assertEquals(2, $results->count());
    }
}
