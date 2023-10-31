<?php

namespace App\Observers;

use App\Models\InviteUser;
use Illuminate\Support\Facades\Log;

class InviteUserObserver
{
    /**
     * Handle the Invitation "created" event.
     *
     * @param \App\Models\InviteUser $inviteUser
     * @return void
     */
    public function created(InviteUser $inviteUser)
    {
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/created.log'),
        ])->info(':email has been invited created', ['email' => $inviteUser->email]);
    }
}
