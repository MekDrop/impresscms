<?php

use Phoenix\Migration\AbstractMigration;

class TemplateSetsInDatabaseAreStoreNotAsNameButAsID extends AbstractMigration
{
    protected function up(): void
    {
        $this->execute('ALTER TABLE `'.$this->prefix('tplfile').'` ADD COLUMN `tpl_tplset_id` INT NULL AFTER `tpl_type`, ADD INDEX `tpl_tplset_id` (`tpl_tplset_id`, `tpl_file`);');
        $this->execute('UPDATE `'.$this->prefix('tplfile').'` f JOIN `'.$this->prefix('tplset').'` s ON f.tpl_tplset = s.tplset_name SET f.tpl_tplset_id = s.tplset_id;');
        $this->execute('ALTER TABLE `'.$this->prefix('tplfile').'` DROP INDEX `tpl_tplset`;');
        $this->execute('ALTER TABLE `'.$this->prefix('tplfile').'` DROP COLUMN `tpl_tplset`;');
    }

    protected function down(): void
    {
    	$this->execute('ALTER TABLE `'.$this->prefix('tplfile').'` ADD COLUMN `tpl_tplset` VARCHAR(50) NOT NULL DEFAULT \'\';');
		$this->execute('ALTER TABLE `'.$this->prefix('tplfile').'` ADD INDEX `tpl_tplset` (`tpl_tplset`, `tpl_file`) USING BTREE;');
		$this->execute('UPDATE `'.$this->prefix('tplfile').'` f JOIN `'.$this->prefix('tplset').'` s ON f.tpl_tplset_id = s.tplset_id SET f.tpl_tplset = s.tplset_name;');
		$this->execute('ALTER TABLE `'.$this->prefix('tplfile').'` DROP INDEX `tpl_tplset_id`, DROP COLUMN `tpl_tplset_id`;');
	}

	/**
	 * Prefix table
	 *
	 * @param string $table Table to prefix
	 *
	 * @return string
	 */
	private function prefix(string $table): string
	{
		return \icms::getInstance()->get('db-connection-1')->prefix($table);
	}
}
