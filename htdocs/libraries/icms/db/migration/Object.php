<?php
/**
 * Migration object
 *
 * @copyright   The ImpressCMS Project <http://www.impresscms.org>
 */

/**
 * This migration object
 *
 * @author	Raimondas Rimkevičius aka MekDrop <mekdrop@impresscms.org>
 * @package ImpressCMS\Database\Migration
 * 
 * @property int	$id			ID of this migration
 * @property string	$name		Name of migration
 * @property string $module		Module where this migration belongs
 * @property bool	$applied	Was applied?
 */
class icms_db_migration_Object extends icms_core_Object {
		
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::initVar('id', self::DTYPE_INTEGER, null, true);
		parent::initVar('name', self::DTYPE_STRING, null, true, 200, [
			self::VARCFG_VALIDATE_RULE => '/([A-Za-z0-9_]+)/i',
		]);		
		parent::initVar('module', self::DTYPE_STRING, null, true, 255, [
			self::VARCFG_VALIDATE_RULE => '/(@install|[A-Za-z0-9_\ \.]+)/i',
		]);
		parent::initVar('applied', self::DTYPE_BOOLEAN, false, true);
	}
	
	/**
	 * Gets filename for this migration
	 * 
	 * @return string
	 */
	public function getFileName() {
		if (!$this->id || !$this->name || !$this->module) {
			throw new Exception('All required data not set for filename generation');
		}
		if ($this->module === '@install') {
			return ICMS_ROOT_PATH . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR . $this->id . '_' . $this->name . '.php';
		} else {
			return ICMS_MODULES_PATH . DIRECTORY_SEPARATOR . $this->module . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR . $this->id . '_' . $this->name . '.php';
		}
	}
	
}
