<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Console\Command\Command as CommandAlias;

use function config;

/**
 * Test that everything is connected properly
 */
class CycleTimeTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cycletime:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the connection to Jira';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $response = Http::withToken(config('cycletime.token'))
            ->acceptJson()->get(config('cycletime.jira-url') . 'rest/api/3/serverInfo');

        $this->info('Testing connection');
        if (!$response->ok()) {
            $this->error('FAIL: Received a ' . $response->status() . ' response');
            return CommandAlias::FAILURE;
        }
        $this->info('SUCCESS: ' . $response->json('baseUrl'));
        return CommandAlias::SUCCESS;
    }
}
