<?php
/***************************************************************************
*                           install.php  -  description
*                              -------------------
*     begin                : Sat Oct 14 2000
*     copyright            : (C) 2001 The phpBB Group
*     email                : support@phpbb.com
* 
*     $Id$
* 
****************************************************************************/
  
/***************************************************************************
*
*   This program is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 2 of the License, or
*   (at your option) any later version.
*
***************************************************************************/
include('extention.inc');
include("functions.$phpEx");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Strict//EN">
          <HTML>
          <HEAD>
          <TITLE>phpBB - Installation</TITLE>
          </HEAD>
          <BODY BGCOLOR="#000000" TEXT="#FFFFFF" LINK="#11C6BD" VLINK="#11C6BD">
  
  
<?php
if($next) {
   switch($next) {
    case 'database':
      if(!$done) {
	 echo "Testing DB Connection...";
	 flush();
	 if(!$db = mysql_connect("$dbserver", "$dbuser", "$dbpass"))
	   die("<font color=\"#FF0000\">Error, I could not connect to the database at $dbserver. Using username $dbuser and password $dbpass.<BR>Please go back and try again.");
	 echo "<font color=\"#00FF00\">DB Connection Good!</FONT><BR>";
	 flush();
	 echo "Selected database $dbname...";
	 flush();
	 if(!@mysql_select_db("$dbname", $db)) {
	    echo "<font color=\"#FF0000\">Database could not be found</font><BR>";
	    flush();
	    echo "Attempting to create database $dbname...";
	    flush();
	    if(!$r = mysql_query("CREATE DATABASE $dbname", $db))
	      die("<font color=\"#FF0000\">Error, count not select or create database $dbname, please create it manually or have your system administrator do it for you and try again.");
	    mysql_select_db("$dbname", $db);
	    echo "<font color=\"#00FF00\">Database Created!</font><BR>";
	    flush();
	 }
	 else
	   echo "<font color=\"#00FF00\">Database Selected!</font><BR>";
	 flush();
	 echo "Creating phpBB tables an inserting default data...<BR>";
	 flush();
	 $tables = array ("cat" => "CREATE TABLE catagories (
							     cat_id int(10) DEFAULT '0' NOT NULL auto_increment,
							     cat_title varchar(100),
							     cat_order varchar(10),
							     PRIMARY KEY (cat_id)
							     )",
			  "config" => "CREATE TABLE config (
							    config_id int(10) DEFAULT '0' NOT NULL auto_increment,
							    sitename varchar(100),
							    allow_html int(2),
							    allow_bbcode int(2),
							    allow_sig int(2),
							    allow_namechange int(2) DEFAULT '0',
							    admin_passwd varchar(32),
							    selected int(2) DEFAULT '0' NOT NULL,
							    posts_per_page int(10),
							    hot_threshold int(10),
							    topics_per_page int(10),
							    allow_theme_create int(10),
							    override_themes int(2) DEFAULT '0',
							    email_sig varchar(255),
							    email_from varchar(100),
							    default_lang varchar(255),
							    UNIQUE selected (selected),
							    PRIMARY KEY (config_id)
							    )",
			  "disallow" => "CREATE TABLE disallow (
								disallow_id int(10) DEFAULT '0' NOT NULL AUTO_INCREMENT,
								disallow_username varchar(50),
								PRIMARY KEY(disallow_id)
								)",
			  "forums" => "CREATE TABLE forums (   
							    forum_id int(10) DEFAULT '0' NOT NULL auto_increment,
							    forum_name varchar(150),
							    forum_desc text, 
							    forum_access int(10) DEFAULT '1',
							    forum_moderator int(10),
							    forum_topics int(10) NOT NULL DEFAULT '0',
							    forum_posts int(10) NOT NULL DEFAULT '0',
							    forum_last_post_id int(10) NOT NULL DEFAULT '0',
							    cat_id int(10),
							    forum_type int(10) DEFAULT '0',
							    KEY(forum_last_post_id),
							    PRIMARY KEY (forum_id)
							    )",
			  "hmf" => "CREATE TABLE headermetafooter (
								   header text,
								   meta text,
								   footer text
								   )",
			  "ranks" => "CREATE TABLE ranks (
							  rank_id int(10) DEFAULT '0' NOT NULL auto_increment,
							  rank_title varchar(50) NOT NULL,
							  rank_min int(10) NOT NULL,
							  rank_max int(10) NOT NULL,
							  rank_special int(2) DEFAULT '0',
							  rank_image varchar(255),
							  PRIMARY KEY(rank_id),
							  KEY (rank_min),
							  KEY (rank_max) 
							  )",
			  "posts" => "CREATE TABLE posts (
							  post_id int(10) DEFAULT '0' NOT NULL auto_increment,
							  topic_id int(10) DEFAULT '0' NOT NULL,
							  forum_id int(10) DEFAULT '0' NOT NULL,
							  poster_id int(10) NOT NULL,
							  post_time varchar(20),
							  poster_ip varchar(16),
							  KEY(post_id),
 							  KEY(forum_id),
							  KEY(topic_id),
				          KEY(poster_id),
							  PRIMARY KEY (post_id)
							  )",
			  "posts_text" => "CREATE TABLE posts_text (
			  						post_id int(10) NOT NULL,
			  						post_text text,
			  						PRIMARY KEY(post_id)
			  						)",
			  "pmsg" => "CREATE TABLE priv_msgs (
							     msg_id int(10) DEFAULT '0' NOT NULL auto_increment,
							     from_userid int(10) DEFAULT '0' NOT NULL,
							     to_userid int(10) DEFAULT '0' NOT NULL,
							     msg_time varchar(20),
							     poster_ip varchar(16),
							     msg_status int(10) DEFAULT '0',
							     msg_text text, 
							     PRIMARY KEY (msg_id),
							     KEY msg_id (msg_id),
							     KEY to_userid (to_userid)
							     )",
			  "sessions" => "CREATE TABLE sessions (
								sess_id int(10) unsigned DEFAULT '0' NOT NULL,
								user_id int(10) DEFAULT '0' NOT NULL,
								start_time int(10) unsigned DEFAULT '0' NOT NULL,
								remote_ip varchar(15) DEFAULT '' NOT NULL,
								PRIMARY KEY (sess_id),
								KEY sess_id (sess_id),
								KEY start_time (start_time),
								KEY remote_ip (remote_ip)
								)",
			  "themes" => "CREATE TABLE themes (
							    theme_id int(10) DEFAULT '0' NOT NULL auto_increment,
							    theme_name varchar(35),
							    bgcolor varchar(10),
							    textcolor varchar(10),
							    color1 varchar(10), 
							    color2 varchar(10),
							    table_bgcolor varchar(10),
							    header_image varchar(50),
							    newtopic_image varchar(50),
							    reply_image varchar(50),
							    linkcolor varchar(15),
							    vlinkcolor varchar(15),
							    theme_default int(2) DEFAULT '0',
							    fontface varchar(100),
							    fontsize1 varchar(5),
							    fontsize2 varchar(5),
							    fontsize3 varchar(5),
							    fontsize4 varchar(5),
							    tablewidth varchar(10),
							    replylocked_image varchar(255),
							    PRIMARY KEY (theme_id)
							    )",
			  "topics" => "CREATE TABLE topics (
							    topic_id int(10) DEFAULT '0' NOT NULL auto_increment,
							    topic_title varchar(100),
							    topic_poster int(10),
							    topic_time varchar(20),
							    topic_views int(10) DEFAULT '0' NOT NULL,
							    topic_replies int(10) NOT NULL DEFAULT '0',
							    topic_last_post_id int(10) NOT NULL,
							    forum_id int(10) NOT NULL,
							    topic_status int(10) DEFAULT '0' NOT NULL,
							    topic_notify int(2) DEFAULT '0',
							    KEY(topic_id),
							    KEY(forum_id),
							    KEY(topic_last_post_id),
							    PRIMARY KEY (topic_id) 
							    )",
			  "users" => "CREATE TABLE users (
							  user_id int(10) DEFAULT '0' NOT NULL auto_increment,
							  username varchar(40) DEFAULT '' NOT NULL,
							  user_regdate varchar(20) NOT NULL,
							  user_password varchar(32) DEFAULT '' NOT NULL,
							  user_email varchar(50),
							  user_icq varchar(15),
							  user_website varchar(100),
							  user_occ varchar(100),
							  user_from varchar(100),
							  user_intrest varchar(150),
							  user_sig varchar(255),
							  user_viewemail tinyint(2),
							  user_theme int(10),
							  user_aim varchar(18),  
							  user_yim varchar(25),
							  user_msnm varchar(25),
							  user_posts int(10) DEFAULT '0',
							  user_attachsig int(2) DEFAULT '0',
							  user_desmile int(2) DEFAULT '0',
							  user_html int(2) DEFAULT '0',
							  user_bbcode int(2) DEFAULT '0',
							  user_rank int(10) DEFAULT '0',
							  user_level int(10) DEFAULT '1',
							  user_lang varchar(255),
							  user_actkey varchar(32),
							  user_newpasswd varchar(32),
							  PRIMARY KEY (user_id)
							  )",
			  "online" => "CREATE TABLE whosonline (
								id int(3) DEFAULT '0' NOT NULL auto_increment,
								ip varchar(255),
								name varchar(255),   
								count varchar(255),
								date varchar(255),
								username varchar(40),  
								forum int(10),
								PRIMARY KEY (id)
								)",
			  "acess" => "CREATE TABLE access (   
							   access_id int(10) DEFAULT '0' NOT NULL auto_increment,
							   access_title varchar(20),
							   PRIMARY KEY (access_id)
							   )",

			  "smiles" => "CREATE TABLE smiles (
							    id int(10) DEFAULT '0' NOT NULL AUTO_INCREMENT,
							    code varchar(50),
							    smile_url varchar(100),
							    emotion varchar(75),
							    PRIMARY KEY(id)
							    )",
			  "words" => "CREATE TABLE words (
							  word_id int(10) NOT NULL AUTO_INCREMENT DEFAULT '0',
							  word varchar(100),
							  replacement varchar(100),
							  PRIMARY KEY(word_id))",
			  "banlist" => "CREATE TABLE banlist(
							     ban_id int(10) NOT NULL AUTO_INCREMENT DEFAULT '0',
							     ban_userid int(10),
							     ban_ip varchar(16),
							     ban_start int(32),
							     ban_end int(50),
							     ban_time_type int(10),
							     KEY(ban_id),
							     PRIMARY KEY(ban_id))",
			  "forum_mods" => "CREATE TABLE forum_mods (
								   forum_id int(10) NOT NULL,
								   user_id int(10) NOT NULL
								   )",
                          "forum_access" => "CREATE TABLE forum_access(
                                                                      forum_id int(10) NOT NULL,
                                                                      user_id  int(10) NOT NULL,
                                                                      can_post tinyint(1) NOT NULL DEFAULT '0',
                                                                      PRIMARY KEY(forum_id, user_id))");

	 $ta_users_data = "INSERT INTO users (user_id, username, user_regdate) VALUES (-1, 'Anonymous', 'May 12, 1978')";
	 $ta_access_data = array("INSERT INTO access VALUES (-1, 'Deleted')",
				 "INSERT INTO access VALUES (1,'User')",
				 "INSERT INTO access VALUES (2,'Moderator')",
				 "INSERT INTO access VALUES (3,'Super Moderator')",
				 "INSERT INTO access VALUES (4,'Administrator')");
	 $ta_themes_data = array("default" => "INSERT INTO themes VALUES (1,'Default','#000000','#FFFFFF','#6C706D','#2E4460','#001100','images/header-dark.jpg','images/new_topic-dark.jpg','images/reply-dark.jpg','#11C6BD','#11C6BD', '1', 'sans-serif', '1','2','-2','+1','95%','images/reply_locked-dark.jpg')",
				 "ocean" => "INSERT INTO themes VALUES (2,'Ocean','#FFFFFF','#000000','#CCCCCC','#9BB6DA','#000000','images/header.jpg','images/new_topic.jpg','images/reply.jpg','#011001','#2100cc', '0', 'sans-serif', '1','2','-2','+1','95%','images/reply_locked-dark.jpg')",
				 "OCPrices.com" => "INSERT INTO themes VALUES (3,'OCPrices.com','#FFFFFF','#000000','#efefef','#cdcdcd','#001100','images/header.jpg','images/new_topic.jpg','images/reply.jpg','#000000','#000000', '0', 'sans-serif', '1','2','-2','+1','95%','images/reply_locked-dark.jpg')");
	 $ta_smile_data = array(
				"INSERT INTO smiles VALUES (1,':D','icon_biggrin.gif','Very Happy')",
				"INSERT INTO smiles VALUES (2,':-D','icon_biggrin.gif','Very Happy')",
				"INSERT INTO smiles VALUES (3,':grin:','icon_biggrin.gif','Very Happy')",
				"INSERT INTO smiles VALUES (4,':)','icon_smile.gif','Smile')",
				"INSERT INTO smiles VALUES (5,':-)','icon_smile.gif','Smile')",
				"INSERT INTO smiles VALUES (6,':smile:','icon_smile.gif','Smile')",
				"INSERT INTO smiles VALUES (7,':(','icon_frown.gif','Sad')", 
				"INSERT INTO smiles VALUES (8,':-(','icon_frown.gif','Sad')",
				"INSERT INTO smiles VALUES (9,':sad:','icon_frown.gif','Sad')", 
				"INSERT INTO smiles VALUES (10,':o','icon_eek.gif','Surprised')", 
				"INSERT INTO smiles VALUES (11,':-o','icon_eek.gif','Surprised')",
				"INSERT INTO smiles VALUES (12,':eek:','icon_eek.gif','Suprised')", 
				"INSERT INTO smiles VALUES (13,':-?','icon_confused.gif','Confused')",
				"INSERT INTO smiles VALUES (14,':???:','icon_confused.gif','Confused')",
				"INSERT INTO smiles VALUES (15,'8)','icon_cool.gif','Cool')",
				"INSERT INTO smiles VALUES (16,'8-)','icon_cool.gif','Cool')",
				"INSERT INTO smiles VALUES (17,':cool:','icon_cool.gif','Cool')",
				"INSERT INTO smiles VALUES (18,':lol:','icon_lol.gif','Laughing')",
				"INSERT INTO smiles VALUES (19,':x','icon_mad.gif','Mad')",
				"INSERT INTO smiles VALUES (20,':-x','icon_mad.gif','Mad')",
				"INSERT INTO smiles VALUES (21,':mad:','icon_mad.gif','Mad')",
				"INSERT INTO smiles VALUES (22,':P','icon_razz.gif','Razz')",
				"INSERT INTO smiles VALUES (23,':-P','icon_razz.gif','Razz')",
				"INSERT INTO smiles VALUES (24,':razz:','icon_razz.gif','Razz')",
				"INSERT INTO smiles VALUES (25,':oops:','icon_redface.gif','Embaressed')",
				"INSERT INTO smiles VALUES (26,':cry:','icon_cry.gif','Crying (very sad)')",
				"INSERT INTO smiles VALUES (27,':evil:','icon_evil.gif','Evil or Very Mad')",
				"INSERT INTO smiles VALUES (28,':roll:','icon_rolleyes.gif','Rolling Eyes')",
				"INSERT INTO smiles VALUES (29,':wink:','icon_wink.gif','Wink')",
				"INSERT INTO smiles VALUES (30,';)','icon_wink.gif','Wink')",
				"INSERT INTO smiles VALUES (31,';-)','icon_wink.gif','Wink')");
	 
	 echo "<TABLE BORDER=\"0\">\n";
	 while(list($name, $table) = each($tables)) {
	    echo "<TR><TD>Creating table $name</TD> ";
	    if(!$r = mysql_query($table, $db))
	      die("<TD><font color=\"#FF0000\">ERROR! Could not create table. Reason: <b>". mysql_error()."</b></TD></TR></TABLE>");
	    echo "<TD><font color=\"#00FF00\">[OK]</FONT></TD></TR>";
	    flush();
	 }
	 echo "<TR><TD>Inserting default data</TD>";
	 if(!$r = mysql_query($ta_users_data, $db))
	   die("<TD>Error could not insert data into the users table. Reason: ". mysql_error() ."</TD></TR></TABLE>");
	 for($x = 0; $x < count($ta_access_data); $x++) {
	    if(!$r = mysql_query($ta_access_data[$x], $db))
	      die("<TD>Error, could not insert data into the access table. Reason: ". mysql_error() . "</TD></TR></TABLE>");
	 }
	 while(list($name, $theme) = each($ta_themes_data)) {
	    if(!$r = mysql_query($theme, $db))
	      die("<TD><font color=\"#FF0000\">ERROR! Could not enter theme data. Reason: <b>". mysql_error()."</b></TD></TR></TABLE>");
	 }
	 for($x = 0; $x < count($ta_smile_data); $x++) { 
	    if(!$r = mysql_query($ta_smile_data[$x], $db))
	       die("<TD>Error, could not insert data into the access table. Reason: ". mysql_error() . "</TD></TR></TABLE>");
	 }
	 echo "<TD><font color=\"#00FF00\">[OK]</FONT></TD></TR>";
	 echo "</TABLE>";

	 echo "<font color=\"#00FF00\">Database Created Successfully!</FONT><BR>";
