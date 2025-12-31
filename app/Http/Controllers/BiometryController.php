<?php

namespace App\Http\Controllers;

use App\Models\GuestAccessToken;
use App\Models\Guest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BiometryController extends Controller
{
    public function wizard(Request $request)
    {
        $validateGuest = $this->validateGuest($request->url());
        if (!$validateGuest['success']) {
            return $validateGuest['message'];
        }

        // Etapa atual armazenada no backend
        $step = session("biometry_step", 1);

        // 🔒 REGRA: só permitimos 1 à 6 por enquanto
        if ($step > 6) {
            $step = 6;
            session(["biometry_step" => 6]);
        }

        return Inertia::render("Layouts/Main", [
            'serverStep'         => $step,
            'access_token'       => $validateGuest['access_token'],
            'guest'              => $validateGuest['guest'],
            'lockedAfterCapture' => session('biometry_locked', false)
        ]);
    }

    public function syncStep(Request $request)
    {
        $request->validate([
            'step' => 'required|integer|in:1,2,3,4,5,6'
        ]);

        $current = session("biometry_step", 1);

        if ($request->step !== $current + 1 && $request->step !== $current) {
            abort(403);
        }
        
        session(["biometry_step" => $request->step]);

        return response()->noContent();
    }

    public function lock()
    {
        session(['biometry_locked' => true]);
        return response()->json(["locked" => true]);
    }

    private function validateGuest($url)
    {
        $guestToken = GuestAccessToken::where('biometry_link', $url)->latest()->first();

        if (empty($guestToken)) {
            return [
                'success' => false,
                'message' => 'Url not found.'
            ]; 
        }

        $expired   = true;
        $expiresAt = Carbon::parse($guestToken->expires_at, config('app.timezone'));
        if ($expiresAt->isFuture()) {
            $expired = false;
        }

        if ($expired) {
            return [
                'success' => false,
                'message' => 'Invalid or expired token.'
            ];
        }

        $guest = Guest::find($guestToken->guest_id);

        if (!$guest) {
            return [
                'success' => false,
                'message' => 'Guest not found.'
            ];
        }

        return [
            'success'      => true,
            'access_token' => $guestToken->token,
            'guest'        => $guestToken->guest_id
        ];
    }

    public function getImages(Request $request)
    {
        return config('biometry.images');
    }

    public function getRokegnitionParameters(Request $request)
    {
        return config('biometry.rekognition');
    }
}