<?  session_start(); ?>
<?php
/***************************************************************************
                          sendpmsg.php  -  description
                             -------------------
    begin                : Wed June 19 2000
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
 * sendpmsg.php - Nathan Codding
 * - Used for sending private messages between users of the BB.
 */
include('extention.inc');
include('functions.'.$phpEx);
include('config.'.$phpEx);
require('auth.'.$phpEx);
$pagetitle = "Send Private Message";
$pagetype = "sendprivmsg";
include('page_header.'.$phpEx);


if($submit) {
	if($message == '') {
		error_die($l_emptymsg);
	}
	if ($tousername == '') {
		error_die($l_norecipient);
	}
	$touserdata = get_userdata($tousername, $db);
	if(!$touserdata[username]) {
		error_die($l_nouser);
	}

	if (!$user_logged_in) { // don't check this stuff if we have a valid session..
		if($fromusername == '' || $password == '') {
			error_die("$l_userpass $l_tryagain");
		}
		
		$md_pass = md5($password);
		$fromuserdata = get_userdata($fromusername, $db);
		if($md_pass != $fromuserdata["user_password"]) {
			error_die("$l_wrongpass $l_tryagain");
		}
	} else {
		// we have a valid session..
		$fromuserdata = $userdata; // fromuser = current user.
	}
	
	/* correct password or logged-in user, continuing with message send. */

	$is_html_disabled = false;
	if($allow_pmsg_html == 0 && !isset($html)) {
		$message = htmlspecialchars($message);
		$is_html_disabled = true;
	}
	
	if($sig) {
   	$message .= "<BR>__________________<BR>" . $fromuserdata[user_sig];
   }
   if($allow_pmsg_bbcode == 1 && !isset($bbcode)) {
		$message = bbencode($message, $is_html_disabled);
	}
	
	
	// MUST do make_clickable() and smile() before changing \n into <br>.
	$message = make_clickable($message);
	if(!$smile) {
		$message = smile($message);
	}
	
	$message = str_replace("\n", "<BR>", $message);
	$message = addslashes($message);
	$time = date("Y-m-d H:i");
	
	$sql = "INSERT INTO priv_msgs (from_userid, to_userid, msg_time, msg_text) ";
	$sql .= "VALUES ($fromuserdata[user_id], $touserdata[user_id], '$time', '$message')";
	
	if(!mysql_query($sql, $db)) {
		echo $sql . " : " . mysql_error() . "<br>";
		error_die("Could not enter data into the database.");
	}

	echo "<br><TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\" ALIGN=\"CENTER\" VALIGN=\"TOP\" WIDTH=\"$tablewidth\">";
	echo "<TR><TD  BGCOLOR=\"$table_bgcolor\"><TABLE BORDER=\"0\" CALLPADDING=\"1\" CELLSPACING=\"1\" WIDTH=\"100%\">";
	echo "<TR BGCOLOR=\"$color1\" ALIGN=\"LEFT\"><TD><font face=\"Verdana\" size=\"2\"><P>";
	echo "<P><BR><center>";
	echo "$l_stored<br> \n";
	echo "<a href=\"sendpmsg.$phpEx\">$l_sendothermsg</a> <br> \n";
	echo "<p></center></font>";
	echo "</TD></TR></TABLE></TD></TR></TABLE><br>";
	

} else {

/* displaying the form */

?>
<FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
	<TABLE BORDER="0" CELLPADDING="1" CELLSPACEING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
	<TABLE BORDER="0" CALLPADDING="1" CELLSPACEING="1" WIDTH="100%">
	<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
		<TD width=25%>
			<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
			<b><?php echo $l_aboutpost?></b>
			</FONT>
		</TD>
		<TD>
			<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
			<?php echo "$l_regusers $l_cansend"?>
		</TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>"  width=25%>
			<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
			<b><?php echo $l_yourname?>:<b>
			</FONT>
		</TD>
		<TD  BGCOLOR="<?php echo $color2?>">
			<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
<?PHP
	if ($user_logged_in) {
		echo $userdata[username] . " \n";
	} else {
		echo "<INPUT TYPE=\"TEXT\" NAME=\"fromusername\" SIZE=\"25\" MAXLENGTH=\"40\" VALUE=\"$userdata[username]\"> \n";
	}
?>
			</FONT>
		</TD>
	</TR>
<?PHP
	if (!$user_logged_in) { 
		// no session, need a password.
		echo "    <TR ALIGN=\"LEFT\"> \n";
		echo "        <TD BGCOLOR=\"$color1\" width=25%><b>$l_password:</b></TD> \n";
		echo "        <TD BGCOLOR=\"$color2\"><INPUT TYPE=\"PASSWORD\" NAME=\"password\" SIZE=\"25\" MAXLENGTH=\"25\"></TD> \n";
		echo "    </TR> \n";
	}
?>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>"  width=25%>
			<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
			<b><?php echo $l_recptname?>:<b>
			</FONT>
		</TD>
		<TD  BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" NAME="tousername" SIZE="25" MAXLENGTH="40" VALUE="<?php echo $tousername?>"></TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width=25%>
			<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
			<b><?php echo $l_body?>:</b><br><br>
			</FONT>
		<font size=-1>
		<?php
		echo "$l_htmlis: ";
		if($allow_pmsg_html == 1)
			echo "$l_on<BR>\n";
		else
			echo "$l_off<BR>\n";
		echo "$l_bbcodeis:";
		if($allow_pmsg_bbcode == 1)
			echo "$l_on<br>\n";
		else
			echo "$l_off<BR>\n";
		?>		
		</font></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><TEXTAREA NAME="message" ROWS=10 COLS=45 WRAP="VIRTUAL"></TEXTAREA></TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width=25%>
			<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
			<b><?php echo $l_options?>:</b>
			</FONT>
		</TD>
		<TD  BGCOLOR="<?php echo $color2?>" >
			<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
		<?php
			if($allow_pmsg_html == 1) {
				echo "<INPUT TYPE=\"CHECKBOX\" NAME=\"html\">$l_disable $l_html $l_onthispost<BR>";
			}
			if($allow_pmsg_bbcode == 1) {
				echo "<INPUT TYPE=\"CHECKBOX\" NAME=\"bbcode\">$l_disable <a href=\"$bbref_url\" target=\"_blank\"><i>$l_bbcode</i></a> $l_onthispost<BR>";
			}

		echo "<INPUT TYPE=\"CHECKBOX\" NAME=\"smile\">$l_disable <a href=\"$smileref_url\" target=\"_blank\"><i>$l_smilies</i></a> $l_onthispost.<BR>";
			if($allow_sig == 1) {
		?>
				<INPUT TYPE="CHECKBOX" NAME="sig"><?php echo $l_attachsig?></font><BR>
		<?php
			}
		?>
			</FONT>
		</TD>
	</TR>
	<TR>
		<TD  BGCOLOR="<?php echo $color1?>" colspan=2 ALIGN="CENTER">
		<INPUT TYPE="SUBMIT" NAME="submit" VALUE="<?php echo $l_submit?>">
	</TR>
	</TABLE></TD></TR></TABLE>
	</FORM>

<?php
}
require('page_tail.'.$phpEx);
?>
