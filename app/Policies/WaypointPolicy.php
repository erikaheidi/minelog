<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Waypoint;

class WaypointPolicy
{
    public function view(User $user, Waypoint $waypoint): bool
    {
        return $waypoint->world->user_id === $user->id;
    }

    public function update(User $user, Waypoint $waypoint): bool
    {
        return $waypoint->world->user_id === $user->id;
    }

    public function delete(User $user, Waypoint $waypoint): bool
    {
        return $this->update($user, $waypoint);
    }
}
