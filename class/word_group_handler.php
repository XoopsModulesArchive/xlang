<?php
// $Id: word_group_handler.php,v 1.11 2007/12/31 10:51:46 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

//=========================================================
// class xlang_word_group_handler
//=========================================================
class xlang_word_group_handler extends xlang_word_handler
{
	var $_word_both_undefined_array = array();
	var $_flag_not_exist_1    = false;
	var $_flag_not_exist_2    = false;
	var $_flag_not_exist_both = false;
	var $_flag_undefined_1    = false;
	var $_flag_undefined_2    = false;

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_word_group_handler()
{
	$this->xlang_word_handler();
	$this->set_group_handler();
	$this->set_log_handler();
}

function &getInstance()
{
	static $instance;
	if (!isset($instance)) 
	{
		$instance = new xlang_word_group_handler();
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
function add_with_exist_check( $dirname, $language, $file, $word, $content, $note )
{
	$row =& $this->get_word_by_word( $dirname, $language, $file, $word );
	if ( is_array($row) )
	{
		$row['w_content'] = $content;
		$row['w_note']    = $note;

		$ret = $this->update_word_with_log( $row );
		if ( !$ret )
		{	return 0;	}

		return 2;
	}

	return $this->add( $dirname, $language, $file, $word, $content, $note );
}

function add( $dirname, $language, $file, $word, $content, $note, $act=1 )
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

	$ret = $this->_group_handler->add_with_exist_check(
		_XLANG_C_KIND_FILE, $dirname, $language, $file );
	if ( !$ret )
	{
		$this->set_error( $this->_group_handler->get_errors() );
		return 0;
	}

	$gid = $this->_group_handler->add_with_exist_check(
		_XLANG_C_KIND_WORD, $dirname, $language, $file, $word );
	if ( !$gid )
	{
		$this->set_error( $this->_group_handler->get_errors() );
		return 0;
	}

	$row_new = array(
		'gid'       => $gid,
		'w_content' => $content,
		'w_note'    => $note,
		'w_act'     => $act,
	);

	$ret = $this->insert_word_with_log( $row_new );
	if ( !$ret )
	{	return 0;	}

	return 1;
}

//---------------------------------------------------------
// insert
//---------------------------------------------------------
function insert_manage_record( &$row )
{
	return $this->insert_word_with_log( $row );
}

function insert_word_with_log( &$row )
{
	$row['time']  = time();

	if ( !isset($row['w_act']) )
	{
		$row['w_act'] = 1;
	}

	$newid = $this->insert( $row );
	if ( !$newid )
	{	return false;	}

	$this->_log_handler->write( $row['gid'], _XLANG_C_OP_WORD_INSERT, $row['w_content'] );

	return $newid;
}

//---------------------------------------------------------
// update
//---------------------------------------------------------
function update_manage_record( &$row )
{
	return $this->update_word_with_log( $row );
}

function update_word_with_log( &$row )
{
	$row['time'] = time();

	$ret = $this->update( $row );
	if ( !$ret )
	{	return false;	}

	$this->_log_handler->write( $row['gid'], _XLANG_C_OP_WORD_UPDATE, $row['w_content'] );

	return true;
}

function update_no_act_with_log( &$row )
{
	$row['w_act'] = 0;
	$row['time']  = time();

	$ret = $this->update( $row );
	if ( !$ret )
	{	return false;	}

	$this->_log_handler->write( $row['gid'], _XLANG_C_OP_WORD_NO_ACT, '' );

	return true;
}

//---------------------------------------------------------
// delete
//---------------------------------------------------------
function delete_manage_record( &$row )
{
	return $this->delete_word_with_log( $row );
}

function delete_word_with_log( &$row )
{
	$ret = $this->delete_by_id( $row['id'] );
	if ( !$ret )
	{	return false;	}

	$this->_log_handler->write( $row['gid'], _XLANG_C_OP_WORD_DELETE, '' );

	return true;
}

//---------------------------------------------------------
// count
//---------------------------------------------------------
function get_manage_count( $dirname, $language, $file=null, $word=null, $mail=null )
{
	return $this->get_count_by_dirname( $dirname, $language, $file );
}

function get_count_by_dirname( $dirname, $language=null, $file=null )
{
	return $this->get_count_group_by_dirname(
		_XLANG_C_KIND_WORD, $dirname, $language, $file );
}

function get_cached_word_id_by_word( $dirname, $language, $file, $word )
{
	$row =& $this->get_cached_word_by_word( $dirname, $language, $file, $word );
	if ( is_array( $row ) )
	{
		return intval( $row['id'] );
	}
	return 0;
}

//---------------------------------------------------------
// get
//---------------------------------------------------------
function &get_manage_rows( $dirname, $language=null, $file=null, $word=null, $mail=null, $limit=0, $offset=0 )
{
	return $this->get_words_by_dirname( $dirname, $language, $file, $limit, $offset );
}

function &get_word_by_id( $id )
{
	return $this->get_row_group_by_id( $id );
}

function &get_cached_word_by_word( $dirname, $language, $file, $word )
{
	if ( isset( $this->_cached[ $dirname ][ $language ][ $file ][ $word ] ) )
	{
		return $this->_cached[ $dirname ][ $language ][ $file ][ $word ];
	}
	$row =& $this->get_word_by_word( $dirname, $language, $file, $word );
	$this->_cached[ $dirname ][ $language ][ $file ][ $word ] = $row;
	return $row;
}

function &get_word_by_word( $dirname, $language, $file, $word )
{
	return $this->get_row_group_by_dirname( 
		_XLANG_C_KIND_WORD, $dirname, $language, $file, $word );
}

function &get_words_all( $limit=0, $offset=0 )
{
	return $this->get_rows_group_all( $limit, $offset );
}

function &get_words_by_dirname( $dirname, $language=null, $file=null, $limit=0, $offset=0 )
{
	$sql  = 'SELECT '. $this->build_sql_select_group();
	$sql .= ' FROM '.  $this->build_sql_from_group();
	$sql .= ' WHERE ';
	$sql .= $this->build_sql_where_group( _XLANG_C_KIND_WORD, $dirname, $language, $file );
	$sql .= ' ORDER BY h.id';
	return $this->get_rows_by_sql( $sql, $limit, $offset, 'word' );
}

function &get_dirnames_group_by_dirname( $dirname=null, $file=null, $word=null, $limit=0, $offset=0 )
{
	$word_arr =& $this->get_rows_group_by_dirname( 
			$dirname, $file, $word, $limit, $offset );

	$arr = array();
	foreach ( $word_arr as $row )
	{
		$arr[] = $row['dirname'];
	}
	return $arr;
}

function &get_languages_group_by_language( $dirname, $file=null, $word=null, $limit=0, $offset=0 )
{
	$word_arr =& $this->get_rows_group_by_language( 
			$dirname, $file, $word, $limit, $offset );

	$arr = array();
	foreach ( $word_arr as $row )
	{
		$arr[] = $row['language'];
	}
	return $arr;
}

function &get_files_group_by_file( $dirname, $language=null, $limit=0, $offset=0 )
{
	$word_arr =& $this->get_rows_group_by_file( 
			$dirname, $language, $limit, $offset );

	$arr = array();
	foreach ( $word_arr as $row )
	{
		$arr[] = $row['file'];
	}
	return $arr;
}

function &get_rows_group_by_dirname( $dirname=null, $file=null, $word=null, $limit=0, $offset=0 )
{
	$sql  = 'SELECT '. $this->build_sql_select_group();
	$sql .= ' FROM '.  $this->build_sql_from_group();
	$sql .= ' WHERE ';
	$sql .= $this->build_sql_where_group( _XLANG_C_KIND_WORD, $dirname, null, $file, $word );
	$sql .= ' GROUP BY g.dirname';
	$sql .= ' ORDER BY h.id';
	return $this->get_rows_by_sql( $sql, $limit, $offset );
}

function &get_rows_group_by_language( $dirname, $file=null, $word=null, $limit=0, $offset=0 )
{
	$sql  = 'SELECT '. $this->build_sql_select_group();
	$sql .= ' FROM '.  $this->build_sql_from_group();
	$sql .= ' WHERE ';
	$sql .= $this->build_sql_where_group( _XLANG_C_KIND_WORD, $dirname, null, $file, $word );
	$sql .= ' GROUP BY g.language';
	$sql .= ' ORDER BY h.id';
	return $this->get_rows_by_sql( $sql, $limit, $offset );
}

function &get_rows_group_by_file( $dirname, $language=null, $limit=0, $offset=0 )
{
	$sql  = 'SELECT '. $this->build_sql_select_group();
	$sql .= ' FROM '.  $this->build_sql_from_group();
	$sql .= ' WHERE ';
	$sql .= $this->build_sql_where_group( _XLANG_C_KIND_WORD, $dirname, $language );
	$sql .= ' GROUP BY g.file';
	$sql .= ' ORDER BY h.id';
	return $this->get_rows_by_sql( $sql, $limit, $offset );
}

function &get_latest_word_by_dirname( $dirname, $language=null, $file=null )
{
	$rows =& $this->get_words_latest_by_dirname( $dirname, $language, $file, 1 );
	if ( isset( $rows[0] ) )
	{
		return $rows[0];
	}
	$null = null;
	return $null;
}

function &get_words_latest_by_dirname( $dirname, $language=null, $file=null, $limit=0, $offset=0 )
{
	$sql  = 'SELECT '. $this->build_sql_select_group();
	$sql .= ' FROM '.  $this->build_sql_from_group();
	$sql .= ' WHERE ';
	$sql .= $this->build_sql_where_group( _XLANG_C_KIND_WORD, $dirname, $language, $file );
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

function &get_words_by_search( &$query_array, $andor, $limit=0, $offset=0 )
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
		$arr1 = array();
		$arr2 = array();

		foreach ( $query_array as $q )
		{
			$arr1[] = " g.word LIKE '%". $q ."%' ";
			$arr2[] = " h.w_content LIKE '%". $q ."%' ";
		}

		$where .= ' AND ( ( '. implode( $andor, $arr1 ) .' ) ';
		$where .=    ' OR ( '. implode( $andor, $arr2 ) .' ) ) ';
	}

	return $where;
}

//---------------------------------------------------------
// get & check
//---------------------------------------------------------
function &get_words_by_two_languages( $dirname, $file, $language_1, $language_2, $flag_check=false )
{
	$this->_flag_not_exist_1    = false;
	$this->_flag_not_exist_2    = false;
	$this->_flag_not_exist_both = false;
	$this->_flag_undefined_1    = false;
	$this->_flag_undefined_2    = false;

	$true    = true;
	$false   = false;
	$count_1 = 0;
	$count_2 = 0;
	$word_both_arr           = array();
	$key_arr                 = array();
	$word_both_undefined_arr = array();

	if ( $flag_check && ( $language_1 == $language_2 ) )
	{	return $true;	}

	$word_arr_1 =& $this->get_words_by_dirname( $dirname, $language_1, $file );
	$word_arr_2 =& $this->get_words_by_dirname( $dirname, $language_2, $file );

	if ( is_array($word_arr_1) )
	{
		$count_1 = count($word_arr_1);
	}
	if ( is_array($word_arr_2) )
	{
		$count_2 = count($word_arr_2);
	}

	if ( ( $count_1 == 0 )&&( $count_2 == 0 ) )
	{
		if ( $flag_check )
		{	return $true;	}

		$this->_flag_not_exist_both = true;
		return $false;
	}

	if ( $count_1 == 0 )
	{
		if ( $flag_check )
		{	return $false;	}

		$this->_flag_not_exist_1 = true;
	}

	if ( $count_2 == 0 )
	{
		$this->_flag_not_exist_2 = true;
	}

	foreach ( $word_arr_2 as $word => $row_2 )
	{
		$key_arr[ $word ] = true;

		$temp    = array();
		$temp[0] = $word;
		$temp[2] = $row_2;

		if ( isset( $word_arr_1[ $word ] ) )
		{
			$temp[1] =& $word_arr_1[ $word ];
		}
		else
		{
			$temp[1] = null;

// push in array if act
			if ( $row_2['w_act'] )
			{
				$this->_flag_undefined_1   = true;
				$word_both_undefined_arr[] = $temp;

				if ( $flag_check )
				{	return $false;	}
			}
		}

		$word_both_arr[] = $temp;
	}

	foreach ( $word_arr_1 as $word => $row_1 )
	{
		if ( isset( $key_arr[ $word ] ) )
		{	continue;	}

		$temp    = array();
		$temp[0] = $word;
		$temp[1] = $row_1;

		if ( isset( $word_arr_2[ $word ] ) )
		{
			$temp[2] =& $word_arr_2[ $word ];
		}
		else
		{
			$temp[2] = null;

// push in array if act
			if ( $row_1['w_act'] )
			{
				$this->_flag_undefined_2   = true;
				$word_both_undefined_arr[] = $temp;
			}
		}

		$word_both_arr[] = $temp;
	}

	if ( $flag_check )
	{	return $true;	}

	$this->_word_both_undefined_array =& $word_both_undefined_arr;

	return $word_both_arr;
}

function compare_words_by_two_languages( $dirname, $file, $language_1, $language_2 )
{
	return $this->get_words_by_two_languages( $dirname, $file, $language_1, $language_2, true );
}

//----- class end -----
}

?>