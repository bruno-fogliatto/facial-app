<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\GuestAccessToken;
use App\Services\AwsS3Service;
use App\Services\AwsRekognition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Exception;

class AwsController extends Controller
{
    public function upload(Request $request)
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

        $filename = "Faces/{$id}.jpg";

        $s3  = new AwsS3Service();
        $key = $s3->uploadImage($imageBinary, $filename, 'image/jpeg');

        Guest::where('id', $guestIdToken)->update([
            'foto_s3_key' => base64_encode($key)
        ]);

        return true;
    }

    public function photoAnalysis(Request $request)
    {
        $image    = $request->get('image');
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

            $this->saveRekognitionData($id, $return);

            $error  = $rekognition->checkAnalysisParameters($return);

            if ($error !== false) {
                return response()->json($error, Response::HTTP_OK);
            }

            return response()->json(true, Response::HTTP_OK);
        } catch (Exception $ex) {
            Log::error("Erro no Rekognition: {$ex->getMessage()}");

            return response()->json(
                'Não conseguimos identificar seu rosto. Certifique-se de estar bem posicionado e com boa iluminação.', 
                Response::HTTP_OK
            );
        }
    }

    /**
     * Salva dados da analise rekognition na sessão
     */
    private function saveRekognitionData($guestId, $rekognitionResult)
    {
        if (empty($rekognitionResult) || !isset($rekognitionResult['FaceDetails'][0])) {
            return;
        }

        $faceDetails = $rekognitionResult['FaceDetails'][0];
        $landmarks   =  $faceDetails['Landmarks'] ?? [];

        $nose_index = array_search('nose', array_column($landmarks, 'Type'));
        $nose       = $nose_index !== false ? $landmarks[$nose_index] : null;

        $analysisData = [
            'brightness'  => $faceDetails['Quality']['Brightness'] ?? 0,
            'sharpness'   => $faceDetails['Quality']['Sharpness'] ?? 0,
            'confidence'  => $faceDetails['Confidence'] ?? 0,
            'nose'        => $nose ? [
                'x' => $nose['X'],
                'y' => $nose['Y']
            ] : null,
            'pose'       => [
                'yaw'   => $faceDetails['Pose']['Yaw'] ?? 0,
                'pitch' => $faceDetails['Pose']['Pitch'] ?? 0,
                'roll'  => $faceDetails['Pose']['Roll'] ?? 0
            ],
            'eyes_open'   => $faceDetails['EyesOpen']['Value'] ?? false,
            'analyzed_at' => now()->toDateTimeString()
        ];

        session(["rekognition_analysis_{$guestId}" => $analysisData]);
    }

    /**
     * Retorna dados salvos da análise Rekognition
     */
    public function getRekognitionAnalysis(Request $request)
    {
        $guestId = $request->query('guest_id');

        if (!$guestId) {
            return response()->json([
                'error' => 'guest_id required.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $analysisData = session("rekognition_analysis_{$guestId}");

        if (!$analysisData) {
            return response()->json([
                'error' => 'Dados da análise não encontrados.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json($analysisData, Response::HTTP_OK);
    }

    /**
     * Busca a foto do S3
     */
    public function getPhoto(Request $request)
    {
        $guestId = $request->query('guest_id');

        if (!$guestId) {
            return response()->json([
                'error' => 'O parâmetro guest_id é obrigatório.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $guest = Guest::find($guestId);
        if (!$guest) {
            return response()->json([
                'error' => 'Convidado não encontrado.'
            ], Response::HTTP_NOT_FOUND);
        }

        if (empty($guest->foto_s3_key)) {
            return response()->json([
                'error' => 'Foto não encontrada.'
            ], Response::HTTP_NOT_FOUND);
        }
        
        $key = base64_decode($guest->foto_s3_key);

        $s3  = new AwsS3Service();
        return $s3->getSignetUrl($key, 60);
    }

}
