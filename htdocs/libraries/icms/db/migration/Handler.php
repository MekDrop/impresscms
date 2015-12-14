<?php
/**
 * Migrations handler
 *
 * @copyright   The ImpressCMS Project <http://www.impresscms.org>
 */

/**
 * This is class dealing with migrations
 *
 * @author Raimondas Rimkevičius aka MekDrop <mekdrop@impresscms.org>
 * @package ImpressCMS\Database\Migration
 */
class icms_db_migration_Handler extends icms_core_ObjectHandler {

	/**
	 * Creates a new migration
	 * 
	 * @return \icms_db_migration_Object
	 */
	public function &create() {
		$obj = new \icms_db_migration_Object();
		$obj->setVar('id', time());
		$cwd = getcwd();
		$o = strrpos($cwd, ICMS_MODULES_PATH, -strlen($cwd));
		if ($o !== FALSE) {
			$module_path_name = substr($cwd, strrpos($cwd, DIRECTORY_SEPARATOR, $o + 1));
			$obj->setVar('module', $module_path_name);
		} elseif (strrpos($cwd, ICMS_ROOT_PATH . DIRECTORY_SEPARATOR . 'install', -strlen($cwd)) !== false) {
			$obj->setVar('module', '@install');
		}		
		$obj->setNew();
		return $obj;
	}

	/**
	 * Gets migration by ID
	 *
	 * @param int $int_id		Migration ID
	 */
	public function &get($int_id) {
		$criteria = new icms_db_criteria_Item('id', $int_id);
		$criteria->setLimit(1);
		$objs = $this->getObjects($criteria, false);
		return (isset($objs[0]) === true)?$objs[0]:($ret = null);
	}
	
    /**
     * Retrieve objects from the database
     *
     * @param object $criteria {@link icms_db_criteria_Element} conditions to be met
     * @param bool $id_as_key use the ID as key for the array?
     *
     * @return array
     */
    public function getObjects($criteria = null, $id_as_key = false) {
		
	}	

	/**
	 * insert/update object
	 *
	 * @param object $object
	 */
	public function insert(&$object) {
		
	}

	/**
	 * delete object from database
	 *
	 * @param object $object
	 */
	public function delete(&$object) {
		
	}
	
}
