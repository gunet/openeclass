<?php
/***************************************************************************
                          admin_priv_forums.php  -  description
                             -------------------
    begin                : Thu 12 Jan 2001
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

if($login) 
{
	// Try to log the user in with the given username and password.
	
	if ($username == '') 
	{
		die("You have to enter your username. Go back and do so.");
	}
	if ($password == '')
	{
		die("You have to enter your password. Go back and do so.");
	}
	if (!check_username($username, $db)) 
	{
		die("Invalid username \"$username\". Go back and try again.");
	}
	if (!check_user_pw($username, $password, $db))
	{
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
else if(!$user_logged_in) 
{
	$pagetitle = "Forum Administration";
	$pagetype = "admin";
	include('../page_header.'.$phpEx);
   
?>
	<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="<?php echo $TableWidth?>">
		<TR>
			<TD BGCOLOR="<?php echo $table_bgcolor?>">
				<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
					<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
						<TD>
							<P><BR><FONT FACE="<?php echo $FontFace?>" SIZE="<? echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
							Please enter your username and password to login.<BR>
							<i>(NOTE: You MUST have cookies enabled in order to login to the administration section of this forum)</i><BR>
						
							<FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
								<b>User Name: </b><INPUT TYPE="TEXT" NAME="username" SIZE="25" MAXLENGTH="40" VALUE="<?php echo $userdata[username]?>"><BR>
								<b>Password: </b><INPUT TYPE="PASSWORD" NAME="password" SIZE="25" MAXLENGTH="25"><br><br>
								<INPUT TYPE="SUBMIT" NAME="login" VALUE="Submit">&nbsp;&nbsp;&nbsp;<INPUT TYPE="RESET" VALUE="Clear">
							</FORM>
						</TD>
					</TR>
				</TABLE>
			</TD>
		</TR>
	</TABLE>

<?php
	include('../page_tail.'.$phpEx);
	exit();
}
else if($user_logged_in && $userdata[user_level] == 4)
{
	$pagetitle = "Forum Administration";
	$pagetype = "admin";
	include('../page_header.'.$phpEx);
	
	if (!$op)
	{
		// No opcode passed. Show list of private forums.
?>
		
	<FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
		<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%">
			<TR>
				<TD BGCOLOR="<?php echo $table_bgcolor?>">
					<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
						<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
							<TD ALIGN="CENTER" COLSPAN="2">
								<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
									<B>Select a Forum to Edit</B>
								</FONT>
							</TD>
						</TR>
						<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
							<TD ALIGN="CENTER" COLSPAN="2">
								<SELECT NAME="forum" SIZE="0">
	<?php

		$sql = "SELECT forum_name, forum_id FROM forums WHERE (forum_type = 1) ORDER BY forum_id";
		if(!$result = mysql_query($sql, $db))
		{
			die ("Error getting forum list from database. \n");
		}
		
		if($myrow = mysql_fetch_array($result)) 
		{
			do 
			{
				$name = stripslashes($myrow[forum_name]);
				echo "<OPTION VALUE=\"$myrow[forum_id]\">$name</OPTION>\n";
			} 
			while($myrow = mysql_fetch_array($result));
		}
		else 
		{
			echo "<OPTION VALUE=\"-1\">No Forums in Database</OPTION>\n";
		}
	
?>
								</SELECT>
							</TD>
						</TR>
						<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
							<TD ALIGN="CENTER" COLSPAN="2">
								<INPUT TYPE="HIDDEN" NAME="op" VALUE="showform">
								<INPUT TYPE="SUBMIT" NAME="submit" VALUE="Edit">&nbsp;&nbsp;
							</TD>
						</TR>
					</TABLE>
				</TD>
			</TR>
		</TABLE>
	</FORM>

<?php	
	
	}
	else
	{
		// Opcode exists. See what it is, do stuff.
		
		
		if ($op == "adduser")
		{
			// Add user(s) to the list for this forum.
			if ($userids)
			{
				while(list($null, $curr_userid) = each($_POST["userids"]))
				{
					$sql = "INSERT INTO forum_access (forum_id, user_id, can_post) VALUES ($forum, $curr_userid, 0)";
					if (!$result = mysql_query($sql, $db))
					{
						die("Error inserting to DB.\n");
					}
				}
			}	
			$op = "showform";
		
		}
		else if ($op == "deluser")
		{
			// Remove a user from the list for this forum.
			$sql = "DELETE FROM forum_access WHERE (forum_id = $forum) AND (user_id = $op_userid)";
			if (!$result = mysql_query($sql, $db))
			{
				die("Error deleting from database.\n");
			}
			
			$op = "showform";
			
		}
		else if ($op == "clearusers")
		{
			// Remove all users from the list for this forum.
			$sql = "DELETE FROM forum_access WHERE (forum_id = $forum)";
			if (!$result = mysql_query($sql, $db))
			{
				die("Error deleting from database.\n");
			}
			
			$op = "showform";
		}
		else if ($op == "grantuserpost")
		{
			// Add posting rights for this user in this forum.
			$sql = "UPDATE forum_access SET can_post=1 WHERE (forum_id = $forum) AND (user_id = $op_userid)";
			if (!$result = mysql_query($sql, $db))
			{
				die("Error updating database.\n");
			}

			$op = "showform";
		
		}
		else if ($op == "revokeuserpost")
		{
			// Revoke posting rights for this user in this forum.
			$sql = "UPDATE forum_access SET can_post=0 WHERE (forum_id = $forum) AND (user_id = $op_userid)";
			if (!$result = mysql_query($sql, $db))
			{
				die("Error updating database.\n");
			}
			
			$op = "showform";
		
		}
		
		// We want this one to be available even after one of the above blocks has executed.
		// The above blocks will set $op to "showform" on success, so it goes right back to the form.
		// Neato. This is really slick.
		if ($op == "showform")
		{
			// Show the form for the given forum.

			$sql = "SELECT forum_name FROM forums WHERE (forum_id = $forum)";
			if ((!$result = mysql_query($sql, $db)) || ($forum == -1))
			{
				die("Couldn't find forum.\n");
			}
			$forum_name = "";
			if ($row = mysql_fetch_array($result))
			{
				$forum_name = $row[forum_name];
			}
?>			
	 <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="<?php echo $TableWidth?>">
		<TR>
			<TD BGCOLOR="<?php echo $table_bgcolor?>">
				<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
					<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
		     <td colspan="3" align="center"><font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">Editing Forum Permissions for: <b><?php echo $forum_name?></b></font></td>
		     </tr>
		     <tr>
		     <td bgcolor="<?php echo $color1?>" align="center" width="40%">
			<FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
		     <font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
		         <b>Users Without Access:</b>
		     </font>
		     </TD>
		     <TD bgcolor="<?php echo $color2?>" align="center" width="20%">
		        &nbsp;
		     </TD>
		     <TD bgcolor="<?php echo $color1?>" align="center">
		     <font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
		        <b>Users With Access:</b>
		     </font>
		     </TD>
		     </TR>
		     
		     <TR>
		      <TD VALIGN="TOP" bgcolor="<?php echo $color1?>" align="center" width="40%">
		     <font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
		     <SELECT NAME="userids[]" SIZE="10" MULTIPLE>
<?php
			$sql = "SELECT u.user_id FROM users u, forum_access f WHERE (u.user_id = f.user_id) AND (f.forum_id = $forum)";
			if (!$result = mysql_query($sql, $db))
			{
				die("Error getting current user list.\n");
			}
			
			$current_users = Array();
			
			while ($row = mysql_fetch_array($result))
			{
				$current_users[] = $row[user_id];
			}
			
			$sql = "SELECT user_id, username FROM users WHERE (user_id != -1) AND (user_level != -1) ";
			while(list($null, $curr_userid) = each($current_users))
			{
	 			$sql .= "AND (user_id != $curr_userid) ";
      	}
      	$sql .= "ORDER BY username ASC";
 
      	if (!$result = mysql_query($sql, $db))
      	{
      		die("Error getting user list from db.\n");
      	}
      	while ($row = mysql_fetch_array($result))
      	{
?>      	
	     <OPTION VALUE="<?php echo $row[user_id] ?>"> <?php echo $row[username] ?> </OPTION>
<?php      	
      	}
?>	
							</SELECT>
						</TD>
						<TD bgcolor="<?php echo $color2?>" align="center">
		                                        <font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
							<INPUT TYPE="HIDDEN" NAME="op" VALUE="adduser">
							<INPUT TYPE="HIDDEN" NAME="forum" VALUE="<?php echo $forum ?>">
							<INPUT TYPE="SUBMIT" NAME="submit" VALUE="Add Users -->">
							<br><br>
							<b><A HREF="<?php echo $PHP_SELF ?>?forum=<?php echo $forum ?>&op=clearusers">Clear all users</A></b>
		                                        </font>
						</TD>
						<TD VALIGN="TOP" bgcolor="<?php echo $color1?>" align="center">
<?php
			$sql = "SELECT u.username, u.user_id, f.can_post FROM users u, forum_access f WHERE (u.user_id = f.user_id) AND (f.forum_id = $forum) ORDER BY u.user_id ASC";
			if (!$result = mysql_query($sql, $db))
			{
				die ("Error getting userlist from DB.\n");
			}
?>			
							<TABLE BORDER="0" CELLPADDING="10" CELLSPACING="0">
								
<?php									
			while ($row = mysql_fetch_array($result))
			{
				$post_text = ($row[can_post]) ? "can" : "can't";
				$post_text .= " post";
				
				$post_toggle_link = "<A HREF=\"$PHP_SELF?forum=$forum&op_userid=$row[user_id]&op=";
				if ($row[can_post])
				{
					$post_toggle_link .= "revokeuserpost\">revoke posting</A>";
				}
				else
				{
					$post_toggle_link .= "grantuserpost\">grant posting</A>";
				}
				
				$remove_link = "<A HREF=\"$PHP_SELF?forum=$forum&op=deluser&op_userid=$row[user_id]\">remove</A>";
?>
								<TR>
									<TD>
			                                                <font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
										<b><?php echo $row[username]?></b>
									</font>
			                                                </TD>
			                                                
									<TD>
			                                                <font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
										<?php echo $post_text ?>
			                                                </font>
									</TD>
									<TD>
			                                                <font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
										<?php echo $post_toggle_link ?>
			                                                </font>
									</TD>
									<TD>
			                                                <font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
										<?php echo $remove_link ?>
			                                                </font>
									</TD>
								<TR>
<?php			
			}
?>							
							</TABLE>
						</TD>
					</TR>
				</TABLE>
			</FORM>
			</table></table>
<?php			
		} // end of big opcode if/else block.
		
	
	}
	
}

include('../page_tail.'.$phpEx);
?>
