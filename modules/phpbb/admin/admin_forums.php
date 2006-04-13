<?php
/***************************************************************************
                          admin_forums.php  -  description
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

/**
* Thursday, July 20, 2000 - Yokhannan - I added [$url_admin] to most of the links.
* I fixed a few typo errors
*
* 09/13/2000 - John B. Abela (abela@4cm.com)
* 	Added Some Cosmetic HTML Code, fixed a Hyperlink typo.
*/
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
 case 'editforum':
   if($save) {
      if(!$delete) {
	 $name = addslashes($name);
	 $desc = addslashes($desc);

	 $sql = "UPDATE forums SET forum_name = '$name', forum_desc = '$desc', forum_type = '$type', cat_id = '$cat', forum_access = '$forum_access' WHERE forum_id = '$forum'";

	 if(!$r = mysql_query($sql, $db))
	   die("Error - could not update the database, please go back and try again.");
	 $count = 0;
	 if(isset($mods)) {
	    while(list($null, $mod) = each($_POST["mods"])) {
	       $mod_data = get_userdata_from_id($mod, $db);
	       if($mod_data[user_level] < 2) {
		  if(!isset($user_query))
		    $user_query = "UPDATE users SET user_level = 2 WHERE ";
		  if($count > 0)
		    $user_query .= "OR ";
		  $user_query .= "user_id = '$mod' ";
		  $count++;
	       }
	       $mod_query = "INSERT INTO forum_mods (forum_id, user_id) VALUES ('$forum', '$mod')";
	       if(!mysql_query($mod_query, $db))
		 die("Mod Query Error!<BR>".mysql_error($db)."<BR>$mod_query");
	    }
	 }

	 if(!isset($mods)) {
	    $current_mods = "SELECT count(*) AS total FROM forum_mods WHERE forum_id = '$forum'";
	    $r = @mysql_query($current_mods, $db);
	    list($total) = mysql_fetch_array($r);
	 }
	 else
	   $total = count($mods) + 1;

	 if(isset($rem_mods) && $total > 1) {
	    while(list($null, $mod) = each($_POST["rem_mods"])) {
	       $rem_query = "DELETE FROM forum_mods WHERE forum_id = '$forum' AND user_id = '$mod'";
	       if(!mysql_query($rem_query))
		 die("Error removing moderators for forum!<BR>".mysql_error($db)."<BR>$rem_query");
	    }
	 }
	 else {
	    if(isset($rem_mods))
	      $mod_not_removed = 1;
	 }
	 if(isset($user_query)) {
	    if(!mysql_query($user_query, $db))
	      die("User Error!<BR>".mysql_error($db)."<BR>$user_query");
	 }

	 echo "<TABLE width=\"95%\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" bordercolor=\"$table_bgcolor\">";
	 echo "<tr><td align=\"center\" width=\"100%\" bgcolor=\"$color1\"><font face=\"$FontFace\" size=\"$FontSize1\" color=\"$textcolor\"><B>Forum Updated.</B></font></td>";
	 if($mod_not_removed)
	   echo "<tr><td align=\"center\" width=\"100%\" bgcolor=\"$color1\"><font face=\"$FontFace\" size=\"$FontSize1\" color=\"$textcolor\"><b>However the selected moderator(s) have not be removed because if they had been there would no longer be any moderators on this forum.</b></font></td>";

	 echo "</tr><TR><TD><TABLE width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><TR>";
	 echo "<td align=\"center\" width=\"100%\" bgcolor=\"$color2\"><font face=\"$FontFace\" size=\"$FontSize1\" color=\"$textcolor\"><P><BR>&nbsp;&nbsp;Click <a href=\"$url_admin_index\">here</a> to return to the Administration Panel.<P>Click <a href=\"$url_phpbb_index\">here</a> to return to the forum index.</font><P><BR><P></TD>";
	 echo "</TR></table></TD></TR></TABLE>";
      }
      else {
      	$sql = "SELECT post_id FROM posts WHERE forum_id = $forum";
    		if(!$r = mysql_query($sql, $db))
	 		  die("Error could not delete the posts in this forum");
	 		$sql = "DELETE FROM posts_text WHERE ";
	 		$looped = FALSE;
	 		while($ids = mysql_fetch_array($r))
	 		{
	 			if($looped == TRUE)
	 			{
	 				$sql .= " OR ";
	 			}
	 			$sql .= "post_id = ".$ids["post_id"]." ";
	 			$looped = TRUE;
	 		}
			if(!$r = mysql_query($sql, $db))
	 		  die("Error could not delete the posts in this forum");

	 		$sql = "DELETE FROM posts WHERE forum_id = '$forum'";
	 		if(!$r = mysql_query($sql, $db))
	   		die("Error could not delete the posts in this forum");

	 		$sql = "DELETE FROM topics WHERE forum_id = '$forum'";
	 		if(!$r = mysql_query($sql, $db))
	   		die("Error could not delete the topics in this forum");

			 $sql = "DELETE FROM forums WHERE forum_id = '$forum'";
	 		if(!$r = mysql_query($sql, $db))
	   		die("Error could not delete the forum");

	 		$sql = "DELETE FROM forum_mods WHERE forum_id = '$forum'";
	 		if(!$r = mysql_query($sql, $db))
	   		die("Error could not delete the forum");

	 		echo "<TABLE width=\"95%\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" bordercolor=\"$table_bgcolor\">";
	 		echo "<tr><td align=\"center\" width=\"100%\" bgcolor=\"$color1\"><font face=\"$FontFace\" size=\"$FontSize1\" color=\"$textcolor\"><B>Forum Removed.</B></font></td>";
	 		echo "</tr><TR><TD><TABLE width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><TR>";
	 		echo "<td align=\"center\" width=\"100%\" bgcolor=\"$color2\"><font face=\"$FontFace\" size=\"$FontSize1\" color=\"$textcolor\"><P><BR>&nbsp;&nbsp;Forum removed from database along with all its posts.<P>Click <a href=\"$url_admin_index\">here</a> to return to the Administration Panel.<P>Click <a href=\"$url_phpbb_index\">here</a> to return to the forum index.</font><P><BR><P></TD>";
	 		echo "</TR></table></TD></TR></TABLE>";
      }
   }
   if($submit && !$save) {
      $sql = "SELECT * FROM forums WHERE forum_id = '$forum'";
      if(!$result = mysql_query($sql, $db))
	die("Error connecting to the database.");
      if(!$myrow = mysql_fetch_array($result)) {
	 echo "No such forum";
	 include('page_tail.'.$phpEx);
      }
      $name = stripslashes($myrow[forum_name]);
      $desc = stripslashes($myrow[forum_desc]);
      ?>
<FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
        <TD ALIGN="CENTER" COLSPAN="2"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><B>Edit This Forum</B></FONT></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
        <TD COLSPAN=2><INPUT TYPE="CHECKBOX" NAME="delete" VALUE="1"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"> Delete this forum (This will also remove all posts in this forum!)</FONT></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
        <TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Forum Name:</FONT></TD>
        <TD><INPUT TYPE="TEXT" NAME="name" SIZE="40" MAXLENGTH="150" VALUE="<?php echo $name?>"></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
        <TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Forum Description:</FONT></TD>
        <TD><TEXTAREA NAME="desc" ROWS="15" COLS="45" WRAP="VIRTUAL"><?php echo $desc?></TEXTAREA></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
        <TD valign="top"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Moderator(s):</FONT></TD>
        <TD><b>Current:</b><BR>
<?php
	$sql = "SELECT u.username, u.user_id FROM users u, forum_mods f WHERE f.forum_id = '$forum' AND u.user_id = f.user_id";
      if(!$r = mysql_query($sql, $db))
	die("Error connecting to the database.");
      if($row = mysql_fetch_array($r)) {
	 do {
	    echo "$row[username] (<input type=\"checkbox\" name=\"rem_mods[]\" value=\"$row[user_id]\"> Remove)<BR>";
	    $current_mods[] = $row[user_id];
	 } while($row = mysql_fetch_array($r));
	 echo "<BR>";
      }
      else {
	 echo "No Moderators Assigned<BR><BR>\n";
      }
?>
	<b>Add:</b><BR>
	<SELECT NAME="mods[]" size="5" multiple>
<?php
	$sql = "SELECT user_id, username FROM users WHERE user_id != -1 AND user_level != -1 ";
      while(list($null, $currMod) = each($current_mods)) {
	 $sql .= "AND user_id != $currMod ";
      }
      $sql .= "ORDER BY username";
      if(!$r = mysql_query($sql, $db))
	die("An Error Occurred<HR>Could not connect to the database. Please check the config file.");
      if($row = mysql_fetch_array($r)) {
	 do {
	    $s = "";
	    if($row[user_id] == $myrow[forum_moderator])
	      $s = "SELECTED";
	    echo "<OPTION VALUE=\"$row[user_id]\" $s>$row[username]</OPTION>\n";
	 } while($row = mysql_fetch_array($r));
      }
      else {
	 echo "<OPTION VALUE=\"0\">None</OPTION>\n";
      }
?>
        </SELECT></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
        <TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Category:</FONT></TD>
        <TD><SELECT NAME="cat">
<?php
	$sql = "SELECT * FROM catagories";
      if(!$r = mysql_query($sql, $db))
	die("An Error Occurred<HR>Could not connect to the database. Please check the config file.");
      if($row = mysql_fetch_array($r)) {
	 do {
	    $s = "";
	    if($row[cat_id] == $myrow[cat_id])
						$s = "SELECTED";
	    echo "<OPTION VALUE=\"$row[cat_id]\" $s>$row[cat_title]</OPTION>\n";
	 } while($row = mysql_fetch_array($r));
      }
      else {
	 echo "<OPTION VALUE=\"0\">None</OPTION>\n";
      }
?>
        </SELECT></TD>
<?php
if($myrow[forum_access] == 1)
    $access1 = "SELECTED";
if($myrow[forum_access] == 2)
    $access2 = "SELECTED";
if($myrow[forum_access] == 3)
    $access3 = "SELECTED";
?>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
         <TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Access Level:</font></TD>
	 <TD><SELECT NAME="forum_access">
	     <OPTION VALUE="2" <?php echo $access2?>>Anonymous Posting</OPTION>
	     <OPTION VALUE="1" <?php echo $access1?>>Registered users only</OPTION>
	     <OPTION VALUE="3" <?php echo $access3?>>Moderators/Administrators only</OPTION>
	     </SELECT>
        </TD>
</TR>


<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Type:</FONT></TD>
	<TD><SELECT NAME="type">
<?php
	if($myrow[forum_type] == 1)
		$priv = "SELECTED";
	else
		$pub = "SELECTED";
?>
	<OPTION VALUE="0" <?php echo $pub?>>Public</OPTION>
	<OPTION VALUE="1" <?php echo $priv?>>Private</OPTION>
	</SELECT>
	</TD>
</TR>
<?php

?>
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
        <TD ALIGN="CENTER" COLSPAN="2">
                <INPUT TYPE="HIDDEN" NAME="mode" VALUE="editforum">
                <INPUT TYPE="HIDDEN" NAME="forum" VALUE="<?php echo $forum?>">
                <INPUT TYPE="SUBMIT" NAME="save" VALUE="Save Changes">&nbsp;&nbsp;
                <INPUT TYPE="RESET" VALUE="Clear">
        </TD>
</TR>
</TR>
</TABLE></TD></TR></TABLE>

<?php
		}
		if(!$submit && !$save) {
?>
<FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	<TD ALIGN="CENTER" COLSPAN="2"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><B>Select a Forum to Edit</B><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD ALIGN="CENTER" COLSPAN="2"><SELECT NAME="forum" SIZE="0">
	<?php

	$sql = "SELECT forum_name, forum_id FROM forums ORDER BY forum_id";
	if($result = mysql_query($sql, $db)) {
		if($myrow = mysql_fetch_array($result)) {
			do {
				$name = stripslashes($myrow[forum_name]);
				echo "<OPTION VALUE=\"$myrow[forum_id]\">$name</OPTION>\n";
			} while($myrow = mysql_fetch_array($result));
		}
		else {
			echo "<OPTION VALUE=\"-1\">No Forums in Database</OPTION>\n";
		}
	}
/*
	Your Mother is a Monkey!
*/
	else {
		echo "<OPTION VALUE=\"-1\">Database Error</OPTION>\n";
	}
/*
	Yea, Well... your Father is a Ape!

	How dare you! My father is not an Ape, he's a Buffalo, and proud of it!
*/
	?>
	</SELECT></TD>
</TR>
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	<TD ALIGN="CENTER" COLSPAN="2">
		<INPUT TYPE="HIDDEN" NAME="mode" VALUE="editforum">
		<INPUT TYPE="SUBMIT" NAME="submit" VALUE="Edit">&nbsp;&nbsp;
	</TD>
</TR>
</TR>
</TABLE></TD></TR></TABLE>
<?php
		}
   break;
   case 'editcat':
   	if($submit && $save)
   	{
			$new_title = addslashes($new_title);
			$sql = "UPDATE catagories SET cat_title = '$new_title' WHERE cat_id = $cat_id";
			if(!$result = mysql_query($sql, $db))
   		{
   			die("Could not get catagory data!<br>$sql");
   		}
   		else
   		{
   			echo "<TABLE width=\"95%\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" bordercolor=\"$table_bgcolor\">";
	 			echo "<tr><td align=\"center\" width=\"100%\" bgcolor=\"$color1\"><font face=\"$FontFace\" size=\"$FontSize1\" color=\"$textcolor\"><B>Category Updated.</B></font></td>";
	 			echo "</tr><TR><TD><TABLE width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><TR>";
	 			echo "<td align=\"center\" width=\"100%\" bgcolor=\"$color2\"><font face=\"$FontFace\" size=\"$FontSize1\" color=\"$textcolor\"><P>Click <a href=\"$url_admin_index\">here</a> to return to the Administration Panel.<P>Click <a href=\"$url_phpbb_index\">here</a> to return to the forum index.</font><P><BR><P></TD>";
	 			echo "</TR></table></TD></TR></TABLE>";
	 		}

   	}
   	else if($submit)
   	{
   		$sql = "SELECT cat_title FROM catagories WHERE cat_id = '$cat'";
   		if(!$result = mysql_query($sql, $db))
   		{
   			die("Could not get catagory data!<br>$sql");
   		}
   		$cat_data = mysql_fetch_array($result);
   		$cat_title = stripslashes($cat_data["cat_title"]);
?>
<FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	<TD ALIGN="CENTER" COLSPAN="2"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><B>Editing Category: <?php echo $cat_title ?></B><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<td>Category Title:</td>
	<td><input type="text" name="new_title" value="<?php echo $cat_title ?>" size="45" maxlength="100"></td>
</tr>
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	<TD ALIGN="CENTER" COLSPAN="2">
		<INPUT TYPE="HIDDEN" NAME="mode" VALUE="editcat">
		<input type="hidden" name="save" value="TRUE">
		<input type="hidden" name="cat_id" value="<?php echo $cat?>">
		<INPUT TYPE="SUBMIT" NAME="submit" VALUE="Save Changes">
	</TD>
</TR>
</TR>
</TABLE></TD></TR></TABLE>
<?php
   	}
   	else {
   		$sql = "SELECT cat_id, cat_title FROM catagories ORDER BY cat_order";
   		if(!$result = mysql_query($sql, $db))
   		{
   			die("Could not get catagory list!");
   		}
?>
<FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	<TD ALIGN="CENTER" COLSPAN="2"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><B>Select a Category to Edit</B><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD ALIGN="CENTER" COLSPAN="2"><SELECT NAME="cat" SIZE="0">
<?php
			while($cat_data = mysql_fetch_array($result))
			{
				echo "<option value=\"".$cat_data["cat_id"]."\">".stripslashes($cat_data["cat_title"])."</option>\n";
			}
?>
</select></td>
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	<TD ALIGN="CENTER" COLSPAN="2">
		<INPUT TYPE="HIDDEN" NAME="mode" VALUE="editcat">
		<INPUT TYPE="SUBMIT" NAME="submit" VALUE="Edit">&nbsp;&nbsp;
	</TD>
</TR>
</TR>
</TABLE></TD></TR></TABLE>
<?php
   	}
   break;
 case 'remcat':
   if($submit) {
      $sql = "DELETE FROM catagories WHERE cat_id = '$cat'";
      if(!$r = mysql_query($sql, $db))
	die("Error Deleteing Category<BR>".mysql_error($db));
      echo "<TABLE width=\"95%\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" bordercolor=\"$table_bgcolor\">";
      echo "<tr><td align=\"center\" width=\"100%\" bgcolor=\"$color1\"><font face=\"$FontFace\" size=\"$FontSize1\" color=\"$textcolor\"><B>Category Created.</B></font></td>";
      echo "</tr><TR><TD><TABLE width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><TR>";
      echo "<td align=\"center\" width=\"100%\" bgcolor=\"$color2\"><font face=\"$FontFace\" size=\"$FontSize1\" color=\"$textcolor\"><P><BR>&nbsp;&nbsp;Click <a href=\"$url_admin_index\">here</a> to return to the Administration Panel.<P>Click <a href=\"$url_phpbb_index\">here</a> to return to the forum index.</font><P><BR><P></TD>";
      echo "</TR></table></TD></TR></TABLE>";

   }
   else {
?>
	<FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
	<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
	<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
	<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	<TD ALIGN="CENTER" COLSPAN="2"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><B>Remove a Category</B></FONT></TD>
	</TR>
	<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	<TD ALIGN="CENTER" COLSPAN="2"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><i>Note: This will NOT remove the forums under the category, you must do that via the Edit Forum section.</i></FONT></TD>
	</TR>
	<TR BGCOLOR="<?php echo $color2?>">
	<TD ALIGN="CENTER" COLSPAN="2"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
	<SELECT NAME="cat">

<?php
      $sql = "SELECT * FROM catagories ORDER BY cat_title";
      if(!$r = mysql_query($sql, $db))
	die("Error conencting to the database!");
      while($m = mysql_fetch_array($r)) {
	 echo "<OPTION VALUE=\"$m[cat_id]\">".stripslashes($m[cat_title])."</OPTION>\n";
      }
?>
	</SELECT>
	<INPUT TYPE="HIDDEN" NAME="mode" VALUE="<?php echo $mode ?>"></TD>
	</TR>
	<TR BGCOLOR="<?php echo $color1?>">
	<TD ALIGN="CENTER" COLSPAN="2"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
	<INPUT TYPE="SUBMIT" NAME="submit" VALUE="Remove Category"></TD></TR>
	</TABLE></TABLE></FORM>
<?php
   }
   break;
 case 'addcat':
   if($submit) {
      $sql = "SELECT max(cat_order) AS highest FROM catagories";
      if(!$r = mysql_query($sql, $db))
	die("Error - Could not query the DB");
      list($highest) = mysql_fetch_array($r);
      $highest++;
      $title = addslashes($title);
      $sql = "INSERT INTO catagories (cat_title, cat_order) VALUES ('$title', '$highest')";
      if(!$result = mysql_query($sql, $db))
	die("Error - Could not insert category into the database, please go back and try again.");
      echo "<TABLE width=\"95%\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" bordercolor=\"$table_bgcolor\">";
      echo "<tr><td align=\"center\" width=\"100%\" bgcolor=\"$color1\"><font face=\"$FontFace\" size=\"$FontSize1\" color=\"$textcolor\"><B>Category Created.</B></font></td>";
      echo "</tr><TR><TD><TABLE width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><TR>";
      echo "<td align=\"center\" width=\"100%\" bgcolor=\"$color2\"><font face=\"$FontFace\" size=\"$FontSize1\" color=\"$textcolor\"><P><BR>&nbsp;&nbsp;Click <a href=\"$url_admin_index\">here</a> to return to the Administration Panel.<P>Click <a href=\"$url_phpbb_index\">here</a> to return to the forum index.</font><P><BR><P></TD>";
      echo "</TR></table></TD></TR></TABLE>";
   }
   else {
?>
<FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	<TD ALIGN="CENTER" COLSPAN="2"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><B>Create a New Category</B></FONT></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Category Title:</FONT></TD>
	<TD><INPUT TYPE="TEXT" NAME="title" SIZE="40" MAXLENGTH="100"></TD>
</TR>
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	<TD ALIGN="CENTER" COLSPAN="2">
		<INPUT TYPE="HIDDEN" NAME="mode" VALUE="addcat">
		<INPUT TYPE="SUBMIT" NAME="submit" VALUE="Create Category">&nbsp;&nbsp;
		<INPUT TYPE="RESET" VALUE="Clear">
	</TD>
</TR>
</TR>
</TABLE></TD></TR></TABLE>
<?
		}
   break;
 case 'addforum':
   if($submit) {
      if($name == '' || $desc == '' || !is_array($mods))
			die("You did not fill out all the parts of the form.<br>Did you assign at least one moderator? Please go back and correct the form.");
      $desc = str_replace("\n", "<BR>", $desc);
      $desc = addslashes($desc);
      $name = addslashes($name);

		$sql = "INSERT INTO forums (forum_name, forum_desc, forum_access, cat_id, forum_type) VALUES ('$name', '$desc', '$forum_access', '$cat', '$type')";

      if(!$result = mysql_query($sql, $db))
			die("An Error Occurred<HR>Could not contact the database. Please check your config file.<BR>".mysql_error()."<BR>$sql");
      $forum = mysql_insert_id($db);
      $count = 0;

      while(list($mod_number, $mod) = each($_POST["mods"])) {
	 		$mod_data = get_userdata_from_id($mod, $db);

	 		if($mod_data[user_level] < 2) {
	    		if(!isset($user_query))
	      		$user_query = "UPDATE users SET user_level = 2 WHERE ";
	    	if($count > 0)
	      	$user_query .= "OR ";
	    	$user_query .= "user_id = '$mod' ";
	    	$count++;
	 		}
	 		$mod_query = "INSERT INTO forum_mods (forum_id, user_id) VALUES ('$forum', '$mod')";
	 		if(!mysql_query($mod_query, $db))
	   		die("Mod Query Error!<BR>".mysql_error($db)."<BR>$mod_query");
    	}

    if(isset($user_query)) {
	 	if(!mysql_query($user_query, $db))
	   	die("User Error!<BR>".mysql_error($db)."<BR>$user_query");
    }
      echo "<TABLE width=\"95%\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" bordercolor=\"$table_bgcolor\">";
      echo "<tr><td align=\"center\" width=\"100%\" bgcolor=\"$color1\"><font face=\"$FontFace\" size=\"$FontSize2\" color=\"$textcolor\"><B>Forum Created.</B></font></td>";
      echo "</tr><TR><TD><TABLE width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><TR>";
      echo "<td align=\"center\" width=\"100%\" bgcolor=\"$color2\"><font face=\"$FontFace\" size=\"$FontSize1\" color=\"$textcolor\"><P><BR>&nbsp;&nbsp;Click <a href=\"$url_admin_index\">here</a> to return to the Administration Panel.<P>Click <a href=\"$url_phpbb/viewforum.$phpEx?forum=$forum\">here</a> to view the forum  you just created.</font><P><BR><P></TD>";
      echo "</TR></table></TD></TR></TABLE>";
   }
   else {
      $sql = "SELECT count(*) AS total FROM catagories";
      if(!$r = mysql_query($sql, $db))
	die("Error querying the database!");
      list($total) = mysql_fetch_array($r);
      if($total < 1 || !isset($total))
	die("Error, you must add a category before you add forums");
      ?>
<FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	<TD ALIGN="CENTER" COLSPAN="2"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><B>Create a New Forum</B></FONT></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Forum Name:</FONT></TD>
	<TD><INPUT TYPE="TEXT" NAME="name" SIZE="40" MAXLENGTH="150"></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Forum Description:</FONT></TD>
	<TD><TEXTAREA NAME="desc" ROWS="15" COLS="45" WRAP="VIRTUAL"></TEXTAREA></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Moderator:</FONT></TD>
	<TD><SELECT NAME="mods[]" size="5" multiple>
<?php
	$sql = "SELECT user_id, username FROM users WHERE user_id != -1 AND user_level != -1 ORDER BY username";
      if(!$result = mysql_query($sql, $db))
	die("An Error Occurred<HR>Could not connect to the database. Please check the config file.");
      if($myrow = mysql_fetch_array($result)) {
	 do {
	    echo "<OPTION VALUE=\"$myrow[user_id]\">$myrow[username]</OPTION>\n";
	 } while($myrow = mysql_fetch_array($result));
      }
      else {
	 echo "<OPTION VALUE=\"0\">None</OPTION>\n";
      }
?>
	</SELECT></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Catagory:</FONT></TD>
	<TD><SELECT NAME="cat">
<?php
			$sql = "SELECT * FROM catagories";
			if(!$result = mysql_query($sql, $db))
				die("An Error Occurred<HR>Could not connect to the database. Please check the config file.");
			if($myrow = mysql_fetch_array($result)) {
				do {
					echo "<OPTION VALUE=\"$myrow[cat_id]\">$myrow[cat_title]</OPTION>\n";
				} while($myrow = mysql_fetch_array($result));
			}
			else {
				echo "<OPTION VALUE=\"0\">None</OPTION>\n";
			}
?>
	</SELECT></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	 <TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Access Level:</font></TD>
	 <TD><SELECT NAME="forum_access">
	     <OPTION VALUE="2">Anonymous Posting</OPTION>
	     <OPTION VALUE="1">Registered users only</OPTION>
	     <OPTION VALUE="3">Moderators/Administrators only</OPTION>
	     </SELECT>
	 </TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
        <TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Type:</FONT></TD>
        <TD><SELECT NAME="type">
        <OPTION VALUE="0">Public</OPTION>
        <OPTION VALUE="1">Private</OPTION>
        </SELECT>
        </TD>
</TR>
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	<TD ALIGN="CENTER" COLSPAN="2">
		<INPUT TYPE="HIDDEN" NAME="mode" VALUE="addforum">
		<INPUT TYPE="SUBMIT" NAME="submit" VALUE="Create Forum">&nbsp;&nbsp;
		<INPUT TYPE="RESET" VALUE="Clear">
	</TD>
</TR>
</TR>

</TABLE></TD></TR></TABLE>
<?php
		}
   break;
 case 'catorder':
