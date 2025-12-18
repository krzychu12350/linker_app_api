<?php

namespace App\Models;

use App\Enums\ConversationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', // 'user' or 'group'
        'name',
        'description',
        'match_id',
    ];

    protected $casts = [
        'type' => ConversationType::class,
    ];

    // Conversation.php

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'conversation_user')
            ->withPivot('is_admin'); // Ensure pivot data is loaded
    }

    /**
     * Get all users that belong to the conversation.
     */
//    public function users()
//    {
//        return $this->belongsToMany(User::class, 'conversation_user')
//            ->withTimestamps();
//    }

//    /**
//     * Get all messages in the conversation.
//     */
//    public function messages()
//    {
//        return $this->hasMany(Message::class);
//    }

    /**
     * Get the swipe match if it's a one-on-one conversation.
     */
    public function swipeMatch()
    {
        return $this->belongsTo(SwipeMatch::class, 'match_id');
    }

    /**
     * Get the most recent message in the conversation.
     */
    public function lastMessage()
    {
        return $this->messages()->orderBy('created_at')->first();
    }

    /**
     * Relationship: Get all events related to this conversation.
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }

}
