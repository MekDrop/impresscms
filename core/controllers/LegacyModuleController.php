<?php

namespace ImpressCMS\Core\Controllers;
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
	 */
	public function __invoke(ServerRequestInterface $request): ResponseInterface
	{
		$_SERVER['SCRIPT_NAME'] = $_SERVER['REDIRECT_URL'];
		$_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];

		return new \icms_response_Text(
			require($request->getUri()->getPath())
		);
	}

}