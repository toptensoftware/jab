<?php 
/////////////////////////////////////////////////////////////////////////////
// auth_login_controller.php

global $auth;
$auth=$routeData;

// Open auth db
$auth['pdo']=new PDO($auth['pdo_dsn'], $auth['pdo_username'], $auth['pdo_password'], $auth['pdo_driveroptions']);

// Read schema version, create tables if necessary
$info=$auth['pdo']->query("SELECT * FROM {$auth['tablePrefix']}Info WHERE Name='SchemaVersion'");
$auth['pdo']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
if ($info===false)
{
	$auth['pdo']->exec(<<<SQL
		CREATE TABLE {$auth['tablePrefix']}Info(Name, Value);
SQL
);
	
	$auth['pdo']->exec(<<<SQL
		INSERT INTO {$auth['tablePrefix']}Info(Name, Value) 
		VALUES ('SchemaVersion', 1);
SQL
);

	$auth['pdo']->exec(<<<SQL
		CREATE TABLE {$auth['tablePrefix']}Users(
			username TEXT PRIMARY KEY, 
			email TEXT, 
			password TEXT, 
			activationId TEXT, 
			rights TEXT, 
			activated INTEGER, 
			enabled INTEGER
			)
SQL
);
}

// Login form request
function login_get()
{
	$model['referrer']=jabRequestParam('referrer');
	return jabRenderView("view_login.php", $model);
}

// Login post handler
function login_post($username, $password, $referrer)
{
	global $auth;
	
	// Lookup DB
	$stmt=$auth['pdo']->prepare("SELECT * FROM {$auth['tablePrefix']}Users WHERE username=:username and password=:password and enabled=1 and activated=1");
	$stmt->bindValue(":username", $username);
	$stmt->bindValue(":password", md5($password));
	$stmt->execute();
	$row=$stmt->fetch();
	
	// Found?
	if ($row!==false)
	{
		// Yes
		jabSetUser($row['username'], $row['rights']);
		jabRedirect(strlen($referrer)>0 ? $referrer : "/");
	}
	else
	{
		// No
		$model['username']=$username;
		$model['password']=$password;
		$model['referrer']=$referrer;
		$model['login_failed']=true;
		return jabRenderView("view_login.php", $model);
	}
}

// Logout
function logout($referrer)
{
	jabLogout();
	return jabRedirect($referrer);
}

// Register
function register_get()
{
	return jabRenderView("auth_view_register.php", $model);
}

// Register account handler
function register_post($username, $email, $password, $password2)
{
	$model['username']=$username;
	$model['email']=$email;
	
	if (strlen($username)==0)
		$model['errors'][]="Please enter a username";
	if (strpos($username, "/")!==false || strpos($username, "\\")!==false || strpos($username, "<")!==false)
		$model['errors'][]="User name contains invalid characters";
	if (strlen($password)==0)
		$model['errors'][]="Please enter a password";
	if ($password!=$password2)
		$model['errors'][]="Re-typed password didn't match";
	if (!jabIsValidEmail($email))
		$model['errors'][]="Please enter a valid email address";
	
	if (sizeof($model['errors']))
	{
		return jabRenderView("auth_view_register.php", $model);
	}
	
	global $auth;
	
	try
	{
		// Setup model
		$model['activationId']=md5($username.$email.date(DATE_RFC822));
		$model['activateUrl']="http://".$_SERVER['HTTP_HOST']."/".$auth['routePrefix']."/activate/".urlencode($username)."/".$model['activationId'];
		$model['auth']=$auth;
		$model['to']=$email;
		$model['from']=$auth['adminEmail'];
		$model['subject']="Welcome to ".$auth['sitename'];
		
		// Create the account
		$stmt=$auth['pdo']->prepare("INSERT INTO {$auth['tablePrefix']}Users(username, email, password, rights, activationId, activated, enabled) VALUES (:username, :email, :password, :rights, :activationId, 0, 1);");
		$stmt->bindValue(":username", $username);
		$stmt->bindValue(":email", $email);
		$stmt->bindValue(":password", md5($password));
		$stmt->bindValue(":rights", $auth['defaultRights']);
		$stmt->bindValue(":activationId", $model['activationId']);
		$stmt->execute();

		// Send registration email
		jabRenderMail("auth_email_register.php", $model);
		
		return jabRenderView("auth_view_register_success.php", $model);
	}
	catch (Exception $ex)
	{
		$model['errors'][]="Failed to register account, please try a different account name";
		$model['errors'][]=htmlspecialchars($ex->getMessage());
		return jabRenderView("auth_view_register.php", $model);
	}
}

function activate($username, $activationId)
{
	global $auth;
	$model['auth']=$auth;

	$stmt=$auth['pdo']->prepare(<<<SQL
		UPDATE {$auth['tablePrefix']}Users 
		SET activationId="", activated=1 
		WHERE activationId=:activationId and username=:username
SQL
);
	$stmt->bindValue(":username", $username);
	$stmt->bindValue(":activationId", $activationId);
	$stmt->execute();

	if ($stmt->rowCount()>0)
	{
		return jabRenderView("auth_view_activation_success.php", $model);
	}
	else
	{
		return jabRenderView("auth_view_activation_failed.php", $model);
	}
}

function settings_get()
{
	jabLogin();
	
	jabRenderView("auth_view_settings.php", $model);
}

?>