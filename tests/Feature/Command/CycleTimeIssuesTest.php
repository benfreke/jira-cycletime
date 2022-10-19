<?php

namespace Tests\Feature\Command;

use App\Jobs\GetChangeLogs;
use App\Models\Estimate;
use App\Models\Issue;
use App\Models\Transition;
use App\Models\User;
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
        self::assertEquals(0, count(User::all()));
//        self::assertEquals(0, count(Estimate::all()));
        Artisan::call('cycletime:issues', ['resultsToGet' => 1]);

        // Assert
        self::assertEquals(1, Issue::where('key', 'UNS-34')->count());
        self::assertNotNull(Issue::where('key', 'UNS-34')->first()->transition);
        self::assertNotNull(Issue::where('key', 'UNS-34')->first()->user);
        self::assertEquals(1, User::whereAccountId('5d0196fe59b0d90c57bddda3')->count());
        self::assertNotNull(1, User::whereAccountId('5d0196fe59b0d90c57bddda3')->first()->issue);
//        self::assertEquals(1, count(Estimate::all()));
        Queue::assertPushed(GetChangeLogs::class, 1);
    }
}
