<?php

namespace App\Services;

use Aws\Rekognition\RekognitionClient;
use Aws\Exception;
use Symfony\Component\HttpFoundation\Response;

class AwsRekognition
{
    private RekognitionClient $rekognitionClient;
    private array $analysisParameters;

    public function __construct()
    {
        $this->rekognitionClient = new RekognitionClient([
            'region'      => config('aws.default_region'),
            'version'     => 'latest',
            'credentials' => [
                'key'    => config('aws.access_key'),
                'secret' => config('aws.secret_access_key')
            ]
        ]);

        $this->analysisParameters = config('biometry.rekognition');
    }

    public function getFaceDetection($photo)
    {
        return $this->rekognitionClient->detectFaces([
            'Attributes' => ['ALL'],
            'Image'      => [
                'Bytes' => $photo
            ]
        ]);
    }

    public function checkAnalysisParameters($return)
    {
        if (empty($return) || !isset($return['FaceDetails'][0])) {
            return false;
        }

        $face_details    = $return['FaceDetails'][0];
        $landmarks       = $face_details['Landmarks'] ?? [];
        $brightness      = $face_details['Quality']['Brightness'] ?? 1;
        $sharpness       = $face_details['Quality']['Sharpness'] ?? 1;
        $pose            = $face_details['Pose'] ?? [];
        $eyes_open       = $face_details['EyesOpen']['Value'] ?? false;
        $eyes_confidence = $face_details['EyesOpen']['Confidence'] ?? 0;
        $sunglasses      = $face_details['Sunglasses']['Value'] ?? null;
        $eyeglasses      = $face_details['Eyeglasses']['Value'] ?? null;

        $nose_index = array_search('nose', array_column($landmarks, 'Type'));
        $nose       = $nose_index !== false ? $landmarks[$nose_index] : null;

        $eye_left_index  = array_search('eyeLeft', array_column($landmarks, 'Type'));
        $eye_right_index = array_search('eyeRight', array_column($landmarks, 'Type'));
        $eye_separation  = 0;

        if ($eye_left_index !== false && $eye_right_index !== false) {
            $eye_separation = sqrt(
                pow($landmarks[$eye_right_index]['X'] - $landmarks[$eye_left_index]['X'], 2) +
                pow($landmarks[$eye_right_index]['Y'] - $landmarks[$eye_left_index]['Y'], 2)
            );
        }

        $feedbacks = [
            'face_more_than_one' => count($return['FaceDetails'] ?? []) > 1,
            'face_occluded'      => $face_details['FaceOccluded']['Value'] ?? false,
            'confidence'         => ($face_details['Confidence'] ?? 0) < ($this->analysisParameters['confidence'] ?? 95),
            'eyes_closed'        => !$eyes_open,
            'photo_dark'         => $brightness < ($this->analysisParameters['brightness_min'] ?? 70),
            'photo_bright'       => $brightness > ($this->analysisParameters['brightness_max'] ?? 95),
            'photo_blurry'       => $sharpness < ($this->analysisParameters['sharpness_min'] ?? 10),
            'remove_glasses'     => ($sunglasses && ($this->analysisParameters['sunglasses'] ?? 0)) || ($eyeglasses && ($this->analysisParameters['eyeglasses'] ?? 0)),
            'face_not_aligned'   => (
                ($pose['Yaw'] ?? 0) > ($this->analysisParameters['yaw_max'] ?? 10) ||
                ($pose['Yaw'] ?? 0) < ($this->analysisParameters['yaw_min'] ?? -10) ||
                ($pose['Pitch'] ?? 0) > ($this->analysisParameters['pitch_max'] ?? 15) ||
                ($pose['Pitch'] ?? 0) < ($this->analysisParameters['pitch_min'] ?? -15) ||
                ($pose['Roll'] ?? 0) > ($this->analysisParameters['roll_max'] ?? 10) ||
                ($pose['Roll'] ?? 0) < ($this->analysisParameters['roll_min'] ?? -10)
            ),
            'approach_camera'    => $eye_separation < ($this->analysisParameters['eye_separation_min'] ?? 0.2),
            'move_away_camera'   => $eye_separation > ($this->analysisParameters['eye_separation_max'] ?? 0.26),
            'center_face_right'  => $nose && $nose['X'] < 0.45,
            'center_face_left'   => $nose && $nose['X'] > 0.55,
            'center_face_down'   => $nose && $nose['Y'] < 0.55,
            'center_face_high'   => $nose && $nose['Y'] > 0.65,
            'eyes_error'         => $eyes_confidence < ($this->analysisParameters['eyes_open'] ?? 60)
        ];

        return $this->getFeedbackMessage($feedbacks);
    }

    public function getFeedbackMessage($feedbacks)
    {
        $messages = [
            'face_more_than_one' => 'Mais de um rosto detectado',
            'face_occluded'      => 'O rosto deve estar completamente visível',
            'confidence'         => 'Não é possível analisar seu rosto com precisão',
            'eyes_closed'        => 'Abra os olhos',
            'photo_dark'         => 'Ambiente com pouca iluminação',
            'photo_bright'       => 'Ambiente muito claro',
            'photo_blurry'       => 'Imagem com pouca nitidez',
            'remove_glasses'     => 'Retire seus óculos',
            'face_not_aligned'   => 'Alinhe seu rosto',
            'approach_camera'    => 'Aproxime-se da câmera',
            'move_away_camera'   => 'Afaste-se da câmera',
            'center_face_right'  => 'Se mova mais para a direita',
            'center_face_left'   => 'Se mova mais para a esquerda',
            'center_face_down'   => 'Se mova mais para baixo',
            'center_face_high'   => 'Se mova mais para cima',
            'eyes_error'         => 'Erro ao analisar os olhos'
        ];

        // primeira chave que tiver erro a gente pega o index
        $first_key_index = reset(array_keys(array_filter($feedbacks)));

        return $messages[$first_key_index] ?? false;
    }

    public function verifyLiveness($isSmiling, $liveness, $serious)
    {
        if ($isSmiling && $serious) {
            return [false, 'Fique sério'];
        }

        if (!$isSmiling && ($this->analysisParameters['smile'] ?? 1) && !$liveness) {
            return [true, 'Sorria'];
        }

        return true;
    }
}