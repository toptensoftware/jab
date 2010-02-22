<?php 
/////////////////////////////////////////////////////////////////////////////
// jab.php

/////////////////////////////////////////////////////////////////////////////
// Default configuration

$jab['jab_dir']=dirname(__FILE__);
$jab['login_page']="/account/login?referrer={referrer}";
$jab['editor_page']="/editor?file={file}&referrer={referrer}";

$_SERVER['REQUEST_URI_CLEAN']=strtok($_SERVER['REQUEST_URI'],'?');

/////////////////////////////////////////////////////////////////////////////
// Module loading

function jabRequire($modules)
{
	// Load each
	foreach (explode(";", $modules) as $module)
	{
		$file="{$module}/{$module}.php";
		if (!is_file($file))
			$file=dirname(__FILE__)."/modules/".$file;
			
		require_once($file);
	}
}



/////////////////////////////////////////////////////////////////////////////
// Error handling

// Class to represent a PHP error as an exception
class jabPhpException extends Exception
{
	public function __construct($message = null, $code = 0, Exception $previous = null)
	{
		parent::__construct($message, $code);
	}
}

// Handle php errors and throw as an exception
function jabErrorHandler ($errno, $errstr, $errfile, $errline, $errcontext)
{
	// Don't care about notices
	if ($errno==E_NOTICE || $errno==E_USER_NOTICE || error_reporting()==0)
		return false;

	// Throw it
	$ex=new jabPhpException($errstr, $errno);
	$ex->errfile=$errfile;
	$ex->errline=$errline;
	$ex->errcontext=$errcontext;
	throw $ex;
}

// Set an error handler that throws php errors as exceptions
function jabThrowPhpErrors()
{
	set_error_handler('jabErrorHandler');
}



/////////////////////////////////////////////////////////////////////////////
// Miscellaneous helpers

// Print a paragraph, with optional overridable class
function jabPrint($para, $paraType="p", $class="")
{
	global $jab;
	if (strlen($class)>0)
		$class=" class=\"$class\"";
	
	echo "<$paraType$class>".htmlspecialchars($para)."</$paraType>\n";
}

// Concatenate two paths
function jabPathAppend($left, $right)
{
    if ($left=="")
        return $right;
    if ($right=="")
        return $left;
        
    if (substr($left, -1)=="/")
        $left=substr($left, 0, -1);
    if (substr($right, 0, 1)=="/")
        $right=substr($right, 1);

	return $left."/".$right;
}

// Check if user agent is iPhone
function jabIsIPhone()
{
	return strstr($_SERVER["HTTP_USER_AGENT"], "iPhone;")!==false;
}

// RegEx check for valid email address
function jabIsValidEmail($email)
{
	return eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email);
}

// Redirect and die
function jabRedirect($url)
{
    // Redirects with leading slash don't work with authentication, need
    // to convert to relative path from self
    if ($url[0]=='/')
    {
        // Add one ../ for every / in PHP_SELF
        $self=$_SERVER['PHP_SELF'];
        $len=strlen($self);
        $rel="";
        for ($i=1; $i<$len; $i++)
        {   
            if ($self[$i]=='/')
            {
                if ($rel=="")
                    $rel.="..";
                else
                    $rel.="/..";
            }
            
            if ($self[$i]=='?')
				break;
        }
        
        $url=$rel.$url;
    }
    
    ob_end_clean();
	header("Location: ".$url);
	die;
}

// Get a request parameter
function jabRequestParam($p)
{
	if (isset($_REQUEST[$p]))
	{
		if (get_magic_quotes_gpc())
			return trim(stripslashes($_REQUEST[$p]));
		else
			return trim($_REQUEST[$p]);
	}
	else
		return "";
}

// Dump the results of a pdo statement
function jabPdoDump($stmt)
{
	$row=$stmt->fetch();
	if ($row===false)
	{
		echo "<p>No rows</p>";
		return;
	}
	
	echo "<table>\n";

	echo "<tr>";
	for ($i=0; $i<$stmt->columnCount(); $i++)
	{
		$meta=$stmt->getColumnMeta($i);
		echo "<th>".htmlspecialchars($meta['name'])."</th>\n";
	}
	echo "</tr>\n";
	
	while ($row!==false)
	{
		
		for ($i=0; $i<$stmt->columnCount(); $i++)
		{
			echo "<td>".htmlspecialchars($row[$i])."</td>\n";
		}
	
		$row=$stmt->fetch();
	}
	
	echo "</table>\n";
	
}

// Output a link to edit a page
function jabEditLink($label, $localfile)
{
	global $jab;

	$referrer=urlencode($_SERVER['REQUEST_URI']);
	$file=urlencode($localfile);

	$url=str_replace("{referrer}", $referrer, $jab['editor_page']);
	$url=str_replace("{file}", $file, $url);

	echo "<a href=\"".$url."\">".$label."</a>";
}

