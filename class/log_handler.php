<?php
// $Id: log_handler.php,v 1.1 2007/12/17 12:01:50 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

//=========================================================
// class xlang_log_handler
//=========================================================
class xlang_log_handler extends xlang_handler
{

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_log_handler()
{
	$this->xlang_handler();
	$this->set_table( 'xlang_log' );
	$this->init_xoops_param();

}

function &getInstance()
{
	static $instance;
	if (!isset($instance)) 
	{
		$instance = new xlang_log_handler();
	}
	return $instance;
}

// in original session
function set_uid( $uid )
{
	$this->_xoops_uid = intval( $uid );
}

//---------------------------------------------------------
// insert
//---------------------------------------------------------
function write( $gid, $op, $content )
{
	$row = array( 
		'gid'       => $gid,
		'time'      => time(),
		'uid'       => $this->_xoops_uid,
		'l_op'      => $op,
		'l_content' => $content,
	);
	return $this->insert( $row );
}

function insert( &$row )
{
	foreach ($row as $k => $v) 
	{	${$k} = $v;	}

	$sql  = 'INSERT INTO '.$this->_table.' (';
	$sql .= 'gid, ';
	$sql .= 'time, ';
	$sql .= 'uid, ';
	$sql .= 'l_op, ';
	$sql .= 'l_content ';
	$sql .= ') VALUES (';
	$sql .= intval($gid).', ';
	$sql .= intval($time).', ';
	$sql .= intval($uid).', ';
	$sql .= intval($l_op).', ';
	$sql .= $this->quote($l_content).' ';
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
	$sql .= 'uid='. intval($uid).', ';
	$sql .= 'l_op='. intval($l_op).', ';
	$sql .= 'l_content='. $this->quote($l_content).' ';
	$sql .= 'WHERE id='.intval($id);

	return $this->query( $sql );
}

//---------------------------------------------------------
// get
//---------------------------------------------------------
function get_count( &$gid_array )
{
	$sql  = 'SELECT count(*) FROM '.$this->_table.' WHERE ';
	$sql .= 'gid=' . implode( ' OR gid=', $gid_arr );
	return $this->get_count_by_sql( $sql );
}

function &get_logs( &$gid_array, $limit=0, $start=0 )
{
	$sql  = 'SELECT * FROM '.$this->_table.' WHERE ';
	$sql .= 'gid=' . implode( ' OR gid=', $gid_arr );
	$sql .= ' ORDER BY time DESC, id DESC';
	return $this->get_rows_by_sql( $sql, $limit, $start );
}

//---------------------------------------------------------
// item
//---------------------------------------------------------
function build_short_log( &$row, $length )
{
	$text = $this->shorten_strings_with_nl2br( $row['l_content'], $length );

	switch ($row['l_op'] )
	{
		case _XLANG_C_OP_FILE_DELETE:
			$text = '<b> FILE DELETED </b>';
			break;

		case _XLANG_C_OP_MAIL_DELETE:
			$text = '<b> MAIL TEMPLATE DELETED </b>';
			break;

		case _XLANG_C_OP_WORD_DELETE:
			$text = '<b> WORD DELETED </b>';
			break;	

		case _XLANG_C_OP_WORD_NO_ACT:
			$text = '<i> SET NO USE </i>';
			break;	
	}
	
	return $text;
}

//----- class end -----
}

?>