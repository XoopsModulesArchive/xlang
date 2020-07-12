<?php
// $Id: index.php,v 1.11 2008/12/21 20:49:33 ohwada Exp $

//---------------------------------------------------------
// 2008-12-22 K.OHWADA
// support D3 modules
//---------------------------------------------------------

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

include_once 'admin_header.php';

//=========================================================
// class xlang_admin_index
//=========================================================
class xlang_admin_index extends xlang_form
{
	var $_xoops_module_handler;
	var $_word_group_handler;
	var $_mail_group_handler;
	var $_template_group_handler;
	var $_group_handler;
	var $_file_handler;
	var $_log_handler;
	var $_charset_file;
	var $_language_file;
	var $_option_file;

	var $_line_count = 0;
	var $_base_image;
	var $_my_image;
	var $_html_token;
	var $_has_trust_path = false;

	var $_DEFAULT_IMAGE   = 'install.gif';
	var $_TH_IMPORT_WIDTH = '70px';
	var $_ROWS =  40;
	var $_COLS = 100;

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_admin_index()
{
	$this->xlang_form();

	$this->_xoops_module_handler   =& xoops_gethandler('module');
	$this->_word_group_handler     =& xlang_word_group_handler::getInstance();
	$this->_mail_group_handler     =& xlang_mail_group_handler::getInstance();
	$this->_template_group_handler =& xlang_template_group_handler::getInstance();
	$this->_group_handler          =& xlang_group_handler::getInstance();
	$this->_file_handler           =& xlang_file_handler::getInstance();
	$this->_log_handler            =& xlang_log_handler::getInstance();
	$this->_charset_file           =& xlang_charset_file::getInstance();
	$this->_language_file          =& xlang_language_file::getInstance();
	$this->_option_file            =& xlang_option_file::getInstance();

	$this->_init();

}

function _init()
{
	$this->_base_image = $this->get_language_image( _XLANG_C_BASE_LANGUAGE, true );
	$this->_my_image   = $this->get_language_image( $this->_xoops_language, true );
	$this->_html_token = $this->_xlang_token->get_original_html_token();
	
	if ( defined('XOOPS_TRUST_PATH') && XOOPS_TRUST_PATH ) {
		$this->_has_trust_path = true;
	}
}

//---------------------------------------------------------
// public
//---------------------------------------------------------
function main()
{
	xoops_cp_header();

	switch ( $this->_get_op() )
	{
		case 'info':
			$this->_print_info();
			break;

		case 'language':
			$this->_print_list_language();
			break;

		case 'file':
			$this->_print_list_file();
			break;

		case 'template':
			$this->_print_list_template();
			break;

		case 'dirname':
		default:
			$this->_print_list_dirname();
			break;
	}

	echo $this->build_index_footer();
	xoops_cp_footer();
	exit();
}

function _get_op()
{
	$op       = $this->_xlang_post->get_get( 'op' );
	$dirname  = $this->_xlang_post->get_get( 'dirname' );
	$language = $this->_xlang_post->get_get( 'language' );

	if ( $op )
	{	return $op;	}
	if ( $dirname && $language )
	{	return 'file';	}
	if ( $dirname )
	{	return 'language';	}
	return '';
}

//---------------------------------------------------------
// list_dirname
//---------------------------------------------------------
function _print_list_dirname()
{
	echo "<h3>". _MI_XLANG_NAME ."</h3>\n";
	echo _MI_XLANG_DESC ."<br /><br />\n";

	$group_count      = $this->_group_handler->get_count_all();
	$file_count       = $this->_file_handler->get_count_all();
	$word_count       = $this->_word_group_handler->get_count_all();
	$mail_count       = $this->_mail_group_handler->get_count_all();
	$template_count   = $this->_template_group_handler->get_count_all();
	$log_count        = $this->_log_handler->get_count_all();

	echo "<ul>\n";
	echo '<li><a href="'. $this->get_url_preferences() .'">';
	echo  _PREFERENCES ."</a><br /><br /></li>\n";
	echo '<li><a href="'. XLANG_URL .'/admin/index.php?op=info">';
	echo  _AM_XLANG_SERVER_INFO ."</a><br /><br /></li>\n";
	echo '<li><a href="'. XLANG_URL .'/admin/group_manage.php">';
	echo  _AM_XLANG_GROUP_MANAGE .'</a> ';
	echo '('. $group_count .')' . "<br /><br /></li>\n";
	echo '<li><a href="'. XLANG_URL .'/admin/file_manage.php">';
	echo  _AM_XLANG_FILE_MANAGE .'</a> ';
	echo '('. $file_count .')' . "<br /><br /></li>\n";
	echo '<li><a href="'. XLANG_URL .'/admin/word_manage.php">';
	echo  _AM_XLANG_WORD_MANAGE .'</a>';
	echo '('. $word_count .')' . "<br /><br /></li>\n";
	echo '<li><a href="'. XLANG_URL .'/admin/mail_manage.php">';
	echo  _AM_XLANG_MAIL_MANAGE .'</a>';
	echo '('. $mail_count .')' . "<br /><br /></li>\n";
	echo '<li><a href="'. XLANG_URL .'/admin/template_manage.php">';
	echo  _AM_XLANG_TEMPLATE_MANAGE .'</a>';
	echo '('. $template_count .')' . "<br /><br /></li>\n";
	echo '<li><a href="'. XLANG_URL .'/admin/log_manage.php">';
	echo  _AM_XLANG_LOG_MANAGE .'</a>';
	echo '('. $log_count .')' . "<br /><br /></li>\n";
	echo '<li><a href="'. XLANG_URL .'/admin/table_manage.php">';
	echo  _AM_XLANG_TABLE_MANAGE ."</a><br /><br /></li>\n";
	echo '<li><a href="'. XLANG_URL .'/index.php">';
	echo  _AM_XLANG_GOTO_MODULE ."</a><br /><br /></li>\n";
	echo "</ul>\n";

	if ( !$this->_check_charset() )
	{
		xoops_error( _AM_XLANG_WARNING_CHARSET );
		$msg  = _AM_XLANG_MORE_INFO.' ';
		$msg .= '<a href="'. XLANG_URL .'/admin/index.php?op=info">';
		$msg .=  _AM_XLANG_SERVER_INFO ."</a>";
		echo $this->build_div_box( $msg );
	}

	echo "<h4>". _MI_XLANG_ADMENU_INDEX ."</h4>\n";

	$colspan = 3;
	if ( $this->_is_base_language )
	{	$colspan = 2;	}

    echo $this->build_table_begin();

	echo '<tr align="center">';
	echo '<th width="100px">'. _AM_XLANG_LOGO .'</th>';
	echo '<th>'. _XLANG_MODULE .'</th>';
	echo '<th colspan="'.$colspan.'" >'. _AM_XLANG_IMPORT_FILE .'</th>';
	echo '</tr>'."\n";

	echo '<tr align="center">';
	echo '<th></th>';
	echo '<th></th>';
	if ( !$this->_is_base_language )
	{
		echo '<th width="'. $this->_TH_IMPORT_WIDTH .'" >';
		echo $this->sanitize( ucfirst( $this->_xoops_language ) );
		echo '</th>';
	}
	echo '<th width="'. $this->_TH_IMPORT_WIDTH .'" >';
	echo ucfirst( _XLANG_C_BASE_LANGUAGE );
	echo '</th>';
	echo '<th width="'. $this->_TH_IMPORT_WIDTH .'" >'. _XLANG_TEMPLATE .'</th>';
	echo '</tr>'."\n";

	$dir_arr = array();

	$dir_word_arr =& $this->_word_group_handler->get_dirnames_group_by_dirname();
	if ( is_array($dir_word_arr) && count($dir_word_arr) )
	{
		foreach ( $dir_word_arr as $dirname )
		{
			$dir_arr[] = $dirname;
			$this->_print_list_dirname_line( $dirname );
		}
	}

	echo '<tr align="center"><th colspan="5">un imported</th></tr>'."\n";

	$mod_objes =& $this->_get_module_objs();
	foreach ( $mod_objes as $obj )
	{
		$dirname = $obj->getVar('dirname', 'n');
		if ( in_array( $dirname, $dir_arr ) )
		{	continue;	}

		$dir_arr[] = $dirname;
		$this->_print_list_dirname_line( $dirname );
	}

	echo '<tr align="center"><th colspan="5">un installed</th></tr>'."\n";

	$dir_file_arr =& $this->_language_file->get_root_module_dirs();
	foreach ( $dir_file_arr as $dirname )
	{
		if ( in_array( $dirname, $dir_arr ) )
		{	continue;	}

		$this->_print_list_dirname_line( $dirname );
	}

	echo "</table>\n";
}

function _print_list_dirname_line( $dirname )
{
	$dirname_s = $this->sanitize( $dirname );


	$class     =  $this->get_alternate_class();
	$module    =& $this->_get_module( $dirname );

	$base_path     = XOOPS_ROOT_PATH;
	$my_path       = XOOPS_ROOT_PATH;
	$base_form     = '-';
	$my_form       = '-';
	$template_form = '-';

	$view_url = $this->build_a_tag( 'admin/index.php', null, $dirname );

	$base_root_file_count  = $this->_language_file->get_count_language_files_by_path_dirname(
		XOOPS_ROOT_PATH, $dirname, _XLANG_C_BASE_LANGUAGE );
	$base_table_count = $this->_word_group_handler->get_count_by_dirname(
		 $dirname, _XLANG_C_BASE_LANGUAGE );

	$my_root_file_count  = $this->_language_file->get_count_language_files_by_path_dirname(
		XOOPS_ROOT_PATH, $dirname, $this->_xoops_language );
	$my_table_count = $this->_word_group_handler->get_count_by_dirname(
		$dirname, $this->_xoops_language );

	$template_file_count  = $this->_option_file->get_count_template_option_files_by_dirname( $dirname );
	$template_table_count = $this->_template_group_handler->get_count_by_dirname( $dirname );

	if ( $this->_has_trust_path ) {
		if ( $base_root_file_count == 0 ) {
			$base_path = XOOPS_TRUST_PATH;
			$base_trust_file_count = $this->_language_file->get_count_language_files_by_path_dirname(
				XOOPS_TRUST_PATH, $dirname, _XLANG_C_BASE_LANGUAGE );
		}
		if ( $my_root_file_count == 0 ) {
			$my_path = XOOPS_TRUST_PATH;
			$my_trust_file_count = $this->_language_file->get_count_language_files_by_path_dirname(
				XOOPS_TRUST_PATH, $dirname, $this->_xoops_language );
		}
	}

	if ( $base_root_file_count || $base_trust_file_count ) 
	{
		if ( $base_table_count ) 
		{
			$base_form = 'imported';
		}
		else
		{
			$base_form = $this->_build_form_import(
				$base_path, $dirname, _XLANG_C_BASE_LANGUAGE, null, $this->_base_image );
		}
	}

	if ( $my_root_file_count || $my_trust_file_count ) 
	{
		if ( $my_table_count ) 
		{
			$my_form = 'imported';
		}
		else
		{
			$my_form = $this->_build_form_import(
				$my_path, $dirname, $this->_xoops_language, null, $this->_my_image );
		}
	}

	if ( $template_file_count )
	{
		if ( $template_table_count )
		{
			$template_form = 'imported';
		}
		else
		{
			$template_form = $this->_build_form_import(
				XOOPS_ROOT_PATH, $dirname, null, null, $this->_DEFAULT_IMAGE, 'template_dirname' );
		}
	}

	echo '<tr>';
	echo '<td class="'. $class .'" align="center">';
	echo $view_url . $module['image_link'] .'</a>';
	echo '</td>';
	echo '<td class="'. $class .'">';
	echo $view_url . $dirname_s .'</a>';
	echo '</td>';

	if ( !$this->_is_base_language )
	{
		echo '<td class="'. $class .'" align="center">'. $my_form .'</td>';
	}

	echo '<td class="'. $class .'" align="center">'. $base_form .'</td>';
	echo '<td class="'. $class .'" align="center">'. $template_form .'</td>';
	echo "</tr>\n";
}

//---------------------------------------------------------
// list_language
//---------------------------------------------------------
function _print_list_language()
{
	$dirname   = $this->_xlang_post->get_get( 'dirname' );
	$dirname_s = $this->sanitize( $dirname );

	$this->_print_bread_crumb( $dirname );
	echo "<h3>". _XLANG_LANGUAGE_LIST ."</h3>\n";

	echo $this->build_table_begin();
	echo '<tr align="center">';
	echo '<th>'. _XLANG_LANGUAGE .'</th>';
	echo '<th width="'. $this->_TH_IMPORT_WIDTH .'">'. _AM_XLANG_IMPORT_FILE .'</th>';
	echo '</tr>'."\n";

	$path      =  XOOPS_ROOT_PATH;
	$table_arr =& $this->_word_group_handler->get_languages_group_by_language( $dirname );
	$root_arr  =& $this->_language_file->get_language_dirs_by_path_dirname( XOOPS_ROOT_PATH, $dirname );
	$trust_arr =  null;
	$lang_arr  =  array();

	if ( $this->_has_trust_path ) {
		if ( !is_array($root_arr) || !count($root_arr) ) 
		{
			$path      =  XOOPS_TRUST_PATH;
			$trust_arr =& $this->_language_file->get_language_dirs_by_path_dirname( XOOPS_TRUST_PATH, $dirname );
		}
	}

	$lang_arr =& $this->_array_merge( $table_arr, $root_arr, $trust_arr );

	foreach ( $lang_arr as $language )
	{
		$language_s = $this->sanitize( $language );

		$image_form  = '-';
		$file_count  = $this->_language_file->get_count_language_files_by_path_dirname(
			$path, $dirname, $language );
		$table_count = $this->_word_group_handler->get_count_by_dirname(
			$dirname, $language );

		if ( $file_count ) 
		{
			if ( $table_count ) 
			{
				$image_form = 'imported';
			}
			else
			{
				$image_form = $this->_build_form_import(
					$path, $dirname, $language, null, $this->get_language_image( $language, true ) );
			}
		}

		$class = $this->get_alternate_class();

		echo '<tr>';
		echo '<td class="'. $class .'">';
		echo $this->build_a_tag( 'admin/index.php', null, $dirname, $language );
		echo $language_s;
		echo '</a></td>';
		echo '<td class="'. $class .'" align="center">'. $image_form .'</td>';
		echo "</tr>\n";

	}

	$template_arr =& $this->_option_file->get_template_option_files_by_dirname( $dirname );
	if ( is_array($template_arr) && count($template_arr) )
	{
		$template   = $template_arr[0]['template'];
		$image_form = 'imported';

		if ( $this->_template_group_handler->get_count_by_dirname( $dirname ) == 0 ) 
		{
			$image_form = $this->_build_form_import(
				$path, $dirname, null, null, $this->_DEFAULT_IMAGE, 'template_dirname' );
		}

		$class = $this->get_alternate_class();

		echo '<tr>';
		echo '<td class="'. $class .'">';
		echo $this->build_a_tag( 'admin/index.php', 'template', $dirname );
		echo 'template files'.'</a>';
		echo ' : '. $this->sanitize( $template ). ' etc';
		echo '</td>';
		echo '<td class="'. $class .'" align="center">'. $image_form .'</td>';
		echo "</tr>\n";

	}

	echo "</table>\n";

}

function &_array_merge( $table_arr, $root_arr, $trust_arr )
{
	$arr = null;

	if ( is_array($table_arr) && count($table_arr) &&
	     is_array($root_arr)  && count($root_arr) )
	{
		$arr = array_unique( array_merge($table_arr, $root_arr) );
	}
	elseif ( is_array($table_arr) && count($table_arr) &&
	         is_array($trust_arr) && count($trust_arr) )
	{
		$arr = array_unique( array_merge($table_arr, $trust_arr) );
	}
	elseif ( is_array($table_arr) && count($table_arr) )
	{
		$arr =& $table_arr;
	}
	elseif ( is_array($root_arr) && count($root_arr) )
	{
		$arr =& $root_arr;
	}
	elseif ( is_array($trust_arr) && count($trust_arr) )
	{
		$arr =& $trust_arr;
	}
	
	return $arr;
}

//---------------------------------------------------------
// list_file
//---------------------------------------------------------
function _print_list_file()
{

	$dirname  = $this->_xlang_post->get_get( 'dirname' );
	$language = $this->_xlang_post->get_get( 'language' );

	$dirname_s  = $this->sanitize( $dirname );
	$language_s = $this->sanitize( $language );

	$file_path      =  XOOPS_ROOT_PATH;
	$file_table_arr =& $this->_word_group_handler->get_files_group_by_file(  $dirname, $language );
	$file_root_arr  =& $this->_language_file->get_language_files_by_path_dirname(
		XOOPS_ROOT_PATH, $dirname, $language );
	$file_trust_arr =  null;

	$mail_path      =  XOOPS_ROOT_PATH;
	$mail_table_arr =& $this->_mail_group_handler->get_mails_group_by_mail(  $dirname, $language );
	$mail_root_arr  =& $this->_language_file->get_mail_files_by_path_dirname(
		XOOPS_ROOT_PATH, $dirname, $language );
	$mail_trust_arr =  null;

	if ( $this->_has_trust_path ) {
		if ( !is_array($file_root_arr) || !count($file_root_arr) ) 
		{
			$file_path      =  XOOPS_TRUST_PATH;
			$file_trust_arr =& $this->_language_file->get_language_files_by_path_dirname(
				XOOPS_TRUST_PATH, $dirname, $language );
		}
		if ( !is_array($mail_root_arr) || !count($mail_root_arr) ) 
		{
			$mail_path      =  XOOPS_TRUST_PATH;
			$mail_trust_arr =& $this->_language_file->get_mail_files_by_path_dirname(
				XOOPS_TRUST_PATH, $dirname, $language );
		}
	}

	$file_arr =& $this->_array_merge( $file_table_arr, $file_root_arr, $file_trust_arr );
	$mail_arr =& $this->_array_merge( $mail_table_arr, $mail_root_arr, $mail_trust_arr );

	$image = $this->get_language_image( $language, true );

	$this->_print_bread_crumb( $dirname, $language );
	echo "<h3>". _XLANG_FILE_LIST ."</h3>\n";

	echo $this->build_table_begin();

	echo '<tr align="center">';
	echo '<th>'. _XLANG_FILE .'</th>';
	echo '<th>'. _AM_XLANG_SHOW_FILE .'</th>';
	echo '<th width="'. $this->_TH_IMPORT_WIDTH .'">'. _AM_XLANG_IMPORT_FILE .'</th>';
	echo '</tr>'."\n";

	foreach ( $file_arr as $file )
	{
		$file_s = $this->sanitize( $file );

		$show = null;
		$image_form = null;

		$table_count = $this->_word_group_handler->get_count_by_dirname(
			$dirname, $language, $file );

		if ( $this->_language_file->exist_language_filename_by_path( $file_path, $dirname, $language, $file ) )
		{
			$show  = '<a href="';
			$show .= $this->build_url_by_path(
				'admin/show_file.php', 'show',  $file_path, $dirname, $language, $file );
			$show .= '" target="_blank">';
			$show .= '['. _XLANG_SHOW .']';
			$show .= '</a>';

			if ( $table_count ) 
			{
				$image_form = $this->_build_form_update(
					$file_path, $dirname, $language, $file );
			}
			else 
			{
				$image_form = $this->_build_form_import(
					$file_path, $dirname, $language, $file, $image, 'file' );
			}
		}

		$class = $this->get_alternate_class();

		echo '<tr>';
		echo '<td class="'. $class .'">';
		echo $this->build_a_tag( 'admin/word_manage.php', 'form_file', $dirname, $language, $file );
		echo $file_s;
		echo '</a>';
		echo ' ('.  $table_count .')';
		echo '</td>';
		echo '<td class="'. $class .'" align="center">'. $show .'</td>';
		echo '<td class="'. $class .'" align="center">'. $image_form .'</td>';
		echo "</tr>\n";

	}

	if ( is_array($mail_arr) && count($mail_arr) )
	{
		echo '<tr align="center">';
		echo '<th>'. _XLANG_MAIL .'</th>';
		echo '<th>'. _AM_XLANG_SHOW_FILE .'</th>';
		echo '<th width="'. $this->_TH_IMPORT_WIDTH .'">'. _AM_XLANG_IMPORT_FILE .'</th>';
		echo '</tr>'."\n";
	
		foreach ( $mail_arr as $mail )
		{
			$mail_s = $this->sanitize( $mail );

			$show = null;
			$image_form = null;

			$count = $this->_mail_group_handler->get_count_by_dirname( $dirname, $language, $mail );
		
			if ( $this->_language_file->exist_mail_filename_by_path( $mail_path, $dirname, $language, $mail ) )
			{
				$show  = '<a href="';
				$show .= $this->build_url_by_path(
					'admin/show_file.php', 'mail', $mail_path, $dirname, $language, null, null, $mail );
				$show .= '" target="_blank">';
				$show .= '['. _XLANG_SHOW .']';
				$show .= '</a>';

				if ( $count ) 
				{
					$image_form = $this->_build_form_update(
						$mail_path, $dirname, $language, $mail, 'mail' );
				}
				else 
				{
					$image_form = $this->_build_form_import(
						$mail_path, $dirname, $language, $mail, $image, 'mail' );
				}
			}

			$class = $this->get_alternate_class();

			echo '<tr>';
			echo '<td class="'. $class .'">';
			echo $this->build_a_tag( 'admin/mail_manage.php', 'form_mail',
					$dirname, $language, null, null, $mail );
			echo $mail_s;
			echo '</a>';
			echo ' ('.  $count .')';
			echo '</td>';
			echo '<td class="'. $class .'" align="center">'. $show .'</td>';
			echo '<td class="'. $class .'" align="center">'. $image_form .'</td>';
			echo "</tr>\n";
		}

	}

	echo "</table>\n";
}


//---------------------------------------------------------
// list_template
//---------------------------------------------------------
function _print_list_template()
{
	$dirname   = $this->_xlang_post->get_get( 'dirname' );
	$dirname_s = $this->sanitize( $dirname );

	$this->_print_bread_crumb( $dirname );
	echo "<h3>". _AM_XLANG_TEMPLATE_LIST ."</h3>\n";

	echo $this->build_table_begin();

	echo '<tr align="center">';
	echo '<th>'. _XLANG_TEMPLATE .'</th>';
	echo '<th>'. _AM_XLANG_SHOW_FILE .'</th>';
	echo '<th width="'. $this->_TH_IMPORT_WIDTH .'">'. _AM_XLANG_IMPORT_FILE .'</th>';
	echo '</tr>'."\n";

	$template_arr =& $this->_option_file->get_template_option_files_by_dirname( $dirname );
	foreach ( $template_arr as $row )
	{
		$file       = $row['file'];
		$template   = $row['template'];
		$template_s = $this->sanitize( $template );

		$count = $this->_template_group_handler->get_count_by_dirname( $dirname, $file );
		if ( $count ) 
		{
			$image_form = $this->_build_form_update( $path, $dirname, null, $file, 'template' );
		}
		else 
		{
			$image_form = $this->_build_form_import(
				$path, $dirname, null, $file, $this->_DEFAULT_IMAGE, 'template' );
		}

		$class = $this->get_alternate_class();

		echo '<tr>';
		echo '<td class="'. $class .'">';
		echo $this->build_a_tag( 'admin/template_manage.php', null, $dirname, null, $file );
		echo $template_s;
		echo '</a>';
		echo ' ('.  $count .')';
		echo '</td>';
		echo '<td class="'. $class .'" align="center">';
		echo '<a href="';
		echo $this->build_url( 'admin/show_file.php', 'template', $dirname, null, $template );
		echo '" target="_blank">';
		echo '['. _XLANG_SHOW .']';
		echo '</a></td>';
		echo '<td class="'. $class .'" align="center">'. $image_form .'</td>';
		echo "</tr>\n";

	}

	echo "</table>\n";
}

//---------------------------------------------------------
// common
//---------------------------------------------------------
function &_get_module_objs()
{
	$criteria = new CriteriaCompo();
	$criteria->add( new Criteria('isactive', '1', '=') );
	$arr =& $this->_xoops_module_handler->getObjects( $criteria );
	return $arr;
}

function &_get_module( $dirname )
{
	$mid        = '-';
	$name       = '-';
	$image      = '-';
	$image_link = '-';

	$module =& $this->_xoops_module_handler->getByDirname( $dirname );
	if ( is_object($module) )
	{
		$mid    = $module->getVar('mid');
		$name   = $module->getVar('name', 'n');
		$name_s = $module->getVar('name', 's');
		$image  = $module->getInfo('image');
		$image_url_s = $this->sanitize( XOOPS_URL .'/modules/'. $dirname .'/'. $image );
		$image_link  = '<img src="'. $image_url_s .'" alt="'.$name_s.'" border="0" />';
	}

	$arr = array(
		'mid'        => $mid,
		'name'       => $name,
		'image'      => $image,
		'image_link' => $image_link,
	);

	return $arr;
}

function _build_form_import( $path, $dirname, $language, $file, $image, $op='language' )
{
	$text  = '<form name="xlang_form" action="'. XLANG_URL .'/admin/import.php" method="post" >'."\n";
	$text .= $this->_html_token."\n";
	$text .= '<input type="hidden" name="op"       value="'. $this->sanitize( $op ) .'" />'."\n";
	$text .= '<input type="hidden" name="path"     value="'. $this->sanitize( $path ) .'" />'."\n";
	$text .= '<input type="hidden" name="dirname"  value="'. $this->sanitize( $dirname ) .'" />'."\n";
	$text .= '<input type="hidden" name="language" value="'. $this->sanitize( $language ) .'" />'."\n";
	$text .= '<input type="hidden" name="file"     value="'. $this->sanitize( $file ) .'" />'."\n";
	$text .= '<input type="image" name="submit" src="'. $this->build_language_image_url( $image ) .'" alt="import" />'."\n";
	$text .= '</form>'."\n";
	return $text;
}

function _build_form_update( $path, $dirname, $language, $file, $op='file' )
{
	$text  = '<form name="xlang_form" action="'. XLANG_URL .'/admin/import.php" method="post" >'."\n";
	$text .= $this->_html_token."\n";
	$text .= '<input type="hidden" name="op"       value="'. $this->sanitize( $op ) .'" />'."\n";
	$text .= '<input type="hidden" name="path"     value="'. $this->sanitize( $path ) .'" />'."\n";
	$text .= '<input type="hidden" name="dirname"  value="'. $this->sanitize( $dirname ) .'" />'."\n";
	$text .= '<input type="hidden" name="language" value="'. $this->sanitize( $language ) .'" />'."\n";
	$text .= '<input type="hidden" name="file"     value="'. $this->sanitize( $file ) .'" />'."\n";
	$text .= '<input type="submit" name="submit" value="'. _AM_XLANG_UPDATE .'" />'."\n";
	$text .= '</form>'."\n";
	return $text;
}

function _get_image( $language )
{
	$image = $language . '.gif';
	if ( file_exists( XLANG_ROOT_PATH.'/images/flag/'.$image ) )
	{	return $image;	}
	return $this->_DEFAULT_IMAGE;
}

function _print_bread_crumb( $dirname, $language=null )
{
	echo $this->build_bread_crumb_mod( $dirname, $language );
	echo "<br /><br />\n";
	echo $this->build_bread_crumb_admin( $dirname, $language );
	echo "<br />\n";
}

//---------------------------------------------------------
// info
//---------------------------------------------------------
function _print_info()
{
	echo $this->build_link_index_admin();
	echo ' &gt;&gt ';
	echo _AM_XLANG_SERVER_INFO;

	echo "<h3>". _AM_XLANG_SERVER_INFO ."</h3>\n";

	echo "OS: ". php_uname() ."<br />\n"; 
	echo "MySQL: ". mysql_get_server_info() ."<br />\n"; 
	echo "PHP: ". PHP_VERSION ."<br />\n"; 
	echo "XOOPS: ". XOOPS_VERSION ."<br />\n"; 

	echo "<h4> include/charset.php </h4>\n";

	if ( !$this->_charset_file->exist_charset_file() )
	{
		xoops_error( _AM_XLANG_NOT_EXIST_CHARSET );
		echo $this->build_div_box( _AM_XLANG_RENAME_CHARSET );
		echo "<br />\n";
	}
	elseif ( !$this->_check_charset() )
	{
		xoops_error( _AM_XLANG_WARNING_CHARSET );
		echo "<br />\n";
	}

	echo '<a href="'. XLANG_URL .'/admin/show_file.php?op=charset" target="_blank">';
	echo  _AM_XLANG_SHOW_CHARSET_FILE ."</a><br /><br />\n";

	$mysql_arr =& $this->_word_group_handler->get_mysql_variables();
	$mysql_character_set_client = $this->_get_mysql_character_set_client( $mysql_arr );

	$charset_arr       =& $this->_read_charset_file();
	$charset_error_arr =& $this->_check_charset_file( $charset_arr, $mysql_character_set_client );

	$my_language         = $charset_arr['my_language'];
	$mysql_charset       = $charset_arr['mysql_charset'];
	$charset             = $charset_arr['charset'];
	$force               = $charset_arr['force'];
	$convert             = $charset_arr['convert'];
	$my_lanaguge_error   = $charset_error_arr['my_lanaguge_error'];
	$mysql_charset_error = $charset_error_arr['mysql_charset_error'];
	$charset_error       = $charset_error_arr['charset_error'];
	$convert_error       = $charset_error_arr['convert_error'];

	echo 'XLANG_MY_LANGUAGE : '. $my_language .' ';
	if ( $my_lanaguge_error )
	{	echo $this->highlight( $my_lanaguge_error );	}
	echo "<br />\n";

	echo 'XLANG_MYSQL_CHARSET : '. $mysql_charset .' ';
	if ( $mysql_charset_error )
	{	echo $this->highlight( $mysql_charset_error );	}
	echo "<br />\n";

	echo 'XLANG_CHARSET : '. $charset .' ';
	if ( $charset_error )
	{	echo $this->highlight( $charset_error );	}
	echo "<br />\n";

	echo 'XLANG_MYSQL_CHARSET_FORCE : '. $force .' ';
	echo "<br />\n";

	echo 'XLANG_CONVERT_ENCODING : '. $convert .' ';
	if ( $convert_error )
	{	echo $this->highlight( $convert_error );	}
	echo "<br />\n";

	echo "<h4> XOOPS </h4>\n";

	echo "language : <b>". $this->_xoops_language ."</b><br />\n";
	echo "_CHARSET : <b>". _CHARSET ."</b><br />\n";

	echo "<h4> MySQL </h4>\n";

	foreach ( $mysql_arr as $k => $v )
	{
//mysql 5	
		if ( $k == 'character_set_client' )
		{	$v = '<b>'.$v.'</b>';	}
//mysql 4
		if ( $k == 'character_set' )
		{	$v = '<b>'.$v.'</b>';	}

		echo $k .' : '. $v ."<br>\n";
	}

	echo "<h4> PHP </h4>\n";
	echo "error_reporting: ". error_reporting() ."<br />\n";
	echo "display_errors: ". intval( ini_get('display_errors') ) ."<br />\n";
	echo "magic_quotes_gpc: ". intval( get_magic_quotes_gpc() ) ."<br />\n";
	echo "<br />\n";
	
	if ( function_exists('iconv') )
	{
		echo "iconv: loaded <br />\n";
		if ( function_exists('iconv_get_encoding') )
		{
			$arr = iconv_get_encoding( 'all' );
			foreach ( $arr as $k => $v )
			{
				if ( is_array($v) )
				{
					echo $k .' : '. implode(' ', $v) ."<br>\n";
				}
				else
				{
					echo $k .' : '. $v ."<br>\n";
				}
			}
		}
	}
	else
	{
		echo "iconv: unloaded <br />\n";
	}
	echo "<br />\n";

	if ( function_exists('mb_internal_encoding') )
	{
		echo "mbstring: loaded <br />\n";
		if ( function_exists('mb_get_info') )
		{
			$arr = mb_get_info('all');
			foreach ( $arr as $k => $v )
			{
				if ( is_array($v) )
				{
					echo $k .' : '. implode(' ', $v) ."<br>\n";
				}
				else
				{
					echo $k .' : '. $v ."<br>\n";
				}
			}
		}
	}
	else
	{
		echo "mbstring: unloaded <br />\n";
	}

}

function _check_charset()
{
	$mysql_arr =& $this->_word_group_handler->get_mysql_variables();
	$mysql_character_set_client = $this->_get_mysql_character_set_client( $mysql_arr );

	$charset_arr       =& $this->_read_charset_file();
	$charset_error_arr =& $this->_check_charset_file( $charset_arr, $mysql_character_set_client );

	if ( $charset_error_arr['total_error'] )
	{	return false;	}

	return true;
}

function _get_mysql_character_set_client( &$mysql_arr )
{
// mysql 5
	if ( isset( $mysql_arr['character_set_client'] ) )
	{	return  $mysql_arr['character_set_client'];	}

// mysql 4
	if ( isset( $mysql_arr['character_set'] ) )
	{	return  $mysql_arr['character_set'];	}

	return null;
}

function &_read_charset_file()
{
	$this->_charset_file->read_charset_file();

	$arr = array(
		'my_language'   => $this->_charset_file->_xlang_my_language,
		'mysql_charset' => $this->_charset_file->_my_mysql_charset,
		'charset'       => $this->_charset_file->_my_charset,
		'force'         => $this->_charset_file->_xlang_mysql_charset_force,
		'convert'       => $this->_charset_file->_xlang_convert_encoding,
	);

	return $arr;
}

function &_check_charset_file( &$charset_arr, $mysql_character_set_client )
{
	$my_language   = $charset_arr['my_language'];
	$mysql_charset = $charset_arr['mysql_charset'];
	$charset       = $charset_arr['charset'];
	$convert       = $charset_arr['convert'];

	$my_lanaguge_error   = '';
	$mysql_charset_error = '';
	$charset_error       = '';
	$convert_error       = '';
	$total_error         = false;

	if ( empty($my_language) )
	{	$my_lanaguge_error = _XLANG_UNDEFINED;	}
	elseif ( $my_language != $this->_xoops_language )
	{	$my_lanaguge_error = _AM_XLANG_NOT_MATCH;	}

	if ( empty($mysql_charset) )
	{	$mysql_charset_error = _XLANG_UNDEFINED;	}
	elseif ( $mysql_charset != $mysql_character_set_client )
	{	$mysql_charset_error = _AM_XLANG_NOT_MATCH;	}

	if ( empty($charset) )
	{	$charset_error = _XLANG_UNDEFINED;	}
	elseif ( $charset != _CHARSET )
	{	$charset_error = _AM_XLANG_NOT_MATCH;	}

	if ( $convert === null )
	{	$convert_error = _XLANG_UNDEFINED;	}

	if ( $my_lanaguge_error || $mysql_charset_error || $charset_error || $convert_error )
	{	$total_error = true;	}

	$arr = array(
		'my_lanaguge_error'   => $my_lanaguge_error,
		'mysql_charset_error' => $mysql_charset_error,
		'charset_error'       => $charset_error,
		'convert_error'       => $convert_error,
		'total_error'         => $total_error,
	);

	return $arr;
}

//---------------------------------------------------------
// footer
//---------------------------------------------------------
function build_index_footer()
{
	$text  = "<br /><hr />\n";
	$text .= $this->get_powered_by();
	$text .= $this->build_execution_time();
	$text .= $this->build_memory_usage();
	return $text;
}

// --- class end ---
}

//=========================================================
// main
//=========================================================
$view = new xlang_admin_index();
$view->main();

exit();
// --- main end ---

?>