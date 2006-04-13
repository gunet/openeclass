<?  session_start(); ?>
<?php
/***************************************************************************
                          login.php  -  description
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
 * login.php - Nathan Codding
 * - Used for logging in a user and setting up a session.
 */
include('extention.inc');
include('functions.'.$phpEx);
include('config.'.$phpEx);
require('auth.'.$phpEx);
$pagetitle = "Login";
$pagetype = "other";

/* Note: page_header.php is included later on, because this page needs to be able to send a cookie. */


if (!$submit) {
	include('page_header_ident.'.$phpEx);
	login_form();
} else { // Something has been submitted
	if ($user == '' || $passwd == '') {
		error_die("$l_userpass $l_tryagain");
	}
	if (!check_username($user, $db)) {
		error_die("$l_nouser $l_tryagain");
	}
	if (!check_user_pw($user, $passwd, $db)) {
		error_die("$l_wrongpass $l_tryagain");
	}

	/* if we get here, user has entered a valid username and password combination. */

	$userdata = get_userdata($user, $db);

	$sessid = new_session($userdata[user_id], $REMOTE_ADDR, $sesscookietime, $db);	

	set_session_cookie($sessid, $sesscookietime, $sesscookiename, $cookiepath, $cookiedomain, $cookiesecure);

	// Push back to the main index page, no need to tell the user they 
	// are logged in, they can figure that out on the index page.
	if (defined('USE_IIS_LOGIN_HACK') && USE_IIS_LOGIN_HACK)
	{
		echo "<META HTTP-EQUIV=\"refresh\" content=\"1;URL=$url_phpbb\">";
	}
	else
	{
		header("Location: $url_phpbb/index.$phpEx");
	}
	
} // if/else (!$submit)


require('page_tail.'.$phpEx);
?>
