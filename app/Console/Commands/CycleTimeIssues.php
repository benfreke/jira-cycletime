<?php

namespace App\Console\Commands;

use App\Jobs\GetChangeLogs;
use App\Models\Issue;
use App\Models\User;
use App\Services\Jira;
use Exception;
use Illuminate\Console\Command;

class CycleTimeIssues extends Command
{
    /**
     * Get all issues from Jira
     *
     * @var string
     */
    protected $signature = 'cycletime:issues {resultsToGet=20}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get all issues';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(private readonly int $resultsToGet = 60)
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
        $jiraService = new Jira($this->resultsToGet);

        foreach ($jiraService->getIssues() as $issue) {
            try {
                // Make sure all potential values exist
                if (empty($issue['fields']['assignee'])) {
                    // If no assignee, then we don't care about any values
                    continue;
                }
                // Make sure we have the user created as well.
                $userFields = [
                    'name' => $issue['fields']['assignee']['displayName'],
                    'email' => $issue['fields']['assignee']['emailAddress'],
                    'timezone' => $issue['fields']['assignee']['timeZone'],
                    'avatar' => $issue['fields']['assignee']['avatarUrls']['32x32'],
                ];
                $userKey = [
                    'account_id' => $issue['fields']['assignee']['accountId'],
                ];
                $userModel = User::updateOrCreate($userKey, $userFields);

                $issueFields = [
                    'summary' => $issue['fields']['summary'],
                    'last_jira_update' => $issue['fields']['updated'],
                    'project' => $issue['fields']['project']['key'],
                    'issue_type' => $issue['fields']['issuetype']['name'],
                    'user_id' => $userModel->id,
                ];

                // Define the key we want to be unique
                $issueKey = ['key' => $issue['key']];
                $issueModel = Issue::updateOrCreate($issueKey, $issueFields);

                // Make sure the transition exists as well.
                // This will be filled by later jobs
                if (is_null($issueModel->transition)) {
                    $issueModel->transition()->create();
                }

                // We have the values for this relation, so set them now
//                @todo add estimations back in
//                $issueModel->estimate()->updateOrCreate(['issue_id' => $issueModel->id], [
//                    'spent' => $issue['fields']['timespent'],
//                    'estimated' => $issue['fields']['timeoriginalestimate'],
//                ]);

                GetChangeLogs::dispatch($issueModel);
            } catch (Exception $exception) {
                $this->error($exception->getMessage());
            }
        }

        return Command::SUCCESS;
    }

}
