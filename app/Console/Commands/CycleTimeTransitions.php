<?php

namespace App\Console\Commands;

use App\Models\Issue;
use App\Models\Status;
use App\Models\Transition;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CycleTimeTransitions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cycletime:transitions {key?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gets all the transitions for a specific issue';

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
        $issueIdToFind = $this->argument('key');
        if (!$issueIdToFind) {
            // Let's prompt for the issue id
            $issueIdToFind = $this->ask('Issue ID to find transtions for:');
        }
        if (!$issueIdToFind) {
            $this->error('No issue key provided!');
            return self::FAILURE;
        }
        if (!Issue::whereIssueId($issueIdToFind)->count()) {
            $this->error('Invalid key provided. No matching record found');
            return self::FAILURE;
        }
        $response = Http::withBasicAuth(config('cycletime.jira-user'), config('cycletime.token'))
            ->acceptJson()->get(config('cycletime.jira-url') . "rest/api/3/issue/$issueIdToFind/changelog", []);
        $result = $response->json();
        $transition = Transition::firstOrNew(['issue_id' => $issueIdToFind]);
        foreach ($result['values'] as $transitionItem) {
            $transitionTime = $transitionItem['created'];
            foreach ($transitionItem['items'] as $item) {
                if ($this->isStartTransition($item) && $transition->isOlderStart(
                        Carbon::createFromTimestamp($transitionTime)
                    )) {
                    $transition->start = $transitionTime;
                }

                if ($this->isDoneTransition($item) && $transition->isNewerDone(
                        Carbon::createFromTimestamp($transitionTime)
                    )) {
                    $transition->done = $transitionTime;
                }
            }
        }
        // Only save if something has changed
        if ($transition->isDirty()) {
            $transition->save();
        }

        return self::SUCCESS;
    }

    private function isStartTransition(array $transition): bool
    {
        if ($transition['field'] !== 'status') {
            return false;
        }
        return (Status::isToDoCategory($transition['fromString'])
            && Status::isInProgressCategory($transition['toString']));
    }

    private function isDoneTransition(array $transition): bool
    {
        if ($transition['field'] !== 'status') {
            return false;
        }
        return (Status::isInProgressCategory($transition['fromString'])
            && Status::isDoneCategory($transition['toString']));
    }
}
