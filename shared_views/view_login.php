<?php
jabRequire("forms");
?>
<h1>Login</h1>
<form class="mainform" id="theform" method="post" action="<?php echo $_SERVER["REDIRECT_URL"]?>"> 

<?php if ($model['login_failed']): ?>
	<div class="Errors">
	<p>Login failed.  Please check your username and password are correct and try again.</p>
	</div>
<?php endif; ?>
 
	<?php jabHtmlInput("User Name:", "username", $model["username"], "stdfield") ?>
	<?php jabHtmlPassword("Password:", "password", $model["password"], "stdfield") ?>
	<?php jabHtmlHidden("referrer", $model["referrer"]) ?>
	<div class="clearer"></div>
	<?php jabHtmlSubmitButton("Login", "submit") ?>
	
</form> 
<script type="text/javascript">document.getElementById("username").focus();</script>

<br/> 
<p>Don't have an account?  <a href="register">Register Now</a></p>