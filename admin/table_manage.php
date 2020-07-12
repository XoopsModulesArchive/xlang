<?php
// $Id: table_manage.php,v 1.2 2007/12/20 12:47:06 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

include_once 'admin_header.php';

//=========================================================
// class xlang_table_manage
//=========================================================
class xlang_table_manage extends xlang_form
{
	var $_group_handler;
	var $_file_handler;
	var $_word_handler;
	var $_mail_handler;
	var $_template_handler;
	var $_log_handler;
	var $_file_group_handler;
	var $_word_group_handler;

	var $_MAX_CHECK = 1000;

	var $_total = 0;
	var $_next  = 0;
	var $_flag_error = false;

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_table_manage()
{
	$this->xlang_form();

	$this->_group_handler      =& xlang_group_handler::getInstance();
	$this->_file_handler       =& xlang_file_handler::getInstance();
	$this->_word_handler       =& xlang_word_handler::getInstance();
	$this->_mail_handler       =& xlang_mail_handler::getInstance();
	$this->_template_handler   =& xlang_template_handler::getInstance();
	$this->_log_handler        =& xlang_log_handler::getInstance();
	$this->_file_group_handler =& xlang_file_group_handler::getInstance();
	$this->_word_group_handler =& xlang_word_group_handler::getInstance();

}

//---------------------------------------------------------
// main
//---------------------------------------------------------
function main()
{
	xoops_cp_header();

	switch ( $this->_get_op() )
	{
		case 'group':
			$this->_check_group();
			break;

		case 'file':
			$this->_check_file();
			break;

		case 'word':
			$this->_check_word();
			break;

		case 'mail':
			$this->_check_mail();
			break;

		case 'template':
			$this->_check_template();
			break;

		case 'log':
			$this->_check_log();
			break;

		case 'menu':
		default:
			$this->_print_menu();
			break;
	}

	echo $this->build_admin_footer();
	xoops_cp_footer();
	exit();

}

function _get_op()
{
	return $this->_xlang_post->get_post_get( 'op' );
}


//---------------------------------------------------------
// menu
//---------------------------------------------------------
function _print_menu()
{
	echo $this->_print_bread_crumb();

	$group_file_count = $this->_group_handler->get_count_by_dirname( _XLANG_C_KIND_FILE );
	$group_word_count = $this->_group_handler->get_count_by_dirname( _XLANG_C_KIND_WORD );
	$group_mail_count = $this->_group_handler->get_count_by_dirname( _XLANG_C_KIND_MAIL );
	$group_count      = $this->_group_handler->get_count_all();
	$file_count       = $this->_file_handler->get_count_all();
	$word_count       = $this->_word_handler->get_count_all();
	$mail_count       = $this->_mail_handler->get_count_all();
	$template_count   = $this->_template_handler->get_count_all();
	$log_count        = $this->_log_handler->get_count_all();

	$group_file_error = '';
	$group_word_error = '';
	$group_mail_error = '';

	if ( $group_file_count != $file_count )
	{
		$group_file_error = $this->highlight( 'unmatch number of group' );
	}

	if ( $group_word_count != $word_count )
	{
		$group_word_error = $this->highlight( 'unmatch number of group' );
	}

	if ( $group_mail_count != $mail_count )
	{
		$group_mail_error = $this->highlight( 'unmatch number of group' );
	}

	echo "<ul>\n";
	echo '<li><a href="'. XLANG_URL .'/admin/table_manage.php?op=group">';
	echo 'Check Group Table' .'</a> ';
	echo '('. $group_count .')' . "<br /><br /></li>\n";

	echo '<li><a href="'. XLANG_URL .'/admin/table_manage.php?op=file">';
	echo 'Check File Table' .'</a> ';
	echo '('. $file_count .') ';
	echo $group_file_error . "<br /><br /></li>\n";

	echo '<li><a href="'. XLANG_URL .'/admin/table_manage.php?op=word">';
	echo 'Check Word Table' .'</a> ';
	echo '('. $word_count .') ';
	echo $group_word_error . "<br /><br /></li>\n";

	echo '<li><a href="'. XLANG_URL .'/admin/table_manage.php?op=mail">';
	echo 'Check Mail Table' .'</a> ';
	echo '('. $mail_count .') ';
	echo $group_mail_error . "<br /><br /></li>\n";

	echo '<li><a href="'. XLANG_URL .'/admin/table_manage.php?op=template">';
	echo 'Check Template Table' .'</a>';
	echo '('. $template_count .')' . "<br /><br /></li>\n";

	echo '<li><a href="'. XLANG_URL .'/admin/table_manage.php?op=log">';
	echo 'Check Log Table' .'</a>';
	echo '('. $log_count .')' . "<br /><br /></li>\n";
	echo "</ul>\n";
}

//---------------------------------------------------------
// group
//---------------------------------------------------------
function _check_group()
{
	echo $this->_print_bread_crumb( 'Check Group Table' );

	$start = intval( $this->_xlang_post->get_get( 'start' ) );

	$total =  $this->_group_handler->get_count_all();
	$rows  =& $this->_group_handler->get_rows_all( $this->_MAX_CHECK, $start );

	$this->_print_total( $total, $start );

	foreach ( $rows as $row )
	{
		$id  = $row['id'];

		switch ( $row['kind'] )
		{
			case _XLANG_C_KIND_FILE:
				$this->_check_group_file( $id );
				break;
	
			case _XLANG_C_KIND_WORD:
				$this->_check_group_word( $id );
				break;
		}
	}

	$this->_print_next( 'group' );
	echo "<br />\n";
}

function _check_group_file( $id )
{
	$count = $this->_file_handler->get_count_by_gid( $id );
	if ( $count == 0 )
	{
		$this->_flag_error = true;
		$msg  = $this->_build_url_form( 'group_manage.php', $id );
		$msg .= ' : no file record';
		echo $this->highlight( $msg )."<br />\n";
	}
}

function _check_group_word( $id )
{
	$count = $this->_word_handler->get_count_by_gid( $id );
	if ( $count == 0 )
	{
		$this->_flag_error = true;
		$msg  = $this->_build_url_form( 'group_manage.php', $id );
		$msg .= ' : no word record';
		echo $this->highlight( $msg )."<br />\n";
	}
}

//---------------------------------------------------------
// file
//---------------------------------------------------------
function _check_file()
{
	echo $this->_print_bread_crumb( 'Check File Table' );

	$start = intval( $this->_xlang_post->get_get( 'start' ) );

	$total =  $this->_file_handler->get_count_all();
	$rows  =& $this->_file_handler->get_rows_all( $this->_MAX_CHECK, $start );

	$this->_print_total( $total, $start );

	foreach ( $rows as $row )
	{
		$id  = $row['id'];
		$gid = $row['gid'];

		$same_rows =& $this->_file_handler->get_rows_same_by_id_gid( $id, $gid );
		$this->_print_same_error( $same_rows, 'file_manage.php' );

		$group_row =& $this->_group_handler->get_row_by_id( $gid );
		$this->_print_group_error( $id, $gid, 'file_manage.php', $group_row );

		$this->_check_file_word_table( $id, $group_row );
	}

	$this->_print_next( 'file' );
	echo "<br />\n";
}

function _check_file_word_table( $id, &$row )
{
	if ( !is_array($row) )
	{	return;	}

	$dirname  = $row['dirname'];
	$language = $row['language'];
	$file     = $row['file'];

	$count = $this->_word_group_handler->get_count_by_dirname( $dirname, $language, $file );
	if ( $count == 0 )
	{
		$msg  = $this->_build_url_form( 'file_manage.php', $id );
		$msg .= ' : no word record';
		echo $this->highlight( $msg )."<br />\n";
	}
}

//---------------------------------------------------------
// word
//---------------------------------------------------------
function _check_word()
{
	echo $this->_print_bread_crumb( 'Check Word Table' );

	$start = intval( $this->_xlang_post->get_get( 'start' ) );

	$total =  $this->_word_handler->get_count_all();
	$rows  =& $this->_word_handler->get_rows_all( $this->_MAX_CHECK, $start );

	$this->_print_total( $total, $start );

	foreach ( $rows as $row )
	{
		$id  = $row['id'];
		$gid = $row['gid'];

		$same_rows =& $this->_word_handler->get_rows_same_by_id_gid( $id, $gid );
		$this->_print_same_error( $same_rows, 'word_manage.php' );

		$group_row =& $this->_group_handler->get_row_by_id( $gid );
		$this->_print_group_error( $id, $gid, 'word_manage.php', $group_row );

		$this->_check_word_file_table( $id, $group_row );
	}

	$this->_print_next( 'word' );
}

function _check_word_file_table( $id, &$row )
{
	if ( !is_array($row) )
	{	return;	}

	$dirname  = $row['dirname'];
	$language = $row['language'];
	$file     = $row['file'];

	$count = $this->_file_group_handler->get_count_by_dirname( $dirname, $language, $file );
	if ( $count == 0 )
	{
		$msg  = $this->_build_url_form( 'word_manage.php', $id );
		$msg .= ' : no file record';
		echo $this->highlight( $msg )."<br />\n";
	}
}

//---------------------------------------------------------
// mail
//---------------------------------------------------------
function _check_mail()
{
	echo $this->_print_bread_crumb( 'Check Mail Table' );

	$start = intval( $this->_xlang_post->get_get( 'start' ) );

	$total =  $this->_mail_handler->get_count_all();
	$rows  =& $this->_mail_handler->get_rows_all( $this->_MAX_CHECK, $start );

	$this->_print_total( $total, $start );

	foreach ( $rows as $row )
	{
		$id  = $row['id'];
		$gid = $row['gid'];

		$same_rows =& $this->_mail_handler->get_rows_same_by_id_gid( $id, $gid );
		$this->_print_same_error( $same_rows, 'mail_manage.php' );

		$this->_check_print_group_error( $id, $gid, 'mail_manage.php' );
	}

	$this->_print_next( 'mail' );
	echo "<br />\n";
}

//---------------------------------------------------------
// template
//---------------------------------------------------------
function _check_template()
{
	echo $this->_print_bread_crumb( 'Check Template Table' );

	$start = intval( $this->_xlang_post->get_get( 'start' ) );

	$total =  $this->_template_handler->get_count_all();
	$rows  =& $this->_template_handler->get_rows_all( $this->_MAX_CHECK, $start );

	$this->_print_total( $total, $start );

	foreach ( $rows as $row )
	{
		$id  = $row['id'];
		$gid = $row['gid'];

		$same_rows =& $this->_template_handler->get_rows_same_by_id_gid( $id, $gid );
		$this->_print_same_error( $same_rows, 'template_manage.php' );

		$this->_check_print_group_error( $id, $gid, 'template_manage.php' );
	}

	$this->_print_next( 'template' );
}

//---------------------------------------------------------
// log
//---------------------------------------------------------
function _check_log()
{
	echo $this->_print_bread_crumb( 'Check Log Table' );

	$start = intval( $this->_xlang_post->get_get( 'start' ) );

	$total =  $this->_log_handler->get_count_all();
	$rows  =& $this->_log_handler->get_rows_all( $this->_MAX_CHECK, $start );

	$this->_print_total( $total, $start );

	foreach ( $rows as $row )
	{
		$id  = $row['id'];
		$gid = $row['gid'];

		$this->_check_print_group_error( $id, $gid, 'log_manage.php' );
	}

	$this->_print_next( 'log' );
}

//---------------------------------------------------------
// common
//---------------------------------------------------------
function _print_total( $total, $start )
{
	$this->_total = $total;
	$this->_next  = $start + $this->_MAX_CHECK;
	$this->_flag_error = false;

	echo sprintf( _XLANG_THERE_ARE, $total ) ."<br /><br />\n";
	echo 'check '. ( $start + 1 ).' -> '. $this->_next ."<br /><br />\n";

}

function _print_same_error( &$rows, $script )
{
	foreach ( $rows as $row )
	{
		$this->_flag_error = true;
		$msg  = $this->_build_url_form( $script, $row['id'] );
		$msg .= ' : exist same group gid = '. $row['gid'];
		echo $this->highlight( $msg )."<br />\n";
	}
}

function _check_print_group_error( $id, $gid, $script )
{
	$row =& $this->_group_handler->get_row_by_id( $gid );
	$this->_print_group_error( $id, $gid, $script, $row );
}

function _print_group_error( $id, $gid, $script, &$row )
{
	if ( !is_array($row) )
	{
		$this->_flag_error = true;
		$msg  = $this->_build_url_form( $script, $id );
		$msg .= ' : not exist group gid = '. $gid;
		echo $this->highlight( $msg )."<br />\n";
	}
}

function _build_url_form( $script, $id )
{
	$text  = '<a href="'. XLANG_URL .'/admin/'. $script .'?op=form&amp;id='. $id . '">';
	$text .= sprintf( '%04d', $id ) .'</a>';
	return $text;
}

function _print_next( $op )
{
	if ( !$this->_flag_error )
	{
		echo 'no error'."<br />\n";
	}

	echo "<br />\n";

	if ( $this->_total > $this->_next )
	{
		echo '<a href="'. $this->_THIS_URL .'?op='. $op .'&amp;start='. $this->_next . '">';
		echo 'Check Next '. $this->_MAX_CHECK ."</a><br />\n";
	}
	else
	{
		echo 'FINISH'."</a><br />\n";
	}
}

function _print_bread_crumb( $title=null )
{
	echo $this->build_link_index_admin();
	echo ' &gt;&gt; ';
	echo '<a href="'. $this->_THIS_URL .'">';
	echo  _AM_XLANG_TABLE_MANAGE .'</a>';
	if ( $title )
	{
		echo ' &gt;&gt; ';
		echo $title;
	}
	echo "<br /><br />\n";
}

// --- class end ---
}

//=========================================================
// main
//=========================================================
$manage = new xlang_table_manage();
$manage->main();

exit();
// --- main end ---

?>