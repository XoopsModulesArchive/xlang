<?php
// $Id: form.php,v 1.11 2008/12/21 20:49:33 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

//---------------------------------------------------------
// 2008-12-22 K.OHWADA
// support D3 modules
//---------------------------------------------------------

//=========================================================
// class xlang_form
//=========================================================
class xlang_form extends xlang_error
{
	var $_xlang_token;
	var $_xlang_post;
	var $_pagenavi;

	var $_MODULE_NAME = _MI_XLANG_NAME;
	var $_TITLE_MOD   = _MI_XLANG_NAME;
	var $_TITLE_ADMIN = _XLANG_ADMIN_CP;
	var $_FORM_NAME   = 'xlang_form';
	var $_LIMIT_DEFAULT = 50;
	var $_LIMIT         = 50;
	var $_LENGTH        = 500;
	var $_CHECK_ALL_ID;
	var $_THIS_URL;
	var $_NOTICE_UNDEFINED;

	var $_TABLE_SELECT_WIDTH = '200px';
	var $_TD_SELECT_WIDTH    = '100px';

	var $_is_login_user    = false;
	var $_is_module_admin  = false;
	var $_is_base_language = false;
	var $_flag_contrast_language = true;
	var $_xoops_language;
	var $_xoops_uid   = 0;
	var $_xoops_uname = '';

	var $_conf_index_desc;

	var $_alternate_class = '';
	var $_line_count      = 0;
	var $_token_error     = false;

