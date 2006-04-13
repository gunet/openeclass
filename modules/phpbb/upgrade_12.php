<?php
/***************************************************************************
*                           upgrade_12.php  -  description
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
include('config.' . $phpEx);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Strict//EN">
          <HTML>
          <HEAD>
          <TITLE>phpBB - Database upgrade 1.0 to 1.2</TITLE>
          </HEAD>
          <BODY BGCOLOR="#000000" TEXT="#FFFFFF" LINK="#11C6BD" VLINK="#11C6BD">


<?php
if($next) {
	switch($next) {
	case 'verify':

	echo "<H2>Step 1: Verify that the database has not already been upgraded.</H2><BR>";

	if(!$db = mysql_connect("$dbhost", "$dbuser", "$dbpasswd"))
		die("<font color=\"#FF0000\">Error, I could not connect to the database at $dbhost. Using username $dbuser.<BR>Please go back and try again.</font>");

	if(!@mysql_select_db("$dbname", $db))
		die("<font color=\"#FF0000\">Database $dbname could not be found</font>"); 

	echo "Checking for default_lang in the config table... <BR>";
	flush();

	$r = mysql_query("select default_lang from config limit 1", $db);

	if (1054 != mysql_errno($db)) {
		die ("<font color=\"#FF0000\">The column exists, the database has already been upgraded.</font>");
	} else {
		echo "&nbsp;&nbsp;&nbsp;OK, it does not exist.<BR>";
	}

	echo "Checking for user_lang in the users table... <BR>";
	flush();

	$r = mysql_query("select user_lang from users limit 1", $db);

	if (1054 != mysql_errno($db)) {
		die ("<font color=\"#FF0000\">The column exists, the database has already been upgraded.</font>");
	} else {
		echo "&nbsp;&nbsp;&nbsp;OK, it does not exist.<BR>";
	}

	echo "Checking for forum_pass in the forums table... <BR>";
	flush();

	$r = mysql_query("select forum_pass from forums limit 1", $db);

	if (0 != mysql_errno($db)) {
		die ("<font color=\"#FF0000\">The column does not exist, the database has already been upgraded.</font>");
	} else {
		echo "&nbsp;&nbsp;&nbsp;OK, it exists.<BR>";
	}

	echo "Checking for the forum_access  table... <BR>";
	flush();

	$r = mysql_query("select * from forum_accessx limit 1", $db);

	if (1146 != mysql_errno($db)) {
		die ("<font color=\"#FF0000\">The table exists, the database has already been upgraded.</font>");
	} else {
		echo "&nbsp;&nbsp;&nbsp;OK, it does not exist.<BR>";
	}

	echo "Good!<BR>";
?>
<FORM METHOD="POST" ACTION="<?php echo $PHP_SELF ?>">
<INPUT TYPE="HIDDEN" NAME="next" VALUE="backup">
<INPUT TYPE="SUBMIT" VALUE="Next >">

<?php
	break;

	case 'backup':

	echo "<H2>Step 2: Backup the tables to be modified.</H2><BR>";

	if(!$db = mysql_connect("$dbhost", "$dbuser", "$dbpasswd"))
		die("<font color=\"#FF0000\">Error, I could not connect to the database at $dbhost. Using username $dbuser.<BR>Please go back and try again.</font>");

	if(!@mysql_select_db("$dbname", $db))
		die("<font color=\"#FF0000\">Database $dbname could not be found</font>"); 

	$tables = array("config", "forums", "users");

	while (list($key, $table_name) = each($tables)) {
		echo "Backing up the $table_name table... <BR>";

		$backup_name = $table_name . "_backup";
		$table_create = "CREATE TABLE $backup_name (\n";

		$r = mysql_query("show fields from $table_name", $db);

		if (0 != mysql_errno($db)) {
			die("<font color=\"#FF0000\">Error, could not backup the table $table_name to $backup_name</font>");
		} else {
			while ($row = mysql_fetch_array($r)) {
				$table_create .= "	$row[Field] $row[Type]";
				if (isset($row["Default"]) && (!empty($row["Default"]) || $row["Default"] == "0"))
					$table_create .= " DEFAULT '$row[Default]'";
				if ($row["Null"] != "YES")
					$table_create .= " NOT NULL";
				if ($row["Extra"] != "")
					$table_create .= " $row[Extra]";
				$table_create .= ",\n";
			}
			/* The code above leaves extra ',' at the end of the row.  use ereg_replace to remove it */
			$table_create = ereg_replace(",\n$", "", $table_create);

			echo "&nbsp;&nbsp;&nbsp; Extracted the table columns ...<br>\n";

			$r = mysql_query("SHOW KEYS FROM $table_name", $db);
			while($row = mysql_fetch_array($r)) {
				$key_name = $row['Key_name'];
				unset($index);
				if (($key_name != "PRIMARY") && ($row['Non_unique'] == 0))
					$key_name = "UNIQUE|$key_name";
				if (!isset($index[$key_name]))
					$index[$key_name] = array();
				$index[$key_name][] = $row['Column_name'];
			}

			while(list($x, $columns) = @each($index)) {
				$table_create .= ",\n";
				if ($x == "PRIMARY")
					$table_create .= "   PRIMARY KEY (" . implode($columns, ", ") . ")";
				elseif (substr($x,0,6) == "UNIQUE")
					$table_create .= "   UNIQUE " .substr($x,7). " (" . implode($columns, ", ") . ")";
				else
					$table_create .= "   KEY $x (" . implode($columns, ", ") . ")";
			}

			echo "&nbsp;&nbsp;&nbsp; Extracted the table indexes ...<br>\n";

			$table_create .= "\n)";
			mysql_query($table_create, $db);
			echo "&nbsp;&nbsp;&nbsp; Created the backup table $backup_name ...<br>\n";

			mysql_query("insert into $backup_name select * from $table_name", $db);
			echo "&nbsp;&nbsp;&nbsp; Copied the data from $table_name to $backup_name...<br>\n";
		}
	}
