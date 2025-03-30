<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryRepo extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];
}
