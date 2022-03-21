<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        // Config connect database
        'db' => [
            'host' => 'x.x.x.x',
            'dbname' => 'db',
            'user' => 'user',
            'pass' => 'password',
        ],

        // jwt settings
        "jwt" => [
            'secret' => 'secret'
        ],

        "about" => [
            'AppName' => 'APILabCovid',
            'version' => '1.0.0'
        ],

    ],
];
