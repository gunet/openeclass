<?php
/***************************************************************************
                          admin_users.php  -  description
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
          <TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
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
	case 'moduser':
		if($submit && $edit_user_id) {
			$sql = "UPDATE users SET username = '$edit_username', user_email = '$email', user_rank = '$rank', user_level = '$level' WHERE user_id = $edit_user_id";
			if(!$r = mysql_query($sql, $db))
				die("Error could not update the database.");
		echo "<TABLE width=\"95%\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" bordercolor=\"$table_bgcolor\">";
		echo "<tr><td align=\"center\" width=\"100%\" bgcolor=\"$color1\"><font face=\"$FontFace\" size=\"$FontSize1\" color=\"$textcolor\"><B>User Information Updated.</B></font></td>";
		echo "</tr><TR><TD><TABLE width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><TR>";
		echo "<td align=\"center\" width=\"100%\" bgcolor=\"$color2\"><font face=\"$FontFace\" size=\"$FontSize1\" color=\"$textcolor\"><P><BR>&nbsp;&nbsp;Click <a href=\"$url_admin_index\">here</a> to return to the Administration Panel.<P>Click <a href=\"$PHP_SELF?mode=moduser\">here</a> to modify another user.</font><P><BR><P></TD>";
		echo "</TR></table></TD></TR></TABLE>";
		}
		else {
			if(!$edit_user_id) {
				$sql = "SELECT username, user_id FROM users ORDER BY username";
				if(!$r = mysql_query($sql, $db))
					die("Error connecting to the database. Please check your config.$phpEx file.");
				if(!$m = mysql_fetch_array($r))
					die("No users in the database.");
?>
<FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
        <TD ALIGN="CENTER" COLSPAN="2"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><B>Select a User to Modify</B></FONT></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
        <TD align="right"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">User:</FONT></TD>
        <TD><SELECT NAME="edit_user_id">
<?php
				do {
					echo "<OPTION VALUE=\"$m[user_id]\">$m[username]</OPTION>\n";
				} while($m = mysql_fetch_array($r));
?>
	</SELECT>
	</TD>
</TR>
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
        <TD ALIGN="CENTER" COLSPAN="2">
                <INPUT TYPE="HIDDEN" NAME="mode" VALUE="moduser">
                <INPUT TYPE="SUBMIT" NAME="modify" VALUE="Modify User">&nbsp;&nbsp;
                <INPUT TYPE="RESET" VALUE="Clear">
        </TD>
</TR>
</TR>                        
</TABLE></TD></TR></TABLE>
<?php
			}
			else {
				$moduserdata = get_userdata_from_id($edit_user_id, $db);
				if($moduserdata[user_rank] != 0) {
					$sql = "SELECT rank_id, rank_title FROM ranks WHERE rank_min < " . $moduserdata[user_posts] . " AND rank_max > " . $moduserdata[user_posts] . " AND rank_special = 0";
					if(!$r = mysql_query($sql, $db))
						die("Error connecting to the database. Please check your config.$phpEx file.");
					list($rank_id, $rank) = @mysql_fetch_array($r);
				}
				else {
					$sql = "SELECT rank_title FROM ranks WHERE rank_id = '$moduserdata[user_rank]'";
					if(!$r = mysql_query($sql, $db))
                                                die("Error connecting to the database. Please check your config.$phpEx file.");
                                        list($rank) = @mysql_fetch_array($r);
				}
?>
<FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	<TD ALIGN="CENTER" COLSPAN="2"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Modifying <b><?php echo $moduserdata[username]?></b></FONT></TD>
</TR>
<TR ALIGN="LEFT">
	<TD ALIGN="LEFT" BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">User Name:</FONT></TD>
	<TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" NAME="edit_username" VALUE="<?php echo $moduserdata[username]?>" MAXLENGTH=40 SIZE=25></TD>
</TR>
<TR ALIGN="LEFT">
	<TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Email Address:</FONT></TD>
	<TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" NAME="email" VALUE="<?php echo $moduserdata[user_email]?>" MAXLENGTH=50 SIZE=30></TD>
</TR>
<TR ALIGN="LEFT">
        <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Rank:</FONT></TD>
	<TD BGCOLOR="<?php echo $color2?>"><SELECT NAME="rank">
<?php
				$sql = "SELECT rank_id, rank_title FROM ranks WHERE rank_special = 1";
				$r = mysql_query($sql, $db);
				if($m = mysql_fetch_array($r)) {
					echo "<OPTION VALUE=\"0\">No Special Rank Assigned</OPTION>";
					echo "<OPTION VALUE=\"0\">------------------------</OPTION>";
					do {
						unset($selected);
						if($moduserdata[user_rank] == $m[rank_id])
							$selected = "SELECTED";
						echo "<OPTION VALUE=\"$m[rank_id]\" $selected>$m[rank_title]</OPTION>\n";
					} while($m = mysql_fetch_array($r));
				echo "</SELECT>\n";
				}
				else {
					echo "<OPTION VALUE=\"0\">No Special Ranks in Database</OPTION></SELECT>\n";
					echo "<BR><FONT FACE=\"$FontFace\" SIZE=\"$FontSize2\" COLOR=\"$textcolor\">Click <a href=\"admin_board.$phpEx?mode=rankadmin\">here</a> to add Ranks.</FONT>";
				}
?>
	</TD>
</TR>
<TR ALIGN="LEFT">
        <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">User Level:</FONT></TD>
	<TD BGCOLOR="<?php echo $color2?>"><SELECT NAME="level">
<?php
				$sql = "SELECT access_id, access_title FROM access ORDER BY access_id";
				 $r = mysql_query($sql, $db);
                                if($m = mysql_fetch_array($r)) {
                                        do {
                                                unset($selected);
                                                if($moduserdata[user_level] == $m[access_id])
                                                        $selected = "SELECTED";
                                                echo "<OPTION VALUE=\"$m[access_id]\" $selected>$m[access_title]</OPTION>\n";
                                        } while($m = mysql_fetch_array($r));
				}
?>
		</SELECT>
	</TD>
</TR>
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
        <TD ALIGN="CENTER" COLSPAN="2">
                <INPUT TYPE="HIDDEN" NAME="mode" VALUE="moduser">
					 <INPUT TYPE="HIDDEN" NAME="edit_user_id" VALUE="<?php echo $edit_user_id?>">
                <INPUT TYPE="SUBMIT" NAME="submit" VALUE="Modify User">&nbsp;&nbsp;
                <INPUT TYPE="RESET" VALUE="Clear">
        </TD>
</TR>
</TR>
</TABLE></TD></TR></TABLE>


<?php
		}
	}
   break;
 case 'badwords':
   if($action) {
      switch($action) {
       case 'Add':
	 if($bad_word != '' && $replacement != '') {
	    $bad_word = addslashes($bad_word);
	    $replacement = addslashes($replacement);
	    $sql = "INSERT INTO words (word, replacement) VALUES ('$bad_word', '$replacement')";
	    if(!$r = mysql_query($sql, $db)) {
	       echo "<CENTER><FONT FACE=\"$FontFace\" SIZE=\"$FontSize4\" COLOR=\"$textcolor\">Error. Could not insert into the DB</FONT></CENTER><BR>";
	       break;
	    }
	    else {
	       echo "<CENTER><FONT FACE=\"$FontFace\" SIZE=\"$FontSize4\" COLOR=\"$textcolor\">Word Censor Added!</FONT></CENTER><BR>";
	    }
	 }
	 else {
	    echo "<CENTER><FONT FACE=\"$FontFace\" SIZE=\"$FontSize4\" COLOR=\"$textcolor\">Error. You did not fill out all areas of the form!</CENTER><BR>";
	 }
	 break;
       case 'Delete':
	 $sql = "DELETE FROM words WHERE word_id = '$word_id'";
	 if(!$r = mysql_query($sql, $db)) {
	    echo "<CENTER><FONT FACE=\"$FontFace\" SIZE=\"$FontSize4\" COLOR=\"$textcolor\">Error. Could not delete from the DB</FONT></CENTER><BR>";
	    break;
	 }
	 else {
	    echo "<CENTER><FONT FACE=\"$FontFace\" SIZE=\"$FontSize4\" COLOR=\"$textcolor\">Word Censor Removed!</FONT></CENTER><BR>";
	 }
	 break;
       case 'Edit':
	 $bad_word = addslashes($bad_word);
	 $replacement = addslashes($replacement);
	 $sql = "UPDATE words SET word = '$bad_word', replacement = '$replacement' WHERE word_id = '$word_id'";
	 if(!$r = mysql_query($sql, $db)) {
	    echo "<CENTER><FONT FACE=\"$FontFace\" SIZE=\"$FontSize4\" COLOR=\"$textcolor\">Error. Could not update the DB</FONT></CENTER><BR>";
	    break;
	 }
	 else {
	    echo "<CENTER><FONT FACE=\"$FontFace\" SIZE=\"$FontSize4\" COLOR=\"$textcolor\">Word Censor Updated!</FONT></CENTER><BR>";
	 }
	 break;
      }
   }
?>
     <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
     <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
     <TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
             <TD ALIGN="CENTER" COLSPAN="4"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Current Word Censors<BR>To modify a word and/or its replacement text simply change the values in the text boxes and click the Edit button.<BR>
             To remove a censored word simply click on the 'Delete' button next to the word.</FONT></TD>
     </TR>
     <TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
             <TD ALIGN="CENTER"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Word</FONT></TD>
             <TD ALIGN="CENTER"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Replacement</FONT></TD>
             <TD ALIGN="CENTER"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Edit</FONT></TD>
             <TD ALIGN="CENTER"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Delete</FONT></TD>
     </TR>
<?php
     $sql = "SELECT * FROM words";
   if(!$r = mysql_query($sql, $db)) {
      echo "<TD ALIGN=\"CENTER\" COLSPAN=\"6\"><FONT FACE=\"$FontFace\" SIZE=\"$FontSize\" COLOR=\"$textcolor\">Error connecting to the database.</FONT></TD></TR></TABLE></TABLE>";
      include('../page_tail.'.$phpEx);
      exit();
   }
   if($m = mysql_fetch_array($r)) {
      do {
	 echo "<FORM ACTION=\"$PHP_SELF\" METHOD=\"POST\">\n";
	 echo "<TR BGCOLOR=\"$color2\" ALIGN=\"CENTER\">\n";
	 echo "<TD><INPUT TYPE=\"TEXT\" NAME=\"bad_word\" VALUE=\"" . stripslashes($m[word]) . "\" MAXLENGTH=\"50\" SIZE=\"25\"></TD>\n";
	 echo "<TD><INPUT TYPE=\"TEXT\" NAME=\"replacement\" VALUE=\"" . stripslashes($m[replacement]) . "\" MAXLENGTH=\"50\" SIZE=\"25\"></TD>\n";
	 echo "<TD><INPUT TYPE=\"HIDDEN\" NAME=\"word_id\" VALUE=\"$m[word_id]\">\n";
	 echo "<INPUT TYPE=\"HIDDEN\" NAME=\"mode\" VALUE=\"$mode\">\n";
	 echo "<INPUT TYPE=\"SUBMIT\" NAME=\"action\" VALUE=\"Edit\"></TD>\n";
	 echo "<TD><BR><INPUT TYPE=\"SUBMIT\" NAME=\"action\" VALUE=\"Delete\"></FORM></TD>\n";
	 echo "</TR>";
      } while($m = mysql_fetch_array($r));
   }
   else {
      echo "<TR BGCOLOR=\"$color1\" ALIGN=\"CENTER\"><TD COLSPAN=\"4\"><FONT FACE=\"$FontFace\" SIZE=\"$FontSize\" COLOR=\"$textcolor\">No censored words in the database. You can enter one using the form below</FONT></TD></TR>";
   }
?>
     <TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
             <TD ALIGN="CENTER" COLSPAN="4"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Add a Word<BR>Use this form to add a word censor to the database.</FONT>
             </TD><FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
     </TR>
     <TR BGCOLOR="<?php echo $color1?>" ALIGN="CENTER">
             <TD colspan="2"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Word</font></TD>
             <TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Replacement</font></TD>
             <TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Action</font></TD>
     </TR>
     <TR BGCOLOR="<?php echo $color2?>" ALIGN="CENTER">
             <TD colspan="2"><INPUT TYPE="TEXT" NAME="bad_word" MAXLENGTH="50" SIZE="25"></TD>
             <TD><INPUT TYPE="TEXT" NAME="replacement" MAXLENGTH="50" SIZE="25"></TD>
             <TD><INPUT TYPE="HIDDEN" NAME="mode" VALUE="<?php echo $mode?>">
             <INPUT TYPE="SUBMIT" NAME="action" VALUE="Add"></TD>
             </FORM>
     </TR>
<?php
     echo "</TABLE></TABLE>\n";
   break;
 case 'badusernames':
   if($edit || $add || $delete) {
			if($add) {
				$dis_username = addslashes($dis_username);
				$sql = "INSERT INTO disallow (disallow_username) VALUES ('$dis_username')";
				if(!$r = mysql_query($sql, $db))
					echo "<CENTER><font size=+1>Error - Could not add username. Please try again.</font></center>";
				else
					echo "<CENTER><font size=+1>Username Added</font></center>";
			}
			else if($delete) {
				$sql = "DELETE FROM disallow WHERE disallow_id = '$id'";
				if(!$$r = mysql_query($sql, $db))
                                        echo "<CENTER><font size=+1>Error - Could not remove username. Please try again.</font></center>";
                                else
                                        echo "<CENTER><font size=+1>Username Removed</font></center>";
			}
			else if($edit) {
				$dis_username = addslashes($dis_username);
				$sql = "UPDATE disallow SET disallow_username = '$dis_username' WHERE disallow_id = '$id'";
				if(!$r = mysql_query($sql, $db))
                                        echo "<CENTER><font size=+1>Error - Could not update the database. Please try again.</font></center>";
                                else
                                        echo "<CENTER><font size=+1>Username Updated</font></center>";
			}
		}
?>
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	<TD ALIGN="CENTER" COLSPAN="3"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Current Disallowed Usernames<BR>You can edit an entry by altering the text in the boxes and pressing the 'Edit' button
	<BR>You can remove an entry by clicking its 'Delete' button.</FONT></TD>
</TR>
<TR BGCOLOR="<?php echo $color1?>" ALIGN="CENTER">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Disallowed Username</FONT></TD>
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Edit</FONT></TD>
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Delete</FONT></TD>
</TR>
<?php
	$sql = "SELECT disallow_id, disallow_username FROM disallow";
	if(!$r = mysql_query($sql, $db)) {
		echo "<TR BGCOLOR=\"$color1\" ALIGN=\"LEFT\"><TD COLSPAN=\"3\">Error - Could not query the database. Please check your config.$phpEx file.</TD></TR></TABLE></TABLE>";
		include('../page_tail.'.$phpEx);
		exit();
	}
	if($m = mysql_fetch_array($r)) {
		do {
			echo "<TR BGCOLOR=\"$color2\" ALIGN=\"CENTER\">\n";
			echo "<TD><FORM ACTION=\"$PHP_SELF\" METHOD=\"POST\"><INPUT TYPE=\"TEXT\" NAME=\"dis_username\" VALUE=\"" . stripslashes($m[disallow_username]) . "\" MAXLENGTH=\"40\" SIZE=\"25\"></TD>\n";
			echo "<TD><INPUT TYPE=\"HIDDEN\" NAME=\"mode\" VALUE=\"$mode\"><INPUT TYPE=\"HIDDEN\" NAME=\"id\" VALUE=\"$m[disallow_id]\">";
			echo "<INPUT TYPE=\"SUBMIT\" NAME=\"edit\" VALUE=\"Edit\"></TD>\n";
                        echo "<TD><INPUT TYPE=\"SUBMIT\" NAME=\"delete\" VALUE=\"Delete\"></FORM></TD></TR>\n";
		} while($m = mysql_fetch_array($r));
	}
	else 
		echo "<TR BGCOLOR=\"$color2\" ALIGN=\"CENTER\"><TD COLSPAN=\"3\">No Disallowed usernames in the database, use the form below to add one.</TD></TR>";
?>
<TR BGCOLOR="<?php echo $color1?>" ALIGN="CENTER">
	<TD COLSPAN="3"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Disallow a Username<BR>Use the following form to add usernames to the disallowed list.</FONT></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="CENTER">
	<TD><FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
	    <INPUT TYPE="TEXT" NAME="dis_username" MAXLENGTH="50" SIZE="25">
	</TD>
	<TD COLSPAN="2"><INPUT TYPE="HIDDEN" NAME="mode" VALUE="<?php echo $mode?>">
	    <INPUT TYPE="SUBMIT" NAME="add" VALUE="Add Username"></FORM>
	</TD>
</TR>
</TABLE>
</TABLE>
<?php
	break;		
	case 'remuser':
		if($submit) {
		   if($type == "hard") {
		      $deluserdata = get_userdata_from_id($user_id, $db);
		      if($deluserdata[user_posts] > 0) {
			 echo "Error. This use has posted messages on the forums, therefor he/she cannot be hard deleted. Please go back and 'soft delete' this user if you want to remove them.";
			 include('../page_tail.'.$phpEx);
			 exit();
		      }
		      $sql = "DELETE FROM users WHERE user_id = '$user_id'";
		   }
		   else 
		     $sql = "UPDATE users SET user_level = -1 WHERE user_id = '$user_id'";
		   if(!$r = mysql_query($sql, $db)) {
		      echo "Error - Could not remove user from the database.";
		      include('../page_tail.'.$phpEx);
		      exit();
		   }
		   $sql = "DELETE FROM forum_mods WHERE user_id = '$user_id'";
		   if(!$r = mysql_query($sql, $db)) {
		      echo "Error - Could not remove user from the database.";
		      include('../page_tail.'.$phpEx);
		      exit();
		   }
		   echo "<TABLE width=\"95%\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" bordercolor=\"$table_bgcolor\">";
		   echo "<tr><td align=\"center\" width=\"100%\" bgcolor=\"$color1\"><font face=\"$FontFace\" size=\"$FontSize1\" color=\"$textcolor\"><B>User Removed.</B></font></td>";
		   echo "</tr><TR><TD><TABLE width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><TR>";
		   echo "<td align=\"center\" width=\"100%\" bgcolor=\"$color2\"><font face=\"$FontFace\" size=\"$FontSize1\" color=\"$textcolor\"><P><BR>&nbsp;&nbsp;Click <a href=\"$url_admin_index\">here</a> to return to the Administration Panel.<P>Click <a href=\"$url_phpbb_index\">here</a> to return to the forum index.</font><P><BR><P></TD>";
		   echo "</TR></table></TD></TR></TABLE>";
		}
                else {

			$sql = "SELECT username, user_id FROM users WHERE user_id != -1 ORDER BY username";
                        if(!$r = mysql_query($sql, $db))
                        	die("Error connecting to the database. Please check your config.$phpEx file.");
                       	if(!$m = mysql_fetch_array($r))
                                die("No users in the database.");
?>
<FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
        <TD ALIGN="CENTER" COLSPAN="2"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><B>Select a User to Remove from the Database</B></FONT></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
        <TD align="right"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">User:</FONT></TD>
        <TD><SELECT NAME="user_id">
<?php
                                do {
                                        echo "<OPTION VALUE=\"$m[user_id]\">$m[username]</OPTION>\n";
                                } while($m = mysql_fetch_array($r));
?>
        </SELECT>
        </TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD ALIGN="right"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Deletion Type:</FONT></TD>
	<TD><INPUT TYPE="RADIO" NAME="type" VALUE="hard"> Hard Delete <i>(Remove the users record from the users database, you may not hard delete users who have posted messages!)</i><BR>
	    <INPUT TYPE="RADIO" NAME="type" VALUE="soft" CHECKED> Soft Delete <i>(The users record remains but they cannot login, post, reply etc etc. This is safer)</i></TD>
</TR>		     
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
        <TD ALIGN="CENTER" COLSPAN="2">
                <INPUT TYPE="HIDDEN" NAME="mode" VALUE="remuser">
                <INPUT TYPE="SUBMIT" NAME="submit" VALUE="Delete User">&nbsp;&nbsp;
                <INPUT TYPE="RESET" VALUE="Clear">
        </TD>
</TR>
</TR>
</TABLE></TD></TR></TABLE>

<?php
		}
   break;
 case 'banuser':
   if($add) {
      $starttime = mktime (date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));
      switch($durtype) {
       case 1:
	 $type = 1;
	 break;
       case 2:
	 $type = 60;
	 break;
       case 3:
	 $type = 3600;
	 break;
       case 4:
	 $type = 86400;
	 break;
       case 5:
	 $type = 31536000;
	 break;
      }
      if(!isset($duration))
	$duration = 0;
      
      if($duration != 0)
	$endtime = $starttime + ($duration * $type);
      else 
	$endtime = 0;
      
      if($banby == 1) {
	$sql = "INSERT INTO banlist (ban_ip, ban_start, ban_end, ban_time_type) VALUES ('$ipuser', '$starttime', '$endtime', '$durtype')";
	 if(!$r = mysql_query($sql, $db))                                                                                        
	   echo "<font size=\"$FontSize4\"><center>Error. Could not add ban!</center></font><br>";                               
	 echo "<font size=\"$FontSize4\"><center>Ban Added</center></font><br>";
      }
      else {
	 $banuserdata = get_userdata($ipuser, $db);
	 if($banuserdata[user_id]) {
	    $sql = "INSERT INTO banlist (ban_userid, ban_start, ban_end, ban_time_type) VALUES ('$banuserdata[user_id]', '$starttime', '$endtime', '$durtype')";

	    if(!$r = mysql_query($sql, $db))
	      echo "<font size=\"$FontSize4\"><center>Error. Could not add ban!</center></font><br>";
	    echo "<font size=\"$FontSize4\"><center>Ban Added</center></font><br>";
	 }
	 else 
	   echo "<font size=\"$FontSize4\"><center>Error. No such user!</center></font>";
      }
   }
   else if($del) {
      $sql = "DELETE FROM banlist WHERE ban_id = '$ban_id'";
      if(!$r = mysql_query($sql, $db))
	echo "<font size=\"$FontSize4\"><center>Error. Could not remove ban!</center></font><br>";
      echo "<font size=\"$FontSize4\"><center>Ban Removed</center></font><br>";                 
      
   }
   else if($edit) {
      $starttime = mktime (date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));
      switch($unit) {
       case 1:
	 $type = 1;
	 break;
       case 2:
	 $type = 60;
	 break;
       case 3:
	 $type = 3600;
	 break;
       case 4:
	 $type = 86400;
	 break;
       case 5:
	 $type = 31536000;
	 break;
      }
      if(!isset($dur))
	$dur = 0;
      
      if($dur != 0)
	$endtime = $starttime + ($dur * $type);
      else
	$endtime = 0;
      if(isset($ipaddy)) 
	$sql = "UPDATE banlist SET ban_ip = '$ipaddy', ban_start = '$starttime', ban_end = '$endtime', ban_time_type = '$unit' WHERE ban_id = '$ban_id'";
      else {
	 $banneduserdata = get_userdata($user_name, $db);
	$sql = "UPDATE banlist SET ban_userid = '$banneduserdata[user_id]', ban_start = '$starttime', ban_end = '$endtime', ban_time_type = '$unit' WHERE ban_id = '$ban_id'";
      }

      if(!$r = mysql_query($sql, $db))
	echo "<font size=\"$FontSize4\"><center>Error. Ban could not be updated</center></font>";
      echo "<center><font size=\"$FontSize4\">Ban Modified</font></center>";
   }

   
?>   
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	<TD ALIGN="CENTER" COLSPAN="4"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Current Banned IPs<BR>You can edit an entry by altering the text in the boxes and pressing the 'Edit' button
	<BR>You can remove an entry by clicking its 'Delete' button.</FONT></TD>
</TR>
<TR BGCOLOR="<?php echo $color1?>" ALIGN="CENTER">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">IP Address</FONT></TD>
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Duration</FONT></TD>
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Edit</FONT></TD>
     	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Delete</FONT></TD>
</TR>
<?php
     $sql = "SELECT * FROM banlist WHERE ban_ip";
   if(!$r = mysql_query($sql, $db))
     echo "<tr bgcolor=\"$color2\" align=\"center\"><td colspan=\"4\"><b>Error quering the database!</b></td></tr>";
   while($banlist = mysql_fetch_array($r)) {
      unset($dur);
      unset($unit);
      echo "<tr bgcolor=\"$color2\" align=\"center\"><td><form action=\"$PHP_SELF\" method=\"POST\"><input type=\"text\" name=\"ipaddy\" value=\"$banlist[ban_ip]\" size=\"32\"></td>\n";
      $type = $banlist[ban_time_type];
      if($banlist[ban_end] == 0) {
	 $dur = "Parmanent";
	 $unit = "Ban";
      }
      else {
	 switch($type) {
	  case 1:
	    $dur = ($banlist[ban_end] - $banlist[ban_start]);
	    $unit = "Seconds";
	    $s = "SELECTED";
	    break;
	  case 2:
	    $dur = ($banlist[ban_end] - $banlist[ban_start]) / 60;
	    $unit = "Minutes";
	    $m = "SELECTED";
	    break;
	  case 3:
	    $dur = ($banlist[ban_end] - $banlist[ban_start]) / 3600;
	    $unit = "Hours";
	    $h = "SELECTED";
	    break;
	  case 4:
	    $dur = ($banlist[ban_end] - $banlist[ban_start]) / 86400;
	    $unit = "Days";
	    $d = "SELECTED";
	    break;
	  case 5:
	    $dur = ($banlist[ban_end] - $banlist[ban_start]) / 31536000;
	    $unit = "Years";
	    $y = "SELECTED";
	    break;
	 }
      }
   
      if($unit != "Ban") {
	 echo "<td align=\"center\"><input type=\"text\" name=\"dur\" size=\"".strlen($dur)."\" maxlengh=\"25\" value=\"$dur\">\n";
	 echo "<select name=\"unit\"><option value=\"1\" $s>Seconds</option>
		<option value=\"2\" $m>Minutes</option>
		<option value=\"3\" $h>Hours</option>
		<option value=\"4\" $d>Days</option>
		<option value=\"5\" $y>Years</option></select></td>";
      }
      else {
	 echo "<td align=\"center\">$dur $unit</td>";
      }
      echo "<td><input type=\"HIDDEN\" name=\"ban_id\" value=\"$banlist[ban_id]\">";
      echo "<input type=\"hidden\" name=\"mode\" value=\"$mode\">";
      echo "<input type=\"submit\" name=\"edit\" value=\"Edit\"></td>";
      echo "<td><br><input type=\"submit\" name=\"del\" value=\"Delete\"></form></td>";
      echo "</tr>";
   }
?>
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	<TD ALIGN="CENTER" COLSPAN="4"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Current Banned Usernames<BR>You can edit an entry by altering the text in the boxes and pressing the 'Edit' button
	<BR>You can remove an entry by clicking its 'Delete' button.</FONT></TD>
</TR>
<TR BGCOLOR="<?php echo $color1?>" ALIGN="CENTER">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Username</FONT></TD>
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Duration</FONT></TD>
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Edit</FONT></TD>
     	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Delete</FONT></TD>
</TR>
<?php
     unset($banlist);
   unset($dur);
   unset($unit);
     $sql = "SELECT * FROM banlist WHERE ban_userid";
   if(!$r = mysql_query($sql, $db))
     echo "<tr bgcolor=\"$color2\"><td colspan=\"4\"><b>Error quering the database!</b></td></tr>";
   while($banlist = mysql_fetch_array($r)) {
      $banuserdata = get_userdata_from_id($banlist[ban_userid], $db);
      echo "<tr bgcolor=\"$color2\" align=\"center\"><td align=\"center\"><form action=\"$PHP_SELF\" method=\"POST\"><input type=\"text\" name=\"user_name\" value=\"$banuserdata[username]\" maxlenght=\"35\" size=\"25\"></td>\n";
      $type = $banlist[ban_time_type];
      if($banlist[ban_end] == 0) {
	 $dur = "Permanent";
	 $unit = "Ban";
      }
      else {      
	 switch($type) {
	  case 1:
	    $dur = ($banlist[ban_end] - $banlist[ban_start]);
	    $unit = "Seconds";
	    $s = "SELECTED";
	    break;
	  case 2:
	    $dur = ($banlist[ban_end] - $banlist[ban_start]) / 60;
	    $unit = "Minutes";
	    $m = "SELECTED";
	    break;
	  case 3:
	    $dur = ($banlist[ban_end] - $banlist[ban_start]) / 3600;
	    $unit = "Hours";
	    $h = "SELECTED";
	    break;
	  case 4:
	    $dur = ($banlist[ban_end] - $banlist[ban_start]) / 86400;
	    $unit = "Days";
	    $d = "SELECTED";
	    break;
	  case 5:
	    $dur = ($banlist[ban_end] - $banlist[ban_start]) / 31536000;
	    $unit = "Years";
	    $y = "SELECTED";
	    break;
	 }
      }
     if($unit != "Ban") {
	 echo "<td align=\"center\"><input type=\"text\" name=\"dur\" size=\"".strlen($dur)."\" maxlengh=\"25\" value=\"$dur\">\n";
	 echo "<select name=\"unit\"><option value=\"1\" $s>Seconds</option>
		<option value=\"2\" $m>Minutes</option>
		<option value=\"3\" $h>Hours</option>
		<option value=\"4\" $d>Days</option>
		<option value=\"5\" $y>Years</option></select></td>";
      }
      else {
	 echo "<td align=\"center\">$dur $unit</td>";
      }
      echo "<td align=\"center\"><input type=\"HIDDEN\" name=\"ban_id\" value=\"$banlist[ban_id]\">";
      echo "<input type=\"submit\" name=\"edit\" value=\"Edit\"></td>";
      echo "<input type=\"hidden\" name=\"mode\" value=\"$mode\">"; 
      echo "<td align=\"center\"><br><input type=\"submit\" name=\"del\" value=\"Delete\"></form></td>";
      echo "</tr>";
   }
?>

     
<TR BGCOLOR="<?php echo $color1?>" ALIGN="CENTER">
	<TD COLSPAN="4"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Add a ban<BR>Use the following form to add IPs or Usernames to the banlist.<br>
     To ban a range of IPs simply do not enter the final IP number ie: 192.168.1. Will ban 192.168.1.0-255<br>
     Bans will be automaticly removed from the database when they expire, to create a perminant ban simply enter nothing in the duration field.</FONT></TD>
</TR>
<tr bgcolor="<?php echo $color1?>" ALIGN="CENTER">
     <td>IP/Username</td>
     <td>Duration</td>
     <td colspan="2">Add</td>
</tr>     
<TR BGCOLOR="<?php echo $color2?>" ALIGN="CENTER">
	<TD><FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
	    <INPUT TYPE="TEXT" NAME="ipuser" MAXLENGTH="50" SIZE="25">&nbsp;
            <select name="banby"><option value="1">IP address</option><option value="2">Username</option></select>
	</TD>
        <td><input type="text" name="duration" maxlength="32" size="15">&nbsp;
            <select name="durtype"><option value="1">Seconds</option>
                                   <option value="2">Minutes</option>
                                   <option value="3">Hours</option>
                                   <option value="4">Days</option>
                                   <option value="5">Years</option></select>
        </td>
	<TD COLSPAN="2"><INPUT TYPE="HIDDEN" NAME="mode" VALUE="<?php echo $mode?>">
	    <br><INPUT TYPE="SUBMIT" NAME="add" VALUE="Add Ban"></FORM>
	</TD>
</TR>
</TABLE>
</TABLE>
     
   
   
   
<?php   
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
