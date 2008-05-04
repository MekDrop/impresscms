<?php
/**
 * xos_logos_PageBuilder component class file
 *
 * @copyright	The XOOPS project http://www.xoops.org/
 * @license      http://www.fsf.org/copyleft/gpl.html GNU public license
 * @package      xos_logos
 * @subpackage   xos_logos_PageBuilder
 * @version		$Id: theme_blocks.php 1029 2007-09-09 03:49:25Z phppp $
 * @author       Skalpa Keo <skalpa@xoops.org>
 * @since        2.3.0
 */
/**
 * This file cannot be requested directly
 */
if ( !defined( 'XOOPS_ROOT_PATH' ) )	exit();

include_once XOOPS_ROOT_PATH . '/class/xoopsblock.php';
include_once XOOPS_ROOT_PATH . '/class/template.php';

/**
 * xos_logos_PageBuilder main class
 *
 * @package     xos_logos
 * @subpackage  xos_logos_PageBuilder
 * @author 		Skalpa Keo
 * @since       2.3.0
 */
class xos_logos_PageBuilder {
	
	var $theme = false;
	
	var $blocks = array();	

	function xoInit( $options = array() ) {
	    $this->retrieveBlocks();
	    if ( $this->theme ) {
			$this->theme->template->assign_by_ref( 'xoBlocks', $this->blocks );
	    }
	    return true;
	}
	
	/**
	 * Called before a specific zone is rendered
	 */
	function preRender( $zone = '' ) {
	}
	/**
	 * Called after a specific zone is rendered
	 */
	function postRender( $zone = '' ) {
	}	
	
	function retrieveBlocks() {
		global $xoops, $xoopsUser, $xoopsModule, $xoopsConfig;

		$startMod = ( $xoopsConfig['startpage'] == '--' ) ? 'system' : $xoopsConfig['startpage']; //Getting the top page

		$fullurl = "http://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		$url = substr(str_replace(XOOPS_URL,'',$fullurl),1);

		$arr = explode('-',$startMod);
		if (count($arr) > 1){
			$page_handler =& xoops_gethandler('page');
			$page = $page_handler->get($arr[1]);
			if (is_object($page)){
				$mid = $page->getVar('page_moduleid');
				$module_handler =& xoops_gethandler('module');
				$module =& $module_handler->get($mid);
				$dirname = $module->getVar('dirname');
				$purl = $page->getVar('page_url');
				$isStart = ($purl == $url || $purl == $fullurl);
			}
		}else{
			if ( @is_object( $xoopsModule ) ) {
				list( $mid, $dirname ) = array( $xoopsModule->getVar('mid'), $xoopsModule->getVar('dirname') );
				$isStart = ( substr( $_SERVER['PHP_SELF'], -9 ) == 'index.php' && $xoopsConfig['startpage'] == $dirname );
			} else {
				list( $mid, $dirname ) = array( 1, 'system' );
				$isStart = !@empty( $GLOBALS['xoopsOption']['show_cblock'] );
			}
		}

		if ($isStart){
			$modid = '0-1';
		}else{
			$page_handler =& xoops_gethandler('page');
			$criteria = new CriteriaCompo(new Criteria('page_status', 1));
			$pages = $page_handler->getObjects($criteria);
			$pid = 0;
			foreach ($pages as $page){
				$purl = $page->getVar('page_url');
				if (substr($purl,-1) == '*'){
					$purl = substr($purl,0,-1);
					if (substr($url,0,strlen($purl)) == $purl || substr($fullurl,0,strlen($purl)) == $purl) {
						$pid = $page->getVar('page_id');
						break;
					}
				}else{
					if ($purl == $url || $purl == $fullurl){
						$pid = $page->getVar('page_id');
						break;
					}
				}
			}
			$modid = $mid.'-'.$pid;
		}

		$groups = @is_object( $xoopsUser ) ? $xoopsUser->getGroups() : array( XOOPS_GROUP_ANONYMOUS );
		
		# Adding dynamic block area/position system - TheRpLima - 2007-10-21
		/*
		$oldzones = array(
		XOOPS_SIDEBLOCK_LEFT				=> 'canvas_left',
		XOOPS_SIDEBLOCK_RIGHT				=> 'canvas_right',
		XOOPS_CENTERBLOCK_LEFT				=> 'page_topleft',
		XOOPS_CENTERBLOCK_CENTER			=> 'page_topcenter',
		XOOPS_CENTERBLOCK_RIGHT				=> 'page_topright',
		XOOPS_CENTERBLOCK_BOTTOMLEFT		=> 'page_bottomleft',
		XOOPS_CENTERBLOCK_BOTTOM			=> 'page_bottomcenter',
		XOOPS_CENTERBLOCK_BOTTOMRIGHT		=> 'page_bottomright',
		);
		*/
		$xblock = new XoopsBlock();
		$oldzones = $xblock->getBlockPositions();
		#
		foreach ( $oldzones as $zone ) {
			$this->blocks[$zone] = array();
		}
		if ( $this->theme ) {
			$template =& $this->theme->template;
			$backup = array( $template->caching, $template->cache_lifetime );
		} else {
			$template =& new XoopsTpl();
		}
		$xoopsblock = new XoopsBlock();
    	$block_arr = array();
	    $block_arr = $xoopsblock->getAllByGroupModule( $groups, $modid, $isStart, XOOPS_BLOCK_VISIBLE);
	    foreach ( $block_arr as $block ) {
	    	$side = $oldzones[ $block->getVar('side') ];
	    	if ( $var = $this->buildBlock( $block, $template ) ) {
	    		$this->blocks[$side][$var["id"]] = $var;
	    	}
	    }
		if ( $this->theme ) {
			list( $template->caching, $template->cache_lifetime ) = $backup;
		}
	}

