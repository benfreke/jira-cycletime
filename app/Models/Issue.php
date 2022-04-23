<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Issue
 *
 * @mixin IdeHelperIssue
 */
class Issue extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'last_jira_update' => 'immutable_datetime',
    ];

    public function scopeOnlyValidAssignees(Builder $query): Builder
    {
        return $query->whereNotIn('assignee', ['Ben Freke', 'Mersija Mujic', 'Connie Huang', 'Simon Small']);
    }

    public function scopeHasCycletime(Builder $query): Builder|Issue
    {
        return $query->whereNotNull(['cycletime']);
    }

    public function scopeLastQuarter(Builder $query): Builder
    {
        return $query->whereDate(
            'done',
            '>',
            Carbon::now()->subQuarter()->firstOfQuarter()->startOfDay()
        )
            ->whereDate(
                'done',
                '<',
                Carbon::now()->subQuarter()->lastOfQuarter()->endOfDay()
            );
    }

    public function scopeThisQuarter(Builder $query): Builder
    {
        return $query->whereDate(
            'done',
            '>',
            Carbon::now()->firstOfQuarter()->startOfDay()
        )
            ->whereDate(
                'done',
                '<',
                Carbon::now()->lastOfQuarter()->endOfDay()
            );
    }

    public function scopeLastMonth(Builder $query): Builder
    {
        return $this->getPastMonths($query, 1);
    }

    public function scopeLastTwoMonths(Builder $query): Builder
    {
        return $this->getPastMonths($query, 2);
    }

    public function scopeLastThreeMonths(Builder $query): Builder
    {
        return $this->getPastMonths($query, 3);
    }

    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereBetween(
            'done',
            [
                Carbon::now()->firstOfMonth()->startOfDay(),
                Carbon::now()->endOfMonth()->endOfDay(),
            ]
        );
    }

    protected function getPastMonths(Builder $query, int $months): Builder
    {
        return $query->whereBetween(
            'done',
            [
                Carbon::now()->subMonths($months)->firstOfMonth()->startOfDay(),
                Carbon::now()->subMonths($months)->endOfMonth()->endOfDay(),
            ]
        );
    }

    /**
     * @return int|null
     */
    public static function getLastUpdatedDate(): ?int
    {
        $lastUpdatedIssue = Issue::latest('last_jira_update')->first();
        if (!isset($lastUpdatedIssue->last_jira_update)) {
            return null;
        }
        $hours = $lastUpdatedIssue->last_jira_update->diffInHours();
        if (!$hours) {
            return null;
        }
        return $hours;
    }
}
