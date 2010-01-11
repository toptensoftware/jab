<?php 
/////////////////////////////////////////////////////////////////////////////
// auth.php

jabRequire("route;render");

// Route all urls for authentication
function jabRouteAuth($auth)
{
	jabSetRouteHanderPath(dirname(__FILE__));
	jabRoute("get;post", $auth['routePrefix']."/login", "auth_controller.php", "login_{httpmethod}", $auth);
	jabRoute("get", $auth['routePrefix']."/logout", "auth_controller.php", "logout", $auth);
	jabRoute("get;post", $auth['routePrefix']."/register", "auth_controller.php", "register_{httpmethod}", $auth);
	jabRoute("get;post", $auth['routePrefix']."/settings", "auth_controller.php", "settings_{httpmethod}", $auth);
	jabRoute("get", $auth['routePrefix']."/activate/{username}/{activationId}", "auth_controller.php", "activate", $auth);
	jabSetRouteHanderPath(NULL);
}

?>