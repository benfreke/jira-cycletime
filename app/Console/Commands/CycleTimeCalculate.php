<?php

namespace App\Console\Commands;

use App\Models\Issue;
use App\Models\Transition;
use Illuminate\Console\Command;

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
    public function handle()
    {
        $results = Transition::whereNotNull(['done', 'start'])->get();
        foreach($results as $issue) {
            $this->calculateCycleTime($issue->issue_id);
        }
        return self::SUCCESS;
    }

    private function calculateCycleTime(string $issueId): void
    {
        $issue = Issue::whereIssueId($issueId)->first();
        $transition = Transition::whereIssueId($issueId)->first();
        $issue->cycletime = $transition->done->diffInBusinessDays($transition->start);
        $issue->save();
    }
}
