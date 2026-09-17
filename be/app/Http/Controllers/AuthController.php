<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
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
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password_hash)) {
            return response()->json(['message' => 'The provided credentials are incorrect.'], 422);
        }

        return $this->tokenResponse($user);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json(['message' => 'No account was found for that email.'], 404);
        }

        $code = (string) random_int(100000, 999999);

        DB::table('password_resets')
            ->where('user_id', $user->user_id)->where('used', false)->update(['used' => true]);

        DB::table('password_resets')->insert([
            'user_id' => $user->user_id,
            'code' => $code,
            'expires_at' => now()->addMinutes(15),
            'used' => false,
        ]);

        Mail::send('emails.password-reset', ['code' => $code], function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Panodoro password reset code');
        });

        return response()->json(['message' => 'A password reset code was sent to your email.']);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $reset = DB::table('password_resets')
            ->where('code', $validated['code'])
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->whereIn('user_id', function ($query) use ($validated) {
                $query->select('user_id')
                    ->from('users')
                    ->where('email', $validated['email']);
            })
            ->latest('created_at')
            ->first();

        if (!$reset) {
            return response()->json(['message' => 'The reset code is invalid or expired.'], 422);
        }

        DB::table('users')
            ->where('user_id', $reset->user_id)
            ->update(['password_hash' => Hash::make($validated['password'])]);

        DB::table('password_resets')
            ->where('reset_id', $reset->reset_id)
            ->update(['used' => true]);

        User::find($reset->user_id)?->tokens()->delete();

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
}
