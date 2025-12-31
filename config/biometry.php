<?php

return [
    'images' => [
        'wellcome' => 'https://bluefox-facial-bucket.s3.us-east-2.amazonaws.com/Assets/wellcome.jpg',
        'logo'     => 'https://bluefox-facial-bucket.s3.us-east-2.amazonaws.com/Assets/logo.jpeg',
        'logo2'    => 'https://bluefox-facial-bucket.s3.us-east-2.amazonaws.com/Assets/logo2.png',
        'favicon'  => 'https://bluefox-facial-bucket.s3.us-east-2.amazonaws.com/Assets/favicon-32x32.png',
        'camera'   => 'https://bluefox-facial-bucket.s3.us-east-2.amazonaws.com/Assets/camera.png'
    ],
    'rekognition' => [
        'brightness_min'      => 70,
        'brightness_max'      => 95,
        'sharpness_min'       => 10,
        'eyes_open'           => 60,
        'yaw_min'             => -10,
        'yaw_max'             => 10,
        'pitch_min'           => -15,
        'pitch_max'           => 15,
        'roll_min'            => -10,
        'roll_max'            => 10,
        'eyes_separation_min' => 0.2,
        'eyes_separation_max' => 0.26,
        'confidence'          => 95,
        'smile'               => 1,
        'sunglasses'          => 0,
        'eyeglasses'          => 0
    ],
];