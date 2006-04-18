<?  session_start();
include('../../config/config.php');
/***************************************************************************
                          search.php  -  description
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
include('functions.'.$phpEx);
include('config.'.$phpEx);
require('auth.'.$phpEx);
$pagetitle = $l_search;
$pagetype = "other";
include('page_header.'.$phpEx);

if(!$submit)
{
?>
<FORM NAME="Search" ACTION="<?php echo $PHP_SELF; ?>" METHOD="POST">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="<?php echo $tablewidth?>">
<TR>
	<TD  BGCOLOR="<?php echo $table_bgcolor?>">
	<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%" BGCOLOR="<?php echo $color1; ?>">
	<TR>
	<TD BGCOLOR="<?php echo $color1?>" WIDTH="50%" ALIGN="RIGHT">
		<font face="<?php echo $FontFace?>" size="<?php echo $FontSize2?>"><b><?php echo $l_searchterms?></b>:&nbsp;
	</TD>
	<TD BGCOLOR="<?php echo $color2?>" WIDTH="50%">
		<INPUT TYPE="text" name="term">
	</TD>
	</TR>
	<TR>
	<TD BGCOLOR="<?php echo $color1?>" WIDTH="50%">&nbsp;</TD>
	<TD BGCOLOR="<?php echo $color2?>" WIDTH="50%">
		<INPUT TYPE="radio" name="addterms" value="any" CHECKED>
		<font face="<?php echo $FontFace?>" size="<?php echo $FontSize3?>"><?php echo $l_searchany?>
	</TD>
	</TR>
	<TR>
	<TD BGCOLOR="<?php echo $color1?>" WIDTH="50%">&nbsp;</TD>
	<TD BGCOLOR="<?php echo $color2?>" WIDTH="50%">
		<INPUT TYPE="radio" name="addterms" value="all">
		<font face="<?php echo $FontFace?>" size="<?php echo $FontSize3?>"><?php echo $l_searchall?>
	</TD>
	</TR>
	<TR>
	<TD BGCOLOR="<?php echo $color1?>" WIDTH="50%" ALIGN="RIGHT">
		<font face="<?php echo $FontFace?>" size="<?php echo $FontSize2?>"><b><?php echo $l_forum?></b>:&nbsp;
	</TD>
	<TD BGCOLOR="<?php echo $color2?>" WIDTH="50%">
		<select name="forum">
		<option value="all"><?php echo $l_searchallfrm?></option>
		<?php
			$query = "SELECT forum_name,forum_id FROM forums WHERE forum_type != 1";
			if(!$result = mysql_query($query,$db))
			{
				die("<font size=+1>An Error Occured</font><hr>phpBB was unable to query the forums database");
			}
			while($row = @mysql_fetch_array($result))
			{
				echo "<option value=$row[forum_id]>$row[forum_name]</option>";
			}
		?>
		</select>
	</TD>
	</TR>

	<TR>
	<TD BGCOLOR="<?php echo $color1?>" WIDTH="50%" ALIGN="RIGHT">
		<font face="<?php echo $FontFace?>" size="<?php echo $FontSize2?>"><b><?php echo $l_sortby?></b>:
	</TD>
	<TD BGCOLOR="<?php echo $color2?>" WIDTH="50%">
        <font face="<?php echo $FontFace?>" size="<?php echo $FontSize3?>">
	<?php //All values are the fields used to search the database - a table must be specified for each field ?>
		<INPUT TYPE="radio" name="sortby" value="p.post_time desc" CHECKED><?php echo $l_date?>
		&nbsp;&nbsp;
		<INPUT TYPE="radio" name="sortby" value="t.topic_title"><?php echo $l_topic?>
		&nbsp;&nbsp;
		<INPUT TYPE="radio" name="sortby" value="f.forum_name"><?php echo $l_forum?>
	</TD>
	</TR>

<?php
// 25oct00 dsig -add radio to determine what to search title or text or both..default both
?>
   <TR>
   	<TD BGCOLOR="<?php echo $color1?>" WIDTH="50%" ALIGN="RIGHT">
        <font face="<?php echo $FontFace?>" size="<?php echo $FontSize2?>"><b><?php echo $l_searchin?></b>:
       	</TD>
        <TD BGCOLOR="<?php echo $color2?>" WIDTH="50%">
<?php
/*
26oct00 dsig added note
//           on default to change default 'checked' item simply move the 'CHECKED' keyword
//           from one 'radio' to another.
*/
?>
      <font face="<?php echo $FontFace?>" size="<?php echo $FontSize3?>">
      <INPUT TYPE="radio" name="searchboth" value="both" CHECKED><?php echo "$l_subject & $l_body"?>
      <INPUT TYPE="radio" name="searchboth" value="title"><?php echo $l_subject?>
      <INPUT TYPE="radio" name="searchboth" value="text"><?php echo $l_body?>
      </TD>
  </TR>
</TABLE>

	</TD>
