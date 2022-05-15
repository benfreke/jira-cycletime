<?php

namespace Tests\Feature\Command;

use App\Jobs\GetTransitions;
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

        // Act
        Http::fake(['*' => Http::response($this->getFixture('fakeJiraResponse.json'))]);

        // Act
        Artisan::call('cycletime:issues', ['resultsToGet' => 1]);

        // Assert
        Queue::assertPushed(GetTransitions::class, 2);
    }
}
