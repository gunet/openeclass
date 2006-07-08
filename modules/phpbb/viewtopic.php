<?php
session_start(); 
/***************************************************************************
                            viewtopic.php  -  description
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
include '../../include/baseTheme.php';
$nameTools = $langUsers . " ($langUserNumber : $countUser)";
$tool_content = "";

include('functions.php');
include('config.php');
require('auth.php');
$pagetitle = $l_topictitle;
$pagetype = "viewtopic";

$sql = "SELECT f.forum_type, f.forum_name FROM forums f, topics t WHERE (f.forum_id = '$forum') AND (t.topic_id = $topic) AND (t.forum_id = f.forum_id)";
if(!$result = mysql_query($sql, $db))
	error_die("<font size=+1>An Error Occured</font><hr>Could not connect to the forums database.");
if(!$myrow = mysql_fetch_array($result))
	error_die("Error - The forum/topic you selected does not exist. Please go back and try again.");
$forum_name = own_stripslashes($myrow[forum_name]);

// Note: page_header is included later on, because this page might need to send a cookie.
if(($myrow[forum_type] == 1) && !$user_logged_in && !$logging_in) 
{
	require('page_header.php');

$tool_content .= "<FORM ACTION=\"$PHP_SELF\" METHOD=\"POST\"><TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\" ALIGN=\"CENTER\" VALIGN=\"TOP\" WIDTH=\"$tablewidth\"><TR><TD BGCOLOR=\"$table_bgcolor\"><TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"1\" WIDTH=\"99%\"><TR BGCOLOR=\"$color1\" ALIGN=\"LEFT\"><TD ALIGN=\"CENTER\">$l_private</TD></TR><TR BGCOLOR=\"$color2\" ALIGN=\"LEFT\"><TD ALIGN=\"CENTER\"><TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\"><TR><TD><FONT FACE=\"$FontFace\" SIZE=\"$FontSize2\" COLOR=\"$textcolor\"><b>User Name: &nbsp;</b></font></TD><TD><INPUT TYPE=\"TEXT\" NAME=\"username\" SIZE=\"25\" MAXLENGTH=\"40\" VALUE=\"$userdata[username]\"></TD></TR><TR><TD><FONT FACE=\"$FontFace\" SIZE=\"$FontSize2\" COLOR=\"$textcolor\"><b>Password: </b></TD><TD><INPUT TYPE=\"PASSWORD\" NAME=\"password\" SIZE=\"25\" MAXLENGTH=\"25\"></TD></TR></TABLE></TD></TR><TR BGCOLOR=\"$color1\" ALIGN=\"LEFT\"><TD ALIGN=\"CENTER\"><INPUT TYPE=\"HIDDEN\" NAME=\"forum\" VALUE=\"$forum\"><INPUT TYPE=\"HIDDEN\" NAME=\"topic\" VALUE=\"$topic\"><INPUT TYPE=\"SUBMIT\" NAME=\"logging_in\" VALUE=\"$l_enter\"></TD></TR></TABLE></TD></TR></TABLE></FORM>";
require('page_tail.php');
draw($tool_content, 2);
exit();
}
else 
{
   if ($logging_in)
     {
	if ($username == '' || $password == '') 
	  {
	     error_die("$l_userpass $l_tryagain");
	  }
	if (!check_username($username, $db)) 
	  {
	     error_die("$l_nouser $l_tryagain");
	  }
	if (!check_user_pw($username, $password, $db)) 
	  {
	     error_die("$l_wrongpass $l_tryagain");
	  }
	
	/* if we get here, user has entered a valid username and password combination. */
	
	$userdata = get_userdata($username, $db);
	
	$sessid = new_session($userdata[user_id], $REMOTE_ADDR, $sesscookietime, $db);	
	
	set_session_cookie($sessid, $sesscookietime, $sesscookiename, $cookiepath, $cookiedomain, $cookiesecure);
	
     }
   
   
   
   if ($myrow[forum_type] == 1)
     {
	// To get here, we have a logged-in user. So, check whether that user is allowed to view
	// this private forum.
		
	if (!check_priv_forum_auth($userdata[user_id], $forum, FALSE, $db))
	  {
	     include('page_header.php');
	     error_die("$l_privateforum $l_noread");
	  }
	
	// Ok, looks like we're good.
     }
   
   

$sql = "SELECT topic_title, topic_status FROM topics WHERE topic_id = '$topic'";

$total = get_total_posts($topic, $db, "topic");
if($total > $posts_per_page) {
   $times = 0;
   for($x = 0; $x < $total; $x += $posts_per_page)
     $times++;
   $pages = $times;
}

