<?php

namespace App\Console\Commands;

use App\Models\Issue;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class CycleTimeDisplay extends Command
{
    const PROJECT_SCOUT_FEATURES = 'PLAN';

    const PROJECT_SCOUT_SUPPORT = 'UNS';

    const PROJECT_SCOUT_CBW = 'CBW';

    const PROJECT_AGENCY = 'AGENCY';

    const OUTPUT_TOTAL = 'total';

    const OUTPUT_COUNT = 'count';

    const TIME_PERIOD_THIS_QUARTER = 'This quarter';

    const TIME_PERIOD_LAST_QUARTER = 'Last quarter';

    const TIME_PERIOD_LAST_MONTH = 'Last month';

    const TIME_PERIOD_TWO_MONTHS = '2 months ago';

    const TIME_PERIOD_THREE_MONTHS = '3 months ago';

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
                self::TIME_PERIOD_TWO_MONTHS,
                self::TIME_PERIOD_THREE_MONTHS,
                self::TIME_PERIOD_THIS_MONTH,
            ],
            self::TIME_PERIOD_LAST_MONTH
        );

        $query = $this->setQueryTime($query, $timePeriod);

        // If we want to restrict results to a single user, we do this here
        if ($this->confirm('Only for a single user?')) {
            $assigneeToLimit = $this->choice(
                'Select the user to get details for',
                $this->getBaseQuery()->groupBy('assignee')->pluck('assignee')->toArray()
            );
            $query->whereAssignee($assigneeToLimit);
        }

        $this->info($timePeriod);
        $this->displayTable($query, $timePeriod);

        return self::SUCCESS;
    }

    protected function displayTable($query, $timePeriod)
    {
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
                self::OUTPUT_TOTAL => $results[self::PROJECT_SCOUT_FEATURES][self::OUTPUT_TOTAL]
                    + $results[self::PROJECT_SCOUT_SUPPORT][self::OUTPUT_TOTAL]
                    + $results[self::PROJECT_SCOUT_CBW][self::OUTPUT_TOTAL]
                    + $results[self::PROJECT_AGENCY][self::OUTPUT_TOTAL],
                self::OUTPUT_COUNT => $results[self::PROJECT_SCOUT_FEATURES][self::OUTPUT_COUNT]
                    + $results[self::PROJECT_SCOUT_SUPPORT][self::OUTPUT_COUNT]
                    + $results[self::PROJECT_SCOUT_CBW][self::OUTPUT_COUNT]
                    + $results[self::PROJECT_AGENCY][self::OUTPUT_COUNT],
            ];
            $output[] = [
                $name,
                $this->getAverage($results[self::PROJECT_SCOUT_FEATURES]),
                $results[self::PROJECT_SCOUT_FEATURES][self::OUTPUT_COUNT],
                $this->getAverage($results[self::PROJECT_SCOUT_SUPPORT]),
                $results[self::PROJECT_SCOUT_SUPPORT][self::OUTPUT_COUNT],
                $this->getAverage($results[self::PROJECT_SCOUT_CBW]),
                $results[self::PROJECT_SCOUT_CBW][self::OUTPUT_COUNT],
                $this->getAverage($results[self::PROJECT_AGENCY]),
                $results[self::PROJECT_AGENCY][self::OUTPUT_COUNT],
                $this->getAverage($combined),
                $results[self::PROJECT_SCOUT_FEATURES][self::OUTPUT_COUNT]
                + $results[self::PROJECT_SCOUT_SUPPORT][self::OUTPUT_COUNT]
                + $results[self::PROJECT_SCOUT_CBW][self::OUTPUT_COUNT]
                + $results[self::PROJECT_AGENCY][self::OUTPUT_COUNT],
            ];
        }
        // If we have multiple people, let's do the total averages
        if (!isset($assigneeToLimit)) {
            $output[] = [
                'Averages',
                $this->getAverageFromColumn($output, 1),
                '-',
                $this->getAverageFromColumn($output, 3),
                '-',
                $this->getAverageFromColumn($output, 5),
                '-',
                $this->getAverageFromColumn($output, 7),
                '-',
                $this->getAverageFromColumn($output, 9),
                '-',
            ];
        }

        $this->outputTable(
            ['Name', 'Features', 'Total', 'Support', 'Total', 'CBW', 'Total', 'Agency', 'Total', 'Average', 'Total'],
            $output
        );

        if (isset($assigneeToLimit)) {
            $query = $this->getBaseQuery();
            $query = $this->setQueryTime($query, $timePeriod);
            $this->table(
                ['id', 'cycletime', 'summary'],
                $query->whereAssignee($assigneeToLimit)
                    ->orderByDesc('cycletime')
                    ->without(['transition', 'estimate'])
                    ->get(
                        ['issues.issue_id', 'cycletime', 'summary']
                    )->toArray()
            );
        }
    }

    protected function outputTable(array $headerRow, array $values)
    {
        $this->table(
            $headerRow,
            $values
        );
    }

    /**
     * Gets the base SQL to use for all queries
     *
     * We need to use a join as Eloquent relationships aren't called until after a get
     *
     * @return Builder|Issue
     */
    protected function getBaseQuery(): Builder|Issue
    {
        return Issue::hasCycleTime()
            ->onlyValidAssignees()
            ->OnlyValidTypes()
            ->join(
                'transitions',
                'issues.issue_id',
                '=',
                'transitions.issue_id'
            );
    }

    protected function setDefaultOutput(array $oldArray, string $assignee): array
    {
        $oldArray[$assignee] = [];
        $oldArray[$assignee][self::PROJECT_SCOUT_FEATURES] = [];
        $oldArray[$assignee][self::PROJECT_SCOUT_FEATURES][self::OUTPUT_COUNT] = 0;
        $oldArray[$assignee][self::PROJECT_SCOUT_FEATURES][self::OUTPUT_TOTAL] = 0;
        $oldArray[$assignee][self::PROJECT_SCOUT_SUPPORT] = [];
        $oldArray[$assignee][self::PROJECT_SCOUT_SUPPORT][self::OUTPUT_COUNT] = 0;
        $oldArray[$assignee][self::PROJECT_SCOUT_SUPPORT][self::OUTPUT_TOTAL] = 0;
        $oldArray[$assignee][self::PROJECT_SCOUT_CBW] = [];
        $oldArray[$assignee][self::PROJECT_SCOUT_CBW][self::OUTPUT_COUNT] = 0;
        $oldArray[$assignee][self::PROJECT_SCOUT_CBW][self::OUTPUT_TOTAL] = 0;
        $oldArray[$assignee][self::PROJECT_AGENCY] = [];
        $oldArray[$assignee][self::PROJECT_AGENCY][self::OUTPUT_COUNT] = 0;
        $oldArray[$assignee][self::PROJECT_AGENCY][self::OUTPUT_TOTAL] = 0;

        return $oldArray;
    }

    /**
     * Scope the results to the time period
     *
     * @param  Builder|Issue  $builder
     * @param  string  $timePeriod
     *
     * @return Builder|Issue
     */
    private function setQueryTime(Builder|Issue $builder, string $timePeriod): Builder|Issue
    {
        match ($timePeriod) {
            self::TIME_PERIOD_LAST_QUARTER => $builder->lastQuarter(),
            self::TIME_PERIOD_THIS_QUARTER => $builder->thisQuarter(),
            self::TIME_PERIOD_LAST_MONTH => $builder->lastMonth(),
            self::TIME_PERIOD_TWO_MONTHS => $builder->lastTwoMonths(),
            self::TIME_PERIOD_THREE_MONTHS => $builder->lastThreeMonths(),
            self::TIME_PERIOD_THIS_MONTH => $builder->thisMonth(),
        };

        return $builder;
    }

    /**
     * Gets the average of a column
     *
     * Only counts rows that have a non zero value
     *
     * @param  array  $resultSet
     * @param  int  $columnKey
     *
     * @return float
     */
    private function getAverageFromColumn(array $resultSet, int $columnKey): float
    {
        $total = 0;
        $count = count($resultSet);
        if (!$count) {
            return 0;
        }
        $cellsWithZero = 0;
        for ($i = 0; $i < $count; $i++) {
            $cellNumber = $resultSet[$i][$columnKey];
            $total += $cellNumber;

            // If the value was zero, don't count this when deciding the average
            if (!$cellNumber) {
                $cellsWithZero++;
            }
        }
        // Exit early if they cancel each other out
        if ($count === $cellsWithZero) {
            return 0;
        }
        return number_format($total / ($count - $cellsWithZero), 2);
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
}
