<?php 
/////////////////////////////////////////////////////////////////////////////
// forms.php

function jabRenderCaptcha()
{
	global $jab;
	if (isset($jab['renderCaptcha']))
		$jab['renderCaptcha']();
}

function jabCheckCaptcha()
{
	global $jab;
	if (isset($jab['checkCaptcha']))
		return $jab['checkCaptcha']();
	return true;
}


?>