<?php
/**
 * Migration object
 *
 * @copyright   The ImpressCMS Project <http://www.impresscms.org>
 */

/**
 * This migration object
 *
 * @author Raimondas Rimkevičius aka MekDrop <mekdrop@impresscms.org>
 * @package ImpressCMS\Database\Migration
 */
class icms_db_migration_Object extends icms_core_Object {
		
	public function __construct() {
		parent::initVar('id', self::DTYPE_INTEGER, null, true);
		parent::initVar('name', self::DTYPE_STRING, null, true, 200, [
			self::VARCFG_VALIDATE_RULE => '/([A-Za-z0-9_]+)/i',
		]);		
		parent::initVar('module', self::DTYPE_STRING, null, true, 255, [
			self::VARCFG_VALIDATE_RULE => '/(@install|[A-Za-z0-9_\ \.]+)/i',
		]);
	}
	
	public function exec() {
		
	}
	
}
