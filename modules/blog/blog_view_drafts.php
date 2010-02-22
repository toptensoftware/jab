<?php
$view['additional_head_tags'].="    <link rel=\"alternate\" type=\"application/rss+xml\" title=\"Get RSS 2.0 Feed\" href=\"".blog_link("/feed.rss")."\" />\n";
?>
<h1><?php echo htmlspecialchars($model['blog']['title']) ?></h1>
<h2><?php echo htmlspecialchars($model['blog']['description']) ?></h2>
<?php // ---------------- Command Bar----------------- ?>
<p>
<a href="<?php echo blog_link("/fullindex") ?>">Full Index</a>
| <a href="<?php echo blog_link("/index") ?>">Articles</a>
| <a href="<?php echo blog_link("/feed.rss") ?>">RSS Feed</a> 
<?php if (jabCanUser("author")): ?>
| <a href="/<?php echo $model['blog']['routePrefix']?>/edit/new">New Post</a>
</p>
<hr/>
<?php endif ?>

<h2>Drafts</h2>

<?php 
if (sizeof($model['articles']))
{
	foreach ($model['articles'] as $article)
	{
		// Output paragraph
		echo "<p><a href=\"".$article->FullUrl()."\">[view]</a> - ";
		echo "<a href=\"".blog_link("/edit/".$article->ID)."\">".htmlspecialchars($article->Title)."</a></p>";
		
	}
}
else
{
	echo "<p>No articles</p>";
}

?>