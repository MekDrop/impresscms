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

switch ($GLOBALS['icmsConfig']['debug_mode']) {
	case 2:
		$whoopsHandler = new \Whoops\Handler\PrettyPageHandler();
		break;
	case 1:

		break;
	default:
		$whoopsHandler = new \Whoops\Handler\CallbackHandler(
			function ($exception, \Whoops\Exception\Inspector $inspector, $run) {
				switch ($inspector->getExceptionName()) {
					case \League\Route\Http\Exception\NotFoundException::class:
						echo 'Page not found';
						break;
					default:
						echo 'System error';
				}
			}
		);
}

if (isset($whoopsHandler)) {
	$router->middleware(new \Middlewares\Whoops(
		(new \Whoops\Run())
			->prependHandler($whoopsHandler)
			->register()
	));
}

$request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();

$response = $router->dispatch($request);

(new \Zend\HttpHandlerRunner\Emitter\SapiEmitter())->emit($response);