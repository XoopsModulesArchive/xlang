<?php
// $Id: log.php,v 1.3 2007/12/28 01:28:01 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

include_once 'header.php';

//=========================================================
// class xlang_log
//=========================================================
class xlang_log extends xlang_form
{
	var $_word_group_handler;
	var $_log_group_handler;

	var $_LENGTH = 200;

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_log()
{
	$this->xlang_form();

	$this->_word_group_handler =& xlang_word_group_handler::getInstance();
	$this->_log_group_handler  =& xlang_log_group_handler::getInstance();

}

//---------------------------------------------------------
// main
//---------------------------------------------------------
function main()
{
	switch ( $this->_get_op() )
	{
		case 'detail':
			$this->_show_detail();
			break;

		default:
			$this->_show_log();
			break;
	}
}

function _get_op()
{
	$id = $this->_xlang_post->get_get( 'id' );
	if ( $id )
	{	return 'detail';	}

	return '';
}

//---------------------------------------------------------
// show
//---------------------------------------------------------
function _show_log()
{
	global $xoopsTpl;

	$op       =& $this->_xlang_post->get_get( 'op' );
	$dirname  =& $this->_xlang_post->get_get( 'dirname' );
	$language =& $this->_xlang_post->get_get( 'language' );
	$file     =& $this->_xlang_post->get_get( 'file' );
	$word     =& $this->_xlang_post->get_get( 'word' );
	$mail     =& $this->_xlang_post->get_get( 'mail' );

	$this->set_template( $dirname, $language, $file, $word, $mail );

	if ( empty($dirname) )
	{
		$xoopsTpl->assign( 'param_error', 'no dirname' );
		return false;
	}
	if ( empty($language) )
	{
		$xoopsTpl->assign( 'param_error', 'no language' );
		return false;
	}
	if ( empty($file) && empty($mail) )
	{
		$xoopsTpl->assign( 'param_error', 'no file' );
		return false;
	}

	$log_arr =& $this->_log_group_handler->get_logs_by_dirname(
		null, $dirname, $language, $file, $word, $mail, 'DESC', $this->_LIMIT );

	foreach ( $log_arr as $row )
	{
		$this->_log_line( $row );
	}
}

function _log_line( &$row, $op=null )
{
	global $xoopsTpl;

	$id  = intval( $row['id'] );
	$uid = intval( $row['uid'] );

	if ( $op == 'detail' )
	{
		$summary_s = nl2br( $this->sanitize( $row['l_content'] ) );
	}
	else
	{
		$summary_s = $this->_log_group_handler->build_short_log( $row, $this->_LENGTH );
	}

	$row['dirname_s']  = $this->sanitize( $row['dirname'] );
	$row['language_s'] = $this->sanitize( $row['language'] );
	$row['file_s']     = $this->sanitize( $row['file'] );
	$row['word_s']     = $this->sanitize( $row['word'] );
	$row['mail_s']     = $this->sanitize( $row['mail'] );
	$row['uname_s']    = $this->sanitize( $this->get_xoops_user_name( $uid ) );
	$row['summary_s']  = $summary_s;

	$xoopsTpl->append( 'log_list', $row );
}

//---------------------------------------------------------
// show detail
//---------------------------------------------------------
function _show_detail()
{
	global $xoopsTpl;

	$id = intval( $this->_xlang_post->get_get( 'id' ) );

	$row =& $this->_log_group_handler->get_log_by_id( $id );
	if ( !is_array($row) )
	{
		$this->set_template();
		$xoopsTpl->assign( 'param_error', 'no record' );
		return false;
	}

	$dirname  = $row['dirname'];
	$language = $row['language'];
	$file     = $row['file'];
	$word     = $row['word'];
	$mail     = $row['mail'];

	$this->set_template( $dirname, $language, $file, $word, $mail );
	$this->_log_line( $row, 'detail' );
}

// --- class end ---
}

//=========================================================
// main
//=========================================================
$xlang_log = new xlang_log();

// --- template start ---
$xoopsOption['template_main'] = 'xlang_log.html';
include XOOPS_ROOT_PATH.'/header.php';

$xlang_log->main();

$xoopsTpl->assign('execution_time', $xlang_log->get_execution_time() );
$xoopsTpl->assign('memory_usage',   $xlang_log->get_memory_usage() );
include XOOPS_ROOT_PATH.'/footer.php';
exit();
// --- main end ---

?>