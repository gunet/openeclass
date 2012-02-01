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
               '../../modules/exercice/exercise.php',
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


############################# PAGES ###########################################
    db_query("CREATE TABLE pages (
               id int(11) NOT NULL auto_increment,
               url varchar(200),
               titre varchar(200),
               description text,
               PRIMARY KEY (id))
         $charset_spec");


// dhmiourgia full text indexes gia th diadikasia ths anazhthshs
db_query("ALTER TABLE `course_description` ADD FULLTEXT `course_description` (`title` ,`content`)");

// creation of indexes 
db_query("ALTER TABLE `actions` ADD INDEX `actionsindex` (`module_id` , `date_time`)"); 
