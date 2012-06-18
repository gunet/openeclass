<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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


$require_current_course = TRUE;
$require_login = TRUE;
$require_help = FALSE;
$require_editor = TRUE;
require_once '../../include/baseTheme.php';
require_once 'config.php';
require_once 'functions.php'; 

if (isset($_GET['forum'])) {
        $forum_id = intval($_GET['forum']);
}
if (isset($_GET['topic'])) {
        $topic_id = intval($_GET['topic']);
}
if (isset($_GET['post_id'])) {
        $post_id = intval($_GET['post_id']);
}
if (isset($_POST['submit'])) {
        $message = $_POST['message'];
        if (isset($_POST['subject'])) {
                $subject = $_POST['subject'];	
        }
        $sql = "SELECT * FROM forum_post WHERE id = $post_id AND forum_id = $forum_id";
        if (!$result = db_query($sql)) {
                $tool_content .= $langErrorDataOne;
                draw($tool_content, 2, null, $head_content);
                exit();
        }
        
        $myrow = mysql_fetch_array($result);
        $topic_id = $myrow['topic_id'];
        $forum_id = $myrow['forum_id'];        
        $this_post_time = $myrow['post_time'];
        list($day, $time) = explode(' ', $myrow['post_time']);
        $date = date("Y-m-d H:i");

        $row1 = mysql_fetch_row(db_query("SELECT name FROM forum 
                                                 WHERE forum_id=$forum_id AND
                                                       course_id = $course_id"));
        $forum_name = $row1[0];
        $row2 = mysql_fetch_row(db_query("SELECT title FROM forum_topic
                                                 WHERE topic_id=$topic_id AND
                                                       forum_id = $forum_id"));
        $topic_title = $row2[0];

        $nameTools = $langReply;
        $navigation[] = array ('url' => "index.php?course=$course_code", 'name' => $langForums);
        $navigation[] = array ('url' => "viewforum.php?course=$course_code&amp;forum=$forum_id", 'name' => $name);
        $navigation[] = array ('url' => "viewtopic.php?course=$course_code&amp;topic=$topic_id&amp;forum=$forum_id", 'name' => $title);
                                
        $sql = "UPDATE forum_post SET post_text = " . autoquote(purify($message)) . "
                        WHERE id = $post_id 
                        AND forum_id = $forum_id";
        if (!$result = db_query($sql)) {
                $tool_content .= $langUnableUpdatePost;
                draw($tool_content, 2, null, $head_content);
                exit();
        }
        if (isset($subject)) {
                $subject = strip_tags($subject);
        }
        if (isset($subject) && (trim($subject) != '')) {			
                $sql = "UPDATE forum_topic
                        SET title = " . autoquote($subject) . " 
                        WHERE id = $topic_id
                                AND forum_id = $forum_id";
                if (!$result = db_query($sql)) {
                        $tool_content .= $langUnableUpdateTopic;
                        draw($tool_content, 2, null, $head_content);
                        exit();
                }
        }
        $tool_content .= "<table width='99%'>
        <tbody><tr><td class='success'>$langStored</td>
        </tr></tbody></table>";
        header("Location: {$urlServer}modules/phpbb/viewtopic.php?course=$course_code&topic=$topic_id&forum=$forum_id" . $page);
        exit;
} else {
        $sql = "SELECT f.name, t.title
                        FROM forum f, forum_topic t
                        WHERE f.id = $forum_id
                                AND t.id = $topic_id
                                AND t.forum_id = f.id";

        if (!$result = db_query($sql)) {
                $tool_content .= $langTopicInformation;
                draw($tool_content, 2, null, $head_content);
                exit();
        }
        $myrow = mysql_fetch_array($result);

        $nameTools = $langReply;
        $navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langForums);
        $navigation[] = array('url' => "viewforum.php?course=$course_code&amp;forum=$forum_id", 'name' => $myrow['name']);
        $navigation[] = array('url' => "viewtopic.php?course=$course_code&amp;topic=$topic_id&amp;forum=$forum_id", 'name' => $myrow['title']);
        
        $sql = "SELECT p.post_text, p.post_time, t.title 
                        FROM forum_post p, forum_topic t
                        WHERE p.id = $post_id 
                        AND p.topic_id = t.id";
        $result = db_query($sql);
        $myrow = mysql_fetch_array($result);        
        $message = $myrow["post_text"];
        $message = str_replace('{','&#123;',$message);
        // Special handling for </textarea> tags in the message, which can break the editing form..
        $message = preg_replace('#</textarea>#si', '&lt;/TEXTAREA&gt;', $message);
        list($day, $time) = explode(' ', $myrow["post_time"]);
        $tool_content .= "<form action='$_SERVER[PHP_SELF]?course=$course_code&amp;post_id=$post_id&amp;forum=$forum_id' method='post'>
                <fieldset>
                <legend>$langReplyEdit </legend>
                <table width='100%' class='tbl'>";
        $first_post = is_first_post($topic_id, $post_id);
        if($first_post) {
                $tool_content .= "<tr><td><b>$langSubject:</b><br /><br />
                <input type='text' name='subject' size='53' maxlength='100' value='" . stripslashes($myrow["title"]) . "'  class='FormData_InputText' /></th>
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
