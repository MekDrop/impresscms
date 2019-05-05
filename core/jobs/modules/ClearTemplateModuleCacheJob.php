<?php


namespace ImpressCMS\Core\Jobs\Modules;

use icms_view_Tpl;
use ImpressCMS\Core\Jobs\AbstractJob;

class ClearTemplateModuleCacheJob extends AbstractJob
{

	/**
	 * Module ID for job
	 *
	 * @var int
	 */
	public $mid;

	/**
	 * ClearTemplateModuleCacheJob constructor.
	 *
	 * @param \icms_module_Object $module Related module
	 */
	public function __construct(\icms_module_Object $module)
	{
		$this->mid = $module;
	}

	/**
	 * @inheritDoc
	 */
	public function handle(): void
	{
		icms_view_Tpl::template_clear_module_cache($this->mid);
	}
}