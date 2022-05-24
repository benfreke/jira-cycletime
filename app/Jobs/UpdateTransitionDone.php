<?php

namespace App\Jobs;

use App\Models\Transition;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateTransitionDone implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private readonly Transition $transition, private CarbonImmutable $changelogDoneTime)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->transition->isNewerDone($this->changelogDoneTime)) {
            $this->transition->done = $this->changelogDoneTime;
            $this->transition->save();
        }
    }
}
