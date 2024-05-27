<?php

/* ========================================================================
 * Open eClass
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2018  Greek Universities Network - GUnet
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
 * ========================================================================
 */

 
function is_tutor_course($cid,$userId){
    $result = Database::get()->querySingle("SELECT tutor FROM course_user WHERE course_id = ?d AND user_id = ?d",$cid,$userId);
    return $result->tutor;
}

function is_consultant($cid,$userId){
    $result = Database::get()->querySingle("SELECT editor FROM course_user WHERE course_id = ?d AND user_id = ?d",$cid,$userId);
    return $result->editor;
}

function title_session($cid,$sid){
    $result = Database::get()->querySingle("SELECT title FROM mod_session WHERE id = ?d AND course_id = ?d",$sid,$cid);
    return $result->title;
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
 * @brief insert docs in database
 * @param integer $sid
 */
function insert_session_docs($sid) {
    global $webDir, $course_id, $course_code, $group_sql, $subsystem, $subsystem_id, $basedir;

    if(isset($_POST['document'])){
        if ($sid == -1) { // Insert common documents into main documents
            $target_dir = '';
            if (isset($_POST['dir']) and !empty($_POST['dir'])) {
                // Make sure specified target dir exists in course
                $target_dir = Database::get()->querySingle("SELECT path FROM document
                                            WHERE course_id = ?d AND
                                                  subsystem = " . MAIN . " AND
                                                  path = ?s", $course_id, $_POST['dir']->path);
            }

            foreach ($_POST['document'] as $file_id) {
                $file = Database::get()->querySingle("SELECT * FROM document
                                            WHERE course_id = -1
                                            AND subsystem = " . COMMON . "
                                            AND id = ?d", $file_id);
                if ($file) {
                    $subsystem = MAIN;
                    $subsystem_id = 'NULL';
                    $group_sql = "course_id = $course_id AND subsystem = " . MAIN;
                    $basedir = $webDir . '/courses/' . $course_code . '/document';
                    insert_common_docs($file, $target_dir);
                }
            }
            header('Location: ../document/index.php?course=' . $course_code . '&openDir=' . $target_dir);
            exit;
        }

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



/**
 * @brief Display resources for unit with id=$id
 * @global type $tool_content
 * @global type $max_resource_id
 * @param type $sid
 */
function show_session_resources($sid)
{
    global $max_resource_id,
           $head_content, $langDownload, $langPrint, $langCancel,
           $langFullScreen, $langNewTab, $langActInHome, $langActInClass, $langActAfterClass, $course_code, $langNoAvailableSessionRecourses;

        $html = '';

        $req = Database::get()->queryArray("SELECT * FROM session_resources WHERE session_id = ?d AND `order` >= 0 ORDER BY `order`", $sid);

        if (count($req) > 0) {
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
                                        '<div class=\"iframe-container\" style=\"height:500px;\"><iframe id=\"fileFrame\" src=\"'+fileURL+'\" style=\"width:100%; height:500px;\"></iframe></div>'+
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

    global $langUnknownResType, $is_editor;

    $html = '';
    if ($info->visible == 0 and $info->type != 'doc' and ! $is_editor) { // special case handling for old unit resources with type 'doc' .
        return;
    }
    switch ($info->type) {
        case 'doc':
            $html .= show_session_doc($info->title, $info->comments, $info->id, $info->res_id);
            break;
        case 'work':
            $html .= show_session_work($info->title, $info->comments, $info->id, $info->res_id, $info->visible);
            break;
        case 'tc':
            $html .= show_session_tc($info->title, $info->comments, $info->id, $info->res_id, $info->visible);
            break;
        default:
            $html .= $langUnknownResType;
    }
   
    return $html;
}


/**
 * @brief display resource documents
 * @return string
 */
function show_session_doc($title, $comments, $resource_id, $file_id) {
    global $can_upload, $course_id, $langWasDeleted, $urlServer,
           $id, $course_code, $langResourceBelongsToUnitPrereq;

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
        // if ($can_upload) {
        //     if (resource_belongs_to_unit_completion($_GET['id'], $file->id)) {
        //         $res_prereq_icon = icon('fa-star', $langResourceBelongsToUnitPrereq);
        //     }
        // }

        $status = $file->visible;
        if (!$can_upload and (!resource_access($file->visible, $file->public))) {
            return '';
        }
        if ($file->format == '.dir') {
            $image = 'fa-folder-open';
            $download_hidden_link = '';
            $link = "<a href='{$urlServer}modules/document/index.php?course=$course_code&amp;openDir=$file->path&amp;unit=$_GET[session]'>" .
                q($title) . "</a>";
        } else {
            $file->title = $title;
            $image = choose_image('.' . $file->format);
            $download_url = "{$urlServer}modules/document/index.php?course=$course_code&amp;download=$file->path";
            $download_hidden_link = ($can_upload || visible_module(MODULE_ID_DOCS))?
                "<input type='hidden' value='$download_url'>" : '';
            $file_obj = MediaResourceFactory::initFromDocument($file);
            $file_obj->setAccessURL(file_url($file->path, $file->filename));
            $file_obj->setPlayURL(file_playurl($file->path, $file->filename));
            $link = MultimediaHelper::chooseMediaAhref($file_obj);
        }
    }
    $class_vis = ($status == '0' or $status == 'del') ? ' class="not_visible"' : '';
    if (!empty($comments)) {
        $comment = '<br />' . $comments;
    } else {
        $comment = '';
    }

    return "
        <tr$class_vis data-id='$resource_id'>
          <td width='1'>" . icon($image, '') . "</td>
          <td class='text-start'>$download_hidden_link $link $res_prereq_icon $comment</td>" .
          session_actions('doc', $resource_id, $status) .
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

    global $urlServer, $is_editor, $uid, $m, $langResourceBelongsToUnitPrereq,
            $langWasDeleted, $course_id, $course_code, $langPassCode;

    $title = q($title);
    $res_prereq_icon = '';
    if ($is_editor) {
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
        if (!$is_editor) {
            return '';
        } else {
            $imagelink = icon('fa-xmark link-delete');
            $exlink = "<span class='not_visible'>$title ($langWasDeleted)</span>";
        }
    } else {
        $assign_to_users_message = '';
        if ($is_editor) {
            if ($work->assign_to_specific == 1) {
                $assign_to_users_message = "<small class='help-block'>$m[WorkAssignTo]: $m[WorkToUser]</small>";
            } else if ($work->assign_to_specific == 2) {
                $assign_to_users_message = "<small class='help-block'>$m[WorkAssignTo]: $m[WorkToGroup]</small>";
            }
            // if (resource_belongs_to_unit_completion($_GET['id'], $work_id)) {
            //     $res_prereq_icon = icon('fa-star', $langResourceBelongsToUnitPrereq);
            // }
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

        $link = "<a href='${urlServer}modules/work/index.php?course=$course_code&amp;res_type=assignment&amp;id=$work_id&amp;session=$_GET[session]' $class>";
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
          <td>$exlink $res_prereq_icon $comment_box $assign_to_users_message</td>" .
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
    global  $is_editor, $langWasDeleted, $langInactiveModule, $course_id;

    $module_visible = visible_module(MODULE_ID_TC); // checks module visibility

    if (!$module_visible and !$is_editor) {
        return '';
    }

    $tc = Database::get()->querySingle("SELECT * FROM tc_session WHERE course_id = ?d AND id = ?d", $course_id, $tc_id);
    if (!$tc) { // check if it was deleted
        if (!$is_editor) {
            return '';
        } else {
            $imagelink = icon('fa-xmark link-delete');
            $tclink = "<span class='not_visible'>" .q($title) ." ($langWasDeleted)</span>";
        }
    } else {
        if (!$is_editor and !$tc->active) {
            return '';
        }
        $tclink = q($title);
        if (!$module_visible) {
            $tclink .= " <i>($langInactiveModule)</i>";
        }
        $imagelink = icon('fa-exchange');
    }

    if (!empty($comments)) {
        $comment_box = "<br>$comments";
    } else {
        $comment_box = '';
    }
    $class_vis = (!$tc->active or !$module_visible) ?
        ' class="not_visible"' : ' ';
    return "
        <tr$class_vis data-id='$resource_id'>
          <td width='1'>$imagelink</td>
          <td>$tclink $comment_box</td>" .
        session_actions('tc', $resource_id, $visibility) . '
        </tr>';
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
    global $is_editor, $langEditChange, $langDelete,
    $langAddToCourseHome, $langConfirmDelete, $course_code,
    $langViewHide, $langViewShow, $langReorder, $langAlreadyBrowsed,
    $langNeverBrowsed, $langAddToUnitCompletion;

    $res_types_sessions_completion = ['work', 'doc', 'poll'];
    if (in_array($res_type, $res_types_sessions_completion)) {
        $res_type_to_session_compl = true;
    } else {
        $res_type_to_session_compl = false;
    }
    if (!$is_editor) {
        // if (prereq_unit_has_completion_enabled($_GET['id'])) {
        //     $activity_result = unit_resource_completion($_GET['id'], $resource_id);
        //     switch ($activity_result) {
        //         case 1: $content = "<td class='style='padding: 10px 0; width: 85px;'>
        //                             <span class='fa fa-check-circle' data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-original-title='$langAlreadyBrowsed'></span>
        //                             </td>";
        //             break;
        //         case 0:
        //             $content = "<td class='style='padding: 10px 0; width: 85px;'>
        //                         <span class='fa fa-hourglass-2' data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-original-title='$langNeverBrowsed'></span>
        //                         </td>";
        //             break;
        //         default:
        //             $content = "<td class='style='padding: 10px 0; width: 85px;'>&nbsp;</td>";
        //             break;
        //     }
        //     return $content;
        // } else {
        //     return '';
        // }
    }

    if ($res_type == 'description') {
        $icon_vis = ($status == 1) ? 'fa-send' : 'fa-send-o';
        $edit_link = "edit.php?course=$course_code&amp;id=$_GET[session]&amp;numBloc=$res_id";
    } else {
        $showorhide = ($status == 1) ? $langViewHide : $langViewShow;
        $icon_vis = ($status == 1) ? 'fa-eye-slash' : 'fa-eye';
        $edit_link = "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$_GET[session]&amp;edit=$resource_id";
    }

    //$q = Database::get()->querySingle("SELECT flipped_flag FROM course WHERE code = ?s", $course_code);
    if($is_editor){
        $content = "<td style='padding: 10px 0; width: 85px;'>
                <div class='d-flex justify-content-center gap-3'>
                    <div class='reorder-btn d-flex justify-content-center align-items-center'>
                        <span class='fa fa-arrows' data-bs-toggle='tooltip' data-bs-placement='top' title='$langReorder'></span>
                    </div>
                <div>";

        $content .= action_button(array(
                // array('title' => $langEditChange,
                //       'url' => $edit_link,
                //       'icon' => 'fa-edit',
                //       'show' => $status != 'del'),
                // array('title' => $showorhide,
                //       'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$_GET[id]&amp;vis=$resource_id",
                //       'icon' => $icon_vis,
                //       'show' => $status != 'del' and (in_array($res_type, array('text', 'video', 'forum', 'topic')) or $q->flipped_flag==2)),
                // array('title' => $langAddToCourseHome,
                //       'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$_GET[id]&amp;vis=$resource_id",
                //       'icon' => $icon_vis,
                //       'show' => $status != 'del' and in_array($res_type, array('description'))),
                // array('title' => $langAddToUnitCompletion,
                //        'url' => "manage.php?course=$course_code&amp;manage=1&amp;unit_id=$_GET[id]&amp;badge=1&add=true&amp;act=$res_type&amp;unit_res_id=$resource_id",
                //        'icon' => 'fa-star',
                //        'show' => prereq_unit_has_completion_enabled($_GET['id']) && $res_type_to_session_compl),
                array('title' => $langDelete,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$_GET[session]&amp;del=$resource_id",
                      'icon' => 'fa-xmark',
                      'confirm' => $langConfirmDelete,
                      'class' => 'delete')
            ));
    }else{
        $content = "<td></td>";
    }

    $content .= "</div></div></td>";

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


