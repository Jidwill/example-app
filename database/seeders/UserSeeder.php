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
        // Fixed accounts with known details, one for each role, for testing and the demo.
        // All seeded users have the password "password".
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

        // Random members and a couple of random coaches, each with their own profile.
        User::factory()->count(20)->has(Profile::factory(), 'profile')->create();
        User::factory()->count(2)->coach()->has(Profile::factory(), 'profile')->create();
    }
}
