<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private function logLogin(?User $user, Request $request, string $method, bool $success): void
    {
        if (!$user) return;

        LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method' => $method,
            'success' => $success,
            'login_at' => now(),
        ]);
    }
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|safe_email',
            'password' => 'required|string',
        ]);

        $user = User::with('role')->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            $this->logLogin($user, $request, 'email', false);
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        if (!$user->is_active) {
            $this->logLogin($user, $request, 'email', false);
            return response()->json(['message' => 'Account disabled.'], 403);
        }

        $user->update(['last_login_at' => now()]);
        $this->logLogin($user, $request, 'email', true);

        return response()->json([
            'token' => $user->createToken('pos-token')->plainTextToken,
            'user' => $user,
        ]);
    }

    public function pinLogin(Request $request): JsonResponse
    {
        $request->validate([
            'pin' => 'required|string|digits:4',
        ]);

        $user = User::with('role')->where('pin', $request->pin)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid PIN.'], 401);
        }

        if (!$user->is_active) {
            $this->logLogin($user, $request, 'pin', false);
            return response()->json(['message' => 'Account disabled.'], 403);
        }

        $user->update(['last_login_at' => now()]);
        $this->logLogin($user, $request, 'pin', true);

        return response()->json([
            'token' => $user->createToken('pos-token')->plainTextToken,
            'user' => $user,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request): JsonResponse
    {
        $data = $request->user()->load('role.permissions')->toArray();
        $data['locale'] = Setting::getValue('locale', 'en');
        $data['currency'] = Setting::getValue('currency', 'USD');

        return response()->json($data);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|safe_email|max:255|unique:users,email,' . $user->id,
            'phone' => 'sometimes|string|max:20',
            'password' => 'sometimes|string|min:8|confirmed',
            'current_password' => 'required_with:password|string',
        ]);

        if (isset($validated['current_password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json(['message' => 'Current password is incorrect.'], 403);
            }
        }

        $user->update($validated);

        return response()->json($user->load('role'));
    }

    public function getLocale(): JsonResponse
    {
        return response()->json([
            'locale' => Setting::getValue('locale', 'en'),
            'available' => ['en', 'fr', 'es', 'de', 'pt', 'it', 'nl', 'pl', 'ru', 'zh', 'ja', 'ko', 'ar', 'tr'],
        ]);
    }

    public function updateLocale(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'locale' => 'required|string|in:en,fr,es,de,pt,it,nl,pl,ru,zh,ja,ko,ar,tr',
        ]);

        Setting::setValue('locale', $validated['locale'], 'general');

        App::setLocale($validated['locale']);

        return response()->json([
            'locale' => $validated['locale'],
            'message' => __('messages.common.success'),
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|safe_email|exists:users,email']);

        $status = Password::sendResetLink($request->only('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            return response()->json(['message' => __($status)], 400);
        }

        $token = \DB::table('password_reset_tokens')->where('email', $request->email)->value('token');

        return response()->json([
            'message' => 'Reset link sent.',
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|safe_email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json(['message' => __($status)], 400);
        }

        \DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password reset successfully.']);
    }
}
