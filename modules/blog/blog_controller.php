<?php 
/////////////////////////////////////////////////////////////////////////////
// auth_login_controller.php

global $blog;
$blog=$routeData;

require_once("blog_model.php");

// Get index
function index($page=0)
{
	global $blog;
	$model['blog']=$blog;
	$model['page']=$page;

	// Load articles
	$model['articles']=blog_load_articles($page, $blog['indexArticles']);
	
	// Work out page links
	if (sizeof($model['articles'])==$blog['indexArticles'])
		$model['nextpagelink']="/".$blog['routePrefix']."/index/".($page+1);
	if ($page>1)
		$model['prevpagelink']="/".$blog['routePrefix']."/index/".($page-1);
	else if ($page==1)
		$model['prevpagelink']="/".$blog['routePrefix'];

	// Render it		
	jabRenderView("blog_view_index.php", $model);
}

function new_post_get()
{
	jabCanUser("post", true);

	global $blog;
	$model['blog']=$blog;
	$model['article']=new BlogArticle();
	jabRenderView("blog_view_editarticle.php", $model);
}

function new_post_post()
{
	jabCanUser("post", true);
	
	// Cancel
	if (jabRequestParam("cancel"))
		return jabRedirect(blog_link("/"));
		
	global $blog;
	$model['blog']=$blog;
	$model['article']=new BlogArticle();
	if ($model['article']->InitFromForm($model['errors']))
	{
		if (jabRequestParam("post"))
		{
			if ($model['article']->Save())
			{
				jabRedirect(blog_link("/"));
			}
			
			$model['errors'][]="Failed to write DB record ".$model['article']->ID;
		}
	}
	
	$model['preview']=!!jabRequestParam("preview");
	
	jabRenderView("blog_view_editarticle.php", $model);
}

function edit_post_get($id)
{
	jabCanUser("edit", true);

	global $blog;
	$model['blog']=$blog;
	$model['article']=blog_load_article($id);
	if ($model['article']==null)
		return false;
	jabRenderView("blog_view_editarticle.php", $model);
}

function edit_post_post($id)
{
	jabCanUser("edit", true);

	global $blog;
	$model['blog']=$blog;
	$model['article']=blog_load_article($id);
	if ($model['article']==null)
		return false;
	if ($model['article']->InitFromForm($model['errors']))
	{
		if (jabRequestParam("post"))
		{
			if ($model['article']->Save())
			{
				jabRedirect("/".$blog['routePrefix']);
			}
			$model['errors'][]="Failed to write DB record ".$model['article']->ID;
		}
	}

	$model['preview']=!!jabRequestParam("preview");
	jabRenderView("blog_view_editarticle.php", $model);
}

function delete_post_get($id)
{
	jabCanUser("delete", true);

	global $blog;
	$model['blog']=$blog;
	$model['article']=blog_load_article($id);
	if ($model['article']==null)
		return false;
	jabRenderView("blog_view_deletearticle.php", $model);
}

function delete_post_post($id)
{
	jabCanUser("delete", true);
	
	if (strlen(jabRequestParam("delete"))>0)
		blog_delete_article($id);

	jabRedirect(blog_link("/"));
}

function view_post_get($id)
{
	global $blog;
	$model['blog']=$blog;
	$model['article']=blog_load_article($id);
	
	if ($model['article']==null)
		return false;
	$model['comment']=new BlogComment();
	jabRenderView("blog_view_article.php", $model);
}

function view_post_post($id)
{
	global $blog;
	$model['blog']=$blog;
	$model['comment']=new BlogComment();
	$model['comment']->IDArticle=$id;
	$model['article']=blog_load_article($id);
	$model['preview']=!!jabRequestParam("preview");
	if ($model['comment']->InitFromForm($model['errors']))
	{
		if (jabRequestParam("post"))
		{
			$model['comment']->Save();
			
			if ($blog['notifyOnComment'])
			{
				$model['to']=$blog['notifyEmailTo'];
				$model['from']=strlen($model['comment']->Email)==0 ? $blog['notifyEmailFrom'] : $model['comment']->Email;
				jabRenderMail("blog_email_commentposted.php", $model);
			}
			jabRedirect($_SERVER["REDIRECT_URL"]);
		}
	}
	
	
	jabRenderView("blog_view_article.php", $model);
}

function accept_comment($articleid, $commentid)
{
	jabCanUser("review_comments", true);

	// Get the article
	$article=blog_load_article($articleid);
	if ($article==null)
		return false;

	// Accept the comment
	blog_accept_comment($commentid, true);
	
	// Redirect
	jabRedirect($article->FullUrl());
}

function reject_comment($articleid, $commentid)
{
	// Get the article
	$article=blog_load_article($articleid);
	if ($article==null)
		return false;

	// Accept the comment
	blog_accept_comment($commentid, false);
	
	// Redirect
	jabRedirect($article->FullUrl());
}

function delete_comment($articleid, $commentid)
{
	// Get the article
	$article=blog_load_article($articleid);
	if ($article==null)
		return false;

	// Accept the comment
	blog_delete_comment($commentid);
	
	// Redirect
	jabRedirect($article->FullUrl());
}

function get_uploaded_file($filename)
{
	global $blog;
	
	// Check uploads supported
	if (!isset($blog['uploadfolder']))
		return;
		
	// Check for attempt to access something in another folder
	if (strstr($filename, "..")!==false || strstr($filename, "/")!==false)
		return;
		
	// Work out target file
	$target_path=jabPathAppend($blog['uploadfolder'], $filename);
	
	// Quit if doesn't exist
	if (!file_exists($target_path))
		return;

	// Serve it
	jabEchoFile($target_path);
}

function get_rss_feed()
{
	global $blog;
	
	global $blog;
	$model['blog']=$blog;

	// Load articles
	$model['articles']=blog_load_articles(0, $blog['feedArticles']);
	
	// Render it		
	jabRenderView("blog_view_rss.php", $model);
	
}

?>