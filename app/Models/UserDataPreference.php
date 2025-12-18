<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDataPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'age_range_start',
        'age_range_end',
//        'height',
    ];

    // Relationship to User model
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
