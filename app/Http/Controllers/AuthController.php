<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

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
}
