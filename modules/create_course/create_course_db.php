<?php
/* ========================================================================
 * Open eClass 2.8
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2013  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */


$charset_spec = 'DEFAULT CHARACTER SET=utf8';
db_query("SET storage_engine=MYISAM");

$cdb = db_query("CREATE DATABASE `$repertoire` $charset_spec");
$code = $repertoire;

// select course database
  mysql_select_db($repertoire);

  db_query("CREATE TABLE catagories (
        cat_id int(10) NOT NULL auto_increment,
        cat_title varchar(100),
        cat_order varchar(10),
        PRIMARY KEY (cat_id))
         $charset_spec");


  db_query("CREATE TABLE forums (
     forum_id int(10) NOT NULL auto_increment,
     forum_name varchar(150),
     forum_desc text,
     forum_access int(10) DEFAULT '1',
     forum_moderator int(10),
     forum_topics int(10) DEFAULT '0' NOT NULL,
     forum_posts int(10) DEFAULT '0' NOT NULL,
     forum_last_post_id int(10) DEFAULT '0' NOT NULL,
     cat_id int(10),
     forum_type int(10) DEFAULT '0',
     PRIMARY KEY (forum_id),
     KEY forum_last_post_id (forum_last_post_id))
     $charset_spec");


	db_query("CREATE TABLE posts (
      post_id int(10) NOT NULL auto_increment,
      topic_id int(10) DEFAULT '0' NOT NULL,
      forum_id int(10) DEFAULT '0' NOT NULL,
      poster_id int(10) DEFAULT '0' NOT NULL,
      post_time varchar(20),
      poster_ip varchar(16),
      nom varchar(30),
      prenom varchar(30),
      PRIMARY KEY (post_id),
      KEY post_id (post_id),
      KEY forum_id (forum_id),
      KEY topic_id (topic_id),
      KEY poster_id (poster_id))
       $charset_spec");

      db_query("CREATE TABLE posts_text (
                post_id int(10) DEFAULT '0' NOT NULL,
                post_text text,
                PRIMARY KEY (post_id))
     $charset_spec");

  db_query("CREATE TABLE topics (
               topic_id int(10) NOT NULL auto_increment,
               topic_title varchar(100),
               topic_poster int(10),
               topic_time varchar(20),
               topic_views int(10) DEFAULT '0' NOT NULL,
               topic_replies int(10) DEFAULT '0' NOT NULL,
               topic_last_post_id int(10) DEFAULT '0' NOT NULL,
               forum_id int(10) DEFAULT '0' NOT NULL,
               topic_status int(10) DEFAULT '0' NOT NULL,
               topic_notify int(2) DEFAULT '0',
      nom varchar(30),
      prenom varchar(30),
               PRIMARY KEY (topic_id),
               KEY topic_id (topic_id),
               KEY forum_id (forum_id),
               KEY topic_last_post_id (topic_last_post_id))
     $charset_spec");

db_query("CREATE TABLE exercices (
        id tinyint(4) NOT NULL auto_increment,
        titre varchar(250) default NULL,
        description text,
      type tinyint(4) unsigned NOT NULL default '1',
      StartDate datetime default NULL,
      EndDate datetime default NULL,
      TimeConstrain int(11) default '0',
      AttemptsAllowed int(11) default '0',
      random smallint(6) NOT NULL default '0',
      active tinyint(4) default NULL,
      `public` TINYINT(4) NOT NULL DEFAULT 1,
      results TINYINT(1) NOT NULL DEFAULT '1',
      score TINYINT(1) NOT NULL DEFAULT '1',
      PRIMARY KEY  (id))
       $charset_spec");

 db_query("CREATE TABLE exercise_user_record (
      eurid int(11) NOT NULL auto_increment,
      eid tinyint(4) NOT NULL default '0',
      uid mediumint(8) NOT NULL default '0',
      RecordStartDate datetime NOT NULL default '0000-00-00',
      RecordEndDate datetime default NULL,
      TotalScore float(5,2) NOT NULL default '0',
      TotalWeighting float(5,2) default '0',
      attempt int(11) NOT NULL default '0',
      PRIMARY KEY  (eurid))
       $charset_spec");

// QUESTIONS
db_query("CREATE TABLE questions (
        id int(11) NOT NULL auto_increment,
        question text,
        description text,
        ponderation float(11,2) default NULL,
        q_position int(11) default 1,
        type int(11) default 1,
        PRIMARY KEY  (id))
         $charset_spec");

