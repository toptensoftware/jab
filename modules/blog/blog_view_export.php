n\<?php $view['masterview']="none"; ?>
<<?php echo "?" ?>xml version="1.0" encoding="ISO-8859-1"<?php echo "?"?>>
<blog>
<?php foreach ($model['articles'] as $article): ?>
	<item>
		<id><?php echo htmlspecialchars($article->ID)?></id>
		<title><?php echo htmlspecialchars($article->Title)?></title>
		<timestamp><?php echo date("D, j M Y G:i:s", $article->TimeStamp)?> GMT.</timestamp>
		<rateTotal><?php echo $article->RateTotal ?></rateTotal>
		<rateCount><?php echo $article->RateCount?></rateCount>
		<content><?php echo htmlspecialchars($article->Content) ?></content>
		<comments>
<?php 
$article->LoadComments(true); 
foreach ($article->Comments as $comment): 
?>
			<comment>
				<id><?php echo htmlspecialchars($comment->ID) ?></name>
				<name><?php echo htmlspecialchars($comment->Name) ?></name>
				<email><?php echo htmlspecialchars($comment->Email) ?></email>
				<pending><?php echo $comment->PendingReview?></pending>
				<timestamp><?php echo date("D, j M Y G:i:s", $comment->TimeStamp)?> GMT.</timestamp>
				<content><?php echo htmlspecialchars($comment->Content) ?></content>
			</comment>
<?php endforeach; ?>
		</comments>
	</item>
<?php endforeach; ?>
</blog>
