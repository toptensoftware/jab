<?php 
/////////////////////////////////////////////////////////////////////////////
// editor.php

global $editor;
$editor=$routeData;

// Recursively create a folder
function jabMkDir($path)
{
    $exp=explode("/",$path);
    $way='/';
    foreach($exp as $n)
    {
        if ($n=="")
            continue;
        $way=jabPathAppend($way, $n);
        if(!@is_dir($way))
        {
            if (@mkdir($way)!==true)
            {
            }
        }
    }
    
    return true;
} 


// Route all urls for a cms
function editor_get()
{
	if (!jabCanUser("edit"))
		return;

	global $editor;
	$model['editor']=$editor;
	$model['referrer']=jabRequestParam("referrer");
	$model['file']=jabRequestParam("file");
	
	// Handle no file specified
	if (strlen($model['file'])==0)
		return;
		
	// Handle attempt to escape the document root
	if (strstr($model['file'], ".."))
		return;

	// Work out full path of file to edit and load it
	$fullpath=jabPathAppend($_SERVER['DOCUMENT_ROOT'], $model['file']);
	try
	{
		// Make sure folder exists
		jabMkDir(dirname($fullpath));
		
		$fh = @fopen($fullpath, 'r');
		$model['content']= fread($fh, filesize($fullpath));
		fclose($fh);
	}
	catch (Exception $ex)
	{
		$model['content']="title=New File\nsubtitle=\nkeywords=\n---\n## New File ##\n\n";
	}

	// Render editor
	return jabRenderView("editor_view.php", $model);
}

function editor_post()
{
	if (!jabCanUser("edit"))
		return;

	global $editor;
	$model['editor']=$editor;
	$model['referrer']=jabRequestParam("referrer");
	if (strlen($model['referrer'])==0)
		$model['referrer']="/";
	$model['file']=str_replace("..", ".", jabRequestParam("file"));
	$model['content']=jabRequestParam("content");

	// Handle no file specified
	if (strlen($model['file'])==0)
		return;
		
	// Handle attempt to escape the document root
	if (strstr($model['file'], ".."))
		return;

	// Cancel?
	if (jabRequestParam("cancel"))
		return jabRedirect($model['referrer']);

	$fullpath=jabPathAppend($_SERVER['DOCUMENT_ROOT'], $model['file']);

	// Handle file uploads		
	for ($i=1; $i<=(isset($editor['maxuploadfiles']) ? $editor['maxuploadfiles'] : 4); $i++)
	{
		if (strlen($_FILES['file'.$i]['name'])==0)
			continue;
			
		$target_path=jabPathAppend(dirname($fullpath), basename( $_FILES['file'.$i]['name']));
		if (is_file($target_path) && jabRequestParam('overwrite')=="")
		{
			$errors[]="File ".$_FILES['file'.$i]['name']." would be overwriten";
		}
		else
		{
			if (!move_uploaded_file($_FILES['file'.$i]['tmp_name'], $target_path)) 
			{
				$errors[]="Failed to upload ".$_FILES['file'.$i]['name'];
			}
			else
			{
				if (jabRequestParam("addtoarticle")!="")
				{
					$file=$_FILES['file'.$i]['name'];
					$ext=strrpos($file, ".")===false ? null : substr($file, strrpos($file, ".")+1);
					if (in_array($ext, explode(";", "png;jpg;jpeg;tif;tiff;gif")))
					{
						$model['content'].="\n\n<center>![$file]($file)</center>\n\n";
					}
					else
					{
						$model['content'].="\n\n[$file]($file)\n\n";
					}
				}
			}
		}
	}


	
	if (jabRequestParam("delete"))
	{
		if (jabRequestParam("deleteconfirmed")=="yes")
		{
			try
			{
				unlink($fullpath);
				return jabRedirect($model['referrer']);
			}
			catch (Exception $ex)
			{
				$model['errors'][]="Failed to delete {$ex->getMessage()}.";
			}
		}
		else
		{
			$model['errors'][]="Press Delete again to really delete this file";
			$model['deleteconfirmed']="yes";
		}
		
		return jabRenderView("editor_view.php", $model);
	}

	if (jabRequestParam("save"))
	{
		try
		{
			$fh = @fopen($fullpath, 'w');
			fwrite($fh, $model['content']);
			fclose($fh);
			
			jabRedirect($model['referrer']);
		}
		catch (Exception $ex)
		{
			$model['errors'][]="Failed to save file - {$ex->getMessage()}.";
		}
	}

	return jabRenderView("editor_view.php", $model);
}
?>