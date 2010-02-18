<?php 
/////////////////////////////////////////////////////////////////////////////
// blog_db.php

// Get settings
$blog['pdo']=new PDO($blog['pdo_dsn'], $blog['pdo_username'], $blog['pdo_password'], $blog['pdo_driveroptions']);

function init_blog_db()
{
	// Read schema version
	global $blog;
	$blog['pdo']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
	$info=$blog['pdo']->query("SELECT * FROM {$blog['tablePrefix']}Info WHERE Name='SchemaVersion'");
	$blog['pdo']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	if ($info===false)
	{
		$blog['pdo']->exec(<<<SQL
					CREATE TABLE {$blog['tablePrefix']}Info(Name, Value);
SQL
);

		$blog['pdo']->exec(<<<SQL
					INSERT INTO {$blog['tablePrefix']}Info(Name, Value) 
					VALUES ('SchemaVersion', 1);
SQL
);

		$blog['pdo']->exec(<<<SQL
					CREATE TABLE {$blog['tablePrefix']}Articles(
						ID INTEGER PRIMARY KEY AUTOINCREMENT, 
						Title TEXT, 
						Timestamp INTEGER, 
						Content TEXT, 
						RateTotal INTEGER, 
						RateCount INTEGER
						);
SQL
);

		$blog['pdo']->exec(<<<SQL
					CREATE TABLE {$blog['tablePrefix']}Comments(
						ID INTEGER PRIMARY KEY AUTOINCREMENT, 
						IDArticle INTEGER, 
						Name TEXT, 
						Email TEXT, 
						Website Text,
						Timestamp INTEGER, 
						Content TEXT, 
						PendingReview BOOLEAN
						);
SQL
);

		$blog['pdo']->exec(<<<SQL
					CREATE UNIQUE INDEX {$blog['tablePrefix']}ArticlesIdx 
						ON {$blog['tablePrefix']}Articles(Timestamp);
SQL
);

		$blog['pdo']->exec(<<<SQL
					CREATE INDEX {$blog['tablePrefix']}CommentsIdx 
						ON {$blog['tablePrefix']}Comments(IDArticle);
SQL
);

		$blog['pdo']->exec(<<<SQL
					CREATE INDEX {$blog['tablePrefix']}CommentsIdx2 
						ON {$blog['tablePrefix']}Comments(Timestamp);
SQL
);
	}
}

init_blog_db();

// Look for any links that don't contain a colon and don't start with a slash
// and prefix with $prefix
// Need to handle at <a href=""> and <img src="">
function fix_local_links($html, $prefix)
{

	return $html;
}


// BlogArticle - represents a single blog article
class BlogArticle
{
	var $ID;				// The article ID 
	var	$Title;				// Title of this article
	var $TimeStamp;			// Time stamp of this article
	var $Content;			// Main content of	the article
	var $RateTotal;
	var $RateCount;
	var $Comments;			// Array of comments, populated by BlogArticle->LoadComments()
	
	// Constructor
	function BlogArticle($row=null)
	{
		if ($row!=null)
		{
			$this->ID=$row['ID'];
			$this->Title=$row['Title'];
			$this->TimeStamp=intval($row['Timestamp']);
			$this->Content=$row['Content'];
			$this->RateTotal=intval($row['RateTotal']);
			$this->RateCount=intval($row['RateCount']);
		}
		else
		{
			$this->RatingTotal=0;
			$this->RatingCount=0;
			$this->TimeStamp=time();
		}
	}
	
