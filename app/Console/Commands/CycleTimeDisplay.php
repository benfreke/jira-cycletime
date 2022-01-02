<?php

namespace App\Console\Commands;

use App\Models\Issue;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class CycleTimeDisplay extends Command
{
    const PROJECT_PLANNED = 'PLAN';

    const PROJECT_UNSCHEDULED = 'UNS';

    const OUTPUT_TOTAL = 'total';

    const OUTPUT_COUNT = 'count';

    const TIME_PERIOD_THIS_QUARTER = 'This quarter';

    const TIME_PERIOD_LAST_QUARTER = 'Last quarter';

    const TIME_PERIOD_LAST_MONTH = 'Last month';

    const TIME_PERIOD_THIS_MONTH = 'This month';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cycletime:display';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Displays the cycletime in a table format';

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
     * Prompts for display options for cycletime
     *
     * This could be done much neater in a raw SQL command, but this is simpler and easier to read
     *
     * @return int
     */
    public function handle()
    {

        $query = $this->getBaseQuery();

        // Prompt for the time period we want results for
        $timePeriod = $this->choice(
            'What time period do you want results for?',
            [
                self::TIME_PERIOD_LAST_QUARTER,
                self::TIME_PERIOD_THIS_QUARTER,
                self::TIME_PERIOD_LAST_MONTH,
                self::TIME_PERIOD_THIS_MONTH,
            ],
            self::TIME_PERIOD_LAST_MONTH
        );

        // Scope the results to the time period
        match ($timePeriod) {
            self::TIME_PERIOD_LAST_QUARTER => $query->lastQuarter(),
            self::TIME_PERIOD_THIS_QUARTER => $query->thisQuarter(),
            self::TIME_PERIOD_LAST_MONTH => $query->lastMonth(),
            self::TIME_PERIOD_THIS_MONTH => $query->thisMonth(),
        };

        // If we want to restrict results to a single user, we do this here
        if ($this->confirm('Only for a single user?')) {
            $assigneeToLimit = $this->choice(
                'Select the user to get details for',
                $this->getBaseQuery()->groupBy('assignee')->pluck('assignee')->toArray()
            );
            $query->whereAssignee($assigneeToLimit);
        }

        $this->info($timePeriod);
        // Create an array, for output of results
        $outputResults = [];
        foreach ($query->get() as $row) {
            if (!$row->cycletime) {
                continue;
            }
            if (!isset($outputResults[$row->assignee])) {
                $outputResults = $this->setDefaultOutput($outputResults, $row->assignee);
            }
            $outputResults[$row->assignee][$row->project][self::OUTPUT_COUNT]++;
            $outputResults[$row->assignee][$row->project][self::OUTPUT_TOTAL] += $row->cycletime;
        }

        // Now convert the results into an array for the table output
        $output = [];
        foreach ($outputResults as $name => $results) {
            $combined = [
                self::OUTPUT_TOTAL => $results[self::PROJECT_PLANNED][self::OUTPUT_TOTAL] + $results[self::PROJECT_UNSCHEDULED][self::OUTPUT_TOTAL],
                self::OUTPUT_COUNT => $results[self::PROJECT_PLANNED][self::OUTPUT_COUNT] + $results[self::PROJECT_UNSCHEDULED][self::OUTPUT_COUNT],
            ];
            $output[] = [
                $name,
                $this->getAverage($results['PLAN']),
                $results[self::PROJECT_PLANNED][self::OUTPUT_COUNT],
                $this->getAverage($results['UNS']),
                $results[self::PROJECT_UNSCHEDULED][self::OUTPUT_COUNT],
                $this->getAverage($combined),
                $results[self::PROJECT_PLANNED][self::OUTPUT_COUNT] + $results[self::PROJECT_UNSCHEDULED][self::OUTPUT_COUNT],
            ];
        }
        $this->table(
            ['Name', 'Planned', 'Total', 'Unscheduled', 'Total', 'Average', 'Total'],
            $output
        );
        return self::SUCCESS;
    }

    /**
     * Gets the base SQL to use for all queries
     *
     * We need to use a join as Eloquent relationships aren't called until after a get
     *
     * @return Builder|Issue
     */
    private function getBaseQuery(): Builder|Issue
    {
        return Issue::hasCycleTime()->OnlyValidAssignees()->join(
            'transitions',
            'issues.issue_id',
            '=',
            'transitions.issue_id'
        );
    }

    /**
     * Gets the average cycletime for a given period
     *
     * @param  array  $results
     *
     * @return float
     */
    private function getAverage(array $results): float
    {
        if ($results[self::OUTPUT_COUNT] === 0) {
            return 0.00;
        }
        return number_format($results[self::OUTPUT_TOTAL] / $results[self::OUTPUT_COUNT], 2);
    }

    private function setDefaultOutput(array $oldArray, string $assignee): array
    {
        $oldArray[$assignee] = [];
        $oldArray[$assignee][self::PROJECT_PLANNED] = [];
        $oldArray[$assignee][self::PROJECT_PLANNED][self::OUTPUT_COUNT] = 0;
        $oldArray[$assignee][self::PROJECT_PLANNED][self::OUTPUT_TOTAL] = 0;
        $oldArray[$assignee][self::PROJECT_UNSCHEDULED] = [];
        $oldArray[$assignee][self::PROJECT_UNSCHEDULED][self::OUTPUT_COUNT] = 0;
        $oldArray[$assignee][self::PROJECT_UNSCHEDULED][self::OUTPUT_TOTAL] = 0;

        return $oldArray;
    }
}
