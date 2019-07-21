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

        try {
            $user = new User([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password)
            ]);

            $user->save();

            Auth::attempt($request->only(['email', 'password']));

            $tokenResponse = $this->createTokenResponse(
                $this->createToken($request, auth()->user())
            );
            $registerResponse = [
                'type' => 'register_success',
                'message' => 'User successfully registered.'
            ];

            return response()->json(
                array_merge($tokenResponse, $registerResponse),
                201
            );
        } catch (Exception $e) {
            return $this->respondWithGenericError($e);
        }
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

        try {
            return $this->respondWithToken($this->createToken($request));
        } catch (Exception $e) {
            return $this->respondWithGenericError($e);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->token()->revoke();

            return response()->json([
                'type' => 'logout_success',
                'message' => 'User logged out.'
            ]);
        } catch (Exception $e) {
            return $this->respondWithGenericError($e);
        }
    }

    public function user(Request $request)
    {
        try {
            /*
                TODO: Should be configurable?

                Some people might want the email to be returned from
                /api/auth/user, for example, whereas others may not.
            */
            return response()->json($request->user());
        } catch (Exception $e) {
            return $this->respondWithGenericError($e);
        }
    }

    private function respondWithGenericError(Exception $exception)
    {
        return response()->json([
            'type' => get_class($exception),
            'message' => '???' . $exception->getMessage()
        ], 401);
    }

    private function createToken($request, $user = null)
    {
        $user = $user ?: $request->user();
        $token = $user->createToken('authenticus'); // TODO: Should be configurable?

        if ($request->remember_me)
            $token->token->expires_at = Carbon::now()->addWeeks(1); // TODO: Should be configurable.

        $token->token->save();

        return $token;
    }

    private function createTokenResponse($token)
    {
        return [
            'access_token' => $token->token->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $token->token->expires_at
            )->toDateTimeString()
        ];
    }

    private function respondWithToken($token)
    {
        return response()->json($this->createTokenResponse($token));
    }
}
