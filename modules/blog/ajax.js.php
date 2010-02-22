<script type="text/javascript">
//<!--
function ajaxCreate()
{
	var xmlHttp;
	try
	{
		// Firefox, Opera 8.0+, Safari
		xmlHttp=new XMLHttpRequest();
	}
	catch (e)
	{
		// Internet Explorer
		try
		{
			xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e)
		{
			try
			{
				xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e)
			{
				alert("Your browser does not support AJAX!");
				return false;
			}
		}
	}
	return xmlHttp;
}

var ajax;

function ajaxGetAndReload(url, message)
{
	ajax=ajaxCreate();
	ajax.open("GET", url, true);
	ajax.onreadystatechange=function()
	{
		if (ajax.readyState==4)
		{
			if (message!=null)
				alert(message);
			
			window.location.reload()
		}
	}
	ajax.send("");
}
//-->
</script>
