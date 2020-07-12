<?php
// $Id: word_handler.php,v 1.2 2007/12/19 17:10:24 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

//=========================================================
// class xlang_word_handler
//=========================================================
class xlang_word_handler extends xlang_handler
{

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_word_handler()
{
	$this->xlang_handler();
	$this->set_table( 'xlang_word' );
}

function &getInstance()
{
	static $instance;
	if (!isset($instance)) 
	{
		$instance = new xlang_word_handler();
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
	$sql .= 'w_act, ';
	$sql .= 'w_content, ';
	$sql .= 'w_note ';
	$sql .= ') VALUES (';
	$sql .= intval($gid).', ';
	$sql .= intval($time).', ';
	$sql .= intval($w_act).', ';
	$sql .= $this->quote($w_content).', ';
	$sql .= $this->quote($w_note).' ';
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
	$sql .= 'w_act='. intval($w_act).', ';
	$sql .= 'w_content='. $this->quote($w_content).', ';
	$sql .= 'w_note='. $this->quote($w_note).' ';
	$sql .= 'WHERE id='.intval($id);

	return $this->query( $sql );
}

//----- class end -----
}

?>