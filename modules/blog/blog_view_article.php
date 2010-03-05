<?php
jabRequire("forms;captcha");
$article=$model['article'];
$view['additional_head_tags'].="    <link rel=\"alternate\" type=\"application/rss+xml\" title=\"Get RSS 2.0 Feed\" href=\"".blog_link("/feed.rss")."\" />\n";
?>
<h1><?php echo htmlspecialchars($model['blog']['title']) ?></h1>
<h2><?php echo htmlspecialchars($model['blog']['description']) ?></h2>
<script type="text/javascript">
function appendEmail(email)
{
	var field=document.getElementById("ReplyTo");
	if (field.value.length>0)
		field.value += ", " + email;
	else
		field.value = email;
}
</script>
<?php // ---------------- Command Bar----------------- ?>
<p>
<a href="<?php echo blog_link("/index") ?>">Articles</a>
| <a href="<?php echo blog_link("/fullindex") ?>">Full Index</a>
| <a href="<?php echo blog_link("/feed.rss") ?>">RSS Feed</a> 
<?php if (jabCanUser("author")): ?>
| <a href="/<?php echo $model['blog']['routePrefix']?>/drafts">Drafts</a>
| <a href="/<?php echo $model['blog']['routePrefix']?>/edit/new">New Post</a>
</p>
<?php endif ?>
<hr />

<?php // ---------------- Edit Commands ----------------- ?>
<?php if (jabCanUser("author")): ?>
<span style="float:right">
<small>
<a href="/<?php echo $model['blog']['routePrefix']?>/edit/<?php echo $article->ID?>">[Edit]</a>
<a href="/<?php echo $model['blog']['routePrefix']?>/delete/<?php echo $article->ID?>">[Delete]</a>
</small>
</span>
<?php endif ?>


<div class="blog_article">
<h2><?php echo htmlspecialchars($article->Title) ?></h2>
<?php echo $article->Format() ?>
<p><small>Posted <?php echo formatRelativeTime($article->TimeStamp)?></small></p>
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
?>

</p>
<div id="disqus_thread"></div>
</div>

<?php
if ($model['blog']['enableComments'] && !function_exists(jabRenderDisqusLink))
	$article->LoadComments(jabCanUser("author"));
if (sizeof($article->Comments)>0):
?>
<div class="blog_comments">
<? foreach ($article->Comments as $comment): ?>

<?php if (jabCanUser("author")): ?>
<div class="blog_comment_actions">
<p>
<?php if ($comment->PendingReview): ?>
<a href="<?php echo blog_link("/comments/accept/".$article->ID."/".$comment->ID) ?>">[Accept]</a>
<?php else: ?>
<a href="<?php echo blog_link("/comments/reject/".$article->ID."/".$comment->ID) ?>">[Reject]</a>
<?php endif; ?>
<a href="<?php echo blog_link("/comments/delete/".$article->ID."/".$comment->ID) ?>" onClick="return confirm('Are you sure you want to delete this comment?')">[Delete]</a>
<a href="javascript:appendEmail('<?php echo htmlspecialchars($comment->Email) ?>')">[Reply]</a>
</p>
</div>
<?php endif ?>

<div class="blog_comment<?php echo $comment->ByAuthor ? " blog_authorcomment" : "" ?>">
<div class="blog_comment_gravatar"><img src="http://www.gravatar.com/avatar/<?php echo md5(strtolower($comment->Email)) ?>?s=60&d=<?php echo urlencode("http://".$_SERVER['HTTP_HOST']."/theme/default_gravatar_image.png")?>" width="60" height="60"></div>
<div class="blog_comment_content">
<span style="float:right"><small>Posted <?php echo formatRelativeTime($comment->TimeStamp)?></small></span>
<p class="blog_comment_title"><?php echo $comment->FormatNameLink() ?> says:</p>
<?php echo $comment->Format() ?>
</div>
<div class="clearer"></div>
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
<?php if (!jabCanUser("author")): ?>
	<?php jabHtmlInput("Your Name:", "Name", $model['comment']->Name, "stdfield") ?>
	<?php jabHtmlInput("Email Address: <small>(optional, not shown, used for <a href=\"http://www.gravatar.com\" target=\"_blank\">Gravatar</a>)</small>", "Email", $model['comment']->Email, "stdfield") ?>
	<?php jabHtmlInput("Website: <small>(optional, nofollow)</small>", "Website", $model['comment']->Website, "stdfield") ?>
<?php else: ?>
	<?php jabHtmlInput("Reply To:", "ReplyTo", $model['ReplyTo'], "stdfield") ?>
<?php endif; ?>
	<?php jabHtmlTextArea("Message: <small>(supports some <a href=\"http://michelf.com/projects/php-markdown/extra/\" target=\"_blank\">Markdown Extra</a>)</small>", "Content", $model['comment']->Content, $class="stdtextareafield") ?>

	<div class="clearer"></div>
    
<?php if (!jabCanUser("author")): ?>
	<?php jabRenderCaptcha() ?>
<?php endif; ?>
	
	<?php jabHtmlSubmitButton("Post Comment", "post") ?>
	<?php jabHtmlSubmitButton("Preview", "preview") ?>
	
	<small>All comments will be reviewed for spam before being displayed.</small>
 
</form> 
<?php endif; ?>
<?php if (!$model['blog']['enableComments']): ?>
<p><small>Comments disabled</small></p>
<?php endif; ?>

