<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\GuestAccessToken;
use Carbon\Carbon;

class VerifyGuestToken
{
    public function handle(Request $request, Closure $next)
    {
        $error      = null;
        $bearerToken = $request->bearerToken();

        if (!$bearerToken) {
            return response()->json([
                'error' => 'Missing Authorization header.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $expiresToken = GuestAccessToken::where('token', $bearerToken)->latest()->value('expires_at');
        if ($expiresToken) {
            $expiresAt = Carbon::parse($expiresToken, config('app.timezone'));
            if (!$expiresAt->isFuture()) {
                $error = 'Invalid or expired token.';
            }
        } else {
            $error = 'Token not found.';
        }

        return empty($error) ? $next($request) : response()->json(['error' => $error], Response::HTTP_UNAUTHORIZED);
    }
}