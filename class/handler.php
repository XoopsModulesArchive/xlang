<?php
// $Id: handler.php,v 1.7 2007/12/29 05:48:20 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

//=========================================================
// class xlang_handler
//=========================================================
class xlang_handler extends xlang_error
{
	var $_db;
	var $_table;

	var $_group_handler;
	var $_group_table;

	var $_log_handler;
	var $_log_table;

	var $_id        = 0;
	var $_xoops_uid = 0;
	var $_cached    = array();

	var $_DEBUG_SQL   = _XLANG_C_DEBUG_SQL;
	var $_DEBUG_ERROR = _XLANG_C_DEBUG_ERROR;

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_handler()
{
	$this->xlang_error();
	$this->_db =& Database::getInstance();
}

function set_table( $name )
{
	$this->_table = $this->_db->prefix( $name );
}

function set_group_handler()
{
	$this->_group_handler =& xlang_group_handler::getInstance();
	$this->_group_table   =  $this->_group_handler->_table;
}

function set_log_handler()
{
	$this->_log_handler =& xlang_log_handler::getInstance();
	$this->_log_table   =  $this->_log_handler->_table;
}

function get_id()
{
	return $this->_id;
}

//---------------------------------------------------------
// basic function
//---------------------------------------------------------
function delete( &$row )
{
	foreach ($row as $k => $v) 
	{	${$k} = $v;	}

	return $this->delete_by_id( $id );
}

function delete_by_id( $id )
{
	$sql  = 'DELETE FROM '. $this->_table;
	$sql .= ' WHERE id='.intval($id);
	return $this->query( $sql );
}

function get_count_all()
{
	$sql  = 'SELECT count(*) FROM '.$this->_table;
	return $this->get_count_by_sql( $sql );
}

function get_count_by_gid( $gid )
{
	$sql  = 'SELECT count(*) FROM '.$this->_table;
	$sql .= ' WHERE gid='. intval($gid);
	return $this->get_count_by_sql( $sql );
}

function &get_row_by_id( $id )
{
	$sql  = 'SELECT * FROM '.$this->_table;
	$sql .= ' WHERE id='. intval($id);
	return $this->get_row_by_sql( $sql );
}

function &get_rows_all( $limit=0, $offset=0, $key=null )
{
	$sql  = 'SELECT * FROM '.$this->_table;
	$sql .= ' ORDER BY id';
	return $this->get_rows_by_sql( $sql, $limit, $offset, $key );
}

function &get_rows_by_gid( $gid )
{
	$sql  = 'SELECT * FROM '.$this->_table;
	$sql .= ' WHERE gid='. intval($gid);
	return $this->get_rows_by_sql( $sql );
}

function &get_rows_same_by_id_gid( $id, $gid )
{
	$sql  = 'SELECT * FROM '.$this->_table;
	$sql .= ' WHERE gid='. intval($gid);
	$sql .= ' AND id<>'. intval($id);
	return $this->get_rows_by_sql( $sql );
}

//---------------------------------------------------------
// utility
//---------------------------------------------------------
function &get_count_by_sql( $sql )
{
	$count = intval( $this->get_first_row_by_sql( $sql ) );
	return $count;
}

function &get_first_row_by_sql( $sql )
{
	$res =& $this->query($sql);
	if ( !$res )
	{	return $res;	}
	$row = $this->_db->fetchRow( $res );
	$ret =& $row[0];
	return $ret;
}

function &get_row_by_sql( $sql )
{
	$res =& $this->query( $sql );
	if ( !$res )
	{	return $res;	}
	$row = $this->_db->fetchArray($res);
	return $row; 
}

function &get_rows_by_sql( $sql, $limit=0, $offset=0, $key=null )
{
	$arr = array();

	$res =& $this->query( $sql, $limit, $offset );
	if ( !$res )
	{	return $res;	}

	while ( $row = $this->_db->fetchArray($res) ) 
	{
		if ( $key && isset( $row[ $key ] ) ) 
		{
			$arr[ $row[ $key ] ] = $row;
		}
		else 
		{
			$arr[] = $row;
		}
	}
	return $arr; 
}

function &get_first_rows_by_sql( $sql, $limit=0, $offset=0 )
{
	$res =& $this->query( $sql, $limit, $offset );
	if ( !$res )
	{	return $res;	}

	$arr = array();

	while ( $row = $this->_db->fetchRow($res) ) 
	{
		$arr[] = $row[0];
	}
	return $arr;
}

function &query( $sql, $limit=0, $offset=0 )
{
	if ( $this->_DEBUG_SQL )
	{
		echo $this->sanitize( $sql ) .': limit='. $limit .' :offset='. $offset. "<br />\n";
	}

	$res = $this->_db->query( $sql, intval($limit), intval($offset) );
	if ( !$res ) 
	{
		$error = $this->_db->error();
		$this->set_error( $error );

		if ( $this->_DEBUG_ERROR )
		{
			echo $this->highlight( $this->sanitize( $error ) )."<br />\n";
		}
	}
	return $res;
}

function &queryF( $sql, $limit=0, $offset=0 )
{
	if ( $this->_DEBUG_SQL )
	{
		echo $this->sanitize( $sql )."<br />\n";
	}

	$res = $this->_db->queryF( $sql, intval($limit), intval($offset) );
	if ( !$res ) 
	{
		$error = $this->_db->error();
		$this->set_error( $error );

		if ( $this->_DEBUG_ERROR )
		{
			echo $this->highlight( $this->sanitize( $error ) )."<br />\n";
		}
	}
	return $res;
}

function quote( $str )
{
	$str = "'". addslashes($str) ."'";
	return $str;
}

//---------------------------------------------------------
// execute sql
//---------------------------------------------------------
function get_count_group_by_dirname( $kind, $dirname, $language=null, $file=null, $word=null, $mail=null )
{
	$sql  = 'SELECT count(*) ';
	$sql .= ' FROM '.  $this->build_sql_from_group();
	$sql .= ' WHERE ';
	$sql .= $this->build_sql_where_group( $kind, $dirname, $language, $file, $word, $mail );
	return $this->get_count_by_sql( $sql );
}

function &get_row_group_by_id( $id )
{
	$sql  = 'SELECT '. $this->build_sql_select_group();
	$sql .= ' FROM '.  $this->build_sql_from_group();
	$sql .= ' WHERE h.id='. intval( $id );
	$sql .= ' AND h.gid=g.id ';
	$sql .= ' ORDER BY h.id';
	return $this->get_row_by_sql( $sql );
}

function &get_row_group_by_dirname( $kind, $dirname, $language=null, $file=null, $word=null, $mail=null )
{
	$sql  = 'SELECT '. $this->build_sql_select_group();
	$sql .= ' FROM '.  $this->build_sql_from_group();
	$sql .= ' WHERE ';
	$sql .= $this->build_sql_where_group( $kind, $dirname, $language, $file, $word, $mail );
	$sql .= ' ORDER BY h.id';
	return $this->get_row_by_sql( $sql );
}

function &get_rows_group_all( $limit=0, $offset=0 )
{
	$sql  = 'SELECT '. $this->build_sql_select_group();
	$sql .= ' FROM '.  $this->build_sql_from_group();
	$sql .= ' WHERE h.gid=g.id ';
	$sql .= ' ORDER BY h.id';
	return $this->get_rows_by_sql( $sql, $limit, $offset );
}

function &get_rows_group_by_dirname( $kind, $dirname, $language=null, $file=null, $word=null, $mail=null, $limit=0, $offset=0 )
{
	$sql  = 'SELECT '. $this->build_sql_select_group();
	$sql .= ' FROM '.  $this->build_sql_from_group();
	$sql .= ' WHERE ';
	$sql .= $this->build_sql_where_group( $kind, $dirname, $language, $file, $word, $mail );
	$sql .= ' ORDER BY h.id';
	return $this->get_rows_by_sql( $sql, $limit, $offset );
}

function exist_group_by_gid( $gid )
{
	$row =& $this->get_group_row_by_gid( $gid );
	if ( is_array($row) )
	{	return true;	}
	return false;
}

function &get_group_row_by_gid( $gid )
{
	return $this->_group_handler->get_row_by_id( $gid );
}

//---------------------------------------------------------
// build sql
//---------------------------------------------------------
function build_sql_select_group()
{
	$sql = ' h.*, g.dirname, g.language, g.file, g.word, g.mail, g.kind ';
	return $sql;
}

function build_sql_from_group()
{
	$sql  = $this->_table.' h, ';
	$sql .= $this->_group_table.' g ';
	return $sql;
}

function build_sql_where_group( $kind=null, $dirname=null, $language=null, $file=null, $word=null, $mail=null )
{
	$sql = null;
	$arr = array();
	$arr[] = 'h.gid=g.id';
	if ( $kind )
	{	$arr[] = 'g.kind='. intval($kind);	}
	if ( $dirname )
	{	$arr[] = 'g.dirname='. $this->quote($dirname);	}
	if ( $language )
	{	$arr[] = 'g.language='. $this->quote($language);	}
	if ( $file )
	{	$arr[] = 'g.file='. $this->quote($file);	}
	if ( $word )
	{	$arr[] = 'g.word='. $this->quote($word);	}
	if ( $mail )
	{	$arr[] = 'g.mail='. $this->quote($mail);	}
	if ( count($arr) )
	{	$sql = ' '. implode( ' AND ', $arr ). ' ';	}
	return $sql;
}

function build_sql_where_dirname( $kind=null, $dirname=null, $language=null, $file=null, $word=null, $mail=null )
{
	$sql = null;
	$arr = array();
	if ( $kind )
	{	$arr[] = 'kind='. intval($kind);	}
	if ( $dirname )
	{	$arr[] = 'dirname='. $this->quote($dirname);	}
	if ( $language )
	{	$arr[] = 'language='. $this->quote($language);	}
	if ( $file )
	{	$arr[] = 'file='. $this->quote($file);	}
	if ( $word )
	{	$arr[] = 'word='. $this->quote($word);	}
	if ( $mail )
	{	$arr[] = 'mail='. $this->quote($mail);	}
	if ( count($arr) )
	{	$sql = ' '. implode( ' AND ', $arr ). ' ';	}
	return $sql;
}

function judge_group_kind( $dirname, $language=null, $file=null, $word=null, $mail=null )
{
	if ( $dirname && $language && $file && $word )
	{	return _XLANG_C_KIND_WORD;	}
	if ( $dirname && $language && $file )
	{	return _XLANG_C_KIND_FILE;	}
	if ( $dirname && $language && $mail )
	{	return _XLANG_C_KIND_MAIL;	}
	if ( $dirname && $language )
	{	return _XLANG_C_KIND_LANGUAGE;	}
	if ( $dirname )
	{	return _XLANG_C_KIND_DIRNAME;	}
	return 0;
}

function build_sql_by_gid_array( &$gid_array )
{
	$str = 'gid=' . implode( ' OR gid=', $gid_array );
	return $str;
}

//---------------------------------------------------------
// sql
//---------------------------------------------------------
// MySQL 5 requires the negotiation of the character code
function set_mysql_charset( $charset, $force=false )
{
	if ( ( $this->is_mysql_ver5() || $force ) && $charset )
	{
		$sql = 'SET NAMES ' . $charset;
		$this->query( $sql );
	}
}

function &get_mysql_variables()
{
	$sql = "SHOW VARIABLES LIKE 'char%'";
	$res =& $this->_db->queryF( $sql );
	if ( !$res )
	{	return $res;	}

	$arr =array();
	while ( $row = $this->_db->fetchRow($res) )
	{
		$arr[ $row[0] ] = $row[1];
	}
	return $arr;
}

function is_mysql_ver5()
{
	$ver = mysql_get_server_info();
	if ( preg_match("/^4\.1/", $ver) ) 
	{
		return true;
	}
	if ( preg_match("/^5\./", $ver) ) 
	{
		return true;
	}
	return false;
}

//---------------------------------------------------------
// xoops
//---------------------------------------------------------
function init_xoops_param()
{
	global $xoopsUser;
	if ( is_object($xoopsUser) )
	{
		$this->_xoops_uid = $xoopsUser->getVar('uid');
	}
}

//----- class end -----
}

?>