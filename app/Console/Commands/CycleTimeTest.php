<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
    public function handle()
    {
        return Command::SUCCESS;
    }
}
