<?php

namespace Tests\Unit\Models;

use App\Models\Issue;
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
        self::assertNull(Issue::getLastUpdatedDate());
    }
}
