<?php

namespace App\Models;

use App\Enums\PollResponse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'time',
        'user_id',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function polls()
    {
        return $this->hasMany(EventPoll::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the grouped votes for this event.
     *
     * @return \Illuminate\Support\Collection
     */
    public function votes()
    {
        // Load the polls with the associated user data
        $polls = $this->polls()->with('user')->get();

        // Group votes by the 'response' value and process each group
        return $polls->groupBy('response')->map(function ($votesForResponse, $response) {
            return [
                'response'      => $response,
                'response_name' => PollResponse::tryFrom($response)->name, // Adjust based on the response type
                'count'         => $votesForResponse->count(),
                'users'         => $votesForResponse->map(function ($vote) {
                    return [
                        'id'         => $vote->user->id,
                        'first_name' => $vote->user->first_name,
                        'last_name'  => $vote->user->last_name,
                    ];
                })->values(),
            ];
        })->values();
    }
}