if(!$result = mysql_query($sql, $db))
  error_die("<font size=+1>An Error Occured</font><hr>Could not connect to the forums database.");
$myrow = mysql_fetch_array($result);
$topic_subject = own_stripslashes($myrow[topic_title]);
$lock_state = $myrow[topic_status];
include('page_header.php');

if($total > $posts_per_page) {
   $tool_content .= "<TABLE BORDER=0 WIDTH=$TableWidth ALIGN=CENTER>";
   $times = 1;
   $tool_content .= "<TR ALIGN=\"LEFT\"><TD><FONT FACE=\"$FontFace\" SIZE=\"$FontSize3\" COLOR=\"$textcolor\">$l_gotopage ( ";
   $last_page = $start - $posts_per_page;
   if($start > 0) {
     $tool_content .= "<a href=\"$PHP_SELF?topic=$topic&forum=$forum&start=$last_page\">$l_prevpage</a> ";
   }
   for($x = 0; $x < $total; $x += $posts_per_page) {
      if($times != 1)
	$tool_content .= " | ";
      if($start && ($start == $x)) {
	   $tool_content .= "" .  $times;
      }
      else if($start == 0 && $x == 0) {
	 $tool_content .= "1";
      }
      else {
	$tool_content .= "<a href=\"$PHP_SELF?mode=viewtopic&topic=$topic&forum=$forum&start=$x\">$times</a>";
      }
      $times++;
   }
   if(($start + $posts_per_page) < $total) {
      $next_page = $start + $posts_per_page;
      $tool_content .= " <a href=\"$PHP_SELF?topic=$topic&forum=$forum&start=$next_page\">$l_nextpage</a>";
   }
   $tool_content .= " ) </FONT></TD></TR></TABLE>\n";
}

$tool_content .= "<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\" ALIGN=\"CENTER\" VALIGN=\"TOP\" WIDTH=\"$TableWidth\"><TR><TD  BGCOLOR=\"$table_bgcolor\"><TABLE BORDER=\"0\" CELLPADDING=\"3\" CELLSPACING=\"1\" WIDTH=\"99%\"><TR BGCOLOR=\"$color1\" ALIGN=\"LEFT\"><TD WIDTH=\"20%\"><FONT FACE=\"$FontFace\" SIZE=\"$FontSize2\" COLOR=\"$textcolor\">$l_author</FONT></TD> <TD><FONT FACE=\"$FontFace\" SIZE=\"$FontSize2\" COLOR=\"$textcolor\">$topic_subject</FONT></TD></TR>";

if(isset($start)) {
   $sql = "SELECT p.*, pt.post_text FROM posts p, posts_text pt 
   WHERE topic_id = '$topic' 
   AND p.post_id = pt.post_id
   ORDER BY post_id LIMIT $start, $posts_per_page";
}
else {
   $sql = "SELECT p.*, pt.post_text FROM posts p, posts_text pt
   WHERE topic_id = '$topic'
   AND p.post_id = pt.post_id
   ORDER BY post_id LIMIT $posts_per_page";
}
if(!$result = mysql_query($sql, $db))
  error_die("<font size=+1>An Error Occured</font><hr>Could not connect to the Posts database. $sql");
