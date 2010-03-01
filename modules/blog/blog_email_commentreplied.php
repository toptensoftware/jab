<?php 
$view['masterview']="master_email";
$model['subject']="Comment reply from ".$model['blog']['title'];
?>
<p class="Normal">Hello,</p>

<p class="Normal"><?php echo htmlspecialchars($model['comment']->Name) ?> has replied to your recent comment on 
<a href="http://<?php echo $_SERVER['HTTP_HOST'].$model['article']->FullUrl()?>"><?php echo htmlspecialchars($model['article']->Title) ?></a> at <?php echo htmlspecialchars($model['blog']['title']) ?>.</p>

<p><a href="http://<?php echo $_SERVER['HTTP_HOST'].$model['article']->FullUrl()?>">Click here</a> to view the reply.</p>

<p class="Normal"><small>This is a manually invoked, one time only notification email.  You have not been subscribed to a mailing list and don't need to unsubscribe to stop further notifications.</small></p>

