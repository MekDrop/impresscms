<?php


namespace ImpressCMS\Core\Jobs\Modules;

use ImpressCMS\Core\Jobs\AbstractJob;

class ActivateJob extends AbstractJob
{

	/**
	 * Module ID for job
	 *
	 * @var \icms_module_Object
	 */
	public $module;

	/**
	 * ActiveJob constructor.
	 *
	 * @param \icms_module_Object $module Related module
	 */
	public function __construct(\icms_module_Object $module)
	{
		$this->module = $module;
	}

	/**
	 * @inheritDoc
	 */
	public function handle(): void
	{
		$this->module->isactive = true;
		if (!$this->module->store()) {
			throw new \Exception('Failed processing');
		}
	}

}