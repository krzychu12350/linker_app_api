<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventPoll;
use App\Models\File;
use App\Models\Report;
use App\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
//        User::factory()->create([
//            'name' => 'Test User',
//            'email' => 'test@example.com',
//        ]);
        $this->call([
           RolesAndPermissionsSeeder::class,
            UserDetailSeeder::class,
        ]);

//        User::factory(20)->withRole('user')->create();
        User::factory(20)->withRole('moderator')->create()->each(function (User $user) {
            $this->createAndAttachFile($user);
        });

        User::factory(1)->withRole('user')->create([
            'first_name' => 'Matthew',
            'last_name' => 'Cruise',
            'email' => 'matthew.cruise@gmail.com',
            'password' => Hash::make('securepassword123')
        ])->each(function (User $user) {
            $this->createAndAttachFile($user);
        });

        User::factory(1)->withRole('admin')->create([
            'first_name' => 'Admin',
            'last_name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('securepassword123')
        ])->each(function (User $user) {
            $this->createAndAttachFile($user);
        });

        User::factory(50)->withRole('user')->create([
            'password' => Hash::make('securepassword123')
        ])->each(function (User $user) {
            $this->createAndAttachFile($user);
        });


        User::factory(1)->withRole('user')->create([
            'first_name' => 'John',
            'last_name' => 'Trump',
            'email' => 'john.trump@gmail.com',
            'password' => Hash::make('securepassword123')
        ])->each(function (User $user) {
            $this->createAndAttachFile($user);
        });

        User::factory(1)->withRole('moderator')->create([
            'first_name' => 'Peter',
            'last_name' => 'Flash',
            'email' => 'peter.flash@gmail.com',
            'password' => Hash::make('securepassword123')
        ])->each(function (User $user) {
            $this->createAndAttachFile($user);
        });

        Report::factory(20)->create();
        $this->call([
            EventPollSeeder::class,
        ]);
    }

    /**
     * Create a file and attach it to the given user.
     */
    private function createAndAttachFile(User $user)
    {
        // Generate a random image ID (1 to 70, or another range)
        $randomId = rand(1, 70);

        // Create the file with a random image URL
        $file = File::create([
            'url' => 'https://i.pravatar.cc/150?img=' . $randomId,  // Random image URL
            'type' => 'image',
            'extension' => 'jpg',  // Assuming the file is an image with jpg extension
        ]);

        // Attach the file to the user via the pivot table
        $user->files()->attach($file);
    }

}
