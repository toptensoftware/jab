<?php

global $contact;
$contact=$routeData;

function contact_get()
{
	jabRequire("captcha");
	
	global $contact;
	$model['contact']=$contact;
	jabRenderView("contact_view_form.php", $model);
}

function contact_post()
{
	jabRequire("captcha");

	global $jab;
	global $contact;
	
	// Retrieve model values
	$model['contact']=$contact;
	$model['name']=jabRequestParam('Name');
	$model['email']=jabRequestParam('Email');
	$model['message']=jabRequestParam('Message');
	
	if (strlen($model['name'])==0)
	{
		$model['errors'][]="Name is missing";
	}
	
	if (!jabIsValidEmail($model['email']))
	{
		$model['errors'][]="Invalid email address";
	}
	
	if (strlen($model['message'])==0)
	{
		$model['errors'][]="You haven't entered a message";
	}

	// Check recapture OK
	$error=jabCheckCaptcha();
	if ($error!==true)
		$model['errors'][]=$error;

	if (sizeof($model['errors'])>0)
	{
		return jabRenderView("contact_view_form.php", $model);
	}
	
	$model['to']=$contact['emailTo'];
	$model['from']="\"".$model['name']."\" <".$model['email'].">";
	$model['subject']=$contact['emailSubject'];
	if (!jabRenderMail("contact_email.php", $model))
	{
		$model['send_error']=true;
		jabRenderView("contact_view_form.php", $model);
	}	
	else
	{
		jabRenderView("contact_view_success.php", null);
	}
	
}

?>
