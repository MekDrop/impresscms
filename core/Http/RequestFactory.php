<?php

namespace ImpressCMS\Core\Http;

use GuzzleHttp\Psr7\ServerRequest;
use ImpressCMS\Core\Data\BootConfig;
use Middlewares\BasePath;
use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\Router\Router;

class RequestFactory
{

	/**
	 * @var BootConfig
	 */
	private $bootConfig;

	public function __construct(BootConfig $bootConfig) {
		$this->bootConfig = $bootConfig;
	}

	/**
	 * Create request from current
	 *
	 * @return ServerRequestInterface
	 */
	public function createFromCurrent(): ServerRequestInterface {
		$basePath = $this->bootConfig->get('url_path');
		$request = ServerRequest::fromGlobals();

		if ($basePath !== '/' && $basePath !== null) {
			if (substr($basePath, -1) !== '/') {
				$basePath .= '/';
			}
			if ($basePath[0] !== '/') {
				$basePath = '/' . $basePath;
			}
			$uri = $request->getUri();
			$path = $uri->getPath();
			if (strpos($path, $basePath) === 0) {
				$path = substr($path, strlen($basePath)) ?: '';
				if ($path === '') {
					$path = '/';
				}
				return $request->withUri(
					$uri->withPath($path)
				);
			}
		}

		return $request;
	}
}