</TR>
</TABLE>
<br>
	<CENTER>
	<INPUT TYPE="Submit" Name="submit" Value="<?php echo $l_search?>">
	</FORM>
	</CENTER>

<?php
}
else  // Submitting query
{

/**********
 Sept 6.
 $query is the basis of the query
 $addquery is all the additional search fields - necessary because of the WHERE clause in SQL
**********/

$query = "SELECT u.user_id,f.forum_id, p.topic_id, u.username, p.post_time,t.topic_title,f.forum_name 
			 FROM posts p, posts_text pt, users u, forums f,topics t";
if(isset($term) && $term != "")
{
	$terms = split(" ",addslashes($term));				// Get all the words into an array
	$addquery .= "(pt.post_text LIKE '%$terms[0]%'";		
	$subquery .= "(t.topic_title LIKE '%$terms[0]%'"; 
	
	if($addterms=="any")					// AND/OR relates to the ANY or ALL on Search Page
		$andor = "OR";
	else
		$andor = "AND";
	$size = sizeof($terms);
	for($i=1;$i<$size;$i++) {
		$addquery.=" $andor pt.post_text LIKE '%$terms[$i]%'";
		$subquery.=" $andor t.topic_title LIKE '%$terms[$i]%'"; 
	}	     
	$addquery.=")";
	$subquery.=")";
}
if(isset($forum) && $forum!="all")
{
	if(isset($addquery)) {
	   $addquery .= " AND ";
	   $subquery .= " AND ";
	}
	
	$addquery .=" p.forum_id=$forum";
	$subquery .=" p.forum_id=$forum";
}
if(isset($search_username)&&$search_username!="")
{
	$search_username = addslashes($search_username);
   if(!$result = mysql_query("SELECT user_id FROM users WHERE username='$search_username'",$db))
	{
		error_die("<font size=+1>An Error Occured</font><hr>phpBB was unable to query the forums database");
	}
   $row = @mysql_fetch_array($result);
   if(!$row)
	{
		error_die("That user does not exist.  Please go back and search again.");
	}
   $userid = $row[user_id];
   if(isset($addquery)) {
      $addquery.=" AND p.poster_id=$userid AND u.username='$search_username'";
      $subquery.=" AND p.poster_id=$userid AND u.username='$search_username'";
   }
   else {
      $addquery.=" p.poster_id=$userid AND u.username='$search_username'";
      $subquery.=" p.poster_id=$userid AND u.username='$search_username'";
   }
}	
if(isset($addquery)) {
   switch ($searchboth) { 
    case "both" : 
      $query .= " WHERE ( $subquery OR $addquery ) AND "; 
      break; 
    case "title" : 
      $query .= " WHERE ( $subquery ) AND "; 
      break; 
    case "text" : 
      $query .= " WHERE ( $addquery ) AND "; 
      break; 
   }
}
else
{
     $query.=" WHERE ";
}

   $query .= " p.post_id = pt.post_id 
						AND p.topic_id = t.topic_id 
						AND p.forum_id = f.forum_id 
						AND p.poster_id = u.user_id 
						AND f.forum_type != 1";
//  100100 bartvb  Uncomment the following GROUP BY line to show matching topics instead of all matching posts.
//   $query .= " GROUP BY t.topic_id";
   $query .= " ORDER BY $sortby";
   $query .= " LIMIT 200";

	if(!$result = mysql_query($query,$db))
	{
		die("<font size=+1>An Error Occured</font><hr>phpBB was unable to query the forums database<BR>".mysql_error($db)."<BR>$query");
	}

	if(!$row = @mysql_fetch_array($result))
	{
		die("<center>$l_nomatches</center>");
	}

?>
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="600"><TR>
<TD  BGCOLOR="<?php echo $table_bgcolor; ?>">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
        <TD ALIGN="CENTER" WIDTH="30%"><font face="Verdana" size="2"><B><?php echo $l_forum?></B></font></TD>
        <TD ALIGN="CENTER" WIDTH="30%"><font face="Verdana" size="2"><B><?php echo $l_topic?></B></font></TD>
        <TD ALIGN="CENTER" WIDTH="15%"><font face="Verdana" size="2"><B><?php echo $l_posted?></B></font></TD>
</TR>
<?php
	do {
		echo "<TR BGCOLOR=\"$color2\">";
		echo "<TD ALIGN=\"CENTER\" WIDTH=\"30%\"><a href=\"viewforum.$phpEx?forum=$row[forum_id]\">". stripslashes($row[forum_name]) . "</a></TD>";
		echo "<TD ALIGN=\"CENTER\" WIDTH=\"30%\"><a href=\"viewtopic.$phpEx?topic=$row[topic_id]&forum=$row[forum_id]\">". stripslashes($row[topic_title]) . "</a></TD>";
		echo "<TD ALIGN=\"CENTER\" WIDTH=\"15%\">$row[post_time]</TD>";
		echo "</TR>";
	}while($row=@mysql_fetch_array($result));
?>	

</TABLE>
</TR>
</TR>
</TABLE>
<?php
}
	include('page_tail.'.$phpEx);
?>
