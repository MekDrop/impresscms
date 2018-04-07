<?php

namespace ImpressCMS\Core\Routing;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractController {

	protected $request;

	protected $response;

	/**
	 * AbstractController constructor.
	 *
	 * @param ServerRequestInterface $request Current Request
	 * @param ResponseInterface $response	  Current response
	 */
	public function __construct(ServerRequestInterface $request, ResponseInterface $response) {
		$this->request = $request;
		$this->response = $response;
	}

	protected function render($view) {

	}

}