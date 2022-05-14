<?php

namespace Tests\Feature\Command;

use App\Models\Issue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CycleTimeCalculateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testCycleTimeCalculate()
    {
        // Arrange

        // Act
        $this->artisan('cycletime:calculate')->assertSuccessful();

        // Assert
        $results = Issue::needsNewCycletime();
        self::assertEquals(0, $results->get()->count());
    }
}
