<?php

namespace App\Events;

use App\Models\Email;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmailCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Email $email;

    /**
     * Create a new event instance.
     */
    public function __construct(Email $email)
    {
        $this->email = $email;
    }
}
