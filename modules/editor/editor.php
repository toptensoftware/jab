<?php 
/////////////////////////////////////////////////////////////////////////////
// editor.php

jabRequire("route;render");

// Route all urls for a cms
function jabRouteEditor($editor)
{
	jabSetAuthContext("editor");
	jabSetRouteHandlerPath(dirname(__FILE__));
	jabRoute("get;post", $editor['routePrefix'], "editor_controller.php", "editor_{httpmethod}", $editor);
	jabSetRouteHandlerPath(null);
	jabSetAuthContext(null);
}

?>