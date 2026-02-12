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

function check_user_belongs_in_session($sid){
    global $course_id, $course_code, $is_consultant, $is_coordinator, $is_simple_user, $uid;

    if($is_consultant && !$is_coordinator){
        $check = Database::get()->querySingle("SELECT creator FROM mod_session WHERE id = ?d AND course_id = ?d",$sid, $course_id);
        if($check && $check->creator != $uid){
            redirect_to_home_page("modules/session/index.php?course=$course_code");
        }
    }elseif($is_simple_user){
        $check = Database::get()->querySingle("SELECT id FROM mod_session_users WHERE session_id = ?d AND participants = ?d AND is_accepted = ?d",$sid, $uid, 1);
        if(!$check){
            redirect_to_home_page("modules/session/index.php?course=$course_code");
        }
    }
}

function is_session_type_course(){
    global $course_id, $course_code, $langCourseIsNotCollaborative;

    $t = Database::get()->querySingle("SELECT view_type FROM course WHERE id = ?d AND is_collaborative = ?d",$course_id,1);
    if ($t->view_type != 'sessions') {
        Session::flash('message',$langCourseIsNotCollaborative);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page("courses/$course_code/");
    }
}

function is_session_consultant($sid,$cid){
    global $uid;

    $result = Database::get()->querySingle("SELECT creator FROM mod_session WHERE course_id = ?d AND id = ?d",$cid,$sid);
    if($result->creator == $uid){
        return true;
    }else{
        return false;
    }
}

function get_session_consultant($sid,$cid){
    $result = Database::get()->querySingle("SELECT creator FROM mod_session WHERE course_id = ?d AND id = ?d",$cid,$sid);
    if($result){
        return $result->creator;
    }else{
        return 0;
    }
}

function title_session($cid,$sid){
    $result = Database::get()->querySingle("SELECT title FROM mod_session WHERE id = ?d AND course_id = ?d",$sid,$cid);
    return $result->title;
}

function date_session($cid,$sid){
    $s = Database::get()->querySingle("SELECT start FROM mod_session WHERE id = ?d AND course_id = ?d",$sid,$cid);
    $start = $s->start;

    $f = Database::get()->querySingle("SELECT finish FROM mod_session WHERE id = ?d AND course_id = ?d",$sid,$cid);
    $finish = $f->finish;

    $result = format_locale_date(strtotime($start), 'short');

    return $result;
}

function is_session_visible($cid,$sid){
    $res = Database::get()->querySingle("SELECT visible FROM mod_session WHERE id = ?d AND course_id = ?d",$sid,$cid);
    $start = $res->visible;

    if(!$res->visible){
        return false;
    }else{
        return true;
    }
}

function session_activation($cid,$sid){
    $res = Database::get()->querySingle("SELECT start,finish FROM mod_session WHERE id = ?d AND course_id = ?d",$sid,$cid);

    if(date('Y-m-d H:i:s') < $res->start or date('Y-m-d H:i:s') > $res->finish){
        return false;
    }else{
        return true;
    }
}

function session_is_running($cid,$sid){
    $res = Database::get()->querySingle("SELECT start,finish FROM mod_session WHERE id = ?d AND course_id = ?d",$sid,$cid);

    if(date('Y-m-d H:i:s') > $res->start && date('Y-m-d H:i:s') < $res->finish){
        return true;
    }else{
        return false;
    }
}

function session_not_started($cid,$sid){
    $res = Database::get()->querySingle("SELECT start FROM mod_session WHERE id = ?d AND course_id = ?d",$sid,$cid);

    if(date('Y-m-d H:i:s') < $res->start){
        return true;
    }else{
        return false;
    }
}

function session_has_expired($cid,$sid){
    $res = Database::get()->querySingle("SELECT finish FROM mod_session WHERE id = ?d AND course_id = ?d",$sid,$cid);

    if(date('Y-m-d H:i:s') > $res->finish){
        return true;
    }else{
        return false;
    }
}

function is_remote_session($cid,$sid){
    $res = Database::get()->querySingle("SELECT type_remote FROM mod_session WHERE id = ?d AND course_id = ?d",$sid,$cid);

    if($res->type_remote){
        return true;
    }else{
        return false;
    }
}

function participant_name($userId){
    $name = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$userId);
    $surname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$userId);
    return $name->givenname . ' ' . $surname->surname;
}

function session_resource_info($rid,$sid){
    $resource = array();
    $resource = Database::get()->queryArray("SELECT * FROM session_resources WHERE id = ?d AND session_id = ?d",$rid,$sid);
    return $resource;
}

/**
 * @brief fills an array with user groups (group_id => group_name)
 * passing $as_id will give back only the groups that have been given the specific assignment
 * @param type $uid
 * @param type $course_id
 * @param type $as_id
 * @return type
 */
