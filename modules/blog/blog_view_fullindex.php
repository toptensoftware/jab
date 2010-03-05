<?php
$view['additional_head_tags'].="    <link rel=\"alternate\" type=\"application/rss+xml\" title=\"Get RSS 2.0 Feed\" href=\"".blog_link("/feed.rss")."\" />\n";
?>
<h1><?php echo htmlspecialchars($model['blog']['title']) ?></h1>
<h2><?php echo htmlspecialchars($model['blog']['description']) ?></h2>
<?php // ---------------- Command Bar----------------- ?>
<p>
<a href="<?php echo blog_link("/index") ?>">Articles</a>
| <a href="<?php echo blog_link("/feed.rss") ?>">RSS Feed</a> 
<?php if (jabCanUser("author")): ?>
| <a href="/<?php echo $model['blog']['routePrefix']?>/drafts">Drafts</a>
| <a href="/<?php echo $model['blog']['routePrefix']?>/edit/new">New Post</a>
</p>
<hr/>
<?php endif ?>


<?php 
if (sizeof($model['articles']))
{
	foreach ($model['articles'] as $article)
	{
		// New Year/Month heading
		$monthyear=date('F Y', $article->TimeStamp);
		if ($monthyear!=$prevmonth)
		{
			echo "<h2 class=\"blog_fullindex_monthyear\">$monthyear</h2>\n";
			$prevmonth=$monthyear;
		}
		
		// Output paragraph
		echo "<p><span class=\"blog_fullindex_dom\">".date("jS", $article->TimeStamp)."</span>";
		echo "<span class=\"blog_fullindex_title\"><a href=\"".$article->FullUrl()."\">".htmlspecialchars($article->Title)."</a></span></p>\n";
		
	}
}
else
{
	echo "<p>No articles</p>";
}

?>