<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class LogoutController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        try {
            $token = JWTAuth::getToken();
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token not provided'
                ], 400);
            }

            JWTAuth::invalidate($token);

            return response()->json([
                'success' => true,
                'message' => 'Successfully logged out'
            ], 200);
        } catch (TokenExpiredException $th) {
            return response()->json([
                'success' => false,
                'message' => 'Token has already expired'
            ], 400);
        } catch (TokenInvalidException $th) {
            return response()->json([
                'success' => false,
                'message' => 'Token is invalid'
            ], 400);
        } catch (\Exception $th) {
            return response()->json([
                'success' => false,
                'message' => 'Could not log out, please try again'
            ], 500);
        }
    }
}
