<?php

namespace Tests\Feature\Service;

use App\Models\Issue;
use App\Services\Jira;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Let's make sure Jira doesn't start returning bad data
 */
class JiraTest extends TestCase
{
    public function testGetIssues()
    {
        // Arrange
        Http::fake(['*' => Http::response($this->getFixture('jiraresponse2.json'))]);
        $mockedIssue = \Mockery::mock(Issue::class);
            $mockedIssue->shouldReceive('getLastUpdatedDate')
            ->andReturn(null);

        // Act
        $jiraService = new Jira(1, $mockedIssue);
        $response = $jiraService->getIssues();

        // Assert
        self::assertEqualsCanonicalizing($this->getFixture('jiraresponse.json'), $response);
    }
}
