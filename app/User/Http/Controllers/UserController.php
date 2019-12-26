<?php

namespace App\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Common\Http\Controllers\BaseApiController;
use Auth;

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
        $this->guard = Auth::guard();
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUser()
    {
        return $this->successResponse($this->guard->user(), 'User info fetched successfully');
    }

    public function login(Request $request)
    {

        $credentials = $request->only('username', 'password');

        if ($token = $this->guard->attempt($credentials)) {
            $user = $this->getAuthUser();
            
            // $user->notify(new \App\User\Notifications\LoginEmail($user));
            return $this->successResponse($user, 'Logged in successfully', 200, ['X-Authorization' => $token]);
        }

        return $this->errorResponse('Username Password Mismatched', 401);
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
