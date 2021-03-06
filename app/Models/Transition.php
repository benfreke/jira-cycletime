<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperTransition
 */
class Transition extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'start' => 'immutable_datetime',
        'done' => 'immutable_datetime',
    ];

    public function issue()
    {
        return $this->belongsTo(Issue::class, 'issue_id', 'issue_id');
    }

    public function isOlderStart(CarbonImmutable $dateToCompare): bool
    {
        if (empty($this->start)) {
            return true;
        }
        return $dateToCompare->lessThan($this->start);
    }

    public function isNewerDone(CarbonImmutable $dateToCompare): bool
    {
        if (empty($this->done)) {
            return true;
        }
        return $dateToCompare->greaterThan($this->done);
    }
}