	function InitFromForm(&$errors)
	{
		global $blog;
		
		// Handle uploaded files
		if (isset($blog['uploadfolder']))
		{
			for ($i=1; $i<=(isset($blog['maxuploadfiles']) ? $blog['maxuploadfiles'] : 4); $i++)
			{
				if (strlen($_FILES['file'.$i]['name'])==0)
					continue;
					
				$target_path=jabPathAppend($blog['uploadfolder'], basename( $_FILES['file'.$i]['name']));
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
								$uploadAppend.="\n\n<center>![$file]($file)</center>\n\n";
							}
							else
							{
								$uploadAppend.="\n\n[$file]($file)\n\n";
							}
						}
					}
				}
			}
		}
					

		$this->ID=jabRequestParam("ID");
		$this->Title=jabRequestParam("Title");
		$this->TimeStamp=strtotime(jabRequestParam("TimeStamp"));
		$this->Content=jabRequestParam("Content").$uploadAppend;
		
		if (strlen($this->Title)==0)
			$errors[]="Please specify a title";
		if (strlen($this->Content)==0)
			$errors[]="No article content";
		if ($this->TimeStamp==null)
		{
			$errors[]="Invalid date/time";
			$this->TimeStamp=time();
		}
			
		return sizeof($errors)==0;
	}
	
	function Format($bRss=false)
	{
		global $blog;
		jabRequire("markdown");
		$parser = jabCreateMarkdownParser(false);
		$parser->local_link_prefix="/".$blog['routePrefix']."/";
		if ($bRss)
			$parser->root_link_prefix="http://".$_SERVER['HTTP_HOST'];
		return $parser->transform($this->Content);
	}
	
	// Convert title to something-useable-in-a-url  	
	function UrlTitle()
	{
		$str="";
		for ($i=0; $i<strlen($this->Title); $i++)
		{
			$ch=ord($this->Title[$i]);

			if ($ch>=65 && $ch<=90)
				$str.=chr($ch+32);
			else if ($ch>=97 && $ch<=122)
				$str.=chr($ch);
			else if ($ch>=48 && $ch<=57)
				$str.=chr($ch);
			else if ($ch==32)
				$str.="-";
		}

		return $str;
		
	}
	
	function FullUrl()
	{
		return blog_link("/posts/".$this->ID."/".$this->UrlTitle());
	}
	
	function Save()
	{
		global $blog;
		if (strlen($this->ID)==0)
		{
			// New article
			$stmt=$blog['pdo']->prepare("INSERT INTO {$blog['tablePrefix']}Articles(Title, Timestamp, Content) VALUES (:title, :timestamp, :content)");
			$stmt->bindValue(":title", $this->Title);
			$stmt->bindValue(":timestamp", $this->TimeStamp);
			$stmt->bindValue(":content", $this->Content);
			$stmt->execute();
			$this->ID=$blog['pdo']->lastInsertId();
			return true;
		}
		else
		{
			// Existing article
			$stmt=$blog['pdo']->prepare("UPDATE {$blog['tablePrefix']}Articles SET Title=:title, Timestamp=:timestamp, Content=:content WHERE ID=:idArticle");
			$stmt->bindValue(":title", $this->Title);
			$stmt->bindValue(":timestamp", $this->TimeStamp);
			$stmt->bindValue(":content", $this->Content);
			$stmt->bindValue(":idArticle", $this->ID);
			$stmt->execute();
			return $stmt->rowCount()==1;
		}
	}
	
	// Load comments
	function LoadComments($bIncludePending)
	{
		global $blog;
		
		// All comments, or only those not pending review?
		$cond2="";
		if (!$bIncludePending)
			$cond2=" AND PendingReview<>1";
			
		// Request
		$stmt=$blog['pdo']->prepare("SELECT * FROM {$blog['tablePrefix']}Comments WHERE IDArticle=:idArticle".$cond2." ORDER BY Timestamp ASC;");
		$stmt->bindValue(":idArticle", $this->ID);
		$stmt->execute();
		
		// Create array
		$this->Comments=array();
		foreach ($stmt as $row)
		{
			$this->Comments[]=new BlogComment($row);
		}
	}
	
	function GetCommentCount($bIncludePending)
	{
		global $blog;

		// All comments, or only those not pending review?
		$cond2="";
		if (!$bIncludePending)
			$cond2=" AND PendingReview<>1";
			
		$stmt=$blog['pdo']->prepare("SELECT Count(ID) FROM {$blog['tablePrefix']}Comments WHERE IDArticle=:idArticle".$cond2.";");
		$stmt->bindValue(":idArticle", $this->ID);
		$stmt->execute();
		
		foreach ($stmt as $row)
		{
			return intval($row[0]);
		}
		
		return 0;
	}
};

// BlogComment - represents a single blog comment
class BlogComment
{
	var	$ID;
	var $IDArticle;
	var $TimeStamp;
	var	$Content;	
	var $Name;
	var $Email;
	var $Website;
	var $PendingReview;
	
