<?php

namespace ImpressCMS\Core\Providers;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Defines routing provider
 *
 * @package ImpressCMS\Core\Providers
 */
class RoutingProvider extends AbstractServiceProvider {

	/**
	 * @inheritDoc
	 */
	public function register()
	{
		$this->getContainer()->share('response', Response::class);
		$this->getContainer()->share('request', function () {
			return ServerRequest::fromGlobals();
		});
	}
}