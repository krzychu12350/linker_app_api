<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\EventPoll;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Event;
use App\Enums\PollResponse;
use Faker\Factory as Faker;

class EventPollSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Initialize Faker
        $faker = Faker::create();

        // Ensure conversation with ID 5 exists and has at least 20 users
        $conversation = Conversation::firstOrCreate(
            ['id' => 5],
            ['name' => 'Chat Group - ' . \Illuminate\Support\Str::random(8), 'type' => 'group']
        );

        // Add 20 users to the conversation (if not already present)
        while ($conversation->users()->count() < 20) {
            $user = User::factory()->create();
            $conversation->users()->attach($user->id); // Assign user to conversation
        }

        // Pick a random event (you can create one if needed)
        $event = Event::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $conversation->users()->inRandomOrder()->first()->id, // Assign a random user as the event creator
        ]);

        // Iterate over each user in the conversation and create a single poll response
        $conversation->users->each(function ($user) use ($event, $faker) {
            // Create the event poll for each user
            EventPoll::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'response' => $faker->randomElement(PollResponse::values()), // Assign a random poll response
            ]);
        });
    }
}
