<?php
// $Id: file.php,v 1.9 2008/12/21 20:49:33 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

//---------------------------------------------------------
// 2008-12-22 K.OHWADA
// support D3 modules
//---------------------------------------------------------

include_once 'header.php';
include_once XLANG_ROOT_PATH.'/options/filter_default.php';

//=========================================================
// class xlang_file
//=========================================================
class xlang_file extends xlang_form
{
	var $_word_group_handler;
	var $_file_group_handler;
	var $_log_group_handler;
	var $_template_group_handler;
	var $_option_file;
	var $_highlight;

	var $_param_error = null;

	var $_MAX_NOTIFY_CONTENT = 200;

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_file()
{
	$this->xlang_form();

	$this->_word_group_handler     =& xlang_word_group_handler::getInstance();
	$this->_file_group_handler     =& xlang_file_group_handler::getInstance();
	$this->_log_group_handler      =& xlang_log_group_handler::getInstance();
	$this->_template_group_handler =& xlang_template_group_handler::getInstance();
	$this->_option_file            =& xlang_option_file::getInstance();
	$this->_highlight              =& xlang_highlight::getInstance();

}

//---------------------------------------------------------
// edit
//---------------------------------------------------------
function get_op()
{
	$create = $this->_xlang_post->get_post_get( 'create' );
	if ( $create )
	{	return 'create';	}

	return $this->_xlang_post->get_post_get( 'op' );
}

function edit_file()
{
	$dirname   =& $this->_xlang_post->get_post( 'dirname' );
	$language  =& $this->_xlang_post->get_post( 'language' );
	$file      =& $this->_xlang_post->get_post( 'file' );
	$content   =& $this->_xlang_post->get_post( 'content' );
	$note      =& $this->_xlang_post->get_post( 'note' );

	$url = $this->build_url( 'file.php', 'form', $dirname, $language, $file );

	$ret = $this->_file_group_handler->add_with_exist_check(
		$dirname, $language, $file, $content, $note );
	if ( !$ret )
	{
		$msg  = 'DB Error <br />';
		$msg .= $this->get_format_error();
		redirect_header( $url, 3, $msg );
		exit();
	}

	$id = $this->_file_group_handler->get_id();
	$event_url = XLANG_URL .'/file.php?id='. $id;

	$msg  = "--- \n";
	$msg .= $dirname.' > '.$language.' > '.$file."\n\n";
	$msg .= xlang_substr( $content, 0, $this->_MAX_NOTIFY_CONTENT )."\n";
	$msg .= "--- \n";

	$lang_arr = array( $language );
	$this->xoops_trigger_event_by_dirname( $event_url, $msg, $dirname, $lang_arr );

	redirect_header( $url, 1, 'Finished' );
	exit();
}

//---------------------------------------------------------
// show file
//---------------------------------------------------------
function show_file()
{
	global $xoopsTpl;

	$id       = $this->_xlang_post->get_post_get( 'id' );
	$dirname  = $this->_xlang_post->get_post_get( 'dirname' );
	$language = $this->_xlang_post->get_post_get( 'language' );
	$file     = $this->_xlang_post->get_post_get( 'file' );
	$keywords = $this->_xlang_post->get_post_get( 'keywords' );

	$file_row =& $this->_get_file( $id, $dirname, $language, $file );
	if ( !is_array( $file_row) )
	{
		if ( $this->_param_error )
		{
			$msg = $this->_param_error;
		}
		else
		{
			$msg = 'no file record';
		}

		$this->set_template( $dirname, $language, $file );
		$xoopsTpl->assign( 'param_error', $msg );
		return false;
	}

	$dirname    = $file_row['dirname'];
	$language   = $file_row['language'];
	$file       = $file_row['file'];
	$file_id    = $file_row['id'];
	$file_time  = $file_row['time'];
	$content    = $file_row['f_content'];
	$note       = $file_row['f_note'];

	$param =& $this->_get_update_param( $file_time, $dirname, $language, $file );

	list( $keyword_array, $ignore_array ) = $this->parse_keywords( $keywords );
	$content_s = $this->_highlight->build_highlight_keyword_array(
		$this->sanitize( $content ), $keyword_array );
	$note_s    = $this->sanitize( $note );

// get undefined word array
	list( $undefined_array, $show_undefined, $cont_language ) =
		$this->_get_undefined_array( $dirname, $file, $language );

	$this->set_template( $dirname, $language, $file, $param['word'] );

	$xoopsTpl->assign( 'search_query_s', $this->sanitize( $keywords ) );

	$xoopsTpl->assign( 'file_id',      $file_id );
	$xoopsTpl->assign( 'file_time',    $file_time );
	$xoopsTpl->assign( 'content_s',    $content_s );
	$xoopsTpl->assign( 'note_s',       $note_s );

	$xoopsTpl->assign( 'word_id',      $param['word_id'] );
	$xoopsTpl->assign( 'word_time',    $param['word_time'] );
	$xoopsTpl->assign( 'log_time',     $param['log_time'] );
	$xoopsTpl->assign( 'show_update',  $param['show_update'] );

	$xoopsTpl->assign( 'tpl_id',       0 );
	$xoopsTpl->assign( 'file_status',  0 );
	$xoopsTpl->assign( 'tpl_status',   0 );

	$xoopsTpl->assign( 'cont_language_s', $this->sanitize( $cont_language ) );
	$xoopsTpl->assign( 'show_undefined',  $show_undefined );
	$xoopsTpl->assign( 'undefined_list',  $undefined_array );

}

function &_get_file( $id, $dirname, $language, $file )
{
	$false = false;

	if ( $id )
	{
		$row =& $this->_file_group_handler->get_file_by_id( $id );
		if ( is_array($row) )
		{
			return $row;
		}
	}

	if ( empty($dirname) )
	{
		$this->_param_error = 'no dirname';
		return $false;
	}
	elseif ( empty($language) )
	{
		$this->_param_error = 'no language';
		return $false;
	}
	elseif ( empty($file) )
	{
		$this->_param_error = 'no file';
		return $false;
	}

	return $this->_file_group_handler->get_file_by_file( $dirname, $language, $file );
}

function &_get_update_param( $file_time, $dirname, $language, $file )
{
	$word_id   = 0;
	$word_time = 0;
	$word      = null;

	$word_row =& $this->_word_group_handler->get_latest_word_by_dirname( $dirname, $language, $file );
	if ( is_array( $word_row ) )
	{
		$word_id   = $word_row['id'];
		$word_time = $word_row['time'];
		$word      = $word_row['word'];
	}

	$log_time = $this->_log_group_handler->get_latest_word_time_by_dirname( $dirname, $language, $file );

	$arr = array(
		'word_id'     => $word_id,
		'word_time'   => $word_time,
		'word'        => $word,
		'log_time'    => $log_time,
		'show_update' => $this->judge_word_update( $word_time, $file_time, $log_time ),
	);

	return $arr;
}

//---------------------------------------------------------
// show form
//---------------------------------------------------------
function show_form()
{
	global $xoopsTpl;

	$id       = $this->_xlang_post->get_post_get( 'id' );
	$dirname  = $this->_xlang_post->get_post_get( 'dirname' );
	$language = $this->_xlang_post->get_post_get( 'language' );
	$file     = $this->_xlang_post->get_post_get( 'file' );
	$content  = $this->_xlang_post->get_post_get( 'content' );
	$note     = $this->_xlang_post->get_post_get( 'note' );
	$op       = $this->get_op();

	$file_id       = 0;
	$file_time     = 0;
	$word_error    = null;
	$tpl_id        = 0;
	$tpl_status    = 0;
	$filter_status = 0;
	$file_status   = 0;

	$file_row =& $this->_get_file( $id, $dirname, $language, $file );
	if ( is_array( $file_row) )
	{
		$dirname   = $file_row['dirname'];
		$language  = $file_row['language'];
		$file      = $file_row['file'];
		$file_id   = $file_row['id'];
		$file_time = $file_row['time'];
		$content   = $file_row['f_content'];

		if ( empty($note) )
		{
			$note = $file_row['f_note'];
		}
	}
	elseif ( $this->_param_error )
	{
		$this->set_template( $dirname, $language, $file );
		$xoopsTpl->assign( 'param_error', $this->_param_error );
		return false;
	}

	$count = $this->_word_group_handler->get_count_by_dirname( $dirname, $language, $file );
	if ( $count == 0 )
	{
		$word_error = 'not exists '. $this->sanitize( $language ) .' words';
	}

	$param =& $this->_get_update_param( $file_time, $dirname, $language, $file );

	if ( ( $op == 'create' )|| empty($content) )
	{
		list( $content, $tpl_status, $tpl_id, $filter_status ) = 
			$this->_build_lang_file( $dirname, $language, $file, $note );
		$file_status  = 1;
	}

// get undefined word array
	list( $undefined_array, $show_undefined, $cont_language ) =
		$this->_get_undefined_array( $dirname, $file, $language );

	$this->set_template( $dirname, $language, $file, $param['word'] );

	$xoopsTpl->assign( 'file_time',    $file_time );
	$xoopsTpl->assign( 'file_id',      $file_id );
	$xoopsTpl->assign( 'content_s',    $this->sanitize( $content ) );
	$xoopsTpl->assign( 'note_s',       $this->sanitize( $note ) );

	$xoopsTpl->assign( 'word_id',      $param['word_id'] );
	$xoopsTpl->assign( 'word_time',    $param['word_time'] );
	$xoopsTpl->assign( 'log_time',     $param['log_time'] );
	$xoopsTpl->assign( 'show_update',  $param['show_update'] );

	$xoopsTpl->assign( 'word_error',     $word_error );
	$xoopsTpl->assign( 'tpl_id',         $tpl_id );
	$xoopsTpl->assign( 'tpl_status',     $tpl_status );
	$xoopsTpl->assign( 'filter_status',  $filter_status );
	$xoopsTpl->assign( 'file_status',    $file_status );
	$xoopsTpl->assign( 'token',          $this->get_token() );
	$xoopsTpl->assign( 'token_error',    $this->_token_error );

	$xoopsTpl->assign( 'cont_language_s', $this->sanitize( $cont_language ) );
	$xoopsTpl->assign( 'show_undefined',  $show_undefined );
	$xoopsTpl->assign( 'undefined_list',  $undefined_array );
}

function _build_lang_file( $dirname, $language, $file, $file_note_in )
{
	$word_arr     =& $this->_word_group_handler->get_words_by_dirname(  $dirname, $language, $file );
	$file_row     =& $this->_file_group_handler->get_file_by_file(      $dirname, $language, $file );

	list( $tpl_id, $tpl_status, $template ) = 
		$this->_get_template( $dirname, $file );

	list( $filter_status, $filter, $filter_key ) = 
		$this->_get_filter( $dirname, $file );

	$file_note = '';
	if ( $file_note_in )
	{
		$note_arr = $this->_get_note_array( $file_note_in );
		foreach ( $note_arr as $line )
		{
			$file_note .= '// '. $line ."\n";
		}
	}

	$defines = '';
	foreach( $word_arr as $name => $row )
	{
		if ( $row['w_note'] )
		{
			$note_arr = $this->_get_note_array( $row['w_note'] );
			foreach ( $note_arr as $line )
			{
				$defines .= '// '. $line ."\n";
			}
		}

		$content = $filter( $row['w_content'] );

		if ( $row['w_act'] == 0 )
		{	$defines .= '// ';	}

		$key = '"'. $name .'"';
		$val = '"'. $content .'"';

		if ( $filter_key ) {
			$key = $filter_key( $key );
		}

		$defines .= 'define('. $key .', '. $val .');' ."\n";
	}

	$search = array(
		'{XLANG_DATE}',
		'{XLANG_USER}',
		'{XLANG_NOTE}',
		'{XLANG_DEFINES}'
	 );

	$replace = array(
		date('Y-m-d H:i:s'),
		$this->_xoops_uname,
		$file_note,
		$defines
	 );

	$lang_file = str_replace( $search, $replace, $template );

	return array( $lang_file, $tpl_status, $tpl_id, $filter_status );
}

function _get_template( $dirname, $file )
{
	$tpl_id     = 0;
	$tpl_status = 0;
	$template   = null;

	$template_row =& $this->_template_group_handler->get_template_by_file( $dirname, $file );

	if ( isset( $template_row['t_content'] ) )
	{
		$tpl_status = 1;
		$tpl_id     = $template_row['id'];
		$template   = $template_row['t_content'];
	}

	if ( empty($template) )
	{
		$tpl_option = $this->_option_file->read_template_option_file( $dirname, $file );
		if ( $tpl_option )
		{
			$tpl_status = 2;
			$template   = $tpl_option;
		}
		else
		{
			$tpl_status = 3;
			$template   = $this->_option_file->read_template_default_file();
		}
	}

	return array( $tpl_id, $tpl_status, $template );
}

function _get_filter( $dirname, $file )
{
	list ( $filter_val, $filter_key ) = 
		$this->_option_file->get_include_filter_option_funcname( $dirname, $file );
	if ( $filter_val ) {
		$status = 2;
		$filter = $filter_val ;
	} elseif ( $filter_key ) {
		$status = 2;
		$filter = 'xlang_filter_default' ;
	} else {
		$status = 3;
		$filter = 'xlang_filter_default' ;
	}

	return array( $status, $filter, $filter_key );
}

function &_get_note_array( $note )
{
	$arr = array();
	if ( $note )
	{
		$note     = str_replace( '"',  '\"', $note );
		$note_arr = explode( "\n", $note );
		foreach ( $note_arr as $line )
		{
			$line = str_replace( "\r", '',  $line );
			$line = str_replace( "\t", '',  $line );
			if ( $line )
			{
				$arr[] = $line;
			}
		}
	}
	return $arr;
}

function &_get_undefined_array( $dirname, $file, $language )
{
	$cont_language = $this->get_contrast_language( $language );
	$word_arr =& $this->_word_group_handler->get_words_by_two_languages(
		$dirname, $file, $language, $cont_language );
	$undefined_array =& $this->_word_group_handler->_word_both_undefined_array;

	$show_undefined_list = false;
	if ( is_array($undefined_array) && count($undefined_array) )
	{	$show_undefined_list = true;	}

	$arr = array( $undefined_array, $show_undefined_list, $cont_language );
	return $arr;
}

// --- class end ---
}

//=========================================================
// main
//=========================================================
$manage = new xlang_file();

switch ( $manage->get_op() )
{
	case 'edit':
		if ( $manage->check_token() )
		{
			$manage->check_login();
			$manage->edit_file();
			exit();
		}

		$xoopsOption['template_main'] = 'xlang_file_form.html';
		include XOOPS_ROOT_PATH.'/header.php';
		$manage->show_form();
		break;

	case 'form':
	case 'create':
		$xoopsOption['template_main'] = 'xlang_file_form.html';
		include XOOPS_ROOT_PATH.'/header.php';
		$manage->show_form();
		break;

	case 'show':
	default:
		$xoopsOption['template_main'] = 'xlang_file_show.html';
		include XOOPS_ROOT_PATH.'/header.php';
		$manage->show_file();
		break;	
}

$xoopsTpl->assign('execution_time', $manage->get_execution_time() );
$xoopsTpl->assign('memory_usage',   $manage->get_memory_usage() );
include XOOPS_ROOT_PATH.'/footer.php';
exit();
// --- main end ---

?>