?>
Backups completed ok.<P>
<FORM METHOD="POST" ACTION="<?php echo $PHP_SELF ?>">
<INPUT TYPE="HIDDEN" NAME="next" VALUE="alter">
<INPUT TYPE="SUBMIT" VALUE="Next >">
<?php
	break;

	case 'alter':

	echo "<H2>Step 3: Alter the tables to add default language and add private forum security.</H2><BR>";

	if(!$db = mysql_connect("$dbhost", "$dbuser", "$dbpasswd"))
		die("<font color=\"#FF0000\">Error, I could not connect to the database at $dbhost. Using username $dbuser.<BR>Please go back and try again.</font>");

	if(!@mysql_select_db("$dbname", $db))
		die("<font color=\"#FF0000\">Database $dbname could not be found</font>"); 

	$tables = array("config"=>"default_lang", "users"=>"user_lang", "priv_msgs"=>"msg_status");

	while (list($table_name, $field_name) = each($tables)) {
	   echo "Altering table $table_name to add default language... <BR>";
	   if($table_name == "priv_msgs") {
	      $r = mysql_query("alter table $table_name add $field_name int(10) DEFAULT '0'", $db);
	   }
	   else {
	      $r = mysql_query("alter table $table_name add $field_name varchar(255)", $db);
	   }
	   
	   if (0 != mysql_errno($db)) {
	      die("<font color=\"#FF0000\">Error, could not alter the table $table_name</font>");
	   } else {
	      echo "&nbsp;&nbsp;&nbsp;Completed!<BR>";
	   }
	}
	   echo "Inserting Default Language (English) into config table...<br>";
	   $r = mysql_query("update config set default_lang = 'english'", $db);
	   
	   if (0 != mysql_errno($db)) {                                                                                        
	      die("<font color=\"#FF0000\">Error, could not update the config table!</font>");                         
	   } else {                                                                                                            
	      echo "&nbsp;&nbsp;&nbsp;Completed!<BR>";                                                                    
	   }  
	   
	   echo "Altering table forums to remove forum_pass... <BR>";
	   
	$r = mysql_query("alter table forums drop forum_pass", $db);

	if (0 != mysql_errno($db)) {
		die("<font color=\"#FF0000\">Error, could not alter the table forums</font>");
	} else {
		echo "&nbsp;&nbsp;&nbsp;Completed!<BR>";
	}

	$forum_access = "CREATE TABLE forum_access(
						   forum_id int(10) NOT NULL,
						   user_id  int(10) NOT NULL,
						   can_post tinyint(1) NOT NULL DEFAULT '0',
						   PRIMARY KEY(forum_id, user_id))";
	 
	echo "Adding new private message security table... <BR>";

	$r = mysql_query($forum_access, $db);

	if (0 != mysql_errno($db)) {
		die("<font color=\"#FF0000\">Error, could not add the table forum_access</font>");
	} else {
		echo "&nbsp;&nbsp;&nbsp;Completed!<BR>";
	}
