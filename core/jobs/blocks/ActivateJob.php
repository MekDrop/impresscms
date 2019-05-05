<?php


namespace ImpressCMS\Core\Jobs\Blocks;


use ImpressCMS\Core\Jobs\AbstractJob;

class ActiveJob extends AbstractJob
{

	/**
	 * Block ID for job
	 *
	 * @var \icms_view_block_Object
	 */
	public $block;

	/**
	 * ActiveJob constructor.
	 *
	 * @param \icms_view_block_Object $block Related block
	 */
	public function __construct(\icms_view_block_Object $block)
	{
		$this->block = $block;
	}

	/**
	 * @inheritDoc
	 */
	public function handle(): void
	{
		$this->block->isactive = true;
		if (!$this->block->store()) {
			throw new \Exception('Failed processing');
		}
	}

}