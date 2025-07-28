<?php
/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */


/**
@brief Enter only platform constants here.
*/

/* course status */
define('COURSE_CLOSED', 0);
define('COURSE_REGISTRATION', 1);
define('COURSE_OPEN', 2);
define('COURSE_INACTIVE', 3);

/* hierarchy node status */
define('NODE_OPEN', 2);
define('NODE_SUBSCRIBED', 1);
define('NODE_CLOSED', 0);

/* user status */
define('USER_TEACHER', 1);
define('USER_STUDENT', 5);
define('USER_GUEST', 10);
define('USER_DEPARTMENTMANAGER', 11);

// resized user image
define('IMAGESIZE_LARGE', 256);
define('IMAGESIZE_MEDIUM', 155);
define('IMAGESIZE_SMALL', 32);

// profile info access
define('ACCESS_PROFS', 0);
define('ACCESS_USERS', 1);

/* module type */
define('MODULE_IN_HOME', 0);
define('MODULE_IN_CLASS',1);
define('MODULE_AFTER_CLASS',2);

// user admin rights
define('ADMIN_USER', 0); // admin user can do everything
define('POWER_USER', 1); // poweruser can admin only users and courses
define('USERMANAGE_USER', 2); // usermanage user can admin only users
define('DEPARTMENTMANAGE_USER', 3); // departmentmanage user can admin departments
// user email status
define('EMAIL_VERIFICATION_REQUIRED', 0);  /* email verification required. User cannot login */
define('EMAIL_VERIFIED', 1); // email is verified. User can login.
define('EMAIL_UNVERIFIED', 2); // email is unverified. User can login but cannot receive mail.
// user can receive mail notifications from platform or courses
define('EMAIL_NOTIFICATIONS_DISABLED', 0);
define('EMAIL_NOTIFICATIONS_ENABLED', 1);
// course modules
define('MODULE_ID_AGENDA', 1);
define('MODULE_ID_LINKS', 2);
define('MODULE_ID_DOCS', 3);
define('MODULE_ID_VIDEO', 4);
define('MODULE_ID_ASSIGN', 5);
define('MODULE_ID_ANNOUNCE', 7);
define('MODULE_ID_USERS', 8);
define('MODULE_ID_FORUM', 9);
define('MODULE_ID_EXERCISE', 10);
define('MODULE_ID_COURSEINFO', 14);
define('MODULE_ID_GROUPS', 15);
define('MODULE_ID_MESSAGE', 16);
define('MODULE_ID_GLOSSARY', 17);
define('MODULE_ID_EBOOK', 18);
define('MODULE_ID_CHAT', 19);
define('MODULE_ID_DESCRIPTION', 20); /* deprecated. used only for compatibility in statistics*/
define('MODULE_ID_QUESTIONNAIRE', 21);
define('MODULE_ID_LP', 23);
define('MODULE_ID_USAGE', 24);
define('MODULE_ID_TOOLADMIN', 25);
define('MODULE_ID_WIKI', 26);
define('MODULE_ID_UNITS', 27);
define('MODULE_ID_SEARCH', 28);
define('MODULE_ID_CONTACT', 29);
define('MODULE_ID_ATTENDANCE', 30);
define('MODULE_ID_GRADEBOOK', 32);
define('MODULE_ID_TC', 34);
define('MODULE_ID_BLOG', 37);
define('MODULE_ID_COMMENTS', 38);
define('MODULE_ID_RATING', 39);
define('MODULE_ID_SHARING', 40);
define('MODULE_ID_ABUSE_REPORT', 42);
define('MODULE_ID_WALL', 46);
define('MODULE_ID_MINDMAP', 47); /* deprecated, user only for compatibility in statistics*/
define('MODULE_ID_PROGRESS', 48);
define('MODULE_ID_COURSEPREREQUISITE', 49);
define('MODULE_ID_LTI_CONSUMER', 50);  /* deprecated. used only for compatibility in statistics*/
define('MODULE_ID_ANALYTICS', 51);
define('MODULE_ID_H5P', 52);
define('MODULE_ID_COURSE_WIDGETS', 44);
define('MODULE_ID_REQUEST', 100);
define('MODULE_ID_SESSION', 101);
//user activities
define('MODULE_ID_EBOOK_READ','FC1');
define('MODULE_ID_VIDEO_WATCH','FC2');
define('MODULE_ID_VIDEO_INTERACTION','FC3');
define('MODULE_ID_REVISION','FC5');
define('MODULE_ID_GAMES','FC6');
define('MODULE_ID_DISCUSS','FC7');
define('MODULE_ID_PROJECT','FC8');
define('MODULE_ID_BRAINSTORMING','FC9');
define('MODULE_ID_WORK_PAPER','FC10');
define('MODULE_ID_ROLE_PLAY','FC11');
define('MODULE_ID_SIMULATE','FC12');
define('MODULE_ID_PROBLEM_SOLVING','FC13');
define('MODULE_ID_MINDMAP_FC','FC14');
define('MODULE_ID_EVALUATE','FC15');
define('MODULE_ID_DISCUSS_AC','FC16');
define('MODULE_ID_DIGITAL_STORYTELLING','FC17');
define('MODULE_ID_SUPPORTING_MATERIAL','FC18');

// user modules

// not used only for backward compatibility in logs
define('MODULE_ID_SETTINGS', 31); // use MODULE_ID_COURSEINFO instead !
define('MODULE_ID_NOTES', 35);
define('MODULE_ID_PERSONALCALENDAR',36);
define('MODULE_ID_ADMINCALENDAR', 43);

