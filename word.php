<?php
// $Id: word.php,v 1.7 2008/01/12 11:25:42 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

include_once 'header.php';

//=========================================================
// class xlang_word
//=========================================================
class xlang_word extends xlang_form
{
	var $_word_group_handler;
	var $_file_group_handler;
	var $_log_group_handler;
	var $_highlight;

	var $_modify_title_arr   = array();
	var $_modify_content_arr = array();
	var $_modify_lang_arr    = array();

	var $_MAX_NOTIFY_WORD    = 10;
	var $_MAX_NOTIFY_CONTENT = 100;

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_word()
{
	$this->xlang_form();

	$this->_word_group_handler =& xlang_word_group_handler::getInstance();
	$this->_file_group_handler =& xlang_file_group_handler::getInstance();
	$this->_log_group_handler  =& xlang_log_group_handler::getInstance();
	$this->_highlight          =& xlang_highlight::getInstance();
}

//---------------------------------------------------------
// execute
//---------------------------------------------------------
function get_op()
{
	if ( $this->_xlang_post->get_post( 'edit_no_use' ) ) 
	{	return 'edit_no_use';	}

	$op       = $this->_xlang_post->get_post_get( 'op' );
	$id       = $this->_xlang_post->get_post_get( 'id' );
	$dirname  = $this->_xlang_post->get_post_get( 'dirname' );
	$language = $this->_xlang_post->get_post_get( 'language' );
	$file     = $this->_xlang_post->get_post_get( 'file' );
	$word     = $this->_xlang_post->get_post_get( 'word' );

	if ( $op ) 
	{	return $op;	}

	if ( $id ) 
	{	return 'show';	}

	if ( $dirname && $language && $file && $word ) 
	{	return 'show';	}

	return '';
}

function execute( $op )
{
	switch ( $op )
	{
		case 'edit_word':
			$this->edit_word();
			break;

		case 'edit_word_all':
			$this->edit_word_all();
			break;

		case 'edit_no_use':
			$this->edit_no_use();
			break;
	}
}

//---------------------------------------------------------
// word
//---------------------------------------------------------
function edit_word()
{
	$dirname      =& $this->_xlang_post->get_post( 'dirname' );
	$language     =& $this->_xlang_post->get_post( 'language' );
	$file         =& $this->_xlang_post->get_post( 'file' );
	$word         =& $this->_xlang_post->get_post( 'word' );
	$contrast     =& $this->_xlang_post->get_post( 'contrast' );
	$lang_arr     =& $this->_xlang_post->get_post( 'lang' );
	$content_arr  =& $this->_xlang_post->get_post( 'content' );
	$note_arr     =& $this->_xlang_post->get_post( 'note' );
	$act_arr      =& $this->_xlang_post->get_post( 'act' );

	$this->clear_errors();

	$url = $this->_build_word_url( 'form_word', $dirname, $language, $file, $word, $contrast );

	foreach ( $content_arr as $id => $new_content )
	{
		$new_note = $note_arr[$id];
		$new_act  = $act_arr[$id];
		$this->_word_line( $id, $dirname, $lang_arr[ $id ], $file, $word, $new_content, $new_note, $new_act, true );
	}

	if ( $this->has_error() )
	{
		$msg  = 'DB Error <br />';
		$msg .= $this->get_format_error();
		redirect_header( $url, 3, $msg );
		exit();
	}

	$this->_notify( $url, $dirname );

	redirect_header( $url, 1, 'Finished' );
	exit();
}

function _word_line( $id, $dirname, $language, $file, $word, $new_content, $new_note=null, $new_act=null, $flag_note_act=false )
{
	$ret = false;

	if ( $id > 0 ) 
	{
		$row =& $this->_word_group_handler->get_row_by_id( $id );
		if ( !is_array($row) )
		{	return;	}

// not update if same
		if ( $row['w_content'] == $new_content )
		{
			if ( $flag_note_act )
			{
				if (( $row['w_note'] == $new_note )&&( $row['w_act'] == $new_act ))
				{	return;	}
			}
			else
			{	return;	}
		}

		$row['w_content'] = $new_content;

		if ( $flag_note_act )
		{
			$row['w_note'] = $new_note;
			$row['w_act']  = $new_act;
		}

		$ret = $this->_word_group_handler->update_word_with_log( $row );
		if ( !$ret )
		{
			$this->set_error( $this->_word_group_handler->get_errors() );
		}
	}
	else 
	{
// not insert if empty
		if ( $new_content == '' )
		{	return;	}

		$ret = $this->_word_group_handler->add(
			$dirname, $language, $file, $word, $new_content, $new_note, $new_act );
		if ( !$ret )
		{
			$this->set_error( $this->_word_group_handler->get_errors() );
		}
	}

	if ( $ret )
	{
		$this->_modify_title_arr[]   = $dirname.' > '.$language.' > '.$file.' > '.$word;
		$this->_modify_content_arr[] = $new_content;
		$this->_modify_lang_arr[]    = $language;
	}
}

function _notify( $url, $dirname )
{
	$count = count($this->_modify_title_arr);

	if ( $count == 0 )
	{	return;	}

	$max = $count;
	if ( $max > $this->_MAX_NOTIFY_WORD )
	{
		$max = $this->_MAX_NOTIFY_WORD;
	}

	$msg = "--- \n";

	for ( $i=0; $i<$max; $i++ )
	{
		$msg .= $this->_modify_title_arr[$i] ."\n\n";
		$msg .= xlang_substr( $this->_modify_content_arr[$i], 0, $this->_MAX_NOTIFY_CONTENT ) ."\n";
		$msg .= "--- \n";
	}

	$this->xoops_trigger_event_by_dirname( $url, $msg, $dirname, $this->_modify_lang_arr );

}

function _build_word_url( $op, $dirname, $language, $file, $word, $contrast )
{
	$url = $this->build_url( 'word.php', $op, $dirname, $language, $file, $word );
	if ( $contrast )
	{	$url .= '&amp;contrast='.$this->sanitize( $contrast );	}
	return $url;
}

//---------------------------------------------------------
// word all
//---------------------------------------------------------
function edit_word_all()
{
	$dirname      =& $this->_xlang_post->get_post( 'dirname' );
	$language     =& $this->_xlang_post->get_post( 'language' );
	$file         =& $this->_xlang_post->get_post( 'file' );
	$contrast     =& $this->_xlang_post->get_post( 'contrast' );
	$word_arr     =& $this->_xlang_post->get_post( 'word' );
	$lang_arr     =& $this->_xlang_post->get_post( 'lang' );
	$content_arr  =& $this->_xlang_post->get_post( 'content' );

	$this->clear_errors();

	$url = $this->_build_word_url( 'form_file', $dirname, $language, $file, null, $contrast );

	foreach ( $content_arr as $id => $new_content )
	{
		$this->_word_line( $id, $dirname, $lang_arr[ $id ], $file, $word_arr[ $id ], $new_content );
	}

	if ( $this->has_error() )
	{
		$msg  = 'DB Error <br />';
		$msg .= $this->get_format_error();
		redirect_header( $url, 3, $msg );
		exit();
	}

	$this->_notify( $url, $dirname );

	redirect_header( $url, 1, 'Finished' );
	exit();
}

//---------------------------------------------------------
// act
//---------------------------------------------------------
function edit_no_use()
{
	$dirname   =& $this->_xlang_post->get_post( 'dirname' );
	$language  =& $this->_xlang_post->get_post( 'language' );
	$file      =& $this->_xlang_post->get_post( 'file' );
	$contrast  =& $this->_xlang_post->get_post( 'contrast' );
	$id_arr    =& $this->_xlang_post->get_post( 'xlang_edit_form_id' );

	$this->clear_errors();

	$url = $this->_build_word_url( 'form_file', $dirname, $language, $file, null, $contrast );

	foreach ( $id_arr as $id )
	{
		$orig_row =& $this->_word_group_handler->get_word_by_id( $id );
		if ( !is_array($orig_row) )
		{	continue;	}

		$word_arr =& $this->_word_group_handler->get_rows_group_by_language(
			$orig_row['dirname'], $orig_row['file'], $orig_row['word'] );

		if ( !is_array($word_arr) )
		{	continue;	}

		foreach ( $word_arr as $row )
		{

// update if act
			if ( $row['w_act'] == 1 )
			{
				$ret = $this->_word_group_handler->update_no_act_with_log( $row );
				if ( !$ret )
				{
					$this->set_error( $this->_word_group_handler->get_errors() );
				}
			}
		}

	}

	if ( $this->has_error() )
	{
		$msg  = 'DB Error <br />';
		$msg .= $this->get_format_error();
		redirect_header( $url, 3, $msg );
		exit();
	}

	redirect_header( $url, 1, 'Finished' );
	exit();
}

//---------------------------------------------------------
// form
//---------------------------------------------------------
function show_form_file()
{
	global $xoopsTpl;

	$op            = $this->_xlang_post->get_post_get( 'op' );
	$dirname       = $this->_xlang_post->get_post_get( 'dirname' );
	$file          = $this->_xlang_post->get_post_get( 'file' );
	$orig_language = $this->_xlang_post->get_post_get( 'language' );
	$cont_language = $this->_xlang_post->get_post_get( 'contrast' );

	if ( empty($dirname) )
	{
		$this->set_template( $dirname, $language, $file );
		$xoopsTpl->assign( 'param_error', 'no dirname' );
		return false;
	}
	elseif ( empty($orig_language) )
	{
		$this->set_template( $dirname, $language, $file );
		$xoopsTpl->assign( 'param_error', 'no language' );
		return false;
	}
	elseif ( empty($file) )
	{
		$this->set_template( $dirname, $language, $file );
		$xoopsTpl->assign( 'param_error', 'no file' );
		return false;
	}

	$total = 0;
	$show_contrast       = false;
	$show_cont_select    = false;
	$show_new_select     = false;
	$show_undefined_list = false;
	$op_undefined        = false;

	if ( $op == 'undefined' )
	{	$op_undefined = true;	}

	if ( empty($cont_language) )
	{	$cont_language = $this->get_contrast_language( $orig_language );	}

	if ( $orig_language != $cont_language )
	{	$show_contrast = true;	}


// get word array
	$word_arr =& $this->_word_group_handler->get_words_by_two_languages(
		$dirname, $file, $orig_language, $cont_language );

	$undefined_array     =& $this->_word_group_handler->_word_both_undefined_array;
	$flag_not_exist_orig =& $this->_word_group_handler->_flag_not_exist_1;
	$flag_not_exist_cont =& $this->_word_group_handler->_flag_not_exist_2;

// normal or undefined 
	$word_both_arr =& $word_arr;
	if ( $op_undefined )
	{
		$word_both_arr =& $undefined_array;
	}

	if ( is_array($word_both_arr) )
	{
		$total = count($word_both_arr);
	}

	if ( $total == 0 )
	{
		$xoopsTpl->assign( 'param_error', 'no word record' );
		return false;
	}

	if ( $flag_not_exist_orig )
	{
		$msg = 'not exists '. $this->sanitize( $orig_language ) .' words';
		$xoopsTpl->assign( 'orig_error', $msg );
	}

	if ( $flag_not_exist_cont )
	{
		$msg = 'not exists '. $this->sanitize( $cont_language ) .' words';
		$xoopsTpl->assign( 'cont_error', $msg );
	}

// not define
	if ( !$op_undefined && is_array($undefined_array) && count($undefined_array) )
	{	$show_undefined_list = true;	}

// language selecter
	$lang_arr =& $this->_word_group_handler->get_languages_group_by_language( $dirname, $file );

	$cont_options =& $this->get_contrast_language_options( $lang_arr, $orig_language );
	$new_options  =& $this->get_new_language_options(      $lang_arr );

	if ( is_array($cont_options) && count($cont_options) )
	{	$show_cont_select = true;	}

	if ( is_array($new_options) && count($new_options) )
	{	$show_new_select = true;	}

	$word_latest =& $this->_word_group_handler->get_latest_word_by_dirname( $dirname, $orig_language, $file );
	if ( is_array( $word_latest ) )
	{
		$word_latest['word_s'] = $this->sanitize( $word_latest['word'] );
	}
	else
	{
		$word_latest = array(
			'id'     => 0,
			'time'   => 0,
			'word_s' => null,
		);
	}

	$file_time = $this->_file_group_handler->get_cached_file_time_by_file( $dirname, $orig_language, $file );
	$log_time  = $this->_log_group_handler->get_latest_word_time_by_dirname( $dirname, $orig_language, $file );
	$show_update = $this->judge_word_update( $word_latest['time'], $file_time, $log_time );

// word list
	$this->set_limit_by_post();
	$start = $this->get_pagenavi_start( $total );
	$end   = $this->get_pagenavi_end();

	$new_id    = -1;	// for not defined
	$word_list = array();

	for ( $i = $start; $i < $end; $i++ )
	{
		$id        = 0;
		$comm_act  = 0;
		$orig_id   = 0;
		$orig_act  = 0;
		$cont_id   = 0;
		$cont_act  = 0;

		$word     =& $word_both_arr[$i][0];
		$orig_row =& $word_both_arr[$i][1];
		$cont_row =& $word_both_arr[$i][2];

		if ( is_array($orig_row) )
		{
			$orig_id   = intval( $orig_row['id'] );
			$orig_act  = intval( $orig_row['w_act'] );

			$orig_line              = $orig_row;
			$orig_line['id']        = $orig_id;
			$orig_line['content_s'] = $this->sanitize( $orig_row['w_content'] );
			$orig_line['undefined'] = false;
		}
		else
		{
			$orig_line = array(
				'id'        => $new_id,
				'content_s' => null,
				'undefined' => true,
			);
			$new_id --;	// count down
		}

		if ( is_array($cont_row) )
		{
			$cont_id   = intval( $cont_row['id'] );
			$cont_act  = intval( $cont_row['w_act'] );

			$cont_line              = $cont_row;
			$cont_line['id']        = $cont_id;
			$cont_line['content_s'] = $this->sanitize( $cont_row['w_content'] );
			$cont_line['undefined'] = false;
		}
		else
		{
			$cont_line = array(
				'id'        => $new_id,
				'content_s' => null,
				'undefined' => true,
			);
			$new_id --;	// count down
		}

// id
		if ( $orig_id > 0 )
		{
			$id = $orig_id;
		}
		elseif ( $cont_id > 0 )
		{
			$id = $cont_id;
		}

// not act
		if ( $orig_act || $cont_act )
		{	$comm_act = 1;	}

		$comm = array(
			'id'     => $id,
			'word_s' => $this->sanitize( $word ),
			'act'    => $comm_act,
		);

		$word_list[] = array(
			'comm' => $comm,
			'orig' => $orig_line,
			'cont' => $cont_line,
		);

	}

	$file_id = $this->_file_group_handler->get_cached_file_id_by_file( $dirname, $orig_language, $file );

// navi
	$script  = $this->build_url( 'word.php', $op, $dirname, $orig_language, $file );
	$script .= '&contrast='. $cont_language;
	$script .= '&limit='. $this->_LIMIT;
	$page_navi = $this->_pagenavi->build( $script );

// set template
	$this->set_template( $dirname, $orig_language, $file );

	$xoopsTpl->assign( 'op_undefined',      $op_undefined );
	$xoopsTpl->assign( 'file_id',           $file_id );

	$xoopsTpl->assign( 'word_list',         $word_list );
	$xoopsTpl->assign( 'orig_language_s',   $this->sanitize( $orig_language ) );
	$xoopsTpl->assign( 'cont_language_s',   $this->sanitize( $cont_language ) );
	$xoopsTpl->assign( 'orig_image_s',      
		$this->sanitize( $this->get_language_image( $orig_language ) ) );
	$xoopsTpl->assign( 'cont_image_s',      
		$this->sanitize( $this->get_language_image( $cont_language ) ) );

	$xoopsTpl->assign( 'word_latest',       $word_latest );
	$xoopsTpl->assign( 'file_time',         $file_time );
	$xoopsTpl->assign( 'log_time',          $log_time );
	$xoopsTpl->assign( 'show_update',       $show_update );
	$xoopsTpl->assign( 'show_undefined',    $show_undefined_list );
	$xoopsTpl->assign( 'undefined_list',    $undefined_array );
	$xoopsTpl->assign( 'show_contrast',     $show_contrast );
	$xoopsTpl->assign( 'cont_options',      $cont_options );
	$xoopsTpl->assign( 'new_options',       $new_options );
	$xoopsTpl->assign( 'show_cont_select',  $show_cont_select );
	$xoopsTpl->assign( 'show_new_select',   $show_new_select );

	$xoopsTpl->assign( 'token',             $this->get_token() );
	$xoopsTpl->assign( 'token_error',       $this->_token_error );
	$xoopsTpl->assign( 'there_are',  sprintf( _XLANG_THERE_ARE, $total ) );

	$xoopsTpl->assign( 'start',             ( $start + 1 ) );
	$xoopsTpl->assign( 'end',               $end );
	$xoopsTpl->assign( 'limit',             $this->_LIMIT );
	$xoopsTpl->assign( 'page_navi',         $page_navi );
}

//---------------------------------------------------------
// form word
//---------------------------------------------------------
function show_form_word()
{
	global $xoopsTpl;

	$id            = $this->_xlang_post->get_post_get( 'id' );
	$dirname       = $this->_xlang_post->get_post_get( 'dirname' );
	$file          = $this->_xlang_post->get_post_get( 'file' );
	$word          = $this->_xlang_post->get_post_get( 'word' );
	$orig_language = $this->_xlang_post->get_post_get( 'language' );
	$cont_language = $this->_xlang_post->get_post_get( 'contrast' );

	$orig_word =& $this->_get_orig_word( $id, $dirname, $orig_language, $file, $word );
	if ( !is_array( $orig_word) )
	{
		$this->set_template( $dirname, $orig_language, $file, $word );
		$xoopsTpl->assign( 'param_error', $this->_param_error );
		return false;
	}

	$dirname        = $orig_word['dirname'];
	$file           = $orig_word['file'];
	$word           = $orig_word['word'];
	$orig_language  = $orig_word['language'];
	$orig_id        = intval( $orig_word['id'] );
	$orig_time      = intval( $orig_word['time'] );
	$orig_act       = intval( $orig_word['w_act'] );
	$orig_content   = $orig_word['w_content'];
	$orig_note      = $orig_word['w_note'];
	$orig_undefined = $orig_word['undefined'];

	$show_contrast    = false;
	$show_cont_select = false;
	$show_new_select  = false;

	if ( empty($cont_language) )
	{
		$cont_language = $this->get_contrast_language( $orig_language );
	}

	if ( $orig_language != $cont_language )
	{	$show_contrast = true;	}

	$cont_word =& $this->_get_word_with_default( $dirname, $cont_language, $file, $word );

	$cont_id        = intval( $cont_word['id'] );
	$cont_time      = intval( $cont_word['time'] );
	$cont_act       = intval( $cont_word['w_act'] );
	$cont_content   = $cont_word['w_content'];
	$cont_note      = $cont_word['w_note'];
	$cont_undefined = $cont_word['undefined'];

	if (( $orig_id <= 0 )&&( $cont_id <= 0 ))
	{
		$xoopsTpl->assign( 'param_error', 'no word record' );
		return false;
	}

	if ( $orig_id == 0 )
	{
		$orig_id = -1;
	}

	if ( $cont_id == 0 )
	{
		$cont_id = -2;
	}

	$act_options = array(
		0 => _XLANG_NO_USE,
		1 => _XLANG_USE,
	);

// language selecter
	$lang_arr =& $this->_word_group_handler->get_languages_group_by_language( $dirname, $file );

	$cont_options =& $this->get_contrast_language_options( $lang_arr, $orig_language );
	$new_options  =& $this->get_new_language_options(      $lang_arr );

	if ( is_array($cont_options) && count($cont_options) )
	{	$show_cont_select = true;	}

	if ( is_array($new_options) && count($new_options) )
	{	$show_new_select = true;	}

	$file_id = $this->_file_group_handler->get_cached_file_id_by_file( $dirname, $orig_language, $file );

	$this->set_template( $dirname, $orig_language, $file, $word );

	$xoopsTpl->assign( 'word_id',           $orig_id );
	$xoopsTpl->assign( 'file_id',           $file_id );
	$xoopsTpl->assign( 'act_options',       $act_options );

	$xoopsTpl->assign( 'orig_language_s',   $this->sanitize( $orig_language ) );
	$xoopsTpl->assign( 'orig_id',           $orig_id );
	$xoopsTpl->assign( 'orig_time',         $orig_time );
	$xoopsTpl->assign( 'orig_act',          $orig_act );
	$xoopsTpl->assign( 'orig_content_s',    $this->sanitize( $orig_content ) );
	$xoopsTpl->assign( 'orig_note_s',       $this->sanitize( $orig_note ) );
	$xoopsTpl->assign( 'orig_undefined',    $orig_undefined );
	$xoopsTpl->assign( 'orig_image_s',      
		$this->sanitize( $this->get_language_image( $orig_language ) ) );

	$xoopsTpl->assign( 'cont_language_s',   $this->sanitize( $cont_language ) );
	$xoopsTpl->assign( 'cont_id',           $cont_id );
	$xoopsTpl->assign( 'cont_time',         $cont_time );
	$xoopsTpl->assign( 'cont_act',          $cont_act );
	$xoopsTpl->assign( 'cont_content_s',    $this->sanitize( $cont_content ) );
	$xoopsTpl->assign( 'cont_note_s',       $this->sanitize( $cont_note ) );
	$xoopsTpl->assign( 'cont_undefined',    $cont_undefined );
	$xoopsTpl->assign( 'show_contrast',     $show_contrast );
	$xoopsTpl->assign( 'cont_image_s',      
		$this->sanitize( $this->get_language_image( $cont_language ) ) );

	$xoopsTpl->assign( 'cont_options',      $cont_options );
	$xoopsTpl->assign( 'new_options',       $new_options );
	$xoopsTpl->assign( 'show_cont_select',  $show_cont_select );
	$xoopsTpl->assign( 'show_new_select',   $show_new_select );
	$xoopsTpl->assign( 'token',             $this->get_token() );
	$xoopsTpl->assign( 'token_error',       $this->_token_error );

}

//---------------------------------------------------------
// show word
//---------------------------------------------------------
function show_word()
{
	global $xoopsTpl;

	$id        = $this->_xlang_post->get_post_get( 'id' );
	$dirname   = $this->_xlang_post->get_post_get( 'dirname' );
	$file      = $this->_xlang_post->get_post_get( 'file' );
	$word      = $this->_xlang_post->get_post_get( 'word' );
	$language  = $this->_xlang_post->get_post_get( 'language' );
	$keywords  = $this->_xlang_post->get_post_get( 'keywords' );

	$row =& $this->_get_orig_word( $id, $dirname, $language, $file, $word );
	if ( !is_array( $row) )
	{
		$this->set_template( $dirname, $language, $file, $word );
		$xoopsTpl->assign( 'param_error', $this->_param_error );
		return false;
	}

	if ( $row['undefined'] )
	{
		$this->set_template( $dirname, $language, $file, $word );
		$xoopsTpl->assign( 'param_error', 'no word record' );
		return false;
	}

	$dirname   = $row['dirname'];
	$language  = $row['language'];
	$file      = $row['file'];
	$word      = $row['word'];
	$time      = intval( $row['time'] );
	$act       = intval( $row['w_act'] );
	$content   = $row['w_content'];
	$note      = $row['w_note'];

	list( $keyword_array, $ignore_array ) = $this->parse_keywords( $keywords );
	$content_s = $this->_highlight->build_highlight_keyword_array(
		$this->sanitize( $content ), $keyword_array );
	$note_s    = $this->sanitize( $note );

	$this->set_template( $dirname, $language, $file, $word );

	$xoopsTpl->assign( 'search_query_s', $this->sanitize( $keywords ) );
	$xoopsTpl->assign( 'time',           $time );
	$xoopsTpl->assign( 'act',            $act );
	$xoopsTpl->assign( 'content_s',      $content_s );
	$xoopsTpl->assign( 'note_s',         $note_s );

}

function &_get_orig_word( $id, $dirname, $language, $file, $word )
{
	$false = false;

	$row = null;
	if ( $id )
	{
		$row =& $this->_word_group_handler->get_word_by_id( $id );
		if ( is_array($row) )
		{
			$row['undefined'] = false;
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
	elseif ( empty($word) )
	{
		$this->_param_error = 'no word';
		return $false;
	}

	if ( !is_array($row) )
	{
		$row =& $this->_get_word_with_default( $dirname, $language, $file, $word );
	}

	return $row;
}

function &_get_word_with_default( $dirname, $language, $file, $word )
{
	$row =& $this->_word_group_handler->get_word_by_word( $dirname, $language, $file, $word );
	if ( is_array($row) )
	{
		$row['undefined'] = false;
		return $row;
	}

	$row = array(
		'dirname'   => $dirname,
		'language'  => $language,
		'file'      => $file,
		'word'      => $word,
		'id'        => 0,
		'time'      => 0,
		'w_content' => null,
		'w_note'    => null,
		'w_act'     => 0,
		'undefined' => true,
	);

	return $row;
}

// --- class end ---
}

//=========================================================
// main
//=========================================================
$manage = new xlang_word();

$op = $manage->get_op();
switch ( $op )
{
	case 'edit_word':
	case 'edit_word_all':
	case 'edit_no_use':
		if ( $manage->check_token() )
		{
			$manage->check_login();
			$manage->execute( $op );
			exit();
		}

		$xoopsOption['template_main'] = 'xlang_word_file.html';
		include XOOPS_ROOT_PATH.'/header.php';
		$manage->show_form_file();
		break;

	case 'form_file':
	case 'undefined':
		$xoopsOption['template_main'] = 'xlang_word_file.html';
		include XOOPS_ROOT_PATH.'/header.php';
		$manage->show_form_file();
		break;

	case 'form':
	case 'form_word':
		$xoopsOption['template_main'] = 'xlang_word_form.html';
		include XOOPS_ROOT_PATH.'/header.php';
		$manage->show_form_word();
		break;

	case 'show':
	default:
		$xoopsOption['template_main'] = 'xlang_word_show.html';
		include XOOPS_ROOT_PATH.'/header.php';
		$manage->show_word();
		break;
}

$xoopsTpl->assign('execution_time', $manage->get_execution_time() );
$xoopsTpl->assign('memory_usage',   $manage->get_memory_usage() );
include XOOPS_ROOT_PATH.'/footer.php';
exit();
// --- main end ---

?>