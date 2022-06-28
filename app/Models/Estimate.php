<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperEstimate
 */
class Estimate extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function issue()
    {
        return $this->belongsTo(Issue::class);
    }
}
