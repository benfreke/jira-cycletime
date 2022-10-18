<?php

namespace Tests\Unit\Jobs;

use App\Jobs\GetChangeLogs;
use App\Jobs\UpdateTransitionDone;
use App\Jobs\UpdateTransitionStart;
use App\Models\Issue;
use App\Models\Transition;
use App\Services\Jira;
use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Mockery\Mock;
use Tests\TestCase;

class GetChangeLogsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Use PLAN-30 to get the various changes to an issue to update our local DB
     *
     * @return void
     * @throws FileNotFoundException
     */
    public function testPushingItemsToQueues()
    {
        // Arrange
        Queue::fake();
        Http::fake(['*' => Http::response($this->getFixture('fakeJiraResponse.json'))]);

        /** @var Mock|Jira $mockedJiraService */
        $mockedJiraService = \Mockery::mock(Jira::class)->makePartial();
        $mockedJiraService->shouldReceive('getIssueChangelogs')
            ->andReturn($this->getFixture('fakeChangeLog.json'));

        /** @var Issue $issue */
        $issue = Issue::factory()->has(Transition::factory())->create(
            ['key' => 'PLAN-30', 'last_jira_update' => Carbon::now()->subMinutes(1)]
        );
        // Act
        $job = new GetChangeLogs($issue, $mockedJiraService);
        $job->handle();

        // Assert
        Queue::assertPushed(UpdateTransitionStart::class, 1);
        Queue::assertPushed(UpdateTransitionDone::class, 1);
    }
}
