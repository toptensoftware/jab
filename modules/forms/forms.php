<?php 
/////////////////////////////////////////////////////////////////////////////
// forms.php

// Render a HTML text input with label
function jabHtmlInput($label, $id, $value, $class="", $name="")
{
	if ($name=="")
		$name=$id;
	if ($class!="")
		$class=" class=\"".$class."\"";
	$value=htmlspecialchars($value);
	echo "<label for=\"$name\">$label</label><input$class type=\"text\" id=\"$id\" name=\"$name\" value=\"$value\">\n";
}

// Render a HTML text input with label
function jabHtmlReadOnly($label, $id, $value, $class="", $name="")
{
	if ($name=="")
		$name=$id;
	if ($class!="")
		$class=" class=\"".$class."\"";
	$value=htmlspecialchars($value);
	echo "<label for=\"$name\">$label</label><input$class type=\"text\" id=\"$id\" name=\"$name\" value=\"$value\" DISABLED>\n";
}

// Render a HTML password input with label
function jabHtmlPassword($label, $id, $value, $class="", $name="")
{
	if ($name=="")
		$name=$id;
	if ($class!="")
		$class=" class=\"".$class."\"";
	$value=htmlspecialchars($value);
	echo "<label for=\"$name\">$label</label><input$class type=\"password\" id=\"$id\" name=\"$name\" value=\"$value\">\n";
}

// Render a HTML text area with label
function jabHtmlTextArea($label, $id, $value, $class="", $name="")
{
	if ($name=="")
		$name=$id;
	if ($class!="")
		$class=" class=\"".$class."\"";
	$value=htmlspecialchars($value);
	echo "<label for=\"$name\">$label</label><textarea$class id=\"$id\" name=\"$name\">$value</textarea>\n";
}

// Render a HTML submit button
function jabHtmlSubmitButton($caption, $id, $class="", $name="")
{
	if ($name=="")
		$name=$id;
	if ($class!="")
		$class=" class=\"".$class."\"";
	echo "<input type=\"submit\" id=\"$id\" name=\"$name\" value=\"$caption\"/>\n";
}

// Render a HTML submit button
function jabHtmlButton($caption, $id, $class="", $name="")
{
	if ($name=="")
		$name=$id;
	if ($class!="")
		$class=" class=\"".$class."\"";
	echo "<input type=\"button\" id=\"$id\" name=\"$name\" value=\"$caption\"/>\n";
}

// Render a HTML text input with label
function jabHtmlHidden($name, $value)
{
	$value=htmlspecialchars($value);
	echo "<input type=\"hidden\" id=\"$name\" name=\"$name\" value=\"$value\">\n";
}

function jabHtmlFileUpload($id, $name="")
{
	if ($name=="")
		$name=$id;
	
	echo "<input name=\"$name\" type=\"file\"/>\n";
}

function jabHtmlCheckBox($label, $id, $checked, $name="")
{
	if ($name=="")
		$name=$id;
	
	$checked = !!$checked ? " CHECKED" : "";
	echo "<label class=\"checkbox\" for=\"$name\"><input name=\"$name\" id=\"$id\" type=\"checkbox\"$checked/>$label</label>\n";
}


// Output an array of error strings in a <div class="Errors"> box
function jabHtmlErrors($errors, $message)
{
	if (sizeof($errors)==0)
		return;
	
	echo "<div class=\"Errors\">\n";
	echo "<p>$message</p>\n";
	echo "<ul>\n";
	
	foreach ($errors as $err)
	{
		echo "<li>".htmlspecialchars($err)."</li>\n";
	}
	echo "</ul>\n";
	echo "</div>\n";
}



?>