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
	 *
	 *
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 */
	public function anyError(ServerRequestInterface $request, ResponseInterface $response) {
		$xoopsOption['pagetype'] = 'error';

		\icms::$response = new \icms_response_Error($xoopsOption);
		\icms::$response->errorNo = isset($_REQUEST['e']) ? (int)$_REQUEST['e'] : 500;
		if (isset($_REQUEST['msg'])) {
			\icms::$response->msg = $_REQUEST['msg'];
		}
		\icms::$response->render();
	}

}