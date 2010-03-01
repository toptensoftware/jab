<?php 
/////////////////////////////////////////////////////////////////////////////
// querystring.php

/////////////////////////////////////////////////////////////////////////////
// Query string helpers

// Split a query string into it's base url (before the ?) and a map
// of name/value pairs
function jabQueryStringSplit(&$url, &$nv)
{
	$qpos=strpos($url, "?");
	if ($qpos===false)
	{
		return;
	}
	
	// Split off the query string part
	$qstring=substr($url, $qpos+1);
	
	// Split it
	$vars;
	foreach (explode("&", $qstring) as $var)
	{
		$nv1=split("=", $var, 2);
		$nv[$nv1[0]]=$nv1[1];
	}
	
	$url=substr($url, 0, $qpos);
}

// Join a url and a set of named values back into a query string
function jabQueryStringUnsplit($url, $nv)
{	
	if (sizeof($nv)==0)
		return $url;
		
	// Rebuild string
	$qstring="";
	foreach ($nv as $n=>$v)
	{
		if ($qstring!="")
			$qstring.="&";
		$qstring.=$n;
		if ($v!=null)
			$qstring.="=".$v;
	}
	
	return $url."?".$qstring;
}

// Add (or replace) a named/value pair to a url
function jabQueryStringAdd($url, $name, $value)
{
	jabQueryStringSplit($url, $nv);
	$nv[$name]=urlencode($value);
	return jabQueryStringUnsplit($url, $nv);	
}

// Remove a named value from a query string
function jabQueryStringRemove($url, $name)
{
	jabQueryStringSplit($url, $nv);
	unset($nv[$name]);
	return jabQueryStringUnsplit($url, $nv);	
}


?>