$myrow = mysql_fetch_array($result);
$row_color = $color2;
$count = 0;
do {
   if(!($count % 2))
     $row_color = $color2;
   else 
     $row_color = $color1;
   
   $tool_content .= "<TR BGCOLOR=\"$row_color\" ALIGN=\"LEFT\">\n";
   if($myrow[poster_id] != -1) {
	   $posterdata = get_userdata_from_id($myrow[poster_id], $db);
	}
   else 
     $posterdata = array("user_id" => -1, "username" => $l_anonymous, "user_posts" => "0", "user_rank" => -1);
   $tool_content .= "<TD valign=top><FONT FACE=\"$FontFace\" COLOR=\"$textcolor\">$myrow[prenom] $myrow[nom]</FONT>";
   $posts = $posterdata[user_posts];
   if($posterdata[user_id] != -1) {
      if($posterdata[user_rank] != 0) 
	$sql = "SELECT rank_title, rank_image FROM ranks WHERE rank_id = '$posterdata[user_rank]'";
      else
	$sql = "SELECT rank_title, rank_image  FROM ranks WHERE rank_min <= " . $posterdata[user_posts] . " AND rank_max >= " . $posterdata[user_posts] . " AND rank_special = 0";
      if(!$rank_result = mysql_query($sql, $db))
	error_die("Error connecting to the database!");
      list($rank, $rank_image) = mysql_fetch_array($rank_result);
      
      $tool_content .= "</td>";
   }
   else {
      $tool_content .= "<BR><FONT FACE=\"$FontFace\" SIZE=\"$FontSize1\" COLOR=\"$textcolor\"></font></TD>";
   }
   $tool_content .= "<TD><img src=\"$posticon\">
   <FONT FACE=\"$FontFace\" SIZE=\"$FontSize1\" COLOR=\"$textcolor\">$l_posted: $myrow[post_time]&nbsp;&nbsp;&nbsp";
   $tool_content .= "<HR></font>\n";
   $message = own_stripslashes($myrow[post_text]);
   
   // Before we insert the sig, we have to strip its HTML if HTML is disabled by the admin.
   // We do this _before_ bbencode(), otherwise we'd kill the bbcode's html.
   $sig = $posterdata[user_sig];
   if (!$allow_html)
   {
		$sig = htmlspecialchars($sig);
		$sig = preg_replace("#&lt;br&gt;#is", "<BR>", $sig);
   }
   
   $message = eregi_replace("\[addsig]$", "<BR>_________________<BR>" . own_stripslashes(bbencode($sig, $allow_html)), $message);

   include_once "$webDir"."/modules/latexrender/latex.php";
   $message = latex_content($message);

   $tool_content .= "\n<FONT COLOR=\"$textcolor\" face=\"$FontFace\">" . $message . "</FONT><BR>";
   $tool_content .= "\n<HR>";
   // Added by Thomas 30-11-2001
  // echo " <font size=1 face='arial, helvetica'><a href=\"$url_phpbb/reply.$phpEx?topic=$topic&forum=$forum&post=$myrow[post_id]&quote=1\">$langQuote</a>&nbsp;&nbsp;";
if($status[$dbname]==1 OR $status[$dbname]==2)
{
$tool_content .= "<font size=1 face='arial, helvetica'><a href=\"$url_phpbb/editpost.$phpEx?post_id=$myrow[post_id]&topic=$topic&forum=$forum\">$langEditDel</a>";
}
 
   $tool_content .= "</TD></TR>";
   $count++;
} while($myrow = mysql_fetch_array($result));
$sql = "UPDATE topics SET topic_views = topic_views + 1 WHERE topic_id = '$topic'";
@mysql_query($sql, $db);

$tool_content .= "</TABLE></TD></TR></TABLE><TABLE ALIGN=\"CENTER\" BORDER=\"0\" WIDTH=\"$TableWidth\">";

if($total > $posts_per_page) {
   $times = 1;
   $tool_content .= "<TR ALIGN=\"RIGHT\"><TD colspan=2><FONT FACE=\"$FontFace\" SIZE=\"$FontSize3\" COLOR=\"$textcolor\">$l_gotopage ( ";
   $last_page = $start - $posts_per_page;
   if($start > 0) {
      $tool_content .= "<a href=\"$PHP_SELF?topic=$topic&forum=$forum&start=$last_page\">$l_prevpage</a> ";
   }
   for($x = 0; $x < $total; $x += $posts_per_page) {
      if($times != 1)
	$tool_content .= " | ";
      if($start && ($start == $x)) {
	 $tool_content .= $times;
      }
      else if($start == 0 && $x == 0) {
	 $tool_content .= "1";
      }
      else {
	 $tool_content .= "<a href=\"$PHP_SELF?mode=viewtopic&topic=$topic&forum=$forum&start=$x\">$times</a>";
      }
      $times++;
   }
   if(($start + $posts_per_page) < $total) {
      $next_page = $start + $posts_per_page;
      $tool_content .= "
				<a href=\"".$PHP_SELF."?topic=".$topic."&forum=".$forum."&start=".$next_page."\">".$l_nextpage."</a>";
   }
   $tool_content .= "
			</FONT>
		</TD>
	</TR>";
}

$tool_content .= "<TR><TD colspan=\"2\"><font  size=\"2\" face=\"arial, helvetica\"><a href=\"newtopic.php?forum=$forum\">";
$tool_content .= "$langNewTopic</a>&nbsp;&nbsp;";
		if($lock_state != 1) {

			$tool_content .= "<a href=\"$url_phpbb/reply.php?topic=$topic&forum=$forum\">$langAnswer</a></TD></tr>";
		}
		else {
			$tool_content .= "<IMG SRC=\"$reply_locked_image\" BORDER=\"0\"></TD></tr>";
		}
$tool_content .= "</TD><TD ALIGN=\"RIGHT\" colspan=2><hr noshade size=1></TR></TABLE><CENTER>";

}

require('page_tail.php');
draw($tool_content,2);

?>
