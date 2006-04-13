<?php
/***************************************************************************
                          admin_board.php  -  description
                             -------------------
    begin                : Wed July 19 2000
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
include('../extention.inc');
include('../functions.'.$phpEx);
include('../config.'.$phpEx);
require('../auth.'.$phpEx);

if($login) {       
      if ($username == '') {
	       die("You have to enter your username. Go back and do so.");
      }
      if ($password == '') {
	       die("You have to enter your password. Go back and do so.");
      }
      if (!check_username($username, $db)) {
	       die("Invalid username \"$username\". Go back and try again.");
      }
      if (!check_user_pw($username, $password, $db)) {
	       die("Invalid password. Go back and try again.");
      }
           
      $userdata = get_userdata($username, $db);
      $sessid = new_session($userdata[user_id], $REMOTE_ADDR, $sesscookietime, $db);
      set_session_cookie($sessid, $sesscookietime, $sesscookiename, $cookiepath, $cookiedomain, $cookiesecure);
           
      if (defined('USE_IIS_LOGIN_HACK') && USE_IIS_LOGIN_HACK)
		{
			echo "<META HTTP-EQUIV=\"refresh\" content=\"1;URL=$url_admin_index\">";
		}
		else
		{
			header("Location: $url_admin_index");	
		}
}
else if(!$user_logged_in) {
   $pagetitle = "Forum Administration";
      $pagetype = "admin";
      include('../page_header.'.$phpEx);
   
   ?>
     <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="<?php echo $TableWidth?>">
     <TR><TD BGCOLOR="<?php echo $table_bgcolor?>">
     <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
     <TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
     <TD><P><BR><FONT FACE="<?php echo $FontFace?>" SIZE="<? echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
     Please enter your username and password to login.<BR>
     <i>(NOTE: You MUST have cookies enabled in order to login to the administration section of this forum)</i><BR>
     <UL>
     <FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
     <b>User Name: </b><INPUT TYPE="TEXT" NAME="username" SIZE="25" MAXLENGTH="40" VALUE="<?php echo $userdata[username]?>"><BR>
     <b>Password: </b><INPUT TYPE="PASSWORD" NAME="password" SIZE="25" MAXLENGTH="25"><br><br>
     <INPUT TYPE="SUBMIT" NAME="login" VALUE="Submit">&nbsp;&nbsp;&nbsp;<INPUT TYPE="RESET" VALUE="Clear"></ul>
     </FORM>
     </TD></TR></TABLE></TD></TR></TABLE>
     <?php
          include('../page_tail.'.$phpEx);
        exit();
}
else if($user_logged_in && $userdata[user_level] == 4) {

$pagetitle = "Forum Administration";
$pagetype = "admin";
include('../page_header.'.$phpEx);

switch($mode) {
	case 'setoptions':
		if($submit) {
		   $name = addslashes($name);
		   $esig = addslashes($esig);
		   $sql = "SELECT count(*) AS total FROM config WHERE (selected = 1)";
		   $result = mysql_query($sql, $db);
		   if (!$result) {
		      die("Error doing DB query.");
		   }
		   $row = mysql_fetch_array($result);
		   if ($row[total] != 0) {
		      // settings exist, so we can just update.
		      $sql = "UPDATE config SET sitename = '$name', allow_html = '$html', allow_bbcode = '$bb', allow_sig = '$sig', hot_threshold = $hot, posts_per_page = $ppp, topics_per_page = $tpp, override_themes = $override_themes, allow_namechange = $allow_name_change, email_from = '$from', email_sig = '$esig', default_lang = '$selected_lang' WHERE selected = 1";
		      $result = mysql_query($sql, $db);
		   } else {
		      // have to do an insert..
		      $sql = "INSERT INTO config (sitename, allow_html, allow_bbcode, allow_sig, hot_threshold, posts_per_page, topics_per_page, override_themes, allow_namechange, email_from, email_sig, default_lang, selected) ";
		      $sql .= "VALUES ('$name', $html, $bb, $sig, $hot, $ppp, $tpp, $override_themes, $allow_name_change, '$from', '$esig', '$selected_lang', 1)";
		      $result = mysql_query($sql, $db);
		   }
		   if (!$result) {
		      echo mysql_error() . "<br>";
		      die("<FONT FACE=\"$FontFace\" SIZE=\"$FontSize\" COLOR=\"$textcolor\">Error - Cannot update the database.</FONT");
		   }
		   echo "<TABLE width=\"95%\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" bordercolor=\"$table_bgcolor\">";
		   echo "<tr><td align=\"center\" width=\"100%\" bgcolor=\"$color1\"><font face=\"$FontFace\" size=\"$FontSize1\" color=\"$textcolor\"><B>Forum Settings Updated.</B></font></td>";
		   echo "</tr><TR><TD><TABLE width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><TR>";
		   echo "<td align=\"center\" width=\"100%\" bgcolor=\"$color2\"><font face=\"$FontFace\" size=\"$FontSize1\" color=\"$textcolor\"><P><BR>&nbsp;&nbsp;Click <a href=\"$url_admin_index\">here</a> to return to the Administration Panel.<P>Click <a href=\"$url_phpbb_index\">here</a> to return to the forum index.</font><P><BR><P></TD>";
		   echo "</TR></table></TD></TR></TABLE>";
		   
		}
		else {
		$html_yes = $html_no = $bb_yes = $bb_no = $sig_yes = $sig_no = "";
		if($allow_html == 1)
		     $html_yes = "CHECKED";
		   else
		     $html_no = "CHECKED";
		   
		   if($allow_bbcode == 1)
		     $bb_yes = "CHECKED";
		   else
		     $bb_no = "CHECKED";
		   
		   if($allow_sig == 1)
		     $sig_yes = "CHECKED";
		   else
		     $sig_no = "CHECKED";
		   
		   if($override_user_themes == 1)
		     $override_yes = "CHECKED";
		   else
		     $override_no = "CHECKED";

		   if($allow_namechange == 1)
		     $namechange_yes = "CHECKED";
		   else
		     $namechange_no = "CHECKED";
?>
<FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	<TD ALIGN="CENTER" COLSPAN="2"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><B>Set Forum Wide Options</B></FONT></TD>
</TR>
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	<TD ALIGN="CENTER" COLSPAN="2"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><I>NOTE: These settings will be stored in the database and will override any settings in config.<?php echo $phpEx?></I></FONT></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Site Name:</FONT></TD>
	<TD><INPUT TYPE="TEXT" NAME="name" SIZE="30" MAXLENGTH="100" VALUE="<?php echo stripslashes($sitename)?>"></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Email From Address:</FONT><BR>
	    <font face="<?php echo $FontFace?>" size="<?php echo $FontSize1?>" color="<?php echo $textcolor?>"><i>(This is the address that will appear on every email sent by the forums)</i></font></TD>
	<TD><INPUT TYPE="TEXT" NAME="from" SIZE="30" MAXLENGTH="100" VALUE="<?php echo $email_from?>"></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Email Signature:</FONT><BR>
	    <font face="<?php echo $FontFace?>" size="<?php echo $FontSize1?>" color="<?php echo $textcolor?>"><i>(This is the signature that will appear on every email sent by the forums)</i></font></TD>
	<TD><TEXTAREA NAME="esig" ROWS="5" COLS="20"><?php echo stripslashes($email_sig)?></TEXTAREA></TD>
</TR>

<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Default Language:</FONT><BR>
	    <font face="<?php echo $FontFace?>" size="<?php echo $FontSize1?>" color="<?php echo $textcolor?>"><i>(This is the language your board will appear in)</i></font></TD>
	</TD>
	<TD>
	<?php
	  print language_select($sys_lang, "selected_lang", "../language/");
        ?>
        </td>         
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Allow HTML:</FONT></TD>
	<TD><INPUT TYPE="RADIO" NAME="html" VALUE="1" <?php echo $html_yes?>> Yes <INPUT TYPE="RADIO" NAME="html" VALUE="0" <?php echo $html_no?>> No</TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Allow BBCode:</FONT></TD>
	<TD><INPUT TYPE="RADIO" NAME="bb" VALUE="1" <?php echo $bb_yes?>> Yes <INPUT TYPE="RADIO" NAME="bb" VALUE="0" <?php echo $bb_no?>> No</TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Allow Signatures:</FONT></TD>
	<TD><INPUT TYPE="RADIO" NAME="sig" VALUE="1" <?php echo $sig_yes?>> Yes <INPUT TYPE="RADIO" NAME="sig" VALUE="0" <?php echo $sig_no?>> No</TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Override user theme selection</FONT><BR>
	    <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(Setting this will mean that the forum's default theme will override the theme users' select in their preferences)</I></FONT></TD>
	<TD><INPUT TYPE="RADIO" NAME="override_themes" VALUE="1" <?php echo $override_yes?>> Yes <INPUT TYPE="RADIO" NAME="override_themes" VALUE="0" <?php echo $override_no?>> No</TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
        <TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Allow Users to change their Usernames:</FONT></TD>
	<TD><INPUT TYPE="RADIO" NAME="allow_name_change" VALUE="1" <?php echo $namechange_yes?>> Yes <INPUT TYPE="RADIO" NAME="allow_name_change" VALUE="0" <?php echo $namechange_no?>> No</TD>
</TR>		     
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Hot Topic Threshold:</FONT></TD>
	<TD><INPUT TYPE="TEXT" NAME="hot" SIZE="3" MAXLENGTH="3" VALUE="<?php echo $hot_threshold?>"></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Posts per Page:</FONT><br><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(This is the number of posts per topic that will be displayed per page of a topic)</I></FONT></TD>
	<TD><INPUT TYPE="TEXT" NAME="ppp" SIZE="3" MAXLENGTH="3" VALUE="<?php echo $posts_per_page?>"></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
        <TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Topics per Forum:</FONT><br><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(This is the number of topics per forum that will be displayed per page of a forum)</I></FONT></TD>
        <TD><INPUT TYPE="TEXT" NAME="tpp" SIZE="3" MAXLENGTH="3" VALUE="<?php echo $topics_per_page?>"></TD>
</TR>                   
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	<TD ALIGN="CENTER" COLSPAN="2">
		<INPUT TYPE="HIDDEN" NAME="mode" VALUE="setoptions">
		<INPUT TYPE="SUBMIT" NAME="submit" VALUE="Save Changes">&nbsp;&nbsp;
		<INPUT TYPE="RESET" VALUE="Clear">
	</TD>
</TR>
</TABLE></TD></TR></TABLE>
<?php
		}
	break;
	case 'headermetafooter':
		
		if($submit) {
			$header = addslashes($header);
			$metacode = addslashes($metacode);
			$footer = addslashes($footer);
			$sql = "DELETE FROM headermetafooter WHERE (1=1)";
			$result = mysql_query($sql, $db);
			if (!$result) {
				echo mysql_error() . "<br>\n";
				die("Error doing deletion in admin_board.$phpEx");
			}
			$sql = "INSERT INTO headermetafooter (header, meta, footer) VALUES ('$header', '$metacode', '$footer')";
			$result = mysql_query($sql, $db);
			if(!$result) {
				echo mysql_error() . "<br>\n";
				die("<FONT FACE=\"$FontFace\" SIZE=\"$FontSize\" COLOR=\"$textcolor\">Error doing insertion in board_admin.$phpEx</FONT>");
			}
		echo "<TABLE width=\"95%\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" bordercolor=\"$table_bgcolor\">";
		echo "<tr><td align=\"center\" width=\"100%\" bgcolor=\"$color1\"><font face=\"$FontFace\" size=\"$FontSize1\" color=\"$textcolor\"><B>Data Added.</B></font></td>";
		echo "</tr><TR><TD><TABLE width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><TR>";
		echo "<td align=\"center\" width=\"100%\" bgcolor=\"$color2\"><font face=\"$FontFace\" size=\"$FontSize1\" color=\"$textcolor\"><P><BR>&nbsp;&nbsp;Click <a href=\"$url_admin_index\">here</a> to return to the Administration Panel.<P>Click <a href=\"$url_phpbb_index\">here</a> to return to the forum index.</font><P><BR><P></TD>";
		echo "</TR></table></TD></TR></TABLE>";

		} else {
			$sql = "SELECT * FROM headermetafooter WHERE (1=1)";
			$result = mysql_query($sql, $db);
			if (!$result) {
				echo mysql_error() . "<br>\n";
				die("Error doing DB query in admin_board.$phpEx");
			}
			$row = mysql_fetch_array($result);
			$currHeader = stripslashes($row[header]);
			$currMeta = stripslashes($row[meta]);
			$currFooter = stripslashes($row[footer]);
?>
<FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	<TD ALIGN="CENTER" COLSPAN="2"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><B>Add Header/Meta/Footer Commands</B></FONT></TD>
</TR>

<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Header Code:</FONT></TD>
	<TD><TEXTAREA NAME="header" ROWS="15" COLS="45" WRAP="VIRTUAL"><?php echo $currHeader?></TEXTAREA></TD>
</TR>

<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Meta Commands:</FONT></TD>
	<TD><TEXTAREA NAME="metacode" ROWS="15" COLS="45" WRAP="VIRTUAL"><?php echo $currMeta?></TEXTAREA></TD>
</TR>

<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Footer Code:</FONT></TD>
	<TD><TEXTAREA NAME="footer" ROWS="15" COLS="45" WRAP="VIRTUAL"><?php echo $currFooter?></TEXTAREA></TD>
</TR>
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	<TD ALIGN="CENTER" COLSPAN="2">
		<INPUT TYPE="HIDDEN" NAME="mode" VALUE="headermetafooter">
		<INPUT TYPE="SUBMIT" NAME="submit" VALUE="Add Code">&nbsp;&nbsp;
		<INPUT TYPE="RESET" VALUE="Clear">
	</TD>
</TR>
</TABLE></TD></TR></TABLE>
</FORM>
<?php
		}
	break;
	case 'rankadmin':
		if($edit || $delete || $add) {
			if($add) {
				$title = addslashes($title);
				if($special) 
					$sql = "INSERT INTO ranks (rank_title, rank_min, rank_max, rank_special, rank_image) VALUES ('$title', '-1', '-1', '1', '$image')";
				else
					$sql = "INSERT INTO ranks (rank_title, rank_min, rank_max, rank_special, rank_image) VALUES ('$title', '$min_posts', '$max_posts', '0', '$image')";
				if($r = mysql_query($sql, $db))
					echo "<DIV ALIGN=\"CENTER\"><FONT FACE=\"$FontFace\" SIZE=\"$FontSize\" COLOR=\"$textcolor\">Rank added to the database.</FONT></DIV>";
				else
					echo "<DIV ALIGN=\"CENTER\"><FONT FACE=\"$FontFace\" SIZE=\"$FontSize\" COLOR=\"$textcolor\">Error. Could not add rank to the database.</FONT></DIV>";

			}
			else if($edit) {
				$title = addslashes($title);
				if($selected) 
					$sql = "UPDATE ranks SET rank_title = '$title', rank_image = '$image' WHERE rank_id = '$id'";
				else 
					$sql = "UPDATE ranks SET rank_title = '$title', rank_max = '$max_posts', rank_min = '$min_posts', rank_image = '$image' WHERE rank_id = '$id'";	
				if($r = mysql_query($sql, $db))
					echo "<DIV ALIGN=\"CENTER\"><FONT FACE=\"$FontFace\" SIZE=\"$FontSize\" COLOR=\"$textcolor\">Rank Updated</FONT></DIV>";
				else
					echo "<DIV ALIGN=\"CENTER\"><FONT FACE=\"$FontFace\" SIZE=\"$FontSize\" COLOR=\"$textcolor\">Error. Could not modify the database.</FONT></DIV>";
			}
			else if($delete) {
				$sql = "DELETE FROM ranks WHERE rank_id = '$id'";
				if($r = mysql_query($sql, $db))
                                        echo "<DIV ALIGN=\"CENTER\"><FONT FACE=\"$FontFace\" SIZE=\"$FontSize\" COLOR=\"$textcolor\">Rank Removed</FONT></DIV>";
                                else
                                        echo "<DIV ALIGN=\"CENTER\"><FONT FACE=\"$FontFace\" SIZE=\"$FontSize\" COLOR=\"$textcolor\">Error. Could not modify the database.</FONT></DIV>";
			}
		}
?>
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
        <TD ALIGN="CENTER" COLSPAN="6"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Current Rankings<BR>To modify a ranking simply change the values in the text boxes and click the Edit button.<BR>
	To remove a ranking simply click on the 'Delete' button next to the ranking.</FONT></TD>
</TR>
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	<TD ALIGN="CENTER"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Title</FONT></TD>
	<TD ALIGN="CENTER"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Min. Posts</FONT></TD>
	<TD ALIGN="CENTER"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Max. Posts</FONT></TD>
        <TD ALIGN="CENTER"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Image</FONT></TD>
	<TD ALIGN="CENTER"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Edit</FONT></TD>
	<TD ALIGN="CENTER"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Delete</FONT></TD>
</TR>

<?php
	$sql = "SELECT * FROM ranks WHERE rank_special = 0";
	if(!$r = mysql_query($sql, $db)) {
		echo "<TD ALIGN=\"CENTER\" COLSPAN=\"6\"><FONT FACE=\"$FontFace\" SIZE=\"$FontSize\" COLOR=\"$textcolor\">Error connecting to the database.</FONT></TD></TR></TABLE></TABLE>";
		include('../page_tail.'.$phpEx);
		exit();
	}
	if($m = mysql_fetch_array($r)) {
		do {
			echo "<FORM ACTION=\"$PHP_SELF\" METHOD=\"POST\">\n";
			echo "<TR BGCOLOR=\"$color2\" ALIGN=\"CENTER\">\n";
			echo "<TD><INPUT TYPE=\"TEXT\" NAME=\"title\" VALUE=\"" . stripslashes($m[rank_title]) . "\" MAXLENGTH=\"50\" SIZE=\"25\"></TD>\n";
			echo "<TD><INPUT TYPE=\"TEXT\" NAME=\"min_posts\" VALUE=\"$m[rank_min]\" MAXLENGTH=\"5\" SIZE=\"4\"></TD>\n";
			echo "<TD><INPUT TYPE=\"TEXT\" NAME=\"max_posts\" VALUE=\"$m[rank_max]\" MAXLENGTH=\"5\" SIZE=\"4\"></TD>\n";
		        echo "<TD><INPUT TYPE=\"TEXT\" NAME=\"image\" VALUE=\"$m[rank_image]\"  MAXLENGTH=\"50\" SIZE=\"25\"></TD>\n";
			echo "<TD><INPUT TYPE=\"HIDDEN\" NAME=\"id\" VALUE=\"$m[rank_id]\">\n";
			echo "<INPUT TYPE=\"HIDDEN\" NAME=\"mode\" VALUE=\"$mode\">\n";
			echo "<INPUT TYPE=\"SUBMIT\" NAME=\"edit\" VALUE=\"Edit\"></TD>\n";
			echo "<TD><BR><INPUT TYPE=\"SUBMIT\" NAME=\"delete\" VALUE=\"Delete\"></FORM></TD>\n";
			echo "</TR>";
		} while($m = mysql_fetch_array($r));
	}
	else {
		echo "<TR BGCOLOR=\"$color1\" ALIGN=\"CENTER\"><TD COLSPAN=\"6\"><FONT FACE=\"$FontFace\" SIZE=\"$FontSize\" COLOR=\"$textcolor\">No Ranks in the Database. You can add one by entering into the form below</FONT></TD></TR>";
	}
?>
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
        <TD ALIGN="CENTER" COLSPAN="6"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Special Rankings<BR>These are ranks that can be individually assigned to specific users<BR>To assign a special rank to a user click <a href="admin_users.<?php echo $phpEx?>?mode=moduser">here</a>.
	<BR>NOTE: Min and Max post values will be ignored and automatically set to -1 on these rankings.</FONT>
	</TD>
</TR>   

<?php
	$sql = "SELECT * FROM ranks WHERE rank_special != 0";
	if(!$r = mysql_query($sql, $db)) {
                echo "<TD ALIGN=\"CENTER\" COLSPAN=\"6\"><FONT FACE=\"$FontFace\" SIZE=\"$FontSize\" COLOR=\"$textcolor\">Error connecting to the database.</FONT></TD></TR></TABLE></TABLE>";
                include('../page_tail.'.$phpEx);
                exit();
        }
	if($m = mysql_fetch_array($r)) {
                do {
                        echo "<FORM ACTION=\"$PHP_SELF\" METHOD=\"POST\">\n";
                        echo "<TR BGCOLOR=\"$color2\" ALIGN=\"CENTER\">\n";
                        echo "<TD><INPUT TYPE=\"TEXT\" NAME=\"title\" VALUE=\"$m[rank_title]\" MAXLENGTH=\"50\" SIZE=\"25\"></TD>\n";
                        echo "<TD><INPUT TYPE=\"TEXT\" NAME=\"min_posts\" VALUE=\"$m[rank_min]\" MAXLENGTH=\"5\" SIZE=\"4\"></TD>\n";
                        echo "<TD><INPUT TYPE=\"TEXT\" NAME=\"max_posts\" VALUE=\"$m[rank_max]\" MAXLENGTH=\"5\" SIZE=\"4\"></TD>\n";
		        echo "<TD><INPUT TYPE=\"TEXT\" NAME=\"image\" VALUE=\"$m[rank_image]\"  MAXLENGTH=\"50\" SIZE=\"25\"></TD>\n";
                        echo "<TD><INPUT TYPE=\"HIDDEN\" NAME=\"id\" VALUE=\"$m[rank_id]\">\n";
                        echo "<INPUT TYPE=\"HIDDEN\" NAME=\"mode\" VALUE=\"$mode\">\n";
			echo "<INPUT TYPE=\"HIDDEN\" NAME=\"special\" VALUE=\"1\">\n";
                        echo "<INPUT TYPE=\"SUBMIT\" NAME=\"edit\" VALUE=\"Edit\"></TD>\n";
                        echo "<TD><BR><INPUT TYPE=\"SUBMIT\" NAME=\"delete\" VALUE=\"Delete\"></FORM></TD>\n";
                        echo "</TR>";
                } while($m = mysql_fetch_array($r));
        }
	else {
                echo "<TR BGCOLOR=\"$color1\" ALIGN=\"CENTER\"><TD COLSPAN=\"6\"><FONT FACE=\"$FontFace\" SIZE=\"$FontSize\" COLOR=\"$textcolor\">No Special Ranks in the Database. You can add one by entering into the form below.</FONT></TD></TR>";
        }
?>
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
        <TD ALIGN="CENTER" COLSPAN="6"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Add a Ranking<BR>Use this form to add a ranking to the database.</FONT>
	<FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST"></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="CENTER">
	<TD><INPUT TYPE="TEXT" NAME="title" MAXLENGTH="50" SIZE="25"></TD>
	<TD><INPUT TYPE="TEXT" NAME="min_posts" MAXLENGTH="5" SIZE="4"></TD>
	<TD><INPUT TYPE="TEXT" NAME="max_posts" MAXLENGTH="5" SIZE="4"></TD>
        <TD><INPUT TYPE="TEXT" NAME="image" MAXLENGTH="50" SIZE="25"></TD>
	<TD><INPUT TYPE="CHECKBOX" NAME="special"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"> Special Rank</FONT></TD>
	<TD><INPUT TYPE="HIDDEN" NAME="mode" VALUE="rankadmin">
	<INPUT TYPE="SUBMIT" NAME="add" VALUE="Add"></TD>
	</FORM>
</TR>
<?php
	echo "</TABLE></TABLE>\n";
	break;
	case 'sync':
		if($submit)
		{
			echo "<div align=\"center\">Syncing forum index (This may take a while)<br>";
			flush();
			sync($db, NULL, "all forums");
			echo "Forum index synced<br>";
			echo "Syncing topics (This may take longer!)<br>";
			flush();
			sync($db, NULL, "all topics");
			echo "Topics synced<br>";
			echo "Done!</div>";
		}
		else
		{
?>
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
  <td>Clicking the button below will sync up your forums and topics pages with the correct data from the database. Use this section whenever you notice anomolies in the topics and forums lists.</td>
</tr>
<tr bgcolor="<?php echo $color2?>" align="center">
	<td><form action="<?php echo $PHP_SELF?>" method="POST">
	    <input type="hidden" name="mode" value="<?php echo $mode?>"><input type="submit" name="submit" value="Sync Database"></form></td>
	</td>
</tr>
</table>
</td></tr></table>
<?php			
		}
	break;
	
	
}
}
else {
      $pagetype = "admin";
      $pagetitle = "Access Denied!";
   
      include('../page_header.'.$phpEx);
   ?>
          <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="<?php echo $TableWidth?>">
          <TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
          <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
          <TR BGCOLOR="<?php echo $color1?>" ALIGN="center" VALIGN="TOP">
          <TD><FONT FACE="<?php echo $FontFace?>" SIZE="<? echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
          <B>You do not have acess to this area!</b><BR>
          Go <a href="<?php echo $url_phpbb_index?>">Back</a>
          </TD></TR></TABLE></TD></TR></TABLE>
     <?php
}

include('../page_tail.'.$phpEx);
?>
