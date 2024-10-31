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

require_once '../../include/baseTheme.php';
require_once 'modules/abuse_report/abuse_report.php';
require_once 'modules/message/class.msg.php';

$rtype = $_POST['rtype'];
$rid = intval($_POST['rid']);
$cid = intval($_POST['cid']);
$reason = $_POST['abuse_report_reason'];
$msg = trim($_POST['abuse_report_msg']);

if (empty($rtype) OR empty($rid) OR empty($cid)) {
    exit;
}

if (abuse_report_show_flag ($rtype, $rid, $cid, false)) {

    $response = array();

    if(empty($reason)) {
        $response[0] = 'fail';
        $response[1] = '<p class="text-danger">'.$langAbuseReportCatError.'</p><form id="abuse_form_'.$rtype.'_'.$rid.'">
		                         <fieldset>
                                 <legend class="mb-0" aria-label="'.$langForm.'"></legend>
                                      <div class="form-group has-error mt-3">
                                          <label for="abuse_form_select'.$rtype.'_'.$rid.'">'.$langAbuseReportCat.'</label>
                                          <select class="form-select" name="abuse_report_reason" id="abuse_form_select'.$rtype.'_'.$rid.'">
                                              <option value="rudeness">'.$langRudeness.'</option>
                                              <option value="spam">'.$langSpam.'</option>
                                              <option value="other">'.$langOther.'</option>
                                          </select>
                                      </div>
                                      <div class="form-group mt-3">
                                          <label for="abuse_form_txt'.$rtype.'_'.$rid.'">'.$langMessage.'</label>
                                          <textarea class="form-control" name="abuse_report_msg" id="abuse_form_txt'.$rtype.'_'.$rid.'"></textarea>
                                      </div>
                                      <input type="hidden" name="rtype" value="'.$rtype.'">
                                      <input type="hidden" name="rid" value="'.$rid.'">
                                      <input type="hidden" name="cid" value="'.$cid.'">
		                         </fieldset>
		                     </form>';
    } elseif (empty($msg)) {
        $response[0] = 'fail';
        $response[1] = '<p class="text-danger">'.$langAbuseReportMsgError.'</p><form id="abuse_form_'.$rtype.'_'.$rid.'">
		                         <fieldset>
                                 <legend class="mb-0" aria-label="'.$langForm.'"></legend>
                                      <div class="form-group mt-3">
                                          <label for="abuse_form_select'.$rtype.'_'.$rid.'">'.$langAbuseReportCat.'</label>
                                          <select class="form-select" name="abuse_report_reason" id="abuse_form_select'.$rtype.'_'.$rid.'">
                                              <option value="rudeness">'.$langRudeness.'</option>
                                              <option value="spam">'.$langSpam.'</option>
                                              <option value="other">'.$langOther.'</option>
                                          </select>
                                      </div>
                                      <div class="form-group has-error mt-3">
                                          <label for="abuse_form_txt'.$rtype.'_'.$rid.'">'.$langMessage.'</label>
                                          <textarea class="form-control" name="abuse_report_msg" id="abuse_form_txt'.$rtype.'_'.$rid.'"></textarea>
                                      </div>
                                      <input type="hidden" name="rtype" value="'.$rtype.'">
                                      <input type="hidden" name="rid" value="'.$rid.'">
                                      <input type="hidden" name="cid" value="'.$cid.'">
		                         </fieldset>
		                     </form>';
    } else {
        $id = Database::get()->query("INSERT INTO abuse_report (rid, rtype, course_id, reason, message, timestamp, user_id, status)
            VALUES (?d, ?s, ?d, ?s, ?s, UNIX_TIMESTAMP(NOW()), ?d, ?d)", $rid, $rtype, $cid, $reason, $msg, $uid, 1)->lastInsertID;

        if ($rtype == 'comment') {
            $res = Database::get()->querySingle("SELECT rid, rtype, content FROM comments WHERE id = ?d", $rid);
            $rcontent = $res->content;
            $comm_rid = $res->rid;
            $comm_rtype = $res->rtype;
        } elseif ($rtype == 'forum_post') {
            $res = Database::get()->querySingle("SELECT post_text FROM forum_post WHERE id = ?d", $rid);
            $rcontent = $res->post_text;
        } elseif ($rtype == 'link') {
            $res = Database::get()->querySingle("SELECT url, title FROM `link` WHERE id = ?d", $rid);
            $rcontent = $res->url;
            $link_title = $res->title;
        } elseif ($rtype == 'wallpost') {
            $res = Database::get()->querySingle("SELECT content FROM `wall_post` WHERE id = ?d", $rid);
            $rcontent = $res->content;
        }

        Log::record($cid, MODULE_ID_ABUSE_REPORT, LOG_INSERT,
                    array('id' => $id,
                          'user_id' => $uid,
                          'reason' => $reason,
                          'message' => $msg,
                          'rtype' => $rtype,
                          'rid' => $rid,
                          'rcontent' => $rcontent,
                          'status' => 1
                    ));

        //send PM to course editors
        $res = Database::get()->queryArray("SELECT user_id FROM course_user 
                WHERE course_id = ?d AND (status = ?d OR editor = ?d)", $cid, 1, 1);
        $editors = array();
        foreach ($res as $r) {
            $editors[] = $r->user_id;
        }

        //build variables depending on resource type
        if ($rtype == 'forum_post') {
            $res = Database::get()->querySingle("SELECT p.post_text, t.id, t.forum_id FROM forum_post as p, forum_topic as t 
                        WHERE p.topic_id = t.id AND p.id = ?d", $rid);
            $url = $urlServer."modules/forum/viewtopic.php?course=".course_id_to_code($cid).
                "&topic=".$res->id."&forum=".$res->forum_id."&post_id=".$rid."#".$rid;
            $content_type = $langAForumPost;
            $content = mathfilter($res->post_text, 12, "../../courses/mathimg/");
        } elseif ($rtype == 'comment') {
            if ($comm_rtype == 'blogpost') {
                $url = $urlServer."modules/blog/index.php?course=".course_id_to_code($cid).
                    "&action=showPost&pId=".$comm_rid."#comments_title";

            } elseif ($comm_rtype == 'course') {
                $url = $urlServer."courses/".course_id_to_code($comm_rid);
            } elseif ($comm_rtype == 'wallpost') {
                $url = $urlServer."modules/wall/index.php?course=".course_id_to_code($cid).
                    "&showPost=".$comm_rid."#comments_title";
            }
            $content_type = $langAComment;
            $content = q($rcontent);
        } elseif ($rtype == 'link') {
            $content_type = $langLink;
            $content = "<a href='" . $urlServer . "modules/link/go.php?course=".course_id_to_code($cid)."&amp;id=$rid&amp;url=" .
                urlencode($rcontent) . "'>" . q($link_title) . "</a>";
            $url = $urlServer."modules/link/index.php?course=".course_id_to_code($cid);
        } elseif ($rtype == 'wallpost') {
            $content_type = $langWallPost;
            $content = nl2br(standard_text_escape($rcontent));
            $url = $urlServer."modules/wall/index.php?course=".course_id_to_code($cid)."&amp;showPost=".$rid;
        }

        $v = Database::get()->querySingle("SELECT visible FROM course_module
                                WHERE module_id = ?d AND
                                course_id = ?d", MODULE_ID_MESSAGE, $cid)->visible;

        if ($v == 1) {
            $reports_cats = array('rudeness' => $langRudeness,
                                  'spam' => $langSpam,
                                  'other' => $langOther);

            $msg_body = sprintf($langAbuseReportPMBody, $content_type, $reports_cats[$reason], q($msg), $content, $url);

            $pm = new Msg($uid, $cid, $langAbuseReport, $msg_body, $editors);
        }

        $response[0] = 'succes';
        $response[1] = '<p>'.$langAbuseReportSaveSuccess.'</p>';
    }

    echo json_encode($response);
}
