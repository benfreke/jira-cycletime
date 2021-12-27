<?php

namespace App\Console\Commands;

use App\Models\Issue;
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
    public function handle()
    {
        // Set my parameters
        $response = Http::withBasicAuth('ben@netengine.com.au', config('cycletime.token'))
            ->acceptJson()->get(config('cycletime.jira-url') . 'rest/api/3/search', [
                'jql' => $this->getJql(),
                'startAt' => 0,
                'maxResults' => 10,
                'fields' => [
                    'summary',
                    'statusCategory',
                ],
            ]);
        $this->info($this->getJql());
        $this->info('Total ' . count($response->json('issues')));
        foreach ($response->json('issues') as $issue) {
            Issue::updateOrCreate(
                ['issue_id' => $issue['key']],
                [
                    'summary' => $issue['fields']['summary'],
                    'last_jira_update' => $issue['fields']['updated'],
                ]
            );
        }
        $this->info(Issue::count());
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
        $jql .= ' AND updated >= ' . $this->getLastUpdatedDate();

        $jql .= ' ORDER BY updated ASC';

        return $jql;
    }

    /**
     * @return string The last updated time we have in the DB, in hours
     */
    private function getLastUpdatedDate(): string
    {
        return '-100h';
    }
}
