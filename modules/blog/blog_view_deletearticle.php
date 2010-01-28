<?php
jabRequire("forms");
$view['additional_head_tags'].="    <link rel=\"alternate\" type=\"application/rss+xml\" title=\"Get RSS 2.0 Feed\" href=\"".blog_link("/feed.rss")."\" />\n";
$article=$model['article'];
?>
<h1><?php echo htmlspecialchars($model['blog']['title']) ?></h1>
<h2><?php echo htmlspecialchars($model['blog']['description']) ?></h2>

<p>Really delete this article?</p>
<form class="mainform" id="theform" method="post" action="<?php echo $_SERVER["REQUEST_URI_CLEAN"]?>"> 
<?php jabHtmlSubmitButton("Yes, delete it", "delete") ?>
<?php jabHtmlSubmitButton("No, keep it", "cancel") ?>
</form> 
 
<hr/>

<div class="blog_article">
<h2><?php echo $article->Title ?></h2>
<?php echo $article->Format() ?>
<p><small>Posted <?php echo date('l, jS F Y', $article->TimeStamp)." at ".date('h:i a', $article->TimeStamp)?></small></p>

