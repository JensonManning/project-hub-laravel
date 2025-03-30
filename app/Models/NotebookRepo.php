<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotebookRepo extends Model
{
    protected $fillable = [
        'name',
        'description',
        'content',
    ];
}
