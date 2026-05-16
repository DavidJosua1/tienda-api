<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'apellidos' => 'required|string',
            'email' => 'required|email|unique:personas',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth()->attempt($credentials)) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        return response()->json([
            'message' => 'Login exitoso',
            'token' => $token,
            'user' => auth()->user(),
        ]);
    }

    public function logout()
    {
        auth()->logout();
        return response()->json([
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }

    public function me()
    {
        return response()->json(auth()->user());
    }
    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
        ]);

        auth()->user()->update([
            'latitud' => $request->latitud,
            'longitud' => $request->longitud,
        ]);

        return response()->json([
            'message' => 'Ubicación actualizada exitosamente'
        ]);
    }
    public function googleLogin(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        try {
            $client = new \Google\Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
            $payload = $client->verifyIdToken($request->token);

            if (!$payload) {
                return response()->json(['message' => 'Token inválido'], 401);
            }

            $googleId = $payload['sub'];
            $email = $payload['email'];
            $nombre = $payload['given_name'] ?? 'Usuario';
            $apellidos = $payload['family_name'] ?? 'Google';
            $fotoUrl = $payload['picture'] ?? null;

            $user = User::where('google_id', $googleId)
                ->orWhere('email', $email)
                ->first();

            if (!$user) {
                $user = User::create([
                    'nombre' => $nombre,
                    'apellidos' => $apellidos,
                    'email' => $email,
                    'password' => Hash::make(Str::random(16)),
                    'google_id' => $googleId,
                    'foto_url' => $fotoUrl,
                ]);
            } else {
                $user->update([
                    'google_id' => $googleId,
                    'foto_url' => $fotoUrl,
                ]);
            }

            $token = JWTAuth::fromUser($user);

            return response()->json([
                'message' => 'Login con Google exitoso',
                'token' => $token,
                'user' => $user,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al verificar token: ' . $e->getMessage()
            ], 500);
        }
    }
}
