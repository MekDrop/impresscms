<?php

namespace ImpressCMS\Core\Controllers;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LegacyResourceController {

	/**
	 * Include any file
	 *
	 * @param ServerRequestInterface $request
	 *
	 * @return ResponseInterface
	 */
	public function __invoke(ServerRequestInterface $request): ResponseInterface
	{
		$file = $request->getUri()->getPath();
		if ($file[0] == '.') {
			return new Response(403);
		}
		if (!file_exists($file)) {
			return new Response(404);
		}
		switch (strtolower(pathinfo($file, PATHINFO_EXTENSION))) {
			case 'css':
				$mimetype = 'text/css';
				break;
			case 'js':
				$mimetype = 'text/javascript';
				break;
			default:
				$mimetype = mime_content_type($file);
		}
		return new Response(
			200,
			[
				"Last-Modified" => gmdate("D, d M Y H:i:s", filemtime($file)) . " GMT",
				'Content-Type' => $mimetype,
				'Etag' => md5_file($file)
			],
			fopen($file, 'rb')
		);
	}
}