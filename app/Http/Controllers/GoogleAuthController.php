<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class GoogleAuthController extends Controller
{
    /**
     * Redirect ke Google
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Callback dari Google
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Cari user
            $user = User::where('email', $googleUser->getEmail())->first();

            // Siapkan avatar dengan fallback
            $avatar = $googleUser->getAvatar();
            if (empty($avatar)) {
                // Gunakan UI Avatars jika Google tidak memberikan avatar
                $encodedName = urlencode($googleUser->getName());
                $avatar = "https://ui-avatars.com/api/?name={$encodedName}&background=0ea5e9&color=fff&size=200";
            }

            if ($user) {
                if (!$user->is_active) {
                    return redirect()->away(
                        config('app.frontend_url') . "/login?error=Akun dinonaktifkan"
                    );
                }

                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser->getId(),
                        'photo'     => $avatar,
                    ]);
                }
            } else {
                $user = User::create([
                    'name'      => $googleUser->getName(),
                    'email'     => $googleUser->getEmail(),
                    'password'  => Hash::make(uniqid()),
                    'google_id' => $googleUser->getId(),
                    'photo'     => $avatar, // Simpan avatar
                    'is_active' => true,
                    'role'      => 'user', // default
                ]);
            }

            // 🔐 Token Sanctum
            $token = $user->createToken('google-login')->plainTextToken;

            // 🌐 Redirect ke frontend dengan photo
            $query = http_build_query([
                'token'   => $token,
                'role'    => $user->role,
                'name'    => $user->name,
                'user_id' => $user->id,
                'photo'   => $user->photo, // ← INI YANG PENTING DITAMBAH
            ]);

            return redirect()->away(
                config('app.frontend_url') . "/auth/callback?" . $query
            );

        } catch (\Exception $e) {
            \Log::error("Google Auth Error: " . $e->getMessage());

            return redirect()->away(
                config('app.frontend_url') . "/login?error=Login Google gagal"
            );
        }
    }
}