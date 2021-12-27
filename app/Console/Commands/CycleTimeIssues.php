<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use JiraRestApi\Issue\IssueService;

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
                'maxResults' => 2,
                'fields' => [
                    'summary',
                    'statusCategory',
                ],
            ]);
        $results = $response->json();
        var_dump($results);
        return Command::SUCCESS;
    }

    private function getJql(): string
    {
        $jql = 'project IN ("Unscheduled","Planned Work")';
        $jql .= ' AND statuscategory = "Complete"';

        $jql .= ' ORDER BY updated DESC';

        return $jql;
    }
}
