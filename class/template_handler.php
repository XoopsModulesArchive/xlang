<?php
// $Id: template_handler.php,v 1.1 2007/12/17 12:01:52 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

//=========================================================
// class xlang_template_handler
//=========================================================
class xlang_template_handler extends xlang_handler
{

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_template_handler()
{
	$this->xlang_handler();
	$this->set_table( 'xlang_template' );
}

function &getInstance()
{
	static $instance;
	if (!isset($instance)) 
	{
		$instance = new xlang_template_handler();
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
	$sql .= 't_content ';
	$sql .= ') VALUES (';
	$sql .= intval($gid).', ';
	$sql .= intval($time).', ';
	$sql .= $this->quote($t_content).' ';
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
	$sql .= 't_content='. $this->quote($t_content).' ';
	$sql .= 'WHERE id='.intval($id);

	return $this->query( $sql );
}

//----- class end -----
}

?>