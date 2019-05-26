<?php

namespace ImpressCMS\Core\Providers;

use Enqueue\AmqpBunny\AmqpConnectionFactory as AmqpBunnyConnectionFactory;
use Enqueue\AmqpExt\AmqpConnectionFactory as AmqpConnectionFactoryExt;
use Enqueue\AmqpLib\AmqpConnectionFactory;
use Enqueue\Gearman\GearmanConnectionFactory;
use Enqueue\Gps\GpsConnectionFactory;
use Enqueue\Null\NullConnectionFactory;
use Enqueue\Pheanstalk\PheanstalkConnectionFactory;
use Enqueue\Sqs\SqsConnectionFactory;
use Enqueue\Stomp\StompConnectionFactory;
use Imponeer\QueueInteropConnectionFactoryHelper\QueueConnectionFactoryHelper;
use League\Container\ServiceProvider\AbstractServiceProvider;
use Swarrot\Broker\MessagePublisher\InteropMessagePublisher;

/**
 * Registers command bus/queue services
 *
 * @package ImpressCMS\Core\Providers
 */
class CommandBusServiceProvider extends AbstractServiceProvider
{
	/**
	 * @inheritdoc
	 */
	protected $provides = [
		'queue.context',
		'queue.publisher'
	];

	/**
	 * @inheritDoc
	 */
	public function register()
	{
		$this->getContainer()->add('queue.context', function () {
			$dsn = getenv('QUEUE_DSN') ?: 'file:';
			return QueueConnectionFactoryHelper::createContext($dsn);
		});
		$this->getContainer()->add('queue.publisher', function () {
			$message_publisher = new InteropMessagePublisher(
				$this->getContainer()->get('queue.context'),
				getenv('QUEUE_CHANNEL')
			);
			$message_publisher->publish();
		});
	}
}