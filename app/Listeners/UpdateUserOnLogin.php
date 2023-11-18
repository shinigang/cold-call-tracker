<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\UserMetadataUpdate;

class UpdateUserOnLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $user = User::find($event->user->id);
        if ($user) {
            $metadata = $user->metadata;
            $metadata['logged_in'] = true;
            $metadata['last_login_at'] = now();
            $user->metadata = $metadata;
            $user->save();

            UserMetadataUpdate::dispatch();
        }
    }
}
