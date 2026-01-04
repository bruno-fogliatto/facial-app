<?php

namespace App\Http\Controllers;

use App\Models\GuestAccessToken;
use App\Models\Guest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class BiometryController extends Controller
{
    /**
     * Renderiza a wizard de biometria
     * Aceita query params ?step=X para navegação
     */
    public function wizard(Request $request)
    {
        $validateGuest = $this->validateGuest($request->url());
        if (!$validateGuest['success']) {
            return $validateGuest['message'];
        }

        $requestedStep = (int) $request->query('step');

        if ($requestedStep !== null) {
            if ($requestedStep < 1 || $requestedStep > 5) {
                $requestedStep = 1;
            }

            $canAccess = $this->canAccessStep($requestedStep);
            if (!$canAccess) {
                // Redireciona para o step atual válido
                $currentStep = session('biometry_step', 1);
                return redirect("/biometry/{$request->uuid}?step={$currentStep}");
            }

            session(['biometry_step' => $requestedStep]);
        }

        // Etapa atual armazenada no backend
        $step = session("biometry_step", 1);

        // 🔒 REGRA: só permitimos 1 à 5 por enquanto
        if ($step > 5) {
            $step = 5;
            session(["biometry_step" => 5]);
        }

        return Inertia::render("Layouts/Main", [
            'serverStep'         => $step,
            'access_token'       => $validateGuest['access_token'],
            'guest'              => $validateGuest['guest'],
            'lockedAfterCapture' => session('biometry_locked', false)
        ]);
    }

    /**
     * Sincroniza step via POST e Inertia router
     */
    public function syncStep(Request $request)
    {
        $request->validate([
            'step' => 'required|integer|in:1,2,3,4,5'
        ]);

        $canAccess = $this->canAccessStep($request->step);
        if (!$canAccess) {
            return response()->json([
                'error' => 'Não é possível acessar este step.'
            ], Response::HTTP_FORBIDDEN);
        }
        
        session(["biometry_step" => $request->step]);

        return response()->noContent();
    }

    /**
     * Verifica se pode acessar um step específico
     */
    private function canAccessStep(int $targetStep): bool
    {
        $currentStep = session('biometry_step', 1);
        $locked = session('biometry_locked', false);

        if ($targetStep > $currentStep + 1) {
            return false;
        }

        if ($locked && $currentStep > 3 && $targetStep <= 3) {
            return false;
        }

        return true;
    }

    private function validateGuest($url)
    {
        $guestToken = GuestAccessToken::where('biometry_link', $url)
            ->latest()
            ->first();

        if (empty($guestToken)) {
            return [
                'success' => false,
                'message' => 'Url not found.'
            ]; 
        }

        $expiresAt = Carbon::parse($guestToken->expires_at, config('app.timezone'));
        if (!$expiresAt->isFuture()) {
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
            'guest'        => $guest->id
        ];
    }

    public function getConfigs(Request $request)
    {
        $h = $request->query("h");

        if ($h === 'images') {
            return config('biometry.images');
        } elseif ($h === 'rekognition') {
            return config('biometry.rekognition');
        }
    }
}