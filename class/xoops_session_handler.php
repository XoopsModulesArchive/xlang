<?php
// $Id: xoops_session_handler.php,v 1.1 2007/12/17 12:01:53 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

//=========================================================
// class xlang_xoops_session_handler
//=========================================================
class xlang_xoops_session_handler extends xlang_handler
{

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_xoops_session_handler()
{
	$this->xlang_handler();
	$this->set_table( 'session' );
}

function &getInstance()
{
	static $instance;
	if (!isset($instance)) 
	{
		$instance = new xlang_xoops_session_handler();
	}
	return $instance;
}

//---------------------------------------------------------
// session_set_save_handler
//---------------------------------------------------------
function open( $save_path, $session_name )
{
	return true;
}

function close()
{
	return true;
}

function read( $sess_id )
{
	$sql  = 'SELECT * FROM '.$this->_table.' WHERE ';
	$sql .= 'sess_id='. $this->quote($sess_id);

	$row =& $this->get_row_by_sql( $sql );
	if ( isset( $row['sess_data'] ) )
	{	return  $row['sess_data'];	}

	return '';
}

function write( $sess_id, $sess_data )
{
	return true;
}

function destroy( $sess_id )
{
	return true;
}

function gc($expire)
{
	return true;
}

//----- class end -----
}

?>