?>
	   <CENTER>
	   <FORM METHOD="POST" ACTION="<?php echo $PHP_SELF ?>">
	   <INPUT TYPE="HIDDEN" NAME="dbname" VALUE="<?php echo $dbname ?>">
	   <INPUT TYPE="HIDDEN" NAME="dbserver" VALUE="<?php echo $dbserver ?>">
	   <INPUT TYPE="HIDDEN" NAME="dbuser" VALUE="<?php echo $dbuser ?>">
	   <INPUT TYPE="HIDDEN" NAME="dbpass" VALUE="<?php echo $dbpass ?>">
	   <INPUT TYPE="HIDDEN" NAME="done" VALUE="1">
	   <INPUT TYPE="HIDDEN" NAME="next" VALUE="database">
	   <INPUT TYPE="SUBMIT" VALUE="Next >">
	   </FORM>
	   </CENTER>
<?php	   
	   
      }
      else {
	 $color1 = "#6C706D";
	 $color2 = "#2E4460";
?>
	    <FORM METHOD="POST" ACTION="<?php echo $PHP_SELF ?>">
	           <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%">
	                 <TR>
	                 <TD BGCOLOR="#001100">
	                 <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
	                       <TR BGCOLOR="#6C706D" ALIGN="CENTER">
	                       <TD COLSPAN="2"><b>phpBB Installer</b></TD>
	                       </TR>
	                       <TR BGCOLOR="#2E4460" ALIGN="LEFT">
	                       <TD COLSPAN="2">Now that you have created your database you must create an 'Administrator', a user who will be able to alter the settings of the forum. You can use your own username for this. Other Administrators can be added after the installation is completed.</TD>
	                       </TR>
	                       <TR>
	   <TR ALIGN="LEFT">
	   <TD  BGCOLOR="<?php echo $color1?>"  width=25%><b>Username: *</b><br><FONT SIZE=-2>(Must be unique. No 2 users can have the same Username)</FONT></TD>
	   <TD  BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" NAME="username" SIZE="25" MAXLENGTH="40"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD  BGCOLOR="<?php echo $color1?>" width=25%><b>Password: *</b></TD>
	   <TD  BGCOLOR="<?php echo $color2?>"><INPUT TYPE="PASSWORD" NAME="password" SIZE="25" MAXLENGTH="25"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD  BGCOLOR="<?php echo $color1?>" width=25%><b>Retype Password: *</b></TD>
	   <TD  BGCOLOR="<?php echo $color2?>"><INPUT TYPE="PASSWORD" NAME="password_rep" SIZE="25" MAXLENGTH="25"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD  BGCOLOR="<?php echo $color1?>"  width=25%><b>Email Address: *<b></TD>
	   <TD  BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" NAME="email" SIZE="25" MAXLENGTH="50"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD  BGCOLOR="<?php echo $color1?>"  width=25%><b>ICQ Number: <b></TD>
	   <TD  BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" NAME="icq" SIZE="10" MAXLENGTH="15"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD  BGCOLOR="<?php echo $color1?>"  width=25%><b>AIM Name: <b></TD>
	   <TD  BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" NAME="aim" SIZE="15" MAXLENGTH="18"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD  BGCOLOR="<?php echo $color1?>"  width=25%><b>Yahoo Messanger: <b></TD>
	   <TD  BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" NAME="yim" SIZE="25" MAXLENGTH="25"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD  BGCOLOR="<?php echo $color1?>"  width=25%><b>MSN Messanger: <b></TD>
	   <TD  BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" NAME="msnm" SIZE="25" MAXLENGTH="25"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD  BGCOLOR="<?php echo $color1?>"  width=25%><b>Web Site Address: <b></TD>
	   <TD  BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" NAME="website" SIZE="25" MAXLENGTH="40" VALUE="http://"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD  BGCOLOR="<?php echo $color1?>"  width=25%><b>Location: <b></TD>
	   <TD  BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" NAME="from" SIZE="25" MAXLENGTH="40"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD  BGCOLOR="<?php echo $color1?>"  width=25%><b>Occupation: <b></TD>
	   <TD  BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" NAME="occ" SIZE="25" MAXLENGTH="40"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD  BGCOLOR="<?php echo $color1?>"  width=25%><b>Intrests: <b></TD>
	   <TD  BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" NAME="intrest" SIZE="25" MAXLENGTH="40"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD  BGCOLOR="<?php echo $color1?>" width=25%><b>Signature:</b><br><font size=-2>This is a block of text that can be added to posts you make.<BR>255 chars max!</font></TD>
	   <TD  BGCOLOR="<?php echo $color2?>"><TEXTAREA NAME="sig" ROWS=6 COLS=45></TEXTAREA></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD  BGCOLOR="<?php echo $color1?>" width=25%><b>Options:</b></TD>
	   <TD  BGCOLOR="<?php echo $color2?>"><INPUT TYPE="CHECKBOX" NAME="viewemail" VALUE="1"> Allow other users to view my email address<BR>
	   <INPUT TYPE="CHECKBOX" NAME="cookie_username" VALUE="1"> Store username in a cookie for 1 year.<BR>
	   </TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD  BGCOLOR="<?php echo $color1?>" colspan = 2><font size=-1>Items marked with a * are required</font></TD>
	   </TR>
	   <TR>
	   <TD  BGCOLOR="<?php echo $color1?>" colspan=2 ALIGN="CENTER">
	   <INPUT TYPE="HIDDEN" NAME="dbname" VALUE="<?php echo $dbname ?>">
	   <INPUT TYPE="HIDDEN" NAME="dbserver" VALUE="<?php echo $dbserver ?>">
	   <INPUT TYPE="HIDDEN" NAME="dbuser" VALUE="<?php echo $dbuser ?>">
	   <INPUT TYPE="HIDDEN" NAME="dbpass" VALUE="<?php echo $dbpass ?>">
	   <INPUT TYPE="HIDDEN" NAME="next" VALUE="user">
	   <INPUT TYPE="SUBMIT" VALUE="Next >">&nbsp;<INPUT TYPE="RESET" VALUE="Clear">
	   </TR>
	   </TABLE></TD></TR></TABLE>
	   </FORM>
<?php	   
	 
      }
      break;
    case 'user':
      if(!$db = mysql_connect("$dbserver", "$dbuser", "$dbpass"))
	die("<font color=\"#FF0000\">Error, I could not connect to the database at $dbserver. Using username $dbuser and password $dbpass.<BR>Please go back and try again.");
      mysql_select_db("$dbname", $db);
	 
      if($password == '' || $username == '' || $email == '')
	die("Error - you did not fill in all the required fields, please go back and fill them in.");
      
      if($password != $password_rep)
	die("The passwords you entered do not match. Please go back and try again");
      $sig = str_replace("\n", "<BR>", $sig);
      $sig = addslashes($sig);
      $username = addslashes($username);
      $occ = addslashes($occ);
      $intrest = addslashes($intrest);
      $from = addslashes($from);
      $passwd = md5($password);   
      $hint = addslashes($hint);
      $regdate = date("M d, Y");
      
      if(trim($website) == "http://")
	$website = "";
      
      if($viewemail == 1) {
	 $sqlviewemail = "1";
      }
      else {
	 $sqlviewemail = "0";
      }
      $sql = "INSERT INTO users (user_id, username, user_regdate, user_email, user_icq, user_password, user_occ, user_intrest, user_from, user_website, user_sig, 
			         user_aim, user_viewemail, user_yim, user_msnm, user_level) 
	                         VALUES (1 , '$username', '$regdate', '$email', '$icq', '$passwd', '$occ', '$intrest', '$from', '$website', '$sig',
				 '$aim', '$sqlviewemail', '$yim', '$msnm', 4)";  
      if(!$result = mysql_query($sql, $db))
	die("An Error Occurred while trying to add the information into the database. Please go back and try again. <BR>$sql<BR>$mysql_error()");
      $color1 = "#6C706D";
      $color2 = "#2E4460";

      $lang_select = language_select("english", "language");

      
