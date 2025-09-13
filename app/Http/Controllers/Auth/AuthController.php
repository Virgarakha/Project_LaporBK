<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\Otp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'class' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => $validator->errors()
            ], 422);
        }

        // Simpan user
        $user = User::create([
            'full_name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'siswa',
            'class' => $request->class
        ]);

        // Generate OTP 6 digit
        $otp = rand(100000, 999999);

        // Simpan OTP
        Otp::create([
            'user_id' => $user->id,
            'code' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(10)
        ]);

        // Kirim OTP ke email user
        Mail::to($user->email)->send(new OtpMail($user, $otp));

        return response()->json([
            'message' => 'Berhasil daftar, OTP sudah dikirim ke email!'
        ], 200);
    }


    public function verifyOtp(Request $request){
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();
        if(!$user){
            return response()->json([
                'message' => 'user tidak terdaftar'
            ], 404);
        }

        if($user->is_verified){
            return response()->json([
                'message' => 'Akun sudah di verifikasi'
            ], 422);
        }

        $otpUser = Otp::where('user_id', $user->id)->first();
        if($request->otp != $otpUser->code){
            return response()->json([
                'message' => 'Otp tidak cocok atau sudah kadaluarsa'
            ], 422);
        }

        $user->update([
            'is_verified' => true,
        ]);

        $otpUser->update([
            'code' => null,
            'otp_expires_at' => null,
        ]);

        return response()->json([
            'message' => 'Akun berhasil diverifikasi!'
        ], 200);
    }

    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => $validator->errors()
            ], 422);
        }

        if(!Auth::guard('web')->attempt($request->only('email', 'password'))){
            return response()->json([
                'message' => 'Email atau Password tidak cocok / salah'
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if($user->is_verified != true){
            return response()->json([
                'message' => 'Akun belum di verifikasi silahkan verifikasi lagi!d'
            ], 422);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ], 200);
    }

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Berhasil logout'
        ], 200);
    }

public function resendOtp(Request $request){
    $request->validate([
        'email' => 'required|email'
    ]);

    $user = User::where('email', $request->email)->first();

    if(!$user){
        return response()->json([
            'message' => 'User tidak terdaftar'
        ], 404);
    }

    if($user->is_verified){
        return response()->json([
            'message' => 'Akun sudah di verifikasi'
        ], 422);
    }

    $otp = rand(100000, 999999);

    $otpUser = Otp::where('user_id', $user->id)->first();

    if($otpUser){
        // Update OTP jika record sudah ada
        $otpUser->update([
            'code' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(10)
        ]);
    } else {
        // Buat baru jika record belum ada
        Otp::create([
            'user_id' => $user->id,
            'code' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(10)
        ]);
    }

    // Kirim OTP ke email user
    Mail::to($user->email)->send(new OtpMail($user, $otp));

    return response()->json([
        'message' => 'OTP berhasil dikirim ulang ke email!'
    ]);
}

}
