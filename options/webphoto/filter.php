<?php
// $Id: filter.php,v 1.1 2008/12/21 20:49:33 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

function xlang_filter_webphoto_modinfo_php_key( $str )
{
	$key = '"_MI_WEBPHOTO_' ;
	$val = '$constpref."' ;

	$str = str_replace( $key, $val, $str );
	return $str;
}

function xlang_filter_webphoto_blocks_php_key( $str )
{
	$key = '"_BL_WEBPHOTO_' ;
	$val = '$constpref."' ;

	$str = str_replace( $key, $val, $str );
	return $str;
}

?>