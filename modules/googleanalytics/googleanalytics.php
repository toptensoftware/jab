<?php
/////////////////////////////////////////////////////////////////////////////
// sharethis.php - support for shareThis links

function jabInitGoogleAnalytics($pageTrackerID)
{
	$script=<<<SCRIPT
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("{pageTrackerID}");
pageTracker._trackPageview();
} catch(err) {}</script>
SCRIPT;

	global $jab;
	$jab['trailing_body_script'].=str_replace("{pageTrackerID}", $pageTrackerID, $script);
}

