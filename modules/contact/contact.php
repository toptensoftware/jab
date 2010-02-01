<?php

jabRequire("route;render");

function jabRouteContact($contact)
{
	jabSetRouteHandlerPath(dirname(__FILE__));
	jabRoute("get;post", $contact['routePrefix'], "contact_controller.php", "contact_{httpmethod}", $contact);
	jabSetRouteHandlerPath(null);
}

?>
