<?php

namespace App\Jobs;

use App\Models\Issue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateCycleTime implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private readonly int $issueId)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $issue = Issue::find($this->issueId);
        Log::info('Calculating cycletime for ' . $issue->issue_id);
        $issue->cycletime = $issue->transition->done->diffInBusinessDays($issue->transition->start);
        $issue->save();
        $issue->touch();
    }
}
