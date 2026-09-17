<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        if ($response = $this->throttle($request, 'register')) {
            return $response;
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
        ]);

        return $this->tokenResponse($user, 201);
    }

    public function login(Request $request): JsonResponse
    {
        if ($response = $this->throttle($request, 'login')) {
            return $response;
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password_hash)) {
            return response()->json(['message' => 'The provided credentials are incorrect.'], 422);
        }

        RateLimiter::clear($this->throttleKey($request, 'login'));

        return $this->tokenResponse($user);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        if ($response = $this->throttle($request, 'forgot-password', 3, 900)) {
            return $response;
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user) {
            $code = (string) random_int(100000, 999999);

            DB::table('password_resets')
                ->where('user_id', $user->user_id)->where('used', false)->update(['used' => true]);

            DB::table('password_resets')->insert([
                'user_id' => $user->user_id,
                'code' => $this->hashResetCode($code),
                'expires_at' => now()->addMinutes(15),
                'used' => false,
            ]);

            Mail::send('emails.password-reset', ['code' => $code], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Panodoro password reset code');
            });
        }

        return response()->json(['message' => 'A password reset code was sent to your email.']);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        if ($response = $this->throttle($request, 'reset-password', 5, 900)) {
            return $response;
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::where('email', $validated['email'])->first();
        $reset = $user
            ? DB::table('password_resets')
                ->where('user_id', $user->user_id)
                ->where('used', false)
                ->where('expires_at', '>', now())
                ->latest('created_at')
                ->first()
            : null;

        if (!$reset || !hash_equals($reset->code, $this->hashResetCode($validated['code']))) {
            if ($reset && RateLimiter::attempts($this->throttleKey($request, 'reset-password')) >= 5) {
                DB::table('password_resets')
                    ->where('user_id', $reset->user_id)
                    ->where('used', false)
                    ->update(['used' => true]);
            }

            return response()->json(['message' => 'The reset code is invalid or expired.'], 422);
        }

        DB::table('users')
            ->where('user_id', $reset->user_id)
            ->update(['password_hash' => Hash::make($validated['password'])]);

        DB::table('password_resets')
            ->where('reset_id', $reset->reset_id)
            ->update(['used' => true]);

        User::find($reset->user_id)?->tokens()->delete();
        RateLimiter::clear($this->throttleKey($request, 'reset-password'));

        return response()->json(['message' => 'Password reset successfully.']);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    private function tokenResponse(User $user, int $status = 200): JsonResponse
    {
        return response()->json([
            'token' => $user->createToken('panodoro-web')->plainTextToken,
            'user' => [
                'user_id' => $user->user_id,
                'full_name' => $user->full_name,
                'email' => $user->email,
            ],
        ], $status);
    }

    private function throttle(Request $request, string $action, int $maxAttempts = 10, int $decaySeconds = 60): ?JsonResponse
    {
        $key = $this->throttleKey($request, $action);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'message' => 'Too many attempts. Please try again later.',
                'retry_after' => RateLimiter::availableIn($key),
            ], 429);
        }

        RateLimiter::hit($key, $decaySeconds);

        return null;
    }

    private function throttleKey(Request $request, string $action): string
    {
        return $action.'|'.$request->ip().'|'.strtolower((string) $request->input('email'));
    }

    private function hashResetCode(string $code): string
    {
        return substr(hash_hmac('sha256', $code, (string) config('app.key')), 0, 10);
    }
}