?>
	<FORM METHOD="POST" ACTION="<?php echo $PHP_SELF ?>">
	<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%">
	<TR>
	<TD BGCOLOR="#001100">
	<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
	<TR BGCOLOR="#6C706D" ALIGN="CENTER">
	<TD COLSPAN="2"><b>phpBB Installer</b></TD>
	</TR>
	<TR BGCOLOR="#2E4460" ALIGN="LEFT">
	<TD COLSPAN="2">Administrator user, <b><?php echo $username ?></b> has been created successfully. Next you must set some forum wide options.</TD>
	<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD>Site Name:</TD>
	<TD><INPUT TYPE="TEXT" NAME="name" SIZE="30" MAXLENGTH="100" VALUE="This Site"></TD>
	</TR>
	<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD>Email From Address:<br><font size="-1"><i>(This is the email address that will appear on all emails sent by the forums)</i></td>
	<TD><INPUT TYPE="TEXT" NAME="email_from" SIZE="30" MAXLENGTH="100" VALUE="webmaster@<?php echo $SERVER_NAME?>"></TD>
	</TR>         
	<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD>Email Signature:<br><font size="-1"><i>(This is the email signature that will appear at the end of all emails sent by the forums)</i>
	<TD><TEXTAREA NAME="email_sig" ROWS="5" COLS="15">Yours Truely,
	
