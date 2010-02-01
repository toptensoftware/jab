<?php 
/////////////////////////////////////////////////////////////////////////////
// blog.php

jabRequire("route;render");

// Route all urls for a blog
function jabRouteBlog($blog)
{
	jabSetAuthContext("blog");
	jabSetRouteHandlerPath(dirname(__FILE__));

	jabRoute("get", $blog['routePrefix']."/index/{page}", "blog_controller.php", "index", $blog);
	jabRoute("get", $blog['routePrefix']."/fullindex", "blog_controller.php", "fullindex", $blog);
	jabRoute("get;post", $blog['routePrefix']."/edit/new", "blog_controller.php", "new_post_{httpmethod}", $blog);
	jabRoute("get;post", $blog['routePrefix']."/edit/{id}", "blog_controller.php", "edit_post_{httpmethod}", $blog);
	jabRoute("get;post", $blog['routePrefix']."/delete/{id}", "blog_controller.php", "delete_post_{httpmethod}", $blog);
	jabRoute("get;post", $blog['routePrefix']."/posts/{id}/*", "blog_controller.php", "view_post_{httpmethod}", $blog);
	jabRoute("get", $blog['routePrefix']."/comments/accept/{articleid}/{commentid}", "blog_controller.php", "accept_comment", $blog);
	jabRoute("get", $blog['routePrefix']."/comments/reject/{articleid}/{commentid}", "blog_controller.php", "reject_comment", $blog);
	jabRoute("get", $blog['routePrefix']."/comments/delete/{articleid}/{commentid}", "blog_controller.php", "delete_comment", $blog);
	jabRoute("get", $blog['routePrefix']."/feed.rss", "blog_controller.php", "get_rss_feed", $blog);

	jabRouteStaticContent($blog['routePrefix'], $blog['uploadfolder']);
	
	jabSetRouteHandlerPath(null);
	jabSetAuthContext(null);
}


?>