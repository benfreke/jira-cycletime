<?php

namespace App\Console\Commands;

use App\Jobs\GetChangeLogs;
use App\Models\Issue;
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

        $results = $jiraService->getIssues();
        $totalFound = count($results);

        $this->info("Attempting to find $this->resultsToGet issues");
        $this->info("Total found $totalFound");

        foreach ($results as $issue) {
            try {
                // Make sure all potential values exist
                if (empty($issue['fields']['assignee'])) {
                    throw new Exception("Assignee field blank for {$issue['key']}");
                }
                $upsertFields = [
                    'summary' => $issue['fields']['summary'],
                    'last_jira_update' => $issue['fields']['updated'],
                    'assignee' => $issue['fields']['assignee']['displayName'],
                    'project' => $issue['fields']['project']['key'],
                    'issue_type' => $issue['fields']['issuetype']['name'],
                ];
                $keyField = ['issue_id' => $issue['key']];

                $issueModel = Issue::updateOrCreate($keyField, $upsertFields);
                // Make sure the transition exists as well
                if (is_null($issueModel->transition)) {
                    $issueModel->transition()->create();
                }

                GetChangeLogs::dispatch($issueModel);
            } catch (Exception $exception) {
                $this->error($exception->getMessage());
            }
        }

        // Let's go around again if we need to
        if ($totalFound === $this->resultsToGet) {
            return $this->call(CycleTimeIssues::class);
        }

        $this->info("Remember to call php artisan cycletime:calculate to generate the correct cycletime");

        return Command::SUCCESS;
    }

}
