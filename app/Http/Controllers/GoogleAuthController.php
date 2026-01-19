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

            // Cari user berdasarkan email
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Cek apakah akun aktif
                if (!$user->is_active) {
                    $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
                    return redirect()->away("$frontendUrl/login?error=Akun dinonaktifkan");
                }
                
                // Update data Google jika belum ada
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser->getId(),
                        'photo' => $googleUser->getAvatar(),
                    ]);
                }
            } else {
                // Buat user baru
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => Hash::make(uniqid()),
                    'google_id' => $googleUser->getId(),
                    'photo' => $googleUser->getAvatar(),
                    'is_active' => true, // Default aktif
                ]);
            }

            // Buat token Sanctum
            $token = $user->createToken('google-login')->plainTextToken;

            // Dapatkan URL frontend
            $frontendUrl = config('app.frontend_url', 'http://localhost:3000');

            // 🔁 Redirect ke React dengan query token dan data user
            return redirect()->away("$frontendUrl/auth/callback?token={$token}&role={$user->role}&name={$user->name}&user_id={$user->id}");

        } catch (\Exception $e) {
            \Log::error('Google Auth Error: ' . $e->getMessage());
            
            $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
            return redirect()->away("$frontendUrl/login?error=Login Google gagal");
        }
    }
}