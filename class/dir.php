<?php
// $Id: dir.php,v 1.1 2007/12/17 12:01:49 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

//=========================================================
// class xlang_dir
//=========================================================
class xlang_dir extends xlang_error
{

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_dir()
{
	$this->xlang_error();
}

function &getInstance()
{
	static $instance;
	if (!isset($instance)) 
	{
		$instance = new xlang_dir();
	}
	return $instance;
}

//---------------------------------------------------------
// directory class
//---------------------------------------------------------
function &get_files_from_dir( $dir, $ext=null, $id_as_key=false )
{
	return $this->_read_dir( $dir, false, true, $ext, $id_as_key );
}

function &get_dirs_from_dir( $dir, $id_as_key=false )
{
	return $this->_read_dir( $dir, true, false, null, $id_as_key );
}

function &_read_dir( $dir, $flag_dir=false, $flag_file=false, $ext=null, $id_as_key=false )
{
	$arr   = array();
	$false = false;

	if ( !is_dir( $dir ) )
	{
		$msg = 'not dir: '. $dir;
		$this->set_error( $msg );
		return $false;
	}

	$dh = opendir( $dir );
	if ( !$dh )
	{
		$msg = 'cannot open dir: '. $dir;
		$this->set_error( $msg );
		return $false;
	}

	$pattern = "/\.". preg_quote( $ext ) ."$/";

	while ( false !== ($file = readdir( $dh )) )
	{
		$file_full = $dir."/".$file;
		$flag      = false;

		if ( $flag_dir && is_dir($file_full) )
		{
			if (( $file != '.' )&&( $file != '..' )&&( $file != 'CVS' ))
			{	$flag = true;	}
		}

		if ( $flag_file && is_file($file_full) )
		{
			if ( $ext && preg_match( $pattern, $file ) )
			{	$flag = true;	}
			if ( $ext === null )
			{	$flag = true;	}
		}

		if ( $flag )
		{
			if ( $id_as_key ) 
			{
				$arr[ $file ] = $file;
			}
			else 
			{
				$arr[] = $file;
			}
		}
	}

	closedir( $dh );

	return $arr;
}

//---------------------------------------------------------
// file class
//---------------------------------------------------------
function read_file_by_filename( $filename )
{
	if ( !$this->file_exists( $filename ) )
	{	return false;	}

	return file_get_contents( $filename );
}

function file_exists( $filename )
{
	if ( !file_exists( $filename ) )
	{
		$msg = "not exist file : ". $filename;
		$this->set_error( $msg );
		return false;
	}
	return true;
}

//----- class end -----
}

?>