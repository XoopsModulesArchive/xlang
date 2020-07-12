<?php
// $Id: download.php,v 1.3 2007/12/28 01:28:01 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

include_once 'header.php';
include_once XOOPS_ROOT_PATH.'/class/template.php';

//=========================================================
// class xlang_download
//=========================================================
class xlang_download extends xlang_form
{
	var $_file_group_handler;
	var $_mail_group_handler;
	var $_charset_file;

	var $_param_error = null;
	var $_conv_error  = null;

	var $_flag_convert  = false;
	var $_charset_array = array();

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_download()
{
	$this->xlang_form();

	$this->_file_group_handler =& xlang_file_group_handler::getInstance();
	$this->_mail_group_handler =& xlang_mail_group_handler::getInstance();
	$this->_charset_file       =& xlang_charset_file::getInstance();

	$this->_init();
}

function _init()
{
	$this->_charset_file->read_charset_file();

	$this->_flag_convert  = $this->_charset_file->_flag_convert;
	$this->_charset_array = $this->_charset_file->_xlang_charset_array;
}

//---------------------------------------------------------
// post
//---------------------------------------------------------
function main()
{
	xlang_http_output();

	switch ( $this->_get_op() )
	{
		case 'download':
			$this->_download();
			break;

		case 'show':
		default:
			$this->_show_file();
			break;
	}
}

function _get_op()
{
	$op = $this->_xlang_post->get_get( 'op' );
	if ( $op )
	{	return $op;	}

	if ( !$this->_flag_convert )
	{	return 'download';	}

	return '';
}

//---------------------------------------------------------
// show
//---------------------------------------------------------
function _show_file()
{
	$tpl = new XoopsTpl();

	$row =& $this->_get_row();
	if ( !is_array($row) )
	{
		$this->_show_error();
		return false;
	}

	$dirname  = $row['dirname'];
	$language = $row['language'];
	$file     = $row['file'];
	$mail     = $row['mail'];

	$this->set_template_by_tpl_obj( $tpl, $dirname, $language, $file, null, $mail );

	$tpl->assign( 'module_name_s', $this->sanitize( $row['module_name'] ) );
	$tpl->assign( 'time',          intval( $row['time'] ) );
	$tpl->assign( 'charset_s',     $this->sanitize( $row['charset'] ) );
	$tpl->assign( 'content_s',     $this->sanitize( $row['content'] ) );
	$tpl->assign( 'conv_error_s',  $this->sanitize( $this->_conv_error ) );
	$tpl->assign( 'is_xoops_charset', $row['is_xoops_charset'] );
	$tpl->assign( 'has_convert',      $this->_flag_convert );
	$tpl->assign( 'charset_options',  $this->_get_charset_options() );

	$tpl->display( 'db:xlang_download.html' );

}

function _get_charset_options()
{
	$arr     = array();
	$options = array();

	if ( $this->_flag_convert )
	{
		foreach ( $this->_charset_array as $k => $v )
		{
			$arr[ $v ][] = $k;
		}

		foreach ( $arr as $k => $v )
		{
			$line = array(
				'value' => $this->sanitize( $k ),
				'name'  => $this->sanitize( $k .' : '. implode( ' ', $v ) ),
			);
			$options[] = $line;
		}
	}

	return $options;
}

//---------------------------------------------------------
// download
//---------------------------------------------------------
function _download()
{
	$row =& $this->_get_row();
	if ( !is_array($row) )
	{
		$this->_show_error();
		return false;
	}

	$charset  = $row['charset'];
	$filename = $row['filename'];
	$content  = $row['content'];
	$length   = strlen($content);

	header( "Content-Type: application/octet-stream" );
	header( "Content-Disposition: attachment; filename=" .$filename );
	header( "Content-Length: " . $length );

	echo $content;
}

//---------------------------------------------------------
// common
//---------------------------------------------------------
function &_get_row()
{
	$false = false;

	$file_id  = $this->_xlang_post->get_get( 'file_id' );
	$mail_id  = $this->_xlang_post->get_get( 'mail_id' );
	$dirname  = $this->_xlang_post->get_get( 'dirname' );
	$language = $this->_xlang_post->get_get( 'language' );
	$file     = $this->_xlang_post->get_get( 'file' );
	$mail     = $this->_xlang_post->get_get( 'mail' );
	$charset  = $this->_xlang_post->get_get( 'charset' );

	if ( empty($charset) )
	{	$charset = _CHARSET;	}

	$is_xoops_charset = false;
	$module_name = 'Language Translation Support';

	if ( $charset == _CHARSET )
	{
		$is_xoops_charset = true;
		$module_name = $this->sanitize( $this->_MODULE_NAME );
	}

	if ( $file_id )
	{
		$row =& $this->_file_group_handler->get_file_by_id( $file_id );
		if ( is_array( $row) )
		{
			$dirname  = $row['dirname'];
			$language = $row['language'];
			$file     = $row['file'];
			$content  = $row['f_content'];
			$filename = $row['file'].'.txt';
		}
		else
		{
			$this->_param_error = 'no file record';
			return $false;
		}
	}
	elseif ( $mail_id )
	{
		$row =& $this->_mail_group_handler->get_mail_by_id( $mail_id );
		if ( is_array( $row) )
		{
			$dirname  = $row['dirname'];
			$language = $row['language'];
			$mail     = $row['mail'];
			$content  = $row['m_content'];
			$filename = $row['mail'].'.txt';
		}
		else
		{
			$this->_param_error = 'no mail record';
			return $false;
		}
	}
	elseif ( $dirname && $language && $file )
	{
		$row =& $this->_file_group_handler->get_file_by_file( $dirname, $language, $file );
		if ( !is_array( $row ) )
		{
			$this->_param_error = 'no file record';
			return $false;
		}

		$file_id  = $row['id'];
		$content  = $row['f_content'];
		$filename = $file.'.txt';
	}
	elseif ( $dirname && $language && $mail )
	{
		$row =& $this->_mail_group_handler->get_mail_by_mail( $dirname, $language, $mail );
		if ( !is_array( $row ) )
		{
			$this->_param_error = 'no mail record';
			return $false;
		}

		$mail_id  = $row['id'];
		$content  = $row['m_content'];
		$filename = $mail.'.txt';
	}
	elseif ( empty($dirname) )
	{
		$this->_param_error ='no dirname';
		return $false;
	}
	elseif ( empty($language) )
	{
		$this->_param_error = 'no language';
		return $false;
	}
	elseif ( empty($file) && empty($mail) )
	{
		$this->_param_error = 'no file';
		return $false;
	}

	$arr = $row;
	$arr['module_name'] = $module_name;
	$arr['file_id']     = $file_id;
	$arr['mail_id']     = $mail_id;
	$arr['dirname']     = $dirname;
	$arr['language']    = $language;
	$arr['file']        = $file;
	$arr['mail']        = $mail;
	$arr['charset']     = $charset;
	$arr['filename']    = $filename;
	$arr['content']     = $this->_convert_encoding( $content, $charset );
	$arr['is_xoops_charset'] = $is_xoops_charset;

	return $arr;
}

function _convert_encoding( $str, $charset )
{
	$this->_conv_error = '';

	if ( !$this->_flag_convert )
	{	return $str;	}

	if ( empty($str) )
	{	return $str;	}

	if ( $charset == _CHARSET ) 
	{	return $str;	}

// use original, if can not convert
	$conv = xlang_convert_encoding( $str, $charset, _CHARSET );
	if ( $conv ) 
	{
		$str = $conv;
	}
	else 
	{
		$this->_conv_error = 'Unknown encoding '. $to;
	}

	return $str;
}

function _show_error()
{
	$tpl = new XoopsTpl();
	$tpl->assign( 'module_name_s',    $this->sanitize( $this->_MODULE_NAME ) );
	$tpl->assign( 'charset_s',        $this->sanitize( _CHARSET ) );
	$tpl->assign( 'is_xoops_charset', true );
	$tpl->assign( 'param_error',      $this->_param_error );
	$tpl->display( 'db:xlang_download.html' );
}

// --- class end ---
}

//=========================================================
// main
//=========================================================
$xlang_download = new xlang_download();
$xlang_download->main();
exit();
// --- main end ---

?>