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
 * Open eClass 2.x standard stuff
 */
$require_current_course = TRUE;
$require_login = TRUE;
$require_help = FALSE;
$require_editor = TRUE;
include "../../include/baseTheme.php";
include_once "config.php";
include "functions.php"; 

if (isset($_GET['forum'])) {
        $forum = intval($_GET['forum']);
}
if (isset($_GET['topic'])) {
        $topic = intval($_GET['topic']);
}
if (isset($_GET['post_id'])) {
        $post_id = intval($_GET['post_id']);
}
if (isset($_POST['submit'])) {
        $message = $_POST['message'];
        if (isset($_POST['subject'])) {
                $subject = $_POST['subject'];	
        }
        $sql = "SELECT * FROM posts WHERE post_id = $post_id AND course_id = $cours_id";
        if (!$result = db_query($sql)) {
                $tool_content .= $langErrorDataOne;
                draw($tool_content, 2, null, $head_content);
                exit();
        }
        if (mysql_num_rows($result) <= 0) {
                $tool_content .= $langErrorDataTwo;
                draw($tool_content, 2, null, $head_content);
                exit();
        }
        $myrow = mysql_fetch_array($result);
        $forum_id = $myrow["forum_id"];
        $topic_id = $myrow["topic_id"];
        $this_post_time = $myrow["post_time"];
        list($day, $time) = explode(' ', $myrow["post_time"]);
        $date = date("Y-m-d H:i");

        $row1 = mysql_fetch_row(db_query("SELECT forum_name FROM forums 
                                WHERE forum_id=$forum_id 
                                AND course_id = $cours_id"));
        $forum_name = $row1[0];
        $row2 = mysql_fetch_row(db_query("SELECT topic_title FROM topics 
                                WHERE topic_id=$topic_id
                                        AND course_id = $cours_id"));
        $topic_title = $row2[0];

        $nameTools = $langReply;
        $navigation[] = array ("url"=>"index.php?course=$code_cours", "name"=> $langForums);
        $navigation[] = array ("url"=>"viewforum.php?course=$code_cours&amp;forum=$forum_id", "name"=> $forum_name);
        $navigation[] = array ("url"=>"viewtopic.php?course=$code_cours&amp;topic=$topic_id&amp;forum=$forum_id", "name"=> $topic_title);

        $is_html_disabled = false;
        if ((isset($allow_html) && $allow_html == 0) || isset($html)) {
                $message = htmlspecialchars($message);
                $is_html_disabled = true;
        }
        if (isset($allow_bbcode) && $allow_bbcode == 1 && !isset($bbcode)) {
                $message = bbencode($message, $is_html_disabled);
        }		

        $forward = 1;
        $topic = $topic_id;
        $forum = $forum_id;
        $sql = "UPDATE posts SET post_text = " . autoquote(purify($message)) . "
                        WHERE post_id = $post_id 
                        AND course_id = $cours_id";
        if (!$result = db_query($sql)) {
                $tool_content .= $langUnableUpdatePost;
                draw($tool_content, 2, null, $head_content);
                exit();
        }
        if (isset($subject)) {
                $subject = strip_tags($subject);
        }
        if (isset($subject) && (trim($subject) != '')) {			
                $sql = "UPDATE topics
                        SET topic_title = " . autoquote($subject) . " 
                        WHERE topic_id = $topic_id
                                AND course_id = $cours_id";
                if (!$result = db_query($sql)) {
                        $tool_content .= $langUnableUpdateTopic;
                        draw($tool_content, 2, null, $head_content);
                        exit();
                }
        }
        $tool_content .= "<table width='99%'>
        <tbody><tr><td class='success'>$langStored</td>
        </tr></tbody></table>";
        header("Location: {$urlServer}modules/phpbb/viewtopic.php?course=$code_cours&topic=$topic&forum=$forum" . $page);
        exit;
} else {
        $sql = "SELECT f.forum_type, f.forum_name, t.topic_title
                        FROM forums f, topics t
                        WHERE f.forum_id = $forum
                                AND t.topic_id = $topic
                                AND t.forum_id = f.forum_id
                                AND f.course_id = $cours_id";

        if (!$result = db_query($sql)) {
                $tool_content .= $langTopicInformation;
                draw($tool_content, 2, null, $head_content);
                exit();
        }

        if (!$myrow = mysql_fetch_array($result)) {
                $tool_content .= $langErrorTopicSelect;
                draw($tool_content, 2, null, $head_content);
                exit();
        }

        $nameTools = $langReply;
        $navigation[]= array ("url"=>"index.php?course=$code_cours", "name"=> $langForums);
        $navigation[]= array ("url"=>"viewforum.php?course=$code_cours&amp;forum=$forum", "name"=> $myrow['forum_name']);
        $navigation[]= array ("url"=>"viewtopic.php?course=$code_cours&amp;topic=$topic&amp;forum=$forum", "name"=> $myrow['topic_title']);

        if (($myrow["forum_type"] == 1) && !$user_logged_in && !$logging_in) {
                // Private forum, no valid session, and login form not submitted...
                $tool_content .= "<form action='$_SERVER[PHP_SELF]?course=$code_cours' method='post'>
                <table width='100%' class='tbl'>
                <tr><td>$langPrivateNotice</td></tr>
                <tr><td>
                <table width='99%'>
                <tr>
                <td>&nbsp;</td>
                </tr>
                </table>
                </td>
                </tr>
                <tr>
                <td>
                <input type='hidden' name='forum' value='$forum' />
                <input type='hidden' name='topic' value='$topic' />
                <input type='hidden' name='post_id' value='$post_id' />
                <input type='submit' name='logging_in' value='$langEnter' />
                </td>
                </tr>
                </table></form>";
                draw($tool_content, 2, null, $head_content);
                exit();
        } 			
        $sql = "SELECT p.post_text, p.post_time, t.topic_title 
                        FROM posts p, topics t
                        WHERE p.post_id = $post_id 
                        AND p.topic_id = t.topic_id
                        AND p.course_id = $cours_id";
        $result = db_query($sql);                ;
        $myrow = mysql_fetch_array($result);
        if (isset($user_logged_in) && $user_logged_in) {
                if($user_level <= 2) {
                        if($user_level == 2 && !is_moderator($forum, $uid, $currentCourseID)) {
                                if($user_level < 2 && ($uid != $myrow["p.poster_id"])) {
                                        $tool_content .= $langNotEdit;
                                        draw($tool_content, 2, null, $head_content);
                                        exit();
                                }
                        }
                }
        }
        $message = $myrow["post_text"];
        $message = str_replace('{','&#123;',$message);						
        $message = bbdecode($message);
        $message = undo_make_clickable($message);
        $message = undo_htmlspecialchars($message);
        // Special handling for </textarea> tags in the message, which can break the editing form..
        $message = preg_replace('#</textarea>#si', '&lt;/TEXTAREA&gt;', $message);
        list($day, $time) = explode(' ', $myrow["post_time"]);
        $tool_content .= "<form action='$_SERVER[PHP_SELF]?course=$code_cours&amp;post_id=$post_id&amp;forum=$forum' method='post'>
                <fieldset>
                <legend>$langReplyEdit </legend>
                <table width='100%' class='tbl'>";
        $first_post = is_first_post($topic, $post_id);
        if($first_post) {
                $tool_content .= "<tr><td><b>$langSubject:</b><br /><br />
                <input type='text' name='subject' size='53' maxlength='100' value='" . stripslashes($myrow["topic_title"]) . "'  class='FormData_InputText' /></th>
                </tr>";
        }
        $tool_content .= "<tr><td><b>$langBodyMessage:</b><br /><br />".
        rich_text_editor('message', 10, 50, $message, "class='FormData_InputText'")
        ."	
        </td></tr>
        <tr><td class='right'>";
        $tool_content .= "<input class='Login' type='submit' name='submit' value='$langSubmit' />
        </td></tr>
        </table>
        </fieldset></form>";
}

draw($tool_content, 2, null, $head_content);
