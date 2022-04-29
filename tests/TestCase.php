<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Gets a specific fixture for testing purposes
     * @param $fileName
     *
     * @return mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getFixture($fileName): mixed
    {
        return json_decode(\Storage::drive('fixtures')->get($fileName), true);
    }

    /**
     * Save a fixture for testing
     *
     * @param  string  $fileName
     * @param  string  $contents
     *
     * @return bool
     */
    public function setFixture(string $fileName, string $contents): bool
    {
        return \Storage::drive('fixtures')->put($fileName, $contents);
    }
}
