<?php 
/////////////////////////////////////////////////////////////////////////////
// markdown.php

/////////////////////////////////////////////////////////////////////////////
// Markdown helpers

// Helper to tranlate markdown in optional safe mode
function jabMarkdown($text, $safe=false) 
{
	require_once(dirname(__FILE__)."/markdown/markdown.php");
	if (!$safe)
	{
		return Markdown($text);
	}
	else
	{
		$parser = new Markdown_Parser;
		$parser->no_markup = true;
		$parser->no_entities = true;
		return $parser->transform($text);
	}
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



?>