<?php
/////////////////////////////////////////////////////////////////////////////
// sharethis.php - support for shareThis links

function jabInitShareThis($publisherid, $customButtonHtml=null)
{
	global $jab;
	$jab['shareThisPublisherID']=$publisherid;
	$jab['shareThisNextButtonID']=1;
	if ($customButtonHtml==null)
		$jab['shareThisButtonHtml']="Share This";
	else
		$jab['shareThisButtonHtml']=$customButtonHtml;
}

function jabRenderShareLink($title, $url, $summary="")
{
	global $jab;

	// Quit if not initialized
	if (!isset($jab['shareThisPublisherID']))
		return;

	// Make sure shareThis script loaded in <head>
	if ($jab['shareThisNextButtonID']==1)
	{
		$jab['additional_head_tags'].="    <script type=\"text/javascript\" src=\"http://w.sharethis.com/button/sharethis.js#publisher=".$jab['shareThisPublisherID']."&amp;type=website;button=false\"></script>\n";
	}

	// Work out ID of this button
	$id="shareThisButton".$jab['shareThisNextButtonID'];
	$jab['shareThisNextButtonID']++;

?>
	<span class="shareThisButton" id="<?php echo $id?>"><a href="javascript:void(0);"><?php echo $jab['shareThisButtonHtml']?></a></span>
	<script type="text/javascript">
		//<![CDATA[
		var thisobj = SHARETHIS.addEntry(
		{title:'<?php echo htmlspecialchars($title)?>',
		url:'<?php echo $url?>',
		summary:'<?php echo htmlspecialchars($summary) ?>'},
		{button:false});
		thisobj.attachButton(document.getElementById("<?php echo $id?>"));
		//]]>
	</script>
<?
}
