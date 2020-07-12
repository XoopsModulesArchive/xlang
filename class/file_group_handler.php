<?php
// $Id: file_group_handler.php,v 1.7 2007/12/31 10:51:46 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

//=========================================================
// class xlang_file_group_handler
//=========================================================
class xlang_file_group_handler extends xlang_file_handler
{

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_file_group_handler()
{
	$this->xlang_file_handler();
	$this->set_group_handler();
	$this->set_log_handler();
}

function &getInstance()
{
	static $instance;
	if (!isset($instance)) 
	{
		$instance = new xlang_file_group_handler();
	}
	return $instance;
}

//---------------------------------------------------------
// init
//---------------------------------------------------------
function set_uid( $uid )
{
	$this->_log_handler->set_uid( $uid );
}

//---------------------------------------------------------
// add
//---------------------------------------------------------
function add_with_exist_check( $dirname, $language, $file, $content, $note )
{
	$row =& $this->get_file_by_file( $dirname, $language, $file );
	if ( is_array($row) )
	{
		$row['f_content'] = $content;
		$row['f_note']    = $note;
		$this->_id        = $row['id'];

		$ret = $this->update_file_with_log( $row );
		if ( !$ret )
		{	return 0;	}
		return 2;
	}

	return $this->add( $dirname, $language, $file, $content, $note );
}

function add( $dirname, $language, $file, $content, $note )
{
	$ret = $this->_group_handler->add_with_exist_check(
		_XLANG_C_KIND_DIRNAME, $dirname );
	if ( !$ret )
	{
		$this->set_error( $this->_group_handler->get_errors() );
		return 0;
	}

	$ret = $this->_group_handler->add_with_exist_check(
		_XLANG_C_KIND_LANGUAGE, $dirname, $language );
	if ( !$ret )
	{
		$this->set_error( $this->_group_handler->get_errors() );
		return 0;
	}

	$gid = $this->_group_handler->add_with_exist_check(
		_XLANG_C_KIND_FILE, $dirname, $language, $file );
	if ( !$gid )
	{
		$this->set_error( $this->_group_handler->get_errors() );
		return 0;
	}

	$row_new = array(
		'gid'       => $gid,
		'f_content' => $content,
		'f_note'    => $note,
	);

	$ret = $this->insert_file_with_log( $row_new );
	if ( !$ret )
	{	return 0;	}

	return 1;
}

//---------------------------------------------------------
// insert
//---------------------------------------------------------
function insert_manage_record( &$row )
{
	return $this->insert_file_with_log( $row );
}

function insert_file_with_log( &$row )
{
	$row['time'] = time();

	$newid = $this->insert( $row );
	if ( !$newid )
	{	return false;	}

	$this->_log_handler->write( $row['gid'], _XLANG_C_OP_FILE_INSERT, $row['f_content'] );

	$this->_id = $newid;
	return $newid;
}

//---------------------------------------------------------
// update
//---------------------------------------------------------
function update_manage_record( &$row )
{
	return $this->update_file_with_log( $row );
}

function update_file_with_log( &$row )
{
	$row['time'] = time();

	$ret = $this->update( $row );
	if ( !$ret )
	{	return false;	}

	$this->_log_handler->write( $row['gid'], _XLANG_C_OP_FILE_UPDATE, $row['f_content'] );

	return true;
}

//---------------------------------------------------------
// delete
//---------------------------------------------------------
function delete_manage_record( &$row )
{
	return $this->delete_file_with_log( $row );
}

function delete_file_with_log( &$row )
{
	$ret = $this->delete_by_id( $row['id'] );
	if ( !$ret )
	{	return false;	}

	$this->_log_handler->write( $row['gid'], _XLANG_C_OP_FILE_DELETE, '' );

	return true;
}

//---------------------------------------------------------
// count
//---------------------------------------------------------
function get_manage_count( $dirname, $language, $file=null, $word=null, $mail=null )
{
	return $this->get_count_by_dirname( $dirname, $language );
}

function get_count_by_dirname( $dirname, $language=null, $file=null )
{
	return $this->get_count_group_by_dirname(
		_XLANG_C_KIND_FILE, $dirname, $language, $file );
}

function get_cached_file_id_by_file( $dirname, $language=null, $file=null )
{
	$row =& $this->get_cached_file_by_file( $dirname, $language, $file );
	if ( is_array( $row ) )
	{
		return intval( $row['id'] );
	}
	return 0;
}

function get_cached_file_time_by_file( $dirname, $language, $file )
{
	$row =& $this->get_cached_file_by_file( $dirname, $language, $file );
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
	return $this->get_files_by_dirname( $dirname, $language, $limit, $offset );
}

function &get_file_by_id( $id )
{
	return $this->get_row_group_by_id( $id );
}

function &get_cached_file_by_file( $dirname, $language, $file )
{
	if ( isset( $this->_cached[ $dirname ][ $language ][ $file ] ) )
	{
		return $this->_cached[ $dirname ][ $language ][ $file ];
	}
	$row =& $this->get_file_by_file( $dirname, $language, $file );
	$this->_cached[ $dirname ][ $language ][ $file ] = $row;
	return $row;
}

function &get_file_by_file( $dirname, $language, $file )
{
	return $this->get_row_group_by_dirname( 
		_XLANG_C_KIND_FILE, $dirname, $language, $file );
}

function &get_files_all( $limit=0, $offset=0 )
{
	return $this->get_rows_group_all( $limit, $offset );
}

function &get_files_by_dirname( $dirname, $language=null, $limit=0, $offset=0 )
{
	return $this->get_rows_group_by_dirname(
		_XLANG_C_KIND_FILE, $dirname, $language, null, null, null, $limit, $offset );
}

function &get_files_latest_by_dirname( $dirname=null, $language=null, $limit=0, $offset=0 )
{
	$sql  = 'SELECT '. $this->build_sql_select_group();
	$sql .= ' FROM '.  $this->build_sql_from_group();
	$sql .= ' WHERE ';
	$sql .= $this->build_sql_where_group( _XLANG_C_KIND_FILE, $dirname, $language );
	$sql .= ' ORDER BY h.time DESC, h.id DESC';
	return $this->get_rows_by_sql( $sql, $limit, $offset );
}

//---------------------------------------------------------
// search
//---------------------------------------------------------
function get_count_by_search( &$query_array, $andor )
{
	$sql  = 'SELECT count(*) ';
	$sql .= ' FROM '.  $this->build_sql_from_group();
	$sql .= ' WHERE ';
	$sql .= $this->build_search_where( $query_array, $andor );
	return $this->get_count_by_sql( $sql );
}

function &get_files_by_search( &$query_array, $andor, $limit=0, $offset=0 )
{
	$sql  = 'SELECT '. $this->build_sql_select_group();
	$sql .= ' FROM '.  $this->build_sql_from_group();
	$sql .= ' WHERE ';
	$sql .= $this->build_search_where( $query_array, $andor );
	$sql .= " ORDER BY h.time DESC, h.id DESC";
	return $this->get_rows_by_sql( $sql, $limit, $offset );
}

function build_search_where( &$query_array, $andor )
{
	$where = ' h.gid=g.id ';

	if ( is_array( $query_array ) && count( $query_array ) )
	{
		$arr = array();

		foreach ( $query_array as $q )
		{
			$arr[] = " h.f_content LIKE '%". $q ."%' ";
		}

		$where .= ' AND ( '. implode( $andor, $arr ) .' ) ';
	}

	return $where;
}

//----- class end -----
}

?>