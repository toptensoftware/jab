<?php
jabRequire("forms");
?>

<h1>Contact</h1>

<h2>Got a question? Have some feedback?</h2>

<?php echo $model['contact']['contactDetails'] ?>
<?php jabHtmlErrors($model['errors'], "Please correct the following errors:") ?>
<?php if ($model['send_error']):?>
<div class="Errors">
<p>Sorry, due to a technical problem, your message couldn't be sent.  Please try later, or email us directly.</p>
</div>
<?php endif; ?>

<form class="mainform" id="theform" method="post" action="/contact"> 
 
	<?php jabHtmlInput("Your Name:", "Name", $model["name"], "stdfield") ?>
	<?php jabHtmlInput("Your Email Address:", "Email", $model["email"], "stdfield") ?>
	<?php jabHtmlTextArea("Your Message: (supports some <a href=\"http://michelf.com/projects/php-markdown/extra/\">Markdown Extra</a>)", "Message", $model["message"], $class="stdtextareafield") ?>

	<div class="clearer"></div>
    
    <?php jabRenderCaptcha(); ?>
	
	<?php jabHtmlSubmitButton("Send", "submit") ?>

</form> 
<script type="text/javascript">document.getElementById("Name").focus();</script>
