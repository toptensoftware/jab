<?php

// Set HTML MIME type
$model['headers']="MIME-Version: 1.0\nContent-type: text/html; charset=iso-8859-1\n";

// If rendering to view, turn off template
if ($renderContext=="page")
	$view['masterview']="none";

// Message content below...
?>
<html>
<head>
<style type="text/css">
body
{
	font-size: 11pt;
	font-family: 'Segoe UI', 'Calibri', 'Sans-Serif';
}
h1.heading
{
	font-size: 18pt;
	color: #365f91;
	margin:0;
}
h2
{
	font-size: 13pt;
	color: #4f81bd;
	margin:0;
	margin-top:12pt;
}
p.normal
{
	margin:0;
	margin-top:6pt;
}
a:link, a:visited, a:active
{
	color: #d76400;
}
a:hover
{
	color: Orange;
}
table
{
	border: none;
	border-collapse: collapse;
}
table td
{
	text-align: left;
	border: none;
	vertical-align: top;
	padding:5px;
}
p.startpreview
{
	font-size: 8pt;
	text-align: center;
	color: #000080;
	padding:0;
	margin-top: 12px;
	border-width: 1px;
	border-color: #000080;
	border-bottom-style: dotted;
}
p.endpreview
{
	font-size: 8pt;
	text-align: center;
	color:#000080;
	padding:0;
	margin-bottom:12px;
	border-width: 1px;
	border-color: #000080;
	border-top-style: dotted;
}

</style>
</head>
<body>
<?php if ($renderContext=='page'): ?>
<p>
To: <?php echo htmlspecialchars($model['to'])?><br />
Subject: <?php echo htmlspecialchars($model['subject'])?><br />
From: <?php echo htmlspecialchars($model['from'])?></p>
<hr />
<?php endif; ?>

<?php echo $view['content'] ?>

</body>
</html>