<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserModel; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends Controller
{
    /**
     * Kirim OTP ke email
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = UserModel::where('email', $request->email)->first();

        // Anti email enumeration
        if (!$user) {
            return response()->json([
                'status' => true,
                'message' => 'Jika email terdaftar, OTP akan dikirim'
            ]);
        }

        // Generate OTP 6 digit
        $otp = rand(100000, 999999);

        $user->reset_otp = Hash::make($otp);
        $user->reset_otp_expired_at = now()->addMinutes(1);
        $user->save();

        // Log OTP untuk debugging
        Log::info("OTP DEBUG: {$otp}");

        // Kirim email OTP
        try {
            Mail::raw("Kode OTP: {$otp}\nBerlaku 1 menit.", function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('OTP Reset Password');
            });
            Log::info("EMAIL OTP TERKIRIM KE {$user->email}");
        } catch (\Exception $e) {
            Log::error("GAGAL MENGIRIM EMAIL KE {$user->email}: " . $e->getMessage());
        }

        return response()->json([
            'status' => true,
            'message' => 'Jika email terdaftar, OTP akan dikirim'
        ]);
    }

    /**
     * Reset password pakai OTP
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required',
            'password' => 'required|min:6|confirmed', // frontend harus kirim password_confirmation
        ]);

        $user = UserModel::where('email', $request->email)->first();

        if (
            !$user ||
            !$user->reset_otp ||
            $user->reset_otp_expired_at < now()
        ) {
            return response()->json([
                'status' => false,
                'message' => 'OTP tidak valid atau sudah kadaluarsa'
            ], 400);
        }

        if (!Hash::check($request->otp, $user->reset_otp)) {
            return response()->json([
                'status' => false,
                'message' => 'OTP salah'
            ], 400);
        }

        $user->password = Hash::make($request->password);
        $user->reset_otp = null;
        $user->reset_otp_expired_at = null;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Password berhasil direset'
        ]);
    }
}
