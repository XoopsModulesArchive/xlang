<?php
// $Id: import.php,v 1.6 2008/12/21 20:49:33 ohwada Exp $

//---------------------------------------------------------
// 2008-12-22 K.OHWADA
// support D3 modules
//---------------------------------------------------------

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

//---------------------------------------------------------
// this program use 
// original session control and
// original databese mapper
// and not include common.php
//---------------------------------------------------------

global $xoopsOption;
$xoopsOption['nocommon'] = 1;

include '../../../mainfile.php';

// start execution time
global $XLANG_TIME_START;
list($usec, $sec) = explode(" ",microtime()); 
$XLANG_TIME_START = floatval($sec) + floatval($usec); 

include_once XOOPS_ROOT_PATH.'/modules/xlang/include/constant.php';
include_once XOOPS_ROOT_PATH.'/modules/xlang/include/multibyte.php';
include_once XOOPS_ROOT_PATH.'/modules/xlang/class/error.php';
include_once XOOPS_ROOT_PATH.'/modules/xlang/class/dir.php';
include_once XOOPS_ROOT_PATH.'/modules/xlang/class/language_file.php';
include_once XOOPS_ROOT_PATH.'/modules/xlang/class/option_file.php';
include_once XOOPS_ROOT_PATH.'/modules/xlang/class/charset_file.php';
include_once XOOPS_ROOT_PATH.'/modules/xlang/class/post.php';
include_once XOOPS_ROOT_PATH.'/modules/xlang/class/token.php';
include_once XOOPS_ROOT_PATH.'/modules/xlang/class/xoops_database.php';
include_once XOOPS_ROOT_PATH.'/modules/xlang/class/xoops_mysql_database.php';
include_once XOOPS_ROOT_PATH.'/modules/xlang/class/handler.php';
include_once XOOPS_ROOT_PATH.'/modules/xlang/class/xoops_session_handler.php';
include_once XOOPS_ROOT_PATH.'/modules/xlang/class/word_handler.php';
include_once XOOPS_ROOT_PATH.'/modules/xlang/class/file_handler.php';
include_once XOOPS_ROOT_PATH.'/modules/xlang/class/mail_handler.php';
include_once XOOPS_ROOT_PATH.'/modules/xlang/class/template_handler.php';
include_once XOOPS_ROOT_PATH.'/modules/xlang/class/log_handler.php';
include_once XOOPS_ROOT_PATH.'/modules/xlang/class/group_handler.php';
include_once XOOPS_ROOT_PATH.'/modules/xlang/class/word_group_handler.php';
include_once XOOPS_ROOT_PATH.'/modules/xlang/class/file_group_handler.php';
include_once XOOPS_ROOT_PATH.'/modules/xlang/class/mail_group_handler.php';
include_once XOOPS_ROOT_PATH.'/modules/xlang/class/template_group_handler.php';
include_once XOOPS_ROOT_PATH.'/modules/xlang/class/log_group_handler.php';

//=========================================================
// class xlang_import
//=========================================================
class xlang_import extends xlang_error
{
	var $_word_group_handler;
	var $_file_group_handler;
	var $_mail_group_handler;
	var $_template_group_handler;

	var $_xlang_post;
	var $_xlang_token;
	var $_charset_file;
	var $_language_file;
	var $_option_file;

	var $_post_path;
	var $_post_dirname;
	var $_post_language;
	var $_post_file;
	var $_msgs         = array();
	var $_error_flag   = false;
	var $_skip_files   = null;
	var $_flag_convert = false;

