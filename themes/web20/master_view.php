<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php
// Work out title by extracting from start of document
if (strlen($view['title'])==0 && preg_match("/^[ \t\r\n]*<h1>(.*)<\/h1>(.*)$/isU", $view['content'], $matches, PREG_OFFSET_CAPTURE)==1)
{
	$view['title']=$matches[1][0];
	$view['content']=$matches[2][0];
}
if (strlen($view['subtitle'])==0 && preg_match("/^[ \t\r\n]*<h2>(.*)<\/h2>(.*)$/isU", $view['content'], $matches, PREG_OFFSET_CAPTURE)==1)
{
	$view['subtitle']=$matches[1][0];
	$view['content']=$matches[2][0];
}
?>
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <meta name="keywords" content="<?php echo $view['keywords'] ?>"/>
<?php if (isset($view['title']) && $view['title']!=""): ?>
    <title><?php echo $view['title'] ?> - <?php echo $jab['siteName']?></title>
<?php else: ?>
    <title><?php echo $jab['siteName']?></title>
<?php endif ; ?>
    <meta http-equiv="Content-Type" content="text/html;charset=windows-1252" />
    <link rel="stylesheet" type="text/css" href="/theme/styles.css"/>
	<link rel="shortcut icon" type="image/x-icon" href="http://<?php echo $_SERVER['HTTP_HOST']?>/theme/favicon.ico"/>
	<link rel="icon" type="image/x-icon" href="http://<?php echo $_SERVER['HTTP_HOST']?>/theme/favicon.ico"/>
<?php echo $view['additional_head_tags'] ?>
</head>
<body>
<div class="jab_menubar">
<div class="jab_menubarcontent">
<p>
<a href="/">Home</a>&nbsp;&nbsp;&nbsp;
<?php if (is_array($model) && $model['sourceFile']) { jabEditLink("Edit Page", $model['sourceFile']); echo "&nbsp;&nbsp;&nbsp";} ?>
<?php if (jabUserName()!=null):?>
<span style="float:right"><a href="/account/logout?referrer=<?php echo htmlspecialchars($_SERVER["REDIRECT_URL"])?>">Logout <?php echo htmlspecialchars(jabUserName())?></a></span>
<?php endif; ?>
</p>
</div>
</div>
<div class="jab_heading">
<div class="jab_heading_content">
<h1><?php echo htmlspecialchars($view['title']); ?></h1>
<h2><?php echo htmlspecialchars($view['subtitle']); ?></h2>
</div>
</div>
<div class="jab_container">
<div class="jab_content">

<?php echo $view['content'] ?>

</div>	<!-- content -->
</div>	<!-- container -->

<div class="footer">
<div class="footer_content">
<p>
<span style="float:right"><?php echo $jab['siteCopyright']?></span>
</p>

</div>	<!-- footer_content -->
</div>	<!-- footer -->
<?php if (isset($jab['googlePageTrackerID'])): ?>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("<?php echo $jab['googlePageTrackerID']?>");
pageTracker._trackPageview();
} catch(err) {}</script>
<?php endif ?>
</body>
</html>

