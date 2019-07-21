<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\User;

/*
    TODO: Better error handling (e.g. what if createToken fails etc.).
*/

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'unique:users'],
            'password' => ['required', 'string', 'confirmed']
        ]);

        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $user->save();

        return response()->json([
            'type' => 'register_success',
            'message' => 'User successfully registered.'
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember_me' => 'boolean'
        ]);

        $credentials = $request->only(['email', 'password']);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'type' => 'login_failure',
                'message' => 'Invalid credentials provided.'
            ], 401);
        }

        $user = $request->user();
        $token = $user->createToken('authenticus');

        if ($request->remember_me)
            $token->token->expires_at = Carbon::now()->addWeeks(1);

        $token->token->save();

        return response()->json([
            'access_token' => $token->token->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $token->token->expires_at
            )->toDateTimeString()
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'type' => 'logout_success',
            'message' => 'User logged out.'
        ]);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
