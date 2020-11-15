<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class RecoveryLogin extends Event
{
    use SerializesModels;

    /**
     * The authenticated user.
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return void
     *
     * @codeCoverageIgnore
     */
    public function __construct($user)
    {
        $this->user = $user;
    }
}