	function BlogComment($row=null)
	{
		if ($row!=null)
		{
			$this->ID=$row['ID'];
			$this->IDArticle=$row['IDArticle'];
			$this->Name=$row['Name'];
			$this->Email=$row['Email'];
			$this->Website=$row['Website'];
			$this->Content=$row['Content'];	
			$this->TimeStamp=intval($row['Timestamp']);
			$this->PendingReview=(boolean)$row['PendingReview'];
		}
		else
		{
			$this->PendingReview=true;
			$this->TimeStamp=time();
		}
	}

	function Format()
	{
		jabRequire("markdown");
		return jabMarkdown($this->Content, true);
	}
	
	function FormatNameLink()
	{
		if (strlen($this->Website)>0)
		{
			$site=$this->Website;
			$site=str_replace("<", "", $site);
			if (substr($site, 0, 6)!="http://")
				$site="http://".$site;
			return "<a href=\"".htmlspecialchars($site)."\" title=\"".htmlspecialchars($this->Website)."\" rel=\"nofollow\" target=\"_blank\">".htmlspecialchars($this->Name)."</a>";
		}
		else
		{
			return htmlspecialchars($this->Name);
		}
	}

	function InitFromForm(&$errors)
	{
		$this->Name=jabRequestParam("Name");
		$this->Email=jabRequestParam("Email");
		$this->Website=jabRequestParam("Website");
		$this->Content=jabRequestParam("Content");
		
		if (strlen($this->Name)==0)
			$errors[]="Please enter your name";
		if (strlen($this->Email)!=0 && !jabIsValidEmail($this->Email))
			$errors[]="Email address is not valid";
		if (strlen($this->Content)==0)
			$errors[]="No article content";
			
		return sizeof($errors)==0;
	}

	function Save()
	{
		global $blog;
		$stmt=$blog['pdo']->prepare("INSERT INTO {$blog['tablePrefix']}Comments(IDArticle, Name, Email, Website, Content, PendingReview, TimeStamp) VALUES (:idarticle, :name, :email, :website, :content, :pendingreview, :timestamp)");
		$stmt->bindValue(":idarticle", $this->IDArticle);
		$stmt->bindValue(":name", $this->Name);
		$stmt->bindValue(":email", $this->Email);
		$stmt->bindValue(":website", $this->Website);
		$stmt->bindValue(":content", $this->Content);
		$stmt->bindValue(":pendingreview", $this->PendingReview);
		$stmt->bindValue(":timestamp", $this->TimeStamp);
		$stmt->execute();
		$this->ID=$blog['pdo']->lastInsertId();
	}
};

// Load a single blog article
function blog_load_article($id)
{
	global $blog;
	$stmt=$blog['pdo']->prepare("SELECT * FROM {$blog['tablePrefix']}Articles WHERE ID=:idArticle;");
	$stmt->bindValue(":idArticle", $id);
	$stmt->execute();
	
	// Fetch
	$row=$stmt->fetch();
	if ($row===false)
		return null;
		
	// Create article
	return new BlogArticle($row);
}

// Load a page of articles
function blog_load_articles($page, $pagesize)
{
	global $blog;
	$stmt=$blog['pdo']->prepare("SELECT * FROM {$blog['tablePrefix']}Articles ORDER BY Timestamp DESC LIMIT :limit OFFSET :offset");
	$stmt->bindValue(":offset", $pagesize*$page);
	$stmt->bindValue(":limit", $pagesize);
	$stmt->execute();

	$articles=array();
	foreach ($stmt as $row)
	{
		$articles[]=new BlogArticle($row);
	}			
	return $articles;
}

// Delete a blog article
function blog_delete_article($id)
{
	global $blog;
	
	$blog['pdo']->beginTransaction();
	
	$stmt=$blog['pdo']->prepare("DELETE FROM {$blog['tablePrefix']}Articles WHERE ID=:idArticle");
	$stmt->bindValue(":idArticle", $id);
	$stmt->execute();

	$stmt=$blog['pdo']->prepare("DELETE FROM {$blog['tablePrefix']}Comments WHERE IDArticle=:idArticle");
	$stmt->bindValue(":idArticle", $id);
	$stmt->execute();
	
	$blog['pdo']->commit();
}

// Delete a blog comment
function blog_delete_comment($id)
{
	global $blog;
	
	$stmt=$blog['pdo']->prepare("DELETE FROM {$blog['tablePrefix']}Comments WHERE ID=:id");
	$stmt->bindValue(":id", $id);
	$stmt->execute();
}

