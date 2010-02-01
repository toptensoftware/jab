<?php
jabRequire("forms;captcha");
$article=$model['article'];
$view['additional_head_tags'].="    <link rel=\"alternate\" type=\"application/rss+xml\" title=\"Get RSS 2.0 Feed\" href=\"".blog_link("/feed.rss")."\" />\n";
?>
<h1><?php echo htmlspecialchars($model['blog']['title']) ?></h1>
<h2><?php echo htmlspecialchars($model['blog']['description']) ?></h2>
<?php // ---------------- Command Bar----------------- ?>
<p>
<a href="<?php echo blog_link("/index") ?>">Articles</a>
| <a href="<?php echo blog_link("/fullindex") ?>">Full Index</a>
| <a href="<?php echo blog_link("/feed.rss") ?>">RSS Feed</a> 
<?php if (jabCanUser("post")): ?>
| <a href="/<?php echo $model['blog']['routePrefix']?>/edit/new">New Post</a>
</p>
<hr/>
<?php endif ?>

<div class="blog_article">
<h2><?php echo htmlspecialchars($article->Title) ?></h2>
<?php echo $article->Format() ?>
<p><small>Posted <?php echo date('l, jS F Y', $article->TimeStamp)." at ".date('h:i a', $article->TimeStamp)?></small></p>
<p>
<?php 

if (function_exists(jabRenderShareLink)) 
{ 
	jabRenderShareLink($article->Title, "http://".$_SERVER['HTTP_HOST'].$article->FullUrl()); 
} 

if ($model['blog']['enableComments'] && function_exists(jabRenderDisqusEditor)) 
{ 
	jabRenderDisqusEditor();
}
else
{
	echo "<a href=\"".$article->FullUrl()."\">Permalink</a>\n";
}

?>

</p>
<div id="disqus_thread"></div>
</div>

<?php
if ($model['blog']['enableComments'] && !function_exists(jabRenderDisqusLink))
	$article->LoadComments(jabCanUser("review_comments"));
if (sizeof($article->Comments)>0):
?>
<div class="blog_comments">
<h3>Comments</h3>
<? foreach ($article->Comments as $comment): ?>

<div class="blog_comment">

<?php if (jabCanUser("review_comments")): ?>
<span style="float:right">
<small>
<?php if ($comment->PendingReview): ?>
<a href="<?php echo blog_link("/comments/accept/".$article->ID."/".$comment->ID) ?>">[Accept]</a>
<?php else: ?>
<a href="<?php echo blog_link("/comments/reject/".$article->ID."/".$comment->ID) ?>">[Reject]</a>
<?php endif; ?>
<a href="<?php echo blog_link("/comments/delete/".$article->ID."/".$comment->ID) ?>" onClick="return confirm('Are you sure you want to delete this comment?')">[Delete]</a>
</small>
</span>

<?php endif ?>
<?php echo $comment->Format() ?>
<p><small>Posted <?php echo date('l, jS F Y', $comment->TimeStamp)." at ".date('h:i a', $comment->TimeStamp)?> by <?php echo htmlspecialchars($comment->Name)?></small></p>
</div>

<?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($model['blog']['enableComments'] && !function_exists(jabRenderDisqusLink)): ?>
<h3>Leave A Comment</h3>
<?php if ($model['preview']): ?>
<p class="startpreview">Preview</p>
<h2>Comment by <?php echo $model['comment']->Name?></h2>
<?php echo $model['comment']->Format() ?>
<p class="endpreview">Preview</p>
<?php endif; ?>

<?php jabHtmlErrors($model['errors'], "Please correct the following errors:") ?>

<form class="mainform" id="theform" method="post" action="<?php echo $_SERVER["REQUEST_URI_CLEAN"]?>"> 
 
	<?php jabHtmlHidden("ID", $model['article']->ID) ?>
	<?php jabHtmlInput("Your Name:", "Name", $model['comment']->Name, "stdfield") ?>
	<?php jabHtmlInput("Email Address: (optional, not shown)", "Email", $model['comment']->Email, "stdfield") ?>
	<?php jabHtmlTextArea("Message: (supports some <a href=\"http://michelf.com/projects/php-markdown/extra/\">Markdown Extra</a>)", "Content", $model['comment']->Content, $class="stdtextareafield") ?>

	<div class="clearer"></div>
    
	<?php jabRenderCaptcha() ?>
	
	<?php jabHtmlSubmitButton("Post Comment", "post") ?>
	<?php jabHtmlSubmitButton("Preview", "preview") ?>
	
	<small>All comments will be reviewed for spam before being displayed.</small>
 
</form> 
<?php endif; ?>
<?php if (!$model['blog']['enableComments']): ?>
<p><small>Comments disabled</small></p>
<?php endif; ?>