// REPONSES
db_query("CREATE TABLE reponses (
        id int(11) NOT NULL default '0',
        question_id int(11) NOT NULL default '0',
        reponse text,
        correct int(11) default NULL,
        comment text,
	ponderation float(5,2),
	r_position int(11) default NULL,
        PRIMARY KEY  (id, question_id))
         $charset_spec");

// EXERCISE_QUESTION
db_query("CREATE TABLE exercice_question (
                question_id int(11) NOT NULL default '0',
                exercice_id int(11) NOT NULL default '0',
                PRIMARY KEY  (question_id,exercice_id))
         $charset_spec");


#######################COURSE_DESCRIPTION ################################

db_query("CREATE TABLE `course_description`
(
    `id` TINYINT UNSIGNED DEFAULT '0' NOT NULL,
    `title` VARCHAR(255),
    `content` TEXT,
    `upDate` DATETIME NOT NULL,
    UNIQUE (`id`)
)
 $charset_spec");


#######################ACCUEIL ###########################################

    // arxikopoihsh tou array gia ta checkboxes
    for ($i = 0; $i <= 50; $i++) {
        $sbsystems[$i] = 0;
    }

    // allagh timwn sto array analoga me to poio checkbox exei epilegei
    if (isset($_POST['subsystems'])) {
            foreach ($_POST['subsystems'] as $sb) {
                    $sbsystems[$sb] = 1;
            }
    }

    db_query("CREATE TABLE accueil (
               id int(11) NOT NULL auto_increment,
               rubrique varchar(100), lien varchar(255),
               image varchar(100),
               visible tinyint(4),
               admin varchar(200),
               address varchar(120),
               define_var varchar(50),
               PRIMARY KEY (id))
         $charset_spec");

    db_query("INSERT INTO accueil VALUES (
                '1',
                ".quote($langAgenda).",
                '../../modules/agenda/agenda.php',
                'calendar',
                '1',
                '0',
                '',
                'MODULE_ID_AGENDA')");

    db_query("INSERT INTO accueil VALUES (
               '2',
               ".quote($langLinks).",
               '../../modules/link/link.php',
               'links',
               '1',
               '0',
               '',
               'MODULE_ID_LINKS'
               )");

    db_query("INSERT INTO accueil VALUES (
               '3',
               ".quote($langDoc).",
               '../../modules/document/document.php',
               'docs',
               '1',
               '0',
               '',
               'MODULE_ID_DOCS'
               )");

    db_query("INSERT INTO accueil VALUES (
               '4',
               ".quote($langVideo).",
               '../../modules/video/video.php',
               'videos',
               '0',
               '0',
               '',
               'MODULE_ID_VIDEO'
               )");

    db_query("INSERT INTO accueil VALUES (
               '5',
               ".quote($langWorks).",
               '../../modules/work/work.php',
               'assignments',
               '0',
               '0',
               '',
               'MODULE_ID_ASSIGN'
               )");

    db_query("INSERT INTO accueil VALUES (
               '7',
               ".quote($langAnnouncements).",
               '../../modules/announcements/announcements.php',
               'announcements',
               '1',
               '0',
               '',
               'MODULE_ID_ANNOUNCE'
               )");

    db_query("INSERT INTO accueil VALUES (
               '9',
               ".quote($langForums).",
               '../../modules/phpbb/index.php',
               'forum',
               '0',
               '0',
               '',
               'MODULE_ID_FORUM'
               )");

    db_query("INSERT INTO accueil VALUES (
               '10',
               ".quote($langExercices).",
               '../../modules/exercice/exercice.php',
               'exercise',
               '1',
               '0',
               '',
               'MODULE_ID_EXERCISE'
               )");

    db_query("INSERT INTO accueil VALUES (
                '15',
                ".quote($langGroups).",
                '../../modules/group/group.php',
                'groups',
                '0',
                '0',
                '',
                'MODULE_ID_GROUPS'
                )");

    db_query("INSERT INTO accueil VALUES (
                '16',
                ".quote($langDropBox).",
                '../../modules/dropbox/index.php',
                'dropbox',
                '0',
                '0',
                '',
                'MODULE_ID_DROPBOX'
                )");

    db_query("INSERT INTO accueil VALUES (
                '17',
                ".quote($langGlossary).",
                '../../modules/glossary/glossary.php',
                'glossary',
                '0',
                '0',
                '',
                'MODULE_ID_GLOSSARY'
                )");

    db_query("INSERT INTO accueil VALUES (
            '18',
            ".quote($langEBook).",
            '../../modules/ebook/index.php',
            'ebook',
            '0',
            '0',
            '',
            'MODULE_ID_EBOOK'
            )");

    db_query("INSERT INTO accueil VALUES (
            '19',
            ".quote($langConference).",
            '../../modules/conference/conference.php',
            'conference',
            '0',
            '0',
            '',
            'MODULE_ID_CHAT'
            )");

    db_query("INSERT INTO accueil VALUES (
            '20',
            ".quote($langCourseDescription).",
            '../../modules/course_description/',
            'description',
            '1',
            '0',
            '',
            'MODULE_ID_DESCRIPTION'
            )");

    db_query("INSERT INTO accueil VALUES (
            '21',
            ".quote($langQuestionnaire).",
            '../../modules/questionnaire/questionnaire.php',
            'questionnaire',
            '0',
            '0',
            '',
            'MODULE_ID_QUESTIONNAIRE'
            )");

    db_query("INSERT INTO accueil VALUES (
            '23',
            ".quote($langLearnPath).",
            '../../modules/learnPath/learningPathList.php',
            'lp',
            '0',
            '0',
            '',
            'MODULE_ID_LP'
            )");

    db_query("INSERT INTO accueil VALUES (
            25,
            ".quote($langToolManagement).",
            '../../modules/course_tools/course_tools.php',
            'tooladmin',
            '0',
            '1',
            '',
            'MODULE_ID_TOOLADMIN'
            )");

    db_query("INSERT INTO accueil VALUES (
            '26',
            ".quote($langWiki).",
            '../../modules/wiki/wiki.php',
            'wiki', 
            '0',
            '0',
            '',
            'MODULE_ID_WIKI'
            )");

    db_query("INSERT INTO accueil VALUES (
            '8',
            ".quote($langAdminUsers).",
            '../../modules/user/user.php',
            'users',
            '0',
            '1',
            '',
            'MODULE_ID_USERS'
            )");

    db_query("INSERT INTO accueil VALUES (
            '14',
            ".quote($langModifyInfo).",
            '../../modules/course_info/infocours.php',
            'course_info',
            '0',
            '1',
            '',
            'MODULE_ID_COURSEINFO'
            )");

    db_query("INSERT INTO accueil VALUES (
            '24',
            ".quote($langUsage).",
            '../../modules/usage/usage.php',
            'usage',
            '0',
            '1',
            '',
            'MODULE_ID_USAGE')");

// The Units subsystem is special - neither visible, nor invisible, it doesn't
// appear in the menu, so it gets visibility = 2
db_query("INSERT INTO accueil VALUES (
            '27',
            ".quote($langCourseUnits).",
            '../../modules/units/index.php',
            'description',
            '2',
            '0',
            '',
            'MODULE_ID_UNITS')");


#################################### USAGE ################################
db_query("CREATE TABLE action_types (
            id int(11) NOT NULL auto_increment,
            name varchar(200),
            PRIMARY KEY (id))");
db_query("INSERT INTO action_types VALUES (1, 'access'), (2, 'exit')");
db_query("CREATE TABLE actions (
            id int(11) NOT NULL auto_increment,
            user_id int(11) NOT NULL,
            module_id int(11) NOT NULL,
            action_type_id int(11) NOT NULL,
            date_time DATETIME NOT NULL default '0000-00-00 00:00:00',
            duration int(11) NOT NULL default 900,
            PRIMARY KEY (id))");

db_query("CREATE TABLE logins (
          id int(11) NOT NULL auto_increment,
            user_id int(11) NOT NULL,
      ip char(16) NOT NULL default '0.0.0.0',
            date_time DATETIME NOT NULL default '0000-00-00 00:00:00',
          PRIMARY KEY (id))");

db_query("CREATE TABLE actions_summary (
            id int(11) NOT NULL auto_increment,
            module_id int(11) NOT NULL,
            visits int(11) NOT NULL,
            start_date DATETIME NOT NULL default '0000-00-00 00:00:00',
            end_date DATETIME NOT NULL default '0000-00-00 00:00:00',
            duration int(11) NOT NULL,
            PRIMARY KEY (id))");


#################################### AGENDA ################################
db_query("CREATE TABLE agenda (
    id int(11) NOT NULL auto_increment,
    titre varchar(200),
    contenu text,
    day date NOT NULL default '0000-00-00',
    hour time NOT NULL default '00:00:00',
    lasting varchar(20),
    visibility CHAR(1) NOT NULL DEFAULT 'v',
    PRIMARY KEY (id))
     $charset_spec");

############################# PAGES ###########################################
    db_query("CREATE TABLE pages (
               id int(11) NOT NULL auto_increment,
               url varchar(200),
               titre varchar(200),
               description text,
               PRIMARY KEY (id))
         $charset_spec");

############################# VIDEO ###########################################
    db_query("CREATE TABLE video (
               id int(11) NOT NULL auto_increment,
	       path varchar(255),
               url varchar(200),
               titre varchar(200),
               description text,
               category INT(6) DEFAULT NULL,
               creator varchar(200),
               publisher varchar(200),
               date DATETIME,
               `visible` TINYINT(4) NOT NULL DEFAULT 1,
               `public` TINYINT(4) NOT NULL DEFAULT 1,
               PRIMARY KEY (id))
         $charset_spec");

################################# VIDEO LINKS ################################

        db_query("CREATE TABLE videolinks (
               id int(11) NOT NULL auto_increment,
               url varchar(200),
               titre varchar(200),
               description text,
               category INT(6) DEFAULT NULL,
               creator varchar(200),
               publisher varchar(200),
               date DATETIME,
               `visible` TINYINT(4) NOT NULL DEFAULT 1,
               `public` TINYINT(4) NOT NULL DEFAULT 1,
               PRIMARY KEY (id))
         $charset_spec");
        
        db_query("CREATE TABLE video_category (
            id int(6) NOT NULL auto_increment, 
            name varchar(255) NOT NULL, 
            description text DEFAULT NULL,                         
            PRIMARY KEY (id))
        $charset_spec");

############################# WORKS ###########################################

db_query("CREATE TABLE `assignments` (
    `id` int(11) NOT NULL auto_increment,
    `title` varchar(200) NOT NULL default '',
    `description` text NOT NULL,
    `comments` text NOT NULL,
    `deadline` datetime NOT NULL default '0000-00-00 00:00:00',
    `submission_date` datetime NOT NULL default '0000-00-00 00:00:00',
    `active` char(1) NOT NULL default '1',
    `secret_directory` varchar(30) NOT NULL,
    `group_submissions` CHAR(1) DEFAULT '0' NOT NULL,
    UNIQUE KEY `id` (`id`))
     $charset_spec");

db_query("CREATE TABLE `assignment_submit` (
    `id` int(11) NOT NULL auto_increment,
    `uid` int(11) NOT NULL default '0',
    `assignment_id` int(11) NOT NULL default '0',
    `submission_date` datetime NOT NULL default '0000-00-00 00:00:00',
    `submission_ip` varchar(16) NOT NULL default '',
    `file_path` varchar(200) NOT NULL default '',
    `file_name` varchar(200) NOT NULL default '',
    `comments` text NOT NULL,
    `grade` varchar(50) NOT NULL default '',
    `grade_comments` text NOT NULL,
    `grade_submission_date` date NOT NULL default '0000-00-00',
    `grade_submission_ip` varchar(16) NOT NULL default '',
    `group_id` INT( 11 ) DEFAULT NULL,
    UNIQUE KEY `id` (`id`))
     $charset_spec");

###################################### DROPBOX #####################################

    db_query("CREATE TABLE dropbox_file (
      id int(11) unsigned NOT NULL auto_increment,
      uploaderId int(11) unsigned NOT NULL default '0',
      filename varchar(250) NOT NULL default '',
      real_filename varchar(255) NOT NULL default '',
      filesize int(11) unsigned NOT NULL default '0',
      title varchar(250) default '',
      description text,      
      author varchar(250) default '',
      uploadDate datetime NOT NULL default '0000-00-00 00:00:00',
      lastUploadDate datetime NOT NULL default '0000-00-00 00:00:00',
      PRIMARY KEY (id))
     $charset_spec");

    db_query("CREATE TABLE dropbox_person (
      fileId int(11) unsigned NOT NULL default '0',
      personId int(11) unsigned NOT NULL default '0',
      PRIMARY KEY  (fileId,personId))
     $charset_spec");

    db_query("CREATE TABLE dropbox_post (
      fileId int(11) unsigned NOT NULL default 0,
      recipientId int(11) unsigned NOT NULL default 0,
      PRIMARY KEY  (fileId,recipientId))
     $charset_spec");

#################### QUESTIONNAIRE ###############################################

db_query("CREATE TABLE poll (
      pid int(11) NOT NULL auto_increment,
      creator_id mediumint(8) unsigned NOT NULL default 0,
      course_id varchar(20) NOT NULL default 0,
      name varchar(255) NOT NULL default '',
      creation_date datetime NOT NULL default '0000-00-00 00:00:00',
      start_date datetime NOT NULL default '0000-00-00 00:00:00',
      end_date datetime NOT NULL default '0000-00-00 00:00:00',
      active int(11) NOT NULL default 0,
      description MEDIUMTEXT NOT NULL,
      end_message MEDIUMTEXT NOT NULL,
      anonymized INT(1) NOT NULL DEFAULT 0,
      PRIMARY KEY  (pid))
     $charset_spec");

    db_query("CREATE TABLE poll_answer_record (
      arid int(11) NOT NULL auto_increment,
	pid int(11) NOT NULL default 0,
	qid int(11) NOT NULL default 0,
      	aid int(11) NOT NULL default 0,
	answer_text TEXT NOT NULL,
	user_id int(11) NOT NULL default 0,
      submit_date datetime NOT NULL default '0000-00-00 00:00:00',
      PRIMARY KEY  (arid))
     $charset_spec");

    db_query("CREATE TABLE poll_question (
      pqid bigint(12) NOT NULL AUTO_INCREMENT,
      pid int(11) NOT NULL DEFAULT 0,
      question_text varchar(250) NOT NULL default '',
      qtype tinyint(3) UNSIGNED NOT NULL,
      PRIMARY KEY  (pqid))
     $charset_spec");

    db_query("CREATE TABLE poll_question_answer (
      pqaid int(11) NOT NULL auto_increment,
      pqid int(11) NOT NULL default 0,
      answer_text TEXT NOT NULL,
      PRIMARY KEY  (pqaid))
     $charset_spec");


############################# LEARNING PATH ######################################

db_query("CREATE TABLE `lp_module` (
              `module_id` int(11) NOT NULL auto_increment,
              `name` varchar(255) NOT NULL default '',
              `comment` text NOT NULL,
              `accessibility` enum('PRIVATE','PUBLIC') NOT NULL default 'PRIVATE',
              `startAsset_id` int(11) NOT NULL default 0,
              `contentType` enum('CLARODOC','DOCUMENT','EXERCISE','HANDMADE','SCORM','SCORM_ASSET','LABEL','COURSE_DESCRIPTION','LINK','MEDIA','MEDIALINK') NOT NULL,
              `launch_data` text NOT NULL,
              PRIMARY KEY  (`module_id`)
             )  $charset_spec");
             //COMMENT='List of available modules used in learning paths';

db_query("CREATE TABLE `lp_learnPath` (
              `learnPath_id` int(11) NOT NULL auto_increment,
              `name` varchar(255) NOT NULL default '',
              `comment` text NOT NULL,
              `lock` enum('OPEN','CLOSE') NOT NULL default 'OPEN',
              `visibility` enum('HIDE','SHOW') NOT NULL default 'SHOW',
              `rank` int(11) NOT NULL default 0,
              PRIMARY KEY  (`learnPath_id`),
              UNIQUE KEY rank (`rank`)
            )  $charset_spec");
            //COMMENT='List of learning Paths';

db_query("CREATE TABLE `lp_rel_learnPath_module` (
                `learnPath_module_id` int(11) NOT NULL AUTO_INCREMENT,
                `learnPath_id` int(11) NOT NULL DEFAULT 0,
                `module_id` int(11) NOT NULL DEFAULT 0,
                `lock` enum('OPEN','CLOSE') NOT NULL DEFAULT 'OPEN',
                `visibility` enum('HIDE','SHOW') NOT NULL DEFAULT 'SHOW',
                `specificComment` TEXT NOT NULL,
                `rank` int(11) NOT NULL default '0',
                `parent` int(11) NOT NULL default '0',
                `raw_to_pass` tinyint(4) NOT NULL default '50',
                PRIMARY KEY  (`learnPath_module_id`)
              )  $charset_spec");
              //COMMENT='This table links module to the learning path using them';


db_query("CREATE TABLE `lp_asset` (
              `asset_id` int(11) NOT NULL auto_increment,
              `module_id` int(11) NOT NULL default '0',
              `path` varchar(255) NOT NULL default '',
              `comment` varchar(255) default NULL,
              PRIMARY KEY  (`asset_id`)
            )  $charset_spec");
            //COMMENT='List of resources of module of learning paths';

db_query("CREATE TABLE `lp_user_module_progress` (
              `user_module_progress_id` int(22) NOT NULL auto_increment,
              `user_id` mediumint(9) NOT NULL default '0',
              `learnPath_module_id` int(11) NOT NULL default '0',
              `learnPath_id` int(11) NOT NULL default '0',
              `lesson_location` varchar(255) NOT NULL default '',
              `lesson_status` enum('NOT ATTEMPTED','PASSED','FAILED','COMPLETED','BROWSED','INCOMPLETE','UNKNOWN') NOT NULL default 'NOT ATTEMPTED',
              `entry` enum('AB-INITIO','RESUME','') NOT NULL default 'AB-INITIO',
              `raw` tinyint(4) NOT NULL default '-1',
              `scoreMin` tinyint(4) NOT NULL default '-1',
              `scoreMax` tinyint(4) NOT NULL default '-1',
              `total_time` varchar(13) NOT NULL default '0000:00:00.00',
              `session_time` varchar(13) NOT NULL default '0000:00:00.00',
              `suspend_data` text NOT NULL,
              `credit` enum('CREDIT','NO-CREDIT') NOT NULL default 'NO-CREDIT',
              PRIMARY KEY  (`user_module_progress_id`)
            )  $charset_spec");
            //COMMENT='Record the last known status of the user in the course';

############################# WIKI ######################################

db_query("CREATE TABLE `wiki_properties` (
              `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              `title` VARCHAR(255) NOT NULL DEFAULT '',
              `description` TEXT NULL,
              `group_id` INT(11) NOT NULL DEFAULT 0,
              PRIMARY KEY(`id`)
            )  $charset_spec");

db_query("CREATE TABLE `wiki_acls` (
              `wiki_id` INT(11) UNSIGNED NOT NULL,
              `flag` VARCHAR(255) NOT NULL,
              `value` ENUM('false','true') NOT NULL DEFAULT 'false'
            )  $charset_spec");

db_query("CREATE TABLE `wiki_pages` (
              `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              `wiki_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
              `owner_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
              `title` VARCHAR(255) NOT NULL DEFAULT '',
              `ctime` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
              `last_version` INT(11) UNSIGNED NOT NULL DEFAULT 0,
              `last_mtime` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
              PRIMARY KEY  (`id`)
            )  $charset_spec");

db_query("CREATE TABLE `wiki_pages_content` (
              `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              `pid` INT(11) UNSIGNED NOT NULL DEFAULT 0,
              `editor_id` INT(11) NOT NULL DEFAULT 0,
              `mtime` DATETIME NOT NULL default '0000-00-00 00:00:00',
              `content` TEXT NOT NULL,
              PRIMARY KEY  (`id`)
            )  $charset_spec");

// dhmiourgia full text indexes gia th diadikasia ths anazhthshs
db_query("ALTER TABLE `agenda` ADD FULLTEXT `agenda` (`titre` ,`contenu`)");
db_query("ALTER TABLE `course_description` ADD FULLTEXT `course_description` (`title` ,`content`)");
db_query("ALTER TABLE `exercices` ADD FULLTEXT `exercices` (`titre`,`description`)");
db_query("ALTER TABLE `posts_text` ADD FULLTEXT `posts_text` (`post_text`)");
db_query("ALTER TABLE `forums` ADD FULLTEXT `forums` (`forum_name`,`forum_desc`)");
db_query("ALTER TABLE `video` ADD FULLTEXT `video` (`url` ,`titre` ,`description`)");
db_query("ALTER TABLE `videolinks` ADD FULLTEXT `videolinks` (`url` ,`titre` ,`description`)");

// creation of indexes 
db_query("ALTER TABLE `lp_user_module_progress` ADD INDEX `optimize` (`user_id` , `learnPath_module_id`)");
db_query("ALTER TABLE `actions` ADD INDEX `actionsindex` (`module_id` , `date_time`)"); 
