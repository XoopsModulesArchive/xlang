<?php
// $Id: mail.php,v 1.6 2008/01/12 11:25:42 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

include_once 'header.php';

//=========================================================
// class xlang_mail
//=========================================================
class xlang_mail extends xlang_form
{
	var $_mail_group_handler;
	var $_highlight;

	var $_param_error = null;

	var $_MAX_NOTIFY_CONTENT = 200;

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_mail()
{
	$this->xlang_form();

	$this->_mail_group_handler =& xlang_mail_group_handler::getInstance();
	$this->_highlight          =& xlang_highlight::getInstance();
}

//---------------------------------------------------------
// edit
//---------------------------------------------------------
function get_op()
{
	return $this->_xlang_post->get_post_get( 'op' );
}

function edit_mail()
{
	$flag_error = false;

	$dirname     =& $this->_xlang_post->get_post( 'dirname' );
	$language    =& $this->_xlang_post->get_post( 'language' );
	$mail        =& $this->_xlang_post->get_post( 'mail' );
	$contrast    =& $this->_xlang_post->get_post( 'contrast' );
	$lang_arr    =& $this->_xlang_post->get_post( 'lang' );
	$content_arr =& $this->_xlang_post->get_post( 'content' );
	$note_arr    =& $this->_xlang_post->get_post( 'note' );

	$url = $this->build_url( 'mail.php', 'form', $dirname, $language, null, null, $mail );
	if ( $contrast )
	{	$url .= '&amp;contrast='.$this->sanitize( $contrast );	}

	$modify_title_arr   = array();
	$modify_content_arr = array();
	$modify_lang_arr    = array();

	foreach ( $content_arr as $id => $new_content )
	{
		$language = $lang_arr[ $id ];
		$new_note = $note_arr[ $id ];
		$ret      = false;
	
		if ( $id > 0 ) 
		{
			$row =& $this->_mail_group_handler->get_row_by_id( $id );
			if ( !is_array($row) )
			{	continue;	}

// not uodate if same
			if (( $row['m_content'] == $new_content )&&( $row['m_note'] == $new_note ))
			{	continue;	}

			$row['m_content'] = $new_content;
			$row['m_note']    = $new_note;

			$ret = $this->_mail_group_handler->update_mail_with_log( $row );
			if ( !$ret )
			{
				$this->set_error( $this->_mail_group_handler->get_errors() );
				$flag_error = true;
			}
		}
		else 
		{
// not insert if empty
			if ( $new_content == '' )
			{	continue;	}

			$ret = $this->_mail_group_handler->add(
				$dirname, $language, $mail, $new_content, $new_note );
			if ( !$ret )
			{
				$this->set_error( $this->_mail_group_handler->get_errors() );
				$flag_error = true;
			}
		}

		if ( $ret )
		{
			$modify_title_arr[]   = $dirname.' > '.$language.' > '.$mail;
			$modify_content_arr[] = $new_content;
			$modify_lang_arr[]    = $language;
		}
	}

	if ( $flag_error )
	{
		$msg  = 'DB Error <br />';
		$msg .= $this->get_format_error();
		redirect_header( $url, 3, $msg );
		exit();
	}

	$count = count($modify_title_arr);

	if ( $count > 0 )
	{
		$msg = "--- \n";
		for ( $i=0; $i<$count; $i++ )
		{
			$msg .= $modify_title_arr[$i] ."\n\n";
			$msg .= xlang_substr( $modify_content_arr[$i], 0, $this->_MAX_NOTIFY_CONTENT ) ."\n";
			$msg .= "--- \n";
		}

		$this->xoops_trigger_event_by_dirname( $url, $msg, $dirname, $modify_lang_arr );
	}

	redirect_header( $url, 1, 'Finished' );
	exit();
}

//---------------------------------------------------------
// show form
//---------------------------------------------------------
function show_form()
{
	global $xoopsTpl;

	$id            = $this->_xlang_post->get_post_get( 'id' );
	$dirname       = $this->_xlang_post->get_post_get( 'dirname' );
	$mail          = $this->_xlang_post->get_post_get( 'mail' );
	$orig_language = $this->_xlang_post->get_post_get( 'language' );
	$cont_language = $this->_xlang_post->get_post_get( 'contrast' );

	$orig_mail =& $this->_get_orig_mail( $id, $dirname, $orig_language, $mail );
	if ( !is_array( $orig_mail) )
	{
		$this->set_template( $dirname, $language, null, null, $mail );
		$xoopsTpl->assign( 'param_error', $this->_param_error );
		return false;
	}

	$dirname        = $orig_mail['dirname'];
	$mail           = $orig_mail['mail'];
	$orig_language  = $orig_mail['language'];
	$orig_id        = intval( $orig_mail['id'] );
	$orig_time      = intval( $orig_mail['time'] );
	$orig_content   = $orig_mail['m_content'];
	$orig_note      = $orig_mail['m_note'];
	$orig_undefined = $orig_mail['undefined'];

	$show_contrast    = false;
	$show_cont_select = false;
	$show_new_select  = false;

	if ( empty($cont_language) )
	{
		$cont_language = $this->get_contrast_language( $orig_language );
	}

	if ( $orig_language != $cont_language )
	{	$show_contrast = true;	}

	$cont_mail =& $this->_get_mail_with_default( $dirname, $cont_language, $mail );

	$cont_id        = intval( $cont_mail['id'] );
	$cont_time      = intval( $cont_mail['time'] );
	$cont_content   = $cont_mail['m_content'];
	$cont_note      = $cont_mail['m_note'];
	$cont_undefined = $cont_mail['undefined'];

	if (( $orig_id <= 0 )&&( $cont_id <= 0 ))
	{
		$this->set_template( $dirname, $language, null, null, $mail );
		$xoopsTpl->assign( 'param_error', 'no mail record' );
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

// language selecter
	$lang_arr =& $this->_mail_group_handler->get_languages_group_by_language( $dirname, $mail );

	$cont_options =& $this->get_contrast_language_options( $lang_arr, $orig_language );
	$new_options  =& $this->get_new_language_options(      $lang_arr );

	if ( is_array($cont_options) && count($cont_options) )
	{	$show_cont_select = true;	}

	if ( is_array($new_options) && count($new_options) )
	{	$show_new_select = true;	}

	$this->set_template( $dirname, $orig_language, null, null, $mail );

	$xoopsTpl->assign( 'orig_language_s',   $this->sanitize( $orig_language ) );
	$xoopsTpl->assign( 'orig_image_s',      $this->sanitize( $this->get_language_image( $orig_language ) ) );
	$xoopsTpl->assign( 'orig_id',           $orig_id );
	$xoopsTpl->assign( 'orig_time',         $orig_time );
	$xoopsTpl->assign( 'orig_content_s',    $this->sanitize( $orig_content ) );
	$xoopsTpl->assign( 'orig_note_s',       $this->sanitize( $orig_note ) );
	$xoopsTpl->assign( 'orig_undefined',    $orig_undefined );

	$xoopsTpl->assign( 'cont_language_s',   $this->sanitize( $cont_language ) );
	$xoopsTpl->assign( 'cont_image_s',      $this->sanitize( $this->get_language_image( $cont_language ) ) );
	$xoopsTpl->assign( 'cont_id',           $cont_id );
	$xoopsTpl->assign( 'cont_time',         $cont_time );
	$xoopsTpl->assign( 'cont_content_s',    $this->sanitize( $cont_content ) );
	$xoopsTpl->assign( 'cont_note_s',       $this->sanitize( $cont_note ) );
	$xoopsTpl->assign( 'cont_undefined',    $cont_undefined );
	$xoopsTpl->assign( 'show_contrast',     $show_contrast );

	$xoopsTpl->assign( 'cont_options',      $cont_options );
	$xoopsTpl->assign( 'new_options',       $new_options );
	$xoopsTpl->assign( 'show_cont_select',  $show_cont_select );
	$xoopsTpl->assign( 'show_new_select',   $show_new_select );
	$xoopsTpl->assign( 'token',             $this->get_token() );
	$xoopsTpl->assign( 'token_error',       $this->_token_error );

}

//---------------------------------------------------------
// show
//---------------------------------------------------------
function show_mail()
{
	global $xoopsTpl;

	$id        = $this->_xlang_post->get_post_get( 'id' );
	$dirname   = $this->_xlang_post->get_post_get( 'dirname' );
	$mail      = $this->_xlang_post->get_post_get( 'mail' );
	$language  = $this->_xlang_post->get_post_get( 'language' );
	$keywords  = $this->_xlang_post->get_post_get( 'keywords' );

	$xoopsTpl->assign( 'module_name', $this->sanitize( $this->_MODULE_NAME ) );

	$row =& $this->_get_orig_mail( $id, $dirname, $language, $mail );
	if ( !is_array( $row) )
	{
		$this->set_template( $dirname, $language, null, null, $mail );
		$xoopsTpl->assign( 'param_error', $this->_param_error );
		return false;
	}

	if ( $row['undefined'] )
	{
		$this->set_template( $dirname, $language, null, null, $mail );
		$xoopsTpl->assign( 'param_error', 'no mail record' );
		return false;
	}

	$dirname   = $row['dirname'];
	$language  = $row['language'];
	$mail      = $row['mail'];
	$mail_id   = intval( $row['id'] );
	$time      = intval( $row['time'] );
	$content   = $row['m_content'];
	$note      = $row['m_note'];

	list( $keyword_array, $ignore_array ) = $this->parse_keywords( $keywords );
	$content_s = $this->_highlight->build_highlight_keyword_array(
		$this->sanitize( $content ), $keyword_array );
	$note_s    = $this->sanitize( $note );

	$this->set_template( $dirname, $language, null, null, $mail );

	$xoopsTpl->assign( 'search_query_s', $this->sanitize( $keywords ) );
	$xoopsTpl->assign( 'time',           $time );
	$xoopsTpl->assign( 'content_s',      $content_s );
	$xoopsTpl->assign( 'note_s',         $note_s );

}

function &_get_orig_mail( $id, $dirname, $language, $mail )
{
	$false = false;

	$row = null;
	if ( $id )
	{
		$row =& $this->_mail_group_handler->get_mail_by_id( $id );
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
	elseif ( empty($mail) )
	{
		$this->_param_error = 'no mail';
		return $false;
	}

	if ( !is_array($row) )
	{
		$row =& $this->_get_mail_with_default( $dirname, $language, $mail );
	}

	return $row;
}

function &_get_mail_with_default( $dirname, $language, $mail )
{
	$row =& $this->_mail_group_handler->get_mail_by_mail( $dirname, $language, $mail );

	if ( is_array($row) )
	{
		$row['undefined'] = false;
		return $row;
	}

	$row = array(
		'dirname'   => $dirname,
		'language'  => $language,
		'mail'      => $mail,
		'id'        => 0,
		'time'      => 0,
		'm_content' => null,
		'm_note'    => null,
		'undefined' => true,
	);

	return $row;
}

// --- class end ---
}

//=========================================================
// main
//=========================================================
$manage = new xlang_mail();

switch ( $manage->get_op() )
{
	case 'edit':
		if ( $manage->check_token() )
		{
			$manage->check_login();
			$manage->edit_mail();
			exit();
		}

		$xoopsOption['template_main'] = 'xlang_mail_form.html';
		include XOOPS_ROOT_PATH.'/header.php';
		$manage->show_form();
		break;	

	case 'form':
		$xoopsOption['template_main'] = 'xlang_mail_form.html';
		include XOOPS_ROOT_PATH.'/header.php';
		$manage->show_form();
		break;

	case 'show':
	default:
		$xoopsOption['template_main'] = 'xlang_mail_show.html';
		include XOOPS_ROOT_PATH.'/header.php';
		$manage->show_mail();
		break;
}

$xoopsTpl->assign('execution_time', $manage->get_execution_time() );
$xoopsTpl->assign('memory_usage',   $manage->get_memory_usage() );
include XOOPS_ROOT_PATH.'/footer.php';
exit();
// --- main end ---

?>