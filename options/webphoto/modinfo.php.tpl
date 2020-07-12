<?php
// $Id: modinfo.php.tpl,v 1.1 2008/12/21 20:49:33 ohwada Exp $
// {XLANG_DATE} {XLANG_USER}

//=========================================================
// Webphoto Module
// Language for Module Info
//=========================================================

{XLANG_NOTE}

// test
if ( defined( 'FOR_XOOPS_LANG_CHECKER' ) ) {
	$MY_DIRNAME = 'webphoto' ;

// normal
} elseif (  isset($GLOBALS['MY_DIRNAME']) ) {
	$MY_DIRNAME = $GLOBALS['MY_DIRNAME'];

// call by altsys/mytplsadmin.php
} elseif ( $mydirname ) {
	$MY_DIRNAME = $mydirname;

// probably error
} else {
	echo "not set dirname in ". __FILE__ ." <br />\n";
	$MY_DIRNAME = 'webphoto' ;
}

$constpref = strtoupper( '_MI_' . $MY_DIRNAME. '_' ) ;

// --- define language begin ---
if( defined( 'FOR_XOOPS_LANG_CHECKER' ) || !defined($constpref."LANG_LOADED") ) 
{

{XLANG_DEFINES}

}
// --- define language end ---

?>