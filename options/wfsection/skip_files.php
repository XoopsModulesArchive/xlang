<?php
// $Id: skip_files.php,v 1.1 2007/12/17 12:02:05 ohwada Exp $

//=============================================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=============================================================================

//-----------------------------------------------------------------------------
// there are same files in language direcoty, 
// which is program file not langauge definition.
// this progarm skip to import those files. 
//-----------------------------------------------------------------------------

$XLANG_SKIP_FILES = array(
	'convert.php', 
	'convert_language.php'
 );

?>