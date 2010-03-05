<?php 
/////////////////////////////////////////////////////////////////////////////
// markdown.php

/////////////////////////////////////////////////////////////////////////////
// Markdown helpers

function markdown_image_size($markdown_parser, $url)
{
	// Locate the image, quit if can't locate it
	$file=jabUrlToFile($url);
	if ($file==null)
		return "";
	if (!is_file($file))
		return "";
		
	// Load image		
	$im=false;
	if (substr($file, -4)=='.png' && function_exists("imagecreatefrompng"))
	{
		$im=@imagecreatefrompng($file);
	}
	else if (substr($file, -4)=='.gif' && function_exists("imagecreatefromgif"))
	{
		$im=@imagecreatefromgif($file);
	}
	else if ((substr($file, -4)=='.jpg' || substr($file, -5)=='.jpeg') && function_exists("imagecreatefromjpeg"))
	{
		$im=@imagecreatefromjpeg($file);
	}

	// Format size		
	if ($im!==false)
	{
		return " width=\"".imagesx($im)."\" height=\"".imagesy($im)."\"";
	}
	
	return "";
}

function markdown_anchor_attribs($markdown_parser, $url)
{
	$attribs="";
	if (substr($url, 0, 7)=="http://" || substr($url, 0, 8)=="https://")
	{
		$attribs.=" target=\"_blank\"";
		if ($markdown_parser->no_markup)
		{
			// Add nofollow to external links when in safe mode
			$attribs.=" rel=\"nofollow\"";
		}
	}
	return $attribs;
}

function markdown_qualify_url($markdown_parser, $url)
{
	// If contains protocol, leave it alone
	if (strstr($url, "://")!==false)
		return $url;
		
	// Does it start with a slash?
	if ($url[0]!="/")
		$url=$markdown_parser->local_link_prefix.$url;

	// Add the root link prefix
	$url=$markdown_parser->root_link_prefix.$url;			
		
	return $url;

}

function markdown_format_code($markdown_parser, $code)
{
	jabRequire("syntax");
	return jabFormatCode($code);
}

function jabCreateMarkdownParser()
{
	require_once(dirname(__FILE__)."/markdown/markdown.php");

	$parser_class = MARKDOWN_PARSER_CLASS;
	$parser = new $parser_class;
	$parser->fn_image_size = 'markdown_image_size';
	$parser->fn_anchor_attribs = 'markdown_anchor_attribs';
	$parser->fn_qualify_url = 'markdown_qualify_url';
	$parser->fn_format_code = 'markdown_format_code';
	return $parser;
}

// Helper to tranlate markdown in optional safe mode
function jabMarkdown($text, $safe=false) 
{
	global $jab;
	
	# Setup static parser variable.
	static $parser;
	if (!isset($parser)) 
	{
		$parser=jabCreateMarkdownParser($safe);
	}
	
	if ($safe)
	{
		$text=str_replace("!!gt!!", ">", htmlspecialchars(str_replace(">", "!!gt!!", $text)));
	}
	
	$text=$parser->transform($text);
	return $text;	
}


$jab['markdown_depth']=0;

// Starts translation of output from markdown to html
function jabEnterMarkdown($bSafe=false)
{
	global $jab;
	if (++$jab['markdown_depth']==1)
	{
		// Start buffering
		ob_start();
		$jab['markdown_safe']=$bSafe;		
	}	
}

// Ends translation of markdown
function jabLeaveMarkdown()
{
	global $jab;
	if (--$jab['markdown_depth']==0)
	{
		// Format content
		$html=jabMarkdown(ob_get_contents(), $jab['markdown_safe']);

		ob_end_clean();
		
		echo $html;
	}	
}

function jabEnterPlainText()
{
	ob_start();
}

function jabLeavePlainText()
{
	$text=htmlspecialchars(ob_get_contents());
	ob_end_clean();
	echo "<pre>".$text."</pre>";
}

?>