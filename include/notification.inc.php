<?php
// $Id: notification.inc.php,v 1.1 2007/12/28 01:31:30 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

//---------------------------------------------------------
// function
//---------------------------------------------------------
function xlang_notify_iteminfo( $category, $item_id )
{
	global $xoopsModule, $xoopsModuleConfig, $xoopsConfig, $xoopsDB;

	$DIRNAME = 'xlang';

	$table_group = $xoopsDB->prefix( 'xlang_group ');

	if ( empty($xoopsModule) || $xoopsModule->getVar('dirname') != $DIRNAME ) 
	{
		$module_handler =& xoops_gethandler('module');
		$module =& $module_handler->getByDirname( $DIRNAME );

		$config_handler =& xoops_gethandler('config');
		$config =& $config_handler->getConfigsByCat( 0, $module->getVar('mid') );
	}
	else 
	{
		$module =& $xoopsModule;
		$config =& $xoopsModuleConfig;
	}

	if ( $category == 'dirname' ) 
	{
		$sql = 'SELECT * FROM '.$table_group.' WHERE id='.$item_id;
		$row = $xoopsDB->fetchArray( $xoopsDB->query($sql) );
		if ( is_array($row) )
		{
			$item = array();
			$item['name'] = $row['dirname'];
			$item['url']  = XOOPS_URL.'/modules/xlang/index.php?dir_id='.$item_id;
			return $item;
		}
	}

	if ( $category == 'language' ) 
	{
		$sql = 'SELECT * FROM '.$table_group.' WHERE id='.$item_id;
		$row = $xoopsDB->fetchArray( $xoopsDB->query($sql) );
		if ( is_array($row) )
		{
			$item = array();
			$item['name'] = $row['dirname'] .' > '. $row['language'];
			$item['url']  = XOOPS_URL.'/modules/xlang/index.php?lang_id='.$item_id;
			return $item;
		}
	}

	$item = array();
	$item['name'] = '';
	$item['url']  = '';

	return $item;

}

?>