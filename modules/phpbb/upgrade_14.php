<?php
/***************************************************************************
                          upgrade_14.php  -  description
                             -------------------
    begin                : Sat June 17 2000
    copyright            : (C) 2001 The phpBB Group
 	 email                : support@phpbb.com

    $Id$

 ***************************************************************************/

/***************************************************************************
 *                                         				                                
 *   This program is free software; you can redistribute it and/or modify  	
 *   it under the terms of the GNU General Public License as published by  
 *   the Free Software Foundation; either version 2 of the License, or	    	
 *   (at your option) any later version.
 *
 ***************************************************************************/

include('extention.inc');
include('config.'.$phpEx);
include('functions.'.$phpEx);
include('auth.'.$phpEx);

set_time_limit(0);

if(!$startup)
{
	echo "Thank you for choosing to upgrade to phpBB v1.4.<br>";
	echo "The database changes that have been made for phpBB v1.4 are quite numerous, before starting this upgrade PLEASE make sure";
	echo "you have backed up your database!<br>";
	echo "This upgrade script will take some time if you have a very large database, please <b>do not</b> stop this script at any time until it has finished<br>";
	echo "<br>If you are ready to proceed click <a href=\"$PHP_SELF?startup=1\">HERE</a>";
	die();
}

$sql = "alter table forums add forum_last_post_id int(10) NOT NULL DEFAULT '0'";
if(!mysql_query($sql, $db))
{
	die("Could not update forums table.");
}

$sql = "alter table forums add forum_posts int(10) NOT NULL DEFAULT '0'";
if(!mysql_query($sql, $db))
{
	die("Could not update forums table.");
}

$sql = "alter table forums add forum_topics int(10) NOT NULL DEFAULT '0'";
if(!mysql_query($sql, $db))
{
	die("Could not update forums table.");
}
echo "Forums table altered successfully<br>";
flush();

$sql = "create table posts_text (post_id int(10) NOT NULL, post_text text, PRIMARY KEY(post_id))";
if(mysql_query($sql, $db))
{
	echo "posts_text table created. Inserting data, this may take a while<br>";
	flush();
	$sql = "insert into posts_text (post_id, post_text) select post_id, post_text from posts";
	if(!mysql_query($sql, $db))
	{
		die("Could not fill posts_text table!<br>");
	}
	echo "posts_text table filled successfully, altering posts table, this make take a while<br>";
	flush();
	$sql = "alter table posts drop post_text";
	if(!mysql_query($sql, $db))
	{
		die("Could not alter posts table!<br>Reason: ".mysql_error()."<br>");
	}
	echo "Posts table altered successfully<br>";
	flush();
}
else
{
	die("Could not create posts_text table!");
}

$sql = "Alter table topics add topic_replies int(10) NOT NULL";
if(!mysql_query($sql, $db))
{
	die("Could not update topics table.");
}

$sql = "alter table topics add topic_last_post_id int(10) NOT NULL";
if(!mysql_query($sql, $db))
{
	die("Could not update topics table.");
}

echo "Topics table altered sucessfully<br>";
flush();

echo "<br>Now setting up new forums and topics tables, this may take a while go get a sandwitch<br>";
flush();
$sql = "SELECT forum_id FROM forums";
if($r = mysql_query($sql, $db))
{
	while($row = mysql_fetch_array($r))
	{
		$sql = "SELECT count(post_id) AS t_posts FROM posts WHERE forum_id = '".$row["forum_id"]."'";
		if(!$s_res = mysql_query($sql, $db))
		{
			die("Error getting post data");
		}
		else
		{
			$sub_row = mysql_fetch_array($s_res);
			$posts = $sub_row["t_posts"];
		}
		$sql = "SELECT count(topic_id) AS t_topics FROM topics WHERE forum_id = '".$row["forum_id"]."'";
		if(!$s_res = mysql_query($sql, $db))
		{
			die("Error getting topic data");
		}
		else
		{
			$sub_row = mysql_fetch_array($s_res);
			$topics = $sub_row["t_topics"];
		}
		
		$sql = "SELECT max(post_id) AS last_post FROM posts WHERE forum_id = '".$row["forum_id"]."' LIMIT 1";
		if(!$s_res = mysql_query($sql, $db)){
			die("Error getting last_post data");
		}
		else
		{
			$sub_row = mysql_fetch_array($s_res);
			$last_post = $sub_row["last_post"];
		}
		
		$sql = "UPDATE forums SET forum_topics = '$topics', forum_posts = '$posts', forum_last_post_id = '$last_post'
			 WHERE forum_id = '".$row["forum_id"]."'";
		if(!$s_res = mysql_query($sql, $db))
		{
			die("Error updating Forums table");
		}
		echo "Updated forum #".$row["forum_id"]."<br>";
		flush();
	}
   echo "Done Updating Forums<br>";
}
else
{
   echo "Could not get forum data";
}

$sql = "SELECT topic_id FROM topics";
if($r = mysql_query($sql, $db))
{
	while($row = mysql_fetch_array($r))
	{
		$no_update = FALSE;
		
		$sql = "SELECT count(post_id) AS t_posts FROM posts WHERE topic_id = ".$row["topic_id"];
		if(!$s_res = mysql_query($sql, $db))
			die("Error getting post data");
		else
		{
			$sub_row = mysql_fetch_array($s_res);
			$replies = $sub_row["t_posts"] -1; // Replies = posts minus the original post
			if ($replies < 0)
			{
				$replies = 0;  // Shouldn't be possible but we'll check for it anyway.
				echo " <b>WARNING!! Invalid topic $row[topic_id]. Less then 0 replies??</b><br>\n";
			}
		}
		
		$sql = "SELECT max(post_id) AS last_post FROM posts WHERE topic_id = ".$row["topic_id"];
		if(!$s_res = mysql_query($sql, $db))
			die("Error getting last_post data");
		else
		{
			$sub_row = mysql_fetch_array($s_res);
			$last_post = $sub_row["last_post"];
			if($last_post == '' || $last_post < 0)
			{
				$last_post = 1;  // Shouldn't be possible but we'll check for it anyway.
				echo " <b>WARNING!! Invalid topic $row[topic_id]. No last post?</b><br>\n";
			}
		}
		
		$sql = "UPDATE topics SET topic_replies = ".$replies.", topic_last_post_id = $last_post WHERE topic_id = ".$row["topic_id"];
		if(!$s_res = mysql_query($sql, $db))
			die("Error updating Topics table on topic: ".$row["topic_id"]."<br>$sql");
		echo "Updated Topic #".$row["topic_id"]."<br>";
		flush();
	}
	echo "Done updating topics<br>";
	echo "Done upgrading";
}
else
{
	echo "Could not get topic data<br>";
}

?>
	     
