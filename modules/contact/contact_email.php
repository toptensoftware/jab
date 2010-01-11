<?php 
$view['masterview']="master_email";
jabRequire("markdown");
jabEnterMarkdown(true);
echo $model['message'];
jabLeaveMarkdown();
?>
