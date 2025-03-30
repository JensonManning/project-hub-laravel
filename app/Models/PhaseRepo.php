<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhaseRepo extends Model
{

    protected $fillable = [
        'name',
        'description',
        'order',
    ];
}
