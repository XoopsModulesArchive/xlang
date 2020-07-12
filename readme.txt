$Id: readme.txt,v 1.2 2008/12/21 20:49:33 ohwada Exp $

=================================================
Name:    XOOPS Language Translation Support
Version: 0.20
Date:    2008-12-22
Author:  Kenichi OHWADA
URL:     http://linux2.ohwada.net/
Email:   webmaster@ohwada.net
=================================================

XOOPS Language Translation Support
This module support to translate the language files.
This create the bilingual table of two language English and Foreign language,
like French Arabian Japanse Chinese Korean and others

* Changes *
1. support D3 modules
(1) If there are language files in XOOPS_TRUST_PATH, 
those files are read.

(2) support prefix of language file

You want to make language file like the following.
modinfo.php
----
define($constpref."xxx","yyy");
----

you describe option file  like the following.
exsample of options/webphoto/filter.php
-----
function xlang_filter_webphoto_modinfo_php_key( $str )
{
	$key = '"_MI_WEBPHOTO_' ;
	$val = '$constpref."' ;
	$str = str_replace( $key, $val, $str );
	return $str;
}
-----


=================================================
Version: 0.10
Date:    2008-01-10
=================================================

XOOPS Language Translation Support
This module support to translate the language files.
This create the bilingual table of two language English and Foreign language,
like French Arabian Japanse Chinese Korean and others

I remake "XOOPS tool for the bilingual table" to XOOPS module
http://linux.ohwada.jp/modules/mydownloads/singlefile.php?cid=1&lid=57


* main feature *

I added the following feature, when remake to module.
(1) some persons can edit the words on WEB.
(2) you can download as the file after editing.
(3) you can see the change log of every.
(4) in UTF-8 environment,
  it is possible to handle more languages which have the different character code.


* initial setting *

(1) rename "include/charset-dist.php" to "include/charset.php"

(2) set your language in "include/charset.php"
exsample for English
---
$XLANG_MY_LANGUAGE = 'english';
---

(3) add your MySQL character_set_client
(4) add your langugage character set
(5) set options
please read "include/charset.php" for more detail


* restriction *

1. xlang reads the language file as PHP program.
therefore, cannot read right in the following case.

(1) there is program description in the language file.
(2) there is string connect with XOOPS or PHP constant.
(3) there is same constant-name which is defined in the other file, 
in the mode to read two or more langugae files

please action like the following, to avoid this restriction .
(a) modify langugae file before read
(b) modify words and files on databese after read


2. The file except the language

There are the modules which has the program file not useing for the language definition in the lagugae directory.
xlang creates the database area where there are receptacle but no content,
when import those files. 
It ocuures the managerial trouble.

the admin can specify the files not to read, to avoid this trouble.
exsample) options/weblinks/skip_files.php
-----
$XLANG_SKIP_FILES = array(
	'weblinks_language_convert.php',
	'language_convert.php'
);
-----


3. support dupicatable module
In the  duplicatable module such as weblinks, there is program description in the language file.
xlang cannot import the first defined sentence, becouse restriction above
-----
if( !defined('WEBLINKS_LANG_MI_LOADED') ) 
{
  define('WEBLINKS_LANG_MI_LOADED', 1);
  define("_MI_WEBLINKS_NAME", "Web Links");
}
-----

the admin can define the template file for create in each language file, to solve this restriction.
exsample) options/weblinks/modinfo.php.tpl
-----
if( !defined("WEBLINKS_LANG_MI_LOADED") ) 
{
{XLANG_DEFINES}
}
-----

4. escape processing

xlang surround the words and sentences with double qoutation " as following,
when create language file form the words and sentences.
-----
define("_MI_XLANG_NAME", "Language Translation Support");
-----

xlang escapes double qoutation in the words and sentences 
exsample) 
' specify double qoutation " ' => " specify double qoutation \" "

you must describe double qoutation 3 times """ , when you want raw double qoutation.
exsample) 
' describe raw double qoutation """ ' => " describe raw double qoutation " "

list of escape processing
-----
"    =>  \"
\"   =>  \"  (no change)
"""  =>  "
\'   =>  '
line code (\n,\r) => delete ('')
tab code  (\t)    => delete ('')
-----


5. customize for the escape processing
sometime feel an inconvenient in standard escape processing avobe.

please action like the following
(a) modift the created file ( easy when few changes )
(b) define the escape processing in each laguage file

exsample) options/cbb-308/filter.php
-----
function xlang_filter_cbb_308_modinfo_php( $str )
{
	describe the escape processing
}
-----


* future study *

in currnet version. it has no translation feature.
Some of the translation web service appears, 
I will support sometime.
- http://babelfish.altavista.com/
- http://www.webservicex.net/
