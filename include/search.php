<?php
// $Id: search.php,v 1.2 2007/12/31 10:51:46 ohwada Exp $

//=========================================================
// XOOPS Language Translation Support
// 2007-12-01 K.OHWADA
//=========================================================

//---------------------------------------------------------
// porting from Suin's Search module <http://suin.jp/>
//---------------------------------------------------------
function xlang_build_search_context($text, $word_array, $max=255)
{
	if ( !is_array($word_array) )
	{
		$word_array = array();
	}

	$ret = "";
	$q_word = str_replace(" ","|",preg_quote(join(' ',$word_array),"/"));

	if (preg_match("/$q_word/i", $text, $match))
	{
		$ret = ltrim(preg_replace('/\s+/', ' ', $text));
		list($pre, $aft)=preg_split("/$q_word/i", $ret, 2);
		$m = intval($max/2);
		$ret = (strlen($pre) > $m)? "... " : "";
		$ret .= xlang_substr($pre, max(strlen($pre)-$m+1,0),$m).$match[0];
		$m = $max-strlen($ret);
		$ret .= xlang_substr($aft, 0, min(strlen($aft),$m));
		if (strlen($aft) > $m) $ret .= " ...";
	}

	if (!$ret)
	{
		$ret = xlang_substr($text, 0, $max);
	}

	return $ret;
}

?>