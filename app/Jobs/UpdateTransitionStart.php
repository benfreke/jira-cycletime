<?php

namespace App\Jobs;

use App\Models\Transition;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateTransitionStart implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private readonly Transition $transition, private CarbonImmutable $changelogStartTime)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("Checking $this->transition->issue_id");
        if ($this->transition->isOlderStart($this->changelogStartTime)) {
            Log::info('Start updated: ' . $this->transition->issue_id);
            $this->transition->start = $this->changelogStartTime;
            $this->transition->save();
        }
    }
}
