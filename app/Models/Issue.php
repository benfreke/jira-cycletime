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
        return $query->whereNotIn('assignee', ['Ben Freke']);
    }

    public function scopeHasCycletime(Builder $query): Builder|Issue
    {
        return $query->whereNotNull(['cycletime']);
    }

    public function scopeLastQuarter(Builder $query): Builder
    {
        return $query->whereDate(
            'last_jira_update',
            '<',
            Carbon::now()->firstOfQuarter()
        )
            ->whereDate(
                'last_jira_update',
                '>',
                Carbon::now()->subMonths(3)
            );
    }

    public function scopeLastMonth(Builder $query): Builder
    {
        return $query->whereBetween(
            'last_jira_update',
            [
                Carbon::now()->subMonth()->firstOfMonth(),
                Carbon::now()->subMonth()->lastOfMonth()
            ]
        );
    }
}
