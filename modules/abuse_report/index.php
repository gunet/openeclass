<?php
/* ========================================================================
* Open eClass 3.0
* E-learning and Course Management System
* ========================================================================
* Copyright 2003-2014  Greek Universities Network - GUnet
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

$require_current_course = true;
$require_course_admin = true;

require_once '../../include/baseTheme.php';
require_once 'include/log.php';
require_once 'modules/dropbox/class.msg.php';

$toolName = $langAbuseReports;

if (isset($_GET['choice']) && $_GET['choice'] == 'close') { //close report
    if (isset($_GET['report'])) {
        $id = intval($_GET['report']);
        //check if user has right to close report
        $res = Database::get()->querySingle("SELECT * FROM abuse_report WHERE id = ?d
                    AND course_id = ?d", $id, $course_id);
        if ($res) { //if report id actually belongs to this course then the editor may close it
            
            $rtype = $res->rtype;
            $rid = $res->rid;
            $user_id = $res->user_id;
            $reason = $res->reason;
            $message = $res->message;
            
            if ($rtype == 'comment') {
                $result = Database::get()->querySingle("SELECT rid, rtype, content FROM comments WHERE id = ?d", $rid);
                $comm_rid = $result->rid;
                $comm_rtype = $result->rtype;
                if ($comm_rtype == 'blogpost') {
                    $url = $urlServer."modules/blog/index.php?course=".$course_code.
                    "&action=showPost&pId=".$comm_rid."#comments_title";
                
                } elseif ($comm_rtype == 'course') {
                    $url = $urlServer."courses/".$course_code;
                }
                $content_type = $langAComment;
                $content = q($result->content);
                $rcontent = $result->content;
            } elseif ($rtype == 'forum_post') {
                $result = Database::get()->querySingle("SELECT p.post_text, t.id, t.forum_id FROM forum_post as p, forum_topic as t
                        WHERE p.topic_id = t.id AND p.id = ?d", $rid);
                $url = $urlServer."modules/forum/viewtopic.php?course=".$course_code.
                "&topic=".$result->id."&forum=".$result->forum_id."&post_id=".$rid."#".$rid;
                $content_type = $langAForumPost;
                $content = mathfilter($result->post_text, 12, "../../courses/mathimg/");
                $rcontent = $result->post_text;
            } elseif ($rtype == 'link') {
                $result = Database::get()->querySingle("SELECT url, title FROM `link` WHERE id = ?d", $rid);
                $content_type = $langLink;
                $rcontent = $result->url;
                $url = $urlServer."modules/link/?course=".$course_code;
                $content = "<a href='" . $urlServer . "modules/link/go.php?course=".$course_code."&amp;id=$rid&amp;url=" .
                urlencode($rcontent) . "'>" . q($result->title) . "</a>";
            } elseif ($rtype == 'wallpost') {
                $res = Database::get()->querySingle("SELECT content FROM `wall_post` WHERE id = ?d", $rid);
                $rcontent = $res->url;
                $content_type = $langWallPost;
                $content = nl2br(q($content));
                $url = $urlServer."modules/wall/?course=".course_id_to_code($cid)."&amp;showPost=".$rid;
            }
            
            Log::record($course_id, MODULE_ID_ABUSE_REPORT, LOG_MODIFY,
                    array('id' => $id,
                          'user_id' => $user_id,
                          'reason' => $reason,
                          'message' => $message,
                          'rtype' => $rtype,
                          'rid' => $rid,
                          'rcontent' => $rcontent,
                          'status' => 0
                    ));
            
            Database::get()->query("UPDATE abuse_report SET status = ?d WHERE id = ?d", 0, $id);
            
            if (visible_module(MODULE_ID_DROPBOX)) {
                //send PM to user that created the report and other course editors
                $result = Database::get()->queryArray("SELECT user_id FROM course_user
                    WHERE course_id = ?d AND (status = ?d OR editor = ?d) AND user_id <> ?d", $course_id, 1, 1, $uid);
                $recipients = array();
                $recipients[] = $user_id;
                foreach ($result as $r) {
                    $recipients[] = $r->user_id;
                }
                
                $reports_cats = array('rudeness' => $langRudeness,
                                      'spam' => $langSpam,
                                      'other' => $langOther);
                
                $msg_body = sprintf($langAbuseReportClosePMBody, $content_type, $reports_cats[$reason], q($message), $content, $url);
                
                $pm = new Msg($uid, $course_id, $langMsgRe.$langAbuseReport, $msg_body, $recipients);
            }
            
            Session::Messages($langCloseReportSuccess, 'alert-success');
            redirect_to_home_page("modules/abuse_report/index.php?course=$course_code");
        }
    }
}

// maximum number of reports on a page
$limitReportsPage = 15;
if (isset($_GET['page'])) {
    $page = intval($_GET['page']);
} else {
    $page = 0;
}
// selects $limitReportsPage reports at the same time
$from = $page * $limitReportsPage;

$result = Database::get()->queryArray("SELECT * FROM abuse_report WHERE course_id = ?d AND status = ?d ORDER BY timestamp DESC LIMIT ?d, ?d", $course_id, 1, $from, $limitReportsPage);
//Number of all reports for this course
$total_reports = Database::get()->querySingle("SELECT COUNT(*) as count FROM abuse_report WHERE course_id = ?d AND status = ?d", $course_id, 1)->count;
//Number of reports for current page
$nbrReports = count($result);

if (!$nbrReports) {
    $tool_content .= "<div class='alert alert-warning'>$langNoAbuseReports</div>";
} else {
    $reports_cats = array('rudeness' => $langRudeness,
                          'spam' => $langSpam,
                          'other' => $langOther);
    
    $resource_types = array('comment' => $langComment,
                            'forum_post' => $langForumPost,
                            'link' => $langLink,
                            'wallpost' => $langWallPost);
    
    $maxpage = 1 + intval($total_reports / $limitReportsPage);
    if ($maxpage > 0) {
        $prevpage = $page - 1;
        $nextpage = $page + 1;
        if ($prevpage >= 0) {
            $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;page=$prevpage'>&lt;&lt; $langPreviousPage</a>&nbsp;";
        }
        if ($nextpage < $maxpage) {
            $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;page=$nextpage'>$langNextPage &gt;&gt;</a>";
        }
    }
    
    $tool_content .= "<div class='table-responsive'><table class='table-default'><tr>";
    $tool_content .= "
            <th>$langAbuseReportCat</th>
            <th>$langMessage</th>
            <th>$langAbuseResourceType</th>
            <th>$langContent</th>
            <th>$langUser</th>
            <th>$langDate</th>
            <th class='text-center'>".icon('fa-gears')."</th>
          </tr>";
    
    foreach ($result as $report) {
        
        if ($report->rtype == 'comment') {
            $res = Database::get()->querySingle("SELECT content FROM comments WHERE id = ?d", $report->rid);
            $content = q($res->content);
            
            $res = Database::get()->querySingle("SELECT rid, rtype FROM comments WHERE id = ?d", $report->rid);
            $comm_rid = $res->rid;
            $comm_rtype = $res->rtype;
            if ($comm_rtype == 'blogpost') {
                $visiturl = $urlServer."modules/blog/index.php?course=".$course_code."&action=showPost&pId=".$comm_rid."#comments_title";
            } elseif ($comm_rtype == 'course') {
                $visiturl = $urlServer."courses/".$course_code;
            }
            
            $options = action_button(array(
                           array('title' => $langAbuseReportClose,
                                 'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=close&amp;report=$report->id",
                                 'icon' => 'fa-archive',
                                 'confirm' => $langConfirmAbuseReportClose,
                                 'confirm_title' => $langAbuseReportClose,
                                 'confirm_button' => $langClose),
                           array('title' => $langVisitReportedResource,
                                 'url' => $visiturl,
                                 'icon' => 'fa-external-link'),
                       ));
        } elseif ($report->rtype == 'forum_post') {
            $res = Database::get()->querySingle("SELECT post_text FROM forum_post WHERE id = ?d", $report->rid);
            $content = mathfilter($res->post_text, 12, "../../courses/mathimg/");
            
            $res = Database::get()->querySingle("SELECT t.id, t.forum_id FROM forum_post as p, forum_topic as t
                        WHERE p.topic_id = t.id AND p.id = ?d", $report->rid);
            $visiturl = $urlServer."modules/forum/viewtopic.php?course=".$course_code."&topic=".$res->id."&forum=".$res->forum_id."&post_id=".$report->rid."#".$report->rid;
            $editurl = $urlServer."modules/forum/editpost.php?course=".$course_code."&topic=".$res->id."&forum=".$res->forum_id."&post_id=".$report->rid;
            $deleteurl = $urlServer."modules/forum/viewtopic.php?course=".$course_code."&topic=".$res->id."&forum=".$res->forum_id."&post_id=".$report->rid."&delete=on";
            
            $options = action_button(array(
                           array('title' => $langAbuseReportClose,
                                 'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=close&amp;report=$report->id",
                                 'icon' => 'fa-archive',
                                 'confirm' => $langConfirmAbuseReportClose,
                                 'confirm_title' => $langAbuseReportClose,
                                 'confirm_button' => $langClose),
                           array('title' => $langVisitReportedResource,
                                 'url' => $visiturl,
                                 'icon' => 'fa-external-link'),
                           array('title' => $langEditReportedResource,
                                 'url' => $editurl,
                                 'icon' => 'fa-edit'),
                           array('title' => $langDeleteReportedResource,
                                 'url' => $deleteurl,
                                 'icon' => 'fa-times',
                                 'class' => 'delete',
                                 'confirm' => $langConfirmDeleteReportedResource),
                       ));
        } elseif ($report->rtype == 'link') {
            $res = Database::get()->querySingle("SELECT url, title FROM `link` WHERE id = ?d", $report->id);
            $content = "<a href='" . $urlServer . "modules/link/go.php?course=".$course_code."&amp;id=$report->id&amp;url=" .
                urlencode($res->url) . "'>" . q($res->title) . "</a>";
            $visiturl = $urlServer."modules/link/?course=$course_code&amp;socialview";
            $editurl = $urlServer."modules/link/?course=$course_code&amp;id=$report->id&amp;action=editlink";
            $deleteurl = $urlServer."modules/link/?course=$course_code&amp;id=$report->id&amp;action=deletelink";
            $options = action_button(array(
                    array('title' => $langAbuseReportClose,
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=close&amp;report=$report->id",
                            'icon' => 'fa-archive',
                            'confirm' => $langConfirmAbuseReportClose,
                            'confirm_title' => $langAbuseReportClose,
                            'confirm_button' => $langClose),
                    array('title' => $langVisitReportedResource,
                            'url' => $visiturl,
                            'icon' => 'fa-external-link'),
                    array('title' => $langEditReportedResource,
                          'url' => $editurl,
                          'icon' => 'fa-edit'),
                    array('title' => $langDeleteReportedResource,
                          'url' => $deleteurl,
                          'icon' => 'fa-times',
                          'class' => 'delete',
                          'confirm' => $langConfirmDeleteReportedResource),
            ));
        } elseif ($report->rtype == 'wallpost') {
            $res = Database::get()->querySingle("SELECT content FROM `wall_post` WHERE id = ?d", $report->rid);
            $content = nl2br(q($res->content));
            $visiturl = $urlServer."modules/wall/?course=$course_code&amp;showPost=$report->rid";
            $editurl = $urlServer."modules/wall/?course=$course_code&amp;edit=$report->rid";
            $deleteurl = $urlServer."modules/wall/?course=$course_code&amp;delete=$report->rid";
            $options = action_button(array(
                    array('title' => $langAbuseReportClose,
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=close&amp;report=$report->id",
                            'icon' => 'fa-archive',
                            'confirm' => $langConfirmAbuseReportClose,
                            'confirm_title' => $langAbuseReportClose,
                            'confirm_button' => $langClose),
                    array('title' => $langVisitReportedResource,
                            'url' => $visiturl,
                            'icon' => 'fa-external-link'),
                    array('title' => $langEditReportedResource,
                            'url' => $editurl,
                            'icon' => 'fa-edit'),
                    array('title' => $langDeleteReportedResource,
                            'url' => $deleteurl,
                            'icon' => 'fa-times',
                            'class' => 'delete',
                            'confirm' => $langConfirmDeleteReportedResource),
            ));
        }
        
        $tool_content .= "<tr>
                            <td>".$reports_cats[$report->reason]."</td>
                            <td>".q($report->message)."</td>
                            <td>".$resource_types[$report->rtype]."</td>
                            <td>".$content."</td>
                            <td>".display_user($report->user_id)."</td>
                            <td>".nice_format(date('Y-m-d H:i:s', $report->timestamp), true)."</td>                                    
                            <td class='option-btn-cell'>".$options."</td>
                          </tr>";
    }
    
    $tool_content .= "</table></div>";
}

draw($tool_content, 2, null, $head_content);
