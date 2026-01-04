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
            'face_more_than_one' => 'Mais de um rosto detectado. Apenas uma pessoa deve estar na foto.',
            'face_occluded'      => 'O rosto deve estar completamente visível, sem obstruções.',
            'confidence'         => 'Não foi possível analisar seu rosto com precisão. Melhore a iluminação e posição.',
            'eyes_closed'        => 'Mantenha os olhos abertos durante a captura.',
            'photo_dark'         => 'Ambiente com pouca iluminação. Procure um local mais claro.',
            'photo_bright'       => 'Ambiente muito claro. Reduza a intensidade da luz.',
            'photo_blurry'       => 'Imagem com pouca nitidez. Mantenha-se parado e estável.',
            'remove_glasses'     => 'Retire seus óculos para uma melhor captura.',
            'face_not_aligned'   => 'Alinhe seu rosto de frente para a câmera.',
            'approach_camera'    => 'Aproxime-se mais da câmera.',
            'move_away_camera'   => 'Afaste-se um pouco da câmera.',
            'center_face_right'  => 'Mova-se mais para a direita do enquadramento.',
            'center_face_left'   => 'Mova-se mais para a esquerda do enquadramento.',
            'center_face_down'   => 'Posicione a câmera um pouco mais para baixo.',
            'center_face_high'   => 'Posicione a câmera um pouco mais para cima.',
            'eyes_error'         => 'Não foi possível detectar seus olhos corretamente. Melhore a iluminação.'
        ];

        $feedbacks = array_filter($feedbacks);

        if (empty($feedbacks)) {
            return false;
        }

        $first_key = array_key_first($feedbacks);
        $errorMessage = $messages[$first_key] ?? 'Foto não atende aos critérios de qualidade. Tente novamente.';

        return $errorMessage;
    }
}