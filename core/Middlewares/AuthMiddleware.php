<?php

namespace ImpressCMS\Core\Middlewares;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Checks if authenticated
 *
 * @package ImpressCMS\Core\Middlewares
 */
class AuthMiddleware implements MiddlewareInterface
{

	/**
	 * Process an incoming server request.
	 *
	 * Processes an incoming server request in order to produce a response.
	 * If unable to produce the response itself, it may delegate to the provided
	 * request handler to do so.
	 *
	 * @param ServerRequestInterface $request
	 * @param RequestHandlerInterface $handler
	 *
	 * @return ResponseInterface
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		if (\icms::$user) {
			return $handler->handle($request);
		}

		return new Response(403, ['Location' => '/'], null, '1.1', 'Need authentication to access this area');
	}
}