The Site Admin</TEXTAREA></TD>                                                    
	</TR>  
	<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD>Allow HTML:</TD>
	<TD><INPUT TYPE="RADIO" NAME="html" VALUE="1" CHECKED> Yes <INPUT TYPE="RADIO" NAME="html" VALUE="0"> No</TD>
	</TR>
	<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD>Allow BBCode:</TD>
	<TD><INPUT TYPE="RADIO" NAME="bb" VALUE="1"CHECKED> Yes <INPUT TYPE="RADIO" NAME="bb" VALUE="0"> No</TD>
	</TR>                    
	<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD>Allow Signatures:</TD>
	<TD><INPUT TYPE="RADIO" NAME="sig" VALUE="1" CHECKED> Yes <INPUT TYPE="RADIO" NAME="sig" VALUE="0"> No</TD>
	</TR>
	<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD>Hot Topic Threshold:</FONT></TD>
	<TD><INPUT TYPE="TEXT" NAME="hot" SIZE="3" MAXLENGTH="3" VALUE="15"></TD>
	</TR>
	<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD>Posts per Page:</FONT><br></TD>
	<TD><INPUT TYPE="TEXT" NAME="ppp" SIZE="3" MAXLENGTH="3" VALUE="15"></TD>
	</TR>
	<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD>Topics per Forum:</TD>
	<TD><INPUT TYPE="TEXT" NAME="tpp" SIZE="3" MAXLENGTH="3" VALUE="50"></TD>
	</TR>
	<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD>Forum Language:</TD>
	<TD><?php echo $lang_select?></TD>
	</TR>
	<TD BGCOLOR="<?php echo $color1?>" colspan=2 ALIGN="CENTER">
	<INPUT TYPE="HIDDEN" NAME="dbname" VALUE="<?php echo $dbname ?>">
	<INPUT TYPE="HIDDEN" NAME="dbserver" VALUE="<?php echo $dbserver ?>">
	<INPUT TYPE="HIDDEN" NAME="dbuser" VALUE="<?php echo $dbuser ?>">
	<INPUT TYPE="HIDDEN" NAME="dbpass" VALUE="<?php echo $dbpass ?>">
	<INPUT TYPE="HIDDEN" NAME="next" VALUE="options">
	<INPUT TYPE="SUBMIT" VALUE="Next >">&nbsp;<INPUT TYPE="RESET" VALUE="Clear">
	</TR>
	</TABLE></TD></TR></TABLE>
	</FORM>
