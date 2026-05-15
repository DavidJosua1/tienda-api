<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:personas,email',
        ]);

        $token = Str::random(6);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token'      => $token,
                'created_at' => now(),
            ]
        );

        Mail::send([], [], function ($message) use ($request, $token) {
            $message->to($request->email)
                    ->subject('Recuperación de contraseña')
                    ->html("
                        <h2>Recuperación de contraseña</h2>
                        <p>Tu código de recuperación es:</p>
                        <h1 style='color: #e74c3c; font-size: 36px;'>{$token}</h1>
                        <p>Este código expira en 15 minutos.</p>
                        <p>Si no solicitaste esto, ignora este email.</p>
                    ");
        });

        return response()->json([
            'message' => 'Código de recuperación enviado a tu correo'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|exists:personas,email',
            'token'    => 'required|string',
            'password' => 'required|min:6|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$record) {
            return response()->json([
                'message' => 'Código inválido o expirado'
            ], 400);
        }

        if (now()->diffInMinutes($record->created_at) > 15) {
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();
            return response()->json([
                'message' => 'Código expirado, solicita uno nuevo'
            ], 400);
        }

        User::where('email', $request->email)
            ->update(['password' => Hash::make($request->password)]);

        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        return response()->json([
            'message' => 'Contraseña actualizada exitosamente'
        ]);
    }
}
