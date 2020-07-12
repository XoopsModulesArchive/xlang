<?php
// $Id: xoops_version.php,v 1.4 2008/12/21 20:49:33 ohwada Exp $

//================================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//================================================================

$modversion['name'] = _MI_XLANG_NAME;
$modversion['version'] = 0.20;
$modversion['description'] = _MI_XLANG_DESC;
$modversion['credits'] = '';
$modversion['author']   = 'K.OHWADA';
$modversion['help'] = '';
$modversion['license'] = 'GPL see LICENSE';
$modversion['official'] = 1;
$modversion['image']    = 'images/xlang_slogo.png';
$modversion['dirname']  = 'xlang';

//---------------------------------------------------------
// Tables 
//---------------------------------------------------------
// All tables should not have any prefix!
$modversion['sqlfile']['mysql'] = 'sql/xlang.sql';

// -- Tables created by sql file (without prefix!) ---
$modversion['tables'][0] = 'xlang_group';
$modversion['tables'][1] = 'xlang_file';
$modversion['tables'][2] = 'xlang_word';
$modversion['tables'][3] = 'xlang_mail';
$modversion['tables'][4] = 'xlang_template';
$modversion['tables'][5] = 'xlang_log';

//---------------------------------------------------------
// Main 
//---------------------------------------------------------
$modversion['hasMain'] = 1;

//---------------------------------------------------------
// Admin 
//---------------------------------------------------------
$modversion['hasAdmin']   = 1;
$modversion['adminindex'] = 'admin/index.php';
$modversion['adminmenu']  = 'admin/menu.php';

//---------------------------------------------------------
//  Search 
//---------------------------------------------------------
$modversion['hasSearch'] = 1;
$modversion['search']['file'] = 'include/search.inc.php';
$modversion['search']['func'] = 'xlang_search';

//---------------------------------------------------------
// Templates
//---------------------------------------------------------
$modversion['templates'][1]['file'] = 'xlang_index_top.html';
$modversion['templates'][1]['description'] = '';
$modversion['templates'][2]['file'] = 'xlang_index_language.html';
$modversion['templates'][2]['description'] = '';
$modversion['templates'][3]['file'] = 'xlang_index_file.html';
$modversion['templates'][3]['description'] = '';
$modversion['templates'][4]['file'] = 'xlang_file_show.html';
$modversion['templates'][4]['description'] = '';
$modversion['templates'][5]['file'] = 'xlang_file_form.html';
$modversion['templates'][5]['description'] = '';
$modversion['templates'][6]['file'] = 'xlang_mail_show.html';
$modversion['templates'][6]['description'] = '';
$modversion['templates'][7]['file'] = 'xlang_mail_form.html';
$modversion['templates'][7]['description'] = '';
$modversion['templates'][8]['file'] = 'xlang_word_show.html';
$modversion['templates'][8]['description'] = '';
$modversion['templates'][9]['file'] = 'xlang_word_form.html';
$modversion['templates'][9]['description'] = '';
$modversion['templates'][10]['file'] = 'xlang_word_file.html';
$modversion['templates'][10]['description'] = '';
$modversion['templates'][11]['file'] = 'xlang_log.html';
$modversion['templates'][11]['description'] = '';
$modversion['templates'][12]['file'] = 'xlang_download.html';
$modversion['templates'][12]['description'] = '';
$modversion['templates'][13]['file'] = 'xlang_search.html';
$modversion['templates'][13]['description'] = '';

//---------------------------------------------------------
// Config Settings (only for modules that need config settings generated automatically)
// max length of config_name is 25
// max length of conf_title and conf_desc is 30
//---------------------------------------------------------
$modversion['config'][1]['name']        = 'index_desc';
$modversion['config'][1]['title']       = '_MI_XLANG_CONF_DESC';
$modversion['config'][1]['description'] = '';
$modversion['config'][1]['formtype']    = 'textarea';
$modversion['config'][1]['valuetype']   = 'text';
$modversion['config'][1]['default']     = _MI_XLANG_CONF_DESC_DEF;

