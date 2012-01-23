<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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

// create phpbb 1.4 tables
  db_query("CREATE TABLE catagories (
        cat_id int(10) NOT NULL auto_increment,
        cat_title varchar(100),
        cat_order varchar(10),
        PRIMARY KEY (cat_id))
         $charset_spec");

  // Create an example category
  db_query("INSERT INTO catagories VALUES (2,'$langCatagoryMain',NULL)");

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

  db_query("INSERT INTO forums VALUES (1,'$langTestForum','$langDelAdmin',2,1,0,0,0,2,0)");

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
      results TINYINT(1) NOT NULL DEFAULT '1',
      score TINYINT(1) NOT NULL DEFAULT '1',
      PRIMARY KEY  (id))
       $charset_spec");

 db_query("CREATE TABLE exercise_user_record (
      eurid int(11) NOT NULL auto_increment,
      eid tinyint(4) NOT NULL default '0',
      uid mediumint(8) NOT NULL default '0',
      RecordStartDate datetime NOT NULL default '0000-00-00',
      RecordEndDate datetime NOT NULL default '0000-00-00',
      TotalScore int(11) NOT NULL default '0',
      TotalWeighting int(11) default '0',
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

// Content accueil (homepage) Table
    db_query("INSERT INTO accueil VALUES (
                '1',
                '$langAgenda',
                '../../modules/agenda/agenda.php',
                'calendar',
                '".$sbsystems[1]."',
                '0',
                '',
                'MODULE_ID_AGENDA')");

    db_query("INSERT INTO accueil VALUES (
               '2',
               '$langLinks',
               '../../modules/link/link.php',
               'links',
               '".$sbsystems[2]."',
               '0',
               '',
               'MODULE_ID_LINKS'
               )");

    db_query("INSERT INTO accueil VALUES (
               '3',
               '$langDoc',
               '../../modules/document/document.php',
               'docs',
               '".$sbsystems[3]."',
               '0',
               '',
               'MODULE_ID_DOCS'
               )");
    //den yparxei akomh MODULE_ID_ gia to module VIDEO opote prepei na symplhrwthei
    db_query("INSERT INTO accueil VALUES (
               '4',
               '$langVideo',
               '../../modules/video/video.php',
               'videos',
               '".$sbsystems[4]."',
               '0',
               '',
               'MODULE_ID_VIDEO'
               )");

db_query("INSERT INTO accueil VALUES (
               '5',
               '$langWorks',
               '../../modules/work/work.php',
               'assignments',
               '".$sbsystems[5]."',
               '0',
               '',
               'MODULE_ID_ASSIGN'
               )");

    db_query("INSERT INTO accueil VALUES (
               '7',
               '$langAnnouncements',
               '../../modules/announcements/announcements.php',
               'announcements',
               '".$sbsystems[7]."',
               '0',
               '',
               'MODULE_ID_ANNOUNCE'
               )");

    db_query("INSERT INTO accueil VALUES (
               '9',
               '$langForums',
               '../../modules/phpbb/index.php',
               'forum',
               '".$sbsystems[9]."',
               '0',
               '',
               'MODULE_ID_FORUM'
               )");

    db_query("INSERT INTO accueil VALUES (
               '10',
               '$langExercices',
               '../../modules/exercice/exercice.php',
               'exercise',
               '".$sbsystems[10]."',
               '0',
               '',
               'MODULE_ID_EXERCISE'
               )");

	db_query("INSERT INTO accueil VALUES (
        '15',
        '$langGroups',
        '../../modules/group/group.php',
        'groups',
        '".$sbsystems[15]."',
        '0',
        '',
        'MODULE_ID_GROUPS'
        )");

    db_query("INSERT INTO accueil VALUES (
        '16',
        '$langDropBox',
        '../../modules/dropbox/index.php',
        'dropbox',
        '".$sbsystems[16]."',
        '0',
        '',
        'MODULE_ID_DROPBOX'
        )");

    db_query("INSERT INTO accueil VALUES (
        '17',
        '$langGlossary',
        '../../modules/glossary/glossary.php',
        'glossary',
        '".$sbsystems[17]."',
        '0',
        '',
        'MODULE_ID_GLOSSARY'
        )");

    db_query("INSERT INTO accueil VALUES (
        '18',
        '$langEBook',
        '../../modules/ebook/index.php',
        'ebook',
        '".$sbsystems[18]."',
        '0',
        '',
        'MODULE_ID_EBOOK'
        )");

    db_query("INSERT INTO accueil VALUES (
                '19',
                '$langConference',
                '../../modules/conference/conference.php',
                'conference',
                '".$sbsystems[19]."',
                '0',
                '',
                'MODULE_ID_CHAT'
                )");

    db_query("INSERT INTO accueil VALUES (
               '20',
               '$langCourseDescription',
               '../../modules/course_description/',
               'description',
               '".$sbsystems[20]."',
               '0',
               '',
               'MODULE_ID_DESCRIPTION'
               )");

db_query("INSERT INTO accueil VALUES (
                '21',
                '$langQuestionnaire',
                '../../modules/questionnaire/questionnaire.php',
                'questionnaire',
                '".$sbsystems[21]."',
                '0',
                '',
                'MODULE_ID_QUESTIONNAIRE'
                )");

    db_query("INSERT INTO accueil VALUES (
               '23',
               '$langLearnPath',
               '../../modules/learnPath/learningPathList.php',
               'lp',
               '".$sbsystems[23]."',
               '0',
               '',
               'MODULE_ID_LP'
               )");

    db_query("INSERT INTO accueil VALUES (
               25,
               '$langToolManagement',
               '../../modules/course_tools/course_tools.php',
               'tooladmin',
               '0',
               '1',
               '',
               'MODULE_ID_TOOLADMIN'
               )");

    db_query("INSERT INTO accueil VALUES (
               '26',
               '$langWiki',
               '../../modules/wiki/wiki.php',
               'wiki',
               '".$sbsystems[26]."',
               '0',
               '',
               'MODULE_ID_WIKI'
               )");

        db_query("INSERT INTO accueil VALUES (
        '8',
        '$langAdminUsers',
        '../../modules/user/user.php',
        'users',
        '0',
        '1',
        '',
        'MODULE_ID_USERS'
        )");

db_query("INSERT INTO accueil VALUES (
               '14',
               '$langModifyInfo',
               '../../modules/course_info/infocours.php',
               'course_info',
               '".$sbsystems[14]."',
               '1',
               '',
               'MODULE_ID_COURSEINFO'
               )");

db_query("INSERT INTO accueil VALUES (
                '24',
                '".$langUsage."',
                '../../modules/usage/usage.php',
                'usage',
                '".$sbsystems[24]."',
                '1',
                '',
                'MODULE_ID_USAGE')");

// The Units subsystem is special - neither visible, nor invisible, it doesn't
// appear in the menu, so it gets visibility = 2
$sbsystems[27] = 2;
db_query("INSERT INTO accueil VALUES (
                '27',
                '".$langCourseUnits."',
                '../../modules/units/index.php',
                'description',
                '".$sbsystems[27]."',
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
      qtype ENUM('multiple', 'fill') NOT NULL,
      PRIMARY KEY  (pqid))
     $charset_spec");

    db_query("CREATE TABLE poll_question_answer (
      pqaid int(11) NOT NULL auto_increment,
      pqid int(11) NOT NULL default 0,
      answer_text TEXT NOT NULL,
      PRIMARY KEY  (pqaid))
     $charset_spec");


// dhmiourgia full text indexes gia th diadikasia ths anazhthshs
db_query("ALTER TABLE `agenda` ADD FULLTEXT `agenda` (`titre` ,`contenu`)");
db_query("ALTER TABLE `course_description` ADD FULLTEXT `course_description` (`title` ,`content`)");
db_query("ALTER TABLE `exercices` ADD FULLTEXT `exercices` (`titre`,`description`)");
db_query("ALTER TABLE `posts_text` ADD FULLTEXT `posts_text` (`post_text`)");
db_query("ALTER TABLE `forums` ADD FULLTEXT `forums` (`forum_name`,`forum_desc`)");

// creation of indexes 
db_query("ALTER TABLE `actions` ADD INDEX `actionsindex` (`module_id` , `date_time`)"); 
