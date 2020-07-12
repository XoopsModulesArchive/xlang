<?php
// $Id: show_file.php,v 1.3 2008/12/21 20:49:33 ohwada Exp $

//---------------------------------------------------------
// 2008-12-22 K.OHWADA
// support D3 modules
//---------------------------------------------------------

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

include_once 'admin_header.php';

//=========================================================
// class xlang_show_file
//=========================================================
class xlang_show_file extends xlang_form
{
	var $_charset_file;
	var $_language_file;
	var $_option_file;

	var $_ROWS = 30;
	var $_COLS = 80;

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_show_file()
{
	$this->xlang_form();

	$this->_charset_file  =& xlang_charset_file::getInstance();
	$this->_language_file =& xlang_language_file::getInstance();
	$this->_option_file   =& xlang_option_file::getInstance();
}

//---------------------------------------------------------
// public
//---------------------------------------------------------
function main()
{
	switch ( $this->_get_op() )
	{
		case 'mail':
			$this->_show_mail_file();
			break;

		case 'template':
			$this->_show_template_option_file();
			break;

		case 'template_default':
			$this->_show_template_default_file();
			break;

		case 'filter':
			$this->_show_filter_option_file();
			break;

		case 'charset':
			$this->_show_charset_file();
			break;

		case 'show':
			default:
			$this->_show_language_file();
			break;
	}
}

function _get_op()
{
	return $this->_xlang_post->get_get( 'op' );
}

//---------------------------------------------------------
// _show_language_file
//---------------------------------------------------------
function _show_language_file()
{
	$path     = $this->_xlang_post->get_get( 'path' );
	$dirname  = $this->_xlang_post->get_get( 'dirname' );
	$language = $this->_xlang_post->get_get( 'language' );
	$file     = $this->_xlang_post->get_get( 'file' );

	$bread_crumb  = $this->build_bread_crumb_admin( $dirname, $language, $file );
	$bread_crumb .= ' &gt;&gt; <b>'. _AM_XLANG_SHOW_FILE .'</b>';
	$bread_crumb .= "<br />\n";

	$filename = $this->_language_file->get_language_filename_by_path( $path, $dirname, $language, $file );
	$this->_print_file_in_html( $filename, null, $bread_crumb );
}

//---------------------------------------------------------
// _show_mail_file
//---------------------------------------------------------
function _show_mail_file()
{
	$path     = $this->_xlang_post->get_get( 'path' );
	$dirname  = $this->_xlang_post->get_get( 'dirname' );
	$language = $this->_xlang_post->get_get( 'language' );
	$mail     = $this->_xlang_post->get_get( 'mail' );

	$filename = $this->_language_file->get_mail_filename_by_path( $path, $dirname, $language, $mail );
	$this->_print_file_in_html( $filename );
}

//---------------------------------------------------------
// _show_template_option_file
//---------------------------------------------------------
function _show_template_option_file()
{
	$dirname  = $this->_xlang_post->get_get( 'dirname' );
	$file     = $this->_xlang_post->get_get( 'file' );

	$filename = $this->_option_file->get_template_option_filename( $dirname, $file );
	$this->_print_file_in_html( $filename );
}

//---------------------------------------------------------
// _show_template_default_file
//---------------------------------------------------------
function _show_template_default_file()
{
	$filename = $this->_option_file->get_template_default_filename();
	$this->_print_file_in_html( $filename );
}

//---------------------------------------------------------
// _show_filter_option_file
//---------------------------------------------------------
function _show_filter_option_file()
{
	$dirname  = $this->_xlang_post->get_get( 'dirname' );

	$filename = $this->_option_file->get_filter_option_filename( $dirname );
	$this->_print_file_in_html( $filename );
}

//---------------------------------------------------------
// _show_charset_file
//---------------------------------------------------------
function _show_charset_file()
{
	$filename = $this->_charset_file->get_charset_filename();
	$this->_print_file_in_html( $filename, _AM_XLANG_SHOW_CAHRSET_FILE );
}

//---------------------------------------------------------
// common
//---------------------------------------------------------
function _print_file_in_html( $filename, $title=null, $bread_crumb=null )
{
	if ( empty($title) )
	{
		$title = _AM_XLANG_SHOW_FILE;
	}

	echo $this->build_html_header( $title );

	if ( $bread_crumb )
	{
		echo  $bread_crumb;
	}
	else
	{
		echo $this->build_link_index_admin();
	}

	echo "<h3>". $title ."</h3>\n";

	$content = null;
	if ( file_exists( $filename ) )
	{
		echo $filename ."<br />\n";
		$content = file_get_contents( $filename );
	}
	else
	{
		$msg = 'not exist file : '. $filename;
		$this->highlight( $msg ) ."<br />\n";
	}
	echo "<br />\n";

	echo '<textarea rows="'. $this->_ROWS .'" cols="'. $this->_COLS .'">'."\n";
	echo $content;
	echo "</textarea>\n";

	echo $this->build_html_footer();
}

// --- class end ---
}

//=========================================================
// main
//=========================================================
$view = new xlang_show_file();
$view->main();
exit();
// --- main end ---

?>