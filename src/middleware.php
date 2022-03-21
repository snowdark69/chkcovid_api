<?php

use Firebase\JWT\JWT;
use Slim\App;

return function (App $app) {
    // e.g: $app->add(new \Slim\Csrf\Guard);
    // $app->add(new Tuupola\Middleware\HttpBasicAuthentication([
    //     "users" => [
    //         "username" => "password",
    //     ]
    // ]));

    //$container = $app->getContainer();    
    //$settings = $container->get('settings')['jwt']; 
    $container = $app->getContainer();   

    $app->add(new \Tuupola\Middleware\JwtAuthentication([
        
        "path" => "/api", /* or ["/api", "/admin"] */
        "attribute" => "decoded_token_data",
        //"secret" => $settings['secret'],//
        "secret" => $container->get('settings')['jwt']['secret'],
        //"secret" => getenv('settings')['jwt'],

        "algorithm" => ["HS256"],
        "error" => function ($response, $arguments) {
            $data["status"] = "error";
            $data["message"] = $arguments["message"];
            return $response
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        }
    ]));

};
