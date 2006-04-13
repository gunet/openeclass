<?  
if (!session_id()) { session_start(); }
include('../../include/config.php');

/***************************************************************************
                          config.php  -  description
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
// This is the only setting you should need to change in this file.
// You should set this to the web path to your phpBB installation.
// For example, if you have phpBB installed in:
// http://www.mysite.com/phpBB
// Leave this setting EXACTLY how it is, you're done.
// If you have phpBB installed in:
// http://www.mysite.com/forums
// Change this to:
// $url_phpbb = "/forums";
// Once this is set you should not need to modify anything else in this file.
$url_phpbb =$urlAppend."/modules/phpbb";

// -- Edit the following ONLY if you cannot login and $url_phpbb is set correclty --
// You shouldn't have to change any of these 5.
$url_admin = "$url_phpbb/admin";
$url_images = "$url_phpbb/images";
$url_smiles = "$url_images/smiles";
$url_phpbb_index = $url_phpbb . '/index.' . $phpEx;
$url_admin_index = $url_admin . '/index.' . $phpEx;

/* -- Cookie settings (lastvisit, userid) -- */
// Most likely you can leave this be, however if you have problems
// logging into the forum set this to your domain name, without
// the http://
// For example, if your forum is at http://www.mysite.com/phpBB then
// set this value to
// $cookiedomain = "www.mysite.com";
$cookiedomain = "support.icampus.ucl.ac.be";
// It should be safe to leave this alone as well. But if you do change it
// make sure you don't set it to a variable already in use such as 'forum'.
$cookiename = "phpBB";
// It should be safe to leave these alone as well.
$cookiepath = $url_phpbb;
$cookiesecure = false;

/* -- Cookie settings (sessions) -- */
// This is the cookie name for the sessions cookie, you shouldn't have to change it
$sesscookiename = "phpBBsession";
// This is the number of seconds that a session lasts for, 3600 == 1 hour.
// The session will exprire if the user dosan't view a page on the forum within
// this amount of time.
$sesscookietime = 3600;

/**
 * This setting is only for people running Microsoft IIS.
 * If you're running IIS and your users cannot login using
 * the "login" link on the main page, but they CAN login
 * through other pages like preferences, then you should
 * change this setting to 1. Otherwise, leave at set
 * to 0, because this is an ugly hack around some IIS junk.
 */
// Change to "define('USE_IIS_LOGIN_HACK', 1);" if you need to.
define('USE_IIS_LOGIN_HACK', 0);

/* Stuff for priv msgs - not in DB yet: */
// Allow BBCode in private messages?
$allow_pmsg_bbcode = 1;
// Allow HTML in private message?
$allow_pmsg_html = 0;

/* -- You shouldn't have to change anything after this point */
/* -- Cosmetic Settings -- */
$FontColor = "#FFFFFF";
$textcolorMessage = "#FFFFFF";  // Message Font Text Color
$FontSizeMessage = "1";  // Message Font Text Size
$FontFaceMessage = "Arial";  // Message Font Text Face

/* -- Images -- */
$reply_wquote_image = "$url_images/quote.gif";

$folder_image = "$url_images/folder.gif";
$hot_folder_image = "$url_images/hot_folder.gif";
$newposts_image = "$url_images/red_folder.gif";
$hot_newposts_image = "$url_images/hot_red_folder.gif";

$posticon = "$url_images/posticon.gif";
$edit_image = "$url_images/edit.gif";
$profile_image = "$url_images/profile.gif";
$email_image = "$url_images/email.gif";

$locked_image = "$url_images/lock.gif";
$locktopic_image = "$url_images/lock_topic.gif";
$deltopic_image = "$url_images/del_topic.gif";
$movetopic_image = "$url_images/move_topic.gif";
$unlocktopic_image = "$url_images/unlock_topic.gif";
$ip_image = "$url_images/ip_logged.gif";

$www_image = "$url_images/www_icon.gif";
$icq_add_image = "$url_images/icq_add.gif";
$images_aim = "$url_images/aim.gif";
$images_yim = "$url_images/yim.gif";
$images_msnm = "$url_images/msnm.gif";

/* -- Other Settings -- */
$phpbbversion = "1.4.0";
$dbhost = "$mysqlServer";
//$dbname = "phpbb";
$dbuser = "$mysqlUser";
$dbpasswd = "$mysqlPassword";

?>
