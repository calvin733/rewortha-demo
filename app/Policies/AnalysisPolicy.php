<?php

namespace App\Policies;

use App\Models\Analysis;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AnalysisPolicy
{
    public function view(User $user, Analysis $analysis): Response
    {
        return $analysis->device->user_id === $user->id ? Response::allow() : Response::denyAsNotFound();
    }
}
