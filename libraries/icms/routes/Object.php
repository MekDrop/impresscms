<?php

/**
 * Defines route entry
 *
 * @property int $id ID
 * @property int $mid Module ID
 * @property string $uri URI
 * @property string[] $type Supported action types
 * @property string[]|null $middlewares Enabled middlewares
 * @property string|null $strategy Enabled strategy
 * @property string|null $name Route name
 * @property int $last_update Last update timestamp
 */
class icms_route_Object
	extends icms_ipf_Object
{

	/**
	 * Request type is HEAD
	 */
	const TYPE_HEAD = 'HEAD';

	/**
	 * Request type is GET
	 */
	const TYPE_GET = 'GET';

	/**
	 * Request type is DELETE
	 */
	const TYPE_DELETE = 'DELETE';

	/**
	 * Request type is POST
	 */
	const TYPE_POST = 'POST';

	/**
	 * Request type is PUT
	 */
	const TYPE_PUT = 'PUT';

	/**
	 * Request type is PATCH
	 */
	const TYPE_PATCH = 'PATCH';

	/**
	 * Request type is OPTIONS
	 */
	const TYPE_OPTIONS = 'OPTIONS';

	/**
	 * @inheritDoc
	 */
	public function __construct(icms_ipf_Handler $handler, array $data = array())
	{
		$this->initVar('id', self::DTYPE_INTEGER, null, false);
		$this->initVar('mid', self::DTYPE_INTEGER, null, false);
		$this->initVar('uri', self::DTYPE_STRING, null, true, 255);
		$this->initVar('type', self::DTYPE_LIST, [self::TYPE_GET, self::TYPE_HEAD], null, [
			self::VARCFG_POSSIBLE_OPTIONS => [
				self::TYPE_HEAD,
				self::TYPE_GET,
				self::TYPE_DELETE,
				self::TYPE_POST,
				self::TYPE_PUT,
				self::TYPE_PATCH,
				self::TYPE_OPTIONS
			],
			self::VARCFG_SEPARATOR => ','
		]);
		$this->initVar('action', self::DTYPE_STRING, null, true, 255);
		$this->initVar('middlewares', self::DTYPE_LIST, null, false, 255);
		$this->initVar('strategy', self::DTYPE_STRING, null, false, 255);
		$this->initVar('name', self::DTYPE_STRING, null, false, 50);
		$this->initVar('last_update', self::DTYPE_INTEGER, null, false);

		parent::__construct($handler, $data);
	}

}