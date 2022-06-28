<?php

namespace App\Console\Commands;

use App\Models\Issue;
use Illuminate\Database\Eloquent\Builder;

class EstimateDisplay extends CycleTimeDisplay
{
    const TOTAL = 'Total';

    const HAS_ESTIMATE = 'Estimated';

    const TOTAL_ESTIMATED = 'Total Estimated';

    const VARIANCE = 'Variance';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'estimate:display';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display the estimates for a user or the team';

    protected function getBaseQuery(): Builder|Issue
    {
        return Issue::onlyValidAssignees()
            ->OnlyValidTypes()
            ->leftJoin(
                'estimates',
                'estimates.issue_id',
                '=',
                'issues.id'
            )
            ->join(
                'transitions',
                'issues.issue_id',
                '=',
                'transitions.issue_id'
            );
    }

    protected function displayTable($query, $timePeriod)
    {
        $outputResults = [];
        foreach ($query->get() as $row) {
            if (!isset($outputResults[$row->assignee])) {
                $outputResults = $this->setDefaultOutput($outputResults, $row->assignee);
            }
            $outputResults[$row->assignee][self::TOTAL]++;
            if ($row->estimated) {
                $outputResults[$row->assignee][self::TOTAL_ESTIMATED]++;
            }
            if ($row->spent) {
                $outputResults[$row->assignee][self::VARIANCE][] = [
                    self::TOTAL_ESTIMATED => $row->estimated,
                    self::TOTAL => $row->spent,
                ];
            }
        }

        $output = [];
        foreach ($outputResults as $name => $result) {
            $output[] = [
                $name,
                $result[self::TOTAL],
                $result[self::TOTAL_ESTIMATED],
                round(($result[self::TOTAL_ESTIMATED] / $result[self::TOTAL]) * 100),
                $this->getVariance($result[self::VARIANCE]),
            ];
        }
        $this->outputTable(
            ['Name', self::TOTAL, self::HAS_ESTIMATE, self::TOTAL_ESTIMATED, self::VARIANCE],
            $output
        );
    }

    protected function setDefaultOutput(array $oldArray, string $assignee): array
    {
        $oldArray[$assignee] = [];
        $oldArray[$assignee][self::TOTAL] = 0;
        $oldArray[$assignee][self::HAS_ESTIMATE] = 0;
        $oldArray[$assignee][self::TOTAL_ESTIMATED] = 0;
        $oldArray[$assignee][self::VARIANCE] = [];

        return $oldArray;
    }

    private function getVariance(array $fullResults): string
    {
        $defaultValue = '0%';
        if (empty($fullResults)) {
            return $defaultValue;
        }
        $variances = [];
        foreach ($fullResults as $result) {
            if (!$result[self::TOTAL_ESTIMATED]) {
                $variances[] = 100;
                continue;
            }
            $variances[] = abs(
                    ($result[self::TOTAL_ESTIMATED] - $result[self::TOTAL])
                    / $result[self::TOTAL_ESTIMATED]
                ) * 100;
        }
        if (count($variances)) {
            return round(array_sum($variances) / count($variances)) . '%';
        }
        return $defaultValue;
    }
}