function user_group_session_info($uid, $course_id, $as_id = NULL) {
    $gids = array();

    if ($uid != null) {
        $q = Database::get()->queryArray("SELECT group_members.group_id AS grp_id, `group`.name AS grp_name FROM group_members,`group`
            WHERE group_members.group_id = `group`.id
            AND `group`.course_id = ?d AND group_members.user_id = ?d", $course_id, $uid);
    } else {
        if (!is_null($as_id) && Database::get()->querySingle("SELECT assign_to_specific FROM assignment WHERE id = ?d", $as_id)->assign_to_specific) {
            $q = Database::get()->queryArray("SELECT `group`.name AS grp_name,`group`.id AS grp_id FROM `group`, assignment_to_specific WHERE `group`.id = assignment_to_specific.group_id AND `group`.course_id = ?d AND assignment_to_specific.assignment_id = ?d", $course_id, $as_id);
        } else {
            $q = Database::get()->queryArray("SELECT name AS grp_name,id AS grp_id FROM `group` WHERE course_id = ?d", $course_id);
        }
    }

    foreach ($q as $r) {
        $gids[$r->grp_id] = $r->grp_name;
    }
    return $gids;
}

/**
 * @brief all participants ids in session
 * @param integer $sid
 */
function session_participants_ids($sid){
    $partipants_ids = array();
    $res = Database::get()->queryArray("SELECT participants FROM mod_session_users WHERE session_id = ?d AND is_accepted = ?d",$sid,1);
    if(count($res) > 0){
        foreach($res as $r){
            $partipants_ids[] = $r->participants;
        }
    }
    return $partipants_ids;
}

/**
 * @brief all participants ids in session where not accepted yet
 * @param integer $sid
 */
function session_edit_participants_ids($sid){
    $users = array();
    $res = Database::get()->queryArray("SELECT participants FROM mod_session_users WHERE session_id = ?d",$sid);
    if(count($res) > 0){
        foreach($res as $r){
            $users[] = $r->participants;
        }
    }
    return $users;
}

/**
 * @brief check if a user participates in a session
 * @param integer $sid
 */
function participation_in_session($sid){
    global $uid;

    $res = Database::get()->queryArray("SELECT * FROM mod_session_users WHERE session_id = ?d AND participants = ?d AND is_accepted = ?d",$sid,$uid,1);
    if(count($res) > 0){
        return true;
    }else{
        return false;
    }

}

/**
 * @brief insert docs in database
 * @param integer $sid
 */
function insert_session_docs($sid) {
    global $webDir, $course_id, $course_code, $group_sql, $subsystem, $subsystem_id, $basedir;

    if(isset($_POST['document'])){
        $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM session_resources WHERE session_id = ?d", $sid)->maxorder;
        foreach ($_POST['document'] as $file_id) {
            $order++;
            $file = Database::get()->querySingle("SELECT * FROM document
                                        WHERE course_id = ?d AND id = ?d", $course_id, $file_id);
            $title = (empty($file->title)) ? $file->filename : $file->title;
            if (empty($file->comment)) {
                $comment = '';
            } else {
                $comment = $file->comment;
            }

            $q = Database::get()->query("INSERT INTO session_resources SET session_id = ?d, type='doc',
                                            title = ?s, comments = ?s,
                                            visible = 1, `order` = ?d,
                                            `date` = " . DBHelper::timeAfter() . ", res_id = ?d",
                                        $sid, $title, $comment, $order, $file->id);
        }
    }

    header('Location: session_space.php?course=' . $course_code . '&session=' . $sid);
    exit;
}

/**
 * @brief insert tc resource in course session resources
 * @param integer $sid
 */
function insert_session_tc($sid) {
    global $course_code, $course_id;

    if(isset($_POST['tc'])){
        $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM session_resources WHERE session_id = ?d", $sid)->maxorder;
        foreach ($_POST['tc'] as $tc_id) {
            $order++;
            $tc = Database::get()->querySingle("SELECT * FROM tc_session
                            WHERE course_id = ?d AND id = ?d", $course_id, $tc_id);

            $q =  Database::get()->query("INSERT INTO session_resources SET session_id = ?d, type='tc', title = ?s, comments = ?s,
                                        visible = 1, `order` = ?d, `date` = " . DBHelper::timeAfter() . ", res_id = ?d",
                                            $sid, $tc->title, $tc->description, $order, $tc->id);
        }

    }

    header('Location: session_space.php?course=' . $course_code . '&session=' . $sid);
    exit;
}

/**
 * @brief insert work (assignment) in database
 * @param integer $sid
 */
function insert_session_work($sid) {
    global $course_code, $course_id;
    if(isset($_POST['work'])){
        $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM session_resources WHERE session_id = ?d", $sid)->maxorder;
        foreach ($_POST['work'] as $work_id) {
            $order++;
            $work = Database::get()->querySingle("SELECT * FROM assignment
                            WHERE course_id = ?d AND id = ?d", $course_id, $work_id);
            if ($work->active == '0') {
                $visibility = 0;
            } else {
                $visibility = 1;
            }

            $q = Database::get()->query("INSERT INTO session_resources SET
                                    session_id = ?d,
                                    type = 'work',
                                    title = ?s,
                                    comments = ?s,
                                    visible = ?d,
                                    `order` = ?d,
                                    `date` = " . DBHelper::timeAfter() . ",
                                    res_id = ?d", $sid, $work->title, $work->description, $visibility, $order, $work->id);
        }

    }

    header('Location: session_space.php?course=' . $course_code . '&session=' . $sid);
    exit;
}

// delete all files and sub-folders from a folder
function deleteAll($dir) {
    foreach(glob($dir . '/*') as $file) {
        if(is_dir($file)){
            deleteAll($file);
        }else{
            unlink($file);
        }
    }
    rmdir($dir);
}

/**
 * @brief delete session from database
 * @param integer $sid
 */
function delete_session($sid = 0){

    global $course_code, $webDir, $course_id;

    if($sid){
        $sqlbadge = Database::get()->querySingle("SELECT id FROM badge WHERE course_id = ?d AND session_id = ?d", $course_id, $sid);
        if($sqlbadge){
            $badge_id = $sqlbadge->id;
            $res = Database::get()->queryArray("SELECT id FROM badge_criterion WHERE badge = ?d",$badge_id);
            if(count($res) > 0){
                foreach($res as $r){
                    Database::get()->query("DELETE FROM user_badge_criterion WHERE badge_criterion = ?d",$r->id);
                }
            }
            Database::get()->query("DELETE FROM user_badge WHERE badge = ?d",$badge_id);
            Database::get()->query("DELETE FROM badge_criterion WHERE badge = ?d",$badge_id);
            Database::get()->query("DELETE FROM badge WHERE id = ?d AND session_id = ?d",$badge_id,$sid);
        }
        Database::get()->query("DELETE FROM session_prerequisite WHERE session_id = ?d OR prerequisite_session = ?d",$sid,$sid);
        $dirname = "$webDir/courses/$course_code/session/session_" . $sid;
        if (file_exists($dirname)) {
            deleteAll($dirname);
        }
        Database::get()->query("DELETE FROM document WHERE course_id = ?d AND subsystem = ?d AND subsystem_id = ?d",$course_id,MYSESSIONS,$sid);
        $tc_res = Database::get()->querySingle("SELECT res_id FROM session_resources WHERE session_id = ?d AND type = ?s",$sid,'tc');
        if($tc_res){
            Database::get()->query("DELETE FROM tc_session WHERE id = ?d AND id_session = ?d",$tc_res->res_id,$sid);
        }
        Database::get()->query("DELETE FROM session_resources WHERE session_id = ?d",$sid);
        Database::get()->query("DELETE FROM mod_session WHERE id = ?d",$sid);
    }else{
        redirect_to_home_page("modules/$course_code/");
    }

}


/**
 * @brief Display resources for unit with id=$id
 * @global type $tool_content
 * @global type $max_resource_id
 * @param type $sid
 */
function show_session_resources($sid)
{

    global $max_resource_id, $is_simple_user,
           $head_content, $langDownload, $langPrint, $langCancel,
           $langFullScreen, $langNewTab, $langActInHome, $langActInClass, $langActAfterClass, $course_code, $langNoAvailableSessionRecourses;

        $html = '';

        $visible_sql = "";
        if($is_simple_user){
            $visible_sql = "AND visible = 1";
        }

        $req = Database::get()->queryArray("SELECT * FROM session_resources WHERE session_id = ?d $visible_sql AND `order` >= 0 ORDER BY `order`", $sid);

        if (count($req) > 0) {
            load_js('sortable/Sortable.min.js');
            load_js('screenfull/screenfull.min.js');
            $head_content .= "<script>
            $(document).ready(function(){
                Sortable.create(sessionResources,{
                    handle: '.fa-arrows',
                    animation: 150,
                    onEnd: function (evt) {

                    var itemEl = $(evt.item);

                    var idReorder = itemEl.attr('data-id');
                    var prevIdReorder = itemEl.prev().attr('data-id');

                    $.ajax({
                    type: 'post',
                    dataType: 'text',
                    data: {
                            toReorder: idReorder,
                            prevReorder: prevIdReorder,
                            }
                        });
                    }
                });
            });
            $(function(){
                $('.fileModal').click(function (e)
                {
                    e.preventDefault();
                    var fileURL = $(this).attr('href');
                    var downloadURL = $(this).prev('input').val();
                    var fileTitle = $(this).attr('title');
                    var buttons = {};
                    if (downloadURL) {
                        buttons.download = {
                                label: '<i class=\"fa fa-download\"></i> $langDownload',
                                className: 'submitAdminBtn gap-1',
                                callback: function (d) {
                                    window.location = downloadURL;
                                }
                        };
                    }
                    buttons.print = {
                                label: '<i class=\"fa fa-print\"></i> $langPrint',
                                className: 'submitAdminBtn gap-1',
                                callback: function (d) {
                                    var iframe = document.getElementById('fileFrame');
                                    iframe.contentWindow.print();
                                }
                            };
                    if (screenfull.enabled) {
                        buttons.fullscreen = {
                            label: '<i class=\"fa fa-arrows-alt\"></i> $langFullScreen',
                            className: 'submitAdminBtn gap-1',
                            callback: function() {
                                screenfull.request(document.getElementById('fileFrame'));
                                return false;
                            }
                        };
                    }
                    buttons.newtab = {
                        label: '<i class=\"fa fa-plus\"></i> $langNewTab',
                        className: 'submitAdminBtn gap-1',
                        callback: function() {
                            window.open(fileURL);
                            return false;
                        }
                    };
                    buttons.cancel = {
                                label: '$langCancel',
                                className: 'cancelAdminBtn'
                            };
                    bootbox.dialog({
                        size: 'large',
                        title: fileTitle,
                        message: '<div class=\"row\">'+
                                    '<div class=\"col-sm-12\">'+
                                        '<div class=\"iframe-container\" style=\"height:500px;\"><iframe title=\"'+fileTitle+'\" id=\"fileFrame\" src=\"'+fileURL+'\" style=\"width:100%; height:500px;\"></iframe></div>'+
                                    '</div>'+
                                '</div>',
                        buttons: buttons
                    });
                });
            });

        </script>";
            $max_resource_id = Database::get()->querySingle("SELECT id FROM session_resources
                                WHERE session_id = ?d ORDER BY `order` DESC LIMIT 1", $sid)->id;
            $html .= "<div class='table-responsive'>";
            $html .= "<table class='table table-striped table-hover table-default'><tbody id='sessionResources'>";
            foreach ($req as $info) {
                if (!is_null($info->comments)) {
                    $info->comments = standard_text_escape($info->comments);
                }
                $html .= show_sessionResource($info);
            }
            $html .= "</tbody></table>";
            $html .= "</div>";
        }else{
            $html .= "<div class='alert alert-warning'>
                            <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                            <span>$langNoAvailableSessionRecourses</span>
                      </div>";
        }



    return $html;
}


/**
 * @brief display unit resources
 * @param type $info
 */
function show_sessionResource($info) {

    global $langUnknownResType, $is_consultant;

    $html = '';
    if ($info->visible == 0 and $info->type != 'doc' and !$is_consultant) { // special case handling for old unit resources with type 'doc' .
        return;
    }
    switch ($info->type) {
        case 'doc':
            $html .= show_session_doc($info->title, $info->comments, $info->id, $info->res_id);
            break;
        case 'doc_reference':
            $html .= show_session_reference_doc($info->title, $info->comments, $info->id, $info->res_id);
            break;
        case 'work':
            $html .= show_session_work($info->title, $info->comments, $info->id, $info->res_id, $info->visible);
            break;
        case 'tc':
            $html .= show_session_tc($info->title, $info->comments, $info->id, $info->res_id, $info->visible);
            break;
        case 'passage':
            $html .= show_session_passage($info->title, $info->id, $info->passage, $info->visible);
            break;
        case 'link':
            $html .= show_session_link($info->title, $info->comments, $info->id, $info->res_id, $info->visible);
            break;
        case 'poll':
            $html .= show_session_poll($info->title, $info->comments, $info->id, $info->res_id, $info->visible);
            break;
        default:
            $html .= $langUnknownResType;
    }

    return $html;
}


/**
 * @brief display resource poll
 * @param type $type
 * @param type $title
 * @param type $resource_id
 * @param type $poll_id
 * @param type $visibility
 * @return string
 */
function show_session_poll($title, $comments, $resource_id, $poll_id, $visibility) {

    global $course_id, $course_code, $is_consultant, $urlServer, $uid, $langWasDeleted, 
           $langResourceBelongsToSessionPrereq, $m, $is_course_reviewer, $langUsersAnswers,
           $langWorkToUser, $langWorkAssignTo, $langWorkToGroup;

    $res_prereq_icon = '';
    $class_vis = ($visibility == 0 ) ? ' class="not_visible"' : ' ';
    $title = q($title);
    $poll_results_link = '';
    if ($is_consultant or $is_course_reviewer) {
        $poll = Database::get()->querySingle("SELECT * FROM poll WHERE course_id = ?d AND pid = ?d", $course_id, $poll_id);
        if ($poll) {
            $poll_user_answers = Database::get()->queryArray("SELECT id FROM poll_user_record WHERE pid = ?d", $poll->pid);
            if (count($poll_user_answers) == 0) {
                $badge_criterion = Database::get()->querySingle("SELECT id FROM badge_criterion 
                                                                    WHERE activity_type = ?s
                                                                    AND `resource` = ?d
                                                                    AND badge IN (SELECT id FROM badge WHERE 
                                                                                    course_id = ?d AND session_id = ?d)", 'questionnaire', $poll->pid, $course_id, $_GET['session']);

                if ($badge_criterion) {
                    Database::get()->query("DELETE FROM user_badge_criterion WHERE badge_criterion = ?d", $badge_criterion->id);
                }
            }
        }
    } else {
        $gids = user_group_session_info($uid, $course_id);
        if (!empty($gids)) {
            $gids_sql_ready = implode(',',array_keys($gids));
        } else {
            $gids_sql_ready = "''";
        }
        $query = "SELECT * FROM poll WHERE course_id = ?d AND pid = ?d AND active = 1";
        $query .= " AND
                    (assign_to_specific = '0' OR assign_to_specific != '0' AND pid IN
                       (SELECT poll_id FROM poll_to_specific WHERE user_id = ?d
                        UNION
                       SELECT poll_id FROM poll_to_specific WHERE group_id IN ($gids_sql_ready))
                    )";
        $poll = Database::get()->querySingle($query, $course_id, $poll_id, $uid);

        // Check if poll answers have been deleted
        if ($poll) {
            $poll_user_answers = Database::get()->querySingle("SELECT id FROM poll_user_record WHERE pid = ?d AND `uid` = ?d", $poll->pid, $uid);
            if (!$poll_user_answers) {
                $badge_criterion = Database::get()->querySingle("SELECT id FROM badge_criterion 
                                                                    WHERE activity_type = ?s
                                                                    AND `resource` = ?d
                                                                    AND badge IN (SELECT id FROM badge WHERE 
                                                                                    course_id = ?d AND session_id = ?d)", 'questionnaire', $poll->pid, $course_id, $_GET['session']);

                if ($badge_criterion) {
                    Database::get()->query("DELETE FROM user_badge_criterion WHERE user = ?d AND badge_criterion = ?d", $uid, $badge_criterion->id);
                }
            }

            $poll_user_answers_count = Database::get()->querySingle("SELECT COUNT(*) as total FROM poll_user_record 
                                                                     WHERE pid = ?d
                                                                     AND uid IN (SELECT participants FROM mod_session_users
                                                                                 WHERE session_id = ?d
                                                                                 AND is_accepted = 1)
                                                                     AND session_id = ?d", $poll->pid, $_GET['session'], $_GET['session'])->total;
            if ($poll->show_results && $poll_user_answers_count >0) {
                $poll_results_link = "</br></br><a class='link-color small-text' href='../questionnaire/pollresults.php?course=$course_code&session=$_GET[session]&pid={$poll->pid}&from_session_view=true'><i class='fa-solid fa-chart-line'></i>&nbsp;$langUsersAnswers</a>)";
            }
        }
    }

    if (!$poll) { // check if it was deleted
        if (!$is_consultant or !$is_course_reviewer) {
            return '';
        } else {
            $imagelink = icon('fa-xmark link-delete');
            $polllink = "<span class='not_visible'>$title ($langWasDeleted)</span>";
        }
    } else {
        $assign_to_users_message = '';
        if ($is_consultant or $is_course_reviewer) {
            if ($poll->assign_to_specific == 1) {
                $assign_to_users_message = "<small class='help-block'>$langWorkAssignTo: $langWorkToUser</small>";
            } else if ($poll->assign_to_specific == 2) {
                $assign_to_users_message = "<small class='help-block'>$langWorkAssignTo: $langWorkToGroup</small>";
            }
            if (resource_belongs_to_session_completion($_GET['session'], $poll_id)) {
                $res_prereq_icon = icon('fa-star', $langResourceBelongsToSessionPrereq);
            }
        }
        $link = "<a href='{$urlServer}modules/units/view.php?course=$course_code&amp;res_type=questionnaire&amp;pid=$poll_id&amp;UseCase=1&amp;session=$_GET[session]&amp;from_session_view=true'>";
        $polllink = $link . $title . '</a>';
        $imagelink = $link . "</a>" . icon('fa-question-circle') . "";
    }

    if (!empty($comments)) {
        $comment_box = "<br>$comments";
    } else {
        $comment_box = '';
    }
    return "
        <tr$class_vis data-id='$resource_id'>
          <td width='1'>$imagelink</td>
          <td>$polllink $res_prereq_icon $comment_box $assign_to_users_message $poll_results_link</td>
          <td class='text-start'></td>" .
            session_actions('poll', $resource_id, $visibility) . '
        </tr>';
}


/**
 * @brief display resource link
 * @param type $title
 * @param type $comments
 * @param type $resource_id
 * @param type $link_id
 * @param type $visibility
 * @return string
 */
function show_session_link($title, $comments, $resource_id, $link_id, $visibility) {

    global $is_consultant, $langWasDeleted, $course_id, $langOpenNewTab;

    $class_vis = ($visibility == 0) ? ' class="not_visible"' : ' ';
    $l = Database::get()->querySingle("SELECT * FROM link WHERE course_id = ?d AND id = ?d", $course_id, $link_id);
    if (!$l) { // check if it was deleted
        if (!$is_consultant) {
            return '';
        } else {
            $imagelink = icon('fa-xmark link-delete');
            $exlink = "<span class='not_visible'>" . q($title) . " ($langWasDeleted)</span>";
        }
    } else {
        if ($title == '') {
            $title = q($l->url);
        } else {
            $title = q($title);
        }
        $link = "<a href='" . q($l->url) . "' target='_blank' aria-label='$langOpenNewTab'>";
        $exlink = $link . "$title</a>";
        $imagelink = icon('fa-link');
    }

    if (!empty($comments)) {
        $comment_box = '<br />' . standard_text_escape($comments);
    } else {
        $comment_box = '';
    }

    return "
        <tr$class_vis data-id='$resource_id'>
          <td width='1'>$imagelink</td>
          <td>$exlink $comment_box</td>
          <td></td>" . session_actions('link', $resource_id, $visibility) . "
        </tr>";
}


/**
 * @brief display resource documents
 * @return string
 */
function show_session_doc($title, $comments, $resource_id, $file_id) {
    global $can_upload, $course_id, $langWasDeleted, $urlServer,
           $id, $course_code, $langResourceBelongsToSessionPrereq, $sessionID,
           $is_consultant, $uid, $is_course_admin;

    $can_upload = $can_upload || $is_consultant;

    $file = Database::get()->querySingle("SELECT * FROM document WHERE course_id = ?d AND id = ?d", $course_id, $file_id);
    // We get only the files which belong to user consultant or tutor in order to show them to simple users.
    $ids_simple_users = array();
    $idsNotConsultant = Database::get()->queryArray("SELECT user_id FROM course_user
                                                        WHERE course_id = ?d AND status = ?d AND tutor = ?d
                                                        AND editor = ?d AND course_reviewer = ?d AND reviewer = ?d
                                                        AND user_id IN (SELECT participants FROM mod_session_users
                                                                        WHERE session_id = ?d AND is_accepted = ?d)", $course_id, USER_STUDENT, 0, 0, 0, 0, $sessionID, 1);
    foreach($idsNotConsultant as $u){
        $ids_simple_users[] = $u->user_id;
    }
    if(in_array($file->lock_user_id,$ids_simple_users)){
        return;
    }

    $res_prereq_icon = '';
    if (!$file) {
        $download_hidden_link = '';
        if (!$can_upload) {
            return '';
        }
        $status = 'del';
        $image = 'fa-xmark link-delete';
        $link = "<span class='not_visible'>" . q($title) . " ($langWasDeleted)</span>";
    } else {
        if ($can_upload) {
            if (resource_belongs_to_session_completion($_GET['session'], $file->id)) {
                $res_prereq_icon = icon('fa-star', $langResourceBelongsToSessionPrereq);
            }
        }

        $status = $file->visible;
        if (!$can_upload and (!resource_access($file->visible, $file->public))) {
            return '';
        }
        if ($file->format == '.dir') {
            $image = 'fa-folder-open';
            $download_hidden_link = '';
            $link = "<a href='{$urlServer}modules/document/index.php?course=$course_code&amp;openDir=$file->path&amp;session=$_GET[session]'>" .
                q($title) . "</a>";
        } else {
            $image = choose_image('.' . $file->format);
            $download_hidden_link = '';
            $link = "<a class='link-color' href='{$urlServer}modules/session/resource_space.php?course=$course_code&session=$sessionID&resource_id=$resource_id&file_id=$file_id'>$title</a>";
        }
    }
    $class_vis = ($status == '0' or $status == 'del') ? ' class="not_visible"' : '';
    if (!empty($comments)) {
        $comment = '<br />' . $comments;
    } else {
        $comment = '';
    }

    return "
        <tr $class_vis data-id='$resource_id'>
          <td width='1'>" . icon($image, '') . "</td>
          <td class='text-start'>$download_hidden_link $link $res_prereq_icon $comment</td>
          <td>$file->creator</td>" .
          session_actions('doc', $resource_id, $status, $file->id) .
            "</tr>";
}

/**
 * @brief display resource documents
 * @return string
 */
function show_session_reference_doc($title, $comments, $resource_id, $file_id) {
    global $can_upload, $course_id, $langWasDeleted, $urlServer,
           $id, $course_code, $sessionID,
           $is_consultant, $uid, $langReference;

    $can_upload = $can_upload || $is_consultant;

    $file = Database::get()->querySingle("SELECT * FROM document WHERE course_id = ?d AND id = ?d", $course_id, $file_id);

    $res_prereq_icon = '';
    if (!$file) {
        $download_hidden_link = '';
        if (!$can_upload) {
            return '';
        }
        $status = 'del';
        $image = 'fa-xmark link-delete';
        $link = "<span class='not_visible'>" . q($title) . " ($langWasDeleted)</span>";
    } else {
        $status = $file->visible;
        if (!$can_upload and (!resource_access($file->visible, $file->public))) {
            return '';
        }
        if ($file->format == '.dir') {
            $image = 'fa-folder-open';
            $download_hidden_link = '';
            $link = "<a href='{$urlServer}modules/document/index.php?course=$course_code&amp;openDir=$file->path&amp;session=$_GET[session]'>" .
                q($title) . "</a>";
        } else {
            $image = choose_image('.' . $file->format);
            //$download_url = $_SERVER['SCRIPT_NAME'] . "?course=$course_code&amp;session=$sessionID&amp;download=" . getInDirectReference($file->path) . "&userID=" .$file->lock_user_id;
            $file_obj = MediaResourceFactory::initFromDocument($file);
            $file_obj->setAccessURL(session_file_reference_uploaded_url($file->path, $file->filename, $file->lock_user_id));
            $file_obj->setPlayURL(session_file_reference_playurl($file->path, $file->filename));
            $link = MultimediaHelper::chooseMediaAhref($file_obj);
            $download_hidden_link = '';
        }
    }
    $class_vis = ($status == '0' or $status == 'del') ? ' class="not_visible"' : '';
    if (!empty($comments)) {
        $comment = '<br />' . $comments;
    } else {
        $comment = '';
    }

    return "
        <tr $class_vis data-id='$resource_id'>
          <td width='1'>" . icon($image, '') . "</td>
          <td class='text-start'>$download_hidden_link $link ($langReference) $res_prereq_icon $comment</td>
          <td>$file->creator</td>" .
          session_actions('doc_reference', $resource_id, $status, $file->id) .
            "</tr>";
}


/**
 * @brief display resource assignment (aka work)
 * @param type $title
 * @param type $comments
 * @param type $resource_id
 * @param type $work_id
 * @param type $visibility
 * @return string
 */
function show_session_work($title, $comments, $resource_id, $work_id, $visibility) {

    global $urlServer, $is_consultant, $uid, $m, $langResourceBelongsToSessionPrereq,
            $langWasDeleted, $course_id, $course_code, $langPassCode, $langWorkToUser,
            $langWorkAssignTo, $langWorkToGroup;

    $title = q($title);
    $res_prereq_icon = '';
    if ($is_consultant) {
        $work = Database::get()->querySingle("SELECT * FROM assignment WHERE course_id = ?d AND id = ?d", $course_id, $work_id);
    } else {
        $gids = user_group_session_info($uid, $course_id);
        if (!empty($gids)) {
            $gids_sql_ready = implode(',',array_keys($gids));
        } else {
            $gids_sql_ready = "''";
        }
        $work = Database::get()->querySingle("SELECT * FROM assignment WHERE course_id = ?d AND id = ?d
                                 AND
                                (assign_to_specific = 0 OR id IN
                                    (SELECT assignment_id FROM assignment_to_specific WHERE user_id = ?d
                                        UNION
                                    SELECT assignment_id FROM assignment_to_specific WHERE group_id != 0 AND group_id IN ($gids_sql_ready))
                                )", $course_id, $work_id, $uid);
    }

    if (!$work) { // check if it was deleted
        if (!$is_consultant) {
            return '';
        } else {
            $imagelink = icon('fa-xmark link-delete');
            $exlink = "<span class='not_visible'>$title ($langWasDeleted)</span>";
        }
    } else {
        $assign_to_users_message = '';
        if ($is_consultant) {
            if ($work->assign_to_specific == 1) {
                $assign_to_users_message = "<small class='help-block'>$langWorkAssignTo: $langWorkToUser</small>";
            } else if ($work->assign_to_specific == 2) {
                $assign_to_users_message = "<small class='help-block'>$langWorkAssignTo: $langWorkToGroup</small>";
            }
            if (resource_belongs_to_session_completion($_GET['session'], $work_id)) {
                $res_prereq_icon = icon('fa-star', $langResourceBelongsToSessionPrereq);
            }
        }

        if ($work->password_lock) {
            $lock_description = "<ul>";
            $lock_description .= "<li>$langPassCode</li>";
            enable_password_session_bootbox();
            $class = 'class="password_protected"';
            $lock_description .= "</ul>";
            $exclamation_icon = "&nbsp;&nbsp;<span class='fa fa-exclamation-triangle space-after-icon' data-bs-toggle='tooltip' data-bs-placement='right' data-bs-html='true' data-bs-original-title='$lock_description'></span>";
        } else {
            $class = $exclamation_icon = '';
        }

        $link = "<a href='{$urlServer}modules/work/index.php?course=$course_code&amp;res_type=assignment&amp;id=$work_id&amp;session=$_GET[session]' $class>";
        $exlink = $link . "$title</a> $exclamation_icon";
        $imagelink = $link . "</a>".icon('fa-flask')."";
    }

    if (!empty($comments)) {
        $comment_box = "<br>$comments";
    } else {
        $comment_box = '';
    }
    return "
        <tr data-id='$resource_id'>
          <td width='1'>$imagelink</td>
          <td>$exlink $res_prereq_icon $comment_box $assign_to_users_message</td>
          <td></td>" .
            session_actions('lp', $resource_id, $visibility) . '
        </tr>';
}

/**
 * @brief display tc resources
 * @param $title
 * @param $comments
 * @param $resource_id
 * @param $tc_id
 * @param $visibility
 * @return string
 */
function show_session_tc($title, $comments, $resource_id, $tc_id, $visibility) {
    global  $is_consultant, $langWasDeleted, $langInactiveModule, $course_id,
            $urlServer, $course_code, $langTcNotStartedYet, $langHasExpired,
            $langInProgress, $langResourceBelongsToSessionPrereq, $langOpenNewTab;

    $module_visible = visible_module(MODULE_ID_TC); // checks module visibility

    if (!$module_visible and !$is_consultant) {
        return '';
    }
    $locked = $help_info = $has_expired = $comment_box = $langProgress = '';
    $tc = Database::get()->querySingle("SELECT * FROM tc_session WHERE course_id = ?d AND id = ?d", $course_id, $tc_id);
    if (!$tc) { // check if it was deleted
        if (!$is_consultant) {
            return '';
        } else {
            $imagelink = icon('fa-xmark link-delete');
            $tclink = "<span class='not_visible'>" .q($title) ." ($langWasDeleted)</span>";
        }
    } else {
        if (!$is_consultant and !$tc->active) {
            return '';
        }

        $res_prereq_icon = '';
        if ($is_consultant) {
            if (resource_belongs_to_session_completion($_GET['session'], $tc_id)) {
                $res_prereq_icon = icon('fa-star', $langResourceBelongsToSessionPrereq);
            }
        }

        $resourse_info = Database::get()->querySingle("SELECT title,comments FROM session_resources WHERE id = ?d",$resource_id);
        $new_title = $resourse_info->title;
        $new_meeting_id = $tc->meeting_id;
        $new_att_pw = $tc->att_pw;
        $unlock_interval = $tc->unlock_interval;
        $start_datetime = new DateTime(date('Y-m-d H:i:s'));
        $diff = $start_datetime->diff(new DateTime($tc->start_date));
        $total_minutes = ($diff->days * 24 * 60);
        $total_minutes += ($diff->h * 60);
        $total_minutes += $diff->i;
        if($tc->start_date > date('Y-m-d H:i:s')){
            if($total_minutes > $unlock_interval && $total_minutes > 0){
                $locked = 'pe-none opacity-help';
                $help_info = "&nbsp;<span class='TextBold'>($langTcNotStartedYet)</span>";
            }
        }
        if($tc->start_date < date('Y-m-d H:i:s') && $tc->end_date > date('Y-m-d H:i:s') and !is_null($tc->end_date)){
            $langProgress = "&nbsp;<span class='TextBold'>($langInProgress)</span>";
        }
        if($tc->end_date < date('Y-m-d H:i:s') and !is_null($tc->end_date)){
            $locked = 'opacity-help';
            $has_expired = "&nbsp;<span class='TextBold text-danger'>($langHasExpired)</span>";
        }
        if($is_consultant){
            $locked = '';
        }
        $bbblink = $urlServer . "modules/tc/index.php?course=$course_code&amp;choice=do_join&amp;meeting_id=$new_meeting_id&amp;title=" . urlencode($new_title) . "&amp;att_pw=$new_att_pw";
        if ($is_consultant) {
            $bbblink .= '&amp;mod_pw=' . urlencode($tc->mod_pw);
        }
        $tclink = "<a class='$locked' href='$bbblink' target='_blank' aria-label='$langOpenNewTab'>";
        if (!$module_visible) {
            $tclink .= " <i>($langInactiveModule)</i>&nbsp;";
        }
        $tclink .= "$new_title</a>";
        $imagelink = icon('fa-exchange');

        if (!empty($resourse_info->comments)) {
            $comment_box = "&nbsp;$resourse_info->comments";
        }
    }

    $class_vis = (!$tc->active or !$module_visible or !$visibility) ?
        ' class="not_visible"' : ' ';
    return "
        <tr $class_vis data-id='$resource_id'>
          <td width='1'>$imagelink</td>
          <td>$tclink $res_prereq_icon $comment_box $help_info $has_expired $langProgress</td>
          <td></td>" .
        session_actions('tc', $resource_id, $visibility) . '
        </tr>';
}


/**
 * @brief display passage resource
 * @param $title
 * @param $resource_id
 * @param $passage
 * @param $visibility
 * @return string
 */
function show_session_passage($title, $resource_id, $passage, $visibility){
    global  $is_consultant, $course_id, $urlServer, $course_code;

    $class_vis = ($visibility == 0) ? ' class="not_visible"' : ' ';

    $image = icon('fa-solid fa-keyboard');
    return "
        <tr $class_vis data-id='$resource_id'>
            <td width='1'>$image</td>
            <td>$passage</td>
            <td></td>" .
            session_actions('passage', $resource_id, $visibility) . '
        </tr>';
}


/**
 * @brief checks if a session resource belongs to session prerequisites
 * @param $session_id
 * @param $session_resource_id
 * @return boolean
 */
function resource_belongs_to_session_completion($session_id, $session_resource_id) {

    $q = Database::get()->querySingle("SELECT * FROM badge_criterion JOIN badge
                    ON badge.id = badge_criterion.badge
                    WHERE session_id = ?d
                        AND resource = ?d", $session_id, $session_resource_id);
    if ($q) {
        return true;
    } else {
        return false;
    }
}



/**
 * @brief resource actions
 * @param type $res_type
 * @param type $resource_id
 * @param type $status
 * @param type $res_id
 * @return string
 */
function session_actions($res_type, $resource_id, $status, $res_id = false) {
    global $is_consultant, $langEditChange, $langDelete, $uid,
    $langAddToCourseHome, $langConfirmDelete, $course_code,
    $langViewHide, $langViewShow, $langReorder, $langAlreadyBrowsed,
    $langNeverBrowsed, $langAddToUnitCompletion, $urlAppend, $langDownload,
    $sessionID, $is_course_reviewer, $is_simple_user, $urlServer, $langUsersAnswers,
    $langCommentsByConsultant, $langSubmissionOnBehalfOfUser;

    $res_types_sessions_completion = ['work', 'doc', 'poll', 'tc'];
    if (in_array($res_type, $res_types_sessions_completion)) {
        $res_type_to_session_compl = true;
    } else {
        $res_type_to_session_compl = false;
    }

    $downloadPath = "";
    $fromSystem = 0;
    if(($res_type == 'doc' or $res_type == 'doc_reference') && $res_id){
        $res = Database::get()->querySingle("SELECT path,subsystem,lock_user_id FROM document WHERE id = ?d",$res_id);
        $downloadPath = $res->path;
        $fromSystem = $res->subsystem;
    }

    if($fromSystem == MYSESSIONS){
        $download_url = $_SERVER['SCRIPT_NAME'] . "?course=$course_code&amp;session=$sessionID&amp;download=" . getIndirectReference($downloadPath);
    }elseif($fromSystem == SESSION_REFERENCE){
        $download_url = $_SERVER['SCRIPT_NAME'] . "?course=$course_code&amp;session=$sessionID&amp;downloadReference=true&amp;download=" . getIndirectReference($downloadPath);
    }else{
        $download_url = $urlAppend . "modules/document/index.php?course=$course_code&amp;download=" . getIndirectReference($downloadPath);
    }

    $content = "";

    if ($is_simple_user) {
        if (prereq_session_has_completion_enabled($_GET['session'])) {
            $activity_result = session_resource_completion($_GET['session'], $resource_id);
            switch ($activity_result) {
                case 1: $content = "<td class='text-end pe-3' style='padding: 10px 0; width: 85px;'>
                                        <span class='fa-solid fa-check fa-lg Success-200-cl' data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-original-title='$langAlreadyBrowsed'></span>
                                    </td>";
                    break;
                case 0:
                    $content = "<td class='text-end pe-3' style='padding: 10px 0; width: 85px;'>
                                    <span class='fa fa-hourglass-2' data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-original-title='$langNeverBrowsed'></span>
                                </td>";
                    break;
                default:
                    if($res_type == 'doc'){
                        $content = "<td class='text-end pe-1' style='padding: 10px 0; width: 85px;'>";
                                $content .= action_button(array(
                                    array('title' => $langDownload,
                                          'url' => "$download_url",
                                          'icon' => 'fa-download'),
                                    array('title' => $langDelete,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$_GET[session]&amp;del=$resource_id",
                                          'icon' => 'fa-xmark',
                                          'confirm' => $langConfirmDelete,
                                          'class' => 'delete',
                                          'show' => ($uid == $res->lock_user_id))
                                ));
                        $content .= "</td>";
                    }else{
                        $content = "<td></td>";
                    }

                    break;
            }
            return $content;
        } else {
            if($res_type == 'doc'){
                $content = "<td class='text-end pe-1' style='padding: 10px 0; width: 85px;'>";
                        $content .= action_button(array(
                            array('title' => $langDownload,
                                  'url' => "$download_url",
                                  'icon' => 'fa-download'),
                            array('title' => $langDelete,
                                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$_GET[session]&amp;del=$resource_id",
                                  'icon' => 'fa-xmark',
                                  'confirm' => $langConfirmDelete,
                                  'class' => 'delete',
                                  'show' => ($uid == $res->lock_user_id))
                        ));
                $content .= "</td>";
            }else{
                $content = "<td></td>";
            }
            return $content;
        }
    }

    if($is_consultant){

        if ($res_type == 'description') {
            $icon_vis = ($status == 1) ? 'fa-send' : 'fa-send-o';
            $edit_link = "edit.php?course=$course_code&amp;id=$_GET[session]&amp;numBloc=$res_id";
        } else {
            $showorhide = ($status == 1) ? $langViewHide : $langViewShow;
            $icon_vis = ($status == 1) ? 'fa-eye-slash' : 'fa-eye';
            if($res_type == 'passage'){
                $edit_link = "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$_GET[session]&amp;show_passage=$resource_id";
            }else{
                $edit_link = "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$_GET[session]&amp;editResource=$resource_id";
            }
        }

        $status_visible = Database::get()->querySingle("SELECT visible FROM session_resources WHERE id = ?d",$resource_id);
        if($status_visible->visible){
            $vis_res_val = 0;
            $showorhideIcon = "fa-solid fa-eye-slash";
            $showorhideMessage = "$langViewHide";
        }else{
            $vis_res_val = 1;
            $showorhideIcon = "fa-solid fa-eye";
            $showorhideMessage = "$langViewShow";
        }

        $poll_id = 0;
        $exist_answers = false;
        $poll = Database::get()->querySingle("SELECT res_id FROM session_resources WHERE id = ?d AND `type` = ?s",$resource_id,'poll');
        if($poll){
            $poll_id = $poll->res_id;
            $answers = Database::get()->queryArray("SELECT id FROM poll_user_record WHERE pid = ?d
                                                    AND uid IN (SELECT participants FROM mod_session_users
                                                                WHERE session_id = ?d
                                                                AND is_accepted = 1)
                                                    AND session_id = ?d",$poll_id, $_GET['session'], $_GET['session']);
            if (count($answers)>0){
                $exist_answers = true;
            }
        }

        $content = "<td>
                        <div class='d-flex justify-content-end align-items-center gap-3 w-100'>
                            <div class='reorder-btn d-flex justify-content-center align-items-center'>
                                <span class='fa fa-arrows' data-bs-toggle='tooltip' data-bs-placement='top' title='$langReorder'></span>
                            </div>";

                    $content .= action_button(array(
                                    array('title' => $langEditChange,
                                        'url' => $edit_link,
                                        'icon' => 'fa-edit',
                                        'show' => ($status != 'del' && $res_type != 'doc_reference')),
                                    array('title' => $langDownload,
                                        'url' => "$download_url",
                                        'icon' => 'fa-download',
                                        'show' => ($res_type == 'doc' or $res_type == 'doc_reference')),
                                    array('title' => $showorhideMessage,
                                        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;session=$_GET[session]&amp;res_id=$resource_id&amp;vis_res=$vis_res_val",
                                        'icon' => $showorhideIcon,
                                        'show' => ($res_type != 'doc_reference')),
                                    array('title' => $langSubmissionOnBehalfOfUser,
                                          'url' => $urlServer . "modules/questionnaire/pollparticipate.php?course=$course_code&amp;UseCase=1&amp;session=$_GET[session]&amp;pid=$poll_id&amp;res_type=questionnaire&amp;from_session_view=true&amp;onBehalfOfUser=true",
                                          'icon' => 'fa-solid fa-user-plus',
                                          'show' => ($res_type == 'poll')),
                                    array('title' => $langUsersAnswers,
                                        'url' => $urlServer . "modules/questionnaire/pollresults.php?course=$course_code&amp;session=$_GET[session]&amp;pid=$poll_id&amp;from_session_view=true",
                                        'icon' => 'fa-line-chart',
                                        'show' => ($status != 'del' && $res_type != 'doc_reference' && $poll_id > 0 && $exist_answers)),
                                    array('title' => $langCommentsByConsultant,
                                        'url' => $urlServer . "modules/session/poll_comments.php?course=$course_code&amp;session=$_GET[session]&amp;pid=$poll_id",
                                        'icon' => 'fa-comments',
                                        'show' => ($status != 'del' && $res_type != 'doc_reference' && $poll_id > 0)),
                                    array('title' => $langDelete,
                                        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$_GET[session]&amp;del=$resource_id",
                                        'icon' => 'fa-xmark',
                                        'confirm' => $langConfirmDelete,
                                        'class' => 'delete')
                                ));
        $content .= "   <div>
                    </td>";
    }

    // display consultant's comments and poll results to the course reviewer about a poll.
    if ($is_course_reviewer && !$is_consultant && $res_type == 'poll') {
        $status_vis = Database::get()->querySingle("SELECT visible FROM session_resources WHERE id = ?d",$resource_id);
        if($status_vis->visible){
            $poll_id = 0;
            $existAnswers = false;
            $poll = Database::get()->querySingle("SELECT res_id FROM session_resources WHERE id = ?d AND `type` = ?s",$resource_id,'poll');
            if($poll){
                $poll_id = $poll->res_id;
                $answers_res = Database::get()->queryArray("SELECT id FROM poll_user_record 
                                                            WHERE pid = ?d
                                                            AND uid IN (SELECT participants FROM mod_session_users
                                                                WHERE session_id = ?d
                                                                AND is_accepted = 1)
                                                            AND session_id = ?d", $poll_id, $_GET['session'], $_GET['session']);
                if (count($answers_res) > 0) {
                    $existAnswers = true;
                }
            }
            $content = "<td>
                            <div class='d-flex justify-content-end align-items-center gap-3 w-100'>";
                    $content .= action_button(array(
                                    array('title' => $langCommentsByConsultant,
                                        'url' => $urlServer . "modules/session/poll_comments.php?course=$course_code&amp;session=$_GET[session]&amp;pid=$poll_id",
                                        'icon' => 'fa-comments',
                                        'show' => ($status != 'del' && $res_type != 'doc_reference' && $poll_id > 0)),
                                    array('title' => $langUsersAnswers,
                                        'url' => $urlServer . "modules/questionnaire/pollresults.php?course=$course_code&amp;session=$_GET[session]&amp;pid=$poll_id&amp;from_session_view=true",
                                        'icon' => 'fa-line-chart',
                                        'show' => ($status != 'del' && $res_type != 'doc_reference' && $poll_id > 0 && $existAnswers))
                                ));
        $content .= "       <div>
                    </td>";
        }
    }

    $first = false;
    return $content;
}


/**
 * @brief Enable display of bootbox password dialog for assignments and
 *        exercises and warn about paused exercises
 */
function enable_password_session_bootbox() {
    global $head_content, $langCancel, $langSubmit,
        $langAssignmentPasswordModalTitle, $langExercisePasswordModalTitle,
        $langTheFieldIsRequired, $langTemporarySaveNotice2,
        $langContinueAttemptNotice, $langContinueAttempt;

    static $enabled = false;

    if ($enabled) {
        return;
    } else {
        $enabled = true;
        $head_content .= "
        <script>
            var lang = {
                assignmentPasswordModalTitle: '" . js_escape($langAssignmentPasswordModalTitle). "',
                exercisePasswordModalTitle: '" . js_escape($langExercisePasswordModalTitle). "',
                theFieldIsRequired: '" . js_escape($langTheFieldIsRequired). "',
                temporarySaveNotice: '" . js_escape($langTemporarySaveNotice2). "',
                continueAttemptNotice: '" . js_escape($langContinueAttemptNotice). "',
                continueAttempt: '" . js_escape($langContinueAttempt). "',
                cancel: '" . js_escape($langCancel). "',
                submit: '" . js_escape($langSubmit). "',
            };
            $(function () {
                $(document).on('click', '.ex_settings, .password_protected', unit_password_bootbox);
            });
        </script>";
    }
}

function check_activation_of_collaboration(){
    if(!get_config('show_collaboration')){
        redirect_to_home_page("main/portfolio.php");
    }
}

function session_exists($sid){
    global $course_code;
    $result = Database::get()->queryArray("SELECT * FROM mod_session WHERE id =?d",$sid);
    if(count($result) == 0){
        redirect_to_home_page("modules/session/index.php?course=".$course_code);
    }
}

function upload_session_empty_doc($sid){
    global $webDir, $course_code, $course_id, $langDeliverableInserted, $uid, $language, $langFile, $langTool;

    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

    $sdir = "$webDir/courses/$course_code/session/session_$sid/";
    if (!file_exists($sdir)) {
        mkdir("$webDir/courses/$course_code/session/session_$sid/", 0755, true);
    }

    $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM session_resources WHERE session_id = ?d", $sid)->maxorder;
    $order = $order+1;
    $title = q($_POST['title']);
    if(empty($title)){
        $title = "$langTool" . "_$sid";
    }
    $comments = strip_tags($_POST['comments']) ?? '';
    $textfile = fopen("$webDir/courses/$course_code/session/session_$sid/$title.txt", "w") or die("Unable to open file!");
    $txt = "$comments";
    fwrite($textfile, $txt);
    fclose($textfile);

    $file_creator = "$_SESSION[givenname] $_SESSION[surname]";
    $file_date = date('Y-m-d G:i:s');

    $safe_filename = safe_filename(get_file_extension("$title.txt"));
    $s_real_filename = "$title.txt";
    $sfilepath = '/' . $safe_filename;
    $ses_filename = add_ext_on_mime($s_real_filename);
    rename("$webDir/courses/$course_code/session/session_$sid/$title.txt","$webDir/courses/$course_code/session/session_$sid/$safe_filename");

    $upload_file = Database::get()->query("INSERT INTO document SET
        course_id = ?d,
        subsystem = ?d,
        subsystem_id = ?d,
        path = ?s,
        extra_path = '',
        filename = ?s,
        visible = 1,
        comment = ?s,
        category = 0,
        title = ?s,
        creator = ?s,
        date = ?s,
        date_modified = ?s,
        subject = '',
        description = '',
        author = ?s,
        format = ?s,
        language = ?s,
        copyrighted = 0,
        editable = 0,
        lock_user_id = ?d",
            $course_id, MYSESSIONS, $sid, $sfilepath,
            $s_real_filename, $comments, $title, $file_creator,
            $file_date, $file_date, $file_creator, get_file_extension($ses_filename),
            $language, $uid);


    Database::get()->query("INSERT INTO session_resources SET
                            session_id = ?d,
                            type = 'doc',
                            title = ?s,
                            comments = ?s,
                            visible = 1,
                            `order` = ?d,
                            `date` = " . DBHelper::timeAfter() . ",
                            res_id = ?d,
                            doc_id = ?d,
                            from_user = ?d", $sid, $title, $comments, $order, $upload_file->lastInsertID , 0, 0);

    Session::flash('message',$langDeliverableInserted);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/session/session_space.php?course=".$course_code."&session=".$sid);

}

function upload_session_doc($sid){
    global $webDir, $course_code, $course_id, $language, $uid,
            $langFormErrors, $langTheField , $langTitle, $langEmptyUploadFile,
            $langUploadDocCompleted, $sessionID, $langFileExists, $is_consultant,
            $langDoNotChooseResource, $langPreviousDocDeleted, $langFileExistsWithSameName,
            $langNotExistUsers, $langResourceNoExists, $langDoNotOverrideDeliverable;

    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

    if(!$is_consultant && isset($_POST['for_deliverable'])){
        if(!session_resource_exists($_POST['for_deliverable'],$sid)){
            Session::flash('message',$langResourceNoExists);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page("modules/session/session_space.php?course=$course_code&session=$sid");
        }else{
            if(isset($_POST['for_file']) && isset($_POST['fromUser'])){
                $is_completed_deliverable = database::get()->querySingle("SELECT is_completed FROM session_resources
                                                                        WHERE session_id = ?d
                                                                        AND doc_id = ?d
                                                                        AND from_user = ?d", $sid, $_POST['for_file'], $_POST['fromUser']);
                if($is_completed_deliverable->is_completed){
                    Session::flash('message',$langDoNotOverrideDeliverable);
                    Session::flash('alert-class', 'alert-danger');
                    redirect_to_home_page("modules/session/resource_space.php?course=".$course_code."&session=".$sid."&resource_id=".$_POST['for_deliverable']."&file_id=".$_POST['for_file']);
                }
            }
        };
    }

    if($is_consultant && !isset($_POST['fromUser']) && isset($_POST['onBehalfOfUserID'])){
        Session::flash('message',$langNotExistUsers);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page("modules/session/resource_space.php?course=".$course_code."&session=".$sid."&resource_id=".$_POST['for_deliverable']."&file_id=".$_POST['for_file']."&upload_for_user=true");
    }

    if($_FILES['file-upload']['error'] > 0) {
        // cover_image is empty (and not an error), or no file was uploaded
        Session::flash('message',$langEmptyUploadFile);
        Session::flash('alert-class', 'alert-danger');
        if(isset($_POST['for_deliverable']) and isset($_POST['for_file'])){
            redirect_to_home_page("modules/session/resource_space.php?course=".$course_code."&session=".$sid."&resource_id=".$_POST['for_deliverable']."&file_id=".$_POST['for_file']);
        }else{
            redirect_to_home_page("modules/session/resource.php?course=".$course_code."&session=".$sid."&type=doc_upload");
        }
    }

    if(!$is_consultant && !isset($_POST['refers_to_resource']) && isset($_POST['fromUser']) > 0){
        Session::flash('message',$langDoNotChooseResource);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page("modules/session/resource_space.php?course=".$course_code."&session=".$sid."&resource_id=".$_POST['for_deliverable']."&file_id=".$_POST['for_file']);
    }

    // If uploaded file exists do not continue.
    // This uploaded file does not refer to deliverable but refers to resource of a session.
    if($is_consultant && !isset($_POST['for_deliverable']) && !isset($_POST['for_file'])){
        $path_exists = Database::get()->queryArray("SELECT * FROM document
                                                    WHERE course_id = ?d
                                                    AND subsystem = ?d
                                                    AND subsystem_id = ?d
                                                    AND filename = ?s",
                                                    $course_id, MYSESSIONS, $sid, $_FILES['file-upload']['name']);

        if (count($path_exists) > 0) {
            Session::flash('message',$langFileExists);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page("modules/session/resource.php?course=".$course_code."&session=".$sid."&type=doc_upload");
        }
    }


    // upload attached file
    if (isset($_FILES['file-upload']) and is_uploaded_file($_FILES['file-upload']['tmp_name'])) { // upload comments file
        $session_filename = $_FILES['file-upload']['name'];
        validateUploadedFile($session_filename); // check file type
        $session_filename = add_ext_on_mime($session_filename);
        // File name used in file system and path field
        $safe_session_filename = safe_filename(get_file_extension($session_filename));
        if($is_consultant && !isset($_POST['onBehalfOfUserID'])){
            $session_dir = "$webDir/courses/$course_code/session/session_$sid/";
        }elseif($is_consultant && isset($_POST['onBehalfOfUserID'])){
            $theUSER = $_POST['fromUser'] ?? 0;
            $session_dir = "$webDir/courses/$course_code/session/session_$sid/$theUSER/";
        }else{// personal files from simple user
            $session_dir = "$webDir/courses/$course_code/session/session_$sid/$uid/";
        }

        if (!file_exists($session_dir)) {
            if($is_consultant && !isset($_POST['onBehalfOfUserID'])){
                mkdir("$webDir/courses/$course_code/session/session_$sid/", 0755, true);
            }elseif($is_consultant && isset($_POST['onBehalfOfUserID'])){
                $theUSER = $_POST['fromUser'] ?? 0;
                mkdir("$webDir/courses/$course_code/session/session_$sid/$theUSER/", 0755, true);
            }else{
                mkdir("$webDir/courses/$course_code/session/session_$sid/$uid/", 0755, true);
            }

        }
        if($is_consultant && !isset($_POST['onBehalfOfUserID'])){
            $spathfile = "$webDir/courses/$course_code/session/session_$sid/$safe_session_filename";
        }elseif($is_consultant && isset($_POST['onBehalfOfUserID'])){
            $theUSER = $_POST['fromUser'] ?? 0;
            $spathfile = "$webDir/courses/$course_code/session/session_$sid/$theUSER/$safe_session_filename";
        }else{
            $spathfile = "$webDir/courses/$course_code/session/session_$sid/$uid/$safe_session_filename";
        }

        if (move_uploaded_file($_FILES['file-upload']['tmp_name'], $spathfile)) {
            @chmod($spathfile, 0644);
            $session_real_filename = $_FILES['file-upload']['name'];
            $session_filepath = '/' . $safe_session_filename;
        }

        $file_creator = "$_SESSION[givenname] $_SESSION[surname]";
        if($is_consultant && isset($_POST['onBehalfOfUserID'])){
            if(isset($_POST['fromUser']) && $_POST['fromUser'] > 0){
                $file_creator = participant_name($_POST['fromUser']);
            }
        }

        $file_date = date('Y-m-d G:i:s');

        $info_file = pathinfo($session_filename);
        $title = !empty($_POST['title']) ? q($_POST['title']) : $info_file['filename'];
        $comments = !empty($_POST['comments']) ? purify($_POST['comments']) : null;
        $User_ID = $uid;
        if($is_consultant && isset($_POST['onBehalfOfUserID'])){
            $User_ID = $_POST['fromUser'] ?? 0;
        }

        $doc_inserted = Database::get()->query("INSERT INTO document SET
            course_id = ?d,
            subsystem = ?d,
            subsystem_id = ?d,
            path = ?s,
            extra_path = '',
            filename = ?s,
            visible = 1,
            comment = ?s,
            category = 0,
            title = ?s,
            creator = ?s,
            date = ?s,
            date_modified = ?s,
            subject = '',
            description = '',
            author = ?s,
            format = ?s,
            language = ?s,
            copyrighted = 0,
            editable = 0,
            lock_user_id = ?d",
                $course_id, MYSESSIONS, $sid, $session_filepath,
                $session_real_filename, $comments, $title, $file_creator,
                $file_date, $file_date, $file_creator, get_file_extension($session_filename),
                $language, $User_ID);

        $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM session_resources WHERE session_id = ?d", $sid)->maxorder;
        $order = $order+1;

        // doc_id and uUserId refer to resources for session completion
        $doc_id = isset($_POST['refers_to_resource']) ? $_POST['refers_to_resource'] : 0;
        $uUserId = isset($_POST['fromUser']) ? $_POST['fromUser'] : 0;
        // if exists then update uploaded file else upload it for simple user.
        $checkExist = Database::get()->querySingle("SELECT * FROM session_resources
                                                        WHERE session_id = ?d
                                                        AND type = ?s
                                                        AND doc_id = ?d
                                                        AND from_user = ?d
                                                        AND doc_id <> ?d
                                                        AND from_user <> ?d", $sid, 'doc', $doc_id, $uUserId, 0, 0);

        if($checkExist){
            $checker = 1;
            $q = Database::get()->query("UPDATE session_resources SET
                                        session_id = ?d,
                                        type = 'doc',
                                        title = ?s,
                                        comments = ?s,
                                        visible = 1,
                                        `order` = ?d,
                                        `date` = " . DBHelper::timeAfter() . ",
                                        res_id = ?d,
                                        doc_id = ?d,
                                        from_user = ?d,
                                        is_completed = ?d
                                        WHERE doc_id = ?d
                                        AND from_user = ?d
                                        AND session_id = ?d", $sid, $title, $comments, $order, $doc_inserted->lastInsertID, $doc_id, $uUserId, 0, $doc_id, $uUserId, $sid);

            // Delete criterion for the user for whom the deliverable has deleted.
            Database::get()->query("DELETE FROM user_badge_criterion
                                    WHERE user = ?d
                                    AND badge_criterion IN
                                    (SELECT id FROM badge_criterion WHERE activity_type = ?s AND resource = ?d
                                    AND badge IN (SELECT id FROM badge WHERE course_id = ?d AND session_id = ?d))",$uUserId,'document-submit',$doc_id,$course_id,$sid);

            // Now we must delete the resource file from document table in db.
            if($q){
                $target_userdir = "$webDir/courses/$course_code/session/session_$sid/$uUserId/";
                $oldFilePath = Database::get()->querySingle("SELECT path FROM document
                                                                WHERE id = ?d
                                                                AND course_id = ?d
                                                                AND subsystem = ?d
                                                                AND subsystem_id = ?d
                                                                AND lock_user_id = ?d",$checkExist->res_id, $course_id, MYSESSIONS, $sid, $uUserId);
                if($oldFilePath){
                    unlink($target_userdir.$oldFilePath->path);
                }

                Database::get()->query("DELETE FROM document
                                        WHERE id = ?d
                                        AND course_id = ?d
                                        AND subsystem = ?d
                                        AND subsystem_id = ?d
                                        AND lock_user_id = ?d",$checkExist->res_id, $course_id, MYSESSIONS, $sid, $uUserId);
            }
        }else{
            $checker = 0;
            $q = Database::get()->query("INSERT INTO session_resources SET
                                            session_id = ?d,
                                            type = 'doc',
                                            title = ?s,
                                            comments = ?s,
                                            visible = 1,
                                            `order` = ?d,
                                            `date` = " . DBHelper::timeAfter() . ",
                                            res_id = ?d,
                                            doc_id = ?d,
                                            from_user = ?d", $sid, $title, $comments, $order, $doc_inserted->lastInsertID, $doc_id, $uUserId);
        }

        $msg = ($checker == 1) ? "$langUploadDocCompleted" . "</br>" . "$langPreviousDocDeleted" : "$langUploadDocCompleted";
        Session::flash('message',$msg);
        Session::flash('alert-class', 'alert-success');

        // for uploading of deliverable
        if(isset($_POST['for_deliverable']) and isset($_POST['for_file'])){
            if(!$is_consultant && !isset($_POST['onBehalfOfUserID'])){
                informConsultantAboutDeliverable($sid,$_POST['fromUser'],$_POST['for_deliverable']);
            }
            redirect_to_home_page("modules/session/resource_space.php?course=".$course_code."&session=".$sid."&resource_id=".$_POST['for_deliverable']."&file_id=".$_POST['for_file']);
        }else{ // for not deliverable
            redirect_to_home_page("modules/session/session_space.php?course=".$course_code."&session=".$sid);
        }


    }

}

/**
 * @return string
 */
function localhostUrl() {
    return sprintf(
        "%s://%s",
        isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
        $_SERVER['SERVER_NAME']
    );
}

/**
 * @brief check if we have created unit completion badge
 * @return boolean
 * @global type $course_id
 * @param int $session_id
 */
function is_session_completion_enabled($session_id) {
    global $course_id;

    $sql = Database::get()->querySingle("SELECT id FROM badge WHERE course_id = ?d AND session_id = ?d
                                                    AND bundle = -1", $course_id, $session_id);

    if ($sql) {
        return $sql->id;
    } else {
        return 0;
    }
}

/**
 * @brief display badge / certificate settings
 * @param type $element
 * @param type $element_id
 */
function display_session_settings($element, $element_id, $session_id = 0): void
{

    global $tool_content, $course_id, $course_code, $urlServer, $langTitle,
           $langDescription, $langMessage, $langProgressBasicInfo, $langCourseCompletion,
           $langpublisher, $langEditChange, $is_consultant;

    $field = ($element == 'certificate') ? 'template' : 'icon';

    $data = Database::get()->querySingle("SELECT issuer, $field, title, description, message, active, bundle
                            FROM $element WHERE id = ?d AND course_id = ?d AND session_id = ?d", $element_id, $course_id, $session_id);

    $bundle = $data->bundle;
    $issuer = $data->issuer;
    $title = $data->title;
    $description = $data->description;
    $message = $data->message;

    if ($bundle != -1) {
        if ($element == 'badge') {
            $badge_details = get_badge_icon($data->icon);
            $badge_name = key($badge_details);
            $badge_icon = $badge_details[$badge_name];
            $icon_link = $urlServer . BADGE_TEMPLATE_PATH . "$badge_icon";
        } else {
            $template_details = get_certificate_template($data->template);
            $template_name = key($template_details);
            $template_filename = $template_details[$template_name];
            $thumbnail_filename = preg_replace('/.html/', '_thumbnail.png', $template_filename);
            $icon_link = $urlServer . CERT_TEMPLATE_PATH . $thumbnail_filename;
        }
        $tool_content .= "
                <div class='col-12'>
                    <div class='card panelCard border-card-left-default px-lg-4 py-lg-3'>
                        <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                <h3>
                                    $langProgressBasicInfo
                                </h3>";
                            if ($is_consultant) {
                                $tool_content .= "<div><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;{$element}_id=$element_id&amp;edit=1&amp;session={$session_id}' class='btn submitAdminBtn gap-2'>"
                                            . "<span class='fa fa-pencil'></span><span class='hidden-xs'>$langEditChange</span>
                                            </a>
                                        </div>";
                            }
                        $tool_content .= "</div>
                        <div class='card-body'>
                            <div class='d-flex justify-content-md-start justify-content-center align-items-start flex-wrap gap-5'>
                                <div>
                                    <img class='img-responsive center-block m-auto d-block' src='$icon_link'>
                                </div>
                                <div class='flex-grow-1'>
                                    <ul class='list-group list-group-flush'>
                                        <li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                <div class='col-md-3 col-12'>
                                                    <div class='pn-info-title-sct title-default'>$langTitle</div>
                                                </div>
                                                <div class='col-md-9 col-12 title-default-line-height'>
                                                    <div class='pn-info-text-sct'>$title</div>
                                                </div>
                                            </div>
                                        </li>

                                        <li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                <div class='col-md-3 col-12'>
                                                    <div class='pn-info-title-sct title-default'>$langDescription</div>
                                                </div>
                                                <div class='col-md-9 col-12 title-default-line-height'>
                                                    <div class='pn-info-text-sct'>$description</div>
                                                </div>
                                            </div>
                                        </li>

                                        <li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                <div class='col-md-3 col-12'>
                                                    <div class='pn-info-title-sct title-default'>$langMessage</div>
                                                </div>
                                                <div class='col-md-9 col-12 title-default-line-height'>
                                                    <div class='pn-info-text-sct text-start'>$message</div>
                                                </div>
                                            </div>
                                        </li>

                                        <li class='list-group-item element'>
                                            <div class='row row-cols-1 row-cols-md-2 g-1'>
                                                <div class='col-md-3 col-12'>
                                                    <div class='pn-info-title-sct title-default'>$langpublisher</div>
                                                </div>
                                                <div class='col-md-9 col-12 title-default-line-height'>
                                                    <div class='pn-info-text-sct text-start'>$issuer</div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>";
    } else { // course completion
        if (!$session_id) {
            $tool_content .= "
            <div class='col-12'>
                <div class='card panelCard card-default px-lg-4 py-lg-3'>
                    <div class='card-body'>

                        <h3 class='mb-0 text-center'>$langCourseCompletion</h3>

                    </div>
                </div>
            </div>";
        }
    }
}

/**
 * @brief display all certificate activities
 * @param type $element
 * @param type $certificate_id
 */
function display_session_activities($element, $id, $session_id = 0) {

    global $tool_content, $course_code, $is_consultant,
           $langNoActivCert, $langAttendanceActList, $langTitle, $langType,
           $langOfAssignment, $langOfPoll, $langConfirmDelete, $langDelete, $langEditChange,
           $langDocumentAsModuleLabel, $langCourseParticipation,
           $langAdd, $langBack, $langUsers,
           $langValue, $langOfCourseCompletion, $langOfUnitCompletion,
           $course_id, $langUnitCompletion, $langSessionPrerequisites, $langNewUnitPrerequisite,
           $langNoSessionPrerequisite, $langSessionCompletion, $langWithoutCompletedResource,
           $langCompletedSession, $langNotCompletedSession, $langSubmit, $langCancel,
           $langContinueToCompletetionWithoutAct, $langOfSubmitAssignment, $langOfSubmitDocument,
           $langWithSubmittedUploadedFile, $langWithTCComplited,
           $langContinueToCompletetionWithCompletedTC, $langAddCompletionCriteria,
           $langWithMeetingCompletion, $langContinueToCompletetionWithMeeting,
           $langWithAttendanceRegistrationByConsultant, $langSettingSelect, $infoPrereqSession, 
           $langOfSubmitQuestionnaire, $langAutomaticCompletion, $langCompleteCriteriaSession, 
           $langSelect, $langCriteriaBelowParticipateInCompletion, $langCompletedSessionWithMeeting,
           $langListCompletionCriteria, $langUserParticipation;

    if ($session_id) {
        $link_id = "course=$course_code&amp;manage=1&amp;session=$session_id&amp;badge_id=$id";
    } else {
        if ($element == 'certificate') {
            $link_id = "course=$course_code&amp;certificate_id=$id&amp;session=$session_id";
        } else {
            $link_id = "course=$course_code&amp;badge_id=$id&amp;session=$session_id";
        }
    }

    $tool_content .= action_bar(
            array(
                array('title' => $langBack,
                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;session=$session_id",
                    'icon' => 'fa-reply',
                    'level' => 'primary',
                    'show'  =>  $session_id ? false : true),
                array('title' => $langUsers,
                    'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;progressall=true",
                    'icon' => 'fa-users',
                    'level' => 'primary-label',
                    'show'  =>  $session_id ? false : true)
            ),
            false
        );

    $active_completion_without_resource = 'opacity-help pe-none';
    $activity_off = 'opacity-help pe-none';
    $is_remote_session = 0;
    if ($session_id) {
        // Check the time that session starts
        $started = Database::get()->querySingle("SELECT start,type_remote FROM mod_session WHERE id = ?d AND course_id = ?d",$session_id,$course_id);
        if($started && (date('Y-m-d H:i:s') >= $started->start)){
            $active_completion_without_resource = '';
        }
        $is_remote_session = $started->type_remote;

        // check if session completion is enabled
        $cc_enable = Database::get()->querySingle("SELECT count(id) as active FROM badge
                                                            WHERE course_id = ?d AND session_id = ?d
                                                            AND bundle = -1", $course_id, $session_id)->active;

        // check if current element is session completion badge
        $cc_is_current = false;
        if ($element == 'badge') {
            $bundle = Database::get()->querySingle("select bundle from badge where id = ?d", $id)->bundle;
            if ($bundle && $bundle == -1) {
                $cc_is_current = true;
            }
        }

        // check if current session is completed by all users
        $is_session_completed = false;
        $is_session_completed_message = "";
        $sql_badge = Database::get()->querySingle("SELECT id FROM badge WHERE course_id = ?d AND session_id = ?d", $course_id, $session_id);
        if ($sql_badge) {
            $per = 0;
            $badge_id = $sql_badge->id;
            $participants = Database::get()->queryArray("SELECT participants FROM mod_session_users WHERE session_id = ?d AND is_accepted = ?d",$session_id,1);
            if(count($participants) > 0){
                foreach($participants as $p){
                    $per = $per + get_cert_percentage_completion_by_user('badge',$badge_id,$p->participants);
                }
            }
            if( count($participants) > 0 && $per/count($participants) == 100 ){
                 $is_session_completed = true;
                 $is_session_completed_message .= "<span class='badge Success-200-bg small-text'>$langCompletedSession</span>";
            }else{
                $is_session_completed_message .= "<span class='badge Accent-200-bg small-text'>$langNotCompletedSession</span>";
            }

            $checkActivityType = Database::get()->querySingle("SELECT activity_type FROM badge_criterion WHERE badge = ?d",$badge_id);
            if($checkActivityType && ($checkActivityType->activity_type == 'document' or
                $checkActivityType->activity_type == 'assignment-submit' or
                $checkActivityType->activity_type == 'document-submit' or
                $checkActivityType->activity_type == 'meeting-completed' or
                $checkActivityType->activity_type == 'consultant-completion' or
                $checkActivityType->activity_type == 'questionnaire' or
                $checkActivityType->activity_type == 'autocomplete' or
                $checkActivityType->activity_type == 'tc-completed')){
                    $activity_off = '';
                    $active_completion_without_resource = 'opacity-help pe-none';
            }elseif($checkActivityType && $checkActivityType->activity_type == 'noactivity'){
                    $activity_off = 'opacity-help pe-none';
                    $active_completion_without_resource = '';
                    if($started && (date('Y-m-d H:i:s') < $started->start)){
                        $active_completion_without_resource = 'opacity-help pe-none';
                    }
            }elseif(!$checkActivityType){
                    $activity_off = '';
                    $active_completion_without_resource = '';
                    if($started && (date('Y-m-d H:i:s') < $started->start)){
                        $active_completion_without_resource = 'opacity-help pe-none';
                    }
            }

        }

    } else {
        redirect_to_home_page("courses/$course_code/");
    }

    // certificate details
    $tool_content .= display_session_settings($element, $id, $session_id);
    $addActivityBtn = action_button(array(
            // array('title' => $langWithoutCompletedResource,
            //     'url' => "#",
            //     'icon-class' => $active_completion_without_resource,
            //     'icon-extra' => "data-id='{$session_id}' data-bs-toggle='modal' data-bs-target='#CompletionWithoutActivities{$session_id}'",
            //     'icon' => 'fa fa-trophy'
            // ),
            array('title' => $langAutomaticCompletion,
                'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=autocomplete",
                'icon' => 'fa-solid fa-clock',
                'icon-class' => $activity_off
            ),
            array('title' => $langWithSubmittedUploadedFile,
                'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=submitFile",
                'icon' => 'fa-upload',
                'icon-class' => $activity_off
            ),
            array('title' => $langOfSubmitQuestionnaire,
                'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=poll",
                'icon' => 'fa fa-flask space-after-icon',
                'icon-class' => $activity_off
            ),
            array('title' => $langWithMeetingCompletion,
                'url' => "#",
                'icon-extra' => "data-id='{$session_id}' data-bs-toggle='modal' data-bs-target='#WithCompletedLiveMeeting{$session_id}'",
                'icon' => 'fa-solid fa-users-rectangle',
                'icon-class' => $activity_off,
                'show' => !$is_remote_session
            ),
            array('title' => $langWithTCComplited,
                'url' => "#",
                'icon-extra' => "data-id='{$session_id}' data-bs-toggle='modal' data-bs-target='#WithCompletedTc{$session_id}'",
                'icon' => 'fa-solid fa-users-rectangle',
                'icon-class' => $activity_off,
                'show' => $is_remote_session
            ),
            array('title' => $langWithAttendanceRegistrationByConsultant,
                'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=withCompletionByConsultant",
                'icon' => 'fa-solid fa-users',
                'icon-class' => $activity_off
            ),
        ),
        array(
            'secondary_title' => $langListCompletionCriteria,
            'secondary_icon' => 'fa-plus',
            'secondary_btn_class' => 'submitAdminBtn'
        ));

    //get available activities
    $result = Database::get()->queryArray("SELECT * FROM {$element}_criterion WHERE $element = ?d ORDER BY `id` DESC", $id);

    if($session_id) {
            $tool_content .= "<div class='main-content'>
                                <div class='col-12'>
                                    <div class='card panelCard card-default px-lg-4 py-lg-3'>
                                        <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                            <h3>
                                                $langSessionCompletion </br>
                                            </h3>
                                            <div>
                                                $addActivityBtn
                                            </div>
                                        </div>
                                        <div class='panel-body'>";

                                            /////////////////////////////////////////////////////
                                            $all_resources = Database::get()->queryArray("SELECT * FROM session_resources 
                                                                                          WHERE session_id = ?d AND doc_id = ?d AND from_user = ?d
                                                                                          AND (type = ?s OR type = ?s OR type = ?s)", $session_id, 0, 0, 'poll', 'doc', 'tc');
                                            if ($badge_id) {
                                                $tool_content .= "<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;session=$session_id'>";
                                                $tool_content .= "<table class='table-default'>
                                                                    <thead>
                                                                        <tr>
                                                                            <th>$langCompleteCriteriaSession</th>
                                                                            <th class='text-center'>$langUserParticipation</th>
                                                                            <th class='text-end'>$langSelect</th>
                                                                        </tr>
                                                                    </thead>";
                                                $nameAttr = '';
                                                $sqlResourse = '';
                                                if (count($all_resources) > 0) {
                                                    foreach ($all_resources as $r) {
                                                        $badge_criterion = Database::get()->querySingle("SELECT id FROM badge_criterion 
                                                                                                         WHERE badge = $badge_id
                                                                                                         AND resource = ?d
                                                                                                         $sqlResourse", $r->res_id);
                                                        $iconCompletion = "<span class='badge Accent-200-bg py-2 px-2'><i class='fa-solid fa-circle-xmark fa-lg'></i></span>";
                                                        if ($badge_criterion) {
                                                            $u_check = Database::get()->querySingle("SELECT user FROM user_badge_criterion WHERE badge_criterion = ?d", $badge_criterion->id);
                                                            if ($u_check) {
                                                                $iconCompletion = "<span class='badge Success-200-bg py-2 px-2'><i class='fa-solid fa-circle-check fa-lg'></i></span>";
                                                            }
                                                        }
                                                        $selected = '';
                                                        $selected = Database::get()->querySingle("SELECT id FROM badge_criterion WHERE badge = ?d 
                                                                                                        AND activity_type = ?d AND resource = ?d", $badge_id, $r->type, $r->res_id);
                                                        $tool_content .= "<tr>
                                                                            <td>$r->title</td>
                                                                            <td class='text-center'>$iconCompletion</td>
                                                                            <td>
                                                                                <div class='d-flex justify-content-end'>
                                                                                    <div class='checkbox'>
                                                                                        <label class='label-container' aria-label='$langSelect'>
                                                                                            <input type='checkbox' name='add_resources[]' value='$r->res_id".' '."$r->type' ". ($selected ? 'checked' : '') .">
                                                                                            <span class='checkmark'></span>
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                        </tr>";
                                                    }
                                                }

                                                $selectedItem1 = Database::get()->querySingle("SELECT id FROM badge_criterion WHERE badge = ?d AND activity_type = ?s", $badge_id, 'consultant-completion');
                                                $selectedItem2 = Database::get()->querySingle("SELECT id FROM badge_criterion WHERE badge = ?d AND activity_type = ?s", $badge_id, 'autocomplete');
                                                $iconCompletion1 = "<span class='badge Accent-200-bg py-2 px-2'><i class='fa-solid fa-circle-xmark fa-lg'></i></span>";
                                                $iconCompletion2 = "<span class='badge Accent-200-bg py-2 px-2'><i class='fa-solid fa-circle-xmark fa-lg'></i></span>";
                                                if ($selectedItem1) {
                                                    $u_check = Database::get()->querySingle("SELECT user FROM user_badge_criterion WHERE badge_criterion = ?d", $selectedItem1->id);
                                                    if ($u_check) {
                                                        $iconCompletion1 = "<span class='badge Success-200-bg py-2 px-2'><i class='fa-solid fa-circle-check fa-lg'></i></span>";
                                                    }
                                                }
                                                if ($selectedItem2) {
                                                    $u_check = Database::get()->querySingle("SELECT user FROM user_badge_criterion WHERE badge_criterion = ?d", $selectedItem2->id);
                                                    if ($u_check) {
                                                        $iconCompletion2 = "<span class='badge Success-200-bg py-2 px-2'><i class='fa-solid fa-circle-check fa-lg'></i></span>";
                                                    }
                                                }
                                                $tool_content .= "  <tr>
                                                                        <td>$langWithAttendanceRegistrationByConsultant</td>
                                                                        <td><div class='d-flex justify-content-center'>$iconCompletion1</div></td>
                                                                        <td>
                                                                            <div class='d-flex justify-content-end'>
                                                                                <div class='checkbox'>
                                                                                    <label class='label-container' aria-label='$langSelect'>
                                                                                        <input type='checkbox' name='add_resources[]' value='0".' '."consultant-completion' ". ($selectedItem1 ? 'checked' : '') .">
                                                                                        <span class='checkmark'></span>
                                                                                    </label>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                    </tr>";

                                                $tool_content .= "  <tr>
                                                                        <td>$langAutomaticCompletion</td>
                                                                        <td><div class='d-flex justify-content-center'>$iconCompletion2</div></td>
                                                                        <td>
                                                                            <div class='d-flex justify-content-end'>
                                                                                <div class='checkbox'>
                                                                                    <label class='label-container' aria-label='$langSelect'>
                                                                                        <input type='checkbox' name='add_resources[]' value='0".' '."autocomplete' ". ($selectedItem2 ? 'checked' : '') .">
                                                                                        <span class='checkmark'></span>
                                                                                    </label>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                    </tr>";

                                                $typeRemoteSession = Database::get()->querySingle("SELECT type_remote FROM mod_session WHERE id = ?d", $session_id);
                                                if (!$typeRemoteSession->type_remote) {
                                                    $iconCompletion3 = "<span class='badge Accent-200-bg py-2 px-2'><i class='fa-solid fa-circle-xmark fa-lg'></i></span>";
                                                    $selectedItem3 = Database::get()->querySingle("SELECT id FROM badge_criterion WHERE badge = ?d AND activity_type = ?s", $badge_id, 'meeting-completed');
                                                    if ($selectedItem3) {
                                                        $u_check = Database::get()->querySingle("SELECT user FROM user_badge_criterion WHERE badge_criterion = ?d", $selectedItem3->id);
                                                        if ($u_check) {
                                                            $iconCompletion3 = "<span class='badge Success-200-bg py-2 px-2'><i class='fa-solid fa-circle-check fa-lg'></i></span>";
                                                        }
                                                    }
                                                    $tool_content .= "  <tr>
                                                                            <td>$langCompletedSessionWithMeeting</td>
                                                                            <td><div class='d-flex justify-content-center'>$iconCompletion3</div></td>
                                                                            <td>
                                                                                <div class='d-flex justify-content-end'>
                                                                                    <div class='checkbox'>
                                                                                        <label class='label-container' aria-label='$langSelect'>
                                                                                            <input type='checkbox' name='add_resources[]' value='0".' '."meeting-completed' ". ($selectedItem3 ? 'checked' : '') .">
                                                                                            <span class='checkmark'></span>
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                        </tr>";
                                                }
                                                
                                                $tool_content .= "</table>
                                                <input type='hidden' name='badgeId' value='$badge_id'>
                                                " . generate_csrf_token_form_field() . "
                                                <div class='col-12 d-flex justify-content-end mt-3'><input type='submit' class='btn submitAdminBtn' name='submitCriteria' value='$langAddCompletionCriteria'></div>
                                                </form>";
                                            }

                                            ////////////////////////////////////////////////////

                                            if (count($result) == 0) {
                                                $tool_content .= "<p class='margin-top-fat text-center text-muted mb-3'>$langNoActivCert</p>";
                                            } else {

                                                $tool_content .= "  <div class='col-12 mt-5'>
                                                                        <h3>$langCriteriaBelowParticipateInCompletion</h3>
                                                                    </div>
                                                                    <div class='res-table-wrapper'>
                                                                        <div class='table-responsive'>
                                                                            <table class='table-default'><thead>
                                                                                <tr class='list-header'>
                                                                                    <th>
                                                                                        $langTitle
                                                                                    </th>
                                                                                    <th>
                                                                                        $langType
                                                                                    </th>
                                                                                    <th aria-label='$langSettingSelect'>
                                                                                        <i class='fa fa-cogs'></i>
                                                                                    </th>
                                                                                </tr></thead>";
                                                                                foreach ($result as $details) {
                                                                                    $resource_data = get_resource_details($element, $details->id, $session_id);
                                                                                    $tool_content .= "
                                                                                    <tr>
                                                                                        <td>".$resource_data['title']."</td>
                                                                                        <td>". $resource_data['type']."</td>";

                                                                                    $tool_content .= "<td class='text-end'>".
                                                                                        action_button(array(
                                                                                            array('title' => $langEditChange,
                                                                                                'icon' => 'fa-edit',
                                                                                                'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;session=$session_id&amp;act_mod=$details->id",
                                                                                                'show' => in_array($details->activity_type, criteria_with_operators())
                                                                                            ),
                                                                                            array('title' => $langDelete,
                                                                                                'icon' => 'fa-xmark',
                                                                                                'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;session=$session_id&amp;del_cert_res=$details->id",
                                                                                                'confirm' => $langConfirmDelete,
                                                                                                'class' => 'delete'))).
                                                                                        "</td></tr>";
                                                                                }

                                                          $tool_content .= "</table>
                                                                        </div>
                                                                    </div>";
                                            }
                       $tool_content .= "</div>
                                    </div>";
            $tool_content .=    "</div>
                            </div>";

                        //************* SESSION PREREQUISITES *************//
                        $course_sessions = Database::get()->queryArray("SELECT * FROM mod_session
                                                                            WHERE course_id = ?d", $course_id);

                        $session_prerequisite_id = Database::get()->querySingle("SELECT up.prerequisite_session
                                                                                    FROM session_prerequisite up
                                                                                    JOIN mod_session cu ON (cu.id = up.session_id)
                                                                                    WHERE cu.id = ".$session_id);

                        $action_button_content = [];

                        foreach ($course_sessions as $prereq) {
                            if ($prereq->id == $session_id) { // Don't include current unit on prerequisites list
                                continue;
                            }
                            $action_button_content[] = [
                                'title' =>  $prereq->title,
                                'icon'  =>  'fa fa-book fa-fw',
                                'url'   =>  "$_SERVER[SCRIPT_NAME]?course=$course_code&prereq=$prereq->id&session=$session_id",
                                'class' =>  '',
                                'show'  =>  !is_session_prereq_enabled($session_id),
                            ];
                        }
                        $addPrereqBtn = action_button($action_button_content,
                            array(
                                'secondary_title' => $langNewUnitPrerequisite,
                                'secondary_icon' => 'fa-plus',
                                'secondary_btn_class' => 'submitAdminBtn',
                            ));
        $tool_content .= "

                        <div class='card panelCard card-default px-lg-4 py-lg-3 mt-3'>
                            <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                <h3>
                                    $langSessionPrerequisites
                                </h3>
                                <div>
                                    $addPrereqBtn
                                </div>
                            </div>
                            <div class='card-body'>
                                <div class='alert alert-info'>
                                    <i class='fa-solid fa-circle-info fa-lg'></i>
                                    <span>$infoPrereqSession</span>
                                </div>
                                <div class='res-table-wrapper'>";
                                        $delPrereqBtn = action_button(array(
                                        array('title' => $langDelete,
                                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&del_un_prereq=1&session=$session_id",
                                            'icon' => 'fa-xmark',
                                            'class' => 'delete',
                                            'confirm' => $langConfirmDelete)));

                                if ( $session_prerequisite_id ) {
                                    $prereq_session_title = Database::get()->querySingle("SELECT title FROM mod_session
                                                                                                WHERE id = ?d", $session_prerequisite_id->prerequisite_session)->title;

                                        $tool_content .= "
                                        <div class='table-responsive mt-0'>
                                            <table class='table-default'>
                                                <tr>
                                                    <td><p class='text-start'>$prereq_session_title</p></td>

                                                    <td class='text-end'>$delPrereqBtn</td>
                                                </tr>
                                            </table>
                                        </div>";
                                } else {
                                    $tool_content .= "<p class='text-center text-muted'>$langNoSessionPrerequisite</p>";
                                }

            $tool_content .= "  </div>
                            </div>
                        </div>";

    }


    $tool_content .= "<div class='modal fade' id='CompletionWithoutActivities{$session_id}' tabindex='-1' aria-labelledby='CompletionWithoutActivitiesLabel' aria-hidden='true'>
                            <form method='post' action='$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=withoutCompletedResource'>
                                <div class='modal-dialog modal-md modal-success'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <div class='modal-title'>
                                                <div class='icon-modal-default'><i class='fa-solid fa-circle-info fa-xl Neutral-500-cl'></i></div>
                                                <h2 class='modal-title-default text-center mb-0 mt-2' id='CompletionWithoutActivitiesLabel'>$langSessionCompletion</h2>
                                            </div>
                                        </div>
                                        <div class='modal-body text-center'>
                                            $langContinueToCompletetionWithoutAct
                                        </div>
                                        <div class='modal-footer d-flex justify-content-center align-items-center'>
                                            <a class='btn cancelAdminBtn' href='' data-bs-dismiss='modal'>$langCancel</a>
                                            <button type='submit' class='btn submitAdminBtnDefault'>$langSubmit</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>";

    $tool_content .= "<div class='modal fade' id='WithCompletedTc{$session_id}' tabindex='-1' aria-labelledby='WithCompletedTcLabel' aria-hidden='true'>
                        <form method='post' action='$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=withCompletedTCResource'>
                            <div class='modal-dialog modal-md modal-success'>
                                <div class='modal-content'>
                                    <div class='modal-header'>
                                        <div class='modal-title'>
                                            <div class='icon-modal-default'><i class='fa-solid fa-circle-info fa-xl Neutral-500-cl'></i></div>
                                            <h2 class='modal-title-default text-center mb-0 mt-2' id='WithCompletedTcLabel'>$langSessionCompletion</h2>
                                        </div>
                                    </div>
                                    <div class='modal-body text-center'>
                                        $langContinueToCompletetionWithCompletedTC
                                    </div>
                                    <div class='modal-footer d-flex justify-content-center align-items-center'>
                                        <a class='btn cancelAdminBtn' href='' data-bs-dismiss='modal'>$langCancel</a>
                                        <button type='submit' class='btn submitAdminBtnDefault'>$langSubmit</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>";

    $tool_content .= "<div class='modal fade' id='WithCompletedLiveMeeting{$session_id}' tabindex='-1' aria-labelledby='WithCompletedLiveMeetingLabel' aria-hidden='true'>
                        <form method='post' action='$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=withCompletedMeeting'>
                            <div class='modal-dialog modal-md modal-success'>
                                <div class='modal-content'>
                                    <div class='modal-header'>
                                        <div class='modal-title'>
                                            <div class='icon-modal-default'><i class='fa-solid fa-circle-info fa-xl Neutral-500-cl'></i></div>
                                            <h2 class='modal-title-default text-center mb-0 mt-2' id='WithCompletedLiveMeetingLabel'>$langSessionCompletion</h2>
                                        </div>
                                    </div>
                                    <div class='modal-body text-center'>
                                        $langContinueToCompletetionWithMeeting
                                    </div>
                                    <div class='modal-footer d-flex justify-content-center align-items-center'>
                                        <a class='btn cancelAdminBtn' href='' data-bs-dismiss='modal'>$langCancel</a>
                                        <button type='submit' class='btn submitAdminBtnDefault'>$langSubmit</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>";


}

/**
 * @brief display editing form about resource
 * @global type $tool_content
 * @global type $course_code
 * @global type $langModify
 * @global type $langOperator
 * @global type $langUsedCertRes
 * @param type $element_id
 * @param type $element
 * @param type $activity_id
 * @param int $session_id
 */
function display_session_modification_activity($element, $element_id, $activity_id, $session_id = 0) {

    global $tool_content, $course_code, $langModify, $langOperator, $langUsedCertRes, $urlAppend, $langImgFormsDes;

    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';
    if (resource_usage($element, $activity_id)) { // check if resource has been used by user
        Session::flash('message',$langUsedCertRes);
        Session::flash('alert-class', 'alert-warning');
        if ($session_id) {
            redirect(localhostUrl().$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&session=$session_id");
        } else {
            redirect_to_home_page("modules/session/complete.php?course=$course_code&session=$session_id&manage=1");
        }

    } else { // otherwise editing is not allowed
        $data = Database::get()->querySingle("SELECT threshold, operator FROM {$element}_criterion
                                            WHERE id = ?d AND $element = ?d", $activity_id, $element_id);

        if ($session_id) {
            $action = "complete.php?course=$course_code&manage=1&session=$session_id";
        } else {
            $action = "index.php?course=$course_code";
        }
        $operators = get_operators();

        $tool_content .= "<div class='d-lg-flex gap-4 mt-4'>
                        <div class='flex-grow-1'><form action=$action method='post'><div class='form-wrapper form-edit rounded'>";
        $tool_content .= "<input type='hidden' name='$element_name' value='$element_id'>";
        $tool_content .= "<input type='hidden' name='activity_id' value='$activity_id'>";
        $tool_content .= "<div class='form-group mt-3'>";
        $tool_content .= "<label for='name' class='col-sm-1 control-label-notes'>$langOperator:</label>";
        $tool_content .= "<span class='col-sm-2'>" . selection($operators, 'cert_operator', $data->operator) . "</span>";
        $tool_content .= "<span class='col-sm-2'><input class='form-control mt-3' type='text' name='cert_threshold' value='$data->threshold'></span>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='col-sm-5 col-sm-offset-5 mt-3'>";
        $tool_content .= "<input class='btn submitAdminBtn' type='submit' name='mod_cert_activity' value='$langModify'>";
        $tool_content .= "</div>";
        $tool_content .= "</div></form>

    </div><div class='d-none d-lg-block'>
    <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
</div>
</div>";
    }
}




/**
 * @brief choose activity for inserting in certificate / badge
 * @param type $element_id
 * @param type $element
 * @param type $activity
 * @param type $session_id
 * @param type $session_resource_id
 */
function insert_session_activity($element, $element_id, $activity, $session_id = 0, $session_resource_id = 0) {

    switch ($activity) {
        case AssignmentEvent::ACTIVITY:
        case 'work':
            display_session_available_assignments($element, $element_id, AssignmentSubmitEvent::ACTIVITY, $session_id, $session_resource_id);
            break;
        case 'document':
        case 'doc':
        case 'submitFile':
            display_session_available_documents($element, $element_id, $session_id, $session_resource_id);
            break;
        case 'poll':
            display_session_available_polls($element, $element_id, $session_id, $session_resource_id);
            break;
        case 'withoutCompletedResource':
            session_completion_without_resources($element, $element_id, $session_id, $session_resource_id);
            break;
        case 'withCompletedTCResource':
            session_completion_with_tc_completed($element, $element_id, $session_id, $session_resource_id);
            break;
        case 'withCompletedMeeting':
            session_completion_with_meeting_completed($element, $element_id, $session_id, $session_resource_id);
            break;
        case 'withCompletionByConsultant':
            session_completion_by_consultant($element, $element_id, $session_id, $session_resource_id);
            break;
        case 'autocomplete':
            session_completion_automatically($element, $element_id, $session_id, $session_resource_id);
            break;
        default: break;
        }
}

/**
 * @brief assignments display form
 * @param type $element
 * @param type $element_id
 * @param int $session_id
 * @param int $session_resource_id
 */
function display_session_available_assignments($element, $element_id, $activity_type, $session_id = 0, $session_resource_id = 0) {

    global $course_id, $tool_content, $langNoAssign, $course_code,
           $langTitle, $langGroupWorkDeadline_of_Submission,
           $langAddModulesButton, $langChoice, $langParticipateSimple,
           $langOperator, $langGradebookGrade, $urlServer, $langSelect;

    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';
    $form_submit_name = 'add_assignment';
    if ($activity_type == AssignmentSubmitEvent::ACTIVITY) {
        $form_submit_name = 'add_assignment_participation';
    }
    $notInSql = "(SELECT resource FROM {$element}_criterion WHERE $element = ?d
                     AND resource != ''
                     AND activity_type = '" . $activity_type . "'
                     AND module = " . MODULE_ID_ASSIGN . ")";

    if ($session_id) {
        if ($session_resource_id) {
            $resWorksSql = "SELECT assignment.id, assignment.title, assignment.description, submission_date
                              FROM assignment, session_resources
                             WHERE assignment.id = session_resources.res_id
                               AND session_id = ?d
                               AND session_resources.id = ?d";
            $result = Database::get()->queryArray("$resWorksSql AND assignment.id NOT IN $notInSql ORDER BY assignment.title", $session_id, $session_resource_id, $element_id);
        } else {
            $sessionWorksSql = "SELECT assignment.id, assignment.title, assignment.description, submission_date
                               FROM assignment, session_resources
                              WHERE assignment.id = session_resources.res_id
                                AND session_id = ?d
                                AND session_resources.type = 'work'
                                AND visible = 1";
            $result = Database::get()->queryArray("$sessionWorksSql AND assignment.id NOT IN $notInSql ORDER BY assignment.title", $session_id, $element_id);
        }
    }

    if (count($result) == 0) {
        $tool_content .= "<div class='col-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoAssign</span></div></div>";
    } else {
        if ($session_id) {
            $action = "complete.php?course=$course_code&manage=1&session=$session_id";
        } else {
            $action = "index.php?course=$course_code";
        }
        $tool_content .= "<form action=$action method='post'>" .
            "<input type='hidden' name = '$element_name' value='$element_id'>" .
            "<div class='table-responsive'><table class='table-default'>" .
            "<thead><tr class='list-header'>" .
            "<th>$langTitle</th>" .
            "<th>$langGroupWorkDeadline_of_Submission</th>";
        if ($activity_type == AssignmentEvent::ACTIVITY) {
            "<th>$langOperator</th>" .
            "<th>$langGradebookGrade</th>";
        }
        $tool_content .=
            "<th>$langChoice</th>" .
            "</tr></thead>";
        foreach ($result as $row) {
            $assignment_id = $row->id;
            $description = empty($row->description) ? '' : "<div style='margin-top: 10px;' class='text-muted'>$row->description</div>";
            $tool_content .= "<tr>" .
                "<td><a href='{$urlServer}modules/work/index.php?course=$course_code&amp;id=$row->id'>" . q($row->title) . "</a>$description</td>" .
                "<td>" . format_locale_date(strtotime($row->submission_date), 'short') . "</td>";
            if ($activity_type == AssignmentEvent::ACTIVITY) {
                $tool_content .=
                "<td>" . selection(get_operators(), "operator[$assignment_id]") . "</td>" .
                "<td><input class='form-control' type='text' name='threshold[$assignment_id]' value=''></td>";
            }
            $tool_content .=
                "<td><label class='label-container' aria-label='$langSelect'><input name='assignment[]' value='$assignment_id' type='checkbox'><span class='checkmark'></span></label></td>" .
                "</tr>";
        }
        $tool_content .= "</table></div>
                          <div class='text-end mt-3'>
                            <input class='btn submitAdminBtn' type='submit' name='$form_submit_name' value='$langAddModulesButton'>
                          </div></form>";
    }
}


/**
 * @brief document display form
 * @param type $element
 * @param type $element_id
 * @param int $session_id
 * @param int $session_resource_id
 */
function display_session_available_documents($element, $element_id, $session_id = 0, $session_resource_id = 0) {

    global $webDir, $tool_content,
            $langDirectory, $langUp, $langName, $langSize,
            $langDate, $langAddModulesButton, $langChoice,
            $langNoDocuments, $course_code, $group_sql, $langNotFolders , $course_id, $langSettingSelect, $langSelect;

    require_once 'modules/document/doc_init.php';
    require_once 'include/lib/mediaresource.factory.php';
    require_once 'include/lib/fileManageLib.inc.php';
    require_once 'include/lib/fileDisplayLib.inc.php';
    require_once 'include/lib/multimediahelper.class.php';

    doc_init();

    $common_docs = false;
    if($session_id){
        $basedir = $webDir . '/courses/' . $course_code . '/session/session_' . $session_id;
    }else{
        $basedir = $webDir . '/courses/' . $course_code . '/document';
    }

    $path = get_dir_path('path');
    $dir_param = get_dir_path('dir');
    $dir_setter = $dir_param ? ('&amp;dir=' . $dir_param) : '';
    $dir_html = $dir_param ? "<input type='hidden' name='dir' value='$dir_param'>" : '';

    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';

    if ($session_id) {
        $sql_only_not_selected_resources = '';
        if(isset($_GET['act']) && $_GET['act'] == 'submitFile'){
            if(isset($_GET['badge_id'])){
                $badge_id = $_GET['badge_id'];
            }else{
                $badge_id = Database::get()->querySingle("SELECT id FROM badge WHERE session_id = ?d AND course_id = ?d",$session_id,$course_id)->id;
            }
            $sql_query = "SELECT resource FROM badge_criterion WHERE activity_type = 'document-submit' AND badge = $badge_id";
            $sql_only_not_selected_resources = "AND session_resources.res_id NOT IN ($sql_query)";
        }
        if ($session_resource_id) {
            $result = Database::get()->queryArray("SELECT document.id, subsystem, course_id, path, filename, format, document.title, extra_path, date_modified, document. visible, copyrighted, comment, IF(document.title = '', filename, document.title) AS sort_key
                                            FROM document, session_resources
                                            WHERE document.id = session_resources.res_id
                                                AND session_id = ?d
                                                AND session_resources.id = ?d
                                                AND session_resources.doc_id = ?d
                                                AND session_resources.from_user = ?d
                                                $sql_only_not_selected_resources"
                                            , $session_id, $session_resource_id,0,0);
        } else {
            $result = Database::get()->queryArray("SELECT document.id, subsystem, course_id, path, filename, format, document.title, extra_path, date_modified, document. visible, copyrighted, comment, IF(document.title = '', filename, document.title) AS sort_key
                                            FROM document, session_resources
                                            WHERE document.id = session_resources.res_id
                                                AND session_id = ?d
                                                AND session_resources.type = 'doc'
                                                AND session_resources.visible = 1
                                                AND session_resources.doc_id = ?d
                                                AND session_resources.from_user = ?d
                                                $sql_only_not_selected_resources", $session_id,0,0);
        }

    }


    $fileinfo = array();
    $urlbase = $_SERVER['SCRIPT_NAME'] . "?course=$course_code&amp;session=$session_id&amp;$element_name=$element_id&amp;add=true&amp;act=document$dir_setter&amp;type=doc&amp;path=";

    foreach ($result as $row) {
        $fullpath = $basedir . $row->path;
        if ($row->extra_path) {
            $size = 0;
        } else {
            $size = file_exists($fullpath)? filesize($fullpath): 0;
        }
        $fileinfo[] = array(
            'id' => $row->id,
            'subsystem' => $row->subsystem,
            'is_dir' => is_dir($fullpath),
            'size' => $size,
            'title' => $row->title,
            'name' => htmlspecialchars($row->filename),
            'format' => $row->format,
            'path' => $row->path,
            'visible' => $row->visible,
            'comment' => $row->comment,
            'copyrighted' => $row->copyrighted,
            'date' => $row->date_modified,
            'object' => MediaResourceFactory::initFromDocument($row));
    }

    if (count($fileinfo) == 0) {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoDocuments</span></div></div>";
    } else {
        if (!empty($path)) {
            if($session_id){
                $group_sql = "course_id = $course_id AND subsystem = " . MYSESSIONS;
            }
            $dirname = Database::get()->querySingle("SELECT filename FROM document WHERE $group_sql AND path = ?s", $path);
            $parentpath = dirname($path);
            $dirname =  htmlspecialchars($dirname->filename);
            $parentlink = $urlbase . $parentpath;
            $parenthtml = "<span class='float-end'><a href='$parentlink'>$langUp " .
                    icon('fa-level-up') . "</a></span>";
            $colspan = 4;
        }
        if ($session_id) {
            $action = "complete.php?course=$course_code&manage=1&session=$session_id";
        } else {
            $action = "index.php?course=$course_code";
        }


        $tool_content .= "<div class='alert alert-info'>
                                <i class='fa-solid fa-circle-info fa-lg'></i>
                                <span>$langNotFolders</span>
                            </div>";

        $tool_content .= "<form action=$action method='post'>" .
                "<input type='hidden' name='$element_name' value='$element_id'>" .
                "<div class='table-responsive'><table class='table-default'>";
        if( !empty($path)) {
        $tool_content .=
                "<tr>" .
                "<th colspan='$colspan'><div>$langDirectory: $dirname$parenthtml</div></th>" .
                "</tr>" ;
        }
        $tool_content .=
                "<thead><tr class='list-header'>" .
                "<th>$langName</th>" .
                "<th>$langSize</th>" .
                "<th>$langDate</th>" .
                "<th aria-label='$langSettingSelect'></th>" .
                "</tr></thead>";
        $counter = 0;
        foreach (array(true, false) as $is_dir) {
            foreach ($fileinfo as $entry) {
                if ($entry['is_dir'] != $is_dir) {
                    continue;
                }
                $dir = $entry['path'];
                if ($is_dir) {
                    $image = 'fa-folder-open';
                    $file_url = $urlbase . $dir;
                    $link_text = $entry['name'];
                    $link_href = "<a href='$file_url'>$link_text</a>";
                } else {
                    $image = choose_image('.' . $entry['format']);
                    if($entry['subsystem'] == MYSESSIONS){
                        $file_url = session_file_url($entry['path'], $entry['name']);
                    }else{
                        $file_url = file_url($entry['path'], $entry['name'], $common_docs ? 'common' : $course_code);
                    }


                    $dObj = $entry['object'];
                    $dObj->setAccessURL($file_url);
                    if($entry['subsystem'] == MYSESSIONS){
                        $dObj->setPlayURL(session_file_playurl($entry['path'], $entry['name']));
                    }else{
                        $dObj->setPlayURL(file_playurl($entry['path'], $entry['name'], $common_docs ? 'common' : $course_code));
                    }


                    $link_href = MultimediaHelper::chooseMediaAhref($dObj);
                }
                if ($entry['visible'] == 'i') {
                    $vis = 'invisible';
                } else {
                    $vis = '';
                }
                $tool_content .= "<tr class='$vis'>";
                $tool_content .= "<td>" . icon($image, '')."&nbsp;&nbsp;&nbsp;$link_href";

                /* * * comments ** */
                if (!empty($entry['comment'])) {
                    $tool_content .= "<div style='margin-top: 10px;' class='comment'>" .
                            standard_text_escape($entry['comment']) .
                            "</div>";
                }
                $tool_content .= "</td>";
                if ($is_dir) {
                    // skip display of date and time for directories
                    $tool_content .= "<td>&nbsp;</td><td>&nbsp;</td>";
                } else {
                    $size = format_file_size($entry['size']);
                    $date = format_locale_date(strtotime($entry['date']), 'short', false);
                    $tool_content .= "<td>$size</td><td>$date</td>";
                }
                $tool_content .= "<td><label class='label-container' aria-label='$langSelect'><input type='checkbox' name='document[]' value='$entry[id]' /><span class='checkmark'></span></label></td>";
                $tool_content .= "</tr>";
                $counter++;
            }
        }
        $tool_content .= "</table></div>";
        $tool_content .= "<div class='text-end mt-3'>";
        if(isset($_GET['act']) && $_GET['act'] == 'submitFile'){
            $tool_content .= "<input class='btn submitAdminBtn' type='submit' name='add_submited_document' value='$langAddModulesButton' /></div>$dir_html</form>";
        }else{
            $tool_content .= "<input class='btn submitAdminBtn' type='submit' name='add_document' value='$langAddModulesButton' /></div>$dir_html</form>";
        }

    }
}

/**
 * @brief session completion without resources
 * @param type $element
 * @param type $element_id
 * @param int $session_id
 * @param int $session_resource_id
 */
function session_completion_by_consultant($element, $element_id, $session_id = 0, $session_resource_id = 0){
    global $course_code, $course_id, $langResourceAddedWithSuccess, $langResourceExists;

    if($session_id){
        $check = Database:: get()->querySingle("SELECT * FROM {$element}_criterion WHERE $element = ?d AND activity_type = ?s",$element_id,'consultant-completion');
        if(!$check){
            Database::get()->query("INSERT INTO {$element}_criterion
                        SET $element = ?d,
                        activity_type = 'consultant-completion'",$element_id);

            Session::flash('message',$langResourceAddedWithSuccess);
            Session::flash('alert-class', 'alert-success');
        }else{
            Session::flash('message',$langResourceExists);
            Session::flash('alert-class', 'alert-warning');
        }

    }

    redirect_to_home_page('modules/session/complete.php?course=' . $course_code . '&manage=1&session=' . $session_id);
}

/**
 * @brief session completion without resources
 * @param type $element
 * @param type $element_id
 * @param int $session_id
 * @param int $session_resource_id
 */
function session_completion_without_resources($element, $element_id, $session_id = 0, $session_resource_id = 0){

    global $course_code, $course_id, $langForbidden, $langSessionHasCompleted, $langSessionCompletedNotContinue, $langSessionCompletedIsActivated, $langNotExistUsers;

    if($session_id){

        $participants = Database::get()->queryArray("SELECT participants FROM mod_session_users
                                                     WHERE session_id = ?d AND is_accepted = ?d",$session_id,1);
        if(count($participants) == 0){
            Session::flash('message',$langForbidden.'</br>'.$langNotExistUsers);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page("modules/session/complete.php?course=$course_code&manage=1&session=$session_id");
        }

        // Initially we should check if a session has completed prequisite session
        $check_completion_badge = false;
        $has_completed_prerequisite_session = false;
        $has_prereq = Database::get()->querySingle("SELECT prerequisite_session FROM session_prerequisite
                                                    WHERE course_id = ?d AND session_id = ?d",$course_id,$session_id);
        if(isset($has_prereq->prerequisite_session) and $has_prereq->prerequisite_session){
            // Now we must check if prerequisite session is completed in order to continue
            $badge = Database::get()->querySingle("SELECT id FROM {$element} WHERE session_id = ?d",$has_prereq->prerequisite_session);
            $pr = Database::get()->querySingle("SELECT completed FROM user_{$element} WHERE $element = ?d",$badge->id);
            if(isset($pr->completed) and $pr->completed){
                $has_completed_prerequisite_session = true;
            }
        }elseif(!isset($has_prereq->prerequisite_session)){
            $has_completed_prerequisite_session = true;
        }

        if(isset($has_completed_prerequisite_session) and $has_completed_prerequisite_session){
            // Now we have to check if badge contains activities for completing.
            $res = Database::get()->querySingle("SELECT activity_type FROM {$element}_criterion
                                                 WHERE activity_type <> ?s
                                                 AND $element = ?d",'noactivity',$element_id);
            if($res){
                $has_completed_prerequisite_session = false;
            }
        }

        // Check if session has badge with no activities in order to not insert new records in db
        if(!$check_completion_badge){
            $badge_res = Database::get()->querySingle("SELECT id FROM {$element} WHERE session_id = ?d",$session_id);
            $new_res = Database::get()->querySingle("SELECT activity_type FROM {$element}_criterion WHERE badge = ?d",$badge_res->id);
            if($new_res->activity_type == 'noactivity'){
                $check_completion_badge = true;
                $has_completed_prerequisite_session = false;
                Session::flash('message',$langSessionCompletedIsActivated);
                Session::flash('alert-class', 'alert-danger');
                redirect_to_home_page('modules/session/complete.php?course=' . $course_code . '&manage=1&session=' . $session_id);
            }
        }

        if($has_completed_prerequisite_session){
            $crit = Database::get()->query("INSERT INTO {$element}_criterion SET $element = ?d, activity_type = ?s",$element_id,'noactivity');
            $par = Database::get()->queryArray("SELECT participants FROM mod_session_users
                                                        WHERE session_id = ?d AND is_accepted = ?d",$session_id,1);

            if($crit && count($par) > 0){
                foreach ($par as $p) {
                    $noExistUser = Database::get()->querySingle("SELECT id FROM user_{$element} WHERE user = ?d AND $element = ?d",$p->participants,$element_id);
                    if (!$noExistUser) {
                        Database::get()->query("INSERT INTO user_{$element} SET
                                                user = ?d,
                                                $element = ?d,
                                                updated = " . DBHelper::timeAfter() . ",
                                                assigned = " . DBHelper::timeAfter() . ",
                                                completed = ?d,
                                                completed_criteria = ?d,
                                                total_criteria = ?d",$p->participants,$element_id,1,1,1);
                    }
                    
                    Database::get()->query("INSERT INTO user_{$element}_criterion SET
                                                user = ?d,
                                                created = " . DBHelper::timeAfter() . ",
                                                {$element}_criterion = ?d",$p->participants, $crit->lastInsertID);
                }
            }
            Session::flash('message',$langSessionHasCompleted);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page('modules/session/complete.php?course=' . $course_code . '&manage=1&session=' . $session_id);
        }else{
            Session::flash('message',$langSessionCompletedNotContinue);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page('modules/session/complete.php?course=' . $course_code . '&manage=1&session=' . $session_id);
        }
    }else{
        redirect_to_home_page("courses/$course_code/");
    }
}

/**
 * @brief session completion with meeting resource
 * @param type $element
 * @param type $element_id
 * @param int $session_id
 * @param int $session_resource_id
 */
function session_completion_with_meeting_completed($element, $element_id, $session_id = 0, $session_resource_id = 0){
    global $course_code, $langResourceAddedWithSuccess, $course_id, $langResourceExists;

    if($session_id){
        $res = Database::get()->querySingle("SELECT start,finish FROM mod_session WHERE course_id = ?d AND type_remote = ?d AND id = ?d", $course_id, 0, $session_id);
        // check if badge exists
        $ch = Database::get()->querySingle("SELECT id FROM {$element}_criterion
                                            WHERE badge = ?d
                                            AND activity_type = ?s", $element_id,'meeting-completed');

        if(!$ch){
            Database::get()->query("INSERT INTO {$element}_criterion
                                    SET $element = ?d,
                                    activity_type = ?s",$element_id, 'meeting-completed');

            Session::flash('message',$langResourceAddedWithSuccess);
            Session::flash('alert-class', 'alert-success');
        }else{
            Session::flash('message',$langResourceExists);
            Session::flash('alert-class', 'alert-danger');
        }

        redirect_to_home_page('modules/session/complete.php?course=' . $course_code . '&manage=1&session=' . $session_id);

    }else{
        redirect_to_home_page("courses/$course_code/");
    }
}


/**
 * @brief session completion with tc resource
 * @param type $element
 * @param type $element_id
 * @param int $session_id
 * @param int $session_resource_id
 */
function session_completion_with_tc_completed($element, $element_id, $session_id = 0, $session_resource_id = 0){

    global $course_code, $langResourceAddedWithSuccess, $course_id, $langResourceExists, $langNoExistsTClink;

    if($session_id){
        $res = Database::get()->querySingle("SELECT start,finish FROM mod_session WHERE course_id = ?d AND type_remote = ?d AND id = ?d", $course_id, 1, $session_id);
        $tc = Database::get()->querySingle("SELECT id FROM tc_session WHERE course_id = ?d AND start_date = ?t AND end_date = ?t AND id_session = ?d",$course_id, $res->start, $res->finish, $session_id);
        //check if exists
        $ch = Database::get()->querySingle("SELECT id FROM {$element}_criterion
                                            WHERE badge = ?d AND activity_type = ?s AND module = ?d AND resource = ?d",$element_id,'tc-completed',MODULE_ID_TC,$tc->id);
        if($tc && !$ch){

            // Check if tc has been added as a session resource.
            $exists_as_resource_session = Database::get()->querySingle("SELECT id FROM session_resources
                                                                        WHERE session_id = ?d
                                                                        AND type = ?s
                                                                        AND res_id = ?d",$session_id,'tc',$tc->id);
            if($exists_as_resource_session){
                Database::get()->query("INSERT INTO {$element}_criterion
                        SET $element = ?d,
                        module= " . MODULE_ID_TC . ",
                        resource = ?d,
                        activity_type = 'tc-completed'",$element_id, $tc->id);

                Session::flash('message',$langResourceAddedWithSuccess);
                Session::flash('alert-class', 'alert-success');
            }else{
                Session::flash('message',$langNoExistsTClink);
                Session::flash('alert-class', 'alert-warning');
            }

        }else{
            if(!$tc){
                Session::flash('message',$langNoExistsTClink);
                Session::flash('alert-class', 'alert-warning');
            }else{
                Session::flash('message',$langResourceExists);
                Session::flash('alert-class', 'alert-danger');
            }

        }
        redirect_to_home_page('modules/session/complete.php?course=' . $course_code . '&manage=1&session=' . $session_id);

    }else{
        redirect_to_home_page("courses/$course_code/");
    }

}

/**
 * @brief session completion checker for meeting type
 * @param int $sid
 * @param int $forUid
 */
function check_session_completion_by_meeting_completed($session_id = 0, $forUid = 0){
    global $course_id;

    if($session_id){
        $badge_criterion = Database::get()->querySingle("SELECT * FROM badge_criterion
                                                            WHERE badge IN (SELECT id FROM badge
                                                                            WHERE course_id = ?d
                                                                            AND session_id = ?d)
                                                            AND activity_type = ?s",$course_id, $session_id,'meeting-completed');

        if($badge_criterion){
            $badge_id = $badge_criterion->badge;
            $badge_criterion_id = $badge_criterion->id;

            // Initially we should check if a session has completed prequisite session
            $per = 100;
            $has_prereq = Database::get()->querySingle("SELECT prerequisite_session FROM session_prerequisite
                                                        WHERE course_id = ?d AND session_id = ?d",$course_id,$session_id);

            if($has_prereq){
                $badge_prerequisite = Database::get()->querySingle("SELECT id FROM badge WHERE course_id = ?d AND session_id = ?d",$course_id,$has_prereq->prerequisite_session);
                if($badge_prerequisite){
                    $per = get_cert_percentage_completion_by_user('badge',$badge_prerequisite->id,$forUid);
                }
            }

            if($per < 100){
                return;
            }

            $res = Database::get()->querySingle("SELECT * FROM user_badge_criterion WHERE user = ?d AND badge_criterion = ?d",$forUid,$badge_criterion_id);
            if(!$res){
                // When meeting has expired then update db
                $check_meeting = Database::get()->querySingle("SELECT finish,start FROM mod_session WHERE id = ?d AND course_id = ?d AND type_remote = ?d",$session_id,$course_id,0);
                if($check_meeting->finish < date("Y-m-d H:i:s") && $check_meeting->start < date("Y-m-d H:i:s")){
                    Database::get()->query("INSERT INTO user_badge_criterion
                                            SET user = ?d,
                                            created = " . DBHelper::timeAfter() . ",
                                            badge_criterion = ?d",$forUid,$badge_criterion_id);

                    Database::get()->query("UPDATE user_badge SET completed_criteria = completed_criteria + 1,
                                            updated = " . DBHelper::timeAfter() . ",
                                            assigned = " . DBHelper::timeAfter() . "
                                            WHERE user = ?d AND badge = ?d",$forUid,$badge_id);
                }
            }elseif($res){
                $check_meeting = Database::get()->querySingle("SELECT finish FROM mod_session WHERE id = ?d AND course_id = ?d",$session_id,$course_id);
                // meeting not completed
                if($check_meeting->finish > date("Y-m-d H:i:s")){
                    Database::get()->query("DELETE FROM user_badge_criterion
                                            WHERE user = ?d
                                            AND badge_criterion = ?d",$forUid,$badge_criterion_id);

                    Database::get()->query("UPDATE user_badge SET completed_criteria = completed_criteria - 1,
                                            updated = " . DBHelper::timeAfter() . ",
                                            assigned = " . DBHelper::timeAfter() . "
                                            WHERE user = ?d AND badge = ?d",$forUid,$badge_id);
                }
            }
        }
    }
}

/**
 * @brief session completion checker for tc type
 * @param int $sid
 * @param int $forUid
 */
function check_session_completion_by_tc_completed($session_id = 0, $forUid = 0){
    global $course_id;

    if($session_id){
        $badge_criterion = Database::get()->queryArray("SELECT * FROM badge_criterion
                                                            WHERE badge IN (SELECT id FROM badge
                                                                            WHERE course_id = ?d
                                                                            AND session_id = ?d)
                                                            AND activity_type = ?s",$course_id, $session_id,'tc-completed');

        $badge_id = 0;
        $badge_criterion_id = 0;
        $tc_id = 0;
        if(count($badge_criterion) > 0){
            foreach($badge_criterion as $b){
                if($b->activity_type == 'tc-completed'){
                    $badge_id = $b->badge;
                    $badge_criterion_id = $b->id;
                    $tc_id = $b->resource;
                    break;
                }
            }

            if($badge_criterion_id > 0){

                // Initially we should check if a session has completed prequisite session
                $per = 100;
                $has_prereq = Database::get()->querySingle("SELECT prerequisite_session FROM session_prerequisite
                                                            WHERE course_id = ?d AND session_id = ?d",$course_id,$session_id);

                if($has_prereq){
                    $badge_prerequisite = Database::get()->querySingle("SELECT id FROM badge WHERE course_id = ?d AND session_id = ?d",$course_id,$has_prereq->prerequisite_session);
                    if($badge_prerequisite){
                        $per = get_cert_percentage_completion_by_user('badge',$badge_prerequisite->id,$forUid);
                    }
                }

                if($per < 100){
                    return;
                }

                $res = Database::get()->querySingle("SELECT * FROM user_badge_criterion WHERE user = ?d AND badge_criterion = ?d",$forUid,$badge_criterion_id);
                if(!$res){
                    // When tc has expired then update db
                    $check_tc = Database::get()->querySingle("SELECT end_date,start_date FROM tc_session WHERE id = ?d AND course_id = ?d AND id_session = ?d",$tc_id,$course_id,$session_id);
                    if($check_tc->end_date < date("Y-m-d H:i:s") && $check_tc->start_date < date("Y-m-d H:i:s")){
                        Database::get()->query("INSERT INTO user_badge_criterion
                                                SET user = ?d,
                                                created = " . DBHelper::timeAfter() . ",
                                                badge_criterion = ?d",$forUid,$badge_criterion_id);

                        Database::get()->query("UPDATE user_badge SET completed_criteria = completed_criteria + 1,
                                                updated = " . DBHelper::timeAfter() . ",
                                                assigned = " . DBHelper::timeAfter() . "
                                                WHERE user = ?d AND badge = ?d",$forUid,$badge_id);
                    }
                }elseif($res){
                    $check_tc = Database::get()->querySingle("SELECT end_date FROM tc_session WHERE id = ?d AND course_id = ?d AND id_session = ?d",$tc_id,$course_id,$session_id);
                    // tc not completed
                    if($check_tc->end_date > date("Y-m-d H:i:s")){
                        Database::get()->query("DELETE FROM user_badge_criterion
                                                WHERE user = ?d
                                                AND badge_criterion = ?d",$forUid,$badge_criterion_id);

                        Database::get()->query("UPDATE user_badge SET completed_criteria = completed_criteria - 1,
                                                updated = " . DBHelper::timeAfter() . ",
                                                assigned = " . DBHelper::timeAfter() . "
                                                WHERE user = ?d AND badge = ?d",$forUid,$badge_id);
                    }
                }
            }
        }
    }
}


/**
 * @brief poll display form
 * @param type $element
 * @param type $element_id
 * @param int $session_id
 * @param int $session_resource_id
 */
function display_session_available_polls($element, $element_id, $session_id = 0, int $session_resource_id = 0) {

    global $course_id, $course_code, $urlServer, $tool_content,
            $langPollNone, $langQuestionnaire, $langChoice, 
            $langAddModulesButton, $langSelect, $langDescription;

    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';

    if ($session_id) {
        if ($session_resource_id) {
            $result = Database::get()->queryArray("SELECT poll.pid, name, description FROM poll, session_resources
                                            WHERE poll.pid = session_resources.res_id
                                                AND poll.active = 1
                                                AND poll.end_date >= ". DBHelper::timeAfter() . "
                                                AND session_id = ?d
                                                AND session_resources.id = ?d"
                                            , $session_id, $session_resource_id);
        } else {
            $result = Database::get()->queryArray("SELECT poll.pid, name, description FROM poll, session_resources
                                            WHERE poll.pid = session_resources.res_id
                                                AND poll.active = 1
                                                AND poll.end_date >= ". DBHelper::timeAfter() . "
                                                AND session_id = ?d
                                                AND session_resources.type = 'poll'
                                                AND visible = 1", $session_id);
        }

    }

    $pollinfo = array();
    foreach ($result as $row) {
        $pollinfo[] = array(
            'id' => $row->pid,
            'title' => $row->name,
            'description' => $row->description);
    }
    if (count($pollinfo) == 0) {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langPollNone</span></div></div>";
    } else {
        if ($session_id) {
            $action = "complete.php?course=$course_code&manage=1&session=$session_id";
        } else {
            $action = "index.php?course=$course_code";
        }
        $tool_content .= "<form action=$action method='post'>" .
                "<input type='hidden' name='$element_name' value='$element_id'>" .
                "<div class='table-responsive'><table class='table-default'>" .
                "<thead><tr class='list-header'>" .
                "<th>$langQuestionnaire</th>" .
                "<th class='text-end'>$langChoice</th>" .
                "</tr></thead>";
        foreach ($pollinfo as $entry) {
            $description = empty($entry['description']) ? '' : "<div style='margin-top: 10px;' class='text-muted'>". $entry['description']. "</div>";
            $tool_content .= "<tr>";
            $tool_content .= "<td>
                                &nbsp;".icon('fa-question-circle')."&nbsp;&nbsp;
                                <a href='{$urlServer}modules/units/view.php?course=$course_code&amp;res_type=questionnaire&amp;pid=$entry[id]&amp;UseCase=1&amp;session=$_GET[session]&amp;from_session_view=true'>
                                    " . q($entry['title']) . "
                                </a>";
                                if (!empty($entry['description'])) {
                    $tool_content .= "  <div class='panel'>
                                            <div class='panel-group group-section' id='accordion_$entry[id]' role='tablist' aria-multiselectable='true'>
                                                <ul class='list-group list-group-flush mt-2'>
                                                    <li class='list-group-item px-0 bg-transparent'>
                                                        <a class='accordion-btn d-flex justify-content-start align-items-start' role='button' data-bs-toggle='collapse' href='#des-$entry[id]' aria-expanded='false'>
                                                            <span class='fa-solid fa-chevron-down'></span>$langDescription
                                                        </a>
                                                        <div id='des-$entry[id]' class='panel-collapse accordion-collapse collapse border-0 rounded-0' role='tabpanel' data-bs-parent='#accordion_$entry[id]'>
                                                            <div class='panel-body bg-transparent Neutral-900-cl px-4'>
                                                                " . $description . "
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>";
                                }
            $tool_content .= "</td>";
            $tool_content .= "<td><div class='d-flex justify-content-end'><label class='label-container' aria-label='$langSelect'><input type='checkbox' name='poll[]' value='$entry[id]'><span class='checkmark'></span></label></div></td>";
            $tool_content .= "</tr>";
        }
        $tool_content .= "</table></div>";
        $tool_content .= "<div class='text-end mt-3'>";
        $tool_content .= "<input class='btn submitAdminBtn' type='submit' name='add_poll' value='$langAddModulesButton'></div></form>";
    }
}

/**
 * @param int $prereq_session_id
 * @return bool
 */
function prereq_session_has_completion_enabled($prereq_session_id) {
    // This is for session completion enabled when session contains any resource
    $exists = Database::get()->querySingle("SELECT id FROM badge WHERE session_id = ?d", $prereq_session_id);
    if ($exists) {
        return true;
    }
    return false;
}

/**
 * @param int $session_id
 * @param int $prereq_session_id
 */
function insert_session_prerequisite_unit($session_id, $prereq_session_id) {

    global $is_consultant,
           $course_id, $course_code,
           $langResultsFailed, $langSessionHasNotCompletionEnabled, $langNewSessionPrerequisiteFailInvalid,
           $langNewUnitPrerequisiteSuccess, $langNewSessionPrerequisiteFailAlreadyIn;

    if ($is_consultant) { // Auth check
        if ($prereq_session_id < 0) {
            Session::flash('message',$langNewSessionPrerequisiteFailInvalid);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page('modules/session/complete.php?course=' . $course_code . '&manage=1&session=' . $session_id);
        }

        $prereqHasCompletion = prereq_session_has_completion_enabled($prereq_session_id);

        if ( !$prereqHasCompletion ) {
            Session::flash('message',$langSessionHasNotCompletionEnabled);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page('modules/session/complete.php?course=' . $course_code . '&manage=1&session=' . $session_id);
        }

        // check already exists
        $result = Database::get()->queryArray("SELECT up.id
                                 FROM session_prerequisite up
                                 WHERE up.course_id = ?d
                                 AND up.session_id = ?d
                                 AND up.prerequisite_session = ?d", $course_id, $session_id, $prereq_session_id);

        if (count($result) > 0) {
            Session::flash('message',$langNewSessionPrerequisiteFailAlreadyIn);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page('modules/session/complete.php?course=' . $course_code . '&manage=1&session=' . $session_id);
        }

        Session::flash('message',$langNewUnitPrerequisiteSuccess);
        Session::flash('alert-class', 'alert-success');
        Database::get()->query("INSERT INTO session_prerequisite (course_id, session_id, prerequisite_session)
                                                VALUES (?d, ?d, ?d)", $course_id, $session_id, $prereq_session_id);
    } else {
        Session::flash('message',$langResultsFailed);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page('modules/session/complete.php?course=' . $course_code . '&manage=1&session=' . $session_id);
    }
}

/**
 * @param int $session_id
 * @return bool
 */
function is_session_prereq_enabled($session_id) {
    $prereq_id = Database::get()->queryArray("SELECT prerequisite_session FROM session_prerequisite
                                                        WHERE session_id = ?d", $session_id);
    if (count($prereq_id) > 0) {
        return true;
    }
    return false;
}

/**
 * @param int $session_id
 */
function delete_session_prerequisite($session_id) {
    $query = "DELETE FROM session_prerequisite WHERE session_id = ?d";
    Database::get()->query($query, $session_id);
}


/**
 * @global type $course_id
 * @param int $uid
 * @param type $all_sessions
 * @return array
 */
function findUserVisibleSessions($uid, $all_sessions) {
    global $course_id;

    $user_sessions = [];
    $userInBadges = Database::get()->queryArray("SELECT cu.id, cu.title, cu.comments, cu.start, cu.finish, cu.visible, cu.public, ub.completed
                                                          FROM mod_session cu
                                                          INNER JOIN badge b ON (b.session_id = cu.id)
                                                          INNER JOIN user_badge ub ON (b.id = ub.badge)
                                                          WHERE ub.user = ?d
                                                          AND cu.course_id = ?d
                                                          AND cu.visible = 1
                                                          AND cu.public = 1
                                                          AND cu.order >= 0", $uid, $course_id);
    if ( isset($userInBadges) and $userInBadges ) {
        foreach ($userInBadges as $userInBadge) {
            if ($userInBadge->completed == 0) {
                $userIncompleteSessions[] = $userInBadge->id;
            }
        }
    }
    foreach ($all_sessions as $session) {
        $sessionPrereq = Database::get()->querySingle("SELECT prerequisite_session FROM session_prerequisite
                                                                WHERE session_id = ?d", $session->id);

        if ( $sessionPrereq and isset($userIncompleteSessions) and in_array($sessionPrereq->prerequisite_session, $userIncompleteSessions) ) {
            continue;
        }
        $user_sessions[] = $session;
    }
    return $user_sessions;
}



/**
 * @brief check if session resource has completed
 * @param $session_id
 * @param $session_resource_id
 * @return integer
 */
function session_resource_completion($session_id, $session_resource_id) {

    global $uid, $course_id;

    $badge_id = Database::get()->querySingle("SELECT id FROM badge WHERE course_id = ?d AND session_id = ?d", $course_id, $session_id)->id;
    $res_id = Database::get()->querySingle("SELECT res_id FROM session_resources WHERE id = ?d", $session_resource_id)->res_id;
    $q = Database::get()->querySingle("SELECT * FROM badge_criterion WHERE badge = ?d AND resource = ?d", $badge_id, $res_id);
    if ($q) {
        // complete user resources
        $sql = Database::get()->querySingle("SELECT badge_criterion FROM user_badge_criterion JOIN badge_criterion
                                                    ON user_badge_criterion.badge_criterion = badge_criterion.id
                                                        AND badge_criterion.badge = ?d
                                                        AND badge_criterion.resource = ?d
                                                        AND user = ?d", $badge_id, $res_id, $uid);
        if ($sql) {
            return 1; // activity has been completed
        } else {
            return 0; // activity has not been completed
        }
    } else {
        return 2; // there is no activity
    }
}

/**
 * @brief get certificate / badge percentage completion
 * @param type $element
 * @param type $userId
 * @param type $element_id
 * @return type
 */
function get_cert_percentage_completion_by_user($element, $element_id, $userId) {

    // Special case for session polls regarding the current session.
    
    $allCr = Database::get()->queryArray("SELECT * FROM badge_criterion WHERE badge = ?d", $element_id);
    $sid = Database::get()->querySingle("SELECT session_id FROM badge WHERE id = ?d", $element_id);
    if (count($allCr) > 0) {
        foreach ($allCr as $c) {
            if ($c->activity_type == 'questionnaire') {
                $badgeCrId = $c->id;
                $check = Database::get()->querySingle("SELECT id FROM poll_user_record
                                                        WHERE pid = ?d 
                                                        AND uid = ?d 
                                                        AND session_id = ?d", $c->resource, $userId, $sid->session_id);
                if (!$check) {
                    $check2 = Database::get()->querySingle("SELECT id FROM user_badge_criterion 
                                                            WHERE user = ?d AND badge_criterion = ?d", $userId, $badgeCrId);
                    if ($check2) {
                        Database::get()->query("DELETE FROM user_badge_criterion WHERE id = ?d", $check2->id);
                    }
                }
            }
        }
    }

    $data = Database::get()->querySingle("SELECT completed_criteria, total_criteria "
            . "FROM user_{$element} WHERE user = ?d AND $element = ?d", $userId, $element_id);

    if (!$data or !$data->total_criteria) {
        return 0;
    } else {
        return round($data->completed_criteria / $data->total_criteria * 100, 0);
    }
}

/**
 * @brief create tc link for current session using BBB
 * @param $sid
 * @param $cid
 * @param $tc_type
 * @param $token
 */
function session_tc_creation($sid,$cid,$tc_type,$token){

    global $course_code, $langRemoteConference, $langExistsTc;

    if ($tc_type == 'bbb') {

        if (!isset($token) || !validate_csrf_token($token)) csrf_token_error();

        $exists_resource = Database::get()->querySingle("SELECT id FROM session_resources WHERE session_id = ?d AND type = ?s",$sid,'tc');
        if($exists_resource){
            Session::flash('message',$langExistsTc);
            Session::flash('alert-class', 'alert-warning');
            redirect_to_home_page('modules/session/session_space.php?course=' . $course_code . '&session=' . $sid);
        }

        $t_title = Database::get()->querySingle("SELECT title FROM mod_session WHERE id = ?d AND course_id = ?d",$sid,$cid)->title;
        $title = $langRemoteConference . '-' . $t_title;
        $desc = '';
        $start = Database::get()->querySingle("SELECT start FROM mod_session WHERE id = ?d AND course_id = ?d",$sid,$cid)->start;
        $end = Database::get()->querySingle("SELECT finish FROM mod_session WHERE id = ?d AND course_id = ?d",$sid,$cid)->finish;
        $status = 1;
        $notifyUsers = 1;
        $notifyExternalUsers = 0;
        $addAnnouncement = 1;
        $minutes_before = "10";
        $external_users = NULL;
        $bbb_max_part_per_room = get_config('bbb_max_part_per_room', 0);
        $sessionUsers = Database::get()->querySingle("SELECT COUNT(*) AS count FROM mod_session_users WHERE session_id = ?d AND is_accepted = ?d",$sid,1)->count;
        if (!empty($bbb_max_part_per_room) and ($sessionUsers > $bbb_max_part_per_room)) {
            $sessionUsers = $bbb_max_part_per_room;
        }
        $options_arr = array();
        if (get_config('bbb_muteOnStart')) {
            $options_arr['muteOnStart'] = 1;
        }
        if (get_config('bbb_DisableMic')) {
            $options_arr['lockSettingsDisableMic'] = 1;
        }
        if (get_config('bbb_DisableCam')) {
            $options_arr['lockSettingsDisableCam'] = 1;
        }
        if (get_config('bbb_webcamsOnlyForModerator')) {
            $options_arr['webcamsOnlyForModerator'] = 1;
        }
        if (get_config('bbb_DisablePrivateChat')) {
            $options_arr['lockSettingsDisablePrivateChat'] = 1;
        }
        if (get_config('bbb_DisablePublicChat')) {
            $options_arr['lockSettingsDisablePublicChat'] = 1;
        }
        if (get_config('bbb_DisableNote')) {
            $options_arr['lockSettingsDisableNote'] = 1;
        }
        if (get_config('bbb_HideUserList')) {
            $options_arr['lockSettingsHideUserList'] = 1;
        }
        if (get_config('bbb_hideParticipants')) {
            $options_arr['hideParticipants'] = 1;
        }
        if (count($options_arr) > 0) {
            $options = serialize($options_arr);
        } else {
            $options = NULL;
        }
        $record = 'false';

        $t = Database::get()->querySingle("SELECT tc_servers.id FROM tc_servers JOIN course_external_server
                                                    ON tc_servers.id = external_server
                                                    WHERE course_id = ?d
                                                        AND`type` = ?s
                                                        AND enabled = 'true'
                                                    ORDER BY weight
                                                        ASC", $cid, $tc_type);
        if ($t) {
            $server_id = $t->id;
        } else { // else course will use default tc_server
            $server_id = Database::get()->querySingle("SELECT id FROM tc_servers WHERE `type` = ?s and enabled = 'true' ORDER BY weight ASC", $tc_type)->id;
        }

        $participants_users = Database::get()->queryArray("SELECT participants FROM mod_session_users WHERE session_id = ?d AND is_accepted = ?d",$sid,1);
        $r_group = '';
        foreach ($participants_users as $group) {
            $r_group .= $group->participants .',';
        }
        $r_group = mb_substr($r_group, 0, -1);
        if(count($participants_users) == 0){
            $r_group .= '0';
        }
        $q = Database::get()->query("INSERT INTO tc_session SET course_id = ?d,
                                                        title = ?s,
                                                        description = ?s,
                                                        start_date = ?t,
                                                        end_date = ?t,
                                                        public = 1,
                                                        active = ?s,
                                                        running_at = ?d,
                                                        meeting_id = ?s,
                                                        mod_pw = ?s,
                                                        att_pw = ?s,
                                                        unlock_interval = ?s,
                                                        external_users = ?s,
                                                        participants = ?s,
                                                        record = ?s,
                                                        sessionUsers = ?s,
                                                        options = ?s,
                                                        id_session = ?d",
            $cid, $title, $desc, $start, null, $status, $server_id,
            generateRandomString(), generateRandomString(), generateRandomString(),
            $minutes_before, $external_users, $r_group, $record, $sessionUsers, $options, $sid);

            //insert tc in session resourses
            if($q){
                $comments = '';
                $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM session_resources WHERE session_id = ?d", $sid)->maxorder;
                $order = $order + 1;
                $Insert = Database::get()->query("INSERT INTO session_resources SET
                                            session_id = ?d,
                                            type = 'tc',
                                            title = ?s,
                                            comments = ?s,
                                            visible = 1,
                                            `order` = ?d,
                                            `date` = " . DBHelper::timeAfter() . ",
                                            res_id = ?d", $sid, $title, $comments, $order, $q->lastInsertID);
                if($Insert){
                    return true;
                }
            }
    }else{
        return false;
    }
}



/**
 * @brief add submitted document db entries in certificate criterion
 * @param type $element
 * @param type $element_id
 * @return type
 */
function add_submitted_document_to_certificate($element, $element_id) {

    if (isset($_POST['document'])) {
        foreach ($_POST['document'] as $data) {
            $is_dir_or_zip = Database::get()->querySingle("SELECT format FROM document WHERE id = ?d",$data);
            if($is_dir_or_zip->format != '.dir' && $is_dir_or_zip->format != 'zip'){
                Database::get()->query("INSERT INTO {$element}_criterion
                                        SET $element = ?d,
                                        module= " . MODULE_ID_DOCS . ",
                                        resource = ?d,
                                        activity_type = 'document-submit'", $element_id, $data);
            }
        }
    }
    return;
}


/**
 * @brief Add a passage to session space
 * @param $sid
 */
function passage_insertion($sid){
    global $course_code, $course_id, $tool_content, $langInsertPassage, $langSubmit, $langImgFormsDes, $langForm;

    $tool_content .= "
    <div class='d-lg-flex gap-4 mt-4'>
        <div class='flex-grow-1'>
            <div class='form-wrapper form-edit rounded'>
                <form role='form' class='form-horizontal' action='$_SERVER[SCRIPT_NAME]?course=$course_code&session=$sid' method='post'>
                    <fieldset>
                        <legend class='mb-0' aria-label='$langForm'></legend>
                        <div class='form-group'>
                            <label for='add_passage' class='col-12 control-label-notes'>$langInsertPassage</label>
                            " . rich_text_editor('add_passage', 5, 40, '') . "
                        </div>";


      $tool_content .= "<div class='form-group mt-5'>
                            <div class='col-12 d-flex justify-content-end aling-items-center'>
                                <input class='btn submitAdminBtn' type='submit' name='submit_passage' value='$langSubmit'>
                            </div>
                        </div>

                        " . generate_csrf_token_form_field() . "

                    </fieldset>
                </form>
            </div>
        </div>
        <div class='d-none d-lg-block'>
            <img class='form-image-modules' src='" . get_form_image() . "' alt='$langImgFormsDes'>
        </div>
    </div>";

    return $tool_content;
}



/**
 * @brief insert passage resource in course session resources
 * @param integer $sid
 */
function insert_session_passage($sid) {
    global $course_code, $course_id, $langText;


    if($sid){
        $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM session_resources WHERE session_id = ?d", $sid)->maxorder;
        $order++;
        $q =  Database::get()->query("INSERT INTO session_resources SET
                                        session_id = ?d,
                                        type = 'passage',
                                        title = '$langText',
                                        visible = 1,
                                        `order` = ?d,
                                        `date` = " . DBHelper::timeAfter() . ", passage = ?s", $sid, $order, $_POST['add_passage']);


    }

    header('Location: session_space.php?course=' . $course_code . '&session=' . $sid);
    exit;
}


/**
 * @brief delete the badge of user in a session
 * @param integer $u
 * @param integer $sid
 */
function user_badge_deletion($u,$sid){

    global $course_id;

    Database::get()->query("DELETE FROM session_resources WHERE session_id = ?d AND from_user = ?d", $sid, $u);

    Database::get()->query("DELETE FROM user_badge_criterion
                            WHERE user = ?d
                            AND badge_criterion IN
                                (SELECT id FROM badge_criterion WHERE badge IN
                                (SELECT id FROM badge WHERE course_id = ?d AND session_id = ?d))", $u, $course_id, $sid);

    Database::get()->query("DELETE FROM user_badge
                            WHERE user = ?d
                            AND badge IN (SELECT id FROM badge WHERE course_id = ?d AND session_id = ?d)", $u, $course_id, $sid);
}

/**
 * @brief check out if resource exists into current session.
 * @param integer $rid
 * @param integer $sid
 */
function session_resource_exists($rid,$sid){
    $result = Database::get()->querySingle("SELECT * FROM session_resources WHERE id = ?d AND session_id = ?d",$rid,$sid);
    if($result){
        return true;
    }else{
        return false;
    }
}

/**
 * @brief All resources which participate in a session.
 * @param integer $sid
 * @param integer $cid
 */
function session_completed_resources_by_user($sid,$cid,$user){

    global $langResourceAsActivity, $langCompletedSessionMeeting, $langCompletedSessionWithoutActivity, 
            $langCommentsByConsultant, $langAttendance, $langAutomaticCompletion, $langWithAttendanceRegistrationByConsultant;

    $html = "";
    $criteria = Database::get()->queryArray("SELECT * FROM badge_criterion
                                                        WHERE badge IN (SELECT id FROM badge
                                                                    WHERE course_id = ?d AND session_id = ?d)",$cid,$sid);                                                              

    if (count($criteria)) {
        $html .= "<div class='resources_list' style='padding: 0px;'>";
        foreach ($criteria as $c) {
            $resource_info = "";
            if ($c->activity_type == 'document-submit') {
                $info_cr = database::get()->querySingle("SELECT title FROM session_resources
                    WHERE session_id = ?d
                    AND res_id = ?d
                    AND type = ?s",$sid,$c->resource,'doc');

                $completed_cr = database::get()->querySingle("SELECT * FROM session_resources
                                                                WHERE session_id = ?d
                                                                AND doc_id = ?d
                                                                AND from_user = ?d
                                                                AND type = ?s
                                                                AND is_completed = ?d",$sid,$c->resource,$user,'doc',1);

                $comments_by_consultant = "";
                if ($completed_cr) {
                    if (!empty($completed_cr->deliverable_comments)) {
                        $comments_by_consultant = "<div style='margin-left:20px; margin-top:5px;'><strong style='text-decoration: underline;'>$langCommentsByConsultant</strong><ul><li>" . $completed_cr->deliverable_comments . "</li></ul></div>";
                    }
                    $resource_info = "<div class='d-flex justify-content-start align-items-start gap-2'>
                                            <div class='Success-200-cl'>&#10004;</div>" .
                                            "<div>" . $info_cr->title . $comments_by_consultant . "</div>
                                      </div>";
                } else {
                    $resource_info = "<div class='d-flex justify-content-start align-items-start gap-2'>
                                            <div class='Accent-200-cl'>&#x2718;</div>" .
                                            "<div>" . $info_cr->title . $comments_by_consultant . "</div>
                                      </div>";
                }

            } elseif ($c->activity_type == 'tc-completed') {
                $info_cr = database::get()->querySingle("SELECT title FROM session_resources
                    WHERE session_id = ?d
                    AND type = ?s
                    AND res_id = ?d",$sid,'tc',$c->resource);

                $completed_cr = database::get()->querySingle("SELECT * FROM user_badge_criterion
                                                                WHERE user = ?d
                                                                AND badge_criterion = ?d",$user,$c->id);

                if($completed_cr){
                    $resource_info = "  <div class='d-flex justify-content-start align-items-start gap-2'>
                                            <div class='Success-200-cl'>&#10004;</div>" .
                                            "<div>" . $info_cr->title . "</div>
                                        </div>";
                }else{
                    $resource_info = "  <div class='d-flex justify-content-start align-items-start gap-2'>
                                            <div class='Accent-200-cl'>&#x2718;</div>" .
                                            "<div>" . $info_cr->title . "</div>
                                        </div>";
                }

            }elseif ($c->activity_type == 'meeting-completed') {
                $completed_cr = database::get()->querySingle("SELECT * FROM user_badge_criterion
                                                                WHERE user = ?d
                                                                AND badge_criterion = ?d",$user,$c->id);

                if ($completed_cr) {
                    $resource_info = "  <div class='d-flex justify-content-start align-items-start gap-2'>
                                            <div class='Success-200-cl'>&#10004;</div>" .
                                            "<div>" . "$langCompletedSessionMeeting" . "</div>
                                        </div>";
                } else {
                    $resource_info = "  <div class='d-flex justify-content-start align-items-start gap-2'>
                                            <div class='Accent-200-cl'>&#x2718;</div>" .
                                            "<div>" . "$langCompletedSessionMeeting" . "</div>
                                        </div>";
                }

            } elseif ($c->activity_type == 'noactivity') {
                $completed_cr = database::get()->querySingle("SELECT * FROM user_badge_criterion
                                                                WHERE user = ?d
                                                                AND badge_criterion = ?d",$user,$c->id);

                if ($completed_cr) {
                    $resource_info = "  <div class='d-flex justify-content-start align-items-start gap-2'>
                                            <div class='Success-200-cl'>&#10004;</div>" .
                                            "<div>" . "$langCompletedSessionWithoutActivity" . "</div>
                                        </div>";
                } else {
                    $resource_info = "  <div class='d-flex justify-content-start align-items-start gap-2'>
                                            <div class='Accent-200-cl'>&#x2718;</div>" .
                                            "<div>" . "$langCompletedSessionWithoutActivity" . "</div>
                                        </div>";
                }

            }elseif ($c->activity_type == 'consultant-completion') {
                $completed_cr = database::get()->querySingle("SELECT * FROM user_badge_criterion
                                                                WHERE user = ?d
                                                                AND badge_criterion = ?d",$user,$c->id);

                if ($completed_cr) {
                    $resource_info = "  <div class='d-flex justify-content-start align-items-start gap-2'>
                                            <div class='Success-200-cl'>&#10004;</div>" .
                                            "<div>" . "$langWithAttendanceRegistrationByConsultant" . "</div>
                                        </div>";
                } else {
                    $resource_info = "  <div class='d-flex justify-content-start align-items-start gap-2'>
                                            <div class='Accent-200-cl'>&#x2718;</div>" .
                                            "<div>" . "$langWithAttendanceRegistrationByConsultant" . "</div>
                                        </div>";
                }

            }elseif ($c->activity_type == 'questionnaire') {
                $pollItem = Database::get()->querySingle("SELECT `name` FROM poll WHERE pid = ?d AND course_id = ?d", $c->resource, $cid);
                $completed_cr = database::get()->querySingle("SELECT * FROM user_badge_criterion
                                                                WHERE user = ?d
                                                                AND badge_criterion = ?d
                                                                AND user IN (SELECT uid FROM poll_user_record 
                                                                                WHERE session_id = ?d)", $user, $c->id, $sid);

                if ($completed_cr) {
                    $resource_info = "  <div class='d-flex justify-content-start align-items-start gap-2'>
                                            <div class='Success-200-cl'>&#10004;</div>" .
                                            "<div>" . q($pollItem->name) . "</div>
                                        </div>";
                } else {
                    $resource_info = "  <div class='d-flex justify-content-start align-items-start gap-2'>
                                            <div class='Accent-200-cl'>&#x2718;</div>" .
                                            "<div>" . q($pollItem->name) . "</div>
                                        </div>";
                }
            }elseif ($c->activity_type == 'autocomplete') {
                $completed_cr = database::get()->querySingle("SELECT * FROM user_badge_criterion
                                                                WHERE user = ?d
                                                                AND badge_criterion = ?d",$user,$c->id);

                if ($completed_cr) {
                    $resource_info = "  <div class='d-flex justify-content-start align-items-start gap-2'>
                                            <div class='Success-200-cl'>&#10004;</div>" .
                                            "<div>" . "$langAutomaticCompletion" . "</div>
                                        </div>";
                } else {
                    $resource_info = "  <div class='d-flex justify-content-start align-items-start gap-2'>
                                            <div class='Accent-200-cl'>&#x2718;</div>" .
                                            "<div>" . "$langAutomaticCompletion" . "</div>
                                        </div>";
                }

            }
            $html .= "<div class='resource_item' style='margin-bottom:5px;'>$resource_info<div class='bottom-split'></div></div>";
        }
        $html .= "</div>";
    }

    return $html;
}

/**
 * @brief Display the completed resources by a user in a session.
 * @param integer $sid
 * @param integer $cid
 * @param integer $user
 */
function show_completed_resources($sid,$cid,$user){

    global $langCompletedSessionMeeting, $langCompletedResources, $langCompletedSessionWithoutActivity, $langAttendance;

    $html = "";
    $badge = Database::get()->querySingle("SELECT id FROM badge WHERE course_id = ?d AND session_id = ?d",$cid,$sid);
    if($badge){

        // Completed criteria by a user
        $completed_criteria = Database::get()->queryArray("SELECT * FROM badge_criterion
                                                            WHERE badge = ?d
                                                            AND id IN (SELECT badge_criterion FROM user_badge_criterion
                                                                        WHERE user = ?d)",$badge->id,$user);

        // Display info regarding completed criteria
        if(count($completed_criteria)){
            $html .= "<strong>$langCompletedResources</strong>";
            $html .= "<ul>";
            foreach($completed_criteria as $c){
                $resource_info = "";
                if($c->activity_type == 'document-submit'){
                    $info_cr = database::get()->querySingle("SELECT doc_id FROM session_resources
                                                                WHERE session_id = ?d
                                                                AND doc_id = ?d
                                                                AND from_user = ?d
                                                                AND type = ?s
                                                                AND is_completed = ?d",$sid,$c->resource,$user,'doc',1);

                    $resource_info = Database::get()->querySingle("SELECT title FROM session_resources WHERE res_id = ?d",$info_cr->doc_id)->title;

                }elseif($c->activity_type == 'tc-completed'){
                    $info_cr = database::get()->querySingle("SELECT title FROM session_resources
                                                                WHERE session_id = ?d
                                                                AND doc_id = ?d
                                                                AND from_user = ?d
                                                                AND type = ?s
                                                                AND is_completed = ?d
                                                                AND res_id = ?d",$sid,0,0,'tc',0,$c->resource);

                    $resource_info = $info_cr->title;

                }elseif($c->activity_type == 'meeting-completed'){
                    $resource_info = "$langCompletedSessionMeeting";
                }elseif($c->activity_type == 'noactivity'){
                    $resource_info = "$langCompletedSessionWithoutActivity";
                }elseif($c->activity_type == 'consultant-completion'){
                    $resource_info = "$langAttendance";
                }elseif($c->activity_type == 'autocomplete'){
                    $resource_info = "$langAutomaticCompletion";
                }
                $html .= "<li>$resource_info</li>";
            }
            $html .= "</ul>";

            return $html;
        }
    }else{
        return;
    }
}


function informConsultantAboutDeliverable($sid,$fromSimpleUser,$aboutDeliverable){

    require_once 'include/sendMail.inc.php';

    global $course_id, $course_code, $langSubmittedUploadedFile,
            $langUploadDeliverableFromUser, $langStudent, $langProblem,
            $langManager, $siteName, $langEmail, $langAboutDeliverable, $langTel;

    $consultantInfo = Database::get()->querySingle("SELECT email,verified_mail FROM user
                                                    WHERE id IN (SELECT creator FROM mod_session
                                                    WHERE id = ?d AND course_id = ?d)", $sid, $course_id);

    if($consultantInfo->verified_mail){
        $course_title = course_id_to_title($course_id);
        $session_title = title_session($course_id,$sid);

        $simpleUsertInfo = Database::get()->querySingle("SELECT givenname,surname FROM user
                                                     WHERE id = ?d", $fromSimpleUser);

        $deliverableInfo = Database::get()->querySingle("SELECT title FROM session_resources WHERE id = ?d AND session_id = ?d", $aboutDeliverable, $sid);

        $emailHeader = "
                <!-- Header Section -->
                <div id='mail-header'>
                    <br>
                    <div>
                        <div id='header-title'>$langSubmittedUploadedFile&nbsp;&nbsp;<span>($session_title&nbsp; | &nbsp;$course_title)</span></div>
                    </div>
                </div>";

        $emailMain = "
                <!-- Body Section -->
                <div id='mail-body'>
                    <br>
                    <div>$langUploadDeliverableFromUser</div>
                    <div id='mail-body-inner'>
                        <ul id='forum-category'>
                            <li>
                                <span><b>$langStudent: </b></span>
                                <span>$simpleUsertInfo->givenname&nbsp;$simpleUsertInfo->surname</span>
                            </li>
                            <li>
                                <span><b>$langAboutDeliverable: </b></span>
                                <span>$deliverableInfo->title</span>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <br>
                        <p>$langProblem</p><br>" . get_config('admin_name') . "
                        <ul id='forum-category'>
                            <li>$langManager: $siteName</li>
                            <li>$langTel: -</li>
                            <li>$langEmail: " . get_config('email_helpdesk') . "</li>
                        </ul>
                    </div>
                </div>";

        $emailsubject = $siteName;

        $emailbody = $emailHeader.$emailMain;

        $emailPlainBody = html2text($emailbody);

        $emailUser = $consultantInfo->email;

        send_mail_multipart('', '', '', $emailUser, $emailsubject, $emailPlainBody, $emailbody);

    }
}


/**
 * @brief Reference creation from a counselor in a session using file uploading.
 * @param integer $sid
 */
function reference_creation_by_uploaded_file($sid){

    global $webDir, $course_id, $is_consultant, $langEmptyUploadFile, $course_code,
            $langFileExists, $uid, $language, $langDownloadEnd;

    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

    if($_FILES['file_reference_upload']['error'] > 0) {
        // cover_image is empty (and not an error), or no file was uploaded
        Session::flash('message',$langEmptyUploadFile);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page("modules/session/resource.php?course=".$course_code."&session=".$sid."&type=doc_reference");
    }

    // If uploaded file exists do not continue.
    // This uploaded file does not refer to deliverable but refers to resource of a session.
    if(isset($_FILES['file_reference_upload']['name'])){
        $path_exists = Database::get()->queryArray("SELECT * FROM document
                                                    WHERE course_id = ?d
                                                    AND subsystem = ?d
                                                    AND subsystem_id = ?d
                                                    AND filename = ?s",
                                                    $course_id, SESSION_REFERENCE, $sid, $_FILES['file_reference_upload']['name']);

        if (count($path_exists) > 0) {
            Session::flash('message',$langFileExists);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page("modules/session/resource.php?course=".$course_code."&session=".$sid."&type=doc_reference");
        }
    }


    // upload attached file
    if (isset($_FILES['file_reference_upload']) and is_uploaded_file($_FILES['file_reference_upload']['tmp_name'])) { // upload comments file
        $session_filename = $_FILES['file_reference_upload']['name'];
        validateUploadedFile($session_filename); // check file type
        $session_filename = add_ext_on_mime($session_filename);
        // File name used in file system and path field
        $safe_session_filename = safe_filename(get_file_extension($session_filename));
        $session_dir = "$webDir/courses/$course_code/session/session_$sid/";
        if (!file_exists($session_dir)) {
            mkdir("$webDir/courses/$course_code/session/session_$sid/", 0755, true);
        }

        $spathfile = "$webDir/courses/$course_code/session/session_$sid/$safe_session_filename";

        if (move_uploaded_file($_FILES['file_reference_upload']['tmp_name'], $spathfile)) {
            @chmod($spathfile, 0644);
            $session_real_filename = $_FILES['file_reference_upload']['name'];
            $session_filepath = '/' . $safe_session_filename;
        }

        $file_creator = "$_SESSION[givenname] $_SESSION[surname]";
        $file_date = date('Y-m-d G:i:s');

        $info_file = pathinfo($session_filename);
        $title = $info_file['filename'];
        $comments = null;

        $doc_inserted = Database::get()->query("INSERT INTO document SET
            course_id = ?d,
            subsystem = ?d,
            subsystem_id = ?d,
            path = ?s,
            extra_path = '',
            filename = ?s,
            visible = 1,
            comment = ?s,
            category = 0,
            title = ?s,
            creator = ?s,
            date = ?s,
            date_modified = ?s,
            subject = '',
            description = '',
            author = ?s,
            format = ?s,
            language = ?s,
            copyrighted = 0,
            editable = 0,
            lock_user_id = ?d",
                $course_id, SESSION_REFERENCE, $sid, $session_filepath,
                $session_real_filename, $comments, $title, $file_creator,
                $file_date, $file_date, $file_creator, get_file_extension($session_filename),
                $language, $uid);

        $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM session_resources WHERE session_id = ?d", $sid)->maxorder;
        $order = $order+1;

        $q = Database::get()->query("INSERT INTO session_resources SET
                                    session_id = ?d,
                                    type = 'doc_reference',
                                    title = ?s,
                                    comments = ?s,
                                    visible = 0,
                                    `order` = ?d,
                                    `date` = " . DBHelper::timeAfter() . ",
                                    res_id = ?d", $sid, $title, $comments, $order, $doc_inserted->lastInsertID);

        Session::flash('message',$langDownloadEnd);
        Session::flash('alert-class', 'alert-success');

    }else {
        Session::flash('message',$langDownloadNotEnd);
        Session::flash('alert-class', 'alert-danger');
    }

    redirect_to_home_page("modules/session/session_space.php?course=".$course_code."&session=".$sid);
}


/**
 * @brief Reference creation from a counselor in a session using fields.
 * @param integer $sid
 */
function reference_creation_by_fields($sid){

    global $webDir, $course_id, $is_consultant, $langEmptyUploadFile, $course_code,
            $langFileExists, $uid, $language, $langDownloadEnd, $langFieldsMissing;

    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

    if(empty($_POST['title_reference']) or empty($_POST['content_reference'])){
        Session::flash('message',$langFieldsMissing);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page("modules/session/resource.php?course=".$course_code."&session=".$sid."&type=doc_reference");
    }

    // If uploaded file exists do not continue.
    // This uploaded file does not refer to deliverable but refers to resource of a session.
    $checkfilename = q($_POST['title_reference']) . ".txt";
    $path_exists = Database::get()->queryArray("SELECT * FROM document
                                                WHERE course_id = ?d
                                                AND subsystem = ?d
                                                AND subsystem_id = ?d
                                                AND filename = ?s",
                                                $course_id, SESSION_REFERENCE, $sid, $checkfilename);

    if (count($path_exists) > 0) {
        Session::flash('message',$langFileExists);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page("modules/session/resource.php?course=".$course_code."&session=".$sid."&type=doc_reference");
    }


    $sdir = "$webDir/courses/$course_code/session/session_$sid/";
    if (!file_exists($sdir)) {
        mkdir("$webDir/courses/$course_code/session/session_$sid/", 0755, true);
    }

    $order = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM session_resources WHERE session_id = ?d", $sid)->maxorder;
    $order = $order+1;
    $title = q($_POST['title_reference']);
    if(empty($title)){
        $title = "$langTool" . "_$sid";
    }
    $comments = strip_tags($_POST['content_reference']) ?? '';
    $textfile = fopen("$webDir/courses/$course_code/session/session_$sid/$title.txt", "w") or die("Unable to open file!");
    $txt = "$comments";
    fwrite($textfile, $txt);
    fclose($textfile);

    $file_creator = "$_SESSION[givenname] $_SESSION[surname]";
    $file_date = date('Y-m-d G:i:s');

    $safe_filename = safe_filename(get_file_extension("$title.txt"));
    $s_real_filename = "$title.txt";
    $sfilepath = '/' . $safe_filename;
    $ses_filename = add_ext_on_mime($s_real_filename);
    rename("$webDir/courses/$course_code/session/session_$sid/$title.txt","$webDir/courses/$course_code/session/session_$sid/$safe_filename");
    $comments = null;

    $upload_file = Database::get()->query("INSERT INTO document SET
        course_id = ?d,
        subsystem = ?d,
        subsystem_id = ?d,
        path = ?s,
        extra_path = '',
        filename = ?s,
        visible = 1,
        comment = ?s,
        category = 0,
        title = ?s,
        creator = ?s,
        date = ?s,
        date_modified = ?s,
        subject = '',
        description = '',
        author = ?s,
        format = ?s,
        language = ?s,
        copyrighted = 0,
        editable = 0,
        lock_user_id = ?d",
            $course_id, SESSION_REFERENCE, $sid, $sfilepath,
            $s_real_filename, $comments, $title, $file_creator,
            $file_date, $file_date, $file_creator, get_file_extension($ses_filename),
            $language, $uid);


    Database::get()->query("INSERT INTO session_resources SET
                            session_id = ?d,
                            type = 'doc_reference',
                            title = ?s,
                            comments = ?s,
                            visible = 0,
                            `order` = ?d,
                            `date` = " . DBHelper::timeAfter() . ",
                            res_id = ?d", $sid, $title, $comments, $order, $upload_file->lastInsertID);

    Session::flash('message',$langDownloadEnd);
    Session::flash('alert-class', 'alert-success');

    redirect_to_home_page("modules/session/session_space.php?course=".$course_code."&session=".$sid);
}


/**
 * @brief session completion checker for automatic completion type
 * @param int $session_id
 */
function check_session_completion_without_activities($session_id = 0){
    global $course_id;

    if ($session_id > 0) {
        $badge = Database::get()->querySingle("SELECT id,badge FROM badge_criterion
                                                WHERE activity_type = ?s
                                                AND badge IN (SELECT id FROM badge 
                                                                WHERE course_id = ?d AND session_id = ?d)",'noactivity',$course_id,$session_id);

        if ($badge) {
            $badge_id = $badge->badge;
            $badge_criterion_id = $badge->id;
            $result = Database::get()->queryArray("SELECT id,user FROM user_badge WHERE badge = ?d", $badge_id);
            if (count($result) > 0) {
                foreach ($result as $r) {
                    $existUser = Database::get()->querySingle("SELECT id FROM user_badge_criterion WHERE user = ?d AND badge_criterion = ?d", $r->user, $badge_criterion_id);
                    if (!$existUser) {
                        Database::get()->query("INSERT INTO user_badge_criterion SET user = ?d, `created` = " . DBHelper::timeAfter() . ", badge_criterion = ?d", $r->user, $badge_criterion_id);
                        Database::get()->query("UPDATE user_badge SET completed = 1, completed_criteria = 1, total_criteria = 1 
                                                    WHERE user = ?d AND badge = ?d",$r->user, $badge_id);
                    }
                }
            }                                   
        }
    }
}

function session_completion_automatically($element, $element_id, $session_id = 0, $session_resource_id = 0){
    global $course_code, $langResourceAddedWithSuccess, $course_id, $langResourceExists;

    if($session_id){
        // check if badge exists
        $ch = Database::get()->querySingle("SELECT id FROM {$element}_criterion
                                            WHERE badge = ?d
                                            AND activity_type = ?s", $element_id,'autocomplete');

        if(!$ch){
            Database::get()->query("INSERT INTO {$element}_criterion
                                    SET $element = ?d,
                                    activity_type = ?s",$element_id, 'autocomplete');

            Session::flash('message',$langResourceAddedWithSuccess);
            Session::flash('alert-class', 'alert-success');
        }else{
            Session::flash('message',$langResourceExists);
            Session::flash('alert-class', 'alert-danger');
        }

        redirect_to_home_page('modules/session/complete.php?course=' . $course_code . '&manage=1&session=' . $session_id);

    }else{
        redirect_to_home_page("courses/$course_code/");
    }
}

function check_session_completion_with_expired_time($sid){
    global $course_id, $course_code;

    if($sid){
        $badge = Database::get()->querySingle("SELECT id,badge FROM badge_criterion
                                                    WHERE activity_type = ?s
                                                    AND badge IN (SELECT id FROM badge 
                                                                    WHERE course_id = ?d AND session_id = ?d)",'autocomplete',$course_id,$sid);


        $participants = session_participants_ids($sid);
        if(session_has_expired($course_id,$sid)){
            if(count($participants) > 0 && $badge){
                $badge_id = $badge->badge;
                $badge_criterion_id = $badge->id;
                foreach($participants as $p){
                    $result = Database::get()->queryArray("SELECT id FROM user_badge WHERE badge = ?d AND user = ?d", $badge_id, $p);
                    if (count($result) > 0) {
                        $existUser = Database::get()->querySingle("SELECT id FROM user_badge_criterion WHERE user = ?d AND badge_criterion = ?d", $p, $badge_criterion_id);
                        if (!$existUser) {
                            Database::get()->query("INSERT INTO user_badge_criterion SET user = ?d, `created` = " . DBHelper::timeAfter() . ", badge_criterion = ?d", $p, $badge_criterion_id);
                            Database::get()->query("UPDATE user_badge SET completed_criteria = completed_criteria + 1 WHERE user = ?d AND badge = ?d",$p, $badge_id);
                        }
                    }
                }
            }
        }else{
            if(count($participants) > 0 && $badge){
                $badge_id = $badge->badge;
                $badge_criterion_id = $badge->id;
                foreach($participants as $p){
                    $result = Database::get()->queryArray("SELECT id FROM user_badge WHERE badge = ?d AND user = ?d", $badge_id, $p);
                    if (count($result) > 0) {
                        $existUser = Database::get()->querySingle("SELECT id FROM user_badge_criterion WHERE user = ?d AND badge_criterion = ?d", $p, $badge_criterion_id);
                        if ($existUser) {
                            Database::get()->query("DELETE FROM user_badge_criterion WHERE user = ?d AND badge_criterion = ?d", $p, $badge_criterion_id);
                            Database::get()->query("UPDATE user_badge SET completed_criteria = completed_criteria - 1 WHERE user = ?d AND badge = ?d",$p, $badge_id);
                        }
                    }
                }
            }
        }
    }else{
        redirect_to_home_page("courses/$course_code/");
    }
}


/**
 * @brief Update participants on video teleconference.
 * @param integer $sid
 */
function update_tc_participants($sid){
    global $course_id;

    $existTc = Database::get()->querySingle("SELECT id FROM tc_session WHERE course_id = ?d AND id_session = ?d", $course_id, $sid);
    if($existTc){
        $participants = session_participants_ids($sid);
        $r_group = '';
        if(count($participants) > 0){
            foreach ($participants as $p) {
                $r_group .= $p .',';
            }
            $r_group = mb_substr($r_group, 0, -1);
            Database::get()->querySingle("UPDATE tc_session SET participants = ?s WHERE course_id = ?d AND id_session = ?d", $r_group, $course_id, $sid);
        }else{
            $r_group .= NULL;
            Database::get()->querySingle("UPDATE tc_session SET participants = ?s WHERE course_id = ?d AND id_session = ?d", $r_group, $course_id, $sid);
        }
    }
}