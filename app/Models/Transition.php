<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class, 'issue_id', 'issue_id');
    }

    public function isOlderStart(Carbon $dateToCompare): bool
    {
        if (empty($this->start)) {
            return true;
        }
        return $dateToCompare->lessThan($this->start);
    }

    public function isNewerDone(Carbon $dateToCompare): bool
    {
        if (empty($this->done)) {
            return true;
        }
        return $dateToCompare->greaterThan($this->done);
    }


}
