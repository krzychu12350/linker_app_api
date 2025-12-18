<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventPoll;
use App\Models\User;
use App\Models\Conversation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use App\Enums\PollResponse;
use Illuminate\Support\Str;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Find or create conversation with ID 5
        $conversation = Conversation::firstOrCreate(['id' => 5], [
            'type' => 'group', // Adjust type if needed
            'name' => 'Chat Group - ' . Str::random(8), // Generate unique name
            'match_id' => null, // Set to null for group conversations
        ]);

        // Create a user and attach to the conversation
        $user = User::factory()->create();
        $conversation->users()->attach($user->id);

        return [
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'time' => Carbon::now()->addDays(rand(1, 30)),
        ];
    }
}
