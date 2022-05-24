<?php

namespace App\Services;

use App\Models\Issue;
use App\Models\Status;
use Illuminate\Support\Facades\Http;

class Jira
{
    public function __construct(
        private readonly int $resultsToGet = 60,
        private readonly Issue $issueModel = new Issue()
    ) {
    }

    /**
     * Get the issues from Jira
     *
     * @return array
     */
    public function getIssues(): array
    {
        $response = Http::withBasicAuth(config('cycletime.jira-user'), config('cycletime.token'))
            ->acceptJson()->get(config('cycletime.jira-host') . config('cycletime.jira-url'), [
                'jql' => $this->getJql(),
                'startAt' => 0,
                'maxResults' => $this->resultsToGet,
                'fields' => [
                    'summary',
                    'statusCategory',
                ],
            ]);

        return $response->json('issues');
    }

    /**
     * @param  string  $issueId
     *
     * @return array
     */
    public function getIssueChangelogs(string $issueId): array
    {
        $response = Http::withBasicAuth(config('cycletime.jira-user'), config('cycletime.token'))
            ->acceptJson()->get(
                config('cycletime.jira-host') . "rest/api/3/issue/$issueId/changelog",
                []
            );

        return $response->json()['values'];
    }

    /**
     * Get all the issues in our projects, that are completed
     *
     * @return string
     */
    public function getJql(): string
    {
        $jql = 'project IN (' . config('cycletime.jira-categories') . ')';
        $jql .= ' AND statuscategory = "Complete"';
        $updatedHours = $this->getLastUpdatedDate();
        if ($updatedHours) {
            $jql .= " AND updated >= $updatedHours";
        }

        $jql .= ' ORDER BY updated ASC';

        return $jql;
    }

    public function isStartTransition(array $transition): bool
    {
        return ($this->isStatusTransition($transition['field'])
            && Status::isToDoCategory($transition['fromString'])
            && Status::isInProgressCategory($transition['toString']));
    }

    public function isDoneTransition(array $transition): bool
    {
        return ($this->isStatusTransition($transition['field'])
            && Status::isInProgressCategory($transition['fromString'])
            && Status::isDoneCategory($transition['toString']));
    }

    private function isStatusTransition(string $transitionStatus): bool
    {
        return $transitionStatus === 'status';
    }

    /**
     * @return string|null The last updated time we have in the DB, in hours
     */
    private function getLastUpdatedDate(): ?string
    {
        $hours = $this->issueModel->getLastUpdatedDate();
        if (is_numeric($hours)) {
            $hours++;
            return "-${hours}h";
        }
        return $hours;
    }
}
