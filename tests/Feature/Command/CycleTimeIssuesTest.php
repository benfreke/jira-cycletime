<?php

namespace Tests\Feature\Command;

use App\Jobs\GetChangeLogs;
use App\Models\Estimate;
use App\Models\Issue;
use App\Models\Transition;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CycleTimeIssuesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     * @throws FileNotFoundException
     */
    public function testCreatingTransitionJobs(): void
    {
        // Arrange
        Queue::fake();
        Http::fake(['*' => Http::response($this->getFixture('fakeJiraResponse.json'))]);

        // Act
        self::assertEquals(0, count(Issue::all()));
        self::assertEquals(0, count(Transition::all()));
        self::assertEquals(0, count(Estimate::all()));
        Artisan::call('cycletime:issues', ['resultsToGet' => 1]);



        // Assert
        self::assertEquals(1, count(Issue::all()));
        self::assertEquals(1, count(Transition::all()));
        self::assertEquals(1, count(Estimate::all()));
        Queue::assertPushed(GetChangeLogs::class, 1);
    }
}
