<?
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/

$charset_spec = 'DEFAULT CHARACTER SET=utf8';

$cdb=mysql_query("CREATE DATABASE `$repertoire` $charset_spec");
$code=$repertoire;

// select course database
    mysql_select_db($repertoire);

// create phpbb 1.4 tables
  mysql_query("CREATE TABLE catagories (
        cat_id int(10) NOT NULL auto_increment,
        cat_title varchar(100),
        cat_order varchar(10),
        PRIMARY KEY (cat_id))
        TYPE=MyISAM $charset_spec");

  // Create an example category
  mysql_query("INSERT INTO catagories VALUES (2,'$langCatagoryMain',NULL)");

  mysql_query("CREATE TABLE forums (
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
    TYPE=MyISAM $charset_spec");

  mysql_query("INSERT INTO forums VALUES (1,'$langTestForum','$langDelAdmin',2,1,0,0,0,2,0)");

	mysql_query("CREATE TABLE posts (
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
      TYPE=MyISAM $charset_spec");

      mysql_query("CREATE TABLE posts_text (
                post_id int(10) DEFAULT '0' NOT NULL,
                post_text text,
                PRIMARY KEY (post_id))
    TYPE=MyISAM $charset_spec");

  mysql_query("CREATE TABLE topics (
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
    TYPE=MyISAM $charset_spec");

  mysql_query("CREATE TABLE users (
               user_id int(10) NOT NULL auto_increment,
               username varchar(40) NOT NULL,
               user_regdate varchar(20) NOT NULL,
               user_password varchar(32) NOT NULL,
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
               PRIMARY KEY (user_id))
        TYPE=MyISAM $charset_spec");

	mysql_query("INSERT INTO users VALUES (
               '1',
               '$nom $prenom',
               NOW(),
               'password',
               '$email',
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               '0',
               '0',
               '0',
               '0',
               '0',
               '0',
               '1',
               NULL,
               NULL,
               NULL
               )");


mysql_query("INSERT INTO users VALUES (
               '-1',
               '$langAnonymous',
               NOW(),
               'password',
               '',
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               NULL,
               '0',
               '0',
               '0',
               '0',
               '0',
               '0',
               '1',
               NULL,
               NULL,
               NULL
               )");

mysql_query("CREATE TABLE exercices (
        id tinyint(4) NOT NULL auto_increment,
        titre varchar(250) default NULL,
        description text,
      type tinyint(4) unsigned NOT NULL default '1',
      StartDate date default NULL,
      EndDate date default NULL,
      TimeConstrain int(11) default '0',
      AttemptsAllowed int(11) default '0',
      random smallint(6) NOT NULL default '0',
      active tinyint(4) default NULL,
      results TINYINT(1) NOT NULL DEFAULT '1',
      score TINYINT(1) NOT NULL DEFAULT '1',
      PRIMARY KEY  (id))
      TYPE=MyISAM $charset_spec");

 mysql_query("CREATE TABLE exercise_user_record (
      eurid int(11) NOT NULL auto_increment,
      eid tinyint(4) NOT NULL default '0',
      uid mediumint(8) NOT NULL default '0',
      RecordStartDate datetime NOT NULL default '0000-00-00',
      RecordEndDate datetime NOT NULL default '0000-00-00',
      TotalScore int(11) NOT NULL default '0',
      TotalWeighting int(11) default '0',
      attempt int(11) NOT NULL default '0',
      PRIMARY KEY  (eurid))
      TYPE=MyISAM $charset_spec");

// QUESTIONS
mysql_query("CREATE TABLE questions (
        id int(11) NOT NULL auto_increment,
        question text,
        description text,
        ponderation float(11,2) default NULL,
        q_position int(11) default 1,
        type int(11) default 2,
        PRIMARY KEY  (id))
        TYPE=MyISAM $charset_spec");

// REPONSES
mysql_query("CREATE TABLE reponses (
        id int(11) NOT NULL default '0',
        question_id int(11) NOT NULL default '0',
        reponse text,
        correct int(11) default NULL,
        comment text,
	ponderation float(5,2),
	r_position int(11) default NULL,
        PRIMARY KEY  (id, question_id))
        TYPE=MyISAM $charset_spec");

// EXERCISE_QUESTION
mysql_query("CREATE TABLE exercice_question (
                question_id int(11) NOT NULL default '0',
                exercice_id int(11) NOT NULL default '0',
                PRIMARY KEY  (question_id,exercice_id))
        TYPE=MyISAM $charset_spec");


#######################COURSE_DESCRIPTION ################################

mysql_query("CREATE TABLE `course_description`
(
    `id` TINYINT UNSIGNED DEFAULT '0' NOT NULL,
    `title` VARCHAR(255),
    `content` TEXT,
    `upDate` DATETIME NOT NULL,
    UNIQUE (`id`)
)
TYPE=MyISAM $charset_spec");


#######################ACCUEIL ###########################################

    //arxikopoihsh tou array gia ta checkboxes
    for ($i=0; $i<=50; $i++)
    {
        $sbsystems[$i] = 0;
    }

    //allagh timwn sto array analoga me to poio checkbox exei epilegei
    foreach ($subsystems as $sb )
    {
        $sbsystems[$sb] = 1;
    }

mysql_query("CREATE TABLE accueil (
               id int(11) NOT NULL auto_increment,
               rubrique varchar(100), lien varchar(255),
               image varchar(100),
               visible tinyint(4),
               admin varchar(200),
               address varchar(120),
               define_var varchar(50),
               PRIMARY KEY (id))
        TYPE=MyISAM $charset_spec");

// Content accueil (homepage) Table
    mysql_query("INSERT INTO accueil VALUES (
                '1',
                '$langAgenda',
                '../../modules/agenda/agenda.php',
                'calendar',
                '".$sbsystems[1]."',
                '0',
                '',
                'MODULE_ID_AGENDA')");

    mysql_query("INSERT INTO accueil VALUES (
               '2',
               '$langLinks',
               '../../modules/link/link.php',
               'links',
               '".$sbsystems[2]."',
               '0',
               '',
               'MODULE_ID_LINKS'
               )");

    mysql_query("INSERT INTO accueil VALUES (
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
    mysql_query("INSERT INTO accueil VALUES (
               '4',
               '$langVideo',
               '../../modules/video/video.php',
               'videos',
               '".$sbsystems[4]."',
               '0',
               '',
               'MODULE_ID_VIDEO'
               )");

mysql_query("INSERT INTO accueil VALUES (
               '5',
               '$langWorks',
               '../../modules/work/work.php',
               'assignments',
               '".$sbsystems[5]."',
               '0',
               '',
               'MODULE_ID_ASSIGN'
               )");

    mysql_query("INSERT INTO accueil VALUES (
               '7',
               '$langAnnouncements',
               '../../modules/announcements/announcements.php',
               'announcements',
               '".$sbsystems[7]."',
               '0',
               '',
               'MODULE_ID_ANNOUNCE'
               )");

    mysql_query("INSERT INTO accueil VALUES (
               '9',
               '$langForums',
               '../../modules/phpbb/index.php',
               'forum',
               '".$sbsystems[9]."',
               '0',
               '',
               'MODULE_ID_FORUM'
               )");

    mysql_query("INSERT INTO accueil VALUES (
               '10',
               '$langExercices',
               '../../modules/exercice/exercice.php',
               'exercise',
               '".$sbsystems[10]."',
               '0',
               '',
               'MODULE_ID_EXERCISE'
               )");

	mysql_query("INSERT INTO accueil VALUES (
        '15',
        '$langGroups',
        '../../modules/group/group.php',
        'groups',
        '".$sbsystems[15]."',
        '0',
        '',
        'MODULE_ID_GROUPS'
        )");

    mysql_query("INSERT INTO accueil VALUES (
        '16',
        '$langDropBox',
        '../../modules/dropbox/index.php',
        'dropbox',
        '".$sbsystems[16]."',
        '0',
        '',
        'MODULE_ID_DROPBOX'
        )");

    mysql_query("INSERT INTO accueil VALUES (
                '19',
                '$langConference',
                '../../modules/conference/conference.php',
                'conference',
                '".$sbsystems[19]."',
                '0',
                '',
                'MODULE_ID_CHAT'
                )");

    mysql_query("INSERT INTO accueil VALUES (
               '20',
               '$langCourseDescription',
               '../../modules/course_description/',
               'description',
               '".$sbsystems[20]."',
               '0',
               '',
               'MODULE_ID_DESCRIPTION'
               )");

mysql_query("INSERT INTO accueil VALUES (
                '21',
                '$langQuestionnaire',
                '../../modules/questionnaire/questionnaire.php',
                'questionnaire',
                '".$sbsystems[21]."',
                '0',
                '',
                'MODULE_ID_QUESTIONNAIRE'
                )");

    mysql_query("INSERT INTO accueil VALUES (
               '23',
               '$langLearnPath',
               '../../modules/learnPath/learningPathList.php',
               'lp',
               '".$sbsystems[23]."',
               '0',
               '',
               'MODULE_ID_LP'
               )");

    mysql_query("INSERT INTO accueil VALUES (
               25,
               '$langToolManagement',
               '../../modules/course_tools/course_tools.php',
               'tooladmin',
               '0',
               '1',
               '',
               'MODULE_ID_TOOLADMIN'
               )");

    mysql_query("INSERT INTO accueil VALUES (
               '26',
               '$langWiki',
               '../../modules/wiki/wiki.php',
               'wiki',
               '".$sbsystems[26]."',
               '0',
               '',
               'MODULE_ID_WIKI'
               )");

        mysql_query("INSERT INTO accueil VALUES (
        '8',
        '$langAdminUsers',
        '../../modules/user/user.php',
        'users',
        '0',
        '1',
        '',
        'MODULE_ID_USERS'
        )");

mysql_query("INSERT INTO accueil VALUES (
               '14',
               '$langModifyInfo',
               '../../modules/course_info/infocours.php?',
               'course_info',
               '".$sbsystems[14]."',
               '1',
               '',
               'MODULE_ID_COURSEINFO'
               )");

mysql_query("INSERT INTO accueil VALUES (
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
mysql_query("INSERT INTO accueil VALUES (
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
mysql_query("CREATE TABLE agenda (
    id int(11) NOT NULL auto_increment,
    titre varchar(200),
    contenu text,
    day date NOT NULL default '0000-00-00',
    hour time NOT NULL default '00:00:00',
    lasting varchar(20),
    visibility CHAR(1) NOT NULL DEFAULT 'v',
    PRIMARY KEY (id))
    TYPE=MyISAM $charset_spec");

############################# PAGES ###########################################
    mysql_query("CREATE TABLE pages (
               id int(11) NOT NULL auto_increment,
               url varchar(200),
               titre varchar(200),
               description text,
               PRIMARY KEY (id))
        TYPE=MyISAM $charset_spec");

############################# DOCUMENTS ###########################################
mysql_query ("CREATE TABLE document (id int(4) NOT NULL auto_increment,
    path varchar(255) NOT NULL,
    filename text,
    visibility char(1) DEFAULT 'v' NOT NULL,
    comment varchar(255),
    category text,
    title text,
    creator text,
    date datetime default NULL,
    date_modified datetime default NULL,
    subject text,
    description text,
    author text,
    format text,
    language text,
    copyrighted text,
    PRIMARY KEY (id))
    TYPE=MyISAM $charset_spec");

############################# VIDEO ###########################################
    mysql_query("CREATE TABLE video (
               id int(11) NOT NULL auto_increment,
	       path varchar(255),
               url varchar(200),
               titre varchar(200),
               description text,
               creator varchar(200),
               publisher varchar(200),
               date DATETIME,
               PRIMARY KEY (id))
        TYPE=MyISAM $charset_spec");

################################# VIDEO LINKS ################################

        mysql_query("CREATE TABLE videolinks (
               id int(11) NOT NULL auto_increment,
               url varchar(200),
               titre varchar(200),
               description text,
         creator varchar(200),
         publisher varchar(200),
         date DATETIME,
               PRIMARY KEY (id))
        TYPE=MyISAM $charset_spec");


############################# WORKS ###########################################

db_query("CREATE TABLE `assignments` (
    `id` int(11) NOT NULL auto_increment,
    `title` varchar(200) NOT NULL default '',
    `description` text NOT NULL,
    `comments` text NOT NULL,
    `deadline` date NOT NULL default '0000-00-00',
    `submission_date` date NOT NULL default '0000-00-00',
    `active` char(1) NOT NULL default '1',
    `secret_directory` varchar(30) NOT NULL,
    `group_submissions` CHAR(1) DEFAULT '0' NOT NULL,
    UNIQUE KEY `id` (`id`))
    TYPE=MyISAM $charset_spec");

db_query("CREATE TABLE `assignment_submit` (
    `id` int(11) NOT NULL auto_increment,
    `uid` int(11) NOT NULL default '0',
    `assignment_id` int(11) NOT NULL default '0',
    `submission_date` date NOT NULL default '0000-00-00',
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
    TYPE=MyISAM $charset_spec");


############################## LINKS #############################################

    mysql_query("CREATE TABLE liens (
               id int(11) NOT NULL auto_increment,
               url varchar(255),
               titre varchar(255),
               description text,
           category int(4) default '0' NOT NULL,
           ordre mediumint(8) default '0' NOT NULL,
               PRIMARY KEY (id))
        TYPE=MyISAM $charset_spec");

    mysql_query("INSERT INTO liens VALUES (
               '1',
               'http://www.google.com',
               'Google',
               '$langGoogle','0','0'
               )");

    mysql_query("CREATE TABLE `link_categories` (
      `id` int(6) NOT NULL auto_increment,
      `categoryname` varchar(255) default NULL,
      `description` text,
      `ordre` mediumint(8) NOT NULL default '0',
      PRIMARY KEY (`id`))
    TYPE=MyISAM $charset_spec");

###################################### DROPBOX #####################################

    mysql_query("CREATE TABLE dropbox_file (
      id int(11) unsigned NOT NULL auto_increment,
      uploaderId int(11) unsigned NOT NULL default '0',
      filename varchar(250) NOT NULL default '',
      filesize int(11) unsigned NOT NULL default '0',
      title varchar(250) default '',
      description varchar(250) default '',
      author varchar(250) default '',
      uploadDate datetime NOT NULL default '0000-00-00 00:00:00',
      lastUploadDate datetime NOT NULL default '0000-00-00 00:00:00',
      PRIMARY KEY  (id),
      UNIQUE KEY UN_filename (filename))
    TYPE=MyISAM $charset_spec");

    mysql_query("CREATE TABLE dropbox_person (
      fileId int(11) unsigned NOT NULL default '0',
      personId int(11) unsigned NOT NULL default '0',
      PRIMARY KEY  (fileId,personId))
    TYPE=MyISAM $charset_spec");

    mysql_query("CREATE TABLE dropbox_post (
      fileId int(11) unsigned NOT NULL default '0',
      recipientId int(11) unsigned NOT NULL default '0',
      PRIMARY KEY  (fileId,recipientId))
    TYPE=MyISAM $charset_spec");

mysql_query("CREATE TABLE student_group (
    id int(11) NOT NULL auto_increment,
    name varchar(100) default NULL,
    description text,
    tutor int(11) default NULL,
    forumId int(11) default NULL,
    maxStudent int(11) NOT NULL default '0',
    secretDirectory varchar(30) NOT NULL default '0',
    PRIMARY KEY  (id))
TYPE=MyISAM $charset_spec");

mysql_query("CREATE TABLE user_group (
    id int(11) NOT NULL auto_increment,
    user int(11) NOT NULL default '0',
    team int(11) NOT NULL default '0',
    status int(11) NOT NULL default '0',
    role varchar(50) NOT NULL default '',
    PRIMARY KEY  (id))
    TYPE=MyISAM $charset_spec");

mysql_query("CREATE TABLE `group_documents` (
	`id` INT(4) NOT NULL AUTO_INCREMENT,
	`path` VARCHAR(255) default NULL ,
	`filename` VARCHAR(255) default NULL,
 	PRIMARY KEY(id)) 
	TYPE=MyISAM $charset_spec");
 
mysql_query("CREATE TABLE group_properties (
    id tinyint(4) NOT NULL auto_increment,
    self_registration tinyint(4) default '1',
    private tinyint(4) default '0',
    forum tinyint(4) default '1',
    document tinyint(4) default '1',
    wiki tinyint(4) default '0',
    agenda tinyint(4) default '0',
    PRIMARY KEY  (id))
    TYPE=MyISAM $charset_spec");

mysql_query("INSERT INTO group_properties
    (id, self_registration, private, forum, document, wiki, agenda)
    VALUES (NULL, '1', '0', '1', '1', '0', '0')");

#################### QUESTIONNAIRE ###############################################

    mysql_query("CREATE TABLE survey (
      sid bigint(14) NOT NULL auto_increment,
      creator_id mediumint(8) unsigned NOT NULL default '0',
      course_id varchar(20) NOT NULL default '0',
      name varchar(255) NOT NULL default '',
      creation_date datetime NOT NULL default '0000-00-00 00:00:00',
      start_date datetime NOT NULL default '0000-00-00 00:00:00',
      end_date datetime NOT NULL default '0000-00-00 00:00:00',
      type int(11) NOT NULL default '0',
      active int(11) NOT NULL default '0',
      PRIMARY KEY  (sid))
    TYPE=MyISAM $charset_spec");

    mysql_query("CREATE TABLE survey_answer (
      aid bigint(12) NOT NULL default '0',
      creator_id mediumint(8) unsigned NOT NULL default '0',
      sid bigint(12) NOT NULL default '0',
      date datetime NOT NULL default '0000-00-00 00:00:00',
      PRIMARY KEY  (aid))
 TYPE=MyISAM $charset_spec");

    mysql_query("CREATE TABLE survey_answer_record (
      arid int(11) NOT NULL auto_increment,
      aid bigint(12) NOT NULL default '0',
      question_text varchar(250) NOT NULL default '',
      question_answer varchar(250) NOT NULL default '',
      PRIMARY KEY  (arid))
 TYPE=MyISAM $charset_spec");

    mysql_query("CREATE TABLE survey_question (
      sqid bigint(12) NOT NULL default '0',
      sid bigint(12) NOT NULL default '0',
      question_text varchar(250) NOT NULL default '',
      PRIMARY KEY  (sqid))
    TYPE=MyISAM $charset_spec");

    mysql_query("CREATE TABLE survey_question_answer (
      sqaid int(11) NOT NULL auto_increment,
      sqid bigint(12) NOT NULL default '0',
      answer_text varchar(250) default NULL,
      PRIMARY KEY  (sqaid))
  TYPE=MyISAM $charset_spec");

mysql_query("CREATE TABLE poll (
      pid int(11) NOT NULL auto_increment,
      creator_id mediumint(8) unsigned NOT NULL default '0',
      course_id varchar(20) NOT NULL default '0',
      name varchar(255) NOT NULL default '',
      creation_date date NOT NULL default '0000-00-00',
      start_date date NOT NULL default '0000-00-00',
      end_date date NOT NULL default '0000-00-00',
      active int(11) NOT NULL default '0',
      PRIMARY KEY  (pid))
    TYPE=MyISAM $charset_spec");

    mysql_query("CREATE TABLE poll_answer_record (
      arid int(11) NOT NULL auto_increment,
	pid int(11) NOT NULL default '0',
	qid int(11) NOT NULL default '0',
      	aid int(11) NOT NULL default '0',
	answer_text varchar(255) NOT NULL default '',
	user_id int(11) NOT NULL default '0',
      submit_date date NOT NULL default '0000-00-00',
      PRIMARY KEY  (arid))
    TYPE=MyISAM $charset_spec");

    mysql_query("CREATE TABLE poll_question (
      pqid int(11) NOT NULL auto_increment,
      pid int(11) NOT NULL default '0',
      question_text varchar(250) NOT NULL default '',
      qtype ENUM('multiple', 'fill') NOT NULL,
      PRIMARY KEY  (pqid))
    TYPE=MyISAM $charset_spec");

    mysql_query("CREATE TABLE poll_question_answer (
      pqaid int(11) NOT NULL auto_increment,
      pqid int(11) NOT NULL default '0',
      answer_text text NOT NULL,
      PRIMARY KEY  (pqaid))
    TYPE=MyISAM $charset_spec");


############################# LEARNING PATH ######################################

mysql_query("CREATE TABLE `lp_module` (
              `module_id` int(11) NOT NULL auto_increment,
              `name` varchar(255) NOT NULL default '',
              `comment` text NOT NULL,
              `accessibility` enum('PRIVATE','PUBLIC') NOT NULL default 'PRIVATE',
              `startAsset_id` int(11) NOT NULL default '0',
              `contentType` enum('CLARODOC','DOCUMENT','EXERCISE','HANDMADE','SCORM','SCORM_ASSET','LABEL','COURSE_DESCRIPTION','LINK') NOT NULL,
              `launch_data` text NOT NULL,
              PRIMARY KEY  (`module_id`)
             ) TYPE=MyISAM $charset_spec");
             //COMMENT='List of available modules used in learning paths';

mysql_query("CREATE TABLE `lp_learnPath` (
              `learnPath_id` int(11) NOT NULL auto_increment,
              `name` varchar(255) NOT NULL default '',
              `comment` text NOT NULL,
              `lock` enum('OPEN','CLOSE') NOT NULL default 'OPEN',
              `visibility` enum('HIDE','SHOW') NOT NULL default 'SHOW',
              `rank` int(11) NOT NULL default '0',
              PRIMARY KEY  (`learnPath_id`),
              UNIQUE KEY rank (`rank`)
            ) TYPE=MyISAM $charset_spec");
            //COMMENT='List of learning Paths';

mysql_query("CREATE TABLE `lp_rel_learnPath_module` (
                `learnPath_module_id` int(11) NOT NULL auto_increment,
                `learnPath_id` int(11) NOT NULL default '0',
                `module_id` int(11) NOT NULL default '0',
                `lock` enum('OPEN','CLOSE') NOT NULL default 'OPEN',
                `visibility` enum('HIDE','SHOW') NOT NULL default 'SHOW',
                `specificComment` text NOT NULL,
                `rank` int(11) NOT NULL default '0',
                `parent` int(11) NOT NULL default '0',
                `raw_to_pass` tinyint(4) NOT NULL default '50',
                PRIMARY KEY  (`learnPath_module_id`)
              ) TYPE=MyISAM $charset_spec");
              //COMMENT='This table links module to the learning path using them';


mysql_query("CREATE TABLE `lp_asset` (
              `asset_id` int(11) NOT NULL auto_increment,
              `module_id` int(11) NOT NULL default '0',
              `path` varchar(255) NOT NULL default '',
              `comment` varchar(255) default NULL,
              PRIMARY KEY  (`asset_id`)
            ) TYPE=MyISAM $charset_spec");
            //COMMENT='List of resources of module of learning paths';

mysql_query("CREATE TABLE `lp_user_module_progress` (
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
            ) TYPE=MyISAM $charset_spec");
            //COMMENT='Record the last known status of the user in the course';

############################# WIKI ######################################

mysql_query("CREATE TABLE `wiki_properties` (
              `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              `title` VARCHAR(255) NOT NULL DEFAULT '',
              `description` TEXT NULL,
              `group_id` INT(11) NOT NULL DEFAULT 0,
              PRIMARY KEY(`id`)
            ) TYPE=MyISAM $charset_spec");

mysql_query("CREATE TABLE `wiki_acls` (
              `wiki_id` INT(11) UNSIGNED NOT NULL,
              `flag` VARCHAR(255) NOT NULL,
              `value` ENUM('false','true') NOT NULL DEFAULT 'false'
            ) TYPE=MyISAM $charset_spec");

mysql_query("CREATE TABLE `wiki_pages` (
              `id` int(11) unsigned NOT NULL auto_increment,
              `wiki_id` int(11) unsigned NOT NULL default '0',
              `owner_id` int(11) unsigned NOT NULL default '0',
              `title` varchar(255) NOT NULL default '',
              `ctime` datetime NOT NULL default '0000-00-00 00:00:00',
              `last_version` int(11) unsigned NOT NULL default '0',
              `last_mtime` datetime NOT NULL default '0000-00-00 00:00:00',
              PRIMARY KEY  (`id`)
            ) TYPE=MyISAM $charset_spec");

mysql_query("CREATE TABLE `wiki_pages_content` (
              `id` int(11) unsigned NOT NULL auto_increment,
              `pid` int(11) unsigned NOT NULL default '0',
              `editor_id` int(11) NOT NULL default '0',
              `mtime` datetime NOT NULL default '0000-00-00 00:00:00',
              `content` text NOT NULL,
              PRIMARY KEY  (`id`)
            ) TYPE=MyISAM $charset_spec");

//dhmiourgia full text indexes gia th diadikasia ths anazhthshs
mysql_query("ALTER TABLE `agenda` ADD FULLTEXT `agenda` (`titre` ,`contenu`)");
mysql_query("ALTER TABLE `course_description` ADD FULLTEXT `course_description` (`title` ,`content`)");
mysql_query("ALTER TABLE `document` ADD FULLTEXT `document` (`filename` ,`comment` ,`title`,`creator`,`subject`,`description`,`author`,`language`)");
mysql_query("ALTER TABLE `exercices` ADD FULLTEXT `exercices` (`titre`,`description`)");
mysql_query("ALTER TABLE `posts_text` ADD FULLTEXT `posts_text` (`post_text`)");
mysql_query("ALTER TABLE `forums` ADD FULLTEXT `forums` (`forum_name`,`forum_desc`)");
mysql_query("ALTER TABLE `liens` ADD FULLTEXT `liens` (`url` ,`titre` ,`description`)");
mysql_query("ALTER TABLE `video` ADD FULLTEXT `video` (`url` ,`titre` ,`description`)");
mysql_query("ALTER TABLE `videolinks` ADD FULLTEXT `videolinks` (`url` ,`titre` ,`description`)");

// creation of indexes 
mysql_query("ALTER TABLE `lp_user_module_progress` ADD INDEX `optimize` (`user_id` , `learnPath_module_id`)");
mysql_query("ALTER TABLE `actions` ADD INDEX `actionsindex` (`module_id` , `date_time`)"); 

?>
