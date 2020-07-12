<?php
// $Id: modinfo.php,v 1.2 2007/12/28 01:28:02 ohwada Exp $

//================================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//================================================================

// The name of this module
define("_MI_XLANG_NAME", "Language Translation Support");
define("_MI_XLANG_DESC", "This module support to translate the language files");

// admin menu
define("_MI_XLANG_ADMENU_INDEX", "Module List");

// config
define("_MI_XLANG_CONF_DESC", "Description");
define("_MI_XLANG_CONF_DESC_DEF", "<span style='color:#0000ff'>The admin can edit this description in the admin page</span><br />It shows the interlingual translation of two languages English and your language<br />You can edit the words<br />You can download the larest language files");

//-------------------------------------
// notifications
//-------------------------------------
define('_MI_XLANG_NOTIFY_GLOBAL', 'Global');
define('_MI_XLANG_NOTIFY_GLOBAL_DSC', 'Global notification options');
define('_MI_XLANG_NOTIFY_DIRNAME', 'Module');
define('_MI_XLANG_NOTIFY_DIRNAME_DSC', 'Module notification options');
define('_MI_XLANG_NOTIFY_LANGUAGE', 'Langugae');
define('_MI_XLANG_NOTIFY_LANGUAGE_DSC', 'Language notification options');
define('_MI_XLANG_NOTIFY_GLOBAL_MODIFY', 'Notify of global modification');
define('_MI_XLANG_NOTIFY_GLOBAL_MODIFY_CAP', 'Receive notification when any modification in global');
define('_MI_XLANG_NOTIFY_GLOBAL_MODIFY_SBJ', '[{X_SITENAME}] {X_MODULE} : Modification');
define('_MI_XLANG_NOTIFY_DIRNAME_MODIFY', 'Notify of module modification');
define('_MI_XLANG_NOTIFY_DIRNAME_MODIFY_CAP', 'Receive notification when any modification in module');
define('_MI_XLANG_NOTIFY_LANGUAGE_MODIFY', 'Notify of langugae modification');
define('_MI_XLANG_NOTIFY_LANGUAGE_MODIFY_CAP', 'Receive notification when any modification in language');

?>