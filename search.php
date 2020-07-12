<?php
// $Id: search.php,v 1.4 2007/12/31 11:41:19 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

include 'header.php';

//=========================================================
// class xlang_search
//=========================================================
class xlang_search extends xlang_form
{
	var $_file_group_handler;
	var $_word_group_handler;
	var $_mail_group_handler;
	var $_group_handler;
	var $_highlight;

// post
	var $_post_action;
	var $_post_andor;
	var $_post_query;
	var $_post_start;

// result
	var $_query_array   = array();
	var $_ignore_array  = array();
	var $_show_ignore   = false;
	var $_query_urlencoded = null;

	var $_keyword_min = 5;

	var $_LIMIT_SEARCH = 10;
	var $_flag_file  = false;
	var $_flag_word  = false;
	var $_flag_mail  = false;

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_search()
{
	$this->xlang_form();

	$this->_file_group_handler =& xlang_file_group_handler::getInstance();
	$this->_word_group_handler =& xlang_word_group_handler::getInstance();
	$this->_mail_group_handler =& xlang_mail_group_handler::getInstance();
	$this->_group_handler      =& xlang_group_handler::getInstance();
	$this->_highlight          =& xlang_highlight::getInstance();

	$this->set_limit( $this->_LIMIT_SEARCH );
}

//---------------------------------------------------------
// main
//---------------------------------------------------------
function main()
{
	xlang_set_internal_encoding();

	$this->get_post();
	$query = $this->_post_query;

// if no query
	if ( $this->_post_query == '' ) 
	{
		$this->set_xoops_tpl( -1 );
		return -1;
	}

	$ret = $this->parse_query();

// if no query
	if ( !$ret ) 
	{
		$this->set_xoops_tpl( -2 );
		return -2;
	}

	$this->set_xoops_tpl( 1 );

	if ( $this->_flag_file )
	{
		$this->show_file();
	}

	if ( $this->_flag_word )
	{
		$this->show_word();
	}

	if ( $this->_flag_mail )
	{
		$this->show_mail();
	}

	return 0;
}

//---------------------------------------------------------
// get $_POST & $_GET
//---------------------------------------------------------
function get_post()
{
	$this->get_post_op();
	$this->get_post_query();
	$this->get_post_andor();
}

function get_post_op()
{
	$op = $this->_xlang_post->get_post_get('op');

	switch ( $op )
	{
		case 'file':
			$this->_flag_file = true;
			break;

		case 'word':
			$this->_flag_word = true;
			break;

		case 'mail':
			$this->_flag_mail = true;
			break;

		default:
			$this->_flag_file = true;
			$this->_flag_word = true;
			$this->_flag_mail = true;
			break;
	}
}

function get_post_andor()
{
	$andor = $this->_xlang_post->get_post_get('andor');

	switch ( $andor )
	{
		case 'OR';
		case 'exact';
			$ret = $andor;
			break;

		case 'AND';
		default:
			$ret = 'AND';
			break;
	}

	$this->_post_andor = $ret;
	return $ret;
}

function get_post_query()
{
	$this->_post_query = trim( $this->_xlang_post->get_post_get('query') );
	return $this->_post_query;
}

//--------------------------------------------------------
// parse query
//--------------------------------------------------------
function parse_query()
{
	$this->_keyword_min = $this->get_xoops_search_keyword_min();

	list( $this->_query_array, $this->_ignore_array ) =
		$this->parse_keywords( $this->_post_query, $andor = $this->_post_andor );

	if ( count( $this->_query_array ) )
	{
		$this->_query_urlencoded = urlencode( implode( ' ', $this->_query_array ) );
	}
	else
	{	return false;	}

	if ( count($this->_ignore_array) )
	{
		$this->_show_ignore = true;
	}

	return true;
}

//--------------------------------------------------------
// show file
//--------------------------------------------------------
function show_file()
{
	global $xoopsTpl;

	$total = $this->get_file_total();

	if ( $total > 0 )
	{
		$start   =  $this->get_pagenavi_start( $total );
		$rows    =& $this->get_file_rows(  $this->_LIMIT , $start );

		$xoopsTpl->assign('search_file_show',  1);
		$xoopsTpl->assign('search_file_found', sprintf(_SR_FOUND, $total));

		foreach ( $rows as $row )
		{
			$dirname  = $row['dirname'];
			$language = $row['language'];

			$dir_id = $this->_group_handler->get_cached_id_by_dirname( 
				_XLANG_C_KIND_DIRNAME, $dirname );

			$lang_id = $this->_group_handler->get_cached_id_by_dirname(
				_XLANG_C_KIND_LANGUAGE, $dirname, $language );

			$summary   = xlang_build_search_context( $row['f_content'], $this->_query_array );
			$summary_s = $this->_highlight->build_highlight_keyword_array(
				$this->sanitize( $summary ), $this->_query_array );

			$row['dir_id']     = $dir_id;
			$row['lang_id']    = $lang_id;
			$row['dirname_s']  = $this->sanitize( $dirname );
			$row['language_s'] = $this->sanitize( $language );
			$row['file_s']     = $this->sanitize( $row['file'] );
			$row['file_id']    = intval( $row['id'] );
			$row['summary_s']  = $summary_s;

			$xoopsTpl->append('file_list', $row );
		}

		if ( $total > $this->_LIMIT )
		{
			$script  = XLANG_URL .'/search.php';
			$script .= '?op=file';
			$script .= '&query='. $this->_query_urlencoded;
			$script .= '&andor='. $this->_post_andor;
			$navi    = $this->_pagenavi->build( $script );

			$xoopsTpl->assign('search_file_navi', $navi);
		}

	}
	else
	{
		$xoopsTpl->assign('search_file_show', -1);
	}
}

//--------------------------------------------------------
// show word
//--------------------------------------------------------
function show_word()
{
	global $xoopsTpl;

	$total = $this->get_word_total();

	if ( $total > 0 )
	{
		$start   =  $this->get_pagenavi_start( $total );
		$rows    =& $this->get_word_rows(  $this->_LIMIT , $start );

		$xoopsTpl->assign('search_word_show',  1);
		$xoopsTpl->assign('search_word_found', sprintf(_SR_FOUND, $total));

		foreach ( $rows as $row )
		{
			$dirname  = $row['dirname'];
			$language = $row['language'];
			$file     = $row['file'];

			$dir_id = $this->_group_handler->get_cached_id_by_dirname( 
				_XLANG_C_KIND_DIRNAME, $dirname );

			$lang_id = $this->_group_handler->get_cached_id_by_dirname(
				_XLANG_C_KIND_LANGUAGE, $dirname, $language );

			$file_id = $this->_file_group_handler->get_cached_file_id_by_file(
				$dirname, $language, $file );

			$word_s = $this->_highlight->build_highlight_keyword_array(
				$this->sanitize( $row['word'] ), $this->_query_array );

			$summary   = xlang_build_search_context( $row['w_content'], $this->_query_array );
			$summary_s = $this->_highlight->build_highlight_keyword_array(
				$this->sanitize( $summary ), $this->_query_array );

			$row['dir_id']     = $dir_id;
			$row['lang_id']    = $lang_id;
			$row['file_id']    = $file_id;
			$row['dirname_s']  = $this->sanitize( $dirname );
			$row['language_s'] = $this->sanitize( $language );
			$row['file_s']     = $this->sanitize( $row['file'] );
			$row['word_s']     = $word_s;
			$row['word_id']    = intval( $row['id'] );
			$row['summary_s']  = $summary_s;

			$xoopsTpl->append('word_list', $row );
		}

		if ( $total > $this->_LIMIT )
		{
			$script  = XLANG_URL .'/search.php';
			$script .= '?op=word';
			$script .= '&query='. $this->_query_urlencoded;
			$script .= '&andor='. $this->_post_andor;
			$navi    = $this->_pagenavi->build( $script );

			$xoopsTpl->assign('search_word_navi', $navi);
		}

	}
	else
	{
		$xoopsTpl->assign('search_word_show', -1);
	}
}

//--------------------------------------------------------
// show mail
//--------------------------------------------------------
function show_mail()
{
	global $xoopsTpl;

	$total = $this->get_mail_total();

	if ( $total > 0 )
	{
		$start   =  $this->get_pagenavi_start( $total );
		$rows    =& $this->get_mail_rows(  $this->_LIMIT , $start );

		$xoopsTpl->assign('search_mail_show', 1);
		$xoopsTpl->assign('search_mail_found', sprintf(_SR_FOUND, $total));

		foreach ( $rows as $row )
		{
			$dirname  = $row['dirname'];
			$language = $row['language'];

			$dir_id = $this->_group_handler->get_cached_id_by_dirname( 
				_XLANG_C_KIND_DIRNAME, $dirname );

			$lang_id = $this->_group_handler->get_cached_id_by_dirname(
				_XLANG_C_KIND_LANGUAGE, $dirname, $language );

			$summary   = xlang_build_search_context( $row['m_content'], $this->_query_array );
			$summary_s = $this->_highlight->build_highlight_keyword_array(
				$this->sanitize( $summary ), $this->_query_array );

			$row['dir_id']     = $dir_id;
			$row['lang_id']    = $lang_id;
			$row['dirname_s']  = $this->sanitize( $dirname );
			$row['language_s'] = $this->sanitize( $language );
			$row['mail_s']     = $this->sanitize( $row['mail'] );
			$row['mail_id']    = intval( $row['id'] );
			$row['summary_s']  = $summary_s;

			$xoopsTpl->append('mail_list', $row );
		}

		if ( $total > $this->_LIMIT )
		{
			$script  = XLANG_URL .'/search.php';
			$script .= '?op=mail';
			$script .= '&query='. $this->_query_urlencoded;
			$script .= '&andor='. $this->_post_andor;
			$navi    = $this->_pagenavi->build( $script );

			$xoopsTpl->assign('search_mail_navi', $navi);
		}

	}
	else
	{
		$xoopsTpl->assign('search_mail_show', -1);
	}
}

//--------------------------------------------------------
// xoops template
//--------------------------------------------------------
function set_xoops_tpl( $show )
{
	global $xoopsTpl;

	$and   = null;
	$or    = null;
	$exact = null;

	switch ( $this->_post_andor )
	{
		case 'exact':
			$exact = $this->_SELECTED;
			break;

		case 'OR':
			$or = $this->_SELECTED;
			break;
	
		case 'AND':
		default:
			$and = $this->_SELECTED;
			break;
	}

	$this->set_template();

	$xoopsTpl->assign('search_selected_and',   $and );
	$xoopsTpl->assign('search_selected_or',    $or );
	$xoopsTpl->assign('search_selected_exact', $exact );

	$xoopsTpl->assign('search_show',          $show );
	$xoopsTpl->assign('search_query_s',       $this->sanitize( $this->_post_query ) );
	$xoopsTpl->assign('search_keywords_s',    $this->sanitize( $this->_query_urlencoded ) );
	$xoopsTpl->assign('search_keyword_list',  $this->_query_array );
	$xoopsTpl->assign('search_ignore_list',   $this->_ignore_array );
	$xoopsTpl->assign('search_show_ignore',   $this->_show_ignore );
	$xoopsTpl->assign('search_key_too_short', sprintf( _SR_KEYTOOSHORT, $this->_keyword_min ) );

}

//---------------------------------------------------------
// file handler
//---------------------------------------------------------
function get_file_total()
{
	return $this->_file_group_handler->get_count_by_search(
		$this->_query_array, $this->_post_andor );
}

function &get_file_rows( $limit, $offset )
{
	return $this->_file_group_handler->get_files_by_search(
		$this->_query_array, $this->_post_andor, $limit, $offset );
}

//---------------------------------------------------------
// word handler
//---------------------------------------------------------
function get_word_total()
{
	return $this->_word_group_handler->get_count_by_search(
		$this->_query_array, $this->_post_andor );
}

function &get_word_rows( $limit, $offset )
{
	return $this->_word_group_handler->get_words_by_search(
		$this->_query_array, $this->_post_andor, $limit, $offset );
}

//---------------------------------------------------------
// mail handler
//---------------------------------------------------------
function get_mail_total()
{
	return $this->_mail_group_handler->get_count_by_search(
		$this->_query_array, $this->_post_andor );
}

function &get_mail_rows( $limit, $offset )
{
	return $this->_mail_group_handler->get_mails_by_search(
		$this->_query_array, $this->_post_andor, $limit, $offset );
}

// --- class end ---
}


//================================================================
// main
//================================================================
$xlang_search = new xlang_search();

// --- template start ---
$xoopsOption['template_main'] = 'xlang_search.html';
include XOOPS_ROOT_PATH.'/header.php';

$xlang_search->main();

$xoopsTpl->assign('execution_time', $xlang_search->get_execution_time() );
$xoopsTpl->assign('memory_usage',   $xlang_search->get_memory_usage() );
include XOOPS_ROOT_PATH."/footer.php";
exit();
// --- main end ---

?>