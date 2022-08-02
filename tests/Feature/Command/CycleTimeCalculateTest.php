<?php

namespace Tests\Feature\Command;

use App\Models\Issue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Queue;
use Tests\TestCase;

class CycleTimeCalculateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testCycleTimeCalculate(): void
    {
        Queue::fake();

        // Call the method with nothing in the database
        $this->artisan('cycletime:calculate')->assertSuccessful();
        $results = Issue::needsNewCycletime();

        // Assert no results and nothing on the queue
        self::assertEquals(0, $results->get()->count());
        Queue::assertNothingPushed();

        // @todo 1 to add to the queue (done is not null)

        // @todo 1 to not add to the queue (done is null)
    }


}
