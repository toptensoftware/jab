<?php
$view['additional_head_tags'].="    <link rel=\"alternate\" type=\"application/rss+xml\" title=\"Get RSS 2.0 Feed\" href=\"".blog_link("/feed.rss")."\" />\n";
?>
<h1><?php echo htmlspecialchars($model['blog']['title']) ?></h1>
<h2><?php echo htmlspecialchars($model['blog']['description']) ?></h2>
<?php // ---------------- Command Bar----------------- ?>
<p>
<a href="<?php echo blog_link("/fullindex") ?>">Full Index</a>
| <a href="<?php echo blog_link("/feed.rss") ?>">RSS Feed</a>
<?php if (jabCanUser("post")): ?>
| <a href="/<?php echo $model['blog']['routePrefix']?>/edit/new">New Post</a>
</p>
<?php endif ?>
<hr/>

<?php // ---------------- Article Loop----------------- ?>
<?php if (sizeof($model['articles'])): ?>
<div class="blog_index">
<?php foreach ($model['articles'] as $article): ?>

<?php // ---------------- Edit Commands ----------------- ?>
<?php if (jabCanUser("edit") || jabCanUser("delete")): ?>
<span style="float:right">
<small>
<?php if (jabCanUser("edit")): ?>
<a href="/<?php echo $model['blog']['routePrefix']?>/edit/<?php echo $article->ID?>">[Edit]</a>
<?php endif; if (jabCanUser("delete")): ?>
<a href="/<?php echo $model['blog']['routePrefix']?>/delete/<?php echo $article->ID?>">[Delete]</a>
<?php endif; ?>
</small>
</span>
<?php endif ?>

<?php // ---------------- Article ----------------- ?>
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

if ($model['blog']['enableComments'])
{
	if (function_exists(jabRenderDisqusLink))
	{
		jabRenderDisqusLink($article->FullUrl());
	}
	else
	{
		echo "<span class=\"blog_comment_button\"><a href=\"".$article->FullUrl()."\">Read or Leave Comments</a> (".$article->GetCommentCount(jabCanUser("review_comments")).")</span>\n";
	}
}
else
{
	echo "<a href=\"".$article->FullUrl()."\">Permalink</a>\n";
}

?>
</p>
</div>

<?php // ---------------- End of Article Loop ----------------- ?>
<?php endforeach; ?>
</div>
<?php else: ?>
<p>No more articles</p>
<?php endif; ?>


<?php // ---------------- Paging ----------------- ?>
<?php if (isset($model['nextpagelink'])):?>
<span style="float:left"><a href="<?php echo $model['nextpagelink']?>">&#171; Older Articles</a></span>
<?php endif ?>
<?php if (isset($model['prevpagelink'])):?>
<span style="float:right"><a href="<?php echo $model['prevpagelink']?>">Newer Articles &#187;</a></span>
<?php endif ?>

