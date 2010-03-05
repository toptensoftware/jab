<?php

global $jab;

// Includes
require_once("../jab/jab.php");
jabRequire("auth_single;contact;blog;editor;querystring;recaptcha");

// Check for ?login query string command
if (jabUserName()==null && isset($_REQUEST['login']))
{
	$refpage=jabQueryStringRemove($_SERVER['REQUEST_URI'], 'login');
	jabRedirect("/account/login?referrer=".urlencode($refpage));
}

// Check for ?phpinfo query string command
if (isset($_REQUEST['phpinfo']))
{
	phpinfo();
	die;
}

/*
// Insert your recaptcha keys here
// Configure recapture keys
jabInitRecaptcha(
	"xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
	"xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
	);
*/

// Select the theme
jabSetThemeFolder("theme");

// Setup theme related variables
$jab['siteName']="My Jab Site";
$jab['siteCopyright']="Copright © ".$jab['siteName'];

// Insert your Google tracked id here
// $jab['googlePageTrackerID']="XX-NNNNNNN-N";

// Setup Blog
$blog['routePrefix']='blog';
$blog['title']="My Jab Blog";
$blog['copyright']="Copyright © 2010 My Jab Site. All Rights Reserved.";
$blog['description']="My Jab Site";
$blog['pdo_dsn']="sqlite:".dirname(__FILE__)."/content/db.sqlite";
$blog['uploadfolder']=dirname(__FILE__)."/upload";
$blog['maxuploadfiles']=4;
$blog['maxuploadfilesize']=1000000;
$blog['tablePrefix']="";
$blog['feedArticles']=10;
$blog['indexArticles']=5;
$blog['managingEditor']="Your Name";
$blog['enableComments']=true;
$blog['notifyOnComment']=true;
$blog['notifyEmailTo']="contact@".$_SERVER['HTTP_HOST'];
$blog['notifyEmailFrom']="contact@".$_SERVER['HTTP_HOST'];
jabRouteBlog($blog);

// Authentication
$auth['routePrefix']="account";
$auth['username']='jab';
$auth['password']=md5("jab");
$auth['rights']="*";
jabRouteAuth($auth);

// Contact form
$contact['routePrefix']="contact";
$contact['emailTo']="contact@".$_SERVER['HTTP_HOST'];
$contact['emailSubject']="Contact ".$jab['siteName']." (Web Posted)";
$contact['contactDetails']=<<<DETAILS
Your contact details go here
DETAILS;
jabRouteContact($contact);
	
// Editor
$editor['routePrefix']="editor";
$editor['maxuploadfiles']=2;
$editor['maxuploadfilesize']=1000000;
jabRouteEditor($editor);

// Static content
jabRouteStaticContent("", dirname(__FILE__)."/content/");

// Handle the request
jabProcessRoutes();


?>