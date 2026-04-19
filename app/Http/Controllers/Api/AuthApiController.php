<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthApiController extends Controller
{
    use ApiResponses;

    public function login(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
                'device_name' => ['nullable', 'string', 'max:255'],
            ]);

            /** @var User|null $user */
            $user = User::where('email', $validated['email'])->first();

            if (! $user || ! Hash::check($validated['password'], $user->password)) {
                return $this->fail('Невалидни имейл или парола.', 422);
            }

            $device = $validated['device_name'] ?? 'api-client';
            $token = $user->createToken($device)->plainTextToken;

            return $this->ok([
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => $this->userPayload($user),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Невалидни входни данни.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            report($e);

            return $this->fail('Грешка при вход. Опитайте отново.', 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()?->currentAccessToken()?->delete();

            return $this->ok(['message' => 'Успешен изход от API.']);
        } catch (\Throwable $e) {
            report($e);

            return $this->fail('Грешка при изход.', 500);
        }
    }

    public function user(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return $this->ok([
                'user' => $this->userPayload($user),
            ]);
        } catch (\Throwable $e) {
            report($e);

            return $this->fail('Неуспешно зареждане на профила.', 500);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'first_name' => $user->firstName(),
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'avatar_url' => $user->avatarPublicUrl(),
        ];
    }
}
