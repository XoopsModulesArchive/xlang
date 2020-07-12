<?php
// $Id: language_file.php,v 1.4 2008/12/21 20:49:33 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

//---------------------------------------------------------
// 2008-12-22 K.OHWADA
// support D3 modules
//---------------------------------------------------------

//=========================================================
// class xlang_language_file
//=========================================================
class xlang_language_file extends xlang_dir
{

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_language_file()
{
	$this->xlang_dir();
}

function &getInstance()
{
	static $instance;
	if (!isset($instance)) 
	{
		$instance = new xlang_language_file();
	}
	return $instance;
}

//---------------------------------------------------------
// filename
//---------------------------------------------------------
function get_skip_filename( $dirname )
{
	$file = XOOPS_ROOT_PATH .'/modules/xlang/options/'. $dirname .'/skip_files.php';
	return $file;
}

function get_language_filename_by_path( $path, $dirname, $language, $file )
{
	$file = $this->_get_language_dir( $path, $dirname, $language ) .'/'. $file;
	return $file;
}

function get_mail_filename_by_path( $path, $dirname, $language, $file )
{
	$file = $this->_get_mail_dir( $path, $dirname, $language ) .'/'. $file;
	return $file;
}

function exist_language_filename_by_path( $path, $dirname, $language, $file )
{
	if ( file_exists( $this->get_language_filename_by_path( $path, $dirname, $language, $file ) ) )
	{	return true;	}
	return false;
}

function exist_mail_filename_by_path( $path, $dirname, $language, $file )
{
	if ( file_exists( $this->get_mail_filename_by_path( $path, $dirname, $language, $file ) ) )
	{	return true;	}
	return false;
}

//---------------------------------------------------------
// read
//---------------------------------------------------------
function &read_constants_by_path( $path, $dirname, $language, $file )
{
	$arr   = array();
	$false = false;
	$system_arr = get_defined_constants();

	$filename = $this->get_language_filename_by_path( $path, $dirname, $language, $file );
	if ( !$this->file_exists( $filename ) )
	{	return false;	}

// for D3 modules
	$mydirname = $dirname;	// pico
	$GLOBALS['MY_DIRNAME'] = $dirname;	// webphoto

	include_once $filename;

	$file_arr = get_defined_constants();

	foreach( $file_arr as $k => $v )
	{
		if ( $k == 'NULL' ) continue;

// can not use isset()
// because some constants are null
		if ( array_key_exists( $k, $system_arr ) ) continue;

		$arr[ $k ] = $v;
	}

	return $arr;
}

//---------------------------------------------------------
// read
//---------------------------------------------------------
function &get_root_module_dirs()
{
	return $this->get_dirs_from_dir(
		$this->_get_root_module_base_dir(), true );
}

function get_count_language_files_by_path_dirname( $path, $dirname, $language )
{
	$files =& $this->get_language_files_by_path_dirname( $path, $dirname, $language );
	if ( is_array($files) )
	{
		return count($files);
	}
	return 0;
}

function &get_language_dirs_by_path_dirname( $path, $dirname )
{
	return $this->get_dirs_from_dir(
		$this->_get_language_base_dir( $path, $dirname ), true );
}

function &get_language_files_by_path_dirname( $path, $dirname, $language )
{
	$files =& $this->get_files_from_dir(
		$this->_get_language_dir( $path, $dirname, $language ), 'php', true );
	if ( is_array($files) )
	{	return $files;	}

	$arr = array();
	return $arr;
}

function read_language_file_by_path( $path, $dirname, $language, $file )
{
	return $this->read_file_by_filename( 
		$this->get_language_filename_by_path( $path, $dirname, $language, $file ) );
}

function &get_mail_files_by_path_dirname( $path, $dirname, $language )
{
	$files =& $this->get_files_from_dir(
		$this->_get_mail_dir( $path, $dirname, $language ), 'tpl', true );
	if ( is_array($files) )
	{	return $files;	}

	$arr = array();
	return $arr;
}

function read_mail_file_by_path( $path, $dirname, $language, $file )
{
	return $this->read_file_by_filename(
		$this->get_mail_filename_by_path( $path, $dirname, $language, $file ) );
}

//---------------------------------------------------------
// private
//---------------------------------------------------------
function _get_root_module_base_dir()
{
	$dir = XOOPS_ROOT_PATH .'/modules';
	return $dir;
}

function _get_language_base_dir( $path, $dirname )
{
	if ( empty($path) )
	{
		$path = XOOPS_ROOT_PATH;
	}

	$dir = $path .'/modules/'. $dirname .'/language';
	return $dir;
}

function _get_language_dir( $path, $dirname, $language )
{
	if ( empty($path) )
	{
		$path = XOOPS_ROOT_PATH;
	}

	$dir = $path .'/modules/'. $dirname .'/language/'. $language;
	return $dir;
}

function _get_mail_dir( $path, $dirname, $language )
{
	if ( empty($path) )
	{
		$path = XOOPS_ROOT_PATH;
	}

	$dir = $path .'/modules/'. $dirname .'/language/'. $language .'/mail_template';
	return $dir;
}

//----- class end -----
}

?>