<?php

namespace ImpressCMS\Core\Jobs;

use Bernard\Message\AbstractMessage;

abstract class AbstractJob extends AbstractMessage
{

	/**
	 * Handle current job
	 */
	abstract public function handle(): void;

}