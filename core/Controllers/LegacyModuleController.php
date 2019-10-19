<?php

namespace ImpressCMS\Core\Controllers;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Router for legacy modules
 *
 * @package ImpressCMS\Core\Controllers
 */
class LegacyModuleController {

	/**
	 * Include any file
	 *
	 * @param ServerRequestInterface $request
	 *
	 * @return ResponseInterface
	 */
	public function __invoke(ServerRequestInterface $request): ResponseInterface
	{
		$_SERVER['SCRIPT_NAME'] = $_SERVER['REDIRECT_URL'];
		$_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];

		$file = ICMS_ROOT_PATH . $request->getUri()->getPath();

		if (!file_exists($file)) {
			return new Response(404);
		}

		return new Response(
			200,
			[],
			include($file)
		);
	}

}