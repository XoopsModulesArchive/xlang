<?php
// $Id: charset-dist.php,v 1.2 2007/12/28 05:06:44 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

//-----------------------------------------------------------------------------
// Server Charset Definision
//
// The first setting
// (1) rename this file to "charset.php"
// (2) set your language
// (3) add your MySQL character set, if necessary
// (4) add your langugage character set, if necessary
// (5) set convert_encoding, if use option
//-----------------------------------------------------------------------------

//-----------------------------------------------------------------------------
// SET YOUR LANGUAGE
// this program stop to import the langauge files, if not defined
// ex) english, french, japanese, korean
//-----------------------------------------------------------------------------
//$XLANG_MY_LANGUAGE = 'english';
//$XLANG_MY_LANGUAGE = 'japanese';
//$XLANG_MY_LANGUAGE = 'ja_utf8';

//-----------------------------------------------------------------------------
// Add your MySQL character_set_client
// MySQL 4.1 or later requires the negotiation of the character code
// this program does nothing, if not defined
//-----------------------------------------------------------------------------
$XLANG_MYSQL_CHARSET_ARRAY = array();
$XLANG_MYSQL_CHARSET_ARRAY['english']   = 'latin1';
$XLANG_MYSQL_CHARSET_ARRAY['danish']    = 'latin1';
$XLANG_MYSQL_CHARSET_ARRAY['dutch']     = 'latin1';
$XLANG_MYSQL_CHARSET_ARRAY['french']    = 'latin1';
$XLANG_MYSQL_CHARSET_ARRAY['german']    = 'latin1';
$XLANG_MYSQL_CHARSET_ARRAY['italian']   = 'latin1';
$XLANG_MYSQL_CHARSET_ARRAY['spanish']   = 'latin1';
$XLANG_MYSQL_CHARSET_ARRAY['portugues'] = 'latin1';
$XLANG_MYSQL_CHARSET_ARRAY['portugues.do.brasil'] = 'latin1';
$XLANG_MYSQL_CHARSET_ARRAY['japanese'] = 'ujis';
$XLANG_MYSQL_CHARSET_ARRAY['ja_utf8']  = 'utf8';

//-----------------------------------------------------------------------------
// Add your langugage character set
// this program assume UTF-8, if not defined
//-----------------------------------------------------------------------------
$XLANG_CHARSET_ARRAY = array();
$XLANG_CHARSET_ARRAY['english']   = 'ISO-8859-1';
$XLANG_CHARSET_ARRAY['danish']    = 'ISO-8859-1';
$XLANG_CHARSET_ARRAY['dutch']     = 'ISO-8859-1';
$XLANG_CHARSET_ARRAY['french']    = 'ISO-8859-1';
$XLANG_CHARSET_ARRAY['german']    = 'ISO-8859-1';
$XLANG_CHARSET_ARRAY['italian']   = 'ISO-8859-1';
$XLANG_CHARSET_ARRAY['spanish']   = 'ISO-8859-1';
$XLANG_CHARSET_ARRAY['portugues'] = 'ISO-8859-1';
$XLANG_CHARSET_ARRAY['portugues.do.brasil'] = 'ISO-8859-1';
$XLANG_CHARSET_ARRAY['hungarian'] = 'ISO-8859-2';
$XLANG_CHARSET_ARRAY['polish']    = 'ISO-8859-2';
$XLANG_CHARSET_ARRAY['greek']     = 'ISO-8859-7';
$XLANG_CHARSET_ARRAY['czech']     = 'WINDOWS-1250';
$XLANG_CHARSET_ARRAY['russian']   = 'WINDOWS-1251';
$XLANG_CHARSET_ARRAY['arabic']    = 'WINDOWS-1256';
$XLANG_CHARSET_ARRAY['japanese']  = 'EUC-JP';
$XLANG_CHARSET_ARRAY['schinese']  = 'GB2312';
$XLANG_CHARSET_ARRAY['tchinese']  = 'Big5';
$XLANG_CHARSET_ARRAY['korean']    = 'EUC-KR';
$XLANG_CHARSET_ARRAY['persian']   = 'UTF-8';
$XLANG_CHARSET_ARRAY['ja_utf8']   = 'UTF-8';

//-----------------------------------------------------------------------------
// option : mysql chraset force
// MySQL 4.1 or later requires the negotiation of the character code
// this program judges the mysql's version automatically and sets the character code.
// 1 or true  : set character_set_client always
// 0 or false : automatically
//-----------------------------------------------------------------------------
$XLANG_MYSQL_CHARSET_FORCE = 0;

//-----------------------------------------------------------------------------
// option : convert_encoding
// 1 or true  : convert encording when saving in database
// 0 or false : noting to do
//-----------------------------------------------------------------------------
$XLANG_CONVERT_ENCODING = 0;

//-----------------------------------------------------------------------------
// please feedback your system enviroment
//-----------------------------------------------------------------------------

?>