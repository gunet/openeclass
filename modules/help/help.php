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

session_start();

if (isset($_GET['language']) and $_GET['language'] == 'el') {
    $language = 'el';
} else {
    $language = 'en';
}

$siteName = '';
include "../../lang/$language/common.inc.php";
include '../../include/main_lib.php';

$course_help_modules = [
    'agenda',
    'links',
    'documents',
    'video',
    'assignments',
    'announcements',
    'forum',
    'exercises',
    'groups',
    'message',
    'glossary',
    'ebook',
    'chat',
    'questionnaire',
    'learningpath',
    'wiki',
    'blog',
    'wall',
    'gradebook',
    'attendance',
    'tc',
    'progress',
    'request',
    'h5p',
    'course_settings',
    'course_description',
    'course_users',
    'course_stats',
    'course_tools',
    'course_abuse_report',
    'prequesities',
    'learning_analytics',
    'portfolio',
    'registration'
];

$shortVer = preg_replace('/^(\d\.\d+).*$/', '\1', ECLASS_VERSION);

$topic = $subtopic = '';
if (isset($_GET['topic'])) {
    $topic = htmlspecialchars($_GET['topic'], ENT_QUOTES);
}
if (isset($_GET['subtopic'])) {
    $subtopic = '/' . htmlspecialchars($_GET['subtopic'], ENT_QUOTES);
}

if (isset($_SESSION['is_admin'])) {
    if (in_array($topic, $course_help_modules)) {
        $help_status = 'teacher';
    } else {
        $help_status = 'admin';
    }
} else if (isset($_SESSION['status']) and $_SESSION['status'] == USER_TEACHER) {
    $help_status = 'teacher';
} else {
    $help_status = 'student';
}

$link = "https://docs.openeclass.org/$language/$shortVer/$help_status/$topic$subtopic?do=export_xhtml";
header('Content-Type: text/html; charset=UTF-8');

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <body>
        <iframe frameborder="0" width="100%" height="500px" src="<?php echo $link ?>"></iframe>
    </body>
</html>
