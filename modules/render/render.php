<?php 
/////////////////////////////////////////////////////////////////////////////
// render.php

// Load a text ".jab" file with fields at the top, multi-line text at the bottom.
// eg:
/*
Apples=Red
Bananas=Yellow
---
This is an article about fruit!
The End
*/

function jabLoadContent($filename)
{
	// Open file
	$fh = @fopen($filename, 'r');
	if ($fh===false)
		throw new exception("Failed to load jab content file $filename");
		
	// Read it
	$text= @fread($fh, filesize($filename));
	fclose($fh);
	
	// Storage for loaded content
	$vars=array();
	
	// Parse it...
	$i=0;
	$len=strlen($text);
	$name="";
	$line=0;
	while ($i<$len)
	{
		// First assignment on line
		if ($text[$i]=='=' && $name=="")
		{
			// Extract value name
			$name=substr($text, $line, $i-$line);

			// Skip the assignment
			$i++;
			
			// Store new line start
			$line=$i;
			
			continue;
		}
		
		// End of line
		if ($text[$i]=="\r" || $text[$i]=="\n")		
		{
			// Extract the value
			$value=substr($text, $line, $i-$line);
			
			// Skip the EOL
			if ($text[$i]=="\r")
				$i++;
			if ($text[$i]=="\n")
				$i++;
				
			// Delimiter line?
			if ($value[0]=="-")
			{
				$vars['content']=substr($text, $i);
				return $vars;
			}
			
			// If no name specified, assume this is the first line of text
			if ($name=="")
			{
				$vars['content']=substr($text, $line);
				return $vars;
			}
			
			// Add a named value
			$vars[$name]=$value;

			$line=$i;
			$name="";

			continue;
		}
		
		// Next character
		$i++;
	}
	
	return $vars;
}	

// Set the folder to search for views
function jabSetThemeFolder($themeFolder)
{
	global $jab;
	$jab['themeFolder']=realpath($themeFolder);
	
	jabRouteStaticContent("theme", $themeFolder);
}

// Render a jab content file
function jabRenderPartialView($file, &$model, $renderContext="partial")
{
	global $jab;

	// Include theme folder and shared view folders
	$oldpath=get_include_path();
	set_include_path($jab['themeFolder'].PATH_SEPARATOR.get_include_path().PATH_SEPARATOR.$jab['jab_dir']."/shared_views");

	if (substr($file, -4)==".jab")
	{
		// Load jab file
		$view=jabLoadContent($file);
		
		// Format content
		jabRequire("markdown");
		$view['content']=jabMarkdown($view['content']);
	}
	else if (substr($file, -4)==".php")
	{
		// Start buffering
		ob_start();	
		
		// Render it
		include($file);
				
		// Auto close markdown blocks
		while ($jab['markdown_depth']>0)
		{
			jabLeaveMarkdown();
		}


		// Capture it
		$view['content']=ob_get_contents();		
		
		// End buffering
		ob_end_clean();

	}

	// Use a masterview?		
	if ($view['masterview']=="none")
	{
		// Just echo the content
		echo $view['content'];
	}
	else
	{		
		// Default masterview?
		if (!isset($view['masterview']))
			$view['masterview']="master_view";
			
		// Include the masterview
		include($view['masterview'].".php");
	}

	set_include_path($oldpath);
}	

// Render a view and exit
function jabRenderView($file, $model)
{
	jabRenderPartialView($file, $model, "page");
	die;
}

// Render a view to an email
function jabRenderMail($file, $model)
{
	// Render view to buffer
	ob_start();	
	jabRenderPartial($file, $model, "email");
	$message=ob_get_contents();		
	ob_end_clean();

	// Grab headers from model
	$headers=$model['headers'];
	
	// Setup from header
	if (isset($model['from']))
		$headers.="From: ".$model['from']."\nReply-To: ".$model['from']."\n".$headers;
		
	// Send mail
	return mail($model['to'], $model['subject'], $message, $headers);
}

global $jab;
$jab['contentTypes']=array(
	"css"=>"text/css", 
	"png"=>"image/png",
	"jpeg"=>"image/jpeg",
	"jpg"=>"image/jpeg",
	"gif"=>"image/gif",
	"txt"=>"text/plain",
	"htm"=>"text/html",
	"html"=>"text/html",
	"exe"=>"application/octet-stream",
	"zip"=>"application/octet-stream",
	);
		

function jabEchoFile($file)
{
	global $jab;
	
	// Another type of file, just output it
	$dotpos=strrpos($file, ".");
	if (dotpos!==false)
		$ext=strtolower(substr($file, $dotpos+1));
	else
		$ext="";
		
	if (isset($jab['contentTypes'][$ext]))
		$contentType=$jab['contentTypes'][$ext];
	else
		throw new Exception("Refusing to echo file contents for '$file' due to unknown file extension '$ext'");
		
	ob_end_clean();
	Header( "Content-type: {$contentType}");
	readfile($file);
	die;
}

?>