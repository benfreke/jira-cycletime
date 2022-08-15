<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    /**
     * Always load transition at the same time
     *
     * @var string[]
     */
    protected $with = ['transition', 'estimate'];

    /**
     * @return Transition|HasOne|null
     */
    public function transition(): Transition|HasOne|null
    {
        return $this->hasOne(Transition::class, 'issue_id', 'issue_id');
    }

    public function estimate(): Estimate|HasOne|null
    {
        return $this->hasOne(Estimate::class);
    }

    /**
     * This should return all issues,
     *  that have had an updated start or done transition
     *  since the last time cycletime was calculated
     * @param  Builder  $query
     *
     * @return Builder
     */
    public function scopeNeedsNewCycletime(Builder $query): Builder
    {
        return $query
            ->select('issues.id')
            ->join('transitions', 'issues.issue_id', '=', 'transitions.issue_id')
            ->whereColumn('issues.updated_at', '<=', 'transitions.updated_at')
            ->whereNotNull('transitions.start')
            ->whereNotNull('transitions.done');
    }

    public function scopeOnlyValidAssignees(Builder $query): Builder
    {
        return $query->whereNotIn('assignee', ['Ben Freke', 'Mersija Mujic', 'Connie Huang', 'Simon Small']
        )->whereNotNull('assignee');
    }

    public function scopeOnlyValidTypes(Builder $query): Builder
    {
        return $query->whereNotIn('issue_type', ['Epic']);
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

    /**
     * @return int|null
     */
    public function getLastUpdatedDate(): ?int
    {
        $lastUpdatedIssue = Issue::latest('last_jira_update')->first();
        if (!isset($lastUpdatedIssue->last_jira_update)) {
            return null;
        }
        $hours = $lastUpdatedIssue->last_jira_update->diffInHours();
        if (!is_numeric($hours)) {
            return null;
        }
        return $hours;
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
}
