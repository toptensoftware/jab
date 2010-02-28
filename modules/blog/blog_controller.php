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
	$model['articles']=blog_load_articles($page, $blog['indexArticles'], false);
	
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

function fullindex()
{
	global $blog;
	$model['blog']=$blog;

	// Load all articles
	$model['articles']=blog_load_articles(0, 0x7fffffff, false);

	// Render it		
	jabRenderView("blog_view_fullindex.php", $model);
}

function drafts()
{
	if (!jabCanUser("author", true))
		return false;
		
	global $blog;
	$model['blog']=$blog;

	// Load all articles
	$model['articles']=blog_load_articles(0, 0x7fffffff, true);

	// Render it		
	jabRenderView("blog_view_drafts.php", $model);
}

function new_post()
{
	$article=new BlogArticle();
	$article->Title="Untitled Article";
	$article->Draft=true;
	$article->Save();
	
	jabRedirect(blog_link("/edit/".$article->ID));
}

function edit_article_get($id)
{
	jabCanUser("author", true);

	global $blog;
	$model['blog']=$blog;
	$model['article']=blog_load_article($id, true);
	if ($model['article']==null)
		return false;
		
	jabRenderView("blog_view_editarticle.php", $model);
}

function edit_article_post($id)
{
	global $blog;

	jabCanUser("author", true);
	
	// Cancel?
	if (jabRequestParam("cancel"))
	{
		return jabRedirect(jabRequestParam("Draft")==1 ? blog_link("/drafts") : blog_link("/"));
	}
		
	// Delete?
	if (jabRequestParam("delete"))
	{
		if ($id==0)
		{
			jabRedirect(blog_link("/"));
		}
		else
		{
			jabRedirect(blog_link("/delete/".$id));
		}
	}
	
	$model['blog']=$blog;
	$model['article']=blog_load_article($id, true);
	if ($model['article']==null)
		return false;
	
	$save=!!jabRequestParam("save");
	$post=!!jabRequestParam("post");
	$auto=jabRequestParam("autosave")=="1";

	if ($model['article']->InitFromForm($save || $auto, $auto, $model['errors']))
	{
		if ($auto)
		{
			if ($model['article']->Save())
				echo "OK";
			else
				echo "Failed";
			die;
		}
		
		if ($post || $save)
		{
			if ($model['article']->Save())
			{
				jabRedirect(blog_link($model['article']->Draft ? "/drafts" : "/"));
			}

			$model['errors'][]="Failed to write DB record ".$model['article']->ID;
		}
	}

	$model['preview']=!!jabRequestParam("preview");
	jabRenderView("blog_view_editarticle.php", $model);
}

function delete_post_get($id)
{
	jabCanUser("author", true);

	global $blog;
	$model['blog']=$blog;
	$model['article']=blog_load_article($id, true);
	if ($model['article']==null)
		return false;
	jabRenderView("blog_view_deletearticle.php", $model);
}

function delete_post_post($id)
{
	jabCanUser("author", true);
	
	if (strlen(jabRequestParam("delete"))>0)
		blog_delete_article($id);

	jabRedirect(blog_link("/"));
}

function view_post_get($id)
{
	global $blog;
	$model['blog']=$blog;
	$model['article']=blog_load_article($id, jabCanUser("author"));
	
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
	$model['article']=blog_load_article($id, jabCanUser("author"));
	$model['preview']=!!jabRequestParam("preview");
	$model['ReplyTo']=jabRequestParam("ReplyTo");

	if ($model['comment']->InitFromForm($model['errors']))
	{
		if (jabRequestParam("post"))
		{
			if (strlen($model['ReplyTo'] && jabCanUser("author")))
			{
				$model['to']=$model['ReplyTo'];
				$model['from']=$blog['notifyEmailFrom'];
				jabRenderMail("blog_email_commentreplied.php", $model);
			}
			
			$model['comment']->Save();
			
			if ($blog['notifyOnComment'])
			{
				$model['to']=$blog['notifyEmailTo'];
				$model['from']=strlen($model['comment']->Email)==0 ? $blog['notifyEmailFrom'] : $model['comment']->Email;
				jabRenderMail("blog_email_commentposted.php", $model);
			}
			jabRedirect($_SERVER["REQUEST_URI_CLEAN"]);
		}
	}
	
	
	jabRenderView("blog_view_article.php", $model);
}

function accept_comment($articleid, $commentid)
{
	jabCanUser("author", true);

	// Get the article
	$article=blog_load_article($articleid, true);
	if ($article==null)
		return false;

	// Accept the comment
	blog_accept_comment($commentid, true);
	
	// Redirect
	jabRedirect($article->FullUrl());
}

function reject_comment($articleid, $commentid)
{
	jabCanUser("author", true);

	// Get the article
	$article=blog_load_article($articleid, true);
	if ($article==null)
		return false;

	// Accept the comment
	blog_accept_comment($commentid, false);
	
	// Redirect
	jabRedirect($article->FullUrl());
}

function delete_comment($articleid, $commentid)
{
	jabCanUser("author", true);

	// Get the article
	$article=blog_load_article($articleid, true);
	if ($article==null)
		return false;

	// Accept the comment
	blog_delete_comment($commentid);
	
	// Redirect
	jabRedirect($article->FullUrl());
}

function get_rss_feed()
{
	global $blog;
	$model['blog']=$blog;

	// Load articles
	$model['articles']=blog_load_articles(0, $blog['feedArticles'], false);
	
	// Render it		
	jabRenderView("blog_view_rss.php", $model);
	
}

function get_export()
{
	jabCanUser("author", true);

	global $blog;
	$model['blog']=$blog;

	// Load articles
	$model['articles']=blog_load_articles(0, 0x7fffffff, "all");
	
	// Render it		
	jabRenderView("blog_view_export.php", $model);
	
}

function import_get()
{
	jabCanUser("author", true);

	// Render import upload view
	jabRenderView("blog_view_import.php", $model);
}

function import_post()
{
	jabCanUser("author", true);
	
	blog_import($_FILES['importFile']['tmp_name'], jabRequestParam("dropoldcontent")!="");	
	
	// Render import upload view
	jabRedirect(blog_link(""));
}

function upgrade()
{
	init_blog_db();
	jabRedirect(blog_link(""));
}


?>