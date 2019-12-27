<?php

namespace App\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Common\Http\Controllers\BaseApiController;
use Illuminate\Support\Facades\Auth;

class UserController extends BaseApiController
{

    protected $guard;
    /**
     * Create a new UserController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->guard = Auth::guard('api');
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUser()
    {
        try {
            if(!$user = $this->guard->user()) throw new \Exception("User not found", 1);
            
            return $this->successResponse($user, 'User info fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 406);
        }
    }

    public function login(Request $request)
    {
        try {
            
            $credentials = $request->only('email', 'password');

            if (!$token = $this->guard->attempt($credentials)) throw new \Exception('Username/Password Mismatched', 1);
            
            if(!$user = $this->guard->user()) throw new \Exception("User not found", 1);
            
            // $user->notify(new \App\User\Notifications\LoginEmail($user));
            return $this->successResponse($user, 'Logged in successfully', 200, ['X-Authorization' => $token]);

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 406);
        }
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->guard->logout();

        return $this->successResponse([], 'Successfully logged out');
    }

}