?>
<FORM METHOD="POST" ACTION="<?php echo $PHP_SELF ?>">
<INPUT TYPE="HIDDEN" NAME="next" VALUE="http">
<INPUT TYPE="SUBMIT" VALUE="Next >">

<?php
		break;

	case 'http';

	echo "<H2>Step 4: Clean up homepage URL's.</H2><BR>";

	if(!$db = mysql_connect("$dbhost", "$dbuser", "$dbpasswd"))
		die("<font color=\"#FF0000\">Error, I could not connect to the database at $dbhost. Using username $dbuser.<BR>Please go back and try again.</font>");

	if(!@mysql_select_db("$dbname", $db))
		die("<font color=\"#FF0000\">Database $dbname could not be found</font>"); 

	$r = mysql_query("select user_id, user_website from users", $db);

	while ($row = mysql_fetch_array($r)) {
		$website = trim($row["user_website"]);

		if (($website != "") && (substr(strtolower($website), 0, 7) !=  "http://")) {
			$user_id = $row["user_id"];
			echo "correcting user $user_id for website $website<BR>";
			$website = "http://" . $website;

			mysql_query("update users set user_website = '$website' where user_id = $user_id", $db);
		}
	}
?>
All Done.
<FORM METHOD="POST" ACTION="<?php echo $PHP_SELF ?>">
<INPUT TYPE="HIDDEN" NAME="next" VALUE="clean">
<INPUT TYPE="SUBMIT" VALUE="Next >">

<?php
		break;

	case 'clean';
	echo "<H2>Step 5: All done!.</H2><BR>";
	echo "Click <a href=\"http://$SERVER_NAME$PHP_SELF?next=droptables\">here</a> to remove the database backup tables that this script created.<br>";
	echo "It is safe to leave them in the database but it will take up more disk space.";
?>
All done!
<?php
		break;
	 case 'droptables':
		if(!$db = mysql_connect("$dbhost", "$dbuser", "$dbpasswd"))
			die("<font color=\"#FF0000\">Error, I could not connect to the database at $dbhost. Using username $dbuser.<BR>Please go back and try again!");
		if(!@mysql_select_db("$dbname", $db))
			die("<font color=\"#FF0000\">Database $dbname could not be found</font>");

		$tables = array("config", "forums", "users");
		while(list($key, $table_name) = each($tables)) {
			$sql = "DROP TABLE IF EXISTS ".$table_name."_backup";
			if(!$r = mysql_query($sql, $db))
				die("Error could not drop ".$table_name."_backup because ".mysql_error($db)."<br>");
		}
		echo "<h2>Clean!</h2><br>";
		echo "Your upgrade should now be 100% complete and the database backups removed. Enjoy phpBB v1.2.0<br>";
		echo "Thank you,<br> The phpBB Group.";

		break;
	}
}
else {
?>
	<FORM METHOD="POST" ACTION="<?php echo $PHP_SELF ?>">
      Welcome!  This script will upgrade your phpBB v1.0 database to version 1.2.<br>
		The upgrade will perform the following functions:

		<UL>
			<LI>Verify that the database has not already been upgraded.
         <LI>Back up all modified database tables.
			<LI>Add new fields to the database for translation support.
			<LI>Add a new table to the database for better private forum security.
			<LI>add http:// to the beginning of any homepage URL&quot;s that are missing them.
			<LI>Give you a URL, which you may use later to delete the backed up tables.
		</UL>
		<INPUT TYPE="HIDDEN" NAME="next" VALUE="verify">
		<INPUT TYPE="SUBMIT" VALUE="Next >">
   </FORM>
<?php      

}
?>
</BODY>
</HTML>
