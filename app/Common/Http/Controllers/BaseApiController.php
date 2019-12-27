<?php

namespace App\Common\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use App\Common\Models\Setting;
use Auth;

class BaseApiController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct()
    {
        //
    }

    public function successResponse($data = array(), string $message = 'Successful', int $code = 200, array $header = []) {
        $res = response()->json([
            'status' => true,
            'data' => $data,
            'message' => $message,
            'code' => $code,
        ], $code);

        if($header && is_array($header)) {
            foreach ($header as $key => $value) {
                $res->header($key, $value);
            }
        }
        return $res;
    }

    public function errorResponse(string $message = 'error', int $code = 404) {
        return response()->json([
            'status' => false,
            'message' => $message,
            'code' => $code,
        ], $code);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAuthUser()
    {
        return Auth::guard()->user();
    }

    public function getSetting($key) {
        return Setting::where('key', $key)->first();
    }
}
