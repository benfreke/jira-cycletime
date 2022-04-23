<?php

namespace App\Services;

use App\Models\Issue;
use Illuminate\Support\Facades\Http;

class Jira
{
    public function __construct(private readonly int $resultsToGet = 60)
    {

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
     * Get all the issues in our projects, that are completed
     *
     * @return string
     */
    public function getJql(): string
    {
        $jql = 'project IN ' . config('cycletime.jira-categories');
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
        $hours = Issue::getLastUpdatedDate();
        if (is_numeric($hours)) {
            $hours++;
            return "-${hours}h";
        }
        return $hours;
    }
}
