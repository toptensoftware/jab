<?php
jabRequire("forms");
?>
<h1>Editor</h1>
<h2><?php echo htmlspecialchars($model['file'])?></h2>

<?php // ---------------- Heading ----------------- ?>
<?php jabHtmlErrors($model['errors'], "Please correct the following errors:") ?>

<?php // ---------------- Main Edit Form----------------- ?>
<form class="mainform" id="theform" method="post" action="<?php echo $_SERVER["REDIRECT_URL"]?>" enctype="multipart/form-data"> 

	<?php jabHtmlHidden("deleteconfirmed", $model['deleteconfirmed']) ?>
	<?php jabHtmlHidden("file", $model['file']) ?>
	<?php jabHtmlHidden("referrer", $model['referrer']) ?>
	<?php jabHtmlTextArea("Content: (supports <a href=\"http://michelf.com/projects/php-markdown/extra/\">Markdown Extra</a>)", "content", $model['content'], $class="largetextareafield") ?>

	<div class="clearer"></div>
    
	<?php jabHtmlSubmitButton("Save", "save") ?>
	<?php jabHtmlSubmitButton("Cancel", "cancel") ?> 
	<?php jabHtmlSubmitButton("Delete File", "delete") ?>

<?php // ---------------- File Upload----------------- ?>

	<h2>Upload Files</h2>
	<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo isset($model['editor']['maxuploadfilesize']) ? $model['blog']['maxuploadfilesize'] : 1000000?>" />
	<p>Select files to upload:</p>
	<?php 
	for ($i=1; $i<=(isset($model['editor']['maxuploadfiles']) ? $model['editor']['maxuploadfiles'] : 4); $i++)
	{
		jabHtmlFileUpload("file".$i);
		echo "\n";
	}
	?>
	<?php jabHtmlCheckBox("Overwrite Existing Files", "overwrite", True) ?>
	<?php jabHtmlCheckBox("Add to article", "addtoarticle", True) ?>
	<div class="clearer"></div>
	<?php jabHtmlSubmitButton("Upload", "upload") ?>

</form>

<?php // ---------------- Initialize focus ----------------- ?>
<?php if (strlen($model['article']->Title)==0): ?>
<script type="text/javascript">document.getElementById("Content").focus();</script>
<?php endif ?>

