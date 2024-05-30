<?php

namespace AscentCreative\ModelRoles\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use AscentCreative\ModelRoles\Models\ModelUserRole;


class ModelUserRoleEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $modeluserrole;
    public $action;

    const MODELUSERROLE_GRANTED = 'granted';
    const MODELUSERROLE_REVOKED = 'revoked';

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ModelUserRole $mur, $action)
    {   
        $this->modeluserrole = $mur;
        $this->action = $action;
    }

}
