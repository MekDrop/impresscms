<?php
/**
 * Contains the classes responsible for displaying a tree table filled with icms_ipf_Object
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license	http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since	1.1
 * @author	marcan <marcan@impresscms.org>
 */

/**
 * icms_ipf_view_Tree base class
 *
 * Base class representing a table for displaying icms_ipf_Object tree objects
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license	http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package	ICMS\IPF\View
 * @since	1.1
 * @author	marcan <marcan@impresscms.org>
 */
class icms_ipf_view_Tree extends icms_ipf_view_Table {

	/**
	 * Construct the tree object
	 *
	 * @param icms_ipf_Handler $objectHandler Handler
	 * @param icms_db_criteria_Compo|false $criteria		Criteria to filter results
	 * @param string[] $actions		An array of actions for this object
	 * @param boolean $userSide		TRUE - display on the user side; FALSE - do not display
	 */
	public function __construct(&$objectHandler, $criteria = false, $actions = array('edit', 'delete'), $userSide = false) {
		parent::__construct($objectHandler, $criteria, $actions, $userSide);
		$this->_isTree = true;
	}

	/**
	 * Get children objects given a specific category_pid
	 *
	 * @var int $category_pid id of the parent which children we want to retreive
	 * @return array of icms_ipf_Object
	 */
	public function getChildrenOf($category_pid = 0) {
		return isset($this->_objects[$category_pid])?$this->_objects[$category_pid]:false;
	}

	/**
	 * Create a row based on the item and children
	 *
	 * @param icms_ipf_Object	$object	@IPF object to use add to table row
	 * @param integer	$level	sub-level of the item
	 */
	public function createTableRow($object, $level = 0) {

		$aObject = array();

		$i = 0;

		$aColumns = array();
		$doWeHaveActions = false;

		foreach ($this->_columns as $column) {

			$aColumn = array();

			if ($i == 0) {
				$class = "head";
			} elseif ($i % 2 == 0) {
				$class = "even";
			} else {
				$class = "odd";
			}

			if ($column->_customMethodForValue && method_exists($object, $column->_customMethodForValue)) {
				$method = $column->_customMethodForValue;
				$value = $object->$method();
			} else {
				/**
				 * If the column is the identifier, then put a link on it
				 */
				if ($column->getKeyName() == $this->_objectHandler->identifierName) {
					$value = $object->getItemLink();
				} else {
					$value = $object->getVar($column->getKeyName());
				}
			}

			$space = '';
			if ($column->getKeyName() == $this->_objectHandler->identifierName) {
				for ($i = 0; $i < $level; $i++) {
					$space = $space . '--';
				}
			}

			if ($space != '') {
				$space .= '&nbsp;';
			}

			$aColumn['value'] = $space . $value;
			$aColumn['class'] = $class;
			$aColumn['width'] = $column->getWidth();
			$aColumn['align'] = $column->getAlign();
			$aColumn['key'] = $column->getKeyName();

			$aColumns[] = $aColumn;
			$i++;
		}

		$aObject['columns'] = $aColumns;

		$class = $class == 'even'?'odd':'even';
		$aObject['class'] = $class;

		$actions = array();

		// Adding the custom actions if any
		foreach ($this->_custom_actions as $action) {
			if (method_exists($object, $action)) {
				$actions[] = $object->$action();
			}
		}

		$controller = new icms_ipf_Controller($this->_objectHandler);

		if (in_array('edit', $this->_actions)) {
			$actions[] = $controller->getEditItemLink($object, false, true);
		}
		if (in_array('delete', $this->_actions)) {
			$actions[] = $controller->getDeleteItemLink($object, false, true);
		}
		$aObject['actions'] = $actions;

		$this->_tpl->assign('icms_actions_column_width', count($actions) * 30);
		$aObject['id'] = $object->id();
		$this->_aObjects[] = $aObject;

		$childrenObjects = $this->getChildrenOf($object->id());

		$this->_hasActions = $this->_hasActions? true : count($actions) > 0;

		if ($childrenObjects) {
			$level++;
			foreach ($childrenObjects as $subObject) {
				$this->createTableRow($subObject, $level);
			}
		}
	}

	/**
	 * Create all the rows
	 *
	 * @see icms_ipf_view_Table::createTableRows()
	 */
	public function createTableRows() {
		$this->_aObjects = array();

		if (count($this->_objects) > 0) {

			foreach ($this->getChildrenOf() as $object) {
				$this->createTableRow($object);
			}

			$this->_tpl->assign('icms_persistable_objects', $this->_aObjects);
		} else {
			$colspan = count($this->_columns) + 1;
			$this->_tpl->assign('icms_colspan', $colspan);
		}
	}

	/**
	 * Get all the objects, using parentid as the key
	 *
	 * @see icms_ipf_view_Table::fetchObjects()
	 */
	public function fetchObjects() {
		$ret = $this->_objectHandler->getObjects($this->_criteria, 'parentid');
		return $ret;

	}
}
