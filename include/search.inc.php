<?php
// $Id: search.inc.php,v 1.1 2007/12/25 02:29:52 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

function xlang_search( $queryarray, $andor, $limit, $offset, $uid )
{
	global $xoopsDB;

	$myts =& MyTextSanitizer::getInstance();

	$table_file   = $xoopsDB->prefix( 'xlang_file' );
	$table_group  = $xoopsDB->prefix( 'xlang_group' );

	$ret = array();
	$arr = array();

	$where = ' h.gid=g.id ';

	if ( is_array( $queryarray ) && count( $queryarray ) )
	{
		foreach ( $queryarray as $q )
		{
			$arr[] = " h.f_content LIKE '%". $q ."%' ";
		}

		$where .= ' AND ( '. implode( $andor, $arr ) .' ) ';
	}

// file table
	$sql1  = "SELECT h.*, g.dirname, g.language, g.file, g.word, g.mail, g.kind ";
	$sql1 .= " FROM $table_file h, $table_group g ";
	$sql1 .= " WHERE ". $where;
	$sql1 .= " ORDER BY h.time DESC, h.id DESC";

	$res1 = $xoopsDB->query( $sql1, $limit, $offset );
	if ( !$res1 )
	{	return $ret;	}

	while( $row1 = $xoopsDB->fetchArray($res1) )
	{
		$dirname  = $row1['dirname'];
		$language = $row1['language'];
		$file     = $row1['file'];
		$time     = $row1['time'];
		$content  = $row1['f_content'];
		$link     = 'file.php?dirname='. $dirname .'&language='. $language .'&file='. $file;
		$title    = $dirname .' > '. $language .' > '. $file;

		$context = '';
		if ( function_exists( 'search_make_context' ) )
		{
			$context = search_make_context( $content, $queryarray );
		}

		$line = array(
			'link'    => $link,
			'title'   => $title,
			'time'    => $time,
			'context' => $context,
			'image'   => 'images/text.gif',
		);

		$ret[] = $line; 
	}

	return $ret;
}

?>