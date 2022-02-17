<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Config;
use Webpatser\Uuid\Uuid;
use Jdenticon\Identicon;


class UserObserver
{

    /**
     * @param \App\Models\User $user
     * @return void
     * @throws \Exception
     */
    public function creating(User $user): void
    {
        $user->uuid = (string) Uuid::generate(4);

        if (!$user->avatar) {
            if ($placeholder = Config::get('avatar_placeholder')) {
                $user->avatar = new \SplFileInfo($placeholder);
            } else {
                $options = [
                    'value' => $user->email ?? $user->uuid,
                    'size' => 400,
                ];

                $user->avatar = (new Identicon($options))
                    ->getImageDataUri('png');
            }
        }
    }

    /**
     * Handle the User "created" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function created(User $user)
    {
        //

    }

    /**
     * Handle the User "updated" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function updated(User $user)
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function deleted(User $user)
    {
        //
    }

    /**
     * Handle the User "restored" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function restored(User $user)
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        //
    }
}
