<?php

namespace ImpressCMS\Core\Controllers;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Shows errors page
 *
 * @package ImpressCMS\Core\Controllers
 */
class ErrorPageController {

	/**
	 * Shows any error response
	 *
	 * @param ServerRequestInterface $request
	 *
	 * @return ResponseInterface
	 */
	public function anyError(ServerRequestInterface $request): ResponseInterface
	{
		global $xoopsOption;
		$xoopsOption['pagetype'] = 'error';

		$query = $request->getQueryParams();

		return new Response(
			isset($query['e']) ? (int)$query['e'] : 500,
			[],
			isset($query['msg']) ? $query['msg'] : null
		);
	}

}