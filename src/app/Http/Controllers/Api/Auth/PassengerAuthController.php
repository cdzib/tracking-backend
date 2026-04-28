<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Passenger;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PassengerAuthController extends Controller
{
    // Registro de pasajero
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:passengers,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
        ]);

        $passenger = Passenger::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
        ]);

        $token = $passenger->createToken('auth_token')->plainTextToken;

        return response()->json([
            'passenger' => $passenger,
            'token' => $token,
        ], 201);
    }

    // Login de pasajero
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $passenger = Passenger::where('email', $validated['email'])->first();

        if (!$passenger || !Hash::check($validated['password'], $passenger->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        $token = $passenger->createToken('auth_token')->plainTextToken;

        return response()->json([
            'passenger' => $passenger,
            'token' => $token,
        ]);
    }
}
