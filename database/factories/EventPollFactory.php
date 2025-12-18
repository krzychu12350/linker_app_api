<?php

namespace Database\Factories;

use App\Enums\PollResponse;
use App\Models\Event;
use App\Models\EventPoll;
use App\Models\User;
use App\Models\Conversation;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventPollFactory extends Factory
{
    protected $model = EventPoll::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Ensure conversation with ID 5 exists
        $conversation = Conversation::firstOrCreate(
            ['id' => 5],
            ['name' => 'Chat Group - ' . \Illuminate\Support\Str::random(8), 'type' => 'group']
        );

        // Add 20 users to the conversation (if not already present)
        while ($conversation->users()->count() < 20) {
            $user = User::factory()->create();
            $conversation->users()->attach($user->id); // Assign user to conversation
        }

        // Now, pick a random user from the conversation
        $user = $conversation->users()->inRandomOrder()->first();

        // Create an event in the same conversation
        $event = Event::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
        ]);

        return [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'response' => $this->faker->randomElement(PollResponse::values()),
        ];
    }
}
