<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $error      = null;
        $authHeader = $request->bearerToken();

        if (!$authHeader) {
            return response()->json([
                'error' => 'Missing Authorization header.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $pattern  = '/Bearer\s+/';
        $apiToken = preg_match($pattern, $authHeader) ? preg_replace($pattern, '', $authHeader) : $authHeader;
        if ($apiToken !== config('auth.api_token')) {
            $error = 'Invalid API token.';
        }

        return empty($error) ? $next($request) : response()->json(['error' => $error], Response::HTTP_UNAUTHORIZED);
    }
}