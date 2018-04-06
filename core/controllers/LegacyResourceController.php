<?php

namespace ImpressCMS\Core\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LegacyResourceController {

	/**
	 * Include any file
	 *
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 */
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response) {
		if ($request->getUri()->getPath()[0] == '.') {
			$_REQUEST['e'] = 403;
			http_response_code(403);
			include 'error.php';
			exit();
		}
		$file = $request->getUri()->getPath();
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
		$response->withHeader("Last-Modified", gmdate("D, d M Y H:i:s", filemtime($file))." GMT" )
				 ->withHeader('Content-Type', $mimetype)
		 		 ->withHeader('Etag', md5_file($file));
		readfile($file);
	}
}