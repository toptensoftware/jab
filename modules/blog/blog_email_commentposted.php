<?php 
$view['masterview']="master_email";
$model['subject']="New commented posted to ".$model['blog']['title'];
?>
<p>A new comment has been posted against article <b><?php echo htmlspecialchars($model['article']->Title) ?></b>

<p class="startpreview">Start Comment</p>
<?php echo $model['comment']->Format() ?>
<p><small>Posted by <?php echo htmlspecialchars($model['comment']->Name)?>
<?php if ($model['comment']->Email):?>
 (<?php echo htmlspecialchars($model['comment']->Email) ?>)
<?php endif ?>
</small></p>
<p class="endpreview">End Comment</p>


<p>
<a href="http://<?php echo $_SERVER['HTTP_HOST'].$model['article']->FullUrl()?>">[View]</a>&nbsp;
<a href="http://<?php echo $_SERVER['HTTP_HOST'].blog_link("/comments/accept/").$model['article']->ID."/".$model['comment']->ID ?>">[Accept]</a>&nbsp;
<a href="http://<?php echo $_SERVER['HTTP_HOST'].blog_link("/comments/delete/").$model['article']->ID."/".$model['comment']->ID ?>">[Delete]</a>&nbsp;
</p>
