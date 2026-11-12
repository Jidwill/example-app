<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Registered;

/**
 * Gives every newly registered user a profile with sensible starting targets,
 * which they can change on the Profile page.
 *
 * Laravel finds this listener automatically because handle() type-hints the
 * Registered event (Breeze fires it when someone signs up). I used the event
 * instead of a model event so the seeders, which make their own profiles, aren't affected.
 */
class CreateProfileForNewUser
{
    public function handle(Registered $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        // firstOrCreate so it can never make a second profile (user_id is unique)
        $event->user->profile()->firstOrCreate([], [
            'goal' => 'maintain',
            'daily_calories' => 2500,
            'daily_protein' => 150,
        ]);
    }
}
