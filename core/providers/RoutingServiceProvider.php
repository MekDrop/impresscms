<?php

namespace ImpressCMS\Core\Providers;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use League\Container\ServiceProvider\AbstractServiceProvider;

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