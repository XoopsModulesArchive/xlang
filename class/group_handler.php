<?php
// $Id: group_handler.php,v 1.3 2007/12/29 05:48:20 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

//=========================================================
// class xlang_group_handler
//=========================================================
class xlang_group_handler extends xlang_handler
{

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_group_handler()
{
	$this->xlang_handler();
	$this->set_table( 'xlang_group' );
}

function &getInstance()
{
	static $instance;
	if (!isset($instance)) 
	{
		$instance = new xlang_group_handler();
	}
	return $instance;
}

//---------------------------------------------------------
// add
//---------------------------------------------------------
function add_with_exist_check( $kind, $dirname, $language=null, $file=null, $word=null, $mail=null )
{
	$row =& $this->get_cached_row_by_dirname( $kind, $dirname, $language, $file, $word, $mail );
	if ( isset( $row['id'] ) )
	{
		return $row['id'];
	}

	$row_new = array(
		'time'     => time(),
		'dirname'  => $dirname,
		'language' => $language,
		'file'     => $file,
		'word'     => $word,
		'mail'     => $mail,
		'kind'     => $kind,
	);
	return $this->insert( $row_new );
}

//---------------------------------------------------------
// insert
//---------------------------------------------------------
function insert_manage_record( &$row )
{
	$row['time'] = time();
	return $this->insert( $row );
}

function insert( &$row )
{
	foreach ($row as $k => $v) 
	{	${$k} = $v;	}

	$sql  = 'INSERT INTO '.$this->_table.' (';
	$sql .= 'time, ';
	$sql .= 'dirname, ';
	$sql .= 'language, ';
	$sql .= 'file, ';
	$sql .= 'word, ';
	$sql .= 'mail, ';
	$sql .= 'kind ';
	$sql .= ') VALUES (';
	$sql .= intval($time).', ';
	$sql .= $this->quote($dirname).', ';
	$sql .= $this->quote($language).', ';
	$sql .= $this->quote($file).', ';
	$sql .= $this->quote($word).', ';
	$sql .= $this->quote($mail).', ';
	$sql .= $this->quote($kind).' ';
	$sql .= ')';

	$ret = $this->query( $sql );
	if ( !$ret )
	{	return false;	}

	return $this->_db->getInsertId();
}

//---------------------------------------------------------
// update
//---------------------------------------------------------
function update_manage_record( &$row )
{
	$row['time'] = time();
	return $this->update( $row );
}

function update( &$row )
{
	foreach ($row as $k => $v) 
	{	${$k} = $v;	}

	$sql  = 'UPDATE '. $this->_table .' SET ';
	$sql .= 'time='. intval($time).', ';
	$sql .= 'dirname='. $this->quote($dirname).', ';
	$sql .= 'language='. $this->quote($language).', ';
	$sql .= 'file='. $this->quote($file).', ';
	$sql .= 'word='. $this->quote($word).', ';
	$sql .= 'mail='. $this->quote($mail).', ';
	$sql .= 'kind='. intval($kind).' ';
	$sql .= 'WHERE id='.intval($id);

	return $this->query( $sql );
}

//---------------------------------------------------------
// delete
//---------------------------------------------------------
function delete_manage_record( &$row )
{
	return $this->delete( $row );
}

//---------------------------------------------------------
// count
//---------------------------------------------------------
function get_manage_count( $dirname, $language, $file=null, $word=null, $mail=null )
{
	return $this->get_count_by_dirname( null, $dirname, $language, $file, $word, $mail );
}

function get_count_by_dirname( $kind, $dirname=null, $language=null, $file=null, $word=null, $mail=null )
{
	$sql  = 'SELECT count(*) ';
	$sql .= ' FROM '. $this->_table;
	$sql .= ' WHERE ';
	$sql .= $this->build_sql_where_dirname( $kind, $dirname, $language, $file, $word, $mail );
	return $this->get_count_by_sql( $sql );
}

function get_cached_id_by_dirname( $kind, $dirname, $language=null, $file=null, $word=null, $mail=null )
{
	$row =& $this->get_cached_row_by_dirname(
			$kind, $dirname, $language, $file, $word, $mail );
	if ( isset( $row['id'] ) )
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
	return $this->get_rows_by_dirname(
			null, $dirname, $language, $file, $word, $mail, $limit, $offset );
}

function &get_cached_row_by_dirname( $kind, $dirname, $language=null, $file=null, $word=null, $mail=null )
{
	switch( $kind )
	{
		case _XLANG_C_KIND_DIRNAME:
			if ( isset( $this->_cached[ _XLANG_C_KIND_DIRNAME ][ $dirname ] ) )
			{
				return $this->_cached[ _XLANG_C_KIND_DIRNAME ][ $dirname ];
			}
			break;

		case _XLANG_C_KIND_LANGUAGE:
			if ( isset( $this->_cached[ _XLANG_C_KIND_LANGUAGE ][ $dirname ][ $language ] ) )
			{
				return $this->_cached[ _XLANG_C_KIND_LANGUAGE ][ $dirname ][ $language ];
			}
			break;

		case _XLANG_C_KIND_FILE:
			if ( isset( $this->_cached[ _XLANG_C_KIND_FILE ][ $dirname ][ $language ][ $file ] ) )
			{
				return $this->_cached[ _XLANG_C_KIND_FILE ][ $dirname ][ $language ][ $file ];
			}
			break;

		case _XLANG_C_KIND_WORD:
			if ( isset( $this->_cached[ _XLANG_C_KIND_WORD ][ $dirname ][ $language ][ $file ][ $word ] ) )
			{
				return $this->_cached[ _XLANG_C_KIND_WORD ][ $dirname ][ $language ][ $file ][ $word ];
			}
			break;

		case _XLANG_C_KIND_MAIL:
			if ( isset( $this->_cached[ _XLANG_C_KIND_MAIL ][ $dirname ][ $language ][ $mail ] ) )
			{
				return $this->_cached[ _XLANG_C_KIND_MAIL ][ $dirname ][ $language ][ $mail ];
			}
			break;
	}

	$row =& $this->get_row_by_dirname(
			$kind, $dirname, $language, $file, $word, $mail );
	if ( !is_array($row) )
	{
		$null = null;
		return $null;
	}

	switch( $kind )
	{
		case _XLANG_C_KIND_DIRNAME:
			$this->_cached[ _XLANG_C_KIND_DIRNAME ][ $dirname ] = $row;
			break;

		case _XLANG_C_KIND_LANGUAGE:
			$this->_cached[ _XLANG_C_KIND_LANGUAGE ][ $dirname ][ $language ] = $row;
			break;

		case _XLANG_C_KIND_FILE:
			$this->_cached[ _XLANG_C_KIND_FILE ][ $dirname ][ $language ][ $file ] = $row;
			break;

		case _XLANG_C_KIND_WORD:
			$this->_cached[ _XLANG_C_KIND_WORD ][ $dirname ][ $language ][ $file ][ $word ] = $row;
			break;

		case _XLANG_C_KIND_MAIL:
			$this->_cached[ _XLANG_C_KIND_MAIL ][ $dirname ][ $language ][ $mail ] = $row;
			break;
	}

	return $row;
}

function &get_row_by_dirname( $kind, $dirname, $language=null, $file=null, $word=null, $mail=null )
{
	$rows =& $this->get_rows_by_dirname(
			$kind, $dirname, $language, $file, $word, $mail, 1 );
	if ( isset( $rows[0] ) )
	{
		return $rows[0];
	}
	$null = null;
	return $null;
}

function &get_rows_by_dirname( $kind, $dirname, $language=null, $file=null, $word=null, $mail=null, $limit=0, $offset=0 )
{
	$sql  = 'SELECT * ';
	$sql .= ' FROM '. $this->_table;
	$sql .= ' WHERE ';
	$sql .= $this->build_sql_where_dirname( $kind, $dirname, $language, $file, $word, $mail  );
	$sql .= ' ORDER BY id';
	return $this->get_rows_by_sql( $sql, $limit, $offset );
}

//----- class end -----
}

?>