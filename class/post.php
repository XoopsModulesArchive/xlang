<?php
// $Id: post.php,v 1.3 2007/12/28 07:39:15 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

//=========================================================
// class xlang_post
//=========================================================
class xlang_post
{

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_post()
{
	// dummy
}

function &getInstance()
{
	static $instance;
	if (!isset($instance)) 
	{
		$instance = new xlang_post();
	}
	return $instance;
}

//---------------------------------------------------------
// function
//---------------------------------------------------------
function &get_post( $key )
{
	$str = isset( $_POST[ $key ] ) ? $_POST[ $key ] : null;
	return $this->_strip_slashes_gpc( $str );
}

function &get_get( $key )
{
	$str = isset( $_GET[ $key ] ) ? $_GET[ $key ] : null;
	return $this->_strip_slashes_gpc( $str );
}

function &get_post_get( $key )
{
	$str = null;
	if (     isset( $_POST[ $key ] ) ) { $str = $_POST[ $key ]; }
	elseif ( isset( $_GET[ $key ] ) )  { $str = $_GET[ $key ]; }
	return $this->_strip_slashes_gpc( $str );
}

function &_strip_slashes_gpc( $str )
{
	if ( !get_magic_quotes_gpc()  )
	{	return $str;	}

	if ( !is_array( $str ) )
	{
		$ret = stripslashes( $str );
		return $ret;
	}

	$arr = array();
	foreach ( $str as $k => $v )
	{
		$arr[ $k ] = stripslashes( $v );
	}
	return $arr;
}

// --- class end ---
}

?>