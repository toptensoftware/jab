<?php 
/////////////////////////////////////////////////////////////////////////////
// blog_db.php

// Get settings
$blog['pdo']=new PDO($blog['pdo_dsn'], $blog['pdo_username'], $blog['pdo_password'], $blog['pdo_driveroptions']);
$blog['pdo']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function init_blog_db()
{
	// Read schema version
	global $blog;

	
	$schemaVersion=0;
	try
	{
		$info=$blog['pdo']->query("SELECT * FROM {$blog['tablePrefix']}Info WHERE Name='SchemaVersion'");
		$row=$info->fetch();
		$schemaVersion=(int)$row['Value'];
	}
	catch (PDOException $x)
	{
		// Must be new DB
	}
	
	$blog['pdo']->beginTransaction();

	if ($schemaVersion==0)
	{
		$blog['pdo']->exec(<<<SQL
					CREATE TABLE {$blog['tablePrefix']}Info(Name, Value);

					INSERT INTO {$blog['tablePrefix']}Info(Name, Value) 
					VALUES ('SchemaVersion', 2);

					CREATE TABLE {$blog['tablePrefix']}Articles(
						ID INTEGER PRIMARY KEY AUTOINCREMENT, 
						Title TEXT, 
						Timestamp INTEGER, 
						Content TEXT, 
						RateTotal INTEGER, 
						RateCount INTEGER,
						Draft BOOLEAN DEFAULT(0)
						);

					CREATE TABLE {$blog['tablePrefix']}Comments(
						ID INTEGER PRIMARY KEY AUTOINCREMENT, 
						IDArticle INTEGER, 
						Name TEXT, 
						Email TEXT, 
						Website Text,
						Timestamp INTEGER, 
						Content TEXT, 
						PendingReview BOOLEAN,
						ByAuthor BOOLEAN DEFAULT(0)
						);

					CREATE INDEX {$blog['tablePrefix']}ArticlesIdx 
						ON {$blog['tablePrefix']}Articles(Timestamp);

					CREATE INDEX {$blog['tablePrefix']}CommentsIdx 
						ON {$blog['tablePrefix']}Comments(IDArticle);

					CREATE INDEX {$blog['tablePrefix']}CommentsIdx2 
						ON {$blog['tablePrefix']}Comments(Timestamp);
SQL
);
	}

	if ($schemaVersion<2)
	{
		$blog['pdo']->exec(<<<SQL

			UPDATE {$blog['tablePrefix']}Info SET Value=2 WHERE Name='SchemaVersion'; 

			ALTER TABLE {$blog['tablePrefix']}Articles 
				ADD COLUMN Draft BOOLEAN DEFAULT(0);
SQL
);
	}

	if ($schemaVersion<3)
	{
		$blog['pdo']->exec(<<<SQL

			UPDATE {$blog['tablePrefix']}Info SET Value=3 WHERE Name='SchemaVersion'; 
			
			CREATE INDEX {$blog['tablePrefix']}ArticlesIdx 
				ON {$blog['tablePrefix']}Articles(Timestamp);
SQL
);
	}

	
	$blog['pdo']->commit();

}

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
	var $Draft;
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
			$this->Draft=(boolean)$row['Draft'];
		}
		else
		{
			$this->RatingTotal=0;
			$this->RatingCount=0;
			$this->TimeStamp=0;
			$this->Draft=true;
		}
	}
	
	function InitFromForm($checkErrors, &$errors)
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
					

		$this->Title=jabRequestParam("Title");
		$this->TimeStamp=jabRequestParam("TimeStamp")=="" ? 0 : strtotime(jabRequestParam("TimeStamp"));
		$this->Content=jabRequestParam("Content").$uploadAppend;
		
		if (!$checkErrors)
		{
			if (strlen($this->Title)==0)
			{
				$errors[]="Please specify a title";
			}
				
			if (strlen($this->Content)==0)
			{
				$errors[]="No article content";
			}
				
			if ($this->TimeStamp==null)
			{
				$errors[]="Invalid date/time";
				$this->TimeStamp=time();
			}
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
	
	function Guid()
	{
		return blog_link("/guid/".$this->ID);
	}
	
	function Save()
	{
		global $blog;
		
		// Use default time
		if ($this->TimeStamp==0 && !$this->Draft)
			$this->TimeStamp=time();
			
		// Setup a title
		if (strlen($this->Title)==0)
		{
			$this->Title="Untitled - ".date("j F, Y");
		}
		
		if (strlen($this->ID)==0)
		{
			// New article
			$stmt=$blog['pdo']->prepare("INSERT INTO {$blog['tablePrefix']}Articles(Title, Timestamp, Content, Draft) VALUES (:title, :timestamp, :content, :draft)");
			$stmt->bindValue(":title", $this->Title);
			$stmt->bindValue(":timestamp", $this->TimeStamp);
			$stmt->bindValue(":content", $this->Content);
			$stmt->bindValue(":draft", $this->Draft);
			$stmt->execute();
			$this->ID=$blog['pdo']->lastInsertId();
		}
		else
		{
			$blog['pdo']->beginTransaction();
			
			// Existing article
			$stmt=$blog['pdo']->prepare("UPDATE {$blog['tablePrefix']}Articles SET Title=:title, Timestamp=:timestamp, Content=:content, Draft=:draft WHERE ID=:idArticle");
			$stmt->bindValue(":title", $this->Title);
			$stmt->bindValue(":timestamp", $this->TimeStamp);
			$stmt->bindValue(":content", $this->Content);
			$stmt->bindValue(":draft", $this->Draft);
			$stmt->bindValue(":idArticle", $this->ID);
			$stmt->execute();
			
			// Commit all changes
			$blog['pdo']->commit();
			
		}
		
		return true;
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
	var $ByAuthor;
	
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
			$this->ByAuthor=(boolean)$row['ByAuthor'];
		}
		else
		{
			$this->PendingReview=true;
			$this->ByAuthor=false;
			$this->TimeStamp=time();
		}
	}

	function Format()
	{
		jabRequire("markdown");
		return jabMarkdown($this->Content, !$this->ByAuthor);
	}
	
	function FormatNameLink()
	{
		$ret="";
		if (strlen($this->Website)>0)
		{
			$site=$this->Website;
			$site=str_replace("<", "", $site);
			if (substr($site, 0, 7)!="http://")
				$site="http://".$site;
			$ret="<a href=\"".htmlspecialchars($site)."\" title=\"".htmlspecialchars($this->Website)."\" rel=\"nofollow\" target=\"_blank\">".htmlspecialchars($this->Name)."</a>";
		}
		else
		{
			$ret=htmlspecialchars($this->Name);
		}
		
		if (jabCanUser("author") && strlen($this->Email)>0)
		{
			$ret.=" (<a href=\"mailto:".htmlspecialchars($this->Email)."\">".htmlspecialchars($this->Email)."</a>)";
		}
		
		return $ret;
	}

	function InitFromForm(&$errors)
	{
		$this->Name=jabRequestParam("Name");
		$this->Email=jabRequestParam("Email");
		$this->Website=jabRequestParam("Website");
		$this->Content=jabRequestParam("Content");
		$this->ByAuthor=false;
		
		global $blog;
		if (jabCanUser("author"))
		{
			$this->ByAuthor=true;			
			$this->Name=$blog['managingEditor'];
			$this->Email=$blog['notifyEmailFrom'];
			if (isset($blog['authorSite']))
				$this->Website=$blog['authorSite'];
			else
				$this->Website="http://".$_SERVER['HTTP_HOST'];
		}
		
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
		$stmt=$blog['pdo']->prepare("INSERT INTO {$blog['tablePrefix']}Comments(IDArticle, Name, Email, Website, Content, PendingReview, ByAuthor, TimeStamp) VALUES (:idarticle, :name, :email, :website, :content, :pendingreview, :byauthor, :timestamp)");
		$stmt->bindValue(":idarticle", $this->IDArticle);
		$stmt->bindValue(":name", $this->Name);
		$stmt->bindValue(":email", $this->Email);
		$stmt->bindValue(":website", $this->Website);
		$stmt->bindValue(":content", $this->Content);
		$stmt->bindValue(":pendingreview", $this->PendingReview);
		$stmt->bindValue(":byauthor", $this->ByAuthor);
		$stmt->bindValue(":timestamp", $this->TimeStamp);
		$stmt->execute();
		$this->ID=$blog['pdo']->lastInsertId();
	}
};

