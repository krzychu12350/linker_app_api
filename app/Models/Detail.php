<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detail extends Model
{
    use HasFactory;

    // Define the fillable attributes for mass assignment
    protected $fillable = [
        'name',       // The name of the group or sub-group
        'group',      // The type/category of the group (e.g., 'pets', 'interests', etc.)
        'sub_group',  // The sub-group name (optional, can be null)
        'parent_id',  // The parent group's ID (self-relation)
    ];

    // Define the relationship to User through the pivot table 'detail_user'
    public function users()
    {
        return $this->belongsToMany(User::class, 'detail_user', 'detail_id', 'user_id');
    }

    /**
     * Get the parent group of the current detail.
     * If this is a sub-group, this will return the parent group.
     */
    public function parent()
    {
        return $this->belongsTo(Detail::class, 'parent_id');
    }

    /**
     * Get the sub-groups of the current group.
     * If this is a parent group, this will return all its sub-groups.
     */
    public function subGroups()
    {
        return $this->hasMany(Detail::class, 'parent_id');
    }

    /**
     * Get the full group name for display purposes.
     * Can be used to combine `group` and `sub_group` for better clarity.
     */
    public function getFullGroupNameAttribute()
    {
        return $this->sub_group ? $this->sub_group : $this->group;
    }

    // Define the relationship for subgroups (children)
    public function children()
    {
        return $this->hasMany(Detail::class, 'parent_id');
    }


}
