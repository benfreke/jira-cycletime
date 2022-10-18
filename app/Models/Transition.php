<?php

namespace App\Models;

use Carbon\CarbonImmutable;
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

    /**
     * @return BelongsTo
     */
    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
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