<?php	
	
      break;
    case 'options':
      if(!$db = mysql_connect("$dbserver", "$dbuser", "$dbpass"))
	die("<font color=\"#FF0000\">Error, I could not connect to the database at $dbserver. Using username $dbuser and password $dbpass.<BR>Please go back and try again.");
      mysql_select_db("$dbname", $db);
      
      $name = addslashes($name);
      $email_sig = addslashes($email_sig);
      $sql = "INSERT INTO config (sitename, allow_html, allow_bbcode, allow_sig, hot_threshold, posts_per_page, topics_per_page,  email_from, email_sig, selected, default_lang) ";
      $sql .= "VALUES ('$name', $html, $bb, $sig, $hot, $ppp, $tpp,  '$email_from', '$email_sig', 1, '$language')";
      $result = mysql_query($sql, $db);
      if (!$result) {
	 echo mysql_error() . "<br>";
	 die("Error - Cannot update the database.</FONT>");
      }
      $config_file = file("./config.$phpEx");
      if(!strstr($config_file[count($config_file)-1], '?>') ) {  // Last line of config file shouldn't contain php closing tag.
	 if(!$fp = fopen("./config.$phpEx", "a"))
	   die("Error opening config.$phpEx, please check and make sure it exists in the same directory as this installation script and then try again.");
	 $config_data = '$dbhost = "'.$dbserver.'";'."\n".
	   '$dbname = "'.$dbname.'";'."\n".
	   '$dbuser = "'.$dbuser.'";'."\n".
	   '$dbpasswd = "'.$dbpass.'";'."\n".
	   '?>'."\n";
	 fputs($fp, $config_data);
	 fclose($fp);
      } else print "DB config was probably already added. Did not modify DB config.<br>\n";
