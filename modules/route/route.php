<?php 
/////////////////////////////////////////////////////////////////////////////
// route.php

/////////////////////////////////////////////////////////////////////////////
// Routing system

// Find a file on path
function jabFindOnPath($path, $file)
{
	if ($file[0]=='/')
		return $file;
		
	foreach (explode(PATH_SEPARATOR, $path) as $dir)
	{
		$full=jabPathAppend($dir, $file);
		if (is_file($full))
			return realpath($full);
	}
	
	return $file;
}

// Replace all {variables} in string $in with values from $vars
function jabReplaceVariables($in, $vars)
{
	$str="";
	$i=0;
	while (true)
	{
		// Find next variable
		$varpos=strpos($in, "{", $i);
		if ($varpos===false)
		{
			$str.=substr($in, $i);
			return $str;
		}
		
		$str.=substr($in, $i, $varpos-$i);
		
		// Skip it		
		$varpos++;
		
		// Find of variable
		$varend=strpos($in, "}", $varpos);
		if ($varend===false)
		{
			$str.=substr($in, $varpos-1);
			return $str;
		}
		
		// Extract the variable
		$var=substr($in, $varpos, $varend-$varpos);
		$i=$varend+1;
		
		// Replace it
		if (isset($vars[$var]))
			$str.=$vars[$var];
		else
			$str.="{".$var."}";
		
	}
}

// Compare a path to a routing path
function MatchPath($pathSpec, $pathIn)
{
	// start with defaults...
	$vars=array();
	
	$pathSpec.="\0";
	$pathIn.="\0";
	$i=0;
	$j=0;
	
	// Skip leading slash
	if ($pathIn[$j]=="/")
		$j++;
		
	// Can't route an empty path
	if ($pathIn[$j]=="\0")
	{
		// Unless we have total wildcard routing spec
		if ($pathSpec[$i]=="*")
		{
			$vars['urlTail']=substr($pathIn, 0, -1);
			return $vars;
		}
		return null;
	}

	while ($pathSpec[$i]!="\0" && $pathIn[$j]!="\0")
	{
		// Wildcard?
		if ($pathSpec[$i]=="*")
		{
			$vars['urlTail']=substr($pathIn, $j, -1);
			$j=strlen($pathIn)-1;
			continue;
		}
		
		// Variable?
		if ($pathSpec[$i]=="{")
		{
			$i++;
			
			// Extract the variable name
			$end=strpos($pathSpec, "}", $i);
			if ($end===false)
				return null;
			$var=substr($pathSpec, $i, $end-$i);
			$i=$end+1;
			
			// Extract value
			$start=$j;
			while ($pathIn[$j]!="\0" && $pathIn[$j]!="/" && $pathIn[$j]!=$pathSpec[$i])
				$j++;
			$val=substr($pathIn, $start, $j-$start);
			
			// Store it
			$vars[$var]=$val;
		}
		else
		{
			// End of input string can match with slash
			if ($pathSpec[$i]=="/" && $pathIn[$j]=="\0")
			{
				$i++;
				continue;
			}
				
			// Check character matches
			if ($pathSpec[$i]!=$pathIn[$j])
				return null;
			$i++;
			$j++;
		}
	}

	// Check we're at the end of the input string	
	if ($pathIn[$j]!="\0")
		return null;
		
	return $vars;			
}


// Internal function to do routing
function jabProcessRoute($httpmethods, $pathSpec, $impl, $function, $routeData, $routeHandlerPath)
{
	// Check http method
	if (strlen($httpmethods)>0 && !in_array(strtolower($_SERVER["REQUEST_METHOD"]), explode(";", strtolower($httpmethods)) ))
		return false;
		
	$routingDesc="$httpmethods, $pathSpec, $impl, $function";

	// Match path	
	$vars=MatchPath($pathSpec, $_SERVER["REQUEST_URI_CLEAN"]);
	if ($vars===null)
	{
		return false;
	}
	
	
	// Store the http method
	$vars['httpmethod']=strtolower($_SERVER["REQUEST_METHOD"]);

	// Replace variables to get the implementation name
	$impl=jabReplaceVariables($impl, $vars);
		
	$oldpath=get_include_path();
	if (strlen($impl)>0)
	{
		// Work out full route handler path
		$fullRouteHandlerPath=$oldpath;
		if (strlen($routeHandlerPath)>0)
		{
			$fullRouteHandlerPath=$oldpath.PATH_SEPARATOR.$routeHandlerPath;
		}
		
		// Find the implementation on the route handler path
		$impl=jabFindOnPath($fullRouteHandlerPath, $impl);
		
		// Update the PHP include path to include the route handler's folder
		// This is so views can be loaded from the same folder as the route handler
		set_include_path($oldpath.PATH_SEPARATOR.dirname($impl));

		// Include the implementation
		require_once(basename($impl));
	}

	// Replace variables to get the function name
	$function=jabReplaceVariables($function, $vars);
	
	// Get reflection info for the function
	$refFunction=new ReflectionFunction($function);

	// Resolve all arguments
	$args=array();
	foreach ($refFunction->getParameters() as $p)
	{
		// Parameter from URL?
		if (isset($vars[$p->name]))
		{
			$args[]=$vars[$p->name];
			continue;
		}
		
		// Parameter from request params
		if (isset($_REQUEST[$p->name]))
		{
			if (get_magic_quotes_gpc())
				$args[]=trim(stripslashes($_REQUEST[$p->name]));
			else
				$args[]=trim($_REQUEST[$p->name]);
				
			continue;
		}
		
		// Parameter from routeData?
		if (is_array($routeData) && isset($routeData[$p->name]))
		{
			$args[]=$routeData[$p->name];
			continue;
		}
		
		// Does the argument have a default value?
		if ($p->isDefaultValueAvailable())
		{
			$args[]=$p->getDefaultValue();
			continue;
		}

		throw new Exception("Failed to resolve parameter \$".$p->name." for function $function() when routing $routingDesc");		
	}

	// Call it
	$refFunction->invokeArgs($args);

	set_include_path($oldpath);
	
	return false;
}