// Load a single blog article
function blog_load_article($id, $drafts=false)
{
	$cond=$drafts ? "" : " AND Draft<>1";

	global $blog;
	$stmt=$blog['pdo']->prepare("SELECT * FROM {$blog['tablePrefix']}Articles WHERE ID=:idArticle {$cond};");
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
function blog_load_articles($page, $pagesize, $drafts=false)
{
	if ($drafts==="all")
		$cond="";
	else
		$cond=$drafts ? "WHERE Draft=1" : " WHERE Draft<>1";
		
	$order="Timestamp DESC";

	global $blog;
	$stmt=$blog['pdo']->prepare("SELECT * FROM {$blog['tablePrefix']}Articles {$cond} ORDER BY {$order} LIMIT :limit OFFSET :offset");
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
	
	$article=blog_load_article($id, true);
	
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
			$stmt=$blog['pdo']->prepare("INSERT INTO {$blog['tablePrefix']}Articles(ID, Title, Timestamp, Content, RateTotal, RateCount, Draft) 
										 VALUES (:id, :title, :timestamp, :content, :rateTotal, :rateCount, :draft)");
			$stmt->bindValue(":id", intval($article->id));
			$stmt->bindValue(":title", $article->title);
			$stmt->bindValue(":timestamp", intval(strtotime($article->timestamp)));
			$stmt->bindValue(":content", $article->content);
			$stmt->bindValue(":rateTotal", intval($article->rateTotal));
			$stmt->bindValue(":rateCount", intval($article->rateCount));
			$stmt->bindValue(":draft", intval($article->draft));
			$stmt->execute();
			
			foreach ($article->comments->children() as $comment)
			{
				if ($comment->getName()=="comment")
				{
					try
					{
						$stmt=$blog['pdo']->prepare("INSERT INTO {$blog['tablePrefix']}Comments(ID, IDArticle, Name, Email, Website, Content, PendingReview, ByAuthor, TimeStamp) VALUES (:id, :idarticle, :name, :email, :website, :content, :pendingreview, :byauthor, :timestamp)");
						$stmt->bindValue(":id", intval($comment->id));
						$stmt->bindValue(":idarticle", intval($article->id));
						$stmt->bindValue(":name", $comment->name);
						$stmt->bindValue(":email", $comment->email);
						$stmt->bindValue(":website", $comment->website);
						$stmt->bindValue(":content", $comment->content);
						$stmt->bindValue(":pendingreview", intval($comment->pending));
						$stmt->bindValue(":byauthor", intval($comment->byauthor));
						$stmt->bindValue(":timestamp", intval(strtotime($comment->timestamp)));
						$stmt->execute();
					}
					catch (Exception $e)
					{
						throw new Exception("Error inserting comment ".intval($comment->id)." + ".intval($article->id));
					}
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
