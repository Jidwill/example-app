<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Registered;

/**
 * Gives new users a profile with some default targets
 * that they can change on the Profile page.
 *
 * Laravel picks this up automatically because handle() type-hints the
 * Registered event (Breeze fires it on sign up). I used this event instead
 * of a model event so it doesn't mess with the seeders, which make their own profiles.
 */
class CreateProfileForNewUser
{
    public function handle(Registered $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        // firstOrCreate so it can't make a second profile (user_id is unique anyway)
        $event->user->profile()->firstOrCreate([], [
            'goal' => 'maintain',
            'daily_calories' => 2500,
            'daily_protein' => 150,
        ]);
    }
}
