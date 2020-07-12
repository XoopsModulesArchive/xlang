<?php
// $Id: admin_header.php,v 1.2 2007/12/31 10:51:46 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

include '../../../include/cp_header.php';

$XOOPS_LANGUAGE = $xoopsConfig['language'];

//---------------------------------------------------------
// xlang
//---------------------------------------------------------
if( !defined('XLANG_DIRNAME') )
{
	define('XLANG_DIRNAME', $xoopsModule->dirname() );
}

if( !defined('XLANG_ROOT_PATH') )
{
	define('XLANG_ROOT_PATH', XOOPS_ROOT_PATH.'/modules/'.XLANG_DIRNAME );
}

if( !defined('XLANG_URL') )
{
	define('XLANG_URL', XOOPS_URL.'/modules/'.XLANG_DIRNAME );
}

if( !defined('XLANG_ADMIN_URL') )
{
	define('XLANG_ADMIN_URL', XOOPS_URL.'/modules/'.XLANG_DIRNAME. '/admin' );
}

if( !defined('XLANG_TIME_START') )
{
	list($usec, $sec) = explode(" ",microtime()); 
	$time = floatval($sec) + floatval($usec); 
	define('XLANG_TIME_START', $time );
}

include_once XLANG_ROOT_PATH.'/include/constant.php';
include_once XLANG_ROOT_PATH.'/include/multibyte.php';
include_once XLANG_ROOT_PATH.'/include/gtickets.php';
include_once XLANG_ROOT_PATH.'/class/pagenavi.php';
include_once XLANG_ROOT_PATH.'/class/error.php';
include_once XLANG_ROOT_PATH.'/class/dir.php';
include_once XLANG_ROOT_PATH.'/class/post.php';
include_once XLANG_ROOT_PATH.'/class/charset_file.php';
include_once XLANG_ROOT_PATH.'/class/language_file.php';
include_once XLANG_ROOT_PATH.'/class/option_file.php';
include_once XLANG_ROOT_PATH.'/class/token.php';
include_once XLANG_ROOT_PATH.'/class/form.php';
include_once XLANG_ROOT_PATH.'/class/handler.php';
include_once XLANG_ROOT_PATH.'/class/group_handler.php';
include_once XLANG_ROOT_PATH.'/class/file_handler.php';
include_once XLANG_ROOT_PATH.'/class/word_handler.php';
include_once XLANG_ROOT_PATH.'/class/mail_handler.php';
include_once XLANG_ROOT_PATH.'/class/template_handler.php';
include_once XLANG_ROOT_PATH.'/class/log_handler.php';
include_once XLANG_ROOT_PATH.'/class/file_group_handler.php';
include_once XLANG_ROOT_PATH.'/class/word_group_handler.php';
include_once XLANG_ROOT_PATH.'/class/mail_group_handler.php';
include_once XLANG_ROOT_PATH.'/class/template_group_handler.php';
include_once XLANG_ROOT_PATH.'/class/log_group_handler.php';
include_once XLANG_ROOT_PATH.'/class/manage.php';

if ( file_exists( XLANG_ROOT_PATH.'/language/'.$XOOPS_LANGUAGE.'/modinfo.php') ) 
{
	include_once XLANG_ROOT_PATH.'/language/'.$XOOPS_LANGUAGE.'/modinfo.php';
}
else
{
	include_once XLANG_ROOT_PATH.'/language/english/modinfo.php';
}

if ( file_exists( XLANG_ROOT_PATH.'/language/'.$XOOPS_LANGUAGE.'/main.php') ) 
{
	include_once XLANG_ROOT_PATH.'/language/'.$XOOPS_LANGUAGE.'/main.php';
}
else
{
	include_once XLANG_ROOT_PATH.'/language/english/main.php';
}

?>