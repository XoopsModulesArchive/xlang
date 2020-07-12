<?php
// $Id: filter_default.php,v 1.1 2007/12/31 10:54:11 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

function xlang_filter_default( $str )
{
	$replace_array = array(
		'"'         => '\"',    // escape " -> \"
		'\\\"'      => '\"',    // redo \"  -> \\"    -> \"
		'\\"\\"\\"' => '"',     // redo """ -> \"\"\" -> "
		"\\'"       => "'",     // \' -> '
		"\n"        => '',      // strip new line code
		"\r"        => '',      // strip new line code
		"\t"        => '',      // strip tab code
	);

	foreach ( $replace_array as $k => $v )
	{
		$str = str_replace( $k, $v, $str );
	}

	return $str;
}

?>