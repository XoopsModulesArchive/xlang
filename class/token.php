<?php
// $Id: token.php,v 1.2 2007/12/25 02:28:07 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

//=========================================================
// class xlang_token
//=========================================================
class xlang_token
{
	var $_xlang_post;

	var $_SESSION_NAME = 'xlang_token';
	var $_TOKEN_NAME   = 'XLANG_TOKEN';

	var $_original_xoops_uid = 0;
	var $_cached_token = null;
	var $_token_error  = null;

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_token()
{
	$this->_xlang_post =& xlang_post::getInstance();
}

function &getInstance()
{
	static $instance;
	if (!isset($instance)) 
	{
		$instance = new xlang_token();
	}
	return $instance;
}

//---------------------------------------------------------
// XoopsGTicket
//---------------------------------------------------------
function build_gticket_html_token()
{
// get same token on one page, becuase max ticket is 10
	if ( $this->_cached_token )
	{
		return $this->_cached_token;
	}

	global $xoopsGTicket;
	$text = '';
	if ( is_object($xoopsGTicket) )
	{
		$text = $xoopsGTicket->getTicketHtml()."\n";
		$this->_cached_token = $text;
	}
	return $text;
}

function get_gticket_token()
{
	global $xoopsGTicket;
	if ( is_object($xoopsGTicket) )
	{
		return $xoopsGTicket->issue();
	}
	return null;
}

function check_gticket_token( $allow_repost=false )
{
	global $xoopsGTicket;
	if ( is_object($xoopsGTicket) )
	{
		if ( ! $xoopsGTicket->check( true , '',  $allow_repost ) ) 
		{
			$this->_token_error = $xoopsGTicket->getErrors();
			return false;
		}
	}
	return true;
}

//---------------------------------------------------------
// original session
//---------------------------------------------------------
function original_session_start()
{
	$sess_handler =& xlang_xoops_session_handler::getInstance();

	session_set_save_handler(
		array(&$sess_handler, 'open'),
		array(&$sess_handler, 'close'),
		array(&$sess_handler, 'read'), 
		array(&$sess_handler, 'write'), 
		array(&$sess_handler, 'destroy'), 
		array(&$sess_handler, 'gc')
	);

	session_start();

	if ( isset($_SESSION['xoopsUserId']) ) 
	{	$this->_original_xoops_uid = $_SESSION['xoopsUserId'];	}

}

function get_original_xoops_uid()
{
	return $this->_original_xoops_uid;
}

//---------------------------------------------------------
// original token
//---------------------------------------------------------
function check_original_token()
{
	$token = $this->_xlang_post->get_post( $this->_TOKEN_NAME );
	if ( empty($token) )
	{	return false;	}

	if ( isset($_SESSION[ $this->_SESSION_NAME ]) && 
	     (     $_SESSION[ $this->_SESSION_NAME ] == md5( $token ) ))
	{	return true;	}

	return false;
}

function get_original_html_token()
{
// create token
	list( $usec , $sec ) = explode( " " , microtime() ) ;
	$token = md5( $usec . rand() . $sec ) ;

// set session
	$_SESSION[ $this->_SESSION_NAME ] = md5( $token );

	$str = '<input type="hidden" name="'. $this->_TOKEN_NAME .'" value="'. $token .'" />';
	return $str;
}

// --- class end ---
}

?>