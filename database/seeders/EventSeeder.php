<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventPoll;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EventPoll::factory(20)->create();
//        // Create some users in conversation 5
//        $users = User::factory(10)->create(['conversation_id' => 5]);
//
//        // Create events and associate them with users
//        $events = Event::factory(3)->create([
//            'user_id' => $users->random()->id,
//        ]);
//
//        // Create event polls for users in the same conversation
//        foreach ($events as $event) {
//            EventPoll::factory(3)->create([
//                'event_id' => $event->id,
//                'user_id' => $users->random()->id,
//            ]);
//        }
    }
}
