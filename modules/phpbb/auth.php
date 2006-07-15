<?php
/***************************************************************************
                          auth.php  -  description
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
// Set the error reporting to a sane value:
//error_reporting  (E_ERROR | E_WARNING | E_PARSE); // This will NOT report uninitialized variables
/*
 * bogart: Set error_reporting to E_ALL to catch all problems during development
 */
error_reporting (E_ALL);

// -----------------------------------------------------------------------------------
// checking the mysql version
// note version_compare() is used for checking the php version but works for mysql too
// ------------------------------------------------------------------------------------

// Disable Magic Quotes
function stripslashes_array(&$the_array_element, $the_array_element_key, $data)
{
   $the_array_element = stripslashes($the_array_element);
}

if(get_magic_quotes_gpc() == 1)
{
   switch($REQUEST_METHOD)
   {
   case "POST":
      while (list ($key, $val) = each ($_POST))
      {
         if( is_array($val) )
         {
            array_walk($val, 'stripslashes_array', '');
            $$key = $val;
         }
      else
      {
         $$key = stripslashes($val);
      }
      }
   break;
   case "GET":
      while (list ($key, $val) = each ($_GET))
      {
         if( is_array($val) )
         {
            array_walk($val, 'stripslashes_array', '');
            $$key = $val;
         }
         else
         {
            $$key = stripslashes($val);
         }
      }
   break;
   }
}

// Check if the config file is writable (shouldn't be!!)
$config_file_name = "config.php";
if(strstr($PHP_SELF, "admin"))
{
   if(!strstr($PHP_SELF, "topicadmin"))
   {
     $config_file_name = "../config.php";
   }
}

// Make a database connection.
if(!$db = @mysql_connect("$dbhost", "$dbuser", "$dbpasswd"))
	error_die('<font size=+1>An Error Occured</font><hr>phpBB was unable to connect to the database. <BR>Please check $dbhost, $dbuser, and $dbpasswd in config.php.');
if (mysql_version()) mysql_query("SET NAMES greek");
if(!@mysql_select_db("$dbname",$db))
	error_die("<font size=+1>An Error Occured</font><hr>phpBB was unable to find the database <b>$dbname</b> on your MySQL server. <br>TRY too login again Back to forum <form action = \"/index.php?mon_icampus=yes\" method='post'>	Username : 	<input type=\"text\" name=\"uname\" size=\"10\"><br>	Password :  <input type=\"password\" name=\"pass\" size=\"10\"><br>	<input type=\"submit\" value=\"Entrer\" name=\"submit\">	</form>");

// Setup forum Options.

$sql = "SELECT * FROM config WHERE selected = 1";
if($result = mysql_query($sql, $db)) {
   if($myrow = mysql_fetch_array($result)) {
      $sitename = stripslashes($myrow["sitename"]);
      $allow_html = $myrow["allow_html"];
      $allow_bbcode = $myrow["allow_bbcode"];
      $allow_sig = $myrow["allow_sig"];
      $allow_namechange = $myrow["allow_namechange"];
      $posts_per_page = $myrow["posts_per_page"];
      $hot_threshold = $myrow["hot_threshold"];
      $topics_per_page = $myrow["topics_per_page"];
      $override_user_themes = $myrow["override_themes"];
      $email_sig = stripslashes($myrow["email_sig"]);
      $email_from = $myrow["email_from"];
      $default_lang = $myrow["default_lang"];
      $sys_lang = $default_lang;
   }
}

// We MUST do this up here, so it's set even if the cookie's not present.
$user_logged_in = 0;
$logged_in = 0;
$userdata = Array();

// Check for a cookie on the users's machine.
// If the cookie exists, build an array of the users info and setup the theme.


####################################################
####################################################
####################################################

// new code for the session ID cookie..
if(isset($_COOKIE[$sesscookiename])) {
	$sessid = $_COOKIE[$sesscookiename];
	$userid = get_userid_from_session($sessid, $sesscookietime, $_SERVER['REMOTE_ADDR'], $db);

	if ($userid) {
	   $user_logged_in = 1;
	   update_session_time($sessid, $db);

	   $userdata = get_userdata_from_id($userid, $db);

	   $theme = setuptheme($userdata["user_theme"], $db);
	   if($theme)
	   {
	      $bgcolor = $theme["bgcolor"];
	      $table_bgcolor = $theme["table_bgcolor"];
	      $textcolor = $theme["textcolor"];
	      $color1 = $theme["color1"];
	      $color2 = $theme["color2"];
	      $header_image = $theme["header_image"];
	      $newtopic_image = $theme["newtopic_image"];
	      $reply_image = $theme["reply_image"];
	      $linkcolor = $theme["linkcolor"];
	      $vlinkcolor = $theme["vlinkcolor"];
	      $FontFace = $theme["fontface"];
	      $FontSize1 = $theme["fontsize1"];
	      $FontSize2 = $theme["fontsize2"];
	      $FontSize3 = $theme["fontsize3"];
	      $FontSize4 = $theme["fontsize4"];
	      $tablewidth = $theme["tablewidth"];
	      $TableWidth = $tablewidth;
	      $reply_locked_image = $theme["replylocked_image"];

	   }
	   // Use the language the user has choosen
           /* xxx: check if user_lang can be changed */
	   if($userdata["user_lang"] != '')
	     $default_lang = $userdata["user_lang"];
	} // if
}

