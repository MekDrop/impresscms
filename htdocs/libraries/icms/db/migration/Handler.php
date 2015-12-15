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
		$obj->setVar('module', $this->getModuleNameFromPath(getcwd()));		
		$obj->setNew();		
		return $obj;
	}
	
	/**
	 * Get module name from path
	 * 
	 * @param string		$cwd		Path from where to extract module name
	 * 
	 * @return string|null
	 */
	protected function getModuleNameFromPath($cwd) {
		$o = strrpos($cwd, ICMS_MODULES_PATH, -strlen($cwd));
		if ($o !== FALSE) {
			return substr($cwd, strrpos($cwd, DIRECTORY_SEPARATOR, $o + 1));
		} elseif (strrpos($cwd, ICMS_ROOT_PATH . DIRECTORY_SEPARATOR . 'install', -strlen($cwd)) !== false) {
			return '@install';
		}
		return null;
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
     * @param icms_db_criteria_Element	$criteria	Conditions to be met
     * @param bool						$id_as_key	Use the ID as key for the array?
     *
     * @return array
     */
    public function getObjects($criteria = null, $id_as_key = false) {
		$ret = [];
		if ($criteria instanceof \icms_db_criteria_Element) {
			if ($criteria->groupby) {
				throw new Exception('Grouby criteria definition for icms_db_migration_Handler currently is unsuported');
			}			
			if (($criteria instanceof \icms_db_criteria_Item) && isset($criteria->function)) {
				throw new Exception('Grouby criteria definition for icms_db_migration_Handler currently is unsuported');
			}
			if ($criteria->limit > 0) {
				$limit = $criteria->limit;
				$o = 0;
			}
			if ($criteria->start > 0) {
				$start = $criteria->start;
				$i = 0;
			}			
			$code = $criteria->renderPHP();
			if (!empty($code)) {
				$filter_func = eval('return function($data) { return ' . $code . ';}');
			}
		}
		foreach ([
			ICMS_MODULES_PATH . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR . '*_*.php',
			ICMS_ROOT_PATH . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR . '*_*.php'
		] as $path) {
			$iterator = new \GlobIterator(
				$path,
				\FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::CURRENT_AS_FILEINFO
			);
			foreach ($iterator as $fileInfo) {				
				$obj = $this->convertFileInfoToObject($fileInfo);
				if (
						(isset($filter_func) && !$filter_func($obj)) ||
						(isset($start) && ($i++ < $start))
				) {
					continue;
				}
				if ($id_as_key === true) {
					$ret[$obj->id] = $obj;
				} else {
					$ret[] = $obj;
				}
				if (isset($limit) && (++$o > $limit)) {
					break;
				}
			}
		}
		return $ret;
	}

	/**
	 * Converts SplFileInfo file system object to \icms_db_migration_Object
	 * 
	 * @param \SplFileInfo					$info
	 * 
	 * @return \icms_db_migration_Object
	 */
	protected function convertFileInfoToObject(\SplFileInfo $info) {
		$obj = new \icms_db_migration_Object();
		$obj->unsetNew();
		list($id, $name) = explode('_', $info->getBasename('.php'), 2);
		$module = $this->getModuleNameFromPath($info->getPath());
		$obj->assignVars(compact('module', 'id', 'name'));
		return $obj;
	}

	/**
	 * insert/update object
	 *
	 * @param object $object
	 */
	public function insert(\icms_db_migration_Object &$object) {
		if ($object->isChanged()) {
			
		}
	}
	
	public function apply(\icms_db_migration_Object &$object) {
		
	}
	
	public function revert(\icms_db_migration_Object &$object) {
		
	}	

	/**
	 * Delete migration
	 * 
	 * @param \icms_db_migration_Object $object		Migration to delete
	 * 
	 * @return boolean
	 */
	public function delete(\icms_db_migration_Object &$object) {
		if ($object->isNew()) {
			return false;
		}
		if ($object->applied) {
			try {
				$this->revert($object);
			} catch (Exception $ex) {
				return false;
			}
		}		
		return \icms_core_Filesystem::deleteFile($object->getFileName());		
	}	
	
}
