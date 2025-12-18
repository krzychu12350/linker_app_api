<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailUser extends Model
{
    use HasFactory;

    protected $table = 'detail_user'; // Specify the pivot table name

    // Define any additional properties for mass assignment if needed
    protected $fillable = [
        'user_id',
        'detail_id',
    ];
}
