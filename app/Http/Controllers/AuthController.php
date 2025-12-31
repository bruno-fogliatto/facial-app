<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\GuestAccessToken;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function createToken(Request $request)
    {
        if (!isset($request->guest_id)) {
            return response()->json([
                'error' => 'Missing guest_id parameter'
            ], Response::HTTP_BAD_REQUEST);
        }

        $guest = Guest::find($request->guest_id);

        if (!$guest) {
            return response()->json([
                'error' => "Guest not founr"
            ], Response::HTTP_NOT_FOUND);
        }

        $now = Carbon::now(config('app.timezone'));
        $exp = $now->copy()->addMinutes((int) config('app.expiry_time'));

        $guestToken = GuestAccessToken::where('guest_id', $guest->id)->latest()->first();

        if (!empty($guestToken)) {
            $expiresAt = Carbon::parse($guestToken->expires_at, config('app.timezone'));
            if ($expiresAt->isFuture()) {
                return response()->json([
                    'access_token'  => $guestToken->token,
                    'token_type'    => 'Bearer',
                    'expires_in'    => $expiresAt->toDateTimeString()
                ], Response::HTTP_OK);
            }
        }

        $payload = [
            'iss' => config('app.url'),
            'sub' => $guest->id,
            'iat' => $now->copy()->setTimezone('UTC')->timestamp,
            'exp' => $now->copy()->setTimezone('UTC')->timestamp
        ];

        $jwt = JWT::encode($payload, config('auth.jwt_secret'), 'HS256');

        $token = GuestAccessToken::create([
            'guest_id'      => $guest->id,
            'token'         => $jwt,
            'expires_at'    => $exp,
        ]);

        return response()->json([
            'access_token'  => $token->token,
            'token_type'    => 'Bearer',
            'expires_in'    => $token->expires_at->toDateTimeString(),
        ], Response::HTTP_CREATED);
    }

    public function revokeToken(Request $request)
    {
        if (!isset($request->guest_id)) {
            return response()->json([
                'error' => 'Missing guest_id parameter.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $guest = Guest::find($request->guest_id);
        if (!$guest) {
            return response()->json([
                'error' => 'Guest not found.'
            ], Response::HTTP_NOT_FOUND);
        }

        $lastToken = GuestAccessToken::where('guest_id', $guest->id)->latest()->first();

        if (!$lastToken) {
            return response()->json([
                'message' => 'Guest has no token.'
            ], Response::HTTP_OK);
        }

        $lastToken->update([
            'expires_at' => now()
        ]);

        return response()->json([
            'message' => 'Token revoked successfully.'
        ], Response::HTTP_OK);
    }

    public function getBiometryLink(Request $request)
    {
        $link  = null;
        $token = $request->bearerToken();

        $data = GuestAccessToken::where('token', $token)->latest()->value('biometry_link');

        if (!empty($data)) {
            $link = $data;
        } else {
            $url  = config('app.url');
            $uuid = Str::uuid();
            $link = "{$url}/biometry/{$uuid}";

            GuestAccessToken::where('token', $token)->update([
                'biometry_link' => $link
            ]);
        }

        return response()->json([
            'link' => $link
        ], Response::HTTP_CREATED);
}
}