	function generateCacheId($cache_id) {
		if ($this->theme) {
			$cache_id = $this->theme->generateCacheId($cache_id);
		}
		return $cache_id;
	}
	
function buildBlock( $xobject, &$template ) {
        // The lame type workaround will change
        // bid is added temporarily as workaround for specific block manipulation
    global $xoopsUser, $xoopsConfigPersona;
    if ($xoopsConfigPersona['editre_block'] == 1 ) {
 	    if ($xoopsUser && $xoopsUser->isAdmin()){
 		    $titlebtns = "<a href=".XOOPS_URL."/modules/system/admin.php?fct=blocksadmin&op=edit&bid=".$xobject->getVar('bid')."> <img src=".XOOPS_URL."/images/icons/edit_block.gif"." title="._EDIT." alt="._EDIT."  /></a>";
 		    if ($xobject->getVar( 'dirname' ) != 'system'){
 		        $titlebtns .= "<a href=".XOOPS_URL."/modules/system/admin.php?fct=blocksadmin&op=delete&bid=".$xobject->getVar('bid')."> <img src=".XOOPS_URL."/images/icons/delete_block.gif"." title="._DELETE." alt="._DELETE."  /> </a>";
 		    }
 	    }else{
 	    	$titlebtns = '';
 	    }
 	        $block = array(
            'id'        => $xobject->getVar( 'bid' ),
            'module'    => $xobject->getVar( 'dirname' ),
            'title'        =>  $xobject->getVar( 'title' ) . $titlebtns,         
             //'name'        => strtolower( preg_replace( '/[^0-9a-zA-Z_]/', '', str_replace( ' ', '_', $xobject->getVar( 'name' ) ) ) ),
            'weight'    => $xobject->getVar( 'weight' ),
            'lastmod'    => $xobject->getVar( 'last_modified' ),
        );
       }else{
       	   		$block = array(
			'id'	    => $xobject->getVar( 'bid' ),
			'module'	=> $xobject->getVar( 'dirname' ),
			'title'		=> $xobject->getVar( 'title' ),
			//'name'		=> strtolower( preg_replace( '/[^0-9a-zA-Z_]/', '', str_replace( ' ', '_', $xobject->getVar( 'name' ) ) ) ),
			'weight'	=> $xobject->getVar( 'weight' ),
			'lastmod'	=> $xobject->getVar( 'last_modified' ),
		);
	    }
		//global $xoopsLogger;
		
		$xoopsLogger =& XoopsLogger::instance();
		
		$bcachetime = intval( $xobject->getVar('bcachetime') );
		//$template =& new XoopsTpl();
        if (empty($bcachetime)) {
            $template->caching = 0;
        } else {
            $template->caching = 2;
            $template->cache_lifetime = $bcachetime;
        }
		$tplName = ( $tplName = $xobject->getVar('template') ) ? "db:$tplName" : "db:system_block_dummy.html";
		$cacheid = $this->generateCacheId( 'blk_' . $xobject->getVar( 'dirname', 'n' ) . '_' . $xobject->getVar('bid')/*, $xobject->getVar( 'show_func', 'n' )*/ );

        if ( !$bcachetime || !$template->is_cached( $tplName, $cacheid ) ) {
            $xoopsLogger->addBlock( $xobject->getVar('name') );
            if ( ! ( $bresult = $xobject->buildBlock() ) ) {
                return false;
            }
			$template->assign( 'block', $bresult );
            $block['content'] = $template->fetch( $tplName, $cacheid );
        } else {
            $xoopsLogger->addBlock( $xobject->getVar('name'), true, $bcachetime );
            $block['content'] = $template->fetch( $tplName, $cacheid );
        }
        return $block;
	}
	
	
}