//---------------------------------------------------------
// Notification
//---------------------------------------------------------
$modversion['hasNotification'] = 1;
$modversion['notification']['lookup_file'] = 'include/notification.inc.php';
$modversion['notification']['lookup_func'] = 'xlang_notify_iteminfo';

$modversion['notification']['category'][1]['name']           = 'global';
$modversion['notification']['category'][1]['title']          = _MI_XLANG_NOTIFY_GLOBAL;
$modversion['notification']['category'][1]['description']    = _MI_XLANG_NOTIFY_GLOBAL_DSC;
$modversion['notification']['category'][1]['subscribe_from'] = array('index.php');

$modversion['notification']['category'][2]['name']           = 'dirname';
$modversion['notification']['category'][2]['title']          = _MI_XLANG_NOTIFY_DIRNAME;
$modversion['notification']['category'][2]['description']    = _MI_XLANG_NOTIFY_DIRNAME_DSC;
$modversion['notification']['category'][2]['subscribe_from'] = array('index.php');
$modversion['notification']['category'][2]['item_name']      = 'dir_id';
$modversion['notification']['category'][2]['allow_bookmark'] = 1;

$modversion['notification']['category'][3]['name']           = 'language';
$modversion['notification']['category'][3]['title']          = _MI_XLANG_NOTIFY_LANGUAGE;
$modversion['notification']['category'][3]['description']    = _MI_XLANG_NOTIFY_LANGUAGE_DSC;
$modversion['notification']['category'][3]['subscribe_from'] = array('index.php');
$modversion['notification']['category'][3]['item_name']      = 'lang_id';
$modversion['notification']['category'][3]['allow_bookmark'] = 1;

$modversion['notification']['event'][1]['name']          = 'global_modify';
$modversion['notification']['event'][1]['category']      = 'global';
$modversion['notification']['event'][1]['title']         = _MI_XLANG_NOTIFY_GLOBAL_MODIFY;
$modversion['notification']['event'][1]['caption']       = _MI_XLANG_NOTIFY_GLOBAL_MODIFY_CAP;
$modversion['notification']['event'][1]['description']   = _MI_XLANG_NOTIFY_GLOBAL_MODIFY_CAP;
$modversion['notification']['event'][1]['mail_template'] = 'notify_global_modify';
$modversion['notification']['event'][1]['mail_subject']  = _MI_XLANG_NOTIFY_GLOBAL_MODIFY_SBJ;

$modversion['notification']['event'][2]['name']          = 'dirname_modify';
$modversion['notification']['event'][2]['category']      = 'dirname';
$modversion['notification']['event'][2]['title']         = _MI_XLANG_NOTIFY_DIRNAME_MODIFY;
$modversion['notification']['event'][2]['caption']       = _MI_XLANG_NOTIFY_DIRNAME_MODIFY_CAP;
$modversion['notification']['event'][2]['description']   = _MI_XLANG_NOTIFY_DIRNAME_MODIFY_CAP;
$modversion['notification']['event'][2]['mail_template'] = 'notify_global_modify';
$modversion['notification']['event'][2]['mail_subject']  = _MI_XLANG_NOTIFY_GLOBAL_MODIFY_SBJ;

$modversion['notification']['event'][3]['name']          = 'language_modify';
$modversion['notification']['event'][3]['category']      = 'language';
$modversion['notification']['event'][3]['title']         = _MI_XLANG_NOTIFY_LANGUAGE_MODIFY;
$modversion['notification']['event'][3]['caption']       = _MI_XLANG_NOTIFY_LANGUAGE_MODIFY_CAP;
$modversion['notification']['event'][3]['description']   = _MI_XLANG_NOTIFY_LANGUAGE_MODIFY_CAP;
$modversion['notification']['event'][3]['mail_template'] = 'notify_global_modify';
$modversion['notification']['event'][3]['mail_subject']  = _MI_XLANG_NOTIFY_GLOBAL_MODIFY_SBJ;

?>