?>
<FORM METHOD="POST" ACTION="<?php echo $PHP_SELF ?>">
                   <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%">
                         <TR>
                         <TD BGCOLOR="#001100">
                         <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
                               <TR BGCOLOR="#6C706D" ALIGN="CENTER">
                               <TD COLSPAN="2"><b>phpBB Installer</b></TD>
                               </TR>
                               <TR BGCOLOR="#2E4460" ALIGN="LEFT">
                               <TD COLSPAN="2">Congratulations! You have now successfully installed phpBB.<P>Please make sure you have have also edited your config.php file correctly, and that
you have read the INSTALL file that came with this software package.<P>You may now proceed to the <a href="admin/">Administration Area</a> and configure your forums and other settings.<P>Thank you for choosing phpBB<BR> - The phpBB
Team.<BR> - <a href="http://www.phpbb.com/" target="_blank">http://www.phpBB.com/</a></TD>
</TR>
</TABLE></TD></TR></TABLE>		     
<?php
      break;
      }
}
else {  // First screen
	if(!@fopen("config.$phpEx", "a")) {
		echo "<h2>Notice!</h2><br>";
		echo "The file config.$phpEx is not writeable by the web server. In order for the install to proceed this needs to be changed<br>\n";
		echo "You can do this on a Unix based system by issueing the following command:<br>\n";
		echo "<pre>chmod a+w config.$phpEx</pre>\n";
		echo "If you do not have command line access to your webserver your FTP client should be able to change file permissions.<br>\n";
		echo "Once you have changed the permissions on this file simply refresh this page and the install will proceed normally.<br>\n";
		echo "Also, don't forget to remove the writable attribute after running install.$phpEx!!\n";
		exit;
	}

?>
	<FORM METHOD="POST" ACTION="<?php echo $PHP_SELF ?>">
	<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%">
	      <TR>
	      <TD BGCOLOR="#001100">
	      <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
	            <TR BGCOLOR="#6C706D" ALIGN="CENTER">
	            <TD COLSPAN="2"><b>phpBB Installer</b></TD>
	            </TR>
	            <TR BGCOLOR="#2E4460" ALIGN="LEFT">
	            <TD COLSPAN="2">Thank you for choosing phpBB. This install will take only a few minutes of your time and when it is complete you will
	                have a forum that is ready to be posted to.<BR>The first step is to setup your database. In the form below please fill
	                in all areas of the form and press the 'next' button.
	            </TD>
	            </TR>
	            <TR ALIGN="LEFT">
                    <TD BGCOLOR="#6C706D">Database Server Address:</TD>
	            <TD BGCOLOR="#2E4460"><INPUT TYPE="TEXT" NAME="dbserver" SIZE="30" VALUE="localhost"></TD>
	            </TR>
	            <TR>
                    <TD BGCOLOR="#6C706D">Database Name:</TD>
	            <TD BGCOLOR="#2E4460"><INPUT TYPE="TEXT" NAME="dbname" SIZE="30" VALUE="phpBB"></TD>
	            <TR>
	            <TD BGCOLOR="#6C706D">Database User name:</TD>
	            <TD BGCOLOR="#2E4460"><INPUT TYPE="TEXT" NAME="dbuser" SIZE="30" VALUE="root"></TD>
	            </TR>
	            <TR>
                    <TD BGCOLOR="#6C706D">Database Password:</TD>
	            <TD BGCOLOR="#2E4460"><INPUT TYPE="PASSWORD" NAME="dbpass" SIZE="30"></TD>
	            </TR>
	            <TR BGCOLOR="#6C706D" ALIGN="CENTER">
	            <TD COLSPAN="2"><INPUT TYPE="HIDDEN" NAME="next" VALUE="database"><INPUT TYPE="SUBMIT" VALUE="Next >"></TD>
	            </TR>
	      </TABLE>
	</TD>
	</TR>
      </TABLE>
<?php      

}
?>
</BODY>
</HTML>

	
