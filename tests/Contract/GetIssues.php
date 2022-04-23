<?php

namespace Tests\Contract;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Let's make sure Jira doesn't start returning bad data
 */
class GetIssues extends TestCase
{
    public function testGetIssues()
    {
        // Arrange
//        Load from file system
        Http::fake(['*' => Http::response()]);

        // Act
        $response = $this->get();

        // Assert
        self::assertJsonStringEqualsJsonFile('', $response->json());
    }
}
