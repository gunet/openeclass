<?php
/***************************************************************************
                          delpmsg.php  -  description
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
 * delpmsg.php - Nathan Codding
 * - Used for deleting private messages by users of the BB.
 */
include('extention.inc');
include('functions.'.$phpEx);
include('config.'.$phpEx);
require('auth.'.$phpEx);
$pagetitle = "Private Messages";
$pagetype = "privmsgs";
include('page_header.'.$phpEx);


if (!$submit && !$user_logged_in) {
	login_form();
} else {
	if (!$user_logged_in) {
		if ($user == '' || $passwd == '') {
			error_die($l_userpass);
		}
		if (!check_username($user, $db)) {
			error_die("$l_nouser $l_tryagain");
		}
		if (!check_user_pw($user, $passwd, $db)) {
			error_die("$l_wrongpass");
		}
	
		/* throw away user data from the cookie, use username from the form to get new data */
		$userdata = get_userdata($user, $db);
	}

	$sql = "SELECT to_userid FROM priv_msgs WHERE (msg_id = $msgid)";
	$resultID = mysql_query($sql);
	if (!$resultID) {
		echo mysql_error() . "<br>\n";
		error_die("Error during DB query (checking msg ownership)");
	}
	$row = mysql_fetch_array($resultID);
	if ($userdata[user_id] != $row[to_userid]) {
		error_die("That's not your message. You can't delete it.");
	}

	$deleteSQL = "DELETE FROM priv_msgs WHERE (msg_id = $msgid)";
	$success = mysql_query($deleteSQL);
	if (!$success) {
		error_die("Error deleting from DB.");
	}
   echo "<br><TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\" ALIGN=\"CENTER\" VALIGN=\"TOP\" WIDTH=\"$tablewidth\">";
   echo "<TR><TD  BGCOLOR=\"$table_bgcolor\"><TABLE BORDER=\"0\" CALLPADDING=\"1\" CELLSPACING=\"1\" WIDTH=\"100%\">";
   echo "<TR BGCOLOR=\"$color1\" ALIGN=\"LEFT\"><TD><font face=\"Verdana\" size=\"2\"><P>";
   echo "<P><BR><center>$l_deletesucces $l_click <a href=\"$url_phpbb/viewpmsg.$phpEx\">$l_here</a> $l_toreturn<p></center></font>";
   echo "</TD></TR></TABLE></TD></TR></TABLE><br>";

} // if/else (if submit)

require('page_tail.'.$phpEx);
?>
