<?php

// **************************************
// old queries
//  *************************************

        //upgrade queries from 1.2 --> 1.4
        if (!mysql_field_exists("$mysqlMainDb", 'user', 'am'))
                echo add_field('user', 'am', "VARCHAR( 20 ) NOT NULL");
        if (mysql_table_exists($mysqlMainDb, 'todo'))
                db_query("DROP TABLE `todo`");
        // upgrade queries to 1.4
        if (!mysql_field_exists("$mysqlMainDb",'cours','type'))
                echo add_field('cours', 'type', "ENUM('pre', 'post', 'other') DEFAULT 'pre' NOT NULL");
        if (!mysql_field_exists("$mysqlMainDb",'cours','doc_quota'))
                echo add_field('cours', 'doc_quota', "FLOAT DEFAULT '$diskQuotaDocument' NOT NULL");
        if (!mysql_field_exists("$mysqlMainDb",'cours','video_quota'))
                echo add_field('cours', 'video_quota', "FLOAT DEFAULT '$diskQuotaVideo' NOT NULL");
        if (!mysql_field_exists("$mysqlMainDb",'cours','group_quota'))
                echo add_field('cours', 'group_quota', "FLOAT DEFAULT '$diskQuotaGroup' NOT NULL");

        // upgrade query to 1.6
        if (!mysql_field_exists("$mysqlMainDb",'cours','dropbox_quota'))
                echo add_field('cours', 'dropbox_quota', "FLOAT DEFAULT '$diskQuotaDropbox' NOT NULL");

        // upgrade query to 1.7
        if (!mysql_field_exists("$mysqlMainDb", 'annonces','title'))
                echo add_field_after_field('annonces', 'title', 'id', "varchar(255) NULL");
        if (!mysql_field_exists("$mysqlMainDb", 'prof_request','statut'))
                echo add_field('prof_request', 'statut', "tinyint(4) NOT NULL default 1");

        // ***********************************************
        // new queries - upgrade queries to 2.0
        // ***********************************************

	// delete deprecated tables
	if (mysql_table_exists($mysqlMainDb, 'institution'))
                db_query("DROP TABLE `institution`");

        if (!mysql_field_exists("$mysqlMainDb",'cours','course_keywords'))
                echo add_field('cours', 'course_keywords', "TEXT");
        if (!mysql_field_exists("$mysqlMainDb",'cours','course_addon'))
                echo add_field('cours', 'course_addon', "TEXT");
        if (!mysql_field_exists("$mysqlMainDb",'cours','first_create'))
                echo add_field('cours', 'first_create', "datetime not null default '0000-00-00 00:00:00'");

        // delete useless fields
        if (mysql_field_exists("$mysqlMainDb",'cours','cahier_charges'))
                echo delete_field('cours', 'cahier_charges');
        if (mysql_field_exists("$mysqlMainDb",'cours','versionDb'))
                echo delete_field('cours', 'versionDb');
        if (mysql_field_exists("$mysqlMainDb",'cours','versionClaro'))
                echo delete_field('cours', 'versionClaro');
        if (mysql_field_exists("$mysqlMainDb",'user','inst_id'))
                echo delete_field('user', 'inst_id');
	if (mysql_field_exists("$mysqlMainDb",'cours_user','role'))
                echo delete_field('cours_user', 'role');

	// add field to cours_user to keep track course user registration date
	if (!mysql_field_exists($mysqlMainDb,'cours_user','reg_date')) {
                echo add_field('cours_user','reg_date',"DATE NOT NULL");
		db_query("UPDATE cours_user SET reg_date=NOW()");
	} else {
		$min_reg_date_res = mysql_fetch_row(db_query("SELECT MIN(reg_date) 
				FROM cours_user WHERE reg_date <> '0000-00-00'"));
		$min_reg_date = $min_reg_date_res[0]? ("'" . $min_reg_date_res[0] . "'"): 'NOW()';
		db_query("UPDATE cours_user SET reg_date=$min_reg_date WHERE reg_date = '0000-00-00'");
	}

        // kstratos - UOM
        // Add 1 new field into table 'prof_request', after the field 'profuname'
        $reg = time();
        $exp = 126144000 + $reg;
        if (!mysql_field_exists($mysqlMainDb,'prof_request','profpassword'))
                echo add_field('prof_request','profpassword',"VARCHAR(255)");
        if (!mysql_field_exists($mysqlMainDb,'prof_request','lang'))
		echo add_field('prof_request','lang',"ENUM( 'el', 'en' ) NOT NULL DEFAULT 'el'");

        // Add 2 new fields into table 'user': registered_at,expires_at
        if (!mysql_field_exists($mysqlMainDb,'user','registered_at'))
                echo add_field('user', 'registered_at', "INT(10) DEFAULT $reg NOT NULL");
        if (!mysql_field_exists($mysqlMainDb,'user','expires_at'))
                echo add_field('user', 'expires_at', "INT(10) DEFAULT $exp NOT NULL");

        // Add 2 new fields into table 'cours': password,faculteid
        if (!mysql_field_exists($mysqlMainDb,'cours','password'))
                echo add_field('cours', 'password', "VARCHAR(50)");

        // vagpits: update cours.faculteid with id from faculte
        if (!mysql_field_exists($mysqlMainDb,'cours','faculteid')) {
                echo add_field('cours', 'faculteid', "INT(11)");
                mysql_query("UPDATE cours,faculte SET cours.faculteid = faculte.id
                                WHERE cours.faculte = faculte.name");
        }

        // Add 1 new field into table 'cours_faculte': facid
        // vagpits: update cours.faculteid with id from faculte
        if (!mysql_field_exists($mysqlMainDb,'cours_faculte','facid')) {
                echo add_field('cours_faculte', 'facid', "INT(11)");
                mysql_query("UPDATE cours_faculte,faculte SET cours_faculte.facid = faculte.id
                                WHERE cours_faculte.faculte = faculte.name");
        }

        // *****************************
        // new tables added
        // *****************************
        // haniotak:: new table for loginout summary
        if (!mysql_table_exists($mysqlMainDb, 'loginout_summary'))  {
                mysql_query("CREATE TABLE loginout_summary (
                        id mediumint unsigned NOT NULL auto_increment,
                           login_sum int(11) unsigned  NOT NULL default '0',
                           start_date datetime NOT NULL default '0000-00-00 00:00:00',
                           end_date datetime NOT NULL default '0000-00-00 00:00:00',
                           PRIMARY KEY  (id))
                                TYPE=MyISAM DEFAULT CHARACTER SET=utf8");
        }
        // new table for monthly summary
        if (!mysql_table_exists($mysqlMainDb, 'monthly_summary'))  {
                mysql_query("CREATE TABLE monthly_summary (
                        id mediumint unsigned NOT NULL auto_increment,
                           `month` varchar(20)  NOT NULL default '0',
                           profesNum int(11) NOT NULL default '0',
                           studNum int(11) NOT NULL default '0',
                           visitorsNum int(11) NOT NULL default '0',
                           coursNum int(11) NOT NULL default '0',
                           logins int(11) NOT NULL default '0',
                           details text NOT NULL default '',
                           PRIMARY KEY  (id))
                                TYPE=MyISAM DEFAULT CHARACTER SET=utf8");
        }
        // new table 'auth' with auth methods
        if(!mysql_table_exists($mysqlMainDb, 'auth')) {
                db_query("CREATE TABLE `auth` (
                        `auth_id` int( 2 ) NOT NULL AUTO_INCREMENT ,
                        `auth_name` varchar( 20 ) NOT NULL default '',
                        `auth_settings` text NOT NULL default '',
                        `auth_instructions` text NOT NULL default '',
                        `auth_default` tinyint( 1 ) NOT NULL default '0',
                        PRIMARY KEY ( `auth_id` )) ",$mysqlMainDb); //TYPE = MYISAM  COMMENT='New table with auth methods in Eclass 2.0'
                  // Insert the default values into the new table 'auth'
                   	db_query("INSERT INTO `auth` VALUES (1, 'eclass', '', '', 1)");
                	db_query("INSERT INTO `auth` VALUES (2, 'pop3', '', '', 0)");
                	db_query("INSERT INTO `auth` VALUES (3, 'imap', '', '', 0)");
                	db_query("INSERT INTO `auth` VALUES (4, 'ldap', '', '', 0)");
                	db_query("INSERT INTO `auth` VALUES (5, 'db', '', '', 0)");
        }

        //Table agenda (stores events from all lessons)
        if (!mysql_table_exists($mysqlMainDb, 'agenda'))  {
                db_query("CREATE TABLE `agenda` (
                        `id` int(11) NOT NULL auto_increment,
                        `lesson_event_id` int(11) NOT NULL default '0',
                        `titre` varchar(200) NOT NULL default '',
                        `contenu` text NOT NULL,
                        `day` date NOT NULL default '0000-00-00',
                        `hour` time NOT NULL default '00:00:00',
                        `lasting` varchar(20) NOT NULL default '',
                        `lesson_code` varchar(50) NOT NULL default '',
                        PRIMARY KEY  (`id`)) TYPE=MyISAM ", $mysqlMainDb);
        }

        // table admin_announcemets (stores administrator  announcements)
        if (!mysql_table_exists($mysqlMainDb, 'admin_announcements'))  {
                db_query("CREATE TABLE `admin_announcements` (
                        `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                        `gr_title` VARCHAR( 255 ) NULL ,
                        `gr_body` TEXT NULL ,
                        `gr_comment` VARCHAR( 255 ) NULL ,
                        `en_title` VARCHAR( 255 ) NULL ,
                        `en_body` TEXT NULL ,
                        `en_comment` VARCHAR( 255 ) NULL ,
                        `date` DATE NOT NULL ,
                        `visible` ENUM( 'V', 'I' ) NOT NULL
                        ) TYPE = MYISAM ", $mysqlMainDb);
        }

        // Table passwd_reset (used by the password reset module)
        if (!mysql_table_exists($mysqlMainDb, 'passwd_reset'))  {
                db_query("CREATE TABLE `passwd_reset` (
                              `user_id` int(11) NOT NULL,
                              `hash` varchar(40) NOT NULL,
                              `password` varchar(8) NOT NULL,
                              `datetime` datetime NOT NULL
                              ) TYPE=MyISAM", $mysqlMainDb);
        	}

        // add 5 new fields to table users
        if (!mysql_field_exists("$mysqlMainDb",'user','perso'))
                echo add_field('user', 'perso', "enum('yes','no') NOT NULL default 'yes'");
        if (!mysql_field_exists("$mysqlMainDb",'user','announce_flag'))
                echo add_field('user', 'announce_flag', "date NOT NULL default '0000-00-00'");
        if (!mysql_field_exists("$mysqlMainDb",'user','doc_flag'))
                echo add_field('user', 'doc_flag', "date NOT NULL default '0000-00-00'");
        if (!mysql_field_exists("$mysqlMainDb",'user','forum_flag'))
                echo add_field('user', 'forum_flag', "date NOT NULL default '0000-00-00'");
        if (!mysql_field_exists("$mysqlMainDb",'user','lang'))
                echo add_field('user', 'lang', "ENUM('el', 'en') DEFAULT 'el' NOT NULL");

        // add full text indexes for search operation
        @mysql_query("ALTER TABLE `annonces` ADD FULLTEXT `annonces` (`contenu` ,`code_cours`)");
        @mysql_query("ALTER TABLE `cours` ADD FULLTEXT `cours` (`code`, `description`, `intitule`, `course_keywords`)");

        // encrypt passwords in users table
        if (!isset($encryptedPasswd)) {
                echo "<p>$langEncryptPass</p>";
                flush();
                if ($res = db_query("SELECT user_id, password FROM user")) {
                        while ($row = mysql_fetch_array($res)) {
                                $pass = $row["password"];
                                if (!in_array($pass,$auth_methods)) {
                                        $newpass = md5(iconv('ISO-8859-7', 'UTF-8', $pass));
                                        // do the update
                                        db_query("UPDATE user SET password = '$newpass'
                                                        WHERE user_id = $row[user_id]");
                                }
                        }
                } else {
                        die("$langNotEncrypted");
                }
        }

        // update users with no registration date
        $res = db_query("SELECT user_id,registered_at,expires_at FROM user
                        WHERE registered_at='0'
                        OR registered_at='NULL' OR registered_at=NULL
                        OR registered_at='null' OR registered_at=null
                        OR registered_at='\N' OR registered_at=\N
                        OR registered_at=''");

                while ($row = mysql_fetch_array($res)) {
                        $registered_at = $row["registered_at"];
                        $regtime = 126144000+time();
                        db_query("UPDATE user SET registered_at=".time().",expires_at=".$regtime);
                }


        //Empty table 'agenda' in the main database so that we do not have multiple entries
        //in case we run the upgrade script twice. This has to be done at this point and NOT
        //in the while loop. Otherwise it will be emptying the table for each iteration
        $sql = 'TRUNCATE TABLE `agenda`';
        db_query($sql);

        // add indexes
        add_index('i_cours', 'cours', 'code');
        add_index('i_loginout', 'loginout', 'id_user');
        add_index('i_action', 'loginout', 'action');
        add_index('i_codecours', 'annonces', 'code_cours' );
        add_index('i_temps', 'annonces', 'temps');
