<?php

namespace App\Models;

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

    protected $fillable = [
        'summary',
        'issue_id',
        'last_jira_update'
    ];
}
