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
            ->where('spent', '>', 0)
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

    protected function displayTable($query, $singleUser)
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
                round(($result[self::TOTAL_ESTIMATED] / $result[self::TOTAL]) * 100) . '%',
                $this->getVariance($result[self::VARIANCE]),
            ];
        }

        // If we have multiple people, let's do the total averages
        if (!$singleUser) {
            $output[] = [
                'Averages',
                $this->getAverageFromColumn($output, 1),
                $this->getAverageFromColumn($output, 2),
                $this->getAverageFromColumn($output, 3),
                $this->getAverageFromColumn($output, 4),
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

    protected function displayInformation(array|string $timePeriod, array|string $assignee)
    {
        $query = $this->getBaseQuery();
        $query = $this->setQueryTime($query, $timePeriod);
        $this->table(
            ['id', 'estimated', 'spent', 'variance', 'summary'],
            $this->addVariance(
                $query->whereAssignee($assignee)
                    ->orderByDesc('cycletime')
                    ->without(['transition'])
                    ->get(
                        ['issues.issue_id', 'estimated', 'spent', 'summary']
                    )->toArray()
            )
        );
    }

    /**
     * Gets the average of a column
     *
     * Only counts rows that have a non zero value
     *
     * @param  array  $resultSet
     * @param  int  $columnKey
     *
     * @return string
     */
    protected function getAverageFromColumn(array $resultSet, int $columnKey): string
    {
        $total = 0;
        $count = count($resultSet);
        if (!$count) {
            return 0;
        }
        $cellsWithZero = 0;
        for ($i = 0; $i < $count; $i++) {
            $cellNumber = intval(rtrim($resultSet[$i][$columnKey], '%'));
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

    private function addVariance(array $rawResults): array
    {
        $fixedResults = [];
        foreach ($rawResults as $index => $row) {
            $fixedResults[$index][0] = $row['issue_id'];
            $fixedResults[$index][1] = $row['estimated'] / 60;
            $fixedResults[$index][2] = $row['spent'] / 60;
            $fixedResults[$index][3] = $this->getVarianceValue($row['estimated'], $row['spent']);
            $fixedResults[$index][4] = $row['summary'];
        }
        return $fixedResults;
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
            $variances[] = $this->getVarianceValue($result[self::TOTAL_ESTIMATED], $result[self::TOTAL]);
        }
        if (count($variances)) {
            return round(array_sum($variances) / count($variances)) . '%';
        }
        return $defaultValue;
    }

    private function getVarianceValue(?int $estimated, ?int $spent): float
    {
        if (!$estimated || !$spent) {
            return 100.00;
        }
        return number_format(
            abs(
                ($estimated - $spent)
                / $estimated
            ) * 100
        );
    }
}
