<?php 
/////////////////////////////////////////////////////////////////////////////
// recaptcha.php

function renderRecaptcha()
{
	require_once(dirname(__FILE__)."/recaptcha-php-1.10/recaptchalib.php");

	global $jab;
	if (isset($jab['recaptcha_publickey']))
		echo recaptcha_get_html($jab['recaptcha_publickey']);
}

function checkRecaptcha()
{
	require_once(dirname(__FILE__)."/recaptcha-php-1.10/recaptchalib.php");

	global $jab;

	if (!isset($jab['recaptcha_privatekey']))
		return true;

	$resp = recaptcha_check_answer ($jab['recaptcha_privatekey'],
									$_SERVER["REMOTE_ADDR"],
									$_POST["recaptcha_challenge_field"],
									$_POST["recaptcha_response_field"]);

	if ($resp->is_valid) 
		return true;
	else
		return "The reCAPTCHA wasn't entered correctly. Go back and try it again." .
		   "(reCAPTCHA said: " . $resp->error . ")";
}

function jabInitRecaptcha($private, $public)
{
	global $jab;
	$jab['renderCaptcha']=renderRecaptcha;
	$jab['checkCaptcha']=checkRecaptcha;
	$jab['recaptcha_publickey']=$public;
	$jab['recaptcha_privatekey']=$private;
}

?>