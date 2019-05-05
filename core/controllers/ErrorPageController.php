<?php

namespace ImpressCMS\Core\Controllers;
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
	 */
	public function anyError(ServerRequestInterface $request): ResponseInterface
	{
		global $xoopsOption;
		$xoopsOption['pagetype'] = 'error';

		$query = $request->getQueryParams();

		$response = new \icms_response_Error();
		$response->errorNo = isset($query['e']) ? (int)$query['e'] : 500;
		if (isset($query['msg'])) {
			$response->msg = $query['msg'];
		}

		return $response;
	}

}