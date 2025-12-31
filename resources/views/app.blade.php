<html>
    <head>
        <link rel="icon" type="image/png" sizes="32x32" href="https://bluefox-facial-bucket.s3.us-east-2.amazonaws.com/Assets/favicon-32x32.png">
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        @vite('resources/js/app.js')
        @inertiaHead
    </head>
    <body>
        @inertia
    </body>
</html>