	var $_XOOPS_UID           = null;
	var $_MY_LANGUAGE         = null;
	var $_CONVERT_ENCODING    = false;
	var $_MYSQL_CHARSET_FORCE = false;
	var $_MYSQL_CHARSET       = null;
	var $_CHARSET             = 'UTF-8';
	var $_ENCODING_FROM       = 'UTF-8';

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_import()
{
	$this->xlang_error();

	$this->_xlang_post    =& xlang_post::getInstance();
	$this->_xlang_token   =& xlang_token::getInstance();
	$this->_charset_file  =& xlang_charset_file::getInstance();
	$this->_language_file =& xlang_language_file::getInstance();
	$this->_option_file   =& xlang_option_file::getInstance();

}

//---------------------------------------------------------
// public
//---------------------------------------------------------
function main()
{
	error_reporting(E_ALL);
	xlang_http_output();
	$this->_execute();

	$this->_read_config_file();
	echo $this->_build_header();
	echo $this->_get_msg();
	echo $this->_build_footer();
}

function _execute()
{
	$this->_xlang_token->original_session_start();

	if ( !$this->_xlang_token->check_original_token() )
	{
		$this->_set_msg( '<h4 style="color:#ff0000;">Token Error</h4>' );
		return;
	}

	$this->_XOOPS_UID = $this->_xlang_token->get_original_xoops_uid();

	$this->_post_path     =& $this->_xlang_post->get_post( 'path' );
	$this->_post_dirname  =& $this->_xlang_post->get_post( 'dirname' );
	$this->_post_language =& $this->_xlang_post->get_post( 'language' );
	$this->_post_file     =& $this->_xlang_post->get_post( 'file' );

	$ret = $this->_init_config( $this->_post_dirname, $this->_post_language );
	if ( !$ret )
	{
		$this->_set_msg( '<h4 style="color:#ff0000;">Config Error</h4>' );
		return;
	}

	$this->_init_handler();

	switch ( $this->_xlang_post->get_post( 'op' ) )
	{
		case 'language':
			$this->_import_language( $this->_post_path, $this->_post_dirname, $this->_post_language );
			break;

		case 'file':
			$this->_import_file( $this->_post_path, $this->_post_dirname, $this->_post_language, $this->_post_file );
			break;

		case 'mail':
			$this->_import_mail( $this->_post_path, $this->_post_dirname, $this->_post_language, $this->_post_file );
			break;

		case 'template_dirname':
			$this->_import_template_dirname( $this->_post_dirname );
			break;

		case 'template':
			$this->_import_template( $this->_post_dirname, $this->_post_file );
			break;

		default:
			$this->_set_msg( '<h4>No Action</h4>' );
			return;
			break;
	}

	if ( $this->_error_flag )
	{
		$this->_set_msg( '<h4 style="color:#ff0000;">Error</h4>' );
		return;
	}

	$this->_set_msg( '<h4>Finish</h4>' );
	return;
}

function _import_language( $path, $dirname, $language )
{
	$file_arr =& $this->_language_file->get_language_files_by_path_dirname( $path, $dirname, $language );
	$mail_arr =& $this->_language_file->get_mail_files_by_path_dirname(     $path, $dirname, $language );

	if ( is_array($file_arr) && count($file_arr) )
	{
		foreach ( $file_arr as $file )
		{
			$this->_excute_file( $path, $dirname, $language, $file );
		}
	}

	if ( is_array($mail_arr) && count($mail_arr) )
	{
		foreach ( $mail_arr as $mail )
		{
			$this->_excute_mail( $path, $dirname, $language, $mail );
		}
	}

	return;
}

function _import_file( $path, $dirname, $language, $file_in )
{
	if ( is_array( $file_in ) )
	{
		foreach ( $file_in as $file )
		{
			$this->_excute_file( $path, $dirname, $language, $file );
		}
	}
	else
	{
		$this->_excute_file( $path, $dirname, $language, $file_in );
	}

	return;
}

function _import_mail( $path, $dirname, $language, $file_in )
{
	if ( is_array( $file_in ) )
	{
		foreach ( $file_in as $file )
		{
			$this->_excute_mail( $path, $dirname, $language, $file );
		}
	}
	else
	{
		$this->_excute_mail( $path, $dirname, $language, $file_in );
	}

	return;
}

function _import_template_dirname( $dirname )
{
	$template_arr =& $this->_option_file->get_template_option_files_by_dirname( $dirname );

	if ( is_array($template_arr) && count($template_arr) )
	{
		foreach ( $template_arr as $template )
		{
			$this->_excute_template( $dirname, $template['file'] );
		}
	}

	return;
}

function _import_template( $dirname, $file_in )
{
	if ( is_array( $file_in ) )
	{
		foreach ( $file_in as $file )
		{
			$this->_excute_template( $dirname, $file );
		}
	}
	else
	{
		$this->_excute_template( $dirname, $file_in );
	}

	return;
}

function _excute_file( $path, $dirname, $language, $file )
{
	if ( isset( $this->_skip_files ) && is_array( $this->_skip_files ) )
	{
		if ( in_array( $file, $this->_skip_files ) )
		{
			$this->_set_msg( "skip: $path -> $dirname -> $language -> $file" );
			return;
		}
	}

// word table
	$count_insert = 0;
	$count_update = 0;

	$this->_set_msg( "import: $path -> $dirname -> $language -> $file" );

	$word_arr =& $this->_language_file->read_constants_by_path( $path, $dirname, $language, $file );
	if ( !is_array($word_arr) )
	{
		$this->_set_msg( $this->_language_file->get_format_error() );
		$this->_set_error_flag();
		return;
	}
	elseif ( count($word_arr) == 0 )
	{
		$this->_set_msg( $this->highlight( 'no word' ) );
		return;
	}

	foreach ( $word_arr as $word => $constant )
	{
		$ret = $this->_word_group_handler->add_with_exist_check(
			$dirname, $language, $file, $word, $this->_convert_encoding( $constant ), null );

		if ( $ret == 1 )
		{	$count_insert ++;	}
		if ( $ret == 2 )
		{	$count_update ++;	}

		if ( !$ret )
		{
			$this->_set_msg( $this->_word_group_handler->get_format_error() );
			$this->_set_error_flag();
		}
	}

	$this->_set_msg( 'insert : '. $count_insert .', update : '. $count_update );

// file table
	$content = $this->_language_file->read_language_file_by_path( $path, $dirname, $language, $file );
	if ( !$content )
	{
		$this->_set_msg( $this->_language_file->get_format_error() );
		$this->_set_error_flag();
		return;
	}

	$ret = $this->_file_group_handler->add_with_exist_check(
		$dirname, $language, $file, $this->_convert_encoding( $content ), null );
	if ( !$ret )
	{
		$this->_set_msg( $this->_file_group_handler->get_format_error() );
		$this->_set_error_flag();
	}

	return;
}


function _excute_mail( $path, $dirname, $language, $file )
{
	$this->_set_msg( "import: $path -> $dirname -> $language -> $file" );

	$content = $this->_language_file->read_mail_file_by_path( $path, $dirname, $language, $file );
	if ( !$content )
	{
		$this->_set_msg( $this->_language_file->get_format_error() );
		$this->_set_error_flag();
		return false;
	}

	$ret = $this->_mail_group_handler->add_with_exist_check(
		$dirname, $language, $file, $this->_convert_encoding( $content ), null );
	if ( !$ret )
	{
		$this->_set_msg( $this->_mail_group_handler->get_format_error() );
		$this->_set_error_flag();
		return false;
	}

	return true;
}

function _excute_template( $dirname, $file )
{
	$template = $file.'.tpl';
	$this->_set_msg( "import: $dirname -> $template" );

// template table
	$content = $this->_option_file->read_template_option_file( $dirname, $template );
	if ( !$content )
	{
		$this->_set_msg( $this->_option_file->get_format_error() );
		$this->_set_error_flag();
		return;
	}

	$ret = $this->_template_group_handler->add_with_exist_check(
		$dirname, $file, $this->_convert_encoding( $content ) );
	if ( !$ret )
	{
		$this->_set_msg( $this->_file_group_handler->get_format_error() );
		$this->_set_error_flag();
	}

	return;
}

function _build_header()
{

	$dirname_s  = $this->sanitize( $this->_post_dirname );
	$language_s = $this->sanitize( $this->_post_language );
	$file_s     = $this->sanitize( $this->_post_file );

	$text  = "<html><head>\n";
	$text .= '<meta http-equiv="Content-Type" content="text/html; charset='. $this->_CHARSET .'" />'."\n";
	$text .= '<title>'. _MI_XLANG_NAME .' - '. _AM_XLANG_IMPORT_FILE .'</title>'."\n";
	$text .= "</head><body>\n";
	$text .= '<a href="index.php">'. _MI_XLANG_NAME . '</a> &gt;&gt; ';
	$text .= '<a href="index.php?dirname='. $dirname_s .'">'. $dirname_s .'</a> &gt;&gt; ';

	if ( $file_s )
	{
		$text .= '<a href="index.php?dirname='. $dirname_s .'&amp;language='. $language_s .'">';
		$text .=  $language_s .'</a> &gt;&gt; ';
		$text .= '<b>'. $file_s .'</b>';
	}
	else
	{
		$text .= '<b>'. $language_s .'</b>';
	}

	$text .= "<br /><br />\n";

	$text .= "<h4> System Configration </h4>\n";
	$text .= 'mysql charset : '. $this->_MYSQL_CHARSET ."<br />\n";
	$text .= 'charset : '.  $this->_CHARSET ."<br />\n";
	$text .= 'encoding from : '. $this->_ENCODING_FROM ."<br />\n";
	$text .= 'mysql charset force : '. intval( $this->_MYSQL_CHARSET_FORCE ) ."<br />\n";
	$text .= 'convert flag : '. intval( $this->_CONVERT_ENCODING ) ."<br />\n";
	$text .= 'uid : '. $this->_XOOPS_UID ."<br />\n";
	$text .= 'language : '. $this->_MY_LANGUAGE ."<br />\n";

	$text .= "<h4>". _AM_XLANG_IMPORT_FILE ."</h4>\n";
	return $text;
}

function _build_footer()
{
	$link = '';
	if ( isset( $_SERVER['HTTP_REFERER'] ) )
	{
		$link  = '<a href="'. $this->sanitize( $_SERVER['HTTP_REFERER'] ) .'">';
		$link .= 'GO back</a>'."<br/>\n";
	}

	$text  = "<hr />\n";
	$text .= '<a href="index.php">GOTO index</a>'."<br />\n";
	$text .= $link;
	$text .= $this->_build_execution_time();
	$text .= $this->_build_memory_usage();
	$text .= "</body></html>\n";
	return $text;
}

function _build_execution_time()
{
	global $XLANG_TIME_START;
	list($usec, $sec) = explode(" ",microtime()); 
	$time = floatval($sec) + floatval($usec)- $XLANG_TIME_START; 
	$exec = sprintf("%6.3f", $time);
	$text = 'execution time : '. $exec .' sec'."<br />\n";
	return $text;
}

function _build_memory_usage()
{
	if ( function_exists('memory_get_usage') )
	{
		$usage = sprintf("%6.3f",  memory_get_usage() / 1000000 );
		$text  = 'memory usage : '.$usage.' MB'."<br />\n";
		return $text;
	}
	return null;
}

//---------------------------------------------------------
// init config
//---------------------------------------------------------
function _init_config( $dirname, $language )
{
	$this->_charset_file->read_charset_file();

	$this->_MY_LANGUAGE         = $this->_charset_file->_xlang_my_language;
	$this->_CONVERT_ENCODING    = $this->_charset_file->_xlang_convert_encoding;
	$this->_MYSQL_CHARSET_FORCE = $this->_charset_file->_xlang_mysql_charset_force;
	$this->_MYSQL_CHARSET       = $this->_charset_file->_my_mysql_charset;
	$this->_flag_convert        = $this->_charset_file->_flag_convert;

	if ( empty($this->_MY_LANGUAGE) )
	{	return false;	}

	$charset = $this->_charset_file->_my_charset;
	if ( $charset )
	{
		$this->_CHARSET = $charset;
	}

	$charset_from = $this->_charset_file->get_charset_by_language( $language );
	if ( $charset_from )
	{
		$this->_ENCODING_FROM = $charset_from;
	}

	$skip_filename = $this->_language_file->get_skip_filename( $dirname );
	if ( file_exists( $skip_filename ) )
	{
		include_once $skip_filename;

		if ( isset( $XLANG_SKIP_FILES ) )
		{
			$this->_skip_files =& $XLANG_SKIP_FILES;
		}
	}

	return true;
}

function _init_handler()
{
	$this->_word_group_handler     =& xlang_word_group_handler::getInstance();
	$this->_file_group_handler     =& xlang_file_group_handler::getInstance();
	$this->_mail_group_handler     =& xlang_mail_group_handler::getInstance();
	$this->_template_group_handler =& xlang_template_group_handler::getInstance();

	if ( $this->_MYSQL_CHARSET )
	{
		$this->_word_group_handler->set_mysql_charset( $this->_MYSQL_CHARSET, $this->_MYSQL_CHARSET_FORCE );
	}

	$this->_word_group_handler->set_uid( $this->_XOOPS_UID );
	$this->_file_group_handler->set_uid( $this->_XOOPS_UID );
	$this->_mail_group_handler->set_uid( $this->_XOOPS_UID );

}

function _read_config_file()
{

// xlang modinfo.php
	if ( file_exists( XOOPS_ROOT_PATH.'/modules/xlang/language/'.$this->_MY_LANGUAGE.'/modinfo.php') ) 
	{
		include_once XOOPS_ROOT_PATH.'/modules/xlang/language/'.$this->_MY_LANGUAGE.'/modinfo.php';
	}
	else
	{
		include_once XOOPS_ROOT_PATH.'/modules/xlang/language/english/modinfo.php';
	}

// xlang admin.php
	if ( file_exists( XOOPS_ROOT_PATH.'/modules/xlang/language/'.$this->_MY_LANGUAGE.'/admin.php') ) 
	{
		include_once XOOPS_ROOT_PATH.'/modules/xlang/language/'.$this->_MY_LANGUAGE.'/admin.php';
	}
	else
	{
		include_once XOOPS_ROOT_PATH.'/modules/xlang/language/english/admin.php';
	}
}

//---------------------------------------------------------
// encoding
//---------------------------------------------------------
function _convert_encoding( $str )
{
	if ( !$this->_flag_convert )
	{	return $str;	}

	if ( $this->_CHARSET == $this->_ENCODING_FROM ) 
	{	return $str;	}

// use original, if can not convert
	$conv = xlang_convert_encoding( $str, $this->_CHARSET, $this->_ENCODING_FROM );
	if ( $conv )
	{	$str = $conv;	}

	return $str;
}

//---------------------------------------------------------
// message
//---------------------------------------------------------
function _set_msg( $msg )
{
	$this->_msgs[] = $msg;
}

function _get_msg()
{
	$val = '';
	foreach (  $this->_msgs as $msg )
	{
		$val .= $msg . "<br />\n";
	}
	return $val;
}

function _set_error_flag()
{
	$this->_error_flag = true;
}

// --- class end ---
}

//=========================================================
// main
//=========================================================
$import = new xlang_import();
$import->main();
exit();
// --- main end ---

?>