// Remove "." and ".." and "//" from a path
function jabCanonicalizePath($path)
{
	$prefix="";
	$suffix="";
	
	// Remove leading /
	if ($path[0]=="/")
	{
		// Relative to root
		$path=substr($path, 1);
		$prefix="/";
	}
	
	// Remove trailing /
	if (substr($path, -1)=="/")
	{
		$path=substr($path, 0, -1);
		$suffix="/";
	}
	
	
	// Go through handling ".." and "."
	$parts=explode("/", $path);
	for ($i=0; $i<sizeof($parts); $i++)
	{
		if ($parts[$i]=="." || $parts[$i]=="")
		{
			unset($parts[$i]);
			$parts=array_values($parts);
			$i--;
		}		
		else if ($parts[$i]=="..")
		{
			unset($parts[$i]);
			if ($i>=1)
				unset($parts[$i-1]);
			$parts=array_values($parts);
			$i--;
		}
	}
	
	// Rejoin
	return $prefix.implode("/", $parts).$suffix;
}

// Qualify a local url relative to the document root
function jabQualifyLocalUrl($url)
{
	// Remove leading protocol
	$protopos=strpos($url, "://");
	if ($protopos!==false)
	{
		$url=substr($url, $protopos+3);

		// Remove host name
		if (substr($url, 0, strlen($_SERVER['HTTP_HOST']))==$_SERVER['HTTP_HOST'])
		{
			$url=substr($url, strlen($_SERVER['HTTP_HOST']));
		}
		else
		{
			// Not a local url
			return false;
		}

	}
	
	if ($url[0]!="/")
	{
		// Get the request URL without query string
		$requestUrl=$_SERVER['REQUEST_URI'];
		$qpos=strpos($requestUrl, "?");
		if ($qpos!==false)
			$requestUrl=substr($requestUrl, 0, $qpos);
			
		// Relative to REQUEST_URI
		if (substr($requestUrl, -1)=="/")
		{
			$url=$requestUrl.$url;
		}
		else
		{
			$bspos=strrpos($requestUrl, "/");
			if ($bspos!==false)
				$requestDir=substr($requestUrl, 0, $bspos);
			else
				$requestDir="";
				
			$url=$requestDir."/".$url;
		}
	}
	
	$qpos=strpos($url, "?");
	if ($qpos===false)
		return jabCanonicalizePath($url);
	else
		return jabCanonicalizePath(substr($url, 0, $qpos)).substr($url, $qpos);
}

// Convert a URL to a fully qualified url
function jabQualifyUrl($url)
{
	if (strstr($url, "://")!==false)
		return $url;
	else
		return "http://".$_SERVER['HTTP_HOST'].qualify_local_url($url);		
}


// Reverse route a url to a static file
function jabUrlToFile($url)
{
	// Firstly, remove all query string stuff from the url
	$qpos=strrpos($url, "?");
	if ($qpos!==false)
	{
		$url=substr($url, 0, $qpos);
	}
	
	// Qualify to a local url
	$url=jabQualifyLocalUrl($url);
	if ($url===false)
		return "";

	// Do we have a hook for this installed?
	global $jab;
	if ($jab['fn_url_to_file'])
	{
		$file=$jab['fn_url_to_file']($url);
		if ($file)
			return $file;
	}
	
	// Look in the server document root
	if ($url[0]=='/')
	{
		$file=$_SERVER['DOCUMENT_ROOT'].$url;
		if (is_file($file))
			return $file;
	}
	
	// Can't handle it
	return false;
}



/////////////////////////////////////////////////////////////////////////////
// Authentication Core

// Return the user name of the currently logged in user
function jabUserName()
{
	@session_start();
	if (!isset($_SESSION['jab_username']))
		return null;
	return $_SESSION['jab_username'];
}

function jabSetAuthContext($context)
{
	global $jab;
	if ($context)
		$jab['auth_context']=$context;
	else
		unset($jab['auth_context']);
}

function jabSetAnonRights($rights)
{
	global $jab;
	$jab['anon_rights']=$rights;
}

// Check if user has rights
function jabCanUser($right, $forceLogin=false)
{
	global $jab;
	
	// Check logged it
	if (jabUserName()==null)
	{
		$rights=$jab['anon_rights'];
	}
	else
	{
		$rights=$_SESSION['jab_userrights'];
	}
	
	// Super user?
	if ($rights=="*")
		return true;

	// Qualify right with current auth context		
	if (strstr($right, ".")===false && strlen($jab['auth_context'])>0)
		$right=$jab['auth_context'].".".$right;

	// Check rights
	if (in_array($right, explode(";", $rights)))
		return true;
		
	// Force login?
	if ($forceLogin)
		jabLogin();
	
	return false;
}

// Ensure user is logged in
function jabLogin()
{
	// Already logged in?
	if (jabUserName()!=null)
		return;
	
	// Redirect to login page
	global $jab;
	$strRefPage=urlencode($_SERVER['REQUEST_URI']);
	jabRedirect(str_replace("{referrer}", $strRefPage, $jab['login_page']));
}


// Login
function jabSetUser($username, $rights)
{
	// Store the logged in user
	session_start();
	$_SESSION['jab_username']=$username;
	$_SESSION['jab_userrights']=$rights;
}

// Logout
function jabLogout()
{
	session_start();	
	unset($_SESSION['jab_username']);
	unset($_SESSION['jab_userrights']);
}

// Dump a variable
function jabDump(&$var)
{
	echo "<pre>";
	var_dump($var);
	echo "</pre>";
}

function dd(&$var)
{
	jabDump($var);
	die;
}

/*
include_once('geshi.php')
$geshi = new GeSHi($source, $language);
echo $geshi->parse_code();
*/

?>