####################################################
####################################################
####################################################

// Old code for the permanent userid cookie..
// We only need to run this if the user's not logged in.

if (!$user_logged_in)
{
	if(isset($_COOKIE[$cookiename]))
	{
	   $userdata = get_userdata_from_id($_COOKIE["$cookiename"], $db);
	   $theme = setuptheme($userdata["user_theme"], $db);
	   if($theme)
	  	{
	      $bgcolor = $theme["bgcolor"];
	      $table_bgcolor = $theme["table_bgcolor"];
	      $textcolor = $theme["textcolor"];
	      $color1 = $theme["color1"];
	      $color2 = $theme["color2"];
	      $header_image = $theme["header_image"];
	      $newtopic_image = $theme["newtopic_image"];
	      $reply_image = $theme["reply_image"];
	      $linkcolor = $theme["linkcolor"];
	      $vlinkcolor = $theme["vlinkcolor"];
	      $FontFace = $theme["fontface"];
	      $FontSize1 = $theme["fontsize1"];
	      $FontSize2 = $theme["fontsize2"];
	      $FontSize3 = $theme["fontsize3"];
	      $FontSize4 = $theme["fontsize4"];
	      $tablewidth = $theme["tablewidth"];
	      $TableWidth = $tablewidth;
	      $reply_locked_image = $theme["replylocked_image"];
	   }

	   // Use the language the user has choosen.
           /* xxx: check if user_lang can be changed */
	   if($userdata["user_lang"] != '')
	   {
	     $default_lang = $userdata["user_lang"];
	   }
	}
}

####################################################
####################################################
####################################################


// Setup the default theme

if($override_user_themes == 1 || !$theme)
{
   $sql = "SELECT * FROM themes WHERE theme_default = 1";
   if(!$r = mysql_query($sql, $db))
   {
   	error_die('<font size=+1>An Error Occured</font><hr>phpBB was unable to connect to the database. <BR>Please check $dbhost, $dbuser, and $dbpasswd in config.php.');
   }
   if($theme = mysql_fetch_array($r))
   {
      $bgcolor = $theme["bgcolor"];
      $table_bgcolor = $theme["table_bgcolor"];
      $textcolor = $theme["textcolor"];
      $color1 = $theme["color1"];
      $color2 = $theme["color2"];
      $header_image = $theme["header_image"];
      $newtopic_image = $theme["newtopic_image"];
      $reply_image = $theme["reply_image"];
      $linkcolor = $theme["linkcolor"];
      $vlinkcolor = $theme["vlinkcolor"];
      $FontFace = $theme["fontface"];
      $FontSize1 = $theme["fontsize1"];
      $FontSize2 = $theme["fontsize2"];
      $FontSize3 = $theme["fontsize3"];
      $FontSize4 = $theme["fontsize4"];
      $tablewidth = $theme["tablewidth"];
      $TableWidth = $tablewidth;
      $reply_locked_image = $theme["replylocked_image"];
   }
}

// set expire dates: one for a year, one for 10 minutes
$expiredate1 = time() + 3600 * 24 * 365;
$expiredate2 = time() + 600;

// update LastVisit cookie. This cookie is updated each time auth.php runs
setcookie("LastVisit", time(), $expiredate1,  $cookiepath, $cookiedomain, $cookiesecure);

// set LastVisitTemp cookie, which only gets the time from the LastVisit
// cookie if it does not exist yet
// otherwise, it gets the time from the LastVisitTemp cookie
if (!isset($_COOKIE["LastVisitTemp"])) {
	$temptime = $_COOKIE["LastVisit"];
}
else {
	$temptime = $_COOKIE["LastVisitTemp"];
}

// set cookie.
setcookie("LastVisitTemp", $temptime ,$expiredate2, $cookiepath, $cookiedomain, $cookiesecure);

// set vars for all scripts
$now_time = time();
$last_visit = $temptime;

// Include the appropriate language file.
/* xxx: check to use only one language */
if(!strstr($PHP_SELF, "admin"))
{
   include('language/lang_' . $default_lang . '.php');
}
else
{
   if(strstr($PHP_SELF, "topicadmin")) {
     include('language/lang_' . $default_lang . '.php');
	} else {
     include('../language/lang_' . $default_lang . '.php');
	}
}

// See if translated pictures are available..
/* xxx: check if translated images exist */
$header_image = get_translated_file($header_image);
$reply_locked_image = get_translated_file($reply_locked_image);
$newtopic_image = get_translated_file($newtopic_image);
$reply_image = get_translated_file($reply_image);

// Set documentation locations:
$faq_url = get_translated_file("faq.php");
$bbref_url = $faq_url . "#bbcode";
$smileref_url = $faq_url . "#smilies";

?>
