<?php
// $Id: option_file.php,v 1.2 2008/12/21 20:49:33 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

//---------------------------------------------------------
// 2008-12-22 K.OHWADA
// support D3 modules
//---------------------------------------------------------

//=========================================================
// class xlang_option_file
//=========================================================
class xlang_option_file extends xlang_dir
{

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_option_file()
{
	$this->xlang_dir();
}

function &getInstance()
{
	static $instance;
	if (!isset($instance)) 
	{
		$instance = new xlang_option_file();
	}
	return $instance;
}

//---------------------------------------------------------
// template default
//---------------------------------------------------------
function read_template_default_file()
{
	return $this->read_file_by_filename(
		$this->get_template_default_filename() );
}

function get_template_default_filename()
{
	$file = $this->_get_option_base_dir() .'/template_default.tpl';
	return $file;
}

//---------------------------------------------------------
// template option
//---------------------------------------------------------
function get_count_template_option_files_by_dirname( $dirname )
{
	$files =& $this->get_template_option_files_by_dirname( $dirname );
	if ( is_array($files) )
	{
		return count($files);
	}
	return 0;
}

function &get_template_option_files_by_dirname( $dirname )
{
	$arr = array();

	$template_arr =& $this->get_files_from_dir(
		$this->_get_option_dir( $dirname ), 'tpl' );

	if ( is_array($template_arr) && count($template_arr) )
	{
		foreach ( $template_arr as $template )
		{
			$file = str_replace( '.tpl', '', $template );
			$arr[] = array(
				'file'     => $file,
				'template' => $template,
			);
		}
	}
	return $arr;
}

function read_template_option_file( $dirname, $file )
{
	return $this->read_file_by_filename( 
		$this->get_template_option_filename( $dirname, $file ) );
}

function get_template_option_filename( $dirname, $file, $ext=null )
{
	$file = $this->_get_option_dir( $dirname ) .'/'. $file;
	if ( $ext )
	{
		$file .= '.' . $ext;
	}
	return $file;
}

//---------------------------------------------------------
// filter option
//---------------------------------------------------------
function get_include_filter_option_funcname( 
	$dirname, $file, $default='filter.php', $flag=true )
{
	$filename     = $this->get_filter_option_filename( $dirname, $default );
	$funcname     = $this->get_filter_option_funcname( $dirname, $file, '',    $flag );
	$funcname_key = $this->get_filter_option_funcname( $dirname, $file, 'key', $flag );

	$filter     = null ;
	$filter_key = null ;

	if ( file_exists( $filename ) ) {
		include_once $filename; 
		if ( function_exists( $funcname ) ) {
			$filter = $funcname ;
		}
		if ( function_exists( $funcname_key ) ) {
			$filter_key = $funcname_key ;
		}
	}

	return array( $filter, $filter_key );
}

function get_filter_option_filename( $dirname, $default='filter.php' )
{
	$file = $this->_get_option_dir( $dirname ) .'/'. $default;
	return $file;
}

function get_filter_option_funcname( $dirname, $file, $extra=null, $flag=true )
{
	$func = 'xlang_filter_'. $dirname .'_'. $file;
	if ( $extra ) {
		$func .= '_'. $extra ;
	}
	if ( $flag ) {
		$func = str_replace( '.', '_', $func );
		$func = str_replace( '-', '_', $func );
	}
	return $func;
}

//---------------------------------------------------------
// private
//---------------------------------------------------------
function _get_option_base_dir()
{
	$dir = XOOPS_ROOT_PATH .'/modules/xlang/options';
	return $dir;
}

function _get_option_dir( $dirname )
{
	$dir = XOOPS_ROOT_PATH .'/modules/xlang/options/'. $dirname;
	return $dir;
}

//----- class end -----
}

?>