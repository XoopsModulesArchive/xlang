<?php
// $Id: modinfo.php,v 1.2 2007/12/28 01:28:02 ohwada Exp $
//----------------------------------------------------------------
// Japanese UTF-8
//----------------------------------------------------------------

//================================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//================================================================

// The name of this module
define("_MI_XLANG_NAME", "言語翻訳支援");
define("_MI_XLANG_DESC", "言語ファイルの翻訳を支援する");

// admin menu
define("_MI_XLANG_ADMENU_INDEX", "モジュール一覧");

// config
define("_MI_XLANG_CONF_DESC", "説明文");
define("_MI_XLANG_CONF_DESC_DEF", "<span style='color:#0000ff'>この説明文は管理者画面で編集できます</span><br />英語と自国語(日本語)の２つの言語の対訳表を表示します<br />単語の編集ができます<br />最新版の言語ファイルがダウンロードできます");

//-------------------------------------
// notifications
//-------------------------------------
define('_MI_XLANG_NOTIFY_GLOBAL', '言語翻訳支援 全体');
define('_MI_XLANG_NOTIFY_GLOBAL_DSC', '言語翻訳支援 全体における通知オプション');
define('_MI_XLANG_NOTIFY_DIRNAME', 'モジュール');
define('_MI_XLANG_NOTIFY_DIRNAME_DSC', '表示中のモジュールにおける通知オプション');
define('_MI_XLANG_NOTIFY_LANGUAGE', '言語');
define('_MI_XLANG_NOTIFY_LANGUAGE_DSC', '表示中の言語における通知オプション');
define('_MI_XLANG_NOTIFY_GLOBAL_MODIFY', '修正の通知');
define('_MI_XLANG_NOTIFY_GLOBAL_MODIFY_CAP', '言語翻訳支援 全体に修正があった場合に通知する');
define('_MI_XLANG_NOTIFY_GLOBAL_MODIFY_SBJ', '[{X_SITENAME}] {X_MODULE} : 修正がありました');
define('_MI_XLANG_NOTIFY_DIRNAME_MODIFY', 'モジュール 修正の通知');
define('_MI_XLANG_NOTIFY_DIRNAME_MODIFY_CAP', '表示中のモジュールに修正があった場合に通知する');
define('_MI_XLANG_NOTIFY_LANGUAGE_MODIFY', '言語 修正の通知');
define('_MI_XLANG_NOTIFY_LANGUAGE_MODIFY_CAP', '表示中の言語に修正があった場合に通知する');

?>