	var $_DIV_STYLE = 'background-color: #dde1de; border: 1px solid #808080; margin: 5px; padding: 10px 10px 5px 10px; width: 95%; text-align: center; ';
	var $_DIV_ERROR_STYLE = 'background-color: #ffffe0; color: #ff0000; border: #808080 1px dotted; margin:  3px; padding: 3px;';
	var $_SELECTED = 'selected="selected"';

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_form()
{
	$this->xlang_error();

	$this->_xlang_token =& xlang_token::getInstance();
	$this->_xlang_post  =& xlang_post::getInstance();
	$this->_pagenavi    =& xlang_pagenavi::getInstance();

	$this->_THIS_URL     = xoops_getenv('PHP_SELF');
	$this->_CHECK_ALL_ID = $this->_FORM_NAME . '_id';

	$this->_NOTICE_UNDEFINED = $this->highlight( _XLANG_UNDEFINED ) ."<br />\n";

	$this->set_limit( $this->_LIMIT_DEFAULT );

	$this->_init_xoops_param();
}

//---------------------------------------------------------
// header
//---------------------------------------------------------
function build_html_header( $title=null, $charset=null )
{
	if ( empty($title) )
	{	$title = _MI_XLANG_NAME;	}

	if ( empty($charset) )
	{	$charset = _CHARSET;	}

	$text  = '<html><head>'."\n";
	$text .= '<meta http-equiv="Content-Type" content="text/html; charset='. $this->sanitize( $charset ) .'" />'."\n";
	$text .= '<title>'. $this->sanitize( $title ) .'</title>'."\n";
	$text .= '</head><body>'."\n";
	return $text;
}

function build_html_footer( $close=null )
{
	if ( empty($close) )
	{	$close = _CLOSE;	}

	$text  = '<hr />'."\n";
	$text .= '<div style="text-align:center;">';
	$text .= '<input value="'. $close .'" type="button" onclick="javascript:window.close();" />';
	$text .= '</div>'."\n";
	$text .= '</body></html>'."\n";
	return $text;
}

//---------------------------------------------------------
// form
//---------------------------------------------------------
function build_form_begin( $op=null, $id=null )
{
	$text  = '<form name="'. $this->_FORM_NAME .'" action="'. $this->_THIS_URL .'" method="post" >'."\n";
	$text .= $this->build_html_token()."\n";
	if ( $op )
	{	$text .= $this->build_hidden( 'op', $op );	}
	if ( $id )
	{	$text .= $this->build_hidden( 'id', $id );	}
	return $text;
}

function build_js_checkall()
{
	$name     = $this->_FORM_NAME . '_checkall';
	$checkall = "xoopsCheckAll('". $this->_FORM_NAME ."', '". $name ."')";
	$extra    = ' onclick="'.$checkall.'" ';
	$text = '<input type="checkbox" name="'. $name .'" id="'.$name.'" '. $extra .' />'."\n";
	return $text;
}

function build_js_checkbox( $value )
{
	$name = $this->_CHECK_ALL_ID . '[]';
	$text = '<input type="checkbox" name="'. $name .'" id="'. $name .'" value="'. $value .'"  />'."\n";
	return $text;
}

function substite_empty( $str )
{
	if ( empty($str) )
	{	$str = '---';	}
	return $str;
}

function build_select_form( $name, $value, $options, $size=5 )
{
	$text = '<select name="'. $name.'" size="'. $size .'">'."\n";
	foreach ( $options as $k => $v )
	{
		$selected = '';
		if ( $k == $value )
		{	$selected = $this->_SELECTED;	}

		$text .= '<option value="'. $k .'" '. $selected .' >';
		$text .= $v;
		$text .= '</option >'."\n";
	}
	$text .= '</select>'."\n";
	return $text;
}

function build_div_box( $str )
{
	$text = '<div style="'. $this->_DIV_STYLE .'">'. $str ."</div>\n";
	return $text;
}

function build_hidden_dirname( $dirname, $language=null, $file=null, $word=null, $mail=null )
{
	$text = '';
	if ( $dirname )
	{	$text .= $this->build_hidden( 'dirname', $dirname );	}
	if ( $language )
	{	$text .= $this->build_hidden( 'language', $language );	}
	if ( $file )
	{	$text .= $this->build_hidden( 'file', $file );	}
	if ( $word )
	{	$text .= $this->build_hidden( 'word', $word );	}
	if ( $mail )
	{	$text .= $this->build_hidden( 'mail', $mail );	}
	return $text;
}

function build_hidden( $name, $value )
{
	$text = '<input type="hidden" name="'. $this->sanitize( $name ) .'"  value="'. $this->sanitize( $value ) .'" />'."\n";
	return $text;
}

//---------------------------------------------------------
// table element
//---------------------------------------------------------
function build_table_begin()
{
	$text = '<table class="outer" width="100%" cellpadding="4" cellspacing="1">'."\n";
	return $text;
}

function build_list_col( &$row, $key, $class )
{
	$value_s = '';
	if ( isset( $row[$key] ) )
	{
		$value_s = $this->sanitize( $row[$key] );
	}
	$text = '<td class="'. $class .'">' .$value_s .'</td>';
	return $text;
}

function build_line_title( $title )
{
	$text  = '<tr align="center">';
	$text .= '<th colspan="2">'. $title .'</th>';
	$text .= '</tr>'."\n";
	return $text;
}

function build_line_value( $title, $value, $flag=true )
{
	if ( $flag )
	{	$value = $this->sanitize( $value );	}

	$text  = '<tr><td class="head">'. $title .'</td>';
	$text .= '<td class="odd">'. $value .'</td></tr>'."\n";
	return $text;
}

function build_line_label( &$row, $name, $title, $flag=true )
{
	$value_s = '';
	if ( isset( $row[$name] ) )
	{
		$value_s = $row[$name];
		if ( $flag )
		{	$value_s = $this->sanitize( $value_s );	}
	}

	$text  = '<tr><td class="head">'. $title .'</td>';
	$text .= '<td class="odd">';
	$text .= $this->substite_empty( $value_s );
	$text .= "</td></tr>\n";
	return $text;
}

function build_line_text( &$row, $name, $title, $size=50 )
{
	$value_s = '';
	if ( isset( $row[$name] ) )
	{	$value_s = $this->sanitize( $row[$name] );	}

	$text  = '<tr><td class="head">'. $title .'</td>';
	$text .= '<td class="odd">';
	$text .= '<input tyep="text" name="'. $name .'" value="'. $value_s .'" size="'. $size .'" />';
	$text .= "</td></tr>\n";
	return $text;
}

function build_line_textarea( &$row, $name, $title, $rows=5, $cols=80 )
{
	$value_s = '';
	if ( isset( $row[$name] ) )
	{	$value_s = $this->sanitize( $row[$name] );	}

	$text  = '<tr><td class="head">'. $title .'</td>';
	$text .= '<td class="odd">';
	$text .= $this->build_textarea_begin( $name, $rows, $cols );
	$text .= $value_s .'</textarea>';
	$text .= "</td></tr>\n";
	return $text;
}

function build_textarea_begin( $name=null, $rows=5, $cols=80 )
{
	$text = '<textarea name="'. $name .'" rows="'. $rows .'" cols="'. $cols .'">';
	return $text;
}

function build_line_add()
{
	$text  = '<tr><td class="head"></td>';
	$text .= '<td class="head">';
	$text .= '<input type="submit" name="add" value="'. _ADD .'" /> ';
	$text .= "</td></tr>\n";
	return $text;
}

function build_line_edit()
{
	$text  = '<tr><td class="head"></td>';
	$text .= '<td class="head">';
	$text .= '<input type="submit" name="edit"   value="'. _EDIT .'" /> ';
	$text .= '<input type="submit" name="delete" value="'. _DELETE .'" />';
	$text .= "</td></tr>\n";
	return $text;
}

function get_alternate_class()
{
	if ( $this->_line_count % 2 != 0) 
	{
		$class = 'odd';
	}
	else 
	{
		$class = 'even';
	}
	$this->_alternate_class = $class;
	$this->_line_count ++;
	return $class;
}

//---------------------------------------------------------
// bread_crumb
//---------------------------------------------------------
function build_bread_crumb_mod( $dirname, $language=null, $file=null, $word=null, $mail=null, $charset=null )
{
	$text = $this->build_link_index_mod();
	if ( $dirname )
	{
		$text .= ' &gt;&gt; ';
		$text .= $this->build_a_tag( 'index.php', null, $dirname );
		$text .= $this->sanitize( $dirname ) .'</a>';
	}
	if ( $language )
	{
		$text .= ' &gt;&gt ';
		$text .= $this->build_a_tag( 'index.php', null, $dirname, $language );
		$text .= $this->sanitize( $language ) .'</a>';
	}
	if ( $file )
	{
		$text .= ' &gt;&gt ';
		$text .= $this->build_a_tag( 'file.php', null, $dirname, $language, $file );
		$text .= $this->sanitize( $file ) .'</a>';
	}
	if ( $word )
	{
		$text .= ' &gt;&gt ';
		$text .= $this->build_a_tag( 'word.php', null, $dirname, $language, $file, $word );
		$text .= $this->sanitize( $word ) .'</a>';
	}
	if ( $mail )
	{
		$text .= ' &gt;&gt ';
		$text .= $this->build_a_tag( 'mail.php', null, $dirname, $language, $file, $word, $mail );
		$text .= $this->sanitize( $mail ) .'</a>';
	}
	if ( $charset )
	{
		$text .= ' &gt;&gt ';
		$text .= $this->sanitize( $charset );
	}
	return $text;
}

function build_bread_crumb_admin( $dirname, $language=null, $file=null, $word=null, $mail=null )
{
	$text = $this->build_link_index_admin();
	if ( $dirname )
	{
		$text .= ' &gt;&gt; ';
		$text .= $this->build_a_tag( 'admin/index.php', null, $dirname );
		$text .= $this->sanitize( $dirname ) .'</a>';
	}
	if ( $language )
	{
		$text .= ' &gt;&gt ';
		$text .= $this->build_a_tag( 'admin/index.php', null, $dirname, $language );
		$text .= $this->sanitize( $language ) .'</a>';
	}
	if ( $file )
	{
		$text .= ' &gt;&gt ';
		$text .= $this->build_a_tag( 'admin/file_manage.php', null, $dirname, $language, $file );
		$text .= $this->sanitize( $file ) .'</a>';
	}
	if ( $word )
	{
		$text .= ' &gt;&gt ';
		$text .= $this->build_a_tag( 'admin/word_manage.php', null, $dirname, $language, $file, $word );
		$text .= $this->sanitize( $word ) .'</a>';
	}
	if ( $mail )
	{
		$text .= ' &gt;&gt ';
		$text .= $this->build_a_tag( 'admin/mail_manage.php', null, $dirname, $language, $file, $word, $mail );
		$text .= $this->sanitize( $mail ) .'</a>';
	}
	return $text;
}

function build_bread_crumb_self( $title, $path, $op, $dirname, $language=null, $file=null, $word=null, $mail=null )
{
	$text = '<a href="'. XLANG_URL .'/'. $path .'">'. $title .'</a>';
	if ( $dirname )
	{
		$text .= ' &gt;&gt; ';
		$text .= $this->build_a_tag( $path, $op, $dirname );
		$text .= $this->sanitize( $dirname ) .'</a>';
	}
	if ( $language )
	{
		$text .= ' &gt;&gt ';
		$text .= $this->build_a_tag( $path, $op, $dirname, $language );
		$text .= $this->sanitize( $language ) .'</a>';
	}
	if ( $file )
	{
		$text .= ' &gt;&gt ';
		$text .= $this->build_a_tag( $path, $op, $dirname, $language, $file );
		$text .= $this->sanitize( $file ) .'</a>';
	}
	if ( $word )
	{
		$text .= ' &gt;&gt ';
		$text .= $this->build_a_tag( $path, $op, $dirname, $language, $file, $word );
		$text .= $this->sanitize( $word ) .'</a>';
	}
	if ( $mail )
	{
		$text .= ' &gt;&gt ';
		$text .= $this->build_a_tag( $path, $op, $dirname, $language, $file, $word, $mail );
		$text .= $this->sanitize( $mail ) .'</a>';
	}
	return $text;
}

function build_link_index_mod()
{
	$text  = $this->build_a_tag( 'index.php' );
	$text .= $this->sanitize( $this->_TITLE_MOD ) . '</a>';
	return $text;
}

function build_link_index_admin()
{
	$text  = $this->build_a_tag( 'admin/index.php' );
	$text .= $this->sanitize( $this->_TITLE_ADMIN ) . '</a>';
	return $text;
}

function build_a_tag( $path, $op=null, $dirname=null, $language=null, $file=null, $word=null, $mail=null, $charset=null )
{
	$text = '<a href="'. $this->build_url( $path, $op, $dirname, $language, $file, $word, $mail, $charset ) .'">';
	return $text;
}

function build_url_by_path( $script_path=null, $op=null, $xoops_path=null, $dirname=null, $language=null, $file=null, $word=null, $mail=null, $charset=null )
{
	$url = $this->build_url( $script_path, $op, $dirname, $language, $file, $word, $mail, $charset );

	if ( $xoops_path )
	{	$url .= '&amp;path='.$this->sanitize( $xoops_path );	}

	return $url;
}

function build_url( $path=null, $op=null, $dirname=null, $language=null, $file=null, $word=null, $mail=null, $charset=null )
{
	$url = '';
	if ( $path )
	{
		$url .= XLANG_URL .'/'. $path;
	}

	$query = $this->build_url_query( $op, $dirname, $language, $file, $word, $mail, $charset );
	if ( $query )
	{
		$url .= '?' . $query;
	}
	return $url;
}

function build_url_query( $op=null, $dirname=null, $language=null, $file=null, $word=null, $mail=null, $charset=null  )
{
	$query = null;
	$arr   = array();
	if ( $op )
	{	$arr[] = 'op='.$this->sanitize( $op );	}
	if ( $dirname )
	{	$arr[] = 'dirname='.$this->sanitize( $dirname );	}
	if ( $language )
	{	$arr[] = 'language='.$this->sanitize( $language );	}
	if ( $file )
	{	$arr[] = 'file='.$this->sanitize( $file );	}
	if ( $word )
	{	$arr[] = 'word='.$this->sanitize( $word );	}
	if ( $mail )
	{	$arr[] = 'mail='.$this->sanitize( $mail );	}
	if ( $charset )
	{	$arr[] = 'charset='.$this->sanitize( $charset );	}
	if ( count($arr) )
	{	$query = implode( '&amp;', $arr );	}
	return $query;
}

//---------------------------------------------------------
// language select
//---------------------------------------------------------
function &get_contrast_language_options( &$lang_arr, $language )
{
	$arr = array();
	foreach ( $lang_arr as $lang )
	{
		if ( $lang != $language )
		{
			$arr[] = $lang;
		}
	}
	asort( $arr );
	return $arr;
}

function &get_new_language_options( &$lang_arr )
{
	$charset_file =& xlang_charset_file::getInstance();
	$charset_file->read_charset_file();
	$flag_convert = $charset_file->_flag_convert;

	$arr = array();

	if ( $flag_convert )
	{
		$charset_arr =& $charset_file->_xlang_charset_array;
		foreach ( $charset_arr as $charset_language => $charset )
		{
			if ( !in_array( $charset_language, $lang_arr ) )
			{
				$arr[] = $charset_language;
			}
		}

		asort( $arr );
	}

	return $arr;
}

function get_contrast_language( $language )
{
	if ( $language == _XLANG_C_BASE_LANGUAGE )
	{
		if( $this->_xoops_language == _XLANG_C_BASE_LANGUAGE ) {
			$this->_flag_contrast_language = false;
		} else {
// set my language
			$language = $this->_xoops_language;
		}
	}
	else
	{
		$language = _XLANG_C_BASE_LANGUAGE; 
	}
	return $language;
}

//---------------------------------------------------------
// flag image
//---------------------------------------------------------
function build_language_image_img_tag( $language )
{
	$link = null;
	$image = $this->get_language_image( $language, false );
	if ( $image )
	{
		$link = '<img src="'. $this->build_language_image_url( $image ) .'" />';
	}
	return $link;
}

function build_language_image_url( $image )
{
	$url = XLANG_URL .'/images/flag/'. $this->sanitize( $image );
	return $url;
}

function get_language_image( $language, $flag_default=false )
{
	$image = $language . '.gif';
	if ( file_exists( XLANG_ROOT_PATH.'/images/flag/'.$image ) )
	{	return $image;	}

	if ( $flag_default )
	{	return $this->_DEFAULT_IMAGE;	}

	return null;
}

//---------------------------------------------------------
// update
//---------------------------------------------------------
function judge_word_update( $word_time, $file_time, $log_time )
{
	if ( $word_time > $file_time )
	{	return true;	}

	if ( $log_time > $file_time )
	{	return true;	}

	return false;
}

//---------------------------------------------------------
// footer
//---------------------------------------------------------
function build_admin_footer()
{
	$text  = "<br /><hr />\n";
	$text .= $this->build_execution_time();
	$text .= $this->build_memory_usage();
	return $text;
}

function build_execution_time()
{
	$text = 'execution time : '.$this->get_execution_time().' sec'."<br />\n";
	return $text;
}

function build_memory_usage()
{
	$usage = $this->get_memory_usage();
	if ( $usage )
	{
		$text  = 'memory usage : '.$usage.' MB'."<br />\n";
		return $text;
	}
	return null;
}

function get_execution_time()
{
	list($usec, $sec) = explode(" ",microtime()); 
	$time = floatval($sec) + floatval($usec)- XLANG_TIME_START; 
	$exec = sprintf("%6.3f", $time);
	return $exec;
}

function get_memory_usage()
{
	if ( function_exists('memory_get_usage') )
	{
		$usage = sprintf("%6.3f",  memory_get_usage() / 1000000 );
		return $usage;
	}
	return null;
}

function get_powered_by()
{
	$text  = '<div align="right">';
	$text .= '<a href="http://linux2.ohwada.net/" target="_blank">';
	$text .= '<span style="font-size : 80%;">Powered by Happy Linux</span>';
	$text .= "</a></div>\n";
	return $text;
}

//---------------------------------------------------------
// keyword
//---------------------------------------------------------
function &parse_keywords( $keywords, $andor='AND' )
{
	$keyword_min   = $this->get_xoops_search_keyword_min();
	$keyword_array = array();
	$ignore_array  = array();

	if ( $keywords == '' )
	{
		$arr = array( $keyword_array, $ignore_array );
		return $arr;
	}

	if ( $andor == 'exact' ) 
	{
		$keyword_array = array( $keywords );
	}
	else
	{
		$temp_arr = preg_split( '/[\s,]+/', $keywords );

		foreach ($temp_arr as $q) 
		{
			$q = trim($q);

			if ( strlen($q) >= $keyword_min ) 
			{
				$keyword_array[] = $q;
			}
			else 
			{
				$ignore_array[] = $q;
			}
		}
	}

	$arr = array( $keyword_array, $ignore_array );
	return $arr;
}

//---------------------------------------------------------
// paginavi
//---------------------------------------------------------
function set_limit_by_post()
{
	$limit = $this->_xlang_post->get_post_get( 'limit' );
	if ( $limit )
	{
		$this->set_limit( $limit );
	}
}

function set_limit( $val )
{
	$this->_LIMIT = intval($val);
	$this->_pagenavi->setPerpage( $this->_LIMIT );
}

function get_pagenavi_start( $total )
{
	$this->_pagenavi->setTotal( $total );
	$this->_pagenavi->getGetPage();
	return $this->_pagenavi->calcStart();
}

function get_pagenavi_end()
{
	return $this->_pagenavi->calcEnd();
}

function build_pagenavi( $path=null, $op=null, $dirname=null, $language=null, $file=null, $word=null, $mail=null )
{
	$script = $this->build_url( $path, $op, $dirname, $language, $file, $word, $mail );
	$script = $this->add_pagenavi_script_limit( $script, $this->_LIMIT );
	$text  = '<div align="center">';
	$text .= $this->_pagenavi->build( $script );
	$text .= "</div><br />\n";
	return $text;
}

function add_pagenavi_script_limit( $script, $limit )
{
	$type = $this->_pagenavi->_analyze_script_type( $script );

	if ($type == 1)
	{
		$add = "limit=";
	}
	elseif ($type == 2)
	{
		$add = "&limit=";
	}
	else
	{
		$add = "?limit=";
	}

	$script_new = $script.$add.$limit;

	return $script_new;
}

function build_form_pagenavi_limit( $dirname, $language=null, $file=null, $word=null, $mail=null )
{
	$text  = '<div align="center">';
	$text .= '<form name="'. $this->_FORM_NAME .'" action="'. $this->_THIS_URL .'" method="get" >'."\n";
	$text .= $this->build_hidden_dirname( $dirname, $language, $file, $word, $mail );
	$text .= _XLANG_PER_PAGE.' ';
	$text .= '<input type="text" name="limit" id="limit" value="'. $this->_LIMIT .'"  />'."\n";
	$text .= '<input type="submit" name="sumit" value="'. _XLANG_SET .'" />';
	$text .= "</form>\n";
	$text .= "</div><br />\n";
	return $text;
}

//---------------------------------------------------------
// token
//---------------------------------------------------------
function get_token()
{
	return $this->_xlang_token->get_gticket_token();
}

function build_html_token()
{
	return $this->_xlang_token->build_gticket_html_token();
}

function check_token()
{
	$ret = $this->_xlang_token->check_gticket_token();
	if ( $ret )
	{
		$this->_token_error = false;
		return true;
	}

	$this->_token_error = true;
	return false;
}

//---------------------------------------------------------
// xoops template
//---------------------------------------------------------
function set_template( $dirname=null, $language=null, $file=null, $word=null, $mail=null )
{
	global $xoopsTpl;
	$this->set_template_by_tpl_obj( $xoopsTpl, $dirname, $language, $file, $word, $mail );
}

function set_template_by_tpl_obj( &$tpl_obj, $dirname=null, $language=null, $file=null, $word=null, $mail=null )
{
	$group_handler =& xlang_group_handler::getInstance();

	$tpl_obj->assign( 'module_name',     $this->_MODULE_NAME );
	$tpl_obj->assign( 'module_name_s',   $this->sanitize( $this->_MODULE_NAME ) );
	$tpl_obj->assign( 'index_desc',      $this->_conf_index_desc );
	$tpl_obj->assign( 'is_module_admin', $this->_is_module_admin );

	if ( $dirname )
	{
		$tpl_obj->assign( 'dir_id', 
			$group_handler->get_cached_id_by_dirname( _XLANG_C_KIND_DIRNAME, $dirname ) );
		$tpl_obj->assign( 'dirname',    $dirname );
		$tpl_obj->assign( 'dirname_s',  $this->sanitize( $dirname ) );
	}

	if ( $language )
	{
		$language_image = $this->get_language_image( $language );
		$tpl_obj->assign( 'lang_id', 
			$group_handler->get_cached_id_by_dirname( _XLANG_C_KIND_LANGUAGE, $dirname, $language ) );
		$tpl_obj->assign( 'language',         $language );
		$tpl_obj->assign( 'language_s',       $this->sanitize( $language ) );
		$tpl_obj->assign( 'language_image',   $language_image );
		$tpl_obj->assign( 'language_image_s', $this->sanitize( $language_image ) );
	}

	if ( $file )
	{
		$file_group_handler =& xlang_file_group_handler::getInstance();

		$tpl_obj->assign( 'file_id', 
			$file_group_handler->get_cached_file_id_by_file( $dirname, $language, $file ) );
		$tpl_obj->assign( 'file',   $file );
		$tpl_obj->assign( 'file_s', $this->sanitize( $file ) );
	}

	if ( $word )
	{
		$word_group_handler =& xlang_word_group_handler::getInstance();

		$tpl_obj->assign( 'word_id', 
			$word_group_handler->get_cached_word_id_by_word( $dirname, $language, $file, $word ) );
		$tpl_obj->assign( 'word',   $word );
		$tpl_obj->assign( 'word_s', $this->sanitize( $word ) );
	}

	if ( $mail )
	{
		$mail_group_handler =& xlang_mail_group_handler::getInstance();

		$tpl_obj->assign( 'mail_id', 
			$mail_group_handler->get_cached_mail_id_by_mail( $dirname, $language, $mail ) );
		$tpl_obj->assign( 'mail',   $mail );
		$tpl_obj->assign( 'mail_s', $this->sanitize( $mail ) );
	}
}

//---------------------------------------------------------
// xoops notify
//---------------------------------------------------------
function xoops_trigger_event_by_dirname( $url, $content, $dirname, &$lang_arr )
{
	$group_handler =& xlang_group_handler::getInstance();

	$dir_id = $group_handler->get_cached_id_by_dirname( _XLANG_C_KIND_DIRNAME,  $dirname );

	$lang_id_arr = array();
	$lang_arr    = array_unique( $lang_arr );

	if ( is_array($lang_arr) && count($lang_arr) )
	{
		foreach ( $lang_arr as $lang )
		{
			$lang_id_arr[] = $group_handler->get_cached_id_by_dirname( _XLANG_C_KIND_LANGUAGE, $dirname, $lang );
		}
	}

	$this->xoops_trigger_event( $url, $content, $dir_id, $lang_id_arr );
}

function xoops_trigger_event( $url, $content, $dir_id, &$lang_id_arr )
{
	$tags = array();
	$tags['MODIFY_URL']     = $url;
	$tags['MODIFY_CONTENT'] = $content;

	$notification_handler =& xoops_gethandler('notification');
	$notification_handler->triggerEvent('global',   0,        'global_modify',   $tags);
	$notification_handler->triggerEvent('dirname',  $dir_id,  'dirname_modify',  $tags);

	if ( is_array($lang_id_arr) && count($lang_id_arr) )
	{
		foreach ( $lang_id_arr as $lang_id )
		{
			$notification_handler->triggerEvent('language', $lang_id, 'language_modify', $tags);
		}
	}
}

//---------------------------------------------------------
// xoops 
//---------------------------------------------------------
function _init_xoops_param()
{
	global $xoopsConfig, $xoopsModuleConfig, $xoopsUser, $xoopsModule;

	$this->_xoops_language  = $xoopsConfig['language'];
	$this->_conf_index_desc = $xoopsModuleConfig['index_desc'];

	if ( $this->_xoops_language == _XLANG_C_BASE_LANGUAGE )
	{	$this->_is_base_language = true;	}

	if ( is_object($xoopsModule) )
	{
		$this->_MODULE_ID   = $xoopsModule->mid();
		$this->_MODULE_NAME = $xoopsModule->getVar('name', 'n');
		$this->_TITLE_MOD   = $this->_MODULE_NAME;
	}

	if ( is_object($xoopsUser) )
	{
		$this->_is_login_user = true;
		$this->_xoops_uid     = $xoopsUser->getVar('uid');
		$this->_xoops_uname   = $xoopsUser->getVar('uname');

		if ( $xoopsUser->isAdmin( $this->_MODULE_ID ) ) 
		{
			$this->_is_module_admin = true;
		}
	}

}

function get_xoops_search_keyword_min()
{
	$config_handler =& xoops_gethandler('config');
	$xoopsConfigSearch =& $config_handler->getConfigsByCat( XOOPS_CONF_SEARCH );
	$keyword_min = $xoopsConfigSearch['keyword_min'];
	return $keyword_min;
}

function get_xoops_user_name( $uid, $usereal=0 )
{
	return XoopsUser::getUnameFromId( $uid, $usereal );
}

function build_xoops_userinfo( $uid, $usereal=0 )
{
	$uname = $this->get_xoops_user_name( $uid, $usereal );

	$uid = intval($uid);
	if ( $uid == 0 )
	{	return $uname;	}

	$text  = '<a href="'. XOOPS_URL .'/userinfo.php?uid='. $uid .'">'. $uname .'</a>';
	return $text;
}

function check_login()
{
	if ( $this->_is_login_user ) 
	{	return true;	}

	redirect_header( XOOPS_URL.'/user.php', 3, _XLANG_MUST_LOGIN );
	exit();
}

function get_url_preferences()
{
// for Cube 2.1
	if( defined( 'XOOPS_CUBE_LEGACY' ) ) 
	{
		$url = '/modules/legacy/admin/index.php?action=PreferenceEdit&amp;confmod_id=';
	}
	else
	{
		$url = '/modules/system/admin.php?fct=preferences&amp;op=showmod&amp;mod=';
	}
	$url_pref = XOOPS_URL . $url . $this->_MODULE_ID;
	return $url_pref;
}

// --- class end ---
}

?>