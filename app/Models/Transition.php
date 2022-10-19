<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
     * @return HasOne
     */
    public function issue(): HasOne
    {
        return $this->hasOne(Issue::class);
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
