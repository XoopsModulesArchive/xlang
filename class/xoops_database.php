<?php
// $Id: xoops_database.php,v 1.1 2007/12/17 12:01:53 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

//=========================================================
// class Database
// substitute for class XOOPS Database
// this class work for PHP 4
// in PHP 5, occur stric error
//
// same as happy_linux/xoops_database.php
//=========================================================
class Database
{

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function Database()
{
	// dummy
}

function &getInstance()
{
	static $instance;
	if ( !isset($instance) ) 
	{
// Assigning the return value of new by reference is deprecated
		$instance = new mysql_database();
		if ( !$instance->connect() ) 
		{
			echo "<font color='red'>Unable to connect to database.</font><br />\n";
			die();
		}
	}
	return $instance;
}

//---------------------------------------------------------
// function
//---------------------------------------------------------
function prefix($tablename='')
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