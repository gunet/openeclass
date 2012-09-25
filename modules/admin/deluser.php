<?php
/* ========================================================================
 * Open eClass 2.6
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


$require_usermanage_user = TRUE;
include '../../include/baseTheme.php';
$nameTools = $langUnregUser;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);

// get the incoming values and initialize them
$u    = isset($_GET['u']) ? intval($_GET['u']) : false;
$doit = isset($_GET['doit']);

$u_account  = $u ? q(uid_to_username($u)) : '';
$u_realname = $u ? q(uid_to_name($u))     : '';
$t = 0;

if (!$doit) {

    if ($u_account) {

        $tool_content .= "<p class='title1'>$langConfirmDelete</p>
            <div class='alert1'>$langConfirmDeleteQuestion1 <em>$u_realname ($u_account)</em><br/>
            $langConfirmDeleteQuestion3
            </div>
            <p class='eclass_button'><a href='$_SERVER[SCRIPT_NAME]?u=$u&amp;doit=yes'>$langDelete</a></p>";

    } else {
        $tool_content .= "<p>$langErrorDelete</p>";
    }
    
    $tool_content .= "<div class='right'><a href='index.php'>$langBackAdmin</a></div><br/>";

} else {

    if ($u == 1) {
        $tool_content .= $langTryDeleteAdmin;
    } else {
        // validate if this is an existing user
        $q = db_query("SELECT * FROM user WHERE user_id = ". $u);

        if (mysql_num_rows($q)) {
            // delete everything
            $courses = array();
            $q_course = db_query("SELECT code FROM cours_user, cours
                            WHERE cours.cours_id = cours_user.cours_id AND
                            user_id = ". $u);
            while (list($code) = mysql_fetch_row($q_course)) {
                $courses[] = $code;
            }
            
            
            foreach ($courses as $code) {
                
                mysql_select_db($code);
                
                db_query("DELETE FROM actions WHERE user_id = ". $u);
                db_query("DELETE FROM assignment_submit WHERE uid = ". $u);
                db_query("DELETE FROM dropbox_file WHERE uploaderId = ". $u);
                db_query("DELETE FROM dropbox_person WHERE personId = ". $u);
                db_query("DELETE FROM dropbox_post WHERE recipientId = ". $u);
                db_query("DELETE FROM exercise_user_record WHERE uid = ". $u);
                db_query("DELETE FROM logins WHERE user_id = ". $u);
                db_query("DELETE FROM lp_user_module_progress WHERE user_id = ". $u);
                db_query("DELETE FROM poll WHERE creator_id = ". $u);
                db_query("DELETE FROM poll_answer_record WHERE user_id = ". $u);
                db_query("DELETE FROM posts WHERE poster_id = ". $u);
                db_query("DELETE FROM topics WHERE topic_poster = ". $u);
                db_query("DELETE FROM wiki_pages WHERE owner_id = ". $u);
                db_query("DELETE FROM wiki_pages_content WHERE editor_id = ". $u);
            }
            
            mysql_select_db($mysqlMainDb);
            
            db_query("DELETE FROM admin WHERE idUser = ". $u);
            db_query("DELETE FROM forum_notify WHERE user_id = ". $u);
            db_query("DELETE FROM group_members WHERE user_id = ". $u);
            db_query("DELETE FROM loginout WHERE id_user = ". $u);
            db_query("DELETE FROM cours_user WHERE user_id = ". $u);
            db_query("DELETE FROM user WHERE user_id = ". $u);
            
            
            $tool_content .= "<p>$langUserWithId $u $langWasDeleted.</p>\n";
            
        } else {
            $tool_content .= "<p>$langErrorDelete</p>";
        }

        $tool_content .= "<div class='right'><a href='index.php'>$langBackAdmin</a></div><br/>\n";
    }
}


draw($tool_content,3);
