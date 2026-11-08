<?php

namespace App\Policies;

use App\Models\Scenario;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ScenarioPolicy
{
    public function view(User $user, Scenario $scenario): Response
    {
        return $scenario->analysis->device->user_id === $user->id ? Response::allow() : Response::denyAsNotFound();
    }
}
