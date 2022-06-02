<?php

namespace App\Console\Commands;

use App\Jobs\UpdateCycleTime;
use App\Models\Issue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CycleTimeCalculate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cycletime:calculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate the cycle time for all eligible Issues';

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
        $results = Issue::needsNewCycletime();
        Log::info('Total to calculate: ' . $results->count());
        foreach ($results->get() as $issue) {
            Log::info('Dispatching cycletime calculation for ' . $issue->issue_id);
            UpdateCycleTime::dispatch($issue);
        }
        return self::SUCCESS;
    }
}
