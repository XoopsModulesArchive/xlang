<?php
// $Id: file_manage.php,v 1.5 2007/12/31 10:51:46 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

include_once 'admin_header.php';

//=========================================================
// class xlang_file_manage
//=========================================================
class xlang_file_manage extends xlang_manage
{
	var $_word_group_handler;

	var $_LENGTH = 200;
	var $_ROWS   = 40;
	var $_COLS   = 80;

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_file_manage()
{
	$this->xlang_manage();
	$this->set_manage_handler( xlang_file_group_handler::getInstance() );
	$this->set_manage_title( _AM_XLANG_FILE_MANAGE );
	$this->set_manage_path( 'admin/file_manage.php' );
	$this->set_manage_content_name( 'f_content' );

	$this->_word_group_handler =& xlang_word_group_handler::getInstance(); 
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

		case 'form_file':
			xoops_cp_header();
			$this->_print_form_file();
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
			$this->manage_add_with_gid_check();
			break;

		case 'edit':
			$this->manage_edit_with_gid_check();
			break;

		case 'delete':
			$this->manage_delete();
			break;

		case 'edit_all':
			$this->manage_edit_all();
			break;

		case 'delete_all':
			$this->manage_delete_all();
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
	if ( $this->_xlang_post->get_post( 'edit_all' ) )
	{	return 'edit_all';	}
	if ( $this->_xlang_post->get_post( 'delete_all' ) )
	{	return 'delete_all';	}

	$op       = $this->_xlang_post->get_post_get( 'op' );
	$id       = $this->_xlang_post->get_post_get( 'id' );
	$dirname  = $this->_xlang_post->get_post_get( 'dirname' );
	$language = $this->_xlang_post->get_post_get( 'language' );
	$file     = $this->_xlang_post->get_post_get( 'file' );

	if ( $op )
	{	return $op;	}

	if ( $id )
	{	return 'form';	}

	if ( $dirname && $language && $file )
	{	return 'form_file';	}

	return '';
}

//---------------------------------------------------------
// post
//---------------------------------------------------------
function &_build_row_by_post()
{
	$row = array(
		'id'        => $this->_xlang_post->get_post_get( 'id' ),
		'gid'       => $this->_xlang_post->get_post_get( 'gid' ),
		'time'      => $this->_xlang_post->get_post_get( 'time' ),
		'f_content' => $this->_xlang_post->get_post_get( 'f_content' ),
		'f_note'    => $this->_xlang_post->get_post_get( 'f_note' ),
	);
	return $row;
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

	$this->set_limit_by_post();
	$this->_print_file_list( $total, $this->get_manage_rows() );
}

function _print_file_list( $total, $file_arr )
{
	$post_dirname  =& $this->_xlang_post->get_get( 'dirname' );
	$post_language =& $this->_xlang_post->get_get( 'language' );

	echo sprintf( _XLANG_THERE_ARE, $total ) ."<br /><br />\n";

	echo $this->build_form_begin( 'edit_all' );
	echo $this->build_hidden_dirname( $post_dirname, $post_language );

	echo $this->build_table_begin();

	echo '<tr align="center">';
	echo '<th>'. $this->build_js_checkall() .'</th>';
	echo '<th>'. _XLANG_FILE_ID .'</th>';
	echo '<th>'. _XLANG_DIRNAME .'</th>';
	echo '<th>'. _XLANG_LANGUAGE .'</th>';
	echo '<th>'. _XLANG_FILE .'</th>';
	echo '<th>'. _XLANG_CONTENT .'</th>';
	echo '</tr>'."\n";

	foreach ( $file_arr as $row )
	{
		$id    =  intval( $row['id'] );
		$group =& $this->get_manage_group_by_row( $row );

		$class = $this->get_alternate_class();

		echo '<tr>';
		echo $this->build_manage_line_js_checkbox( $id );
		echo $this->build_manage_line_id( $id );
		echo $this->build_manage_line_dirname(  $group );
		echo $this->build_manage_line_language( $group );
		echo $this->build_manage_line_file(     $group );
		echo $this->build_manage_line_short( $row );
		echo "</tr>\n";
	}

	echo '<tr>';
	echo '<td class="head">';
	echo '<input type="submit" name="delete_all" value="'. _DELETE .'" />';
	echo '</td>';
	echo '<td class="head" colspan="5"></td>';
	echo "</tr>\n";
	echo "</table></form>\n";
	echo "<br />\n";

	echo $this->build_form_pagenavi_limit( $post_dirname, $post_language );
	echo $this->build_pagenavi(
		'admin/file_manage.php', null, $post_dirname, $post_language );
}

//---------------------------------------------------------
// form
//---------------------------------------------------------
function _print_form_file()
{
	$dirname  = $this->_xlang_post->get_get( 'dirname' );
	$language = $this->_xlang_post->get_get( 'language' );
	$file     = $this->_xlang_post->get_get( 'file' );

	$row =& $this->_manage_handler->get_file_by_file( $dirname, $language, $file );
	if ( isset( $row['id'] ) ) 
	{
		return $this->_print_form( $row['id'] );
	}

	return $this->_print_list();
}

function _print_form( $id=null )
{
	$row =& $this->get_manage_row_by_id( $id );
	if ( !is_array($row) )
	{	return false;	}

	echo $this->build_manage_title_by_row( $row );
	echo $this->build_manage_same_group( $row );

	$this->_check_word_table( $row );

	echo $this->build_manage_form_begin( $row );

	echo $this->build_table_begin();
	echo $this->build_line_title( _AM_XLANG_FILE_MANAGE );

	echo $this->build_manage_id(     $row,              _XLANG_FILE_ID );
	echo $this->build_line_text(     $row, 'gid',       _XLANG_GROUP_ID );
	echo $this->build_manage_gid(    $row );
	echo $this->build_line_label(    $row, 'dirname',   _XLANG_DIRNAME );
	echo $this->build_line_label(    $row, 'language',  _XLANG_LANGUAGE );
	echo $this->build_line_label(    $row, 'file',      _XLANG_FILE );
	echo $this->build_line_label(    $row, 'time',      _XLANG_UNIXTIME );
	echo $this->build_line_label(    $row, 'date',      _XLANG_DATE );
	echo $this->build_line_textarea( $row, 'f_content', _XLANG_CONTENT, $this->_ROWS, $this->_COLS );
	echo $this->build_line_textarea( $row, 'f_note',    _XLANG_NOTE );
	echo $this->build_manage_submit( $row );

	echo "</table></form>\n";

}

function _check_word_table( &$row )
{
	$dirname  = $row['dirname'];
	$language = $row['language'];
	$file     = $row['file'];

	$count = $this->_word_group_handler->get_count_by_dirname( $dirname, $language, $file );
	if ( $count )
	{
		echo $this->build_a_tag( 'admin/word_manage.php', null,  $dirname, $language, $file );
		echo _AM_XLANG_WORD_MANAGE. '</a>';
		echo ' ('. $count .') '. "<br />\n";
	}
	else
	{
		echo $this->highlight( 'no word record' )."<br />\n";
	}

	echo "<br />\n";
}

// --- class end ---
}

//=========================================================
// main
//=========================================================
$manage = new xlang_file_manage();
$manage->main();

exit();
// --- main end ---

?>