<?php
/***************************************************************************
                          index.php  -  description
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

$require_current_course = TRUE;
$langFiles = 'phpbb';
$require_help = TRUE;
$helpTopic = 'Forums';
include '../../baseTheme.php';
$nameTools = $langUsers . " ($langUserNumber : $countUser)";
$tool_content = "";

include('functions.php'); // application logic for phpBB
include('config.php');
require("auth.php");
$pagetitle = $l_indextitle;
$pagetype = "index";
include('page_header.php');

$sql = "SELECT c.* FROM catagories c, forums f
	 WHERE f.cat_id=c.cat_id
	 GROUP BY c.cat_id, c.cat_title, c.cat_order
	 ORDER BY c.cat_id DESC";
if(!$result = mysql_query($sql, $db))
	error_die("Unable to get categories from database<br>$sql");
$total_categories = mysql_num_rows($result);


$tool_content .= "<TABLE WIDTH=\"" . $TableWidth . "\" CELLPADDING=\"1\" CELLSPACING=\"0\" ALIGN=\"CENTER\" VALIGN=\"TOP\">";
$tool_content .= "<TR><TD BGCOLOR=\"" . $table_bgcolor . "\">\"";
$tool_content .= "<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"1\" WIDTH=\"100%\">";
$tool_content .= "<TR BGCOLOR=\"" . $color1 . "\" ALIGN=\"LEFT\">";
$tool_content .= "<TD BGCOLOR=\"" . $color1 . "\" ALIGN=\"CENTER\" VALIGN=\"MIDDLE\">&nbsp;</TD>";
$tool_content .= "<TD><FONT FACE=\"" . $FontFace . "\" SIZE=\"" . $FontSize1 . "\" COLOR=\"" . $textcolor . "\">";
$tool_content .= "<B>" . $l_forum . "</B></font></TD>";
$tool_content .= "<TD ALIGN=\"CENTER\"><FONT FACE=\"" . $FontFace . "\" SIZE=\"" . $FontSize1 . "\" COLOR=\"" . $textcolor . "\">";
$tool_content .= "<B>" . $l_topics . "</B></font></TD>";
$tool_content .= "<TD ALIGN=\"CENTER\"><FONT FACE=\"" . $FontFace . "\" SIZE=\"" . $FontSize1 . "\" COLOR=\"" . $textcolor . "\">";
$tool_content .= "<B>" . $l_posts . "</B></font></TD>";
$tool_content .= "<TD ALIGN=\"CENTER\"><FONT FACE=\"" . $FontFace . "\" SIZE=\"" . $FontSize1 . "\" COLOR=\"" . $textcolor . "\">";
$tool_content .= "<B>" . $l_lastpost . "</B></font></TD>";
$tool_content .= "</TR>";

if($total_categories)
{
   if(!$viewcat)
     {
	$viewcat = -1;
     }
   while($cat_row = mysql_fetch_array($result))
     {
	$categories[] = $cat_row;
     }

   $limit_forums = "";
   if($viewcat != -1)
     {
	$limit_forums = "WHERE f.cat_id = $viewcat";
     }
   $sql = "SELECT f.*, u.username, u.user_id, p.post_time
	    FROM forums f
	    LEFT JOIN posts p ON p.post_id = f.forum_last_post_id
	    LEFT JOIN users u ON u.user_id = p.poster_id
	    $limit_forums
	    ORDER BY f.cat_id, f.forum_id";
   if(!$f_res = mysql_query($sql, $db))
     {
	error_die("Error getting forum data<br>$sql");
     }

   while($forum_data = mysql_fetch_array($f_res))
     {
	$forum_row[] = $forum_data;
     }
for($i = 0; $i < $total_categories; $i++) {
   if($viewcat != -1) {
      if($categories[$i][cat_id] != $viewcat) {
	$title = stripslashes($categories[$i][cat_title]);
        $tool_content .= "<TR ALIGN=\"LEFT\" VALIGN=\"TOP\"><TD COLSPAN=6 BGCOLOR=\"" . $color1 . "\">";
        $tool_content .= "<FONT FACE=\"" . $FontFace . "\" SIZE=\"" . $FontSize2 . "\" COLOR=\"" . $textcolor . "\"><B>" . $title . "</B></FONT></TD></TR>";
	continue;
     }
   }
   $title = stripslashes($categories[$i][cat_title]);

   // Added by Thomas for Claroline : distinguish group forums from others
   $catNum=$categories[$i][cat_id];

   $tool_content .= "<TR ALIGN=\"LEFT\" VALIGN=\"TOP\"><TD COLSPAN=6 BGCOLOR=\"" . $color1 . "\">";
   $tool_content .= "<FONT FACE=\"" . $FontFace . "\" SIZE=\"" . $FontSize2 . "\" COLOR=\"" . $textcolor . "\"><B>" . $title . "</B></FONT></TD></TR>";
   @reset($forum_row);
   for($x = 0; $x < count($forum_row); $x++)
     {
      unset($last_post);
      if($forum_row[$x]["cat_id"] == $categories[$i]["cat_id"]) {
	 if($forum_row[$x]["post_time"])
	 {
	 	$last_post = $forum_row[$x]["post_time"];
	 }
	 $last_post_datetime = $forum_row[$x]["post_time"];
	 list($last_post_date, $last_post_time) = split(" ", $last_post_datetime);
	 list($year, $month, $day) = explode("-", $last_post_date);
	 list($hour, $min) = explode(":", $last_post_time);
	 $last_post_time = mktime($hour, $min, 0, $month, $day, $year);
	 if(empty($last_post))
	 {
	 	$last_post = "No Posts";
	 }
         $tool_content .= "<TR  ALIGN=\"LEFT\" VALIGN=\"TOP\">";
	 if($last_post_time > $last_visit && $last_post != "No posts") {
            $tool_content .= "<TD BGCOLOR=\"$color1\" ALIGN=\"CENTER\" VALIGN=\"middle\" WIDTH=5%><IMG SRC=\"$newposts_image\"></TD>";
	 }
	 else {
            $tool_content .= "<TD BGCOLOR=\"$color1\" ALIGN=\"CENTER\" VALIGN=\"middle\" WIDTH=5%><IMG SRC=\"$folder_image\"></TD>";
	 }
	 	$name = stripslashes($forum_row[$x][forum_name]);
		$total_posts = $forum_row[$x]["forum_posts"];
		$total_topics = $forum_row[$x]["forum_topics"];
		$desc = stripslashes($forum_row[$x][forum_desc]);

                $tool_content .= "<TD BGCOLOR=\"$color2\"><FONT FACE=\"$FontFace\" SIZE=\"$FontSize2\" COLOR=\"$textcolor\">";


		$forum=$forum_row[$x]["forum_id"];

		// Claroline function added by Thomas July 2002
		// Visit only my group forum if not admin or tutor
		// If tutor, see all groups but indicate my groups
		// echo "<br>categories i $catNum<br>forum : $forum myGroupForum $myGroupForum<br>";	// Debugging

		// TUTOR VIEW
		if($tutorCheck==1)
		{
			$sqlTutor=mysql_query("SELECT id FROM student_group
						WHERE forumId='$forum'
							AND tutor='$uid'");
			$countTutor = mysql_num_rows($sqlTutor); 

			if ($countTutor==0)
			{
                                $tool_content .= "<a href=\"viewforum.php?forum=".$forum_row[$x]["forum_id"]."&$total_posts\">$name</a> 
						";
			}
			else
			{
                                $tool_content .= "<a href=\"viewforum.php?forum=".$forum_row[$x]["forum_id"]."&$total_posts\">$name</a>
					&nbsp;($langOneMyGroups)";
			}
		}
		// ADMIN VIEW
		elseif($status[$dbname] == 1 OR $status[$dbname] == 2)
		{
                        $tool_content .= "<a href=\"viewforum.php?forum=".$forum_row[$x]["forum_id"]."&$total_posts\">$name</a>";
		}
		// STUDENT VIEW
		elseif($catNum==1)
		{ 
			if ($forum==$myGroupForum)
			{
                                $tool_content .= "<a href=\"viewforum.php?forum=".$forum_row[$x]["forum_id"]."&$total_posts\">$name</a>
					&nbsp;&nbsp;($langMyGroup) ";
			}	
			else
			{
				if($privProp==1)
				{
                                        $tool_content .= "$name";
				}
				else
				{
                                        $tool_content .= "<a href=\"viewforum.php?forum=".$forum_row[$x]["forum_id"]."&$total_posts\">$name</a>";
				}
			}
		}
		// OTHER FORUMS
		else
		{
                        $tool_content .= "<a href=\"viewforum.php?forum=".$forum_row[$x]["forum_id"]."&$total_posts\">$name</a> ";
		}

                $tool_content .= "</font>\n";
                $tool_content .= "<br><FONT FACE=\"$FontFace\" SIZE=\"$FontSize1\" COLOR=\"$textcolor\">$desc</font></TD>\n";
                $tool_content .= "<TD BGCOLOR=\"$color1\" WIDTH=5% ALIGN=\"CENTER\" VALIGN=\"MIDDLE\">
		<FONT FACE=\"$FontFace\" SIZE=\"$FontSize2\" COLOR=\"$textcolor\">$total_topics</font></TD>\n";
                $tool_content .= "<TD BGCOLOR=\"$color2\" WIDTH=5% ALIGN=\"CENTER\" VALIGN=\"MIDDLE\">
		<FONT FACE=\"$FontFace\" SIZE=\"$FontSize2\" COLOR=\"$textcolor\">$total_posts</font></TD>\n";
                $tool_content .= "<TD BGCOLOR=\"$color1\" WIDTH=15% ALIGN=\"CENTER\" VALIGN=\"MIDDLE\">
		<FONT FACE=\"$FontFace\" SIZE=\"$FontSize1\" COLOR=\"$textcolor\">$last_post</font></TD>\n";
	 	$forum_moderators = get_moderators($forum_row[$x][forum_id], $db);
                $tool_content .= "</tr>\n";
      }
    }
  }
}
require('page_tail.'.$phpEx);
draw($tool_content, 2, 'user');
?>
