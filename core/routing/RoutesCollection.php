<?php

namespace ImpressCMS\Core\Routing;

use ImpressCMS\Core\Controllers\ErrorPageController;
use ImpressCMS\Core\Controllers\IndexController;
use ImpressCMS\Core\Controllers\LegacyModuleController;
use ImpressCMS\Core\Controllers\LegacyResourceController;
use League\Route\RouteCollection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Internal routes collection
 */
class RoutesCollection extends RouteCollection {

	/**
	 * RoutesCollection constructor.
	 *
	 * @param ContainerInterface $container current container
	 * @param RouteParser $parser Route parer
	 * @param DataGenerator $generator Data generator
	 */
	public function __construct(ContainerInterface $container, RouteParser $parser, DataGenerator $generator) {
		parent::__construct($container, $parser, $generator);

		$this->any('/modules/{module}/{path}.php', [LegacyModuleController::class, '__invoke']);
		$this->any('/modules/{module}/{path}', [LegacyResourceController::class, '__invoke']);
		$this->get('/', [IndexController::class, 'getIndex']);
		$this->any('/', [ErrorPageController::class, 'anyError']);
	}

	/**
	 * Route that starts on any method
	 *
	 * @param string $path Regex path
	 * @param string|callable $handler Handler to handle route
	 *
	 * @return \League\Route\Route
	 */
	public function any($path, $handler) {
		return $this->map(
			[
				'GET',
				'POST',
				'DELETE',
				'PUT',
				'OPTIONS',
				'HEAD',
				'PATCH'
			],
			$path,
			$handler
		);
	}

}