<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\PollResponse;

class EventPoll extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'response'
    ];

    protected $casts = [
        'response' => PollResponse::class,
    ];

    /**
     * Ensure the 'role' attribute is always included in the model's array and JSON representation.
     *
     * @var array
     */
    protected $appends = ['response_name'];

    public function getResponseNameAttribute()
    {
        return $this->response->name;
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}