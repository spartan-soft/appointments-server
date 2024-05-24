<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTController extends Controller
{

    /**
     * Register user.
     *
     */
    public function register(Request $request)
    {

        //data validation
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'phone' => 'required',
            'password' => 'required|confirmed',
        ]);

        //date save
        User::create([
            "name" => $request->name,
            "email" => $request->email,
            "phone" => $request->phone,
            "password" => Hash::make($request->password)
        ]);

        //response
        return response()->json([
            "status" => true,
            "message" => "User created successfully"
        ]);
    }

    /**
     * login user
     *
     */
    public function login(Request $request)
    {
        // Validación de los datos
        $request->validate([
            "login" => "required|string",
            "password" => "required|string",
        ]);

        // Determinar si el login es un email, un username o un phone
        $login = $request->input('login');
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : (is_numeric($login) ? 'phone' : 'user');

        // Intentar autenticar al usuario con JWT
        $credentials = [
            $field => $login,
            'password' => $request->input('password')
        ];

        if ($token = JWTAuth::attempt($credentials)) {
            return response()->json([
                "status" => true,
                "token" => $token,
                "message" => "User logged in successfully"
            ]);
        }

        return response()->json([
            "status" => false,
            "message" => "User not found or credentials do not match",
        ]);
    }

    /**
     * Get user profile.
     *
     */
    public function profile()
    {
        $userData = auth()->user();

        return response()->json([
            "status" => true,
            "message" => "User profile info",
            "data" => $userData
        ]);
    }

    /**
     * Refresh token.
     *
     */
    public function refreshToken()
    {
        $newToken = auth()->refresh();
        return response()->json([
            "status" => true,
            "message" => "Token refreshed",
            "token" => $newToken
        ]);
    }

    /**
     * update user
     */

    public function update(Request $request, $id)
    {
        // Validar los datos recibidos
        $request->validate([
            'user' => 'sometimes|string|max:255',
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $id,
            'phone' => 'sometimes|string|max:15',
            'password' => 'sometimes|string|min:8|confirmed',
        ]);

        // Buscar el usuario por su ID
        $user = User::findOrFail($id);

        // Actualizar los campos del usuario
        if ($request->has('user')) {
            $user->user = $request->input('user');
        }
        if ($request->has('name')) {
            $user->name = $request->input('name');
        }
        if ($request->has('email')) {
            $user->email = $request->input('email');
        }
        if ($request->has('phone')) {
            $user->phone = $request->input('phone');
        }
        if ($request->has('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        $user->save();

        // Responder con el usuario actualizado
        return response()->json([
            'status' => true,
            'message' => 'User updated successfully',
            'user' => $user,
        ]);
    }

    /**
     * Logout user
     *
     */
    public function logout()
    {
        auth()->logout();

        return response()->json([
            "status" => true,
            "message" => "Logged out successfully"
        ]);
    }

    /**
     * Delete user by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteUserById($id)
    {
        $user = User::find($id);

        if ($user) {
            $user->delete();
            return response()->json([
                "status" => true,
                "message" => "User deleted successfully"
            ]);
        }

        return response()->json([
            "status" => false,
            "message" => "User not found"
        ]);
    }

}
