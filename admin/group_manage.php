<?php
// $Id: group_manage.php,v 1.5 2007/12/31 10:51:46 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

include_once 'admin_header.php';

//=========================================================
// class xlang_group_manage
//=========================================================
class xlang_group_manage extends xlang_manage
{
	var $_file_handler;
	var $_word_handler;
	var $_mail_handler;
	var $_template_handler;
	var $_log_handler;

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_group_manage()
{
	$this->xlang_manage();
	$this->set_manage_handler( xlang_group_handler::getInstance() );
	$this->set_manage_title( _AM_XLANG_GROUP_MANAGE );
	$this->set_manage_path( 'admin/group_manage.php' );

	$this->_file_handler     =& xlang_file_handler::getInstance();
	$this->_word_handler     =& xlang_word_handler::getInstance();
	$this->_mail_handler     =& xlang_mail_handler::getInstance();
	$this->_template_handler =& xlang_template_handler::getInstance();
	$this->_log_handler      =& xlang_log_handler::getInstance();

}

//---------------------------------------------------------
// main
//---------------------------------------------------------
function main()
{
	switch ( $this->_get_op() )
	{
		case 'add':
		case 'edit':
		case 'delete':
		case 'edit_all':
		case 'delete_all':
			if ( !$this->check_token() )
			{
				$this->print_form_with_error( 'Token Error' );
				exit();
			}
			$this->_execute();
			break;

		case 'form':
			xoops_cp_header();
			$this->_print_form();
			break;

		case 'list':
		default:
			xoops_cp_header();
			$this->_print_list();
			break;
	}

	echo $this->build_admin_footer();
	xoops_cp_footer();
	exit();

}

function _execute()
{
	switch ( $this->_get_op() )
	{
		case 'add':
			$this->_add();
			break;

		case 'edit':
			$this->manage_edit();
			break;

		case 'delete':
			$this->_delete();
			break;

		case 'edit_all':
			$this->manage_edit_all();
			break;

		case 'delete_all':
			$this->_delete_all();
			break;
	}
}

function _get_op()
{
	if ( $this->_xlang_post->get_post( 'add' ) )
	{	return 'add';	}
	if ( $this->_xlang_post->get_post( 'edit' ) )
	{	return 'edit';	}	
	if ( $this->_xlang_post->get_post( 'delete' ) )
	{	return 'delete';	}
	if ( $this->_xlang_post->get_post( 'delete_all' ) )
	{	return 'delete_all';	}

	$op  = $this->_xlang_post->get_post_get( 'op' );
	$id  = $this->_xlang_post->get_post_get( 'id' );

	if ( $op )
	{	return $op;	}

	if ( $id )
	{	return 'form';	}

	return '';
}

//---------------------------------------------------------
// add
//---------------------------------------------------------
function _add()
{
	$row =& $this->_build_row_by_post();

	if ( empty($row['dirname']) )
	{
		$this->print_form_with_error( 'no dirname' );
		exit();
	}

	if ( empty($row['kind']) )
	{
		$this->print_form_with_error( 'no kind' );
		exit();
	}

	$this->manage_add();
	exit();
}

function &_build_row_by_post()
{
	$row = array(
		'id'       => $this->_xlang_post->get_post( 'id' ),
		'dirname'  => $this->_xlang_post->get_post( 'dirname' ),
		'language' => $this->_xlang_post->get_post( 'language' ),
		'file'     => $this->_xlang_post->get_post( 'file' ),
		'word'     => $this->_xlang_post->get_post( 'word' ),
		'mail'     => $this->_xlang_post->get_post( 'mail' ),
		'kind'     => $this->_xlang_post->get_post( 'kind' ),
	);
	return $row;
}

//---------------------------------------------------------
// delete
//---------------------------------------------------------
function _delete()
{
	$row =& $this->_manage_handler->get_row_by_id( $this->_post_id );
	if ( !$row )
	{
		echo $this->build_link_index_admin(). "<br /><br />\n";
		echo $this->highlight( 'there are no record' );
		return false;
	}

	if ( !$this->_exist_others( $this->_post_id ) )
	{
		xoops_cp_header();
		$this->_print_form();
		return false;
	}

	$ret = $this->_manage_handler->delete( $row );
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

function _delete_all()
{
	$id_arr =& $this->_xlang_post->get_post( $this->_CHECK_ALL_ID );

	$flag_warning = false;
	$flag_error   = false;

	$url = $this->build_url(
		$this->_manage_path, null, 
		$this->_post_dirname, $this->_post_language, $this->_post_file ); 

	foreach ( $id_arr as $id )
	{
		$row =& $this->_manage_handler->get_row_by_id( $id );
		if ( !$row )
		{	continue;	}

		if ( !$this->_exist_others( $id ) )
		{
			$flag_warning = true;
			continue;
		}

		$ret = $this->_manage_handler->delete( $row );
		if ( !$ret )
		{
			$this->_set_error( $this->_manage_handler->get_errors() );
			$flag_error = true;
		}
	}

	if ( $flag_warning )
	{
		xoops_cp_header();
		$this->_print_list();
		return false;
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

function _exist_others( $id )
{
	$head = sprintf( '%04d', $id ).' : ';
	$msgs = array();

	if (  $this->_file_handler->get_count_by_gid( $id ) )
	{
		$msgs[] = $head.'file exist';
	}

	if ( $this->_word_handler->get_count_by_gid( $id ) )
	{
		$msgs[] = $head.'word exist';
	}

	if ( $this->_mail_handler->get_count_by_gid( $id ) )
	{
		$msgs[] = $head.'mail exist';
	}

	if ( $this->_template_handler->get_count_by_gid( $id ) )
	{
		$msgs[] = $head.'template exist';
	}

	if ( $this->_log_handler->get_count_by_gid( $id ) )
	{
		$msgs[] = $head.'log exist';
	}

	if ( count($msgs) )
	{
		$this->set_error( $msgs );
		return false;
	}

	return true;
}

//---------------------------------------------------------
// list
//---------------------------------------------------------
function _print_list()
{
	$total = $this->get_manage_total_print_error();
	if ( $total == 0 )
	{	return false;	}

	echo $this->build_manage_title_by_post();

	if ( $this->has_error() )
	{
		echo '<div style="'. $this->_DIV_ERROR_STYLE .'">';
		echo $this->get_format_error( true, false );
		echo "</div><br />\n";
	}

	$this->set_limit_by_post();
	$this->_print_group_list( $total, $this->get_manage_rows() );
}

function _print_group_list( $total, &$group_arr )
{
	$post_dirname  = $this->_xlang_post->get_get( 'dirname' );
	$post_language = $this->_xlang_post->get_get( 'language' );
	$post_file     = $this->_xlang_post->get_get( 'file' );
	$post_word     = $this->_xlang_post->get_get( 'word' );
	$post_mail     = $this->_xlang_post->get_get( 'mail' );

	echo sprintf( _XLANG_THERE_ARE, $total ) ."<br /><br />\n";

	echo $this->build_form_begin( 'edit_all' );
	echo $this->build_hidden_dirname( $post_dirname, $post_language, $post_file, $post_word, $post_mail );

	echo $this->build_table_begin();
	echo '<tr align="center">';
	echo '<th>'. $this->build_js_checkall() .'</th>';
	echo '<th></th>';
	echo '<th>'. _XLANG_DIRNAME .'</th>';
	echo '<th>'. _XLANG_LANGUAGE .'</th>';
	echo '<th>'. _XLANG_FILE .'</th>';
	echo '<th>'. _XLANG_WORD .'</th>';
	echo '<th>'. _XLANG_MAIL .'</th>';
	echo '<th>'. _XLANG_KIND .'</th>';
	echo '</tr>'."\n";

	foreach ( $group_arr as $row )
	{
		$id    = intval( $row['id'] );
		$class = $this->get_alternate_class();

		echo '<tr>';
		echo $this->build_manage_line_js_checkbox( $id );
		echo $this->build_manage_line_id( $id );
		echo $this->build_manage_line_dirname(  $row, false );
		echo $this->build_manage_line_language( $row );
		echo $this->build_manage_line_file(     $row );
		echo $this->build_manage_line_word(     $row );
		echo $this->build_manage_line_mail(     $row );
		echo $this->build_manage_line_value( $row['kind'] );
		echo "</tr>\n";
	}

	echo '<tr>';
	echo '<td class="head">';
	echo '<input type="submit" name="delete_all" value="'. _DELETE .'" />';
	echo '</td>';
	echo '<td class="head" colspan="7">';
	echo "</td></tr>\n";
	echo "</table></form>\n";
	echo "<br />\n";

	echo $this->build_form_pagenavi_limit(
		$post_dirname, $post_language, $post_file, $post_word, $post_mail );
	echo $this->build_pagenavi(
		'admin/group_manage.php', null, $post_dirname, $post_language, $post_file, $post_word, $post_mail );

}

//---------------------------------------------------------
// form
//---------------------------------------------------------
function _print_form()
{
	$id  = $this->_xlang_post->get_post_get( 'id' );
	$op  = 'add';

	$row =& $this->_build_row_by_post();
	$row['date'] = '';

	if ( $id )
	{
		$op  = 'edit';

		$row =& $this->_group_handler->get_row_by_id( $id );
		if ( !$row )
		{
			echo $this->build_manage_bread_crumb_by_post();
			echo $this->highlight( 'there are no record' );
			return false;
		}

		$row['date'] = formatTimestamp( $row['time'], 'l' );
	}

	$total = $this->_group_handler->get_count_all();

	echo $this->build_manage_title_by_row( $row );

	if ( $this->has_error() )
	{
		echo '<div style="'. $this->_DIV_ERROR_STYLE .'">';
		echo $this->get_format_error( true, false );
		echo "</div><br />\n";
	}

	$kind = $row['kind'];
	$this->_check_group_file(     $id, $kind );
	$this->_check_group_word(     $id, $kind );
	$this->_check_group_mail(     $id, $kind );
	$this->_check_group_template( $id, $kind );
	$this->_check_group_log(      $row );
	echo "<br />\n";

	echo $this->build_form_begin( $op, $id );

	echo $this->build_table_begin();
	echo $this->build_line_title( _AM_XLANG_GROUP_MANAGE );

	echo $this->build_manage_id(  $row,             _XLANG_GROUP_ID );
	echo $this->build_line_text(  $row, 'dirname',  _XLANG_DIRNAME );
	echo $this->build_line_text(  $row, 'language', _XLANG_LANGUAGE );
	echo $this->build_line_text(  $row, 'file',     _XLANG_FILE );
	echo $this->build_line_text(  $row, 'word',     _XLANG_WORD );
	echo $this->build_line_text(  $row, 'mail',     _XLANG_MAIL );
	echo $this->build_line_label( $row, 'time',     _XLANG_UNIXTIME );
	echo $this->build_line_label( $row, 'date',     _XLANG_DATE );
	echo $this->build_line_text(  $row, 'kind',     _XLANG_KIND );
	echo $this->build_manage_submit( $row );

	echo "</table></form>\n";

}

//---------------------------------------------------------
// check
//---------------------------------------------------------
function _check_group_file( $id, $kind )
{
	if ( $id )
	{
		$flag_cannot = true;
		$flag_must   = false;
		if ( $kind == _XLANG_C_KIND_FILE )
		{
			$flag_cannot = false;
			$flag_must   = true;
		}
		$rows  =& $this->_file_handler->get_rows_by_gid( $id );
		$count =  $this->_check_group_table( $rows, 'file', $flag_cannot, $flag_must );
		if ( $flag_must && ( $count == 0 ) )
		{
			echo '<a href="'. XLANG_URL . '/admin/file_manage.php?op=form&amp;gid='. $id .'">';
			echo 'Add File Record'. '</a>'. "<br />\n";
		}
	}
}

function _check_group_word( $id, $kind )
{
	if ( $id )
	{
		$flag_cannot = true;
		$flag_must   = false;
		if ( $kind == _XLANG_C_KIND_WORD )
		{
			$flag_cannot = false;
			$flag_must   = true;
		}
		$rows =& $this->_word_handler->get_rows_by_gid( $id );
		$count = $this->_check_group_table( $rows, 'word', $flag_cannot, $flag_must );
		if ( $flag_must && ( $count == 0 ) )
		{
			echo '<a href="'. XLANG_URL . '/admin/word_manage.php?op=form&amp;gid='. $id .'">';
			echo 'Add Word Record'. '</a>'. "<br />\n";
		}
	}
}

function _check_group_mail( $id, $kind )
{
	if ( $id )
	{
		$flag_cannot = true;
		$flag_must   = false;
		if ( $kind == _XLANG_C_KIND_MAIL )
		{
			$flag_cannot = false;
			$flag_must   = true;
		}
		$rows  =& $this->_mail_handler->get_rows_by_gid( $id );
		$count =  $this->_check_group_table( $rows, 'mail', $flag_cannot, $flag_must );
		if ( $flag_must && ( $count == 0 ) )
		{
			echo '<a href="'. XLANG_URL . '/admin/mail_manage.php?op=form&amp;gid='. $id .'">';
			echo 'Add Mail Record'. '</a>'. "<br />\n";
		}
	}
}

function _check_group_template( $id, $kind )
{
	if ( $id )
	{
		$flag_cannot = true;
		$flag_must   = false;
		$flag_add    = false;
		if ( $kind == _XLANG_C_KIND_FILE )
		{
			$flag_cannot = false;
			$flag_add    = true;
		}
		$rows  =& $this->_template_handler->get_rows_by_gid( $id );
		$count =  $this->_check_group_table( $rows, 'template', $flag_cannot, $flag_must );
		if ( $flag_add && ( $count == 0 ) )
		{
			echo '<a href="'. XLANG_URL . '/admin/template_manage.php?op=form&amp;gid='. $id .'">';
			echo 'Add Template Record'. '</a>'. "<br />\n";
		}
	}
}

function _check_group_log( &$row )
{
	$id       = isset($row['id'])       ? $row['id']       : 0;
	$dirname  = isset($row['dirname'])  ? $row['dirname']  : null;
	$language = isset($row['language']) ? $row['language'] : null;
	$file     = isset($row['file'])     ? $row['file']     : null;
	$word     = isset($row['word'])     ? $row['word']     : null;
	$mail     = isset($row['mail'])     ? $row['mail']     : null;

	if ( $id )
	{
		$count = $this->_log_handler->get_count_by_gid( $id );
		if ( $count )
		{
			echo $this->build_a_tag( 'admin/log_manage.php', null,  $dirname, $language, $file, $word, $mail );
			echo _AM_XLANG_LOG_MANAGE. '</a>';
			echo ' (' .$count .') '. "<br />\n";
		}
	}
}

function _check_group_table( &$rows, $name, $flag_cannot=true, $flag_must=true )
{
	$flag_error = false;
	$count      = 0;

	if ( is_array($rows) )
	{
		$count = count($rows);
		if ( $count == 0 )
		{
			$flag_error = true;
		}
		else
		{
			if ( $count == 1 )
			{
				if ( $flag_cannot )
				{
					echo $this->highlight( 'cannot have '. $name .' record : ' );
				}
				else
				{
					echo $name.' record : ';
				}
			}
			else
			{
				if ( $flag_cannot )
				{
					echo $this->highlight( 'cannot have '. $name .' record' )."<br />\n";
				}
				echo $this->highlight( 'too many '. $name .' records' )."<br />\n";
			}

			foreach ( $rows as $row )
			{
				echo '<a href="'. XLANG_URL . '/admin/'. $name .'_manage.php?op=form&amp;id='. $row['id'] .'">';
				echo sprintf( '%04d', $row['id'] ) .'</a>'."<br />\n";
			}
		}
	}
	else
	{
		$flag_error = true;
	}

	if ( $flag_must && $flag_error )
	{
		echo $this->highlight( 'no '. $name .' record' )."<br />\n";
	}

	return $count;
}

// --- class end ---
}

//=========================================================
// main
//=========================================================
$manage = new xlang_group_manage();
$manage->main();

exit();
// --- main end ---

?>