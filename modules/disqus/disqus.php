<?php
/////////////////////////////////////////////////////////////////////////////
// disqus.php - support for disqus commenting

function jabInitDisqus($forumname)
{
	global $jab;
	$jab['disqusForumName']=$forumname;
	$jab['disqusScriptAdded']=false;
	
//	$jab['additional_head_tags']="<script type=\"text/javascript\">var disqus_developer = true;</script>";
}

function jabRenderDisqusLink($url)
{
	global $jab;
	if (!$jab['disqusScriptAdded'])
	{
		$jab['disqusScriptAdded']=true;

		$script=<<<SCRIPT
			<script type="text/javascript">
			//<![CDATA[
			(function() {
				var links = document.getElementsByTagName('a');
				var query = '?';
				for(var i = 0; i < links.length; i++) {
				if(links[i].href.indexOf('#disqus_thread') >= 0) {
					query += 'url' + i + '=' + encodeURIComponent(links[i].href) + '&';
				}
				}
				document.write('<script charset="utf-8" type="text/javascript" src="http://disqus.com/forums/{forumname}/get_num_replies.js' + query + '"></' + 'script>');
			})();
			//]]>
			</script>
SCRIPT;

//		$jab['additional_head_tags']="    <script type=\"text/javascript\" src=\"http://disqus.com/forums/".$jab['disqusForumName']."/embed.js\"></script>\n";
		$jab['trailing_body_script'].=str_replace("{forumname}", $jab['disqusForumName'], $script);
	}

	echo "<span class=\"disqusButton\"><a href=\"".$url."#disqus_thread\">Comments</a></span>\n";
}


function jabRenderDisqusEditor()
{
	global $jab;
?>
	<div id="disqus_thread"></div><script type="text/javascript" src="http://disqus.com/forums/<?php echo $jab['disqusForumName']?>/embed.js"></script><noscript><a href="http://disqus.com/forums/<?php echo $jab['disqusForumName']?>/?url=ref">View the discussion thread.</a></noscript><a href="http://disqus.com" class="dsq-brlink">blog comments powered by <span class="logo-disqus">Disqus</span></a>
<?php
}