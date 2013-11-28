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

/*
 * Perso Component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract This component is the central controller of eclass personalised.
 * It controls personalisation and initialises several variables used by it.
 *
 * It is based on the diploma thesis of Evelthon Prodromou
 *
 */

if (!defined('INDEX_START')) {
    die("Action not allowed!");
}

require_once 'include/redirector.php';
$sql = "SELECT course.id cid, course.code code, course.public_code,
                        course.title title, course.prof_names profs, course_user.status status
                FROM course JOIN course_user ON course.id = course_user.course_id
                WHERE course_user.user_id = $uid " .
        ($_SESSION['status'] == USER_TEACHER ? ("AND course.visible != " . COURSE_INACTIVE) : '') . "
                ORDER BY status, course.title, course.prof_names";

$result2 = db_query($sql);

$courses = array();
if ($result2 and mysql_num_rows($result2) > 0) {
    while ($mycours = mysql_fetch_array($result2)) {
        $courses[$mycours['code']] = $mycours['status'];
    }
}
$_SESSION['courses'] = $courses;
$subsystem = MAIN;

require_once 'include/lib/textLib.inc.php';
require_once 'include/lib/fileDisplayLib.inc.php';

//include personalised component files (announcemets.php etc.) from /modules/perso
require_once 'modules/perso/lessons.php';
require_once 'modules/perso/assignments.php';
require_once 'modules/perso/announcements.php';
require_once 'modules/perso/documents.php';
require_once 'modules/perso/agenda.php';
require_once 'modules/perso/forumPosts.php';

$_user['persoLastLogin'] = last_login($uid);
$_user['lastLogin'] = str_replace('-', ' ', $_user['persoLastLogin']);

//	BEGIN Get user's lesson info]=====================================================
$user_lesson_info = getUserLessonInfo($uid, "html");
//	END Get user's lesson info]=====================================================
//if user is registered to at least one lesson
if ($user_lesson_info[0][0] > 0) {
    // BEGIN - Get user assignments
    $param = array(
        'uid' => $uid,
        'max_repeat_val' => $user_lesson_info[0][0], //max repeat val (num of lessons)
        'lesson_titles' => $user_lesson_info[0][1],
        'lesson_code' => $user_lesson_info[0][2],
        'lesson_professor' => $user_lesson_info[0][3],
        'lesson_status' => $user_lesson_info[0][4],
        'lesson_id' => $user_lesson_info[0][8]
    );
    $user_assignments = getUserAssignments($param, "html");
    //END - Get user assignments
    // BEGIN - Get user announcements
    $param = array(
        'uid' => $uid,
        'max_repeat_val' => $user_lesson_info[0][0], //max repeat val (num of lessons)
        'lesson_titles' => $user_lesson_info[0][1],
        'lesson_code' => $user_lesson_info[0][2],
        'lesson_professor' => $user_lesson_info[0][3],
        'lesson_status' => $user_lesson_info[0][4],
        'usr_lst_login' => $_user["lastLogin"],
        'usr_memory' => $user_lesson_info[0][5],
        'lesson_id' => $user_lesson_info[0][8]
    );

    $user_announcements = getUserAnnouncements($param, 'html');
    // END - Get user announcements
    // BEGIN - Get user documents

    $param = array(
        'uid' => $uid,
        'max_repeat_val' => $user_lesson_info[0][0], //max repeat val (num of lessons)
        'lesson_titles' => $user_lesson_info[0][1],
        'lesson_code' => $user_lesson_info[0][2],
        'lesson_professor' => $user_lesson_info[0][3],
        'lesson_status' => $user_lesson_info[0][4],
        'usr_lst_login' => $_user["lastLogin"],
        'usr_memory' => $user_lesson_info[0][6]
    );

    $user_documents = getUserDocuments($param, "html");

    // END - Get user documents
    //BEGIN - Get user agenda
    $param = array(
        'uid' => $uid,
        'max_repeat_val' => $user_lesson_info[0][0], //max repeat val (num of lessons)
        'lesson_titles' => $user_lesson_info[0][1],
        'lesson_code' => $user_lesson_info[0][2],
        'lesson_professor' => $user_lesson_info[0][3],
        'lesson_status' => $user_lesson_info[0][4],
        'usr_lst_login' => $_user["lastLogin"],
        'lesson_id' => $user_lesson_info[0][8]
    );
    $user_agenda = getUserAgenda($param, "html");

    //END - Get user agenda
    //BEGIN - Get user forum posts
    $param = array(
        'uid' => $uid,
        'max_repeat_val' => $user_lesson_info[0][0], //max repeat val (num of lessons)
        'lesson_titles' => $user_lesson_info[0][1],
        'lesson_code' => $user_lesson_info[0][2],
        'lesson_professor' => $user_lesson_info[0][3],
        'lesson_status' => $user_lesson_info[0][4],
        'usr_lst_login' => $_user['lastLogin'],
        'usr_memory' => $user_lesson_info[0][7] //forum memory
    );
    $user_forumPosts = getUserForumPosts($param, "html");
    //END - Get user forum posts
} else {
    //show a "-" in all blocks if the user is not enrolled to any lessons
    // (except of the lessons block which is handled before)
    $user_assignments = "<p>-</p>";
    $user_announcements = "<p>-</p>";
    $user_documents = "<p>-</p>";
    $user_agenda = "<p>-</p>";
    $user_forumPosts = "<p>-</p>";
}

// ==  BEGIN create array with personalised content
$perso_tool_content = array(
    'lessons_content' => $user_lesson_info[1],
    'assigns_content' => $user_assignments,
    'announce_content' => $user_announcements,
    'docs_content' => $user_documents,
    'agenda_content' => $user_agenda,
    'forum_content' => $user_forumPosts
);

// == END create array with personalised content


/*
 * Function autoCloseTags
 *
 * It is used by the announcements and agenda personalised components. These
 * tools offer the ability to the professor to add content by using a WYSIWYG editor.
 * Thus, the professor can add several HTML tags to the content.
 *
 * The personalised logic limits this content to an X number of characters. This can
 * cause several tags to be in a not-closed state.
 *
 * This function makes sure ALL tags are closed so that no errors are presented to
 * the personalised interface
 *
 * @param string $string HTML code parsed by the personalised components
 * @return string HTML code with all html tag elements closed properly
 */
function autoCloseTags($string) {
    $donotclose = array('br', 'img', 'input'); // Tags that are not to be closed
    //prepare vars and arrays
    $tagstoclose = '';
    $tags = array();

    //put all opened tags into an array
    preg_match_all("/<(([A-Z]|[a-z]).*)(( )|(>))/isU", $string, $result);

    $openedtags = $result[1];

    // this is just done so that the order of the closed tags in the end will be better
    $openedtags = array_reverse($openedtags);

    // put all closed tags into an array
    preg_match_all("/<\/(([A-Z]|[a-z]).*)(( )|(>))/isU", $string, $result2);
    $closedtags = $result2[1];

    // look up which tags still have to be closed and put them in an array
    for ($i = 0; $i < count($openedtags); $i++) {
        if (in_array($openedtags[$i], $closedtags)) {
            unset($closedtags[array_search($openedtags[$i], $closedtags)]);
        } else
            array_push($tags, $openedtags[$i]);
    }
    // prepare the close-tags for output
    for ($x = 0; $x < count($tags); $x++) {
        $add = strtolower(trim($tags[$x]));

        if (!in_array($add, $donotclose))
            $tagstoclose.='</' . $add . '>';
    }
    return $tagstoclose;
}
