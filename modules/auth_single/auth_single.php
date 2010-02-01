<?php 
/////////////////////////////////////////////////////////////////////////////
// auth.php

jabRequire("route;render");

// Route all urls for authentication
function jabRouteAuth($auth)
{
	jabSetRouteHandlerPath(dirname(__FILE__));
	jabRoute("get;post", $auth['routePrefix']."/login", "auth_controller.php", "login_{httpmethod}", $auth);
	jabRoute("get", $auth['routePrefix']."/logout", "auth_controller.php", "logout", $auth);
	jabSetRouteHandlerPath(NULL);
}

?>