<?php
jabRequire("forms");
?>
<h1>Accont Settings</h1>

<form class="mainform" id="theform" method="post" action="<?php echo $_SERVER["REDIRECT_URL"]?>"> 

	<?php jabHtmlErrors($model['errors'], "Please correct the following errors:") ?>
 
	<?php jabHtmlReadOnly("User Name:", "username", $model["username"], "stdfield") ?>
	<?php jabHtmlInput("Email Address:", "email", $model["email"], "stdfield") ?>
	<?php jabHtmlPassword("Password:", "password", $model["password"], "stdfield") ?>
	<?php jabHtmlPassword("Re-type Password:", "password2", $model["password2"], "stdfield") ?>
	<div class="clearer"></div>
	<br/>
	<?php jabRenderCaptcha() ?>
	<?php jabHtmlSubmitButton("Register", "submit") ?>
	
</form> 
<script type="text/javascript">document.getElementById("email").focus();</script>


<br/> 
<p>Already have an account?  <a href="login">Login Here</a></p>

