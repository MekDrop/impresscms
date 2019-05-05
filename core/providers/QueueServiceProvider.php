<?php

namespace ImpressCMS\Core\Providers;

use Bernard\Middleware\MiddlewareBuilder;
use Bernard\Producer;
use Bernard\QueueFactory\PersistentFactory;
use Bernard\Serializer\SimpleSerializer;
use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Queue service provider
 */
class QueueServiceProvider extends AbstractServiceProvider
{

	/**
	 * @inheritdoc
	 */
	protected $provides = [
		'queue'
	];

	/**
	 * @inheritdoc
	 */
	public function register()
	{
		$this->getContainer()->add('queue', function () {
			return new Producer(
				new PersistentFactory(
					new SimpleSerializer()
				),
				new MiddlewareBuilder()
			);
		});
	}
}