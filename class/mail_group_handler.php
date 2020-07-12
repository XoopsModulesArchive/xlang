<?php
// $Id: mail_group_handler.php,v 1.9 2007/12/31 10:51:46 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

//=========================================================
// class xlang_mail_group_handler
//=========================================================
class xlang_mail_group_handler extends xlang_mail_handler
{

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_mail_group_handler()
{
	$this->xlang_mail_handler();
	$this->set_group_handler();
	$this->set_log_handler();
}

function &getInstance()
{
	static $instance;
	if (!isset($instance)) 
	{
		$instance = new xlang_mail_group_handler();
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
function add_with_exist_check( $dirname, $language, $mail, $content, $note )
{
	$row =& $this->get_mail_by_mail( $dirname, $language, $mail );
	if ( is_array($row) )
	{
		$row['m_content'] = $content;
		$row['m_note']    = $note;

		$ret = $this->update_mail_with_log( $row );
		if ( !$ret )
		{	return 0;	}
		return 2;
	}

	return $this->add( $dirname, $language, $mail, $content, $note );
}

function add( $dirname, $language, $mail, $content, $note )
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
		_XLANG_C_KIND_MAIL, $dirname, $language, null, null, $mail );
	if ( !$gid )
	{
		$this->set_error( $this->_group_handler->get_errors() );
		return 0;
	}

	$row_new = array(
		'gid'       => $gid,
		'm_content' => $content,
		'm_note'    => $note,
	);

	$ret = $this->insert_mail_with_log( $row_new );
	if ( !$ret )
	{	return 0;	}

	return 1;
}

//---------------------------------------------------------
// insert
//---------------------------------------------------------
function insert_manage_record( &$row )
{
	return $this->insert_mail_with_log( $row );
}

function insert_mail_with_log( &$row )
{
	$row['time'] = time();

	$newid = $this->insert( $row );
	if ( !$newid )
	{	return false;	}

	$this->_log_handler->write( $row['gid'], _XLANG_C_OP_MAIL_INSERT, $row['m_content'] );

	return $newid;
}

//---------------------------------------------------------
// update
//---------------------------------------------------------
function update_manage_record( &$row )
{
	return $this->update_mail_with_log( $row );
}

function update_mail_with_log( &$row )
{
	$row['time'] = time();

	$ret = $this->update( $row );
	if ( !$ret )
	{	return false;	}

	$this->_log_handler->write( $row['gid'], _XLANG_C_OP_MAIL_UPDATE, $row['m_content'] );

	return true;
}

//---------------------------------------------------------
// delete
//---------------------------------------------------------
function delete_manage_record( &$row )
{
	return $this->delete_mail_with_log( $row );
}

function delete_mail_with_log( &$row )
{
	$ret = $this->delete_by_id( $row['id'] );
	if ( !$ret )
	{	return false;	}

	$this->_log_handler->write( $row['gid'], _XLANG_C_OP_MAIL_DELETE, '' );

	return true;
}

//---------------------------------------------------------
// count
//---------------------------------------------------------
function get_manage_count( $dirname, $language, $file=null, $word=null, $mail=null )
{
	return $this->get_count_by_dirname( $dirname, $language );
}

function get_count_by_dirname( $dirname, $language=null, $mail=null )
{
	return $this->get_count_group_by_dirname(
		_XLANG_C_KIND_MAIL, $dirname, $language, null, null, $mail );
}

function get_cached_mail_id_by_mail( $dirname, $language=null, $mail=null )
{
	$row =& $this->get_cached_mail_by_mail( $dirname, $language, $mail );
	if ( is_array( $row ) )
	{
		return intval( $row['id'] );
	}
	return 0;
}

//---------------------------------------------------------
// get
//---------------------------------------------------------
function &get_manage_rows( $dirname, $language=null, $file=null, $limit=0, $offset=0 )
{
	return $this->get_mails_by_dirname( $dirname, $language, $limit, $offset );
}

function &get_mail_by_id( $id )
{
	return $this->get_row_group_by_id( $id );
}

function &get_cached_mail_by_mail( $dirname, $language, $mail )
{
	if ( isset( $this->_cached[ $dirname ][ $language ][ $mail ] ) )
	{
		return $this->_cached[ $dirname ][ $language ][ $mail ];
	}
	$row =& $this->get_mail_by_mail( $dirname, $language, $mail );
	$this->_cached[ $dirname ][ $language ][ $mail ] = $row;
	return $row;
}

function &get_mail_by_mail( $dirname, $language, $mail )
{
	return $this->get_row_group_by_dirname( 
		_XLANG_C_KIND_MAIL, $dirname, $language, null, null, $mail );
}

function &get_mails_all( $limit=0, $offset=0 )
{
	return $this->get_rows_group_all( $limit, $offset );
}

function &get_mails_by_dirname( $dirname, $language=null, $limit=0, $offset=0 )
{
	return $this->get_rows_group_by_dirname(
		_XLANG_C_KIND_MAIL, $dirname, $language, null, null, null, $limit, $offset );
}

function &get_languages_group_by_language( $dirname, $mail=null, $limit=0, $offset=0 )
{
	$mail_arr =& $this->get_rows_group_by_language( 
			$dirname, $mail, $limit, $offset );

	$arr = array();
	foreach ( $mail_arr as $row )
	{
		$arr[] = $row['language'];
	}
	return $arr;
}

function &get_mails_group_by_mail( $dirname, $language=null, $limit=0, $offset=0 )
{
	$mail_arr =& $this->get_rows_group_by_mail( 
			$dirname, $language, $limit, $offset );

	$arr = array();
	foreach ( $mail_arr as $row )
	{
		$arr[] = $row['mail'];
	}
	return $arr;
}

function &get_rows_group_by_language( $dirname, $mail=null, $limit=0, $offset=0 )
{
	$sql  = 'SELECT '. $this->build_sql_select_group();
	$sql .= ' FROM '.  $this->build_sql_from_group();
	$sql .= ' WHERE ';
	$sql .= $this->build_sql_where_group( _XLANG_C_KIND_MAIL, $dirname, null, null, null, $mail );
	$sql .= ' GROUP BY g.language';
	$sql .= ' ORDER BY h.id';
	return $this->get_rows_by_sql( $sql, $limit, $offset );
}

function &get_rows_group_by_mail( $dirname, $language=null, $limit=0, $offset=0 )
{
	$sql  = 'SELECT '. $this->build_sql_select_group();
	$sql .= ' FROM '.  $this->build_sql_from_group();
	$sql .= ' WHERE ';
	$sql .= $this->build_sql_where_group( _XLANG_C_KIND_MAIL, $dirname, $language );
	$sql .= ' GROUP BY g.mail';
	$sql .= ' ORDER BY h.id';
	return $this->get_rows_by_sql( $sql, $limit, $offset );
}

function &get_mails_latest_by_dirname( $dirname=null, $language=null, $limit=0, $offset=0 )
{
	$sql  = 'SELECT '. $this->build_sql_select_group();
	$sql .= ' FROM '.  $this->build_sql_from_group();
	$sql .= ' WHERE ';
	$sql .= $this->build_sql_where_group( _XLANG_C_KIND_MAIL, $dirname, $language );
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

function &get_mails_by_search( &$query_array, $andor, $limit=0, $offset=0 )
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
			$arr[] = " h.m_content LIKE '%". $q ."%' ";
		}

		$where .= ' AND ( '. implode( $andor, $arr ) .' ) ';
	}

	return $where;
}

//---------------------------------------------------------
// get & check
//---------------------------------------------------------
function compare_mails_by_two_languages( $dirname, $file, $language_1, $language_2 )
{
	if ( $language_1 == $language_2 )
	{	return true;	}

	$count_1 = $this->get_count_by_dirname( $dirname, $language_1, $file );
	$count_2 = $this->get_count_by_dirname( $dirname, $language_2, $file );

	if ( ( $count_1 == 0 )&&( $count_2 == 0 ) )
	{	return true;	}

	if ( $count_1 == 0 )
	{	return false;	}

	return true;
}

//----- class end -----
}

?>