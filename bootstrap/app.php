<?php

namespace bootstrap;

require '../vendor/autoload.php';

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = true;
$config['db']['type']   = "sqlite";
$config['db']['file'] = "../db/database.db";
$config['name'] = "Kánon";
$config['path'] = "/kanon";

session_start();

$app = new \Slim\App(["settings" => $config]);
$container = $app->getContainer();

//remove trailing slash
$app->add(function (Request $request, Response $response, callable $next) {
    $uri = $request->getUri();
    $path = $uri->getPath();
    if ($path != '/' && substr($path, -1) == '/' && $path != $this->get('settings')['path'] . '/') {
        $uri = $uri->withPath(substr($path, 0, -1));
        
        if($request->getMethod() == 'GET') {
            return $response->withRedirect((string)$uri, 301);
        }
        else {
            return $next($request->withUri($uri), $response);
        }
    }

    return $next($request, $response);
});

$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};

$container['view'] = function ($container) {
    $templateVariables = [
        "router" => $container->router
    ];
    return new \Slim\Views\PhpRenderer('../templates/', $templateVariables);
};

$container['logger'] = function($c) {
    $logger = new \Monolog\Logger($c['settings']['name']);
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};

$container['csrf'] = function ($c) {
    $guard = new \Slim\Csrf\Guard();
    $guard->setFailureCallable(function ($request, $response, $next) {
        $request = $request->withAttribute("csrf_status", false);
        return $next($request, $response);
    });
    return $guard;
};

$app->add($container->csrf);
$app->add(\middleware\csrf::class);

$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $medoo = new \Medoo\Medoo([
        'database_type' => $db['type'],
        'database_file' => $db['file']
    ]);
    return $medoo;
};

$container['seed'] = function ($c) {
    $seed = new seed($c);
    return $seed;
};

$container->seed->check();

return $app;