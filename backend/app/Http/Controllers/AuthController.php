<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Controleur servant à l'authentification
 */
class AuthController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'status' => true,
            'message' => 'Registered successfully',
            'user' => $user,
        ], 201);
    }

    /**
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|string',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Les identifiants fournis sont invalides',
            ], 401);
        }

        $request->session()->regenerate();

        return response()->json(['message' => 'Connexion réussie']);
    }

    /**
     * @return JsonResponse
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Déconnexion réussie',
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'password' => 'sometimes|string|min:8|confirmed',
            'current_password' => 'required_with:password|string',
        ]);

        if (empty($validated)) {
            return response()->json(['message' => 'Aucune donnée à mettre à jour'], 422);
        }

        if (array_key_exists('name', $validated)) {
            $user->name = $validated['name'];
            $user->save();
        }

        if (array_key_exists('password', $validated)) {
            if (! Hash::check($validated['current_password'], $user->password)) {
                return response()->json(['message' => 'Le mot de passe actuel est incorrect'], 422);
            }

            Auth::guard('web')->logoutOtherDevices($validated['current_password']);

            $user->password = Hash::make($validated['password']);
            $user->save();

            $request->session()->regenerate();
        }

        return response()->json([
            'message' => 'Profil mis à jour avec succès',
            'user' => $user,
        ]);
    }
}
