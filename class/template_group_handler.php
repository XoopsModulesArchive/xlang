<?php
// $Id: template_group_handler.php,v 1.4 2007/12/29 05:48:20 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

//=========================================================
// class xlang_template_group_handler
//=========================================================
class xlang_template_group_handler extends xlang_template_handler
{

//---------------------------------------------------------
// constructor
//---------------------------------------------------------
function xlang_template_group_handler()
{
	$this->xlang_template_handler();
	$this->set_group_handler();
}

function &getInstance()
{
	static $instance;
	if (!isset($instance)) 
	{
		$instance = new xlang_template_group_handler();
	}
	return $instance;
}

//---------------------------------------------------------
// add
//---------------------------------------------------------
function add_with_exist_check( $dirname, $file, $content )
{
	$row =& $this->get_template_by_file( $dirname, $file );
	if ( is_array($row) )
	{
		$row['t_content'] = $content;

		$ret = $this->update_template( $row );
		if ( !$ret )
		{	return 0;	}
		return 2;
	}

	return $this->add( $dirname, $file, $content );
}

function add( $dirname, $file, $content )
{
	$ret = $this->_group_handler->add_with_exist_check(
		_XLANG_C_KIND_DIRNAME, $dirname );
	if ( !$ret )
	{
		$this->set_error( $this->_group_handler->get_errors() );
		return 0;
	}

	$gid = $this->_group_handler->add_with_exist_check(
		_XLANG_C_KIND_FILE, $dirname, null, $file );
	if ( !$gid )
	{
		$this->set_error( $this->_group_handler->get_errors() );
		return 0;
	}

	$row_new = array(
		'gid'       => $gid,
		't_content' => $content,
	);

	$ret = $this->insert_template( $row_new );
	if ( !$ret )
	{	return 0;	}

	return 1;
}

//---------------------------------------------------------
// insert
//---------------------------------------------------------
function insert_manage_record( &$row )
{
	return $this->insert_template( $row );
}

function insert_template( &$row )
{
	$row['time'] = time();

	return $this->insert( $row );
}

//---------------------------------------------------------
// update
//---------------------------------------------------------
function update_manage_record( &$row )
{
	return $this->update_template( $row );
}

function update_template( &$row )
{
	$row['time'] = time();

	return $this->update( $row );
}

//---------------------------------------------------------
// delete
//---------------------------------------------------------
function delete_manage_record( &$row )
{
	return $this->delete_template( $row );
}

function delete_template( &$row )
{
	return $this->delete_by_id( $row['id'] );
}

//---------------------------------------------------------
// count
//---------------------------------------------------------
function get_manage_count( $dirname, $language, $file=null, $word=null, $mail=null )
{
	return $this->get_count_by_dirname( $dirname, $file );
}

function get_count_by_dirname( $dirname, $file=null )
{
	return $this->get_count_group_by_dirname(
		_XLANG_C_KIND_FILE, $dirname, null, $file );
}

//---------------------------------------------------------
// get
//---------------------------------------------------------
function &get_manage_rows( $dirname, $language=null, $file=null, $word=null, $mail=null, $limit=0, $offset=0 )
{
	return $this->get_templates_by_dirname( $dirname, $limit, $offset );
}

function &get_template_by_id( $id )
{
	return $this->get_row_group_by_id( $id );
}

function &get_template_by_file( $dirname, $file )
{
	return $this->get_row_group_by_dirname( 
		_XLANG_C_KIND_FILE, $dirname, null, $file );
}

function &get_templates_all( $limit=0, $offset=0 )
{
	return $this->get_rows_group_all( $limit, $offset );
}

function &get_templates_by_dirname( $dirname, $limit=0, $offset=0 )
{
	return $this->get_rows_group_by_dirname(
		_XLANG_C_KIND_FILE, $dirname, null, null, null, null, $limit, $offset );
}

//----- class end -----
}

?>