<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // one account for each role so I can log in and test them
        // (every seeded user has the password "password")
        User::factory()->admin()->has(Profile::factory(), 'profile')->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        User::factory()->coach()->has(Profile::factory(), 'profile')->create([
            'name' => 'Coach User',
            'email' => 'coach@example.com',
        ]);

        User::factory()->has(Profile::factory(), 'profile')->create([
            'name' => 'Member User',
            'email' => 'member@example.com',
        ]);

        // then a load of random members and a couple of extra coaches, all with profiles
        User::factory()->count(20)->has(Profile::factory(), 'profile')->create();
        User::factory()->count(2)->coach()->has(Profile::factory(), 'profile')->create();
    }
}
