<?php

namespace App\Jobs;

use App\Models\Issue;
use App\Services\Jira;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class GetChangeLogs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private Issue $issue, private readonly Jira $jiraService = new Jira())
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $result = $this->jiraService->getIssueChangelogs($this->issue->issue_id);

        foreach ($result as $index => $changeLogItem) {
            foreach ($changeLogItem['items'] as $changeLog) {
                // If this is a status transition, let's do some stuff
                if ($this->jiraService->isStartTransition($changeLog)) {
                    // Do something here
                    UpdateTransitionStart::dispatch($this->issue->transition)->delay(now()->addSeconds(10 * $index));
                }
                if ($this->jiraService->isDoneTransition($changeLog)) {
                    UpdateTransitionDone::dispatch($this->issue->transition)->delay(now()->addSeconds(10 * $index));
                }
            }
        }
    }
}
