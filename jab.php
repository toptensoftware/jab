<?php 
/////////////////////////////////////////////////////////////////////////////
// jab.php

/////////////////////////////////////////////////////////////////////////////
// Default configuration

$jab['jab_dir']=dirname(__FILE__);
$jab['login_page']="/account/login?referrer={referrer}";
$jab['editor_page']="/editor?file={file}&referrer={referrer}";

/////////////////////////////////////////////////////////////////////////////
// Module loading

function jabRequire($modules)
{
	// Load each
	foreach (explode(";", $modules) as $module)
	{
		require_once(dirname(__FILE__)."/modules/{$module}/{$module}.php");
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
	if ($errno==E_NOTICE || $errno==E_USER_NOTICE )
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


?>