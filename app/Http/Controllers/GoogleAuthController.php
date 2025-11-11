<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class GoogleAuthController extends Controller
{
    /**
     * Redirect user ke halaman login Google
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Handle callback setelah login Google
     */
    public function handleGoogleCallback()
    {
        try {
            // Ambil data user dari Google
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Cari user berdasarkan email, atau buat baru
            $user = User::updateOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'photo' => $googleUser->getAvatar(),
                    'password' => Hash::make(uniqid()), // password random
                ]
            );

            // Buat token Sanctum
            $token = $user->createToken('google-login')->plainTextToken;

            // Dapatkan URL frontend dari .env (default: http://localhost:5173)
            $frontendUrl = config('app.frontend_url', 'http://localhost:3000');

            // 🔁 Redirect ke React dengan query token dan role
            return redirect()->away("$frontendUrl/auth/callback?token={$token}&role={$user->role}&name={$user->name}");
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Login Google gagal!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