// Available course settings
define('SETTING_BLOG_COMMENT_ENABLE', 1);
define('SETTING_BLOG_STUDENT_POST', 2);
define('SETTING_BLOG_RATING_ENABLE', 3);
define('SETTING_BLOG_SHARING_ENABLE', 4);
define('SETTING_COURSE_SHARING_ENABLE', 5);
define('SETTING_COURSE_RATING_ENABLE', 6);
define('SETTING_COURSE_COMMENT_ENABLE', 7);
define('SETTING_COURSE_ANONYMOUS_RATING_ENABLE', 8);
define('SETTING_FORUM_RATING_ENABLE', 9);
define('SETTING_COURSE_SOCIAL_BOOKMARKS_ENABLE', 10);
define('SETTING_COURSE_ABUSE_REPORT_ENABLE', 11);
define('SETTING_GROUP_MULTIPLE_REGISTRATION', 12);
define('SETTING_GROUP_STUDENT_DESCRIPTION', 13);
define('SETTING_COURSE_USER_REQUESTS_DISABLE', 20); /* enable user request access if course is closed */
define('SETTING_COURSE_FORUM_NOTIFICATIONS', 21);
define('SETTING_DOCUMENTS_PUBLIC_WRITE', 22);
define('SETTING_OFFLINE_COURSE', 23); /* enable downloading for offline use */
define('SETTING_USERS_LIST_ACCESS', 24);
define('SETTING_AGENDA_ANNOUNCEMENT_COURSE_COMPLETION', 25);
define('SETTING_FACULTY_USERS_REGISTRATION', 26); /* course registration is allowed only for faculty users */
define('SETTING_COUSE_IMAGE_STYLE', 27); /* course image description object-fit css */
define('SETTING_COUSE_IMAGE_PRINT_HEADER', 28); /* course image print header */
define('SETTING_COUSE_IMAGE_PRINT_FOOTER', 29); /* course image print footer */

// Available user settings
define('SETTING_FORUM_POST_VIEW', 1);

// exercise answer types
define('UNIQUE_ANSWER', 1);
define('MULTIPLE_ANSWER', 2);
define('FILL_IN_BLANKS', 3);
define('MATCHING', 4);
define('TRUE_FALSE', 5);
define('FREE_TEXT', 6);
define('FILL_IN_BLANKS_TOLERANT', 7);
define('FILL_IN_FROM_PREDEFINED_ANSWERS', 8);
define('DRAG_AND_DROP_TEXT', 9);
define('DRAG_AND_DROP_MARKERS', 10);
define('CALCULATED', 11);
define('ORDERING', 12);

// exercise view type
define('SINGLE_PAGE_TYPE', 1);
define('MULTIPLE_PAGE_TYPE', 2);
define('ONE_WAY_TYPE', 3);

// exercise attempt types
define('ATTEMPT_ACTIVE', 0);
define('ATTEMPT_COMPLETED', 1);
define('ATTEMPT_PENDING', 2);
define('ATTEMPT_PAUSED', 3);
define('ATTEMPT_CANCELED', 4);

// Widget Areas
define('HOME_PAGE_MAIN', 1);
define('HOME_PAGE_SIDEBAR', 2);
define('PORTFOLIO_PAGE_MAIN', 3);
define('PORTFOLIO_PAGE_SIDEBAR', 4);
define('COURSE_HOME_PAGE_MAIN', 5);
define('COURSE_HOME_PAGE_SIDEBAR', 6);

// for fill in blanks exercise questions
define('TEXTFIELD_FILL', 1);
define('LISTBOX_FILL', 2); //

// assignment type
define('ASSIGNMENT_TYPE_ECLASS', 0);
define('ASSIGNMENT_TYPE_TURNITIN', 1);

// assignment grading types
define('ASSIGNMENT_STANDARD_GRADE', 0);
define('ASSIGNMENT_SCALING_GRADE', 1);
define('ASSIGNMENT_RUBRIC_GRADE', 2);
define('ASSIGNMENT_PEER_REVIEW_GRADE', 3);

// questionnaire ( aka poll ) types
define('POLL_NORMAL', 0);
define('POLL_COLLES', 1);
define('POLL_ATTLS', 2);
define('POLL_QUICK', 3);
define('POLL_LIMESURVEY', 99);

//poll position
define('QPOLL_HOME', 1);

// gradebook activity type
define('GRADEBOOK_ACTIVITY_ASSIGNMENT', 1);
define('GRADEBOOK_ACTIVITY_EXERCISE', 2);
define('GRADEBOOK_ACTIVITY_LP', 3);
define('GRADEBOOK_ACTIVITY_TC', 4);

// Subsystem types (used in documents)
define('MAIN', 0);
define('GROUP', 1);
define('EBOOK', 2);
define('COMMON', 3);
define('MYDOCS', 4);
define('MYSESSIONS',5);
define('SESSION_REFERENCE',6);
define('ORAL_QUESTION',7);

// path for certificates / badges templates
define('CERT_TEMPLATE_PATH', "/courses/user_progress_data/cert_templates/");
define('BADGE_TEMPLATE_PATH', "/courses/user_progress_data/badge_templates/");

// interval in minutes for counting online users
define('MAX_IDLE_TIME', 10);

define('JQUERY_VERSION', '-3.6.0');
