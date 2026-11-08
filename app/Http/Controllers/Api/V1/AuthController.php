<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Timebox;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request): JsonResponse {
                $user = User::query()->create($request->safe()->only(['name', 'email', 'password']));

                return $this->tokenResponse($user, 201);
            });
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['email' => 'The email has already been taken.']);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = (new Timebox)->call(function () use ($request): ?User {
            $user = User::query()->where('email', $request->validated('email'))->first();

            return $user && strlen($request->validated('password')) <= 72 && Hash::check($request->validated('password'), $user->password) ? $user : null;
        }, 200_000);
        if (! $user) {
            throw ValidationException::withMessages(['email' => 'The provided credentials are incorrect.']);
        }

        return $this->tokenResponse($user);
    }

    public function logout(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()->only(['id', 'name', 'email', 'created_at'])]);
    }

    private function tokenResponse(User $user, int $status = 200): JsonResponse
    {
        $expiresAt = now()->addDays(7);
        $token = $user->createToken('api', ['*'], $expiresAt);

        return response()->json(['data' => [
            'user' => $user->only(['id', 'name', 'email', 'created_at']), 'token' => $token->plainTextToken,
            'token_type' => 'Bearer', 'expires_at' => $expiresAt->toISOString(),
        ]], $status);
    }
}
