<?php
// $Id: log_group_handler.php,v 1.4 2007/12/29 05:48:20 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

//=========================================================
// class xlang_log_group_handler
//=========================================================
class xlang_log_group_handler extends xlang_log_handler
{

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_log_group_handler()
{
	$this->xlang_log_handler();
	$this->set_group_handler();
}

function &getInstance()
{
	static $instance;
	if (!isset($instance)) 
	{
		$instance = new xlang_log_group_handler();
	}
	return $instance;
}

//---------------------------------------------------------
// insert
//---------------------------------------------------------
function insert_manage_record( &$row )
{
	return $this->insert_log( $row );
}

function insert_log( &$row )
{
	$row['time'] = time();

	return $this->insert( $row );
}

//---------------------------------------------------------
// update
//---------------------------------------------------------
function update_manage_record( &$row )
{
	return $this->update_log( $row );
}

function update_log( &$row )
{
	return $this->update( $row );
}

//---------------------------------------------------------
// delete
//---------------------------------------------------------
function delete_manage_record( &$row )
{
	return $this->delete_log( $row );
}

function delete_log( &$row )
{
	return $this->delete_by_id( $row['id'] );
}

//---------------------------------------------------------
// count
//---------------------------------------------------------
function get_manage_count( $dirname, $language, $file=null, $word=null, $mail=null )
{
	return $this->get_count_by_dirname( null, $dirname, $language, $file, $word, $mail );
}

function get_count_by_dirname( $kind, $dirname, $language=null, $file=null, $word=null, $mail=null )
{
	return $this->get_count_group_by_dirname(
		$kind, $dirname, $language, $file, $word, $mail );
}

function get_latest_word_time_by_dirname( $dirname, $language=null, $file=null, $word=null )
{
	$row =& $this->get_log_latest_by_dirname(
		_XLANG_C_KIND_WORD, $dirname, $language, $file );
	if ( is_array( $row ) )
	{
		return intval( $row['time'] );
	}
	return 0;
}

//---------------------------------------------------------
// get
//---------------------------------------------------------
function &get_manage_rows( $dirname, $language=null, $file=null, $word=null, $mail=null, $limit=0, $offset=0 )
{
	return $this->get_logs_by_dirname(
			null, $dirname, $language, $file, $word, $mail, 
			'ASC', $limit, $offset );
}

function &get_log_by_id( $id )
{
	return $this->get_row_group_by_id( $id );
}

function &get_log_latest_by_dirname( $kind, $dirname, $language=null, $file=null, $word=null )
{
	$sql  = 'SELECT '. $this->build_sql_select_group();
	$sql .= ' FROM '.  $this->build_sql_from_group();
	$sql .= ' WHERE ';
	$sql .= $this->build_sql_where_group( $kind, $dirname, $language, $file, $word );
	$sql .= ' ORDER BY h.time DESC, h.id DESC';
	return $this->get_row_by_sql( $sql, 1 );
}

function &get_logs_all( $limit=0, $offset=0 )
{
	return $this->get_rows_group_all( $limit, $offset );
}

function &get_logs_by_dirname( $kind, $dirname, $language=null, $file=null, $word=null, $mail=null, $order='DESC', $limit=0, $offset=0 )
{
	$sql  = 'SELECT '. $this->build_sql_select_group();
	$sql .= ' FROM '.  $this->build_sql_from_group();
	$sql .= ' WHERE ';
	$sql .= $this->build_sql_where_group( $kind, $dirname, $language, $file, $word, $mail );
	$sql .= ' ORDER BY h.id '. $order;
	return $this->get_rows_by_sql( $sql, $limit, $offset );
}

//----- class end -----
}

?>