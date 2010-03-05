<?php 
$view['masterview']="none"; 
Header("Content-type: application/xml");
Header("Content-disposition: attachment; filename=export.xml");
?>
<<?php echo "?" ?>xml version="1.0" encoding="ISO-8859-1"<?php echo "?"?>>
<blog>
<?php foreach ($model['articles'] as $article): ?>
	<item>
		<id><?php echo htmlspecialchars($article->ID)?></id>
		<title><?php echo htmlspecialchars($article->Title)?></title>
		<timestamp><?php echo date("D, j M Y G:i:s", $article->TimeStamp)?> GMT.</timestamp>
		<rateTotal><?php echo $article->RateTotal ?></rateTotal>
		<rateCount><?php echo $article->RateCount?></rateCount>
		<draft><?php echo $article->Draft==1 ? "1" : "0" ?></draft>
		<content><?php echo htmlspecialchars($article->Content) ?></content>
		<comments>
<?php 
$article->LoadComments(true); 
foreach ($article->Comments as $comment): 
?>
			<comment>
				<id><?php echo htmlspecialchars($comment->ID) ?></id>
				<name><?php echo htmlspecialchars($comment->Name) ?></name>
				<email><?php echo htmlspecialchars($comment->Email) ?></email>
				<website><?php echo htmlspecialchars($comment->Website) ?></website>
				<pending><?php echo $comment->PendingReview?></pending>
				<byauthor><?php echo $comment->ByAuthor?></byauthor>
				<timestamp><?php echo date("D, j M Y G:i:s", $comment->TimeStamp)?> GMT.</timestamp>
				<content><?php echo htmlspecialchars($comment->Content) ?></content>
			</comment>
<?php endforeach; ?>
		</comments>
	</item>
<?php endforeach; ?>
</blog>
