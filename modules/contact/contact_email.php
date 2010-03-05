<?php 
$view['masterview']="master_email";
$view['plaintext']=$model['message'];
jabRequire("markdown");
jabEnterMarkdown(true);
echo $model['message'];
jabLeaveMarkdown();
?>
