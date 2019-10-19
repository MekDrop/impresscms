<?php

/** mainfile is required, if it doesn't exist - installation is needed */

// ImpressCMS is not installed yet.
if (is_dir('install') && strpos($_SERVER['REQUEST_URI'], '/install') === false) {
	header('Location: install/index.php');
	exit();
}

define('ICMS_PUBLIC_PATH', __DIR__);

include_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'mainfile.php';

/**
 * @var \League\Route\Router $router
 */
$router = \Imponeer\ComposerRoutesRegistrationPlugin\PredefinedRouterFactory::create();
$request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();

(new \Zend\HttpHandlerRunner\Emitter\SapiEmitter())->emit(
	$router->dispatch($request)
);