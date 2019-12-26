<?php

namespace App\Common\Http\Middleware;

use JWTAuth;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Tymon\JWTAuth\Http\Parser\AuthHeaders;

class JwtMiddleware extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        try {
            $headerParser = new AuthHeaders;
            $headerParser->setHeaderName('X-Authorization');
            JWTAuth::parser()->setChain([$headerParser]);

            $user = JWTAuth::parseToken()->authenticate();

            return $next($request);

        } catch (\Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json([
                        'status' => false,
                        'message' => 'Invalid Request',
                        'code' => 400,
                    ], 400);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){

                if($token = JWTAuth::refresh()) return $next($request)->header('X-Authorization', $token);
                
            }else{
                return response()->json([
                        'status' => false,
                        'message' => 'Unauthorized Access',
                        'code' => 401,
                    ], 401);
            }
        }
    }
}