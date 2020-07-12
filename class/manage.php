<?php
// $Id: manage.php,v 1.5 2007/12/31 11:41:19 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

//=========================================================
// class xlang_manage
//=========================================================
class xlang_manage extends xlang_form
{
	var $_group_handler;
	var $_manage_handler;

	var $_manage_title;
	var $_manage_path;
	var $_manage_content_name;
	var $_manage_total = 0;

	var $_post_id;
	var $_post_gid;
	var $_post_dirname;
	var $_post_language;
	var $_post_file;
	var $_post_word;
	var $_post_mail;

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_manage()
{
	$this->xlang_form();

	$this->_group_handler =& xlang_group_handler::getInstance();

	$this->_post_id       = intval( $this->_xlang_post->get_post_get( 'id' ) );
	$this->_post_gid      = intval( $this->_xlang_post->get_post_get( 'gid' ) );
	$this->_post_dirname  = $this->_xlang_post->get_post_get( 'dirname' );
	$this->_post_language = $this->_xlang_post->get_post_get( 'language' );
	$this->_post_file     = $this->_xlang_post->get_post_get( 'file' );
	$this->_post_word     = $this->_xlang_post->get_post_get( 'word' );
	$this->_post_mail     = $this->_xlang_post->get_post_get( 'mail' );
}

function set_manage_handler( &$handler)
{
	$this->_manage_handler =& $handler;
}

function set_manage_title( $title )
{
	$this->_manage_title = $title;
}

function set_manage_path( $path )
{
	$this->_manage_path = $path;
}

function set_manage_content_name( $name )
{
	$this->_manage_content_name = $name;
}

//---------------------------------------------------------
// handler
//---------------------------------------------------------
function &get_manage_group_by_row( &$row )
{
	$id       = $row['id'];
	$gid      = $row['gid'];
	$dirname  = '';
	$language = '';
	$file     = '';
	$word     = '';
	$mail     = '';
	$error    = false;

	if ( isset( $row['dirname'] ) )
	{
		$dirname  = $row['dirname'];
		$language = $row['language'];
		$file     = $row['file'];
		$word     = isset( $row['word'] ) ? $row['word'] : null;
		$mail     = isset( $row['mail'] ) ? $row['mail'] : null;
	}
	else
	{
		$group_row =& $this->_group_handler->get_row_by_id( $gid );
		if ( is_array($group_row) )
		{
			$dirname  = $group_row['dirname'];
			$language = $group_row['language'];
			$file     = $group_row['file'];
			$word     = $group_row['word'];
			$mail     = $group_row['mail'];
		}
		else
		{
			$error = true;
		}
	}

	$arr = array(
		'dirname'  => $dirname,
		'language' => $language,
		'file'     => $file,
		'word'     => $word,
		'mail'     => $mail,
		'error'    => $error,
	);

	return $arr;
}

//---------------------------------------------------------
// add
//---------------------------------------------------------
function manage_add_with_gid_check()
{
	$gid = $this->_post_gid;

	if ( ! $this->_manage_handler->exist_group_by_gid( $gid ) )
	{
		$this->print_form_with_error( 'not exist group gid = '. $gid );
		exit();
	}

	if ( $this->_manage_handler->get_count_by_gid( $gid ) )
	{
		$this->print_form_with_error( 'already exist gid = '. $gid );
		exit();
	}

	$this->manage_add();
	exit();
}

function manage_add()
{
	$row =& $this->_build_row_by_post();

	$newid = $this->_manage_handler->insert_manage_record( $row );
	if ( !$newid )
	{
		$msg  = 'DB error <br />';
		$msg .= $this->_manage_handler->get_format_error();
		redirect_header( $this->build_form_url(), 3, $msg );
		exit();
	}

	redirect_header( $this->build_form_url( $newid ), 1, 'Added' );
	exit();
}

function build_form_url( $id=0 )
{
	if ( empty($id) )
	{
		$id = $this->_post_id;
	}
	$url = $this->_THIS_URL .'?op=form&amp;id='. intval($id);
	return $url;
}

//---------------------------------------------------------
// edit
//---------------------------------------------------------
function manage_edit_with_gid_check()
{
	$gid = $this->_post_gid;

	if ( ! $this->_manage_handler->exist_group_by_gid( $gid ) )
	{
		$this->print_form_with_error( 'not exist group gid = '. $gid );
		exit();
	}

	$this-> manage_edit();
	exit();
}

function manage_edit()
{
	$row =& $this->_build_row_by_post();

	$ret = $this->_manage_handler->update_manage_record( $row );
	if ( !$ret )
	{
		$msg  = 'DB error <br />';
		$msg .= $this->_manage_handler->get_format_error();
		redirect_header( $this->build_form_url(), 3, $msg );
		exit();
	}

	redirect_header( $this->build_form_url(), 1, 'Updated' );
	exit();
}

function manage_edit_all()
{
	$flag_error  = false;
	$content_arr =& $this->_xlang_post->get_post( $this->_manage_content_name );

	foreach ( $content_arr as $id => $new_content )
	{
		$row =& $this->_manage_handler->get_row_by_id( $id );

// not uodate if same
		if ( $row[ $this->_manage_content_name ] == $new_content )
		{	continue;	}

		$row['f_content'] = $new_content;
		$ret = $this->_manage_handler->update_manage_record( $row );
		if ( !$ret )
		{
			$this->_set_error( $this->_manage_handler->get_errors() );
			$flag_error = true;
		}
	}

	$url = $this->build_url(
		$this->_manage_path, null, 
		$this->_post_dirname, $this->_post_language, $this->_post_file ); 

	if ( $flag_error )
	{
		$msg  = 'DB error <br />';
		$msg .= $this->get_format_error();
		redirect_header( $url, 3, $msg );
		exit();
	}

	redirect_header( $url, 1, 'Updated' );
	exit();
}

//---------------------------------------------------------
// delete
//---------------------------------------------------------
function manage_delete()
{
	$row =& $this->_manage_handler->get_row_by_id( $this->_post_id );
	if ( !$row )
	{
		echo $this->build_link_index_admin(). "<br /><br />\n";
		echo $this->highlight( 'there are no record' );
		return false;
	}

	$ret = $this->_manage_handler->delete_manage_record( $row );
	if ( !$ret )
	{
		$msg  = 'DB error <br />';
		$msg .= $this->_manage_handler->get_format_error();
		redirect_header( $this->build_form_url(), 3, $msg );
		exit();
	}

	redirect_header( $this->_THIS_URL, 1, 'Deleted' );
	exit();
}

function manage_delete_all()
{
	$id_arr   =& $this->_xlang_post->get_post( $this->_CHECK_ALL_ID );

	$flag_error = false;
	$url = $this->build_url(
		$this->_manage_path, null, 
		$this->_post_dirname, $this->_post_language, $this->_post_file ); 

	foreach ( $id_arr as $id )
	{
		$row =& $this->_manage_handler->get_row_by_id( $id );
		if ( !$row )
		{	continue;	}

		$ret = $this->_manage_handler->delete_manage_record( $row );
		if ( !$ret )
		{
			$this->_set_error( $this->_manage_handler->get_errors() );
			$flag_error = true;
		}
	}

	if ( $flag_error )
	{
		$msg  = 'DB error <br />';
		$msg .= $this->get_format_error();
		redirect_header( $url, 3, $msg );
		exit();
	}

	redirect_header( $url, 1, 'Deleted' );
	exit();
}

//---------------------------------------------------------
// manage title
//---------------------------------------------------------
function build_manage_title_by_post()
{
	return $this->build_manage_title(
			$this->_post_dirname, $this->_post_language, $this->_post_file,
			$this->_post_word, $this->_post_mail );
}

function build_manage_title_by_row( &$row )
{
	$dirname  = isset($row['dirname'])  ? $row['dirname']  : null;
	$language = isset($row['language']) ? $row['language'] : null;
	$file     = isset($row['file'])     ? $row['file']     : null;
	$word     = isset($row['word'])     ? $row['word']     : null;
	$mail     = isset($row['mail'])     ? $row['mail']     : null;

	return $this->build_manage_title(
			$dirname, $language, $file, $word, $mail );
}

function build_manage_title( $dirname, $language=null, $file=null, $word=null, $mail=null )
{
	$text = $this->build_manage_bread_crumb(
			$dirname, $language, $file, $word, $mail );

	$text .= "<h3>". $this->_manage_title ."</h3>\n";

	$text .= $this->build_show_list(
			$dirname, $language, $file, $word, $mail );

	$text .= $this->build_show_add_record();

	return $text;
}

function build_manage_bread_crumb_by_post()
{
	return $this->build_manage_bread_crumb(
			$this->_post_dirname, $this->_post_language, $this->_post_file,
			$this->_post_word, $this->_post_mail );
}

function build_manage_bread_crumb( $dirname, $language=null, $file=null, $word=null, $mail=null )
{
	$text  = $this->build_bread_crumb_mod( $dirname, $language, $file, $word, $mail );
	$text .= "<br /><br />\n";

	$text .= $this->build_bread_crumb_admin( $dirname, $language, $file, $word, $mail );
	$text .= ' &gt;&gt; ';
	$text .= $this->build_a_tag( $this->_manage_path );
	$text .= $this->_manage_title .'</a>';
	$text .= "<br /><br />\n";

	return $text;
}

//---------------------------------------------------------
// manage list
//---------------------------------------------------------
function get_manage_total_print_error()
{
	$total = $this->get_manage_total();
	if ( $total == 0 )
	{
		echo $this->build_manage_bread_crumb_by_post();
		echo $this->highlight( 'there are no record' );
		return 0;
	}

	return $total;
}

function build_show_list( $dirname, $language=null, $file=null, $word=null, $mail=null )
{
	$text  = $this->build_bread_crumb_self(
				_AM_XLANG_SHOW_LIST, $this->_manage_path, null, 
				$dirname, $language, $file, $word, $mail );
		$text .= "<br /><br >\n";
	return $text;
}

function build_show_add_record()
{
	$text  = '<a href="'. $this->_THIS_URL .'?op=form">'. _AM_XLANG_ADD_RECORD .'</a>';
	$text .= "<br /><br />\n";
	return $text;
}

function get_manage_total()
{
	if ( $this->_post_dirname )
	{
		$total = $this->_manage_handler->get_manage_count(
					$this->_post_dirname, $this->_post_language, $this->_post_file,
					$this->_post_word, $this->_post_mail );
	}
	else
	{
		$total = $this->_manage_handler->get_count_all();
	}

	$this->_manage_total = $total;
	return $total;
}

function &get_manage_rows()
{
	$start = $this->get_pagenavi_start( $this->_manage_total );

	if ( $this->_post_dirname )
	{
		return $this->_manage_handler->get_manage_rows(
					$this->_post_dirname, $this->_post_language, $this->_post_file,
					$this->_post_word, $this->_post_mail,
					$this->_LIMIT, $start );
	}

	return $this->_manage_handler->get_rows_all( $this->_LIMIT, $start );
}

function build_manage_line_js_checkbox( $id )
{
	$text  = '<td class="'. $this->_alternate_class .'">';
	$text .= $this->build_js_checkbox( $id );
	$text .= '</td>';
	return $text;
}

function build_manage_line_id( $id )
{
	$id = intval($id);
	$text  = '<td class="'. $this->_alternate_class .'">';
	$text .= '<a href="'. $this->_THIS_URL .'?op=form&amp;id='. $id . '">';
	$text .= sprintf( '%04d', $id ) .'</a>';
	$text .= '</td>';
	return $text;
}

function build_manage_line_date( &$row )
{
	$date = null;
	if ( isset( $row['time'] ) )
	{
		$date = formatTimestamp( $row['time'], 'l' );
	}
	$text  = '<td class="'. $this->_alternate_class .'">';
	$text .= $date;
	$text .= '</td>';
	return $text;
}

function build_manage_line_short( &$row )
{
	$short = null;
	if ( isset( $row[ $this->_manage_content_name ] ) )
	{
		$short = $this->shorten_strings_with_nl2br(
			$row[ $this->_manage_content_name ], $this->_LENGTH );
	}
	$text  = '<td class="'. $this->_alternate_class .'">';
	$text .= $short;
	$text .= '</td>';
	return $text;
}

function build_manage_line_dirname( &$group, $flag=true )
{
	$text  = '<td class="'. $this->_alternate_class .'">';
	$text .= $this->build_manage_dirname_show( $group, $flag );
	$text .= '</td>';
	return $text;
}

function build_manage_line_language( &$group )
{
	$dirname   = $group['dirname'];
	$language  = $group['language'];

	$text  = '<td class="'. $this->_alternate_class .'">';
	$text .= $this->build_a_tag( $this->_manage_path, null, $dirname, $language );
	$text .= $this->sanitize( $language ) .'</a>';
	$text .= '</td>';
	return $text;
}

function build_manage_line_file( &$group )
{
	$dirname   = $group['dirname'];
	$language  = $group['language'];
	$file      = $group['file'];

	$text  = '<td class="'. $this->_alternate_class .'">';
	$text .= $this->build_a_tag( $this->_manage_path, null, $dirname, $language, $file );
	$text .= $this->sanitize( $file ) .'</a>';
	$text .= '</td>';
	return $text;
}

function build_manage_line_word( &$group )
{
	$dirname   = $group['dirname'];
	$language  = $group['language'];
	$file      = $group['file'];
	$word      = $group['word'];

	$text  = '<td class="'. $this->_alternate_class .'">';
	$text .= $this->build_a_tag(
		$this->_manage_path, null, $dirname, $language, $file, $word );
	$text .= $this->sanitize( $word ) .'</a>';
	$text .= '</td>';
	return $text;
}

function build_manage_line_mail( &$group )
{
	$dirname   = $group['dirname'];
	$language  = $group['language'];
	$file      = $group['file'];
	$word      = $group['word'];
	$mail      = $group['mail'];

	$text  = '<td class="'. $this->_alternate_class .'">';
	$text .= $this->build_a_tag(
		$this->_manage_path, null, $dirname, $language, $file, $word, $mail );
	$text .= $this->sanitize( $mail ) .'</a>';
	$text .= '</td>';
	return $text;
}

function build_manage_line_value( $value, $flag=true )
{
	if ( $flag )
	{
		$value = $this->sanitize( $value );
	}
	$text  = '<td class="'. $this->_alternate_class .'">';
	$text .= $value;
	$text .= '</td>';
	return $text;
}

function build_manage_dirname_show( &$group, $flag=true )
{
	if ( $flag && $group['error'] )
	{
		$show  = $this->highlight( 'not exist group' );
	}
	else
	{
		$dirname = $group['dirname'];
		$show  = $this->build_a_tag( $this->_manage_path, null, $dirname );
		$show .= $this->sanitize( $dirname ) .'</a>';
	}
	return $show;
}

//---------------------------------------------------------
// manage form
//---------------------------------------------------------
function &get_manage_row_by_id( $id=null, $flag_same=true )
{
	$false = false;

	if ( empty($id) )
	{
		$id = $this->_xlang_post->get_post_get( 'id' );
	}
	$id = intval( $id );

	$row =& $this->_build_row_by_post();
	$op        = 'add';
	$dirname   = '';
	$language  = '';
	$file      = '';
	$word      = '';
	$mail      = '';
	$date      = '';
	$user      = '';
	$error     = false;
	$same_rows = null;

	if ( $id )
	{
		$row =& $this->_manage_handler->get_row_by_id( $id );
		if ( !is_array($row) )
		{
			echo $this->build_manage_bread_crumb_by_post();
			echo $this->highlight( 'there are no record' );
			return $false;
		}

		$op = 'edit';
		if ( isset( $row['time'] ) )
		{
			$date = formatTimestamp( $row['time'], 'l' );
		}
		if ( isset( $row['uid'] ) )
		{
			$user = $this->build_xoops_userinfo( $row['uid'] );
		}

		$group_row =& $this->_group_handler->get_row_by_id( $row['gid'] );
		if ( is_array($group_row) )
		{
			$dirname  = $group_row['dirname'];
			$language = $group_row['language'];
			$file     = $group_row['file'];
			$word     = $group_row['word'];
			$mail     = $group_row['mail'];
		}
		else
		{
			$error = true;
		}

		if ( $flag_same )
		{
			$same_rows =& $this->_manage_handler->get_rows_same_by_id_gid( $id, $row['gid'] );
		}
	}

	$row['id']        = $id;
	$row['op']        = $op;
	$row['dirname']   = $dirname;
	$row['language']  = $language;
	$row['file']      = $file;
	$row['word']      = $word;
	$row['mail']      = $mail;
	$row['date']      = $date;
	$row['user']      = $user;
	$row['error']     = $error;
	$row['same_rows'] = $same_rows;

	return $row;
}

function build_manage_form_begin( &$row )
{
	$op       = isset($row['op'])       ? $row['op']       : null;
	$id       = isset($row['id'])       ? $row['id']       : null;
	$dirname  = isset($row['dirname'])  ? $row['dirname']  : null;
	$language = isset($row['language']) ? $row['language'] : null;
	$file     = isset($row['file'])     ? $row['file']     : null;
	$word     = isset($row['word'])     ? $row['word']     : null;
	$mail     = isset($row['mail'])     ? $row['mail']     : null;

	$text  = $this->build_form_begin( $op, $id );
	$text .= $this->build_hidden_dirname( $dirname, $language, $file, $word, $mail );
	return $text;
}

function build_manage_id( &$row, $title )
{
	if ( isset( $row['id'] )  ) 
	{
	 	$id = intval( $row['id'] );
	 	if ( $id )
	 	{
			return $this->build_line_label( $row, 'id', $title );
		}
	}
	return null;
}

function build_manage_gid( &$row, $title=null )
{
	if ( isset( $row['gid'] )  ) 
	{
	 	$gid = intval( $row['gid'] );
		if ( $gid ) 
		{
			$text  = $this->build_a_tag_group_manage_by_gid( $gid ).' ';
			$text .= $this->build_manage_not_exist_group( $row );
			return $this->build_line_value( $title, $text, false );
		}
	}
	return null;
}

function build_manage_not_exist_group( &$row )
{
	$error = isset($row['error']) ? $row['error']  : null;
	if ( $error )
	{
		return $this->highlight( 'not exist group' );
	}
	return null;
}

function build_manage_submit( &$row )
{
	if ( isset( $row['id'] )  ) 
	{
	 	$id = intval( $row['id'] );
	 	if ( $id )
	 	{
			return $this->build_line_edit();
		}
	}
	return $this->build_line_add();
}

function build_a_tag_group_manage_by_gid( $gid )
{
	$link  = '<a href="'. $this->build_url_group_manage_by_gid( $gid ) .'">';
	$link .= _AM_XLANG_GROUP_MANAGE .'</a>';
	return $link;
}

function build_url_group_manage_by_gid( $gid )
{
	$url = XLANG_URL .'/admin/group_manage.php?op=form&amp;id='. intval($gid);
	return $url;
}

//---------------------------------------------------------
// error
//---------------------------------------------------------
function print_form_with_error( $msg=null )
{
	xoops_cp_header();
	if ( $msg )
	{
		xoops_error( $msg );
	}
	$this->_print_form();
	xoops_cp_footer();
	exit();
}

function build_manage_same_group( &$row )
{
	$same_rows = isset($row['same_rows']) ? $row['same_rows']  : null;

	$text = null;
	if ( is_array($same_rows) && count($same_rows) )
	{
		$text  = $this->highlight( 'exist same group :' )."<br />\n";
		$text .= "<ul>\n";
		foreach ( $same_rows as $same_row )
		{
			$text .= '<li><a href="'. $this->_THIS_URL .'?op=form&amp;id='. $same_row['id'] . '">';
			$text .= 'id = '. sprintf( '%04d', $same_row['id'] ) ."</a></li>\n";
		}
		$text .= "</ul><br />\n";
	}
	return $text;
}

// --- class end ---
}

?>