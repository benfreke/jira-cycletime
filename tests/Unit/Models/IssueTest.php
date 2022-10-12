<?php

namespace Tests\Unit\Models;

use App\Models\Issue;
use App\Models\Jirauser;
use App\Models\Transition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\TestCase;

class IssueTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testJiraUserRelationships()
    {
        // Test for a null Jirauser
        $issueClass = Issue::factory()->create(['issue_id' => 'abc-123']);
        self::assertNull($issueClass->jiraUser);

        // Test for a non-null transition
        $issueId = 'abc-321';
        $jiraUser = Jirauser::factory()
            ->hasIssues(1, ['issue_id' => $issueId])
            ->create();
        // Need to reload so issue knows about the new relationship
        self::assertEquals(1, $jiraUser->issues()->count());
        self::assertEquals($issueId, $jiraUser->issues()->first()->issue_id);
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
}