function jabRoute($httpmethods, $pathSpec, $impl, $function, $routeData=null)
{
	global $jab;
	$routeEntry['httpmethods']=$httpmethods;
	$routeEntry['pathSpec']=$pathSpec;
	$routeEntry['impl']=$impl;
	$routeEntry['function']=$function;
	$routeEntry['routeData']=$routeData;
	$routeEntry['authContext']=$jab['auth_context'];
	$routeEntry['routeHandlerPath']=$jab['routeHandlerPath'];
	$jab['routingEntries'][]=$routeEntry;
}

function jabSetRouteHandlerPath($path)
{
	global $jab;
	if ($path)
	{
		$jab['routeHandlerPath']=$path;
	}
	else
		unset($jab['routeHandlerPath']);
}

function jabProcessRoutes()
{
	global $jab;

	// Setup
	jabThrowPhpErrors();
	date_default_timezone_set('UTC');
	session_start();

	// Process
	try
	{
		// Process route entries
		foreach ($jab['routingEntries'] as $routeEntry)
		{
			jabSetAuthContext($routeEntry['authContext']);
			
			jabProcessRoute($routeEntry['httpmethods'],
							$routeEntry['pathSpec'],
							$routeEntry['impl'],
							$routeEntry['function'],
							$routeEntry['routeData'],
							$routeEntry['routeHandlerPath']);

		}
		
		jabSetAuthContext(null);
		
		// File not found?
		jabRenderView("error_notfound.php", null);
	}
	catch (Exception $ex)
	{
		restore_error_handler();
		ob_end_clean();
		jabRenderView("error_php.php", $ex);	
	}
}

function jabRouteStaticContent($routePrefix, $contentRoot)
{
	$routeData['contentRoot']=$contentRoot;
	$routeData['routePrefix']=$routePrefix;

	if (strlen($routePrefix)==0)	
		$routePrefix="*";
	else
		$routePrefix.="/*";

	jabRoute("get", $routePrefix, null, "jabDoRouteStaticContent", $routeData);
}

function jabDoRouteStaticContent($urlTail, $contentRoot)
{
	global $jab;
	
	// Remove querystring
	$qpos=strchr($urlTail, "?");
	if ($qpos!==false)
		$urlTail=substr($urlTail, 0, $qpos);

	// Find jab file
	$path=jabPathAppend($contentRoot, $urlTail);

	if (is_dir($path))
	{
		// If folder path doesn't end in trailing slash, add one and redirect
		if (substr($path, -1)!="/")
		{
			$url=$_SERVER['REQUEST_URI'];
			$qpos=strchr($url, "?");
			if ($qpos!==false)
				$url=substr($url, 0, $qpos);
			jabRedirect("http://".$_SERVER['HTTP_HOST'].$url."/");
		}
		
		$path=jabPathAppend($path, "index.jab");
	}
	else
	{
		// .html at the end is optional
		if (strtolower(substr($path, -5))==".html")
			$path=substr($path, 0, -5);
			
		// Use jab file?
		if (is_file($path.".jab"))
			$path.=".jab";
	}

	if (jabCanUser('cms.edit'))
	{
		$model['sourceFile']=$path;
		
		if (!is_file($path))
			$jab['missingSourceFile']=$model['sourceFile'].".jab";
	}
	
	// Exists?
	if (!is_file($path))
	{
		return false;
	}
		
	// Render it
	if (substr($path, -4)==".php" || substr($path, -4)==".jab")
	{
		jabRenderView($path, $model);
	}
	else
	{
		jabEchoFile($path);
	}
		
}


function jabStaticUrlToFile($url)
{
	// Walk the routing table looking for static routing entries
	global $jab;
	foreach ($jab['routingEntries'] as $routeEntry)
	{
		if ($routeEntry['function']=='jabDoRouteStaticContent')
		{
			$routePrefix=$routeEntry['routeData']['routePrefix'];
			$contentRoot=$routeEntry['routeData']['contentRoot'];
			
			$file=false;
			if (strlen($routePrefix)==0)
			{
				$file=$contentRoot.$url;
			}
			else
			{
				if (substr($url, 0, strlen($routePrefix)+2)=="/".$routePrefix."/")
				{
					$file=$contentRoot.substr($url, strlen($routePrefix)+1);
				}
			}
			
			
			if (is_file($file))
				return realpath($file);
		}
	}
	
	return false;
}

function jabReRoute($from, $to, $regex=false, $redirect=false)
{
	$url=substr($_SERVER['REQUEST_URI'], 1);
	
	if ($regex)
	{
		$newurl=preg_replace($from, $to, $url);
		if ($newurl==$from)
			return false;
	}
	else
	{
		if ($url!=$from)
			return false;
		$urlnew=$to;
	}
	
	$url="/".$url;
	
	if ($redirect)
	{
		jabRedirect($urlnew);
	}
	else
	{
		$_SERVER['REQUEST_URI']=$urlnew;
		$_SERVER['REQUEST_URI_CLEAN']=strtok($urlnew,'?');
	}
}

global $jab;
$jab['fn_url_to_file']='jabStaticUrlToFile';


?>