<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Logout;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\UserMetadataUpdate;

class UpdateUserOnLogout
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
    public function handle(Logout $event): void
    {
        $user = User::find($event->user->id);
        if ($user) {
            $metadata = $user->metadata;
            $metadata['logged_in'] = false;
            $user->metadata = $metadata;
            $user->save();

            broadcast(new UserMetadataUpdate())->toOthers();
        }
    }
}
