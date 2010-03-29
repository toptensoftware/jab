<?php $view['masterview']="none"; header("Content-type: application/rss+xml"); ?>
<<?php echo "?" ?>xml version="1.0" encoding="ISO-8859-1"<?php echo "?"?>>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<title><?php echo htmlspecialchars($model['blog']['title'])?></title>
		<link>http://<?php echo htmlspecialchars($_SERVER['HTTP_HOST']."/".$model['blog']['routePrefix'])?></link>
		<copyright><?php echo htmlspecialchars($model['blog']['copyright'])?></copyright>
		<description><?php echo htmlspecialchars($model['blog']['description'])?></description>
		<managingEditor><?php echo htmlspecialchars($model['blog']['managingEditor'])?></managingEditor>
		<language>en-US</language>
		<generator>jabBlog 1.1</generator>
		<atom:link href="http://<?php echo $_SERVER['HTTP_HOST'].htmlspecialchars($_SERVER['REQUEST_URI_CLEAN'])?>" rel="self" type="application/rss+xml" />
<?php foreach ($model['articles'] as $article): ?>
		<item>
			<title><?php echo htmlspecialchars($article->Title)?></title>
			<author><?php echo htmlspecialchars($model['blog']['managingEditor'])?></author>
			<pubDate><?php echo date("D, j M Y H`:i:s", $article->TimeStamp)?> GMT</pubDate>
			<description><![CDATA[<?php echo $article->Format(true) ?>]]></description>
			<category><?php echo htmlspecialchars($model['blog']['defaultFeedCategory'])?></category>
			<link>http://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'].$article->FullUrl()) ?></link>
			<guid isPermaLink="false">http://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'].$article->Guid()) ?></guid>
			<comments>http://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'].$article->FullUrl()) ?></comments>
		</item>
<?php endforeach; ?>
	</channel>
</rss>
