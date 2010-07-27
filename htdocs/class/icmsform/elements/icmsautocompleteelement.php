<?php
/**
 * Form control creating an autocomplete select box powered by Scriptaculous
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package		icms_ipf_Object
 * @since		  1.1
 * @author		  marcan <marcan@impresscms.org>
 * @version		$Id$
 */

if (!defined('ICMS_ROOT_PATH')) die("ImpressCMS root path not defined");

class IcmsAutocompleteElement extends icms_form_elements_Text {

	var $_include_file;

	/**
	 * Constructor
	 * @param	object    $object   reference to targetobject (@link icms_ipf_Object)
	 * @param	string    $key      the form name
	 */
	function IcmsAutocompleteElement($caption, $name, $include_file, $size, $maxlength, $value="") {
		$this->_include_file = $include_file;
		parent::__construct($caption, $name, $size, $maxlength, $value);
	}

	/**
	 * Prepare HTML for output
	 *
	 * @return	string  $ret  the constructed HTML
	 */
	function render(){
		$ret = "<input type='text' name='".$this->getName()."' id='".$this->getName()."' size='".$this->getSize()."' maxlength='".$this->getMaxlength()."' value='".$this->getValue()."'".$this->getExtra()." />";

		$ret .= '	<div class="icms_autocomplete_hint" id="icms_autocomplete_hint' . $this->getName() . '"></div>

  	<script type="text/javascript">
  		new Ajax.Autocompleter("' .$this->getName(). '","icms_autocomplete_hint' .$this->getName(). '","' . $this->_include_file . '?key=' . $this->getName() . '");
  	</script>';

		return $ret;
	}
}

?>