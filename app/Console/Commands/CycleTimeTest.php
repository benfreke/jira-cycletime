<?php

namespace App\Console\Commands;

use App\Models\Issue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

use function config;

/**
 * Test that everything is connected properly
 */
class CycleTimeTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cycletime:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the connection to Jira';

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
//        Manual updating script
//        $newQuery = Issue::onlyValidAssignees()->join(
//            'transitions',
//            'issues.issue_id',
//            '=',
//            'transitions.issue_id'
//        )->whereNull('done');
//
//        foreach($newQuery->get() as $nullDoneIssue) {
//            $this->call(CycleTimeTransitions::class, ['key' => $nullDoneIssue['issue_id']]);
//        }

        $response = Http::withToken(config('cycletime.token'))
            ->acceptJson()->get(config('cycletime.jira-url') . 'rest/api/3/serverInfo');

        $this->info('Testing connection');
        if (!$response->ok()) {
            $this->error('FAIL: Received a ' . $response->status() . ' response');
            return self::FAILURE;
        }
        $this->info('SUCCESS: ' . $response->json('baseUrl'));
        return self::SUCCESS;
    }
}