// Add a rating for an article
function blog_add_article_rating($idArticle, $rating, $votes=1)
{
	global $blog;
	$stmt=$blog['pdo']->prepare("UPDATE {$blog['tablePrefix']}Articles SET RateTotal=COALESCE(RateTotal,0)+:rating, RateCount=COALESCE(RateCount,0)+:votes WHERE ID=:idArticle");
	$stmt->bindValue(":idArticle", $idArticle);
	$stmt->bindValue(":rating", $rating);
	$stmt->bindValue(":votes", $votes);
	$stmt->execute();
}

// Mark a comment as accepted
function blog_accept_comment($id, $accept)
{
	global $blog;
	$stmt=$blog['pdo']->prepare("UPDATE {$blog['tablePrefix']}Comments SET PendingReview=:pending WHERE ID=:idComment");
	$stmt->bindValue(":idComment", $id);
	$stmt->bindValue(":pending", $accept ? 0 : 1);
	$stmt->execute();
}

function blog_link($url)
{
	global $blog;
	return "/".$blog['routePrefix'].$url;
}

// Import blog content from an XML file
function blog_import($file, $dropoldcontent)
{
	global $blog;
	
	if ($dropoldcontent)
	{
		$blog['pdo']->exec(<<<SQL
					DROP TABLE {$blog['tablePrefix']}Info;
SQL
);
		$blog['pdo']->exec(<<<SQL
					DROP TABLE {$blog['tablePrefix']}Articles;
SQL
);
		$blog['pdo']->exec(<<<SQL
					DROP TABLE {$blog['tablePrefix']}Comments;
SQL
);
	
		init_blog_db();
	}

	// Open the xml file
	$xml=simplexml_load_file($file);
	foreach ($xml->children() as $article)
	{
		if ($article->getName()=="item")
		{
			$stmt=$blog['pdo']->prepare("INSERT INTO {$blog['tablePrefix']}Articles(ID, Title, Timestamp, Content, RateTotal, RateCount) VALUES (:id, :title, :timestamp, :content, :rateTotal, :rateCount)");
			$stmt->bindValue(":id", intval($article->id));
			$stmt->bindValue(":title", $article->title);
			$stmt->bindValue(":timestamp", intval(strtotime($article->timestamp)));
			$stmt->bindValue(":content", $article->content);
			$stmt->bindValue(":rateTotal", intval($article->rateTotal));
			$stmt->bindValue(":rateCount", intval($article->rateCount));
			$stmt->execute();
			
			foreach ($article->comments->children() as $comment)
			{
				if ($comment->getName()=="comment")
				{
					$stmt=$blog['pdo']->prepare("INSERT INTO {$blog['tablePrefix']}Comments(ID, IDArticle, Name, Email, Website, Content, PendingReview, TimeStamp) VALUES (:id, :idarticle, :name, :email, :website, :content, :pendingreview, :timestamp)");
					$stmt->bindValue(":id", intval($comment->id));
					$stmt->bindValue(":idarticle", intval($article->id));
					$stmt->bindValue(":name", $comment->name);
					$stmt->bindValue(":email", $comment->email);
					$stmt->bindValue(":website", $comment->website);
					$stmt->bindValue(":content", $comment->content);
					$stmt->bindValue(":pendingreview", intval($comment->pending));
					$stmt->bindValue(":timestamp", intval(strtotime($comment->timestamp)));
					$stmt->execute();
				}
			}
		
		}
	}
}

function plural($num) {
	if ($num != 1)
		return "s";
}
 
function formatRelativeTime($date) {
	$diff = time() - $date;
	if ($diff<60)
		return $diff . " second" . plural($diff) . " ago";
	$diff = round($diff/60);
	if ($diff<60)
		return $diff . " minute" . plural($diff) . " ago";
	$diff = round($diff/60);
	if ($diff<24)
		return $diff . " hour" . plural($diff) . " ago";
	$diff = round($diff/24);
	if ($diff<7)
		return $diff . " day" . plural($diff) . " ago";
	$diff = round($diff/7);
	if ($diff<4)
		return $diff . " week" . plural($diff) . " ago";
	return "on " . date("F j, Y", $date);
}


?>