//    update catagories set cat_order = cat_order + 1 WHERE cat_order >= 2; update catagories set cat_order = cat_order - 2 where cat_id = 3;

      if($up) {
	 if($current_order != "1") {
	    $order = $current_order - 1;
	    $sql1 = "UPDATE catagories SET cat_order = $order WHERE cat_id = '$cat_id'";
	    if(!$r = mysql_query($sql1, $db))
	      die("Error connecting to the database<BR>".mysql_error($db));
	    $sql2 = "UPDATE catagories SET cat_order = $current_order WHERE cat_id = '$last_id'";
	    if(!$r = mysql_query($sql2, $db))
	      die("Error connecting to the database<BR>".mysql_error($db));
	    echo "<div align=\"center\"><font size=\"$FontSize4\" face=\"$FontFace\" color=\"$textcolor\">Category Moved Up</font></div><BR>";
	 }
	 else
	   echo "<div align=\"center\"><font size=\"$FontSize4\" face=\"$FontFace\" color=\"$textcolor\">This category is already the highest up.</font></div><br>";

      }
      else if($down) {
	 $sql = "SELECT cat_order FROM catagories ORDER BY cat_order DESC LIMIT 1";
	 if(!$r  = mysql_query($sql, $db))
	   die("Error quering the database");
	 list($last_number) = mysql_fetch_array($r);
	 if($last_number != $current_order) {
	    $order = $current_order + 1;
	    $sql = "UPDATE catagories SET cat_order = $current_order WHERE cat_order = $order";
	    if(!$r  = mysql_query($sql, $db))
	      die("Error quering the database");
	    $sql = "UPDATE catagories SET cat_order = $order where cat_id = $cat_id";
	    if(!$r  = mysql_query($sql, $db))
	      die("Error quering the database");
	    echo "<div align=\"center\"><font size=\"$FontSize4\" face=\"$FontFace\" color=\"$textcolor\">Category Moved Down</font></div><BR>";

	 }
	 else
	   echo "<div align=\"center\"><font size=\"$FontSize4\" face=\"$FontFace\" color=\"$textcolor\">This category is already the lowest down.</font></div><BR>";
      }

