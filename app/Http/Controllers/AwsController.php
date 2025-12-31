<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\GuestAccessToken;
use App\Services\AwsS3Service;
use App\Services\AwsRekognition;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Exception;

class AwsController extends Controller
{
    function upload(Request $request)
    {
        $image = $request->get('image');
        $id    = (int) $request->get('guest_id');
        $token = $request->bearerToken();

        if (!$image) {
            return response()->json([
                'error' => 'Image not found.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $guestIdToken = GuestAccessToken::where('token', $token)->latest()->value('guest_id');
        if ($guestIdToken !== $id) {
            return response()->json([
                'error' => 'Guest not authorized'
            ], Response::HTTP_BAD_REQUEST);
        }

        $pattern     = '/^data:image\/\w+;base64,/';
        $base64Image = preg_replace($pattern, '', $image);
        $imageBinary = base64_decode($base64Image);

        $filename = "facial/{$id}.jpg";

        $s3  = new AwsS3Service();
        $key = $s3->uploadImage($imageBinary, $filename, 'image/jpeg');

        Guest::where('id', $guestIdToken)->upload([
            'foto_s3_key' => $key
        ]);

        return true;
    }

    function photoAnalisys(Request $request)
    {
        $image    = $request->get('image');
        $liveness = $request->get('liveness');
        $serious  = $request->get('serious');
        $id       = (int) $request->get('guest_id');
        $token    = $request->bearerToken();

        if (!$image) {
            return response()->json([
                'error' => 'Image not found.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $guestIdToken = GuestAccessToken::where('token', $token)->latest()->value('guest_id');
        if ($guestIdToken !== $id) {
            return response()->json([
                'error' => 'Guest not authorized'
            ], Response::HTTP_BAD_REQUEST);
        }

        $pattern     = '/^data:image\/\w+;base64,/';
        $base64Image = preg_replace($pattern, '', $image);
        $imageBinary = base64_decode($base64Image);

        $rekognition = new AwsRekognition();

        try {
            $return = $rekognition->getFaceDetection($imageBinary);
        } catch (Exception $ex) {
            return 'Não conseguimos identificar seu rosto';
        }

        $error = $rekognition->checkAnalysisParameters($return);

        if ($error !== false) {
            return $error[0];
        }

        return $rekognition->verifyLiveness(
            $return['FaceDetails'][0]['Smile']['Value'] ?? false,
            $liveness,
            $serious
        );
    }
}
