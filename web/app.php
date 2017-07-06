<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../vendor/autoload.php';
if (PHP_VERSION_ID < 70000) {
    include_once __DIR__.'/../var/bootstrap.php.cache';
}

$env   = getenv('SYMFONY_ENV') !== false ? strtolower(getenv('SYMFONY_ENV')) : 'prod';
$debug = ($env === 'dev');

if ($debug) {
    Debug::enable();
}

$kernel = new AppKernel($env, $debug);
if (PHP_VERSION_ID < 70000) {
    $kernel->loadClassCache();
}

Request::enableHttpMethodParameterOverride();
$request  = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
