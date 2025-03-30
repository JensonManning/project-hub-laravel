<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResourceTypeRepo extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];
    
    /**
     * Get the resources that belong to this resource type.
     */
    public function resources(): HasMany
    {
        return $this->hasMany(ResourceRepo::class, 'resource_type_id');
    }
}
