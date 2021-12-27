<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Issue
 *
 * @property int $id
 * @property string $summary
 * @property string $issue_id
 * @property string $last_jira_update
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Issue newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Issue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Issue query()
 * @method static \Illuminate\Database\Eloquent\Builder|Issue whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Issue whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Issue whereIssueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Issue whereLastJiraUpdate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Issue whereSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Issue whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Issue extends Model
{
    use HasFactory;

    protected $fillable = [
        'summary',
        'issue_id',
        'last_jira_update'
    ];
}
