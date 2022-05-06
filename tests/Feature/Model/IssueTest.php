<?php

namespace Tests\Feature\Model;

use App\Models\Issue;
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
            'last_jira_update' => Carbon::now()->subHours(5)->subMinute()
        ]);
        self::assertEquals(5, $issueClass->getLastUpdatedDate());
        Issue::factory()->create([
            'last_jira_update' => Carbon::now()->subHours(5)->addMinute()
        ]);
        self::assertEquals(4, $issueClass->getLastUpdatedDate());
    }
}
