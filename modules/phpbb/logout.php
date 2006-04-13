<?  session_start(); ?>
<?php
/***************************************************************************
                          logout.php  -  description
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
 * logout.php - Nathan Codding
 * - Used for logging out a user and deleting a session.
 */
include('extention.inc');
include('functions.'.$phpEx);
include('config.'.$phpEx);
require('auth.'.$phpEx);
$pagetitle = $l_logout;
$pagetype = "logout";

/* Note: page_header.php is included later on, because this page needs to be able to send a cookie. */

if ($user_logged_in) {
	end_user_session($userdata[user_id], $db);
}

	header("Location: $url_phpbb/index.$phpEx");
require('page_tail.'.$phpEx);
?>
