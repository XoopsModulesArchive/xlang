<?php
// $Id: xoops_mysql_database.php,v 1.1 2007/12/17 12:01:53 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

//=========================================================
// class mysql_database
// substitute for class XOOPS XoopsMySQLDatabase
//
// same as happy_linux/xoops_mysql_database.php
//=========================================================

class mysql_database extends Database
{

// Database connection
	var $conn;

	var $prefix;

// debug
// BUG 2793: Fatal error: Call to undefined function: _print_sql_error()
	var $flag_print_error = 1;

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function mysql_database()
{
	$this->setPrefix(XOOPS_DB_PREFIX);
}

//---------------------------------------------------------
// function
//---------------------------------------------------------
function connect()
{
	$this->conn = mysql_connect(XOOPS_DB_HOST, XOOPS_DB_USER, XOOPS_DB_PASS);

	if (!$this->conn) 
	{
		$this->_print_error();
		return false;
	}

	if (!mysql_select_db(XOOPS_DB_NAME)) 
	{
		$this->_print_error();
		return false;
	}

	if ( ! $this->_set_charset() ) 
	{
		$this->_print_error();
		return false;
	}

	return true;
}

function _set_charset()
{
	if ( $this->_is_mysql_ver5() && defined('HAPPY_LINUX_MYSQL_CHARSET') ) 
	{
		$sql = 'SET NAMES ' . HAPPY_LINUX_MYSQL_CHARSET;
		$ret = $this->query( $sql );
		if ( !$ret ) 
		{
			return false;
		}
	}
	return true;
}

function _is_mysql_ver5()
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

function fetchRow($result)
{
	return @mysql_fetch_row($result);
}

function fetchArray($result)
{
	return @mysql_fetch_assoc( $result );
}

function fetchBoth($result)
{
	return @mysql_fetch_array( $result, MYSQL_BOTH );
}

function getInsertId()
{
	return mysql_insert_id($this->conn);
}

function getRowsNum($result)
{
	return @mysql_num_rows($result);
}

function getAffectedRows()
{
	return mysql_affected_rows($this->conn);
}

function close()
{
	mysql_close($this->conn);
}

function freeRecordSet($result)
{
	return mysql_free_result($result);
}

function error()
{
	return @mysql_error();
}

function errno()
{
	return @mysql_errno();
}

function quoteString($str)
{
	$str = "'".str_replace('\\"', '"', addslashes($str))."'";
	return $str;
}

function &queryF($sql, $limit=0, $start=0)
{
	if ( !empty($limit) ) 
	{
		if (empty($start)) 
		{
			$start = 0;
		}
	
		$sql = $sql. ' LIMIT '.(int)$start.', '.(int)$limit;
	}

// Only variables should be assigned by reference
	$result = mysql_query($sql, $this->conn);

	if ( !$result )
	{

// BUG 2793: Fatal error: Call to undefined function: _print_sql_error()
// wrong function name
		$this->_print_error($sql);

// Notice: Only variable references should be returned by reference
		$false = false;
		return $false;
    }

	return $result;
}

function &query($sql, $limit=0, $start=0)
{
	return $this->queryF($sql, $limit, $start);
}

function setPrefix($value)
{
	$this->prefix = $value;
}

function prefix($tablename='')
{
	if ( $tablename != '' ) {
		return $this->prefix .'_'. $tablename;
	} else {
		return $this->prefix;
	}
}

//---------------------------------------------------------
// debug
//---------------------------------------------------------
function _print_error($sql='')
{
	if ( !$this->flag_print_error )  return;

	if ($sql)
	{
		echo "sql: $sql <br />\n";
	}

	echo "<font color='red'>".$this->error()."</font><br />\n";	
}

//---------------------------------------------------------
}

?>