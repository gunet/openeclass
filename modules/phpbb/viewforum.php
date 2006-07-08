<?  session_start();
/***************************************************************************
                            veiwforum.php  -  description
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
$pagetitle = $l_viewforum;
$pagetype = "viewforum";
if($forum == -1)
  header("Location: $url_phpbb");

$sql = "SELECT f.forum_type, f.forum_name FROM forums f WHERE forum_id = '$forum'";
if(!$result = mysql_query($sql, $db))
	error_die("<font size=+1>An Error Occured</font><hr>Could not connect to the forums database.");
if(!$myrow = mysql_fetch_array($result))
	error_die("Error - The forum you selected does not exist. Please go back and try again.");
$forum_name = own_stripslashes($myrow[forum_name]);

// Note: page_header is included later on, because this page might need to send a cookie.

if(($myrow[forum_type] == 1) && !$user_logged_in && !$logging_in) 
{
	require('page_header.php');

$tool_content .= "
<FORM ACTION=\"$PHP_SELF\" METHOD=\"POST\">
<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\" ALIGN=\"CENTER\" VALIGN=\"TOP\" WIDTH=\"$tablewidth\">
<TR>
	<TD BGCOLOR=\"$table_bgcolor\">
	<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"1\" WIDTH=\"99%\">
	<TR BGCOLOR=\"$color1\" ALIGN=\"LEFT\">
		<TD ALIGN=\"CENTER\"><font face='arial, helvetica' size=2>$l_private</TD>
	</TR>
	<TR BGCOLOR=\"$color2\" ALIGN=\"LEFT\">
		<TD ALIGN=\"CENTER\">
			<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\">
			<TR>
			    <TD>
		 	      <font face='arial, helvetica' size=2 COLOR=\"$textcolor\">
			      <b>User Name: &nbsp;</b></font>
			    </TD>
			    <TD>
			      <INPUT TYPE=\"TEXT\" NAME=\"username\" SIZE=\"25\" MAXLENGTH=\"40\" VALUE=\"$userdata[username]\">
			    </TD>
		       </TR>
		       <TR>
			    <TD>
			      <FONT FACE=\"$FontFace\" SIZE=\"$FontSize2\" COLOR=\"$textcolor\">
			      <b>Password: </b></TD><TD><INPUT TYPE=\"PASSWORD\" NAME=\"password\" SIZE=\"25\" MAXLENGTH=\"25\">
			    </TD>
		      </TR>
		      </TABLE>
	      </TD>
	</TR>
	<TR BGCOLOR=\"$color1\" ALIGN=\"LEFT\">
		<TD ALIGN=\"CENTER\">
			<INPUT TYPE=\"HIDDEN\" NAME=\"forum\" VALUE=\"$forum\">
			<INPUT TYPE=\"SUBMIT\" NAME=\"logging_in\" VALUE=\"$l_enter\">
		</TD>
	</TR>
	</TABLE>
	</TD>
</TR>
</TABLE>
</FORM>";

require('page_tail.php');
draw($tool_content, 1);
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

	require('page_header.php');
	
	if ($myrow[forum_type] == 1)
	{
		// To get here, we have a logged-in user. So, check whether that user is allowed to view
		// this private forum.
		
		if (!check_priv_forum_auth($userdata[user_id], $forum, FALSE, $db))
		{
			error_die("$l_privateforum $l_noread");
		}
		
		// Ok, looks like we're good.
	}

$tool_content .= "<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\" ALIGN=\"CENTER\" VALIGN=\"TOP\" WIDTH=\"$tablewidth\"><TR><TD  BGCOLOR=\"$table_bgcolor\"><TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"1\" WIDTH=\"99%\"><TR BGCOLOR=\"$color1\" ALIGN=\"LEFT\"><TD WIDTH=2%>&nbsp;</TD><TD><font face=\"arial, helvetica\" size=\"2\">&nbsp;$l_topic</font></TD><TD WIDTH=9% ALIGN=\"CENTER\"><font face=\"arial, helvetica\" size=\"2\">$l_replies</font></TD><TD WIDTH=20% ALIGN=\"CENTER\"><font face=\"arial, helvetica\" size=\"2\">&nbsp;$l_poster</font></TD><TD WIDTH=8% ALIGN=\"CENTER\"><font face=\"arial, helvetica\" size=\"2\">$langSeen</font></TD><TD WIDTH=15% ALIGN=\"CENTER\"><font face=\"arial, helvetica\" size=\"2\">$langLastMsg</font></TD></TR>";

if(!$start) $start = 0;
   
$sql = "SELECT t.*, u.username, u2.username as last_poster, p.post_time FROM topics t
        LEFT JOIN users u ON t.topic_poster = u.user_id 
        LEFT JOIN posts p ON t.topic_last_post_id = p.post_id
        LEFT JOIN users u2 ON p.poster_id = u2.user_id
        WHERE t.forum_id = '$forum' 
        ORDER BY topic_time DESC LIMIT $start, $topics_per_page";
        
if(!$result = mysql_query($sql, $db))
	error_die("</table></table><font size=+1>An Error Occured</font><hr>phpBB could not query the topics database.<br>$sql");
$topics_start = $start;
   
if($myrow = mysql_fetch_array($result)) {
   do {
      $tool_content .= "<TR>\n";
      $replys = $myrow["topic_replies"];
      $last_post = $myrow["post_time"];
      $last_post_datetime = $myrow["post_time"];
      
      list($last_post_date, $last_post_time) = split(" ", $last_post_datetime);
      list($year, $month, $day) = explode("-", $last_post_date);
      list($hour, $min) = explode(":", $last_post_time);
      $last_post_time = mktime($hour, $min, 0, $month, $day, $year);
		 if($replys >= $hot_threshold) {
			 if($last_post_time < $last_visit) 
				 $image = $hot_folder_image;
			 else 
				 $image = $hot_newposts_image;
		 }
		 else {
			 if($last_post_time < $last_visit) 
				 $image = $folder_image;
			 else
				 $image = $newposts_image;
		 }
		 if($myrow[topic_status] == 1)
			 $image = $locked_image;
      $tool_content .= "<TD BGCOLOR=\"$color1\"><IMG SRC=\"$image\"></TD>\n";
      
      $topic_title = own_stripslashes($myrow[topic_title]);
		$pagination = '';
		$start = '';
		$topiclink = "viewtopic.php?topic=$myrow[topic_id]&forum=$forum";
		if($replys+1 > $posts_per_page) 
		{
			$pagination .= "&nbsp;&nbsp;&nbsp;<font size=\"$FontSize3\" face=\"$FontFace\" color=\"$textcolor\">(<img src=\"$posticon\">$l_gotopage ";
			$pagenr = 1;
			$skippages = 0;
			for($x = 0; $x < $replys + 1; $x += $posts_per_page) 
			{
				$lastpage = (($x + $posts_per_page) >= $replys + 1);
				
				if($lastpage)
				{
					$start = "&start=$x&$replys";
				} 
				else 
				{
					if ($x != 0)
					{
						$start = "&start=$x";
					}
					$start .= "&" . ($x + $posts_per_page - 1);
				}
				
				if($pagenr > 3 && $skippages != 1) 
				{
					$pagination .= ", ... ";
					$skippages = 1;
				} 

				if ($skippages != 1 || $lastpage) 
				{
					if ($x!=0) $pagination .= ", ";
					$pagination .= "<a href=\"$topiclink$start\">$pagenr</a>";
				}
				
				$pagenr++;
			}
			$pagination .= ")</font>";
		} 

		$topiclink .= "&$replys";

      $tool_content .= "<TD BGCOLOR=\"$color2\"><font face=\"$FontFace\" size=\"2\">&nbsp;<a href=\"$topiclink\">$topic_title</a></font>$pagination";
	      
      $tool_content .= "</TD>\n";
      $tool_content .= "<TD BGCOLOR=\"$color1\" ALIGN=\"CENTER\" VALIGN=\"MIDDLE\"><font face=\"arial, helvetica\" size=\"2\">$replys</font></TD>\n";
      $tool_content .= "<TD BGCOLOR=\"$color2\" ALIGN=\"CENTER\" VALIGN=\"MIDDLE\"><font face=\"arial, helvetica\" size=\"2\">$myrow[prenom] $myrow[nom]</font></TD>\n";
      $tool_content .= "<TD BGCOLOR=\"$color1\" ALIGN=\"CENTER\" VALIGN=\"MIDDLE\"><font face=\"arial, helvetica\" size=\"2\">$myrow[topic_views]</font></TD>\n";
      $tool_content .= "<TD BGCOLOR=\"$color2\" ALIGN=\"CENTER\" VALIGN=\"MIDDLE\"><font face=\"$FontFace\" size=\"$FontSize1\">$last_post</font></TD></TR>\n";
      
   } while($myrow = mysql_fetch_array($result));
}
else {
	$tool_content .= "<TD BGCOLOR=\"$color1\" colspan = 6 ALIGN=CENTER><font face=\"arial, helvetica\" size=\"2\">$l_notopics</TD></TR>\n";
}

$tool_content .= "</TABLE></TD></TR></TABLE>";
}
require('page_tail.php');
draw($tool_content, 2);
?>
