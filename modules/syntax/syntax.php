<?php 
/////////////////////////////////////////////////////////////////////////////
// syntax.php

function jabFormatCode($code)
{
	@include_once("geshi-1.0.8.6/geshi/geshi.php");
	
	if (function_exists('geshi_highlight'))
	{
		// Strip off language specifier
		$language=null;
		if (preg_match("/^{{(.*)}}\n+/", $code, $matches))
		{
			$language=$matches[1];
			$code=preg_replace("/^{{(.*)}}\n+/", "", $code);
		}
		
		global $jab;
		if ($language==null && isset($jab['syntax_language']))
		{
			$language=$jab['syntax_language'];
		}
		
		if ($language!==null)
		{
			$geshi = new GeSHi($code, $language, null);
			$geshi->set_header_type(GESHI_HEADER_NONE);
			$geshi->line_ending="\n";
			$geshi->set_comments_style(1, 'color: #008200;');
			$geshi->set_comments_style('MULTI', 'color: #008200;');
			$geshi->set_strings_style('color: #848200');
			$geshi->set_numbers_style('');
			return "<pre><code>".$geshi->parse_code()."</code></pre>";
		}
	}

	return "<pre><code>".htmlspecialchars($code, ENT_NOQUOTES)."</code></pre>";
}

?>