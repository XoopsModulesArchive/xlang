<?php
// $Id: index.php,v 1.10 2008/12/21 20:49:33 ohwada Exp $

//---------------------------------------------------------
// 2008-12-22 K.OHWADA
// support D3 modules
//---------------------------------------------------------

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

include_once 'header.php';

//=========================================================
// class xlang_index
//=========================================================
class xlang_index extends xlang_form
{
	var $_word_group_handler;
	var $_file_group_handler;
	var $_mail_group_handler;
	var $_log_group_handler;
	var $_group_handler;
	var $_language_file;

	var $_LIMIT_LATEST       = 5; 
	var $_LIMIT_UPDATING     = 5;
	var $_LIMIT_UPDATING_MIN = 1;
	var $_MAX_SREACH_FILE_LEVEL = 10;
	var $_MAX_SREACH_TIME       = 100;

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_index()
{
	$this->xlang_form();

	$this->_word_group_handler =& xlang_word_group_handler::getInstance();
	$this->_file_group_handler =& xlang_file_group_handler::getInstance();
	$this->_mail_group_handler =& xlang_mail_group_handler::getInstance();
	$this->_log_group_handler  =& xlang_log_group_handler::getInstance();
	$this->_group_handler      =& xlang_group_handler::getInstance();
	$this->_language_file      =& xlang_language_file::getInstance();

}

//---------------------------------------------------------
// post
//---------------------------------------------------------
function get_op()
{
	$dir_id   = $this->_xlang_post->get_get( 'dir_id' );
	$lang_id  = $this->_xlang_post->get_get( 'lang_id' );
	$dirname  = $this->_xlang_post->get_get( 'dirname' );
	$language = $this->_xlang_post->get_get( 'language' );

	if ( $lang_id )
	{	return 'file';	}

	if ( $dir_id )
	{	return 'language';	}

	if ( $dirname && $language )
	{	return 'file';	}

	if ( $dirname )
	{	return 'language';	}

	return '';
}

//---------------------------------------------------------
// list dirname
//---------------------------------------------------------
function page_top()
{
	$this->set_template();
	$this->_list_dirname();
	$this->_latest_file_list();
	$this->_latest_mail_list();
	$this->_latest_word_list();

}

function _list_dirname()
{
	global $xoopsTpl;

	$table_arr =& $this->_word_group_handler->get_dirnames_group_by_dirname();
	$root_arr  =& $this->_language_file->get_root_module_dirs();
	$dir_arr   =  array();

	if ( is_array($table_arr) && count($table_arr) &&
	     is_array($root_arr) && count($root_arr) )
	{
		$dir_arr = array_unique( array_merge($table_arr, $root_arr) );
	}
	elseif ( is_array($table_arr) && count($table_arr) )
	{
		$dir_arr =& $table_arr;
	}
	elseif ( is_array($root_arr) && count($root_arr) )
	{
		$dir_arr =& $root_arr;
	}

	foreach ( $dir_arr as $dirname )
	{

// check exists
		$count = $this->_word_group_handler->get_count_by_dirname( $dirname );
		if ( $count == 0 )
		{	continue;	}

		$row =& $this->_get_latest_word( $dirname );
		$row['count'] = $count;
	
		$xoopsTpl->append( 'dirname_list', $row );
	}
}

function &_get_latest_word( $dirname, $language=null, $file=null )
{
	$row =& $this->_word_group_handler->get_latest_word_by_dirname( $dirname, $language, $file );
	if ( is_array($row) )
	{
		$dirname  = $row['dirname'];
		$language = $row['language'];
		$file     = $row['file'];

		$row['dir_id']  = $this->_group_handler->get_cached_id_by_dirname( _XLANG_C_KIND_DIRNAME,  $dirname );
		$row['lang_id'] = $this->_group_handler->get_cached_id_by_dirname( _XLANG_C_KIND_LANGUAGE, $dirname, $language );
		$row['file_id'] = $this->_file_group_handler->get_cached_file_id_by_file( $dirname, $language, $file );
		$row['word_id'] = $row['id'];
	}
	else
	{
		$row = array(
			'dir_id'   => 0,
			'lang_id'  => 0,
			'file_id'  => 0,
			'word_id'  => 0,
			'dirname'  => $dirname,
			'language' => $language,
			'file'     => $file,
			'word'     => null,
			'time'     => 0,
		);
	}

	$row['dirname_s']  = $this->sanitize( $row['dirname'] );
	$row['language_s'] = $this->sanitize( $row['language'] );
	$row['file_s']     = $this->sanitize( $row['file'] );
	$row['word_s']     = $this->sanitize( $row['word'] );

	return $row;
}

function _latest_file_list( $dirname=null, $language=null )
{
	$LIMIT = 2 * $this->_LIMIT_LATEST;

	$i     = 0;
	$j     = 0;
	$start = 0;
	$arr_1 = array();
	$arr_2 = array();

	$file_count = $this->_file_group_handler->get_count_by_dirname( $dirname, $language );

	for ( $k = 0; $k < $this->_MAX_SREACH_FILE_LEVEL; $k++ )
	{
		$file_arr =& $this->_file_group_handler->get_files_latest_by_dirname( $dirname, $language, $LIMIT, $start );
		foreach ( $file_arr as $row )
		{
			$dirname   = $row['dirname'];
			$language  = $row['language'];
			$file      = $row['file'];
			$file_time = $row['time'];

			$count = $this->_word_group_handler->get_count_by_dirname( $dirname, $language, $file );

			$word_time =  0;

			$word_row  =& $this->_word_group_handler->get_latest_word_by_dirname( $dirname, $language, $file );
			if ( is_array( $word_row ) )
			{
				$word_time = $word_row['time'];
			}

			$log_time    = $this->_log_group_handler->get_latest_word_time_by_dirname( $dirname, $language, $file );
			$word_update = $this->judge_word_update( $word_time, $file_time, $log_time );

			$check = $this->_word_group_handler->compare_words_by_two_languages(
				$dirname, $file, $language, $this->get_contrast_language( $language ) );

			if ( $count && !$word_update && $check )
			{
				if ( $i < $this->_LIMIT_LATEST )
				{
					$row['show_file']      = 0;
					$row['show_download']  = 2;
					$row['show_undefined'] = false;
					$new_time = $this->_get_no_confilect_time( $arr_1, $file_time );
					$arr_1[ $new_time ] = $row;
					$i ++;
				}
			}
			else
			{
				if ( $j < $this->_LIMIT_UPDATING )
				{
					$show_file     = 0;
					$show_download = 0;

					if ( ! $check )
					{
						$show_file = 2;
					}
					elseif ( $word_update )
					{
						$show_file = 1;
					}

					if ( $count )
					{
						$show_download = 1;
					}

					$row['show_file']      = $show_file;
					$row['show_download']  = $show_download;
					$row['show_undefined'] = !$check;
					$new_time = $this->_get_no_confilect_time( $arr_2, $file_time );
					$arr_2[ $new_time ] = $row;
					$j ++;
				}
			}
		}

		if (( $i >= $this->_LIMIT_LATEST )&&( $j >= $this->_LIMIT_UPDATING_MIN ))
		{	break;	}

		if ( $start >= $file_count )
		{	break;	}

		$start += $LIMIT;
	}

	krsort( $arr_1, SORT_NUMERIC );
	krsort( $arr_2, SORT_NUMERIC );

	foreach ( $arr_1 as $row )
	{
		$this->_leatest_file_line( $row );
	}

	foreach ( $arr_2 as $row )
	{
		$this->_leatest_file_line( $row );
	}
}

function _leatest_file_line( &$row )
{
	global $xoopsTpl;

	$dirname  = $row['dirname'];
	$language = $row['language'];

	$row['dir_id']  = $this->_group_handler->get_cached_id_by_dirname( _XLANG_C_KIND_DIRNAME, $dirname );
	$row['lang_id'] = $this->_group_handler->get_cached_id_by_dirname( _XLANG_C_KIND_LANGUAGE, $dirname, $language );
	$row['file_id'] = intval( $row['id'] );

	$row['dirname_s']  = $this->sanitize( $dirname );
	$row['language_s'] = $this->sanitize( $language );
	$row['file_s']     = $this->sanitize( $row['file'] );

	$xoopsTpl->append( 'file_list', $row );
}

function _latest_mail_list( $dirname=null, $language=null )
{
	global $xoopsTpl;

	$mail_arr =& $this->_mail_group_handler->get_mails_latest_by_dirname( $dirname, $language, $this->_LIMIT_LATEST );

	if ( is_array($mail_arr) && count($mail_arr) )
	{
		$xoopsTpl->assign( 'show_mail', true );

		foreach ( $mail_arr as $row )
		{
			$dirname  = $row['dirname'];
			$language = $row['language'];

			$row['dir_id']  = $this->_group_handler->get_cached_id_by_dirname( _XLANG_C_KIND_DIRNAME, $dirname );
			$row['lang_id'] = $this->_group_handler->get_cached_id_by_dirname( _XLANG_C_KIND_LANGUAGE, $dirname, $language );
			$row['mail_id'] = intval( $row['id'] );

			$row['dirname_s']  = $this->sanitize( $dirname );
			$row['language_s'] = $this->sanitize( $language );
			$row['mail_s']     = $this->sanitize( $row['mail'] );

			$xoopsTpl->append( 'mail_list', $row );
		}
	}
}

function _latest_word_list( $dirname=null, $language=null, $file=null )
{
	global $xoopsTpl;

	$word_arr =& $this->_word_group_handler->get_words_latest_by_dirname(
		$dirname, $language, $file, $this->_LIMIT_LATEST );

	foreach ( $word_arr as $row )
	{
		$dirname  = $row['dirname'];
		$language = $row['language'];
		$file     = $row['file'];

		$row['dir_id']  = $this->_group_handler->get_cached_id_by_dirname( _XLANG_C_KIND_DIRNAME,  $dirname );
		$row['lang_id'] = $this->_group_handler->get_cached_id_by_dirname( _XLANG_C_KIND_LANGUAGE, $dirname, $language );
		$row['file_id'] = $this->_file_group_handler->get_cached_file_id_by_file( $dirname, $language, $file );
		$row['word_id'] = $row['id'];

		$row['dirname_s']  = $this->sanitize( $dirname );
		$row['language_s'] = $this->sanitize( $language );
		$row['file_s']     = $this->sanitize( $file );
		$row['word_s']     = $this->sanitize( $row['word'] );

		$xoopsTpl->append( 'word_list', $row );
	}
}

function _get_no_confilect_time( &$arr, $time )
{
	$new_time = $time;
	for ( $i = 0; $i < $this->_MAX_SREACH_TIME; $i++ )
	{
		if ( !isset( $arr[ $new_time ] ) )
		{	break;	}
		$new_time ++;
	}
	return $new_time;
}

//---------------------------------------------------------
// list language
//---------------------------------------------------------
function page_language()
{
	$dir_id   = $this->_xlang_post->get_get( 'dir_id' );
	$dirname  = $this->_xlang_post->get_get( 'dirname' );

	if ( $dir_id )
	{
		$row =& $this->_group_handler->get_row_by_id( $dir_id );
		if ( is_array($row) )
		{
			$dirname = $row['dirname'];
		}
	}
	else
	{
		$id = $this->_group_handler->get_cached_id_by_dirname( _XLANG_C_KIND_DIRNAME, $dirname );
		if ( $id )
		{
			$_GET['dir_id'] = $id;
		}
	}

	$this->set_template(   $dirname );
	$this->_list_language( $dirname );
	$this->_latest_file_list(   $dirname );
	$this->_latest_mail_list(   $dirname );
	$this->_latest_word_list(   $dirname );
}

function _list_language( $dirname )
{
	global $xoopsTpl;

	$lang_arr =& $this->_word_group_handler->get_languages_group_by_language( $dirname );

	foreach ( $lang_arr as $language )
	{

// check exists
		$count = $this->_word_group_handler->get_count_by_dirname( $dirname, $language );
		if ( $count == 0 )
		{	continue;	}

		$row =& $this->_get_latest_word( $dirname, $language );
		$row['count']   = $count;
		$row['image_s'] = $this->sanitize( $this->get_language_image( $language ) );

		$row['show_undefined'] = ! $this->_word_group_handler->compare_words_by_two_languages(
			$dirname, null, $language, $this->get_contrast_language( $language ) );

		$xoopsTpl->append( 'language_list', $row );
	}

}

//---------------------------------------------------------
// list file
//---------------------------------------------------------
function page_file()
{
	$lang_id  = $this->_xlang_post->get_get( 'lang_id' );
	$dirname  = $this->_xlang_post->get_get( 'dirname' );
	$language = $this->_xlang_post->get_get( 'language' );

	if ( $lang_id )
	{
		$row =& $this->_group_handler->get_row_by_id( $lang_id );
		if ( is_array($row) )
		{
			$dirname  = $row['dirname'];
			$language = $row['language'];
		}
	}
	else
	{
		$id = $this->_group_handler->get_cached_id_by_dirname( _XLANG_C_KIND_LANGUAGE, $dirname, $language );
		if ( $id )
		{
			$_GET['lang_id'] = $id;
		}
	}

	if ( !isset($_GET['dir_id']) )
	{
		$id = $this->_group_handler->get_cached_id_by_dirname( _XLANG_C_KIND_DIRNAME, $dirname );
		if ( $id )
		{
			$_GET['dir_id'] = $id;
		}
	}

	$cont_language = $this->get_contrast_language( $language );

	$this->set_template( $dirname, $language );
	$this->_list_file(   $dirname, $language, $cont_language );
	$this->_list_mail(   $dirname, $language, $cont_language );
	$this->_latest_word_list( $dirname, $language );
}

function _list_file( $dirname, $language, $cont_language )
{
	global $xoopsTpl;

	$file_arr =& $this->_word_group_handler->get_files_group_by_file( $dirname );

	foreach ( $file_arr as $file )
	{
		$count = $this->_word_group_handler->get_count_by_dirname( $dirname, $language, $file );

		$file_id   = 0;
		$file_time = 0;
		$show_file = 0;
		$show_download = 0;

		$file_row =& $this->_file_group_handler->get_cached_file_by_file( $dirname, $language, $file );
		if ( is_array($file_row) )
		{
			$file_id   = intval( $file_row['id'] );
			$file_time = intval( $file_row['time'] );
		}

		$word_row =& $this->_get_latest_word( $dirname, $language, $file );

		$log_time    = $this->_log_group_handler->get_latest_word_time_by_dirname( $dirname, $language, $file );
		$word_update = $this->judge_word_update( $word_row['time'], $file_time, $log_time );

		$check = $this->_word_group_handler->compare_words_by_two_languages(
			$dirname, $file, $language, $cont_language );


		if ( ! $check )
		{
			$show_file = 2;
		}
		elseif ( $word_update )
		{
			$show_file = 1;
		}

		if ( $count )
		{
			$show_download = 1;
			if ( $file_id && !$word_update && $check )
			{
				$show_download = 2;
			}
		}

		$row =& $word_row;
		$row['count']   = $count;
		$row['file_id'] = $file_id;
		$row['image_s'] = $this->sanitize( $this->get_language_image( $language ) );
		$row['show_download']  = $show_download;
		$row['show_file']      = $show_file;
		$row['show_undefined'] = ! $check;

		$xoopsTpl->append( 'file_list', $row );
	}
}

function _list_mail( $dirname, $language, $cont_language )
{
	global $xoopsTpl;

	$mail_arr =& $this->_mail_group_handler->get_mails_group_by_mail( $dirname );

	if ( is_array($mail_arr) && count($mail_arr) )
	{
		$xoopsTpl->assign( 'show_mail', true );

		foreach ( $mail_arr as $mail )
		{
			$mail_id = 0;
			$time    = 0;
			$count   = 0;
			$show_download = 0;

			$row =& $this->_mail_group_handler->get_mail_by_mail( $dirname, $language, $mail );
			if ( is_array($row) )
			{
				$mail_id = $row['id'];
				$time    = $row['time'];
				$count   = 1;
				$show_download = 2;
			}

			$check = $this->_mail_group_handler->compare_mails_by_two_languages(
				$dirname, $mail, $language, $cont_language );

			$line = array(
				'dirname_s'      => $this->sanitize( $dirname ),
				'language_s'     => $this->sanitize( $language ),
				'mail_s'         => $this->sanitize( $mail ),
				'mail_id'        => intval( $mail_id ),
				'time'           => intval( $time ),
				'count'          => intval( $count ),
				'show_download'  => $show_download,
				'show_undefined' => !$check,
			);

			$xoopsTpl->append( 'mail_list', $line );
		}
	}

}

// --- class end ---
}

//=========================================================
// main
//=========================================================
$view = new xlang_index();

switch ( $view->get_op() )
{
	case 'language':
		$xoopsOption['template_main'] = 'xlang_index_language.html';
		include XOOPS_ROOT_PATH.'/header.php';
		$view->page_language();
		break;

	case 'file':
		$xoopsOption['template_main'] = 'xlang_index_file.html';
		include XOOPS_ROOT_PATH.'/header.php';
		$view->page_file();
		break;

	case 'dirname':
	default:
		$xoopsOption['template_main'] = 'xlang_index_top.html';
		include XOOPS_ROOT_PATH.'/header.php';
		$view->page_top();
		break;
}

$xoopsTpl->assign( 'execution_time', $view->get_execution_time() );
$xoopsTpl->assign( 'memory_usage',   $view->get_memory_usage() );
include XOOPS_ROOT_PATH.'/footer.php';
exit();
// --- main end ---

?>