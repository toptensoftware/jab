<?php
jabRequire("forms");
$view['additional_head_tags'].="    <link rel=\"alternate\" type=\"application/rss+xml\" title=\"Get RSS 2.0 Feed\" href=\"".blog_link("/feed.rss")."\" />\n";
$view['additional_head_tags'].="    <script type=\"text/javascript\" src=\"/js/jquery.js\"></script>\n";
$view['additional_head_tags'].="    <script type=\"text/javascript\" src=\"/js/jQuery.timers.js\"></script>\n";
?>
<h1><?php echo htmlspecialchars($model['blog']['title']) ?></h1>
<h2><?php echo htmlspecialchars($model['blog']['description']) ?></h2>

<script type="text/javascript">
<!--
$(document).ready(
	function()
	{
		if ($('#Draft').attr('value')=="1")
		{
			$('#Content').keypress(postChanged);
			$('#Title').keypress(postChanged);
			$('#TimeStamp').keypress(postChanged);
			$('#Content').bind("paste", postChanged);
			$('#Title').bind("paste", postChanged);
			$('#TimeStamp').bind("paste", postChanged);
			$('#Content').bind("input", postChanged);
			$('#Title').bind("input", postChanged);
			$('#TimeStamp').bind("input", postChanged);
			$('#save_now').click(saveDocument);
		}
	}
)

function postChanged()
{
	if ($('#save_now').length!=0)
	{
		$('#save_now').attr('disabled', false);
		$('#save_now').attr('value', 'Save and Continue');
		$(document).stopTime();
		$(document).oneTime(30000, 
			function()
			{
				saveDocument();
			}
		);
	}
}

function saveDocument()
{
	$('#save_now').attr('disabled', true);
	$('#save_now').attr('value', 'Saving');
	
	var data=$('#theform').serializeArray();
	$.post($(document).attr("location")+"?ajax=1", data, 
		function(result)
		{
			if (result=="OK")
			{
				$('#save_now').attr('disabled', true);
				$('#save_now').attr('value', 'Saved');
			}
			else
			{
				$('#save_now').attr('disabled', false);
				$('#save_now').attr('value', 'Save and Continue');
				alert("Failed to save changes - " + result);
			}
		}
	);
}

-->
</script>

<?php // ---------------- Heading ----------------- ?>
<h2><?php echo $model['article']->ID==0 ? "Post New" : "Edit"?> Article</h2>


<?php // ---------------- Preview ----------------- ?>
<?php if ($model['preview']): ?>
<p class="startpreview">Preview</p>
<h2><?php echo $model['article']->Title ?></h2>
<?php echo $model['article']->Format() ?>
<p><small>Posted <?php echo date('l, jS F Y', $model['article']->TimeStamp)." at ".date('h:i a', $model['article']->TimeStamp)?></small></p>
<p class="endpreview">Preview</p>
<?php endif; ?>
<?php jabHtmlErrors($model['errors'], "Please correct the following errors:") ?>

<?php // ---------------- Main Edit Form----------------- ?>
<form class="mainform" id="theform" method="post" action="<?php echo $_SERVER["REQUEST_URI_CLEAN"]?>" enctype="multipart/form-data"> 
 
	<?php jabHtmlHidden("ID", $model['article']->ID) ?>
	<?php jabHtmlHidden("Draft", $model['article']->Draft ? "1" : "0") ?>
	<?php jabHtmlInput("Title:", "Title", $model['article']->Title, "stdfield") ?>
	<?php jabHtmlInput("Date/Time:", "TimeStamp", $model['article']->TimeStamp==0 ? "" : date('d M Y H:i:s', $model['article']->TimeStamp), "stdfield") ?>
	<?php jabHtmlTextArea("Content: (supports <a href=\"http://michelf.com/projects/php-markdown/extra/\">Markdown Extra</a>)", "Content", $model['article']->Content, $class="stdtextareafield") ?>

	<div class="clearer"></div>
    
<?php
	if ($model['article']->Draft)
	{
		jabHtmlButton("Save and Continue", "save_now");
		jabHtmlSubmitButton("Save and Close", "save");
		jabHtmlSubmitButton("Preview", "preview");
		jabHtmlSubmitButton("Publish", "post");
		jabHtmlSubmitButton("Discard", "delete");
	}
	else
	{
		jabHtmlSubmitButton("Preview", "preview");
		jabHtmlSubmitButton("Publish Changes", "post");
		jabHtmlSubmitButton("Revert to Draft", "save");
		jabHtmlSubmitButton("Delete", "delete");
	}
	
	jabHtmlSubmitButton("Cancel", "cancel");
	
?>
 
<?php // ---------------- File Upload----------------- ?>

	<?php if (isset($model['blog']['uploadfolder'])): ?>
	<h2>Upload Files</h2>
	<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo isset($model['blog']['maxuploadfilesize']) ? $model['blog']['maxuploadfilesize'] : 1000000?>" />
	<p>Select files to upload:</p>
	<?php 
	for ($i=1; $i<=(isset($model['blog']['maxuploadfiles']) ? $model['blog']['maxuploadfiles'] : 4); $i++)
	{
		jabHtmlFileUpload("file".$i);
		echo "\n";
	}
	?>
	<?php jabHtmlCheckBox("Overwrite Existing Files", "overwrite", True) ?>
	<?php jabHtmlCheckBox("Add to article", "addtoarticle", True) ?>
	<div class="clearer"></div>
	<?php jabHtmlSubmitButton("Upload", "upload") ?>
<?php endif; ?>

</form> 

<?php // ---------------- Initialize focus ----------------- ?>
<?php if (strlen($model['article']->Title)==0): ?>
<script type="text/javascript">document.getElementById("Title").focus();</script>
<?php else: ?>
<script type="text/javascript">document.getElementById("Content").focus();</script>
<?php endif ?>

