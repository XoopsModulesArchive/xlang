<?php
// $Id: mail_handler.php,v 1.2 2007/12/19 17:10:24 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

//=========================================================
// class xlang_mail_handler
//=========================================================
class xlang_mail_handler extends xlang_handler
{

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_mail_handler()
{
	$this->xlang_handler();
	$this->set_table( 'xlang_mail' );
}

function &getInstance()
{
	static $instance;
	if (!isset($instance)) 
	{
		$instance = new xlang_mail_handler();
	}
	return $instance;
}

//---------------------------------------------------------
// insert
//---------------------------------------------------------
function insert( &$row )
{
	foreach ($row as $k => $v) 
	{	${$k} = $v;	}

	$sql  = 'INSERT INTO '.$this->_table.' (';
	$sql .= 'gid, ';
	$sql .= 'time, ';
	$sql .= 'm_content, ';
	$sql .= 'm_note ';
	$sql .= ') VALUES (';
	$sql .= intval($gid).', ';
	$sql .= intval($time).', ';
	$sql .= $this->quote($m_content).', ';
	$sql .= $this->quote($m_note).' ';
	$sql .= ')';

	$ret = $this->query( $sql );
	if ( !$ret )
	{	return false;	}

	return $this->_db->getInsertId();
}

//---------------------------------------------------------
// update
//---------------------------------------------------------
function update( &$row )
{
	foreach ($row as $k => $v) 
	{	${$k} = $v;	}

	$sql  = 'UPDATE '. $this->_table .' SET ';
	$sql .= 'gid='. intval($gid).', ';
	$sql .= 'time='. intval($time).', ';
	$sql .= 'm_content='. $this->quote($m_content).', ';
	$sql .= 'm_note='. $this->quote($m_note).' ';
	$sql .= 'WHERE id='.intval($id);

	return $this->query( $sql );
}

//----- class end -----
}

?>