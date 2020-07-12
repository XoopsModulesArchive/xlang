<?php
// $Id: charset_file.php,v 1.3 2007/12/28 05:06:44 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

//=========================================================
// class xlang_charset_file
//=========================================================
class xlang_charset_file
{
	var $_xlang_mysql_charset_array = array();
	var $_xlang_charset_array       = array();
	var $_xlang_my_language         = null;
	var $_xlang_mysql_charset_force = false;
	var $_xlang_convert_encoding    = false;
	var $_my_mysql_charset          = null;
	var $_my_charset                = null;
	var $_flag_convert              = false;

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_charset_file()
{
	// dummy;
}

function &getInstance()
{
	static $instance;
	if (!isset($instance)) 
	{
		$instance = new xlang_charset_file();
	}
	return $instance;
}

//---------------------------------------------------------
// filename
//---------------------------------------------------------
function get_charset_filename()
{
	$file = XOOPS_ROOT_PATH .'/modules/xlang/include/charset.php';
	return $file;
}

function exist_charset_file()
{
	if ( file_exists( $this->get_charset_filename() ) )
	{	return true;	}
	return false;
}

//---------------------------------------------------------
// read
//---------------------------------------------------------
function read_charset_file()
{
	$this->include_charset_file();

	if ( xlang_exists_convert_encoding() )
	{
		$this->_flag_convert = $this->_xlang_convert_encoding;
	}

}

function include_charset_file()
{
	$filename = $this->get_charset_filename();
	if ( !file_exists( $filename ) )
	{	return false;	}

	include_once $this->get_charset_filename();

	if ( isset( $XLANG_MY_LANGUAGE ) )
	{
		$this->_xlang_my_language = $XLANG_MY_LANGUAGE;
	}

	if ( isset( $XLANG_MYSQL_CHARSET_ARRAY ) )
	{
		$this->_xlang_mysql_charset_array = $XLANG_MYSQL_CHARSET_ARRAY;
	}

	if ( isset( $XLANG_CHARSET_ARRAY ) )
	{
		$this->_xlang_charset_array = $XLANG_CHARSET_ARRAY;
	}

	if ( isset( $XLANG_MYSQL_CHARSET_FORCE ) )
	{
		$this->_xlang_mysql_charset_force = $XLANG_MYSQL_CHARSET_FORCE;
	}

	if ( isset( $XLANG_CONVERT_ENCODING ) )
	{
		$this->_xlang_convert_encoding = $XLANG_CONVERT_ENCODING;
	}

	$this->_my_mysql_charset = $this->get_mysql_charset_by_language( $this->_xlang_my_language );
	$this->_my_charset       = $this->get_charset_by_language(       $this->_xlang_my_language );

}

function get_mysql_charset_by_language( $language )
{
	$val = null;
	if ( isset( $this->_xlang_mysql_charset_array[ $language ] ) )
	{
		$val = $this->_xlang_mysql_charset_array[ $language ];
	}
	return $val;
}

function get_charset_by_language( $language )
{
	$val = null;
	if ( isset( $this->_xlang_charset_array[ $language ] ) )
	{
		$val = $this->_xlang_charset_array[ $language ];
	}
	return $val;
}

//----- class end -----
}

?>