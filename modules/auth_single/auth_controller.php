<?php 
/////////////////////////////////////////////////////////////////////////////
// auth_login_controller.php

global $auth;
$auth=$routeData;

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
	
	if ($username==$auth['username'] && md5($password)==$auth['password'])
	{
		jabSetUser($username, $auth['rights']);
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


?>