?>
     <FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
     <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
     <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
     <TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
     <TD ALIGN="CENTER" COLSPAN="3"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><B>Set Category Ordering</B></FONT><BR>
     The order displayed here is the order the categories will display on the index page. To move a category up in the ordering click 'Move Up' to move it down click 'Move Down'.<BR>
     Each click will move the category 1 place up or down in the ordering.</TD>
     </TR>
     <TR BGCOLOR="<?php echo $color1?>" ALIGN="CENTER">
     <TD>Category</TD><TD>Move Up</TD><TD>Move Down</TD>
     </TR>
<?php
     $sql = "SELECT * FROM catagories ORDER BY cat_order";
   if(!$r = mysql_query($sql, $db)) {
      echo "<TR><TD colspan=\"3\">Error Connecting to the database!</TD></TR>";
      exit();
   }
   while($m = mysql_fetch_array($r)) {
      echo "<!-- New Row -->\n";
      echo "<FORM ACTION=\"$PHP_SELF\" METHOD=\"POST\">\n";
      echo "<tr bgcolor=\"$color2\" align=\"center\">\n";
      echo "<td>".stripslashes($m[cat_title])."</TD>\n";
      echo "<td><input type=\"hidden\" name=\"mode\" value=\"$mode\">\n";
      echo "<input type=\"hidden\" name=\"cat_id\" value=\"$m[cat_id]\">\n";
      echo "<input type=\"hidden\" name=\"last_id\" value=\"$last_id\">\n";
      echo "<input type=\"hidden\" name=\"current_order\" value=\"$m[cat_order]\"><input type=\"submit\" name=\"up\" value=\"Move Up\"></td>\n";
      echo "<td><input type=\"submit\" name=\"down\" value=\"Move Down\"></td></tr></form>\n<!-- End of Row -->\n";
      $last_id = $m[cat_id];
   }
?>
     </TABLE></TABLE>
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
