<?php
// $Id: error.php,v 1.3 2007/12/19 17:10:24 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

//=========================================================
// class xlang_error
//=========================================================
class xlang_error
{
	var $_errors = array();

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_error()
{
	// dummy
}

function &getInstance()
{
	static $instance;
	if (!isset($instance)) 
	{
		$instance = new xlang_error();
	}
	return $instance;
}

//---------------------------------------------------------
// error
//---------------------------------------------------------
function return_code()
{
	if ( count($this->_errors) )
	{	return false;	}
	return true;
}

function has_error()
{
	if ( count($this->_errors) )
	{	return true;	}
	return false;
}

function clear_errors()
{
	$this->_errors = array();
}

function &get_errors()
{
	return $this->_errors;
}

function get_format_error( $flag_sanitize=true, $flag_highlight=true )
{
	$val = '';
	foreach (  $this->_errors as $msg )
	{
		if ( $flag_sanitize ) 
		{
			$msg = $this->sanitize($msg);
		}
		$val .= $msg . "<br />\n";
	}
	if ( $flag_highlight ) 
	{
		$val = $this->highlight($val);
	}
	return $val;
}

function set_error( $msg )
{
	if ( is_array($msg) )
	{
		foreach ( $msg as $m )
		{
			$this->_errors[] = $m;
		}
	}
	else
	{
		$this->_errors[] = $msg;
	}
}

//---------------------------------------------------------
// utility
//---------------------------------------------------------
function sanitize( $str )
{
	return htmlspecialchars( $str, ENT_QUOTES );
}

function highlight( $str )
{
	$val = '<span style="color:#ff0000;">'. $str .'</span>';
	return $val;
}

function shorten_strings( $str, $length )
{
	if ( strlen($str) > $length )
	{
		$str = xlang_substr( $str, 0, $length ).' ...';
	}
	return $str;
}

function shorten_strings_with_nl2br( $str, $length )
{
	$text = nl2br( $this->sanitize( $this->shorten_strings( $str, $length ) ) );
	return $text;
}

//----- class end -----
}

?>