<?php

namespace ImpressCMS\Core\Providers;

use icms_db_Factory;
use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Database service provider
 */
class DatabaseServiceProvider extends AbstractServiceProvider
{

	/**
	 * @inheritdoc
	 */
	protected $provides = [
		'db',
		'xoopsDB'
	];

	/**
	 * @inheritdoc
	 */
	public function register()
	{
		$this->getContainer()->add('db', function () {
			return icms_db_Factory::pdoInstance();
		});
		$this->getContainer()->add('xoopsDB', function () {
			return icms_db_Factory::instance();
		});
	}
}