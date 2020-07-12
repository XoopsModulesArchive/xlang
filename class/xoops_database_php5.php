<?php
// $Id: xoops_database_php5.php,v 1.1 2007/12/17 12:01:53 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

//=========================================================
// class Database
// substitute for class XOOPS Database
// this class work only for PHP 5
//
// same as happy_linux/xoops_database_php5.php
//=========================================================
class Database
{
	private static $_singleton = null;

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function Database()
{
	// dummy
}

public static function &getInstance()
{
	if (Database::$_singleton == null)
	{
		$singleton = new mysql_database();
		if ( !$singleton->connect() ) 
		{
			echo "<font color='red'>Unable to connect to database.</font><br />\n";
			die();
		}
		Database::$_singleton = $singleton;
	}
	return Database::$_singleton;
}

//---------------------------------------------------------
// function
//---------------------------------------------------------
public function prefix($tablename='')
{
	if ( $tablename != '' ) {
		return XOOPS_DB_PREFIX .'_'. $tablename;
	} else {
		return XOOPS_DB_PREFIX;
	}
}

//---------------------------------------------------------
}

?>