<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\Registration;

class TokenValidationMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $key = env('JWT_SECRET');
        $token = explode(" ", $request->header("authorization"))[1];
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        $decodedArr = json_decode(json_encode($decoded), true);

        $user_id = $decodedArr['user_id'];
        $email = $decodedArr['email'];
        
        // Perform your authorization logic here
        // For example, check if the user is authenticated
        
        // If authorized, let the request continue
        return $next($request);

        // If not authorized, return an error response
        // For example:
        // return response()->json(['error' => 'Unauthorized'], 401);
    }
}
