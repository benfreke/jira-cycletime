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
        return $query->whereBetween(
            'done',
            [
                Carbon::now()->subMonth()->firstOfMonth()->startOfDay(),
                Carbon::now()->subMonth()->endOfMonth()->endOfDay(),
            ]
        );
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
}
