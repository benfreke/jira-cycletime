<?php

namespace App\Console\Commands;

use App\Models\Issue;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CycleTimeDisplay extends Command
{
    const PROJECT_PLANNED = 'PLAN';

    const PROJECT_UNSCHEDULED = 'UNS';

    const OUTPUT_TOTAL = 'total';

    const OUTPUT_COUNT = 'count';

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
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Get the previous quarter cycle time
        $lastQuarter = Issue::where('assignee', '!=', 'Ben Freke')
            ->whereNotNull('cycletime')
            ->whereDate('last_jira_update', '<', Carbon::now()->firstOfQuarter());
        $thisQuarter = Issue::where('assignee', '!=', 'Ben Freke')
            ->whereNotNull('cycletime')
            ->whereDate('last_jira_update', '>', Carbon::now()->firstOfQuarter())
            ->whereDate('last_jira_update', '<', Carbon::now()->lastOfQuarter());

        $this->info('Last quarter: ' . $lastQuarter->avg('cycletime'));
        $this->info('This quarter: ' . $thisQuarter->avg('cycletime'));
        // Create an array, for output of results
        $outputResults = [];
        foreach ($lastQuarter->get() as $row) {
            if (!$row->cycletime) {
                continue;
            }
            if (!isset($outputResults[$row->assignee])) {
                $outputResults = $this->setDefaultOutput($outputResults, $row->assignee);
            }
            $outputResults[$row->assignee][$row->project][self::OUTPUT_COUNT]++;
            $outputResults[$row->assignee][$row->project][self::OUTPUT_TOTAL] += $row->cycletime;
        }

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
