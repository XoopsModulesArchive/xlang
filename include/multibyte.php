<?php
// $Id: multibyte.php,v 1.2 2007/12/28 01:28:02 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

function xlang_set_internal_encoding()
{
	$encoding = xlang_iconv_get_encoding( 'internal_encoding' );
	$ret = xlang_iconv_set_encoding( 'internal_encoding', _CHARSET );
	if ( $ret === false )
	{
		xlang_iconv_set_encoding( 'internal_encoding', $encoding );
	}

	$encoding = xlang_mb_internal_encoding();
	$ret = xlang_mb_internal_encoding( _CHARSET );
	if ( $ret === false )
	{
		xlang_mb_internal_encoding( $encoding );
	}
}

function xlang_iconv_get_encoding( $type )
{
	if ( function_exists('iconv_get_encoding') ) 
	{
		return iconv_get_encoding( $type );
	}
}

function xlang_iconv_set_encoding( $type, $charset )
{
	if ( function_exists('iconv_set_encoding') ) 
	{
		return iconv_set_encoding( $type, $charset );
	}
}

function xlang_mb_internal_encoding( $encoding=null )
{
	if ( function_exists('mb_internal_encoding') ) 
	{
		if ( $encoding ) 
		{
			return mb_internal_encoding( $encoding );
		}
		else 
		{
			return mb_internal_encoding();
		}
	}
}

function xlang_http_output()
{
	if ( function_exists('mb_http_output') ) 
	{	mb_http_output( 'pass' );	}
}

function xlang_exists_convert_encoding()
{
	if ( function_exists('iconv') ) 
	{	return true;	}

	if ( function_exists('mb_convert_encoding') ) 
	{	return true;	}

	return false;
}

function xlang_convert_encoding( $str, $to, $from )
{
	if ( $to == $from ) 
	{	return $str;	}

	if ( function_exists('iconv') ) 
	{	return iconv( $from, $to.'//IGNORE' , $str );	}

	if ( function_exists('mb_convert_encoding') ) 
	{	return mb_convert_encoding( $str, $from, $to );	}

	return $str;
}

function xlang_substr( $str, $start, $length )
{
	if ( function_exists('iconv_substr') )
	{	return iconv_substr( $str, $start, $length );	}

	if ( function_exists('mb_strcut') )
	{	return mb_strcut( $str, $start, $length );	}

	return substr( $str, $start, $length );
}

?>