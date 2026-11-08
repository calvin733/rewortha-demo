<?php

namespace App\Policies;

use App\Models\Device;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DevicePolicy
{
    public function view(User $user, Device $device): Response
    {
        return $device->user_id === $user->id ? Response::allow() : Response::denyAsNotFound();
    }

    public function update(User $user, Device $device): Response
    {
        return $this->view($user, $device);
    }

    public function delete(User $user, Device $device): Response
    {
        return $this->view($user, $device);
    }
}
