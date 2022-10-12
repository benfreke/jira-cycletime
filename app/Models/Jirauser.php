<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jirauser extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function issues()
    {
        return $this->hasMany(Issue::class);
    }
}
