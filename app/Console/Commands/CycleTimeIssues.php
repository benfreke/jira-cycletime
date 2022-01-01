<?php

namespace App\Console\Commands;

use App\Models\Issue;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CycleTimeIssues extends Command
{
    /**
     * Get all issues from Jira
     *
     * @var string
     */
    protected $signature = 'cycletime:issues';

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
        $resultsToGet = 50;

        $response = Http::withBasicAuth(config('cycletime.jira-user'), config('cycletime.token'))
            ->acceptJson()->get(config('cycletime.jira-url') . 'rest/api/3/search', [
                'jql' => $this->getJql(),
                'startAt' => 0,
                'maxResults' => $resultsToGet,
                'fields' => [
                    'summary',
                    'statusCategory',
                ],
            ]);

        $this->info("Attempting to find $resultsToGet issues");
        $this->info('Total found ' . count($response->json('issues')));

        foreach ($response->json('issues') as $issue) {
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

                Issue::updateOrCreate($keyField, $upsertFields);

                $this->call(CycleTimeTransitions::class, ['key' => $issue['key']]);
            } catch (Exception $exception) {
                $this->error($exception->getMessage());
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Get all the issues in our projects, that are completed
     *
     * @return string
     */
    private function getJql(): string
    {
        $jql = 'project IN ("Unscheduled","Planned Work")';
        $jql .= ' AND statuscategory = "Complete"';
        $updatedHours = $this->getLastUpdatedDate();
        if ($updatedHours) {
            $jql .= " AND updated >= $updatedHours";
        }

        $jql .= ' ORDER BY updated ASC';

        return $jql;
    }

    /**
     * @return string|null The last updated time we have in the DB, in hours
     */
    private function getLastUpdatedDate(): ?string
    {
        $lastUpdatedIssue = Issue::latest('last_jira_update')->first();
        if (!isset($lastUpdatedIssue->last_jira_update)) {
            return null;
        }
        $hours = $lastUpdatedIssue->last_jira_update->diffInHours();
        if ($hours) {
            // Add an extra hour to ensure we don't miss anything
            $hours++;
            return "-${hours}h";
        }
        return null;
    }
}
