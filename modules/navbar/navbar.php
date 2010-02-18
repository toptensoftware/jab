<?php
/////////////////////////////////////////////////////////////////////////////
// navbar.php - support for simple navigation bar

class NavItem
{
	var $ItemText;
	var $Url;
	var $ItemClass;
	var $SubItems;
	
	function IsSelected()
	{
		global $jab;
		return isset($jab['nav_sel_path']) && in_array($this, $jab['nav_sel_path']);
	}
	
	function CssClassString()
	{
		$class=$this->ItemClass;
		if ($this->IsSelected())
			$class.=" selected";
		if (strlen($class)>0)
			$class=" class=\"".$class."\"";
		return $class;
	}
};

// Register a navigation item
//  $itemtext can be heirarchial  eg: "Tour/Videoes"
//  $itemclass is optional and will be applied to the <li> item for that menu item
function jabRegisterNav($itemtext, $url, $itemclass=null)
{
	global $jab;
	
	// Start with the root item
	if (!isset($jab['nav_root_item']))
		$jab['nav_root_item']=new NavItem();
	
	// Setup the navigation item and parent
	$parent=$jab['nav_root_item'];
	$item=null;

	// Split item path
	$itempath=explode("/", $itemtext);
	
	for ($i=0; $i<sizeof($itempath); $i++)
	{
		// Does this item already exist in parent?
		if (isset($parent->SubItems[$itempath[$i]]))
		{
			// Yes, just use it
			$item=$parent->SubItems[$itempath[$i]];
		}
		else
		{
			// No, create a new item and add to the parent
			$item=new NavItem();
			$item->ItemText=$itempath[$i];
			$parent->SubItems[$itempath[$i]]=$item;
		}
		
		$parent=$item;
	}
	
	// Setup other attributes on the item
	$item->Url=$url;
	$item->ItemClass=$itemclass;
}

// Render a single level or entire heirarchy of a nav item
function render_nav_items($item=null, $childitems=false)
{
	// Quit if no sub items
	if ($item->SubItems==null)
		return;
		
	echo "<ul>\n";
	
	foreach ($item->SubItems as $subitem)
	{
		// Work out class
		$class=$subitem->ItemClass;
		if ($subitem->IsSelected())
			$class.=" selected";
		if (strlen($class)>0)
			$class=" class=\"".$class."\"";
			
		// List item
		echo "<li".$class.">";
		
		// Link
		if (strlen($subitem->Url)>0)
			echo "<a href=\"".$subitem->Url."\">";
			
		// Text	
		echo htmlspecialchars($subitem->ItemText);
		
		if (strlen($subitem->Url)>0)
			echo "</a>";
		
		if ($childitems)
			render_nav_items($subitem);

		echo "</li>\n";
	}
	
	echo "</ul>\n";
}

// Select a navigation path by item text (eg: "Tour/Screenshots")
function jabSelectNavByItemText($NavPath)
{
	global $jab;
	
	// Setup the navigation item and parent
	$parent=$jab['nav_root_item'];

	// Clear current selection path
	$jab['nav_sel_path']=array($parent);
	
	// Split item path
	$itempath=explode("/", $NavPath);
	foreach ($itempath as $subitemtext)
	{
		// Find item, add to the path and descend into child item
		if (isset($parent->SubItems[$subitemtext]))
		{
			$subitem=$parent->SubItems[$subitemtext];
			
			$jab['nav_sel_path'][]=$subitem;
			
			$parent=$subitem;
		}
		else
		{
			// Unknown selection path, quit
			$jab['nav_sel_path']=null;
			return;
		}
	}
}

function does_path_match_prefix($pathPrefix, $path)
{
	$prefLen=strlen($pathPrefix);
	$pathLen=strlen($path);

	// Must be long enough
	if ($pathLen<$prefLen)
		return false;
	
	// Prefix must match
	if (substr($path, 0, $prefLen)!=$pathPrefix)
		return false;
		
	// If the path is longer than the prefix, check for a slash between
	if ($pathLen>$prefLen)
		return $path[strlen($pathPrefix)]=="/" || $pathPrefix[$prefLen-1]=="/";
	
	// Matches
	return true;
}

function select_nav_by_path($item, $path)
{
	// Recurse all child items looking for the best (deepest) match
	$best_match=null;
	if ($item->SubItems!=null)
	{
		foreach ($item->SubItems as $subitem)
		{
			$matching_path=select_nav_by_path($subitem, $path);
			if ($matching_path!==null && sizeof($matching_path)+1>sizeof($best_match))
			{
				// Prefix ourself
				$best_match=$matching_path;
			}
		}
	}
	
	// If we found a match, use it
	if ($best_match!==null)
	{
		array_unshift($best_match, $item);
		return $best_match;
	}

	if (does_path_match_prefix($item->Url, $path))
	{
		return array($item);
	}
		
	return null;
}

function jabSelectNavByPath($path=null)
{
	global $jab;
	
	// Use the requested url if not specified
	if ($path==null)
		$path=$_SERVER['REQUEST_URI_CLEAN'];
		
	// Quit if no menu configured
	if (!isset($jab['nav_root_item']))
		return;
		
	// Do a depth first search of the tree for a url that matches $path
	$jab['nav_sel_path']=select_nav_by_path($jab['nav_root_item'], $path);
}

// Render a particular level in the navigation menu
function jabRenderNav($level=null)
{
	global $jab;
	
	if (!isset($jab['nav_root_item']))
		return;
	
	if (!isset($jab['nav_sel_path']))
		$jab['nav_sel_path'][]=$jab['nav_root_item'];

	if ($level===null)
	{
		// Render entire heirarchy
		render_nav_items($jab['nav_root_item'], true);
	}
	else
	{
		if ($level<sizeof($jab['nav_sel_path']))
		{
			// Optimization for first level, just render it
			render_nav_items($jab['nav_sel_path'][$level], false);
		}
	}
}

// Check if can render a particular menu level
function jabGetNavItemForLevel($level)
{
	global $jab;
	if (!isset($jab['nav_root_item']))
		return false;

	if (!isset($jab['nav_sel_path']))
		return $level===0;
		
	if ($level<sizeof($jab['nav_sel_path']))
	{
		return $jab['nav_sel_path'][$level];
	}
	else
	{
		return null;
	}
}

