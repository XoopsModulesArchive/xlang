<?php
// $Id: blocks.php.tpl,v 1.1 2008/12/21 20:49:33 ohwada Exp $
// {XLANG_DATE} {XLANG_USER}

//=========================================================
// Webphoto Module
// Language for Blocks
//=========================================================

{XLANG_NOTE}

$constpref = strtoupper( '_BL_' . $GLOBALS['MY_DIRNAME']. '_' ) ;

// --- define language begin ---
if( !defined($constpref."LANG_LOADED") ) 
{

{XLANG_DEFINES}

}
// --- define language end ---

?>