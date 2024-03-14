<?php

/* ========================================================================
 * Open eClass
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2019  Greek Universities Network - GUnet
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


/**
 * @brief display all certificates -- initial screen
 */
function display_certificates(): void
{
    global $course_id, $tool_content, $course_code, $urlServer, $langPurge,
           $langDeleteCourseActivities, $langConfirmDelete, $is_editor,
           $langNoCertificates, $langActive, $langInactive,
           $langEditChange, $langNewCertificate, $langCertificates, $langActivate,
           $langDeactivate, $langSee, $langConfirmPurgeCert;

    // Fetch the certificate list
    $sql_cer = Database::get()->queryArray("SELECT id, title, description, active, template
                                                    FROM certificate WHERE course_id = ?d", $course_id);

    $tool_content .= "
            <div class='row'>
                <div class='col-xs-12'>
                    <div class='panel panel-default'>
                        <div class='panel-body'>
                            <div class='inner-heading'>
                                <div class='row'>
                                    <div class='col-sm-7'>
                                        <strong>$langCertificates</strong>
                                    </div>";
                                if ($is_editor) {
                                    $tool_content .= "<div class='col-sm-5 text-right'>
                                            <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;newcert=1' class='btn btn-success btn-sm'><span class='fa fa-plus'></span> &nbsp;&nbsp;&nbsp;$langNewCertificate</a>
                                    </div>";
                                }
                            $tool_content .= "</div>
                            </div>
                            <div class='res-table-wrapper'>";
    if (count($sql_cer) == 0) { // If no certificates
        $tool_content .= "<p class='text-center text-muted'>$langNoCertificates</p>";
    } else { // If there are certificates
        foreach ($sql_cer as $data) {
            $vis_status = $data->active ? "text-success" : "text-danger";
            $vis_icon = $data->active ? "fa-eye" : "fa-eye-slash";
            $status_msg = $data->active ? $langActive : $langInactive;
            $template_details = get_certificate_template($data->template);
            $template_name = key($template_details);
            $template_filename = $template_details[$template_name];
            $thumbnail_filename = preg_replace('/.html/', '_thumbnail.png', $template_filename);
            $template_thumbnail = $urlServer . CERT_TEMPLATE_PATH . $thumbnail_filename;
            $tool_content .= "
            <div class='row res-table-row'>
                <div class='col-sm-2'>
                <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$data->id&amp;preview=1' target=_blank>
                    <img style='box-shadow: 0 0 4px 1px #bbb; max-height: 50px;' class='img-responsive block-center' src='$template_thumbnail' title='$template_name'>
                </a>
                </div>
                <div class='col-sm-9'>
                    <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$data->id'>".q($data->title)."</a>
                    <div style='margin-top: 5px;'><span class='fa {$vis_icon}'></span>&nbsp;&nbsp;&nbsp;"
                    . "<span class='{$vis_status}'>$status_msg</span>
                    </div>
                </div>";
            if ($is_editor) {
                $tool_content .= "<div class='col-sm-1 text-left'>" .
                    action_button(array(
                        array('title' => $langEditChange,
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$data->id&amp;edit=1",
                            'icon' => 'fa-edit'),
                        array('title' => $data->active ? $langDeactivate : $langActivate,
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$data->id&amp;vis=" .
                                ($data->active ? '0' : '1'),
                            'icon' => $data->active ? 'fa-eye-slash' : 'fa-eye'),
                        array('title' => $langSee,
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$data->id&amp;preview=1",
                            'icon' => 'fa-search'),
                        array('title' => $langDeleteCourseActivities,
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;del_cert=$data->id",
                            'icon' => 'fa-times',
                            'class' => 'delete',
                            'confirm' => $langConfirmDelete),
                        array('title' => $langPurge,
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;purge_cert=$data->id",
                            'icon' => 'fa-times',
                            'class' => 'delete',
                            'confirm' => $langConfirmPurgeCert)
                    ))
                    . "</div>";
            }
            $tool_content .= "</div>";
        }
    }
            $tool_content .= "
                    </div>
                </div>
            </div>
        </div>
    </div>";
}


/**
 * @brief display all badges -- initial screen
 */
function display_badges(): void
{
    global $course_id, $tool_content, $course_code, $is_editor,
           $langDeleteCourseActivities, $langConfirmDelete,
           $langNoBadges, $langEditChange, $langBadges, $langPurge,
           $langActivate, $langDeactivate, $langNewBadge,
           $langActive, $langInactive, $urlServer, $langConfirmPurgeBadge;

    if ($is_editor) {
        $sql_cer = Database::get()->queryArray("SELECT id, title, description, active, icon FROM badge WHERE course_id = ?d AND bundle >= 0", $course_id);
    } else {
        $sql_cer = Database::get()->queryArray("SELECT id, title, description, active, icon FROM badge WHERE course_id = ?d AND active = 1 AND bundle >= 0 ", $course_id);
    }
        $tool_content .= "
            <div class='row'>
                <div class='col-xs-12'>
                    <div class='panel panel-default'>
                        <div class='panel-body'>
                            <div class='inner-heading'>
                                <div class='row'>
                                    <div class='col-sm-7'>
                                        <strong>$langBadges</strong>
                                    </div>";
                    if ($is_editor) {
                        $tool_content .= "<div class='col-sm-5 text-right'>
                                    <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;newbadge=1' class='btn btn-success btn-sm'><span class='fa fa-plus'></span> &nbsp;&nbsp;&nbsp;$langNewBadge</a>
                                </div>";
                    }
                    $tool_content .= "</div>
                        </div>
                    <div class='res-table-wrapper'>";

    if (count($sql_cer) == 0) { // no badges
        $tool_content .= "<p class='text-center text-muted'>$langNoBadges</p>";
    } else {
        foreach ($sql_cer as $data) {
            $vis_status = $data->active ? "text-success" : "text-danger";
            $vis_icon = $data->active ? "fa-eye" : "fa-eye-slash";
            $status_msg = $data->active ? $langActive : $langInactive;
            $badge_details = get_badge_icon($data->icon);
            $badge_name = key($badge_details);
            $badge_icon = $badge_details[$badge_name];
            $icon_link = $urlServer . BADGE_TEMPLATE_PATH . "$badge_icon";
            $tool_content .= "<div class='row res-table-row'>
                                    <div class='col-sm-2'>
                                        <img style='box-shadow: 0 0 4px 1px #bbb; max-height: 50px;' class='img-responsive block-center' src='$icon_link'>
                                    </div>
                                    <div class='col-sm-9'>
                                        <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;badge_id=$data->id'>".q($data->title)."</a>
                                        <div style='margin-top: 5px;'><span class='fa {$vis_icon}'></span>&nbsp;&nbsp;&nbsp;<span class='{$vis_status}'>$status_msg</span></div>
                                    </div>";
                if ($is_editor) {
                    $tool_content .= "<div class='col-sm-1 text-left'>" .
                        action_button(array(
                            array('title' => $langEditChange,
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;badge_id=$data->id&amp;edit=1",
                                'icon' => 'fa-cogs'),
                            array('title' => $data->active ? $langDeactivate : $langActivate,
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;badge_id=$data->id&amp;vis=" .
                                    ($data->active ? '0' : '1'),
                                'icon' => $data->active ? 'fa-eye-slash' : 'fa-eye'),
                            array('title' => $langDeleteCourseActivities,
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;del_badge=$data->id",
                                'icon' => 'fa-times',
                                'class' => 'delete',
                                'confirm' => $langConfirmDelete),
                            array('title' => $langPurge,
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;purge_cc=$data->id",
                                'icon' => 'fa-times',
                                'class' => 'delete',
                                'confirm' => $langConfirmPurgeBadge)
                        )) . "</div>";
                }
                $tool_content .= "</div>";
        }
    }

    $tool_content .= "</div>
                        </div>
                    </div>
                </div>
            </div>";
    }

/**
 * @brief display course completion (special type of badge)
 */
function display_course_completion(): void
{
    global $course_id, $tool_content, $course_code, $is_editor,
           $langDeleteCourseActivities, $langConfirmDelete, $langCourseCompletion,
           $langActivate, $langDeactivate, $langPurge,
           $langActive, $langInactive, $langConfirmPurgeCourseCompletion;

    $data = Database::get()->querySingle("SELECT id, title, description, active, icon FROM badge "
                                    . "WHERE course_id = ?d AND bundle = -1 AND unit_id = 0", $course_id);

    if ($data) {
        $tool_content .= "
            <div class='row'>
                <div class='col-xs-12'>
                    <div class='panel panel-default'>
                        <div class='panel-body'>
                            <div class='inner-heading'>
                                <div class='row'>
                                    <div class='col-sm-7'>
                                        <strong>$langCourseCompletion</strong>
                                    </div>
                                </div>
                            </div>
                            <div class='res-table-wrapper'>";

            $vis_status = $data->active ? "text-success" : "text-danger";
            $vis_icon = $data->active ? "fa-eye" : "fa-eye-slash";
            $status_msg = $data->active ? $langActive : $langInactive;
            $tool_content .= "
                        <div class='row res-table-row'>
                            <div class='col-sm-2'>
                                <i class='fa fa-trophy fa-3x' aria-hidden='true'></i>
                            </div>
                            <div class='col-sm-9'>
                                <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;badge_id=$data->id'>".q($data->title)."</a>
                                <div style='margin-top: 5px;'><span class='fa {$vis_icon}'></span>&nbsp;&nbsp;&nbsp;<span class='{$vis_status}'>$status_msg</span></div>
                            </div>";
                if ($is_editor) {
                    $tool_content .= "<div class='col-sm-1 text-left'>".
                        action_button(array(
                            array('title' => $data->active ? $langDeactivate : $langActivate,
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;badge_id=$data->id&amp;vis=" .
                                    ($data->active ? '0' : '1'),
                                'icon' => $data->active ? 'fa-eye-slash' : 'fa-eye'),
                            array('title' => $langDeleteCourseActivities,
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;del_badge=$data->id",
                                'icon' => 'fa-times',
                                'class' => 'delete',
                                'confirm' => $langConfirmDelete),
                            array('title' => $langPurge,
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;purge_cc=$data->id",
                                'icon' => 'fa-times',
                                'class' => 'delete',
                                'confirm' => $langConfirmPurgeCourseCompletion)
                        ))
                    ."</div>";
                }
                $tool_content .= "</div>";
        $tool_content .= "
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        ";
    }
}

/**
 * @brief display all certificate activities
 * @param type $element
 * @param type $certificate_id
 */
function display_activities($element, $id, $unit_id = 0) {

    global $tool_content, $course_code, $is_editor,
           $langNoActivCert, $langAttendanceActList, $langTitle, $langType,
           $langOfAssignment, $langExerciseAsModuleLabel, $langOfBlog,
           $langMediaAsModuleLabel, $langOfEBook, $langOfPoll, $langWiki,
           $langNumInForum, $langOfBlogComments, $langConfirmDelete,
           $langOfLearningPath, $langOfLearningPathDuration, $langDelete, $langEditChange,
           $langDocumentAsModuleLabel, $langCourseParticipation,
           $langAdd, $langBack, $langUsers, $langOfGradebook,
           $langValue, $langNumInForumTopic, $langOfCourseCompletion, $langOfUnitCompletion,
           $course_id, $langUnitCompletion, $langUnitPrerequisites, $langNewUnitPrerequisite,
           $langNoUnitPrerequisite, $langAssignmentParticipation, $langAttendance;

    if ($unit_id) {
        $link_id = "course=$course_code&amp;manage=1&amp;unit_id=$unit_id&amp;badge_id=$id";
    } else {
        if ($element == 'certificate') {
            $link_id = "course=$course_code&amp;certificate_id=$id";
        } else {
            $link_id = "course=$course_code&amp;badge_id=$id";
        }
    }

    $tool_content .= action_bar(
            array(
                array('title' => $langUsers,
                    'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;progressall=true",
                    'icon' => 'fa-users',
                    'level' => 'primary-label',
                    'show'  =>  $unit_id ? false : true),
                array('title' => $langBack,
                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label',
                    'show'  =>  $unit_id ? false : true)
            ),
            false
        );

    if ($unit_id) {
        // check if unit completion is enabled
        $cc_enable = Database::get()->querySingle("SELECT count(id) as active FROM badge
                                                            WHERE course_id = ?d AND unit_id = ?d
                                                            AND bundle = -1", $course_id, $unit_id)->active;

        // check if current element is unit completion badge
        $cc_is_current = false;
        if ($element == 'badge') {
            $bundle = Database::get()->querySingle("select bundle from badge where id = ?d", $id)->bundle;
            if ($bundle && $bundle == -1) {
                $cc_is_current = true;
            }
        }
    } else {
        // check if course completion is enabled
        $cc_enable = Database::get()->querySingle("SELECT count(id) as active FROM badge
                                                            WHERE course_id = ?d AND bundle = -1
                                                            AND unit_id = ?d", $course_id, $unit_id)->active;

        // check if current element is course completion badge
        $cc_is_current = false;
        if ($element == 'badge') {
            $bundle = Database::get()->querySingle("select bundle from badge where id = ?d", $id)->bundle;
            if ($bundle && $bundle == -1) {
                $cc_is_current = true;
            }
        }
    }

    // certificate details
    $tool_content .= display_settings($element, $id, $unit_id);
    $addActivityBtn = action_button(array(
        array('title' => $unit_id ? $langOfUnitCompletion : $langOfCourseCompletion,
            'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=". ($unit_id ? "unitcompletion" : "coursecompletion"),
            'icon' => 'fa fa-trophy',
            'show' => !$cc_enable),
        array('title' => $langOfAssignment,
            'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=" . AssignmentEvent::ACTIVITY,
            'icon' => 'fa fa-flask space-after-icon',
            'class' => ''),
        array('title' => $langAssignmentParticipation,
            'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=" . AssignmentSubmitEvent::ACTIVITY,
            'icon' => 'fa fa-flask space-after-icon',
            'class' => ''),
        array('title' => $langExerciseAsModuleLabel,
            'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=" . ExerciseEvent::ACTIVITY,
            'icon' => 'fa fa-pencil-square-o',
            'class' => ''),
        array('title' => $langOfBlog,
            'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=" . BlogEvent::ACTIVITY,
            'icon' => 'fa fa-columns fa-fw',
            'show' => ($unit_id == 0),
            'class' => ''),
        array('title' => $langOfBlogComments,
            'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=blogcomments",
            'icon' => 'fa fa-comment fa-fw',
            'show' => ($unit_id == 0),
            'class' => ''),
        /*array('title' => $langOfCourseComments,
              'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=coursecomments",
              'icon' => 'fa fa-edit space-after-icon',
              'class' => ''),*/
        array('title' => $langNumInForum,
            'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=" . ForumEvent::ACTIVITY,
            'icon' => 'fa fa-comments fa-fw',
            'show' => ($unit_id == 0),
            'class' => ''),
        array('title' => $langNumInForumTopic,
            'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=" . ForumTopicEvent::ACTIVITY,
            'icon' => 'fa fa-comments fa-fw',
            'class' => ''),
        array('title' => $langOfLearningPath,
            'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=lp",
            'icon' => 'fa fa-ellipsis-h fa-fw',
            'class' => ''),
        array('title' => $langOfLearningPathDuration,
            'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=lpduration",
            'icon' => 'fa fa-ellipsis-h fa-fw',
            'class' => ''),
        /*array('title' => $langOfLikesSocial,
              'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=likesocial",
              'icon' => 'fa fa-edit space-after-icon',
              'class' => ''),
        array('title' => $langOfLikesForum,
              'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=likeforum",
              'icon' => 'fa fa-edit space-after-icon',
              'class' => ''),*/
        array('title' => $langDocumentAsModuleLabel,
            'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=document",
            'icon' => 'fa fa-folder-open-o fa-fw',
            'class' => ''),
        array('title' => $langMediaAsModuleLabel,
            'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=multimedia",
            'icon' => 'fa fa-edit space-after-icon',
            'class' => ''),
        array('title' => $langOfEBook,
            'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=ebook",
            'icon' => 'fa fa-book fa-fw',
            'class' => ''),
        array('title' => $langOfPoll,
            'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=poll",
            'icon' => 'fa fa-question-circle fa-fw',
            'class' => ''),
        array('title' => $langWiki,
            'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=" . WikiEvent::ACTIVITY,
            'icon' => 'fa fa-wikipedia-w fa-fw',
            'class' => ''),
        array('title' => $langCourseParticipation,
            'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=participation",
            'icon' => 'fa fa-area-chart fa-fw',
            'class' => ''),
        array('title' => $langOfGradebook,
            'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=" . GradebookEvent::ACTIVITY,
            'icon' => 'fa fa-sort-numeric-desc space-after-icon',
            'show' => ($unit_id == 0),
            'class' => ''),
        array('title' => $langOfCourseCompletion,
            'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=" . CourseCompletionEvent::ACTIVITY,
            'icon' => 'fa fa-trophy',
            'show' => $cc_enable && !$cc_is_current),
        array('title' => $langAttendance,
            'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;add=true&amp;act=" . AttendanceEvent::ACTIVITY,
            'icon' => 'fa fa-sort-numeric-desc space-after-icon',
            'show' => ($unit_id == 0),
            'class' => '')),
        array(
            'secondary_title' => $langAdd,
            'secondary_icon' => '',
            'secondary_btn_class' => 'btn-success btn-sm'
        ));

    //get available activities
    $result = Database::get()->queryArray("SELECT * FROM {$element}_criterion WHERE $element = ?d ORDER BY `id` DESC", $id);

    if (!$unit_id) {
        $tool_content .= "
            <div class='row'>
            <div class='col-xs-12'>
                <div class='panel panel-default'>
                    <div class='panel-body'>
                        <div class='inner-heading'>
                            <div class='row'>
                                <div class='col-sm-10'>
                                    <strong>$langAttendanceActList</strong>
                                </div>";
                                if ($is_editor) {
                                    $tool_content .= "<div class='col-sm-2 text-right'>$addActivityBtn</div>";
                                }
                            $tool_content .= "</div>
                        </div>
                        <div class='res-table-wrapper'>
                            <div class='row res-table-header'>
                                <div class='col-sm-7'>
                                    $langTitle
                                </div>
                                <div class='col-sm-2'>
                                    $langType
                                </div>
                                <div class='col-sm-2'>
                                    $langValue
                                </div>";
                                if ($is_editor) {
                                    $tool_content .= "<div class='col-sm-1 text-center'>
                                        <i class='fa fa-cogs'></i>
                                    </div>";
                                }
            $tool_content .= "</div>";
        if (count($result) == 0) {
            $tool_content .= "<p class='margin-top-fat text-center text-muted'>$langNoActivCert</p>";
        } else {
            foreach ($result as $details) {
                $resource_data = get_resource_details($element, $details->id);
                $tool_content .= "
                <div class='row res-table-row'>
                    <div class='col-sm-7'>".$resource_data['title']."</div>
                    <div class='col-sm-2'>". $resource_data['type']."</div>
                    <div class='col-sm-2'>";
                if (!empty($details->operator) && $details->activity_type != AssignmentSubmitEvent::ACTIVITY) {
                    $op = get_operators();
                    $tool_content .= $op[$details->operator];
                } else {
                    $tool_content .= "&mdash;";
                }
                if ($details->activity_type == AssignmentSubmitEvent::ACTIVITY) {
                    $tool_content .= "&nbsp;</div>";
                } else {
                    $tool_content .= "&nbsp;$details->threshold</div>";
                }
                if ($is_editor) {
                    $tool_content .= "<div class='col-sm-1 text-center'>".
                        action_button(array(
                            array('title' => $langEditChange,
                                'icon' => 'fa-edit',
                                'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;act_mod=$details->id",
                                'show' => in_array($details->activity_type, criteria_with_operators())
                            ),
                            array('title' => $langDelete,
                                'icon' => 'fa-times',
                                'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;del_cert_res=$details->id",
                                'confirm' => $langConfirmDelete,
                                'class' => 'delete'))).
                        "</div>";
                }
                $tool_content .= "</div>";
            }
        }
        $tool_content .= "</div>
                    </div>
                </div>
            </div>
        </div>
        ";
    } else {
            $tool_content .= "<div class='main-content'>
                                <div class='col-sm-12'>
                                    <div class='row row-main'>
                                        <div class='panel panel-default'>
                                            <div class='panel-body'>
                                                <div class='inner-heading'>
                                                    <div class='row'>
                                                        <div class='col-sm-7'>
                                                            <strong>$langUnitCompletion</strong>
                                                        </div>
                                                        <div class='col-sm-5 text-right'>
                                                            <div class='text-right'>
                                                                $addActivityBtn
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class='res-table-wrapper'>
                                                    <div class='row res-table-header'>
                                                        <div class='col-sm-7'>
                                                            $langTitle
                                                        </div>
                                                        <div class='col-sm-2'>
                                                            $langType
                                                        </div>
                                                        <div class='col-sm-2'>
                                                            $langValue
                                                        </div>
                                                        <div class='col-sm-1 text-center'>
                                                            <i class='fa fa-cogs'></i>
                                                        </div>
                                                    </div>";

        if (count($result) == 0) {
            $tool_content .= "<p class='margin-top-fat text-center text-muted'>$langNoActivCert</p>";
        } else {
            foreach ($result as $details) {
                $resource_data = get_resource_details($element, $details->id, $unit_id);
                $tool_content .= "
                <div class='row res-table-row'>
                    <div class='col-sm-7'>".$resource_data['title']."</div>
                    <div class='col-sm-2'>". $resource_data['type']."</div>
                    <div class='col-sm-2'>";
                if (!empty($details->operator) && $details->activity_type != AssignmentSubmitEvent::ACTIVITY) {
                    $op = get_operators();
                    $tool_content .= $op[$details->operator];
                } else {
                    $tool_content .= "&mdash;";
                }
                if ($details->activity_type == AssignmentSubmitEvent::ACTIVITY) {
                    $tool_content .= "&nbsp;</div>";
                } else {
                    $tool_content .= "&nbsp;$details->threshold</div>";
                }
                $tool_content .= "<div class='col-sm-1 text-center'>".
                    action_button(array(
                        array('title' => $langEditChange,
                            'icon' => 'fa-edit',
                            'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;act_mod=$details->id",
                            'show' => in_array($details->activity_type, criteria_with_operators())
                        ),
                        array('title' => $langDelete,
                            'icon' => 'fa-times',
                            'url' => "$_SERVER[SCRIPT_NAME]?$link_id&amp;del_cert_res=$details->id",
                            'confirm' => $langConfirmDelete,
                            'class' => 'delete'))).
                    "</div></div>";
            }
        }

        //************* UNIT PREREQUISITES *************//
        $course_units = Database::get()->queryArray("SELECT * FROM course_units
                                                            WHERE course_id = ?d", $course_id);

        $unit_prerequisite_id = Database::get()->querySingle("SELECT up.prerequisite_unit
                                                                    FROM unit_prerequisite up
                                                                    JOIN course_units cu ON (cu.id = up.unit_id)
                                                                    WHERE cu.id = ".$unit_id);

        $action_button_content = [];

        foreach ($course_units as $prereq) {
            if ($prereq->id == $unit_id) { // Don't include current unit on prerequisites list
                continue;
            }
            $action_button_content[] = [
                'title' =>  $prereq->title,
                'icon'  =>  'fa fa-book fa-fw',
                'url'   =>  "$_SERVER[SCRIPT_NAME]?course=$course_code&prereq=$prereq->id&unit_id=$unit_id",
                'class' =>  '',
                'show'  =>  !is_unit_prereq_enabled($unit_id),
            ];
        }
        $addPrereqBtn = action_button($action_button_content,
            array(
                'secondary_title' => $langNewUnitPrerequisite,
                'secondary_icon' => '',
                'secondary_btn_class' => 'btn-success btn-sm',
            ));
        $tool_content .= "  </div>
                        </div>
                    </div>
                </div>
                <div class='row row-main'>
                    <div class='panel panel-default'>
                        <div class='panel-body'>
                            <div class='inner-heading'>
                                <div class='row'>
                                    <div class='col-sm-7'>
                                        <strong>$langUnitPrerequisites</strong>
                                    </div>
                                    <div class='col-sm-5 text-right'>
                                    $addPrereqBtn
                                    </div>
                                </div>
                            </div>
                            <div class='res-table-wrapper'>";


        $delPrereqBtn = action_button(array(
            array('title' => $langDelete,
                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&del_un_prereq=1&unit_id=$unit_id",
                'icon' => 'fa-times',
                'class' => 'delete',
                'confirm' => $langConfirmDelete)));

        if ( $unit_prerequisite_id ) {
            $prereq_unit_title = Database::get()->querySingle("SELECT title FROM course_units
                                                                        WHERE id = ?d", $unit_prerequisite_id->prerequisite_unit)->title;

            $tool_content .= "  <div class='col-sm-11'>
                                    <p class='text-left'>$prereq_unit_title</p>
                                </div>
                                <div class='col-sm-1 pull-right'>$delPrereqBtn</div>";
        } else {
            $tool_content .= "<p class='text-center text-muted'>$langNoUnitPrerequisite</p>";
        }

        $tool_content .= " </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>";
    }
}

/**
 * @param int $unit_id
 * @return bool
 */
function is_unit_prereq_enabled($unit_id) {
    $prereq_id = Database::get()->queryArray("SELECT prerequisite_unit FROM unit_prerequisite
                                                        WHERE unit_id = ?d", $unit_id);
    if (count($prereq_id) > 0) {
        return true;
    }
    return false;
}

/**
 * @brief choose activity for inserting in certificate / badge
 * @param type $element_id
 * @param type $element
 * @param type $activity
 * @param type $unit_id
 * @param type $unit_resource_id
 */
function insert_activity($element, $element_id, $activity, $unit_id = 0, $unit_resource_id = 0) {

    switch ($activity) {
        case 'coursecompletion':
            add_course_completion_to_certificate($element_id);
            break;
        case 'unitcompletion':
            add_unit_completion_to_certificate($element_id, $unit_id);
            break;
        case AssignmentEvent::ACTIVITY:
        case 'work':
            display_available_assignments($element, $element_id, AssignmentEvent::ACTIVITY, $unit_id, $unit_resource_id);
            break;
        case AssignmentSubmitEvent::ACTIVITY:
            display_available_assignments($element, $element_id, AssignmentSubmitEvent::ACTIVITY, $unit_id, $unit_resource_id);
            break;
        case ExerciseEvent::ACTIVITY:
            display_available_exercises($element, $element_id, $unit_id, $unit_resource_id);
            break;
        case BlogEvent::ACTIVITY;
            display_available_blogs($element, $element_id, $unit_id);
            break;
        case 'blogcomments':
            display_available_blogcomments($element, $element_id, $unit_id);
            break;
        case 'coursecomments':
            display_available_coursecomments($element, $element_id, $unit_id);
            break;
        case ForumEvent::ACTIVITY:
            display_available_forums($element, $element_id, $unit_id);
            break;
        case ForumTopicEvent::ACTIVITY:
        case 'topic':
            display_available_forumtopics($element, $element_id, $unit_id, $unit_resource_id);
            break;
        case 'lp':
            display_available_lps($element, $element_id, LearningPathEvent::ACTIVITY, $unit_id, $unit_resource_id);
            break;
        case 'lpduration':
            display_available_lps($element, $element_id, LearningPathDurationEvent::ACTIVITY, $unit_id, $unit_resource_id);
            break;
        case 'likesocial';
        case 'likeforum';
            break;
        case 'document':
        case 'doc':
            display_available_documents($element, $element_id, $unit_id, $unit_resource_id);
            break;
        case 'multimedia':
        case 'video':
            display_available_multimedia($element, $element_id, $unit_id, $unit_resource_id);
            break;
        case 'ebook':
            display_available_ebooks($element, $element_id, $unit_id, $unit_resource_id);
            break;
        case 'poll':
            display_available_polls($element, $element_id, $unit_id, $unit_resource_id);
            break;
        case 'wiki':
            display_available_wiki($element, $element_id, $unit_id);
            break;
        case 'participation':
            display_available_participation($element, $element_id, $unit_id);
            break;
        case GradebookEvent::ACTIVITY:
            display_available_gradebooks($element, $element_id, $unit_id);
            break;
        case CourseCompletionEvent::ACTIVITY:
            display_available_coursecompletiongrade($element, $element_id, $unit_id);
            break;
        case AttendanceEvent::ACTIVITY:
            display_available_attendances($element, $element_id, $unit_id);
            break;
        default: break;
        }
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
 * @param int $unit_id
 */
function display_modification_activity($element, $element_id, $activity_id, $unit_id = 0) {

    global $tool_content, $course_code, $langModify, $langOperator, $langUsedCertRes;

    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';
    if (resource_usage($element, $activity_id)) { // check if resource has been used by user
        Session::Messages("$langUsedCertRes", "alert-warning");

        if ($unit_id) {
            redirect(localhostUrl().$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&unit_id=$unit_id");
        } else {
            redirect_to_home_page("modules/progress/index.php?course=$course_code&amp;{$element}_id=$element_id");
        }
    } else { // otherwise editing is not allowed
        $data = Database::get()->querySingle("SELECT threshold, operator FROM {$element}_criterion
                                            WHERE id = ?d AND $element = ?d", $activity_id, $element_id);

        if ($unit_id) {
            $action = "manage.php?course=$course_code&manage=1&unit_id=$unit_id";
        } else {
            $action = "index.php?course=$course_code";
        }
        $operators = get_operators();

        $tool_content .= "<form action=$action method='post'>";
        $tool_content .= "<input type='hidden' name='$element_name' value='$element_id'>";
        $tool_content .= "<input type='hidden' name='activity_id' value='$activity_id'>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='name' class='col-sm-1 control-label'>$langOperator:</label>";
        $tool_content .= "<span class='col-sm-2'>" . selection($operators, 'cert_operator', $data->operator) . "</span>";
        $tool_content .= "<span class='col-sm-2'><input class='form-control' type='text' name='cert_threshold' value='$data->threshold'></span>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='col-sm-5 col-sm-offset-5'>";
        $tool_content .= "<input class='btn btn-primary' type='submit' name='mod_cert_activity' value='$langModify'>";
        $tool_content .= "</div>";
        $tool_content .= "</form>";
    }
}

/**
 * @brief assignments display form
 * @param type $element
 * @param type $element_id
 * @param int $unit_id
 * @param int $unit_resource_id
 */
function display_available_assignments($element, $element_id, $activity_type, $unit_id = 0, $unit_resource_id = 0) {

    global $course_id, $tool_content, $langNoAssign, $course_code,
           $langTitle, $langGroupWorkDeadline_of_Submission,
           $langAddModulesButton, $langChoice, $langParticipateSimple,
           $langOperator, $langGradebookGrade, $urlServer;

    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';
    $form_submit_name = 'add_assignment';
    if ($activity_type == AssignmentSubmitEvent::ACTIVITY) {
        $form_submit_name = 'add_assignment_participation';
    }
    $notInSql = "(SELECT resource FROM {$element}_criterion WHERE $element = ?d
                     AND resource != ''
                     AND activity_type = '" . $activity_type . "'
                     AND module = " . MODULE_ID_ASSIGN . ")";

    if ($unit_id) {
        if ($unit_resource_id) {
            $resWorksSql = "SELECT assignment.id, assignment.title, assignment.description, submission_date
                              FROM assignment, unit_resources
                             WHERE assignment.id = unit_resources.res_id
                               AND unit_id = ?d
                               AND unit_resources.id = ?d";
            $result = Database::get()->queryArray("$resWorksSql AND assignment.id NOT IN $notInSql ORDER BY assignment.title", $unit_id, $unit_resource_id, $element_id);
        } else {
            $unitWorksSql = "SELECT assignment.id, assignment.title, assignment.description, submission_date
                               FROM assignment, unit_resources
                              WHERE assignment.id = unit_resources.res_id
                                AND unit_id = ?d
                                AND unit_resources.type = 'work'
                                AND visible = 1";
            $result = Database::get()->queryArray("$unitWorksSql AND assignment.id NOT IN $notInSql ORDER BY assignment.title", $unit_id, $element_id);
        }
    } else {
        $courseWorksSql = "SELECT * FROM assignment WHERE course_id = ?d
                              AND active = 1
                              AND (deadline IS NULL OR deadline >= ". DBHelper::timeAfter() . ")";
        $result = Database::get()->queryArray("$courseWorksSql AND id NOT IN $notInSql ORDER BY title", $course_id, $element_id);
    }

    if (count($result) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langNoAssign</div>";
    } else {
        if ($unit_id) {
            $action = "manage.php?course=$course_code&manage=1&unit_id=$unit_id";
        } else {
            $action = "index.php?course=$course_code";
        }
        $tool_content .= "<form action=$action method='post'>" .
            "<input type='hidden' name = '$element_name' value='$element_id'>" .
            "<table class='table-default'>" .
            "<tr class='list-header'>" .
            "<th class='text-left'>&nbsp;$langTitle</th>" .
            "<th style='width:160px;'>$langGroupWorkDeadline_of_Submission</th>";
        if ($activity_type == AssignmentEvent::ACTIVITY) {
            $tool_content .=
            "<th style='width:5px;'>$langOperator</th>" .
            "<th style='width:50px;'>$langGradebookGrade</th>";
        }
        $tool_content .=
            "<th style='width:10px;' class='text-center'>$langChoice</th>" .
            "</tr>";
        foreach ($result as $row) {
            $assignment_id = $row->id;
            $description = empty($row->description) ? '' : "<div style='margin-top: 10px;' class='text-muted'>$row->description</div>";
            $tool_content .= "<tr>" .
                "<td><a href='{$urlServer}modules/work/?course=$course_code&amp;id=$row->id'>" . q($row->title) . "</a>$description</td>" .
                "<td class='text-center'>" . format_locale_date(strtotime($row->submission_date), 'short') . "</td>";
            if ($activity_type == AssignmentEvent::ACTIVITY) {
                $tool_content .=
                "<td>" . selection(get_operators(), "operator[$assignment_id]") . "</td>" .
                "<td class='text-center'><input style='width:50px;' type='text' name='threshold[$assignment_id]' value=''></td>";
            }
            $tool_content .=
                "<td class='text-center'><input name='assignment[]' value='$assignment_id' type='checkbox'></td>" .
                "</tr>";
        }
        $tool_content .= "</table>
                          <div class='text-right'>
                            <input class='btn btn-primary' type='submit' name='$form_submit_name' value='$langAddModulesButton'>
                          </div></form>";
    }
}


/**
 * @brief exercises display form
 * @param type $element
 * @param type $element_id
 * @param int $unit_id
 * @param int $unit_resource_id
 */
function display_available_exercises($element, $element_id, $unit_id = 0, $unit_resource_id = 0) {

    global $course_id, $course_code, $tool_content, $urlServer, $langExercices,
            $langNoExercises, $langChoice, $langAddModulesButton,
            $langOperator, $langGradebookGrade;

    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';


    if ($unit_id) {
        if ($unit_resource_id) {
            $result = Database::get()->queryArray("SELECT exercise.id, exercise.title, exercise.description, exercise.active
                                            FROM exercise, unit_resources
                                            WHERE exercise.id = unit_resources.res_id
                                                AND unit_id = ?d
                                                AND unit_resources.id = ?d"
                                            , $unit_id, $unit_resource_id);
        } else {
            $result = Database::get()->queryArray("SELECT exercise.id, exercise.title, exercise.description, exercise.active
                                            FROM exercise, unit_resources
                                            WHERE exercise.id = unit_resources.res_id
                                                AND unit_id = ?d
                                                AND unit_resources.type = 'exercise'
                                                AND visible = 1", $unit_id);
        }

    } else {
        $result = Database::get()->queryArray("SELECT * FROM exercise WHERE exercise.course_id = ?d
                                    AND exercise.active = 1
                                    AND (exercise.end_date IS NULL OR exercise.end_date >= ". DBHelper::timeAfter() . ")
                                    AND exercise.id NOT IN
                                    (SELECT resource FROM {$element}_criterion WHERE $element = ?d
                                            AND resource != ''
                                            AND activity_type = '" . ExerciseEvent::ACTIVITY . "'
                                            AND module = " . MODULE_ID_EXERCISE . ") ORDER BY title", $course_id, $element_id);
    }

    $quizinfo = array();
    foreach ($result as $row) {
        $quizinfo[] = array(
            'id' => $row->id,
            'name' => $row->title,
            'comment' => $row->description,
            'visibility' => $row->active);
    }
    if (count($quizinfo) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langNoExercises</div>";
    } else {
        $action = $unit_id ? "manage.php?course=$course_code&manage=1&unit_id=$unit_id" : "index.php?course=$course_code";

        $tool_content .= "<form action=$action method='post'>" .
                "<input type='hidden' name='$element_name' value='$element_id'>" .
                "<table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th class='text-left'>$langExercices</th>" .
                "<th style='width:5px;'>$langOperator</th>" .
                "<th style='width:50px;'>$langGradebookGrade</th>" .
                "<th style='width:20px;' class='text-center'>$langChoice</th>" .
                "</tr>";
        foreach ($quizinfo as $entry) {
            $exercise_id = $entry['id'];
            $comments = empty($entry['comment']) ? '' : "<div style='margin-top: 10px;' class='text-muted'>". $entry['comment']. "</div>";
            $tool_content .= "<tr>";
            $tool_content .= "<td class='text-left'><a href='{$urlServer}modules/exercise/exercise_submit.php?course=$course_code&amp;exerciseId=$exercise_id'>" . q($entry['name']) . "</a>" . $comments . "</td>";
            $tool_content .= "<td>". selection(get_operators(), "operator[$exercise_id]") . "</td>";
            $tool_content .= "<td class='text-center'><input style='width:50px;' type='text' name='threshold[$exercise_id]' value=''></td>";
            $tool_content .= "<td class='text-center'><input type='checkbox' name='exercise[]' value='$exercise_id'></td>";
            $tool_content .= "</tr>";
        }
        $tool_content .= "</table><div class='text-right'>";
        $tool_content .= "<input class='btn btn-primary' type='submit' name='add_exercise' value='$langAddModulesButton'></div>
        </form>";
    }
}

/**
 * @brief document display form
 * @param type $element
 * @param type $element_id
 * @param int $unit_id
 */
function display_available_documents($element, $element_id, $unit_id = 0, $unit_resource_id = 0) {

    global $webDir, $tool_content,
            $langDirectory, $langUp, $langName, $langSize,
            $langDate, $langAddModulesButton, $langChoice,
            $langNoDocuments, $course_code, $group_sql;

    require_once 'modules/document/doc_init.php';
    require_once 'include/lib/mediaresource.factory.php';
    require_once 'include/lib/fileManageLib.inc.php';
    require_once 'include/lib/fileDisplayLib.inc.php';
    require_once 'include/lib/multimediahelper.class.php';

    doc_init();

    $common_docs = false;
    $basedir = $webDir . '/courses/' . $course_code . '/document';
    $path = get_dir_path('path');
    $dir_param = get_dir_path('dir');
    $dir_setter = $dir_param ? ('&amp;dir=' . $dir_param) : '';
    $dir_html = $dir_param ? "<input type='hidden' name='dir' value='$dir_param'>" : '';

    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';

    if ($unit_id) {
        if ($unit_resource_id) {
            $result = Database::get()->queryArray("SELECT document.id, course_id, path, filename, format, document.title, extra_path, date_modified, document. visible, copyrighted, comment, IF(document.title = '', filename, document.title) AS sort_key
                                            FROM document, unit_resources
                                            WHERE document.id = unit_resources.res_id
                                                AND unit_id = ?d
                                                AND unit_resources.id = ?d"
                                            , $unit_id, $unit_resource_id);
        } else {
            $result = Database::get()->queryArray("SELECT document.id, course_id, path, filename, format, document.title, extra_path, date_modified, document. visible, copyrighted, comment, IF(document.title = '', filename, document.title) AS sort_key
                                            FROM document, unit_resources
                                            WHERE document.id = unit_resources.res_id
                                                AND unit_id = ?d
                                                AND unit_resources.type = 'doc'
                                                AND unit_resources.visible = 1", $unit_id);
        }

    } else {
        $result = Database::get()->queryArray("SELECT id, course_id, path, filename, format, title, extra_path, date_modified, visible, copyrighted, comment, IF(title = '', filename, title) AS sort_key FROM document
                                     WHERE $group_sql AND visible = 1 AND
                                          path LIKE ?s AND
                                          path NOT LIKE ?s AND id NOT IN
                                        (SELECT resource FROM {$element}_criterion WHERE $element = ?d
                                            AND resource!='' AND activity_type = '" . ViewingEvent::DOCUMENT_ACTIVITY . "' AND module = " . MODULE_ID_DOCS . ")
                                ORDER BY sort_key COLLATE utf8mb4_general_ci",
            "$path/%", "$path/%/%", $element_id);
    }



    $fileinfo = array();
    $urlbase = $_SERVER['SCRIPT_NAME'] . "?course=$course_code&amp;$element_name=$element_id&amp;add=true&amp;act=document$dir_setter&amp;type=doc&amp;path=";

    foreach ($result as $row) {
        $fullpath = $basedir . $row->path;
        if ($row->extra_path) {
            $size = 0;
        } else {
            $size = file_exists($fullpath)? filesize($fullpath): 0;
        }
        $fileinfo[] = array(
            'id' => $row->id,
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
        $tool_content .= "<div class='alert alert-warning'>$langNoDocuments</div>";
    } else {
        if (!empty($path)) {
            $dirname = Database::get()->querySingle("SELECT filename FROM document WHERE $group_sql AND path = ?s", $path);
            $parentpath = dirname($path);
            $dirname =  htmlspecialchars($dirname->filename);
            $parentlink = $urlbase . $parentpath;
            $parenthtml = "<span class='pull-right'><a href='$parentlink'>$langUp " .
                    icon('fa-level-up') . "</a></span>";
            $colspan = 4;
        }
        if ($unit_id) {
            $action = "manage.php?course=$course_code&manage=1&unit_id=$unit_id";
        } else {
            $action = "index.php?course=$course_code";
        }
        $tool_content .= "<form action=$action method='post'>" .
                "<input type='hidden' name='$element_name' value='$element_id'>" .
                "<table class='table-default'>";
        if( !empty($path)) {
        $tool_content .=
                "<tr>" .
                "<th colspan='$colspan'><div class='text-left'>$langDirectory: $dirname$parenthtml</div></th>" .
                "</tr>" ;
        }
        $tool_content .=
                "<tr class='list-header'>" .
                "<th class='text-left'>$langName</th>" .
                "<th class='text-center'>$langSize</th>" .
                "<th class='text-center'>$langDate</th>" .
                "<th style='width:20px;' class='text-center'>$langChoice</th>" .
                "</tr>";
        $counter = 0;
        foreach (array(true, false) as $is_dir) {
            foreach ($fileinfo as $entry) {
                if ($entry['is_dir'] != $is_dir) {
                    continue;
                }
                $dir = $entry['path'];
                if ($is_dir) {
                    $image = 'fa-folder-o';
                    $file_url = $urlbase . $dir;
                    $link_text = $entry['name'];

                    $link_href = "<a href='$file_url'>$link_text</a>";
                } else {
                    $image = choose_image('.' . $entry['format']);
                    $file_url = file_url($entry['path'], $entry['name'], $common_docs ? 'common' : $course_code);

                    $dObj = $entry['object'];
                    $dObj->setAccessURL($file_url);
                    $dObj->setPlayURL(file_playurl($entry['path'], $entry['name'], $common_docs ? 'common' : $course_code));

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
                    $tool_content .= "<td class='text-right'>$size</td><td class='text-center'>$date</td>";
                }
                $tool_content .= "<td class='text-center'><input type='checkbox' name='document[]' value='$entry[id]' /></td>";
                $tool_content .= "</tr>";
                $counter++;
            }
        }
        $tool_content .= "</table>";
        $tool_content .= "<div class='text-right'>";
        $tool_content .= "<input class='btn btn-primary' type='submit' name='add_document' value='$langAddModulesButton' /></div>$dir_html</form>";
    }
}


/**
 * @brief blog display form
 * @param type $element
 * @param type $element_id
 * @param int $unit_id
 */
function display_available_blogs($element, $element_id, $unit_id = 0) {

    global $tool_content, $langAddModulesButton, $langNumOfBlogs,
           $course_code, $langTitle, $langValue, $langResourceAlreadyAdded,
           $langChoice, $langOperator;

    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';

    $res = Database::get()->queryArray("SELECT resource FROM {$element}_criterion WHERE $element = ?d
                                        AND resource IS NULL
                                        AND activity_type = '" . BlogEvent::ACTIVITY . "'
                                        AND module = " . MODULE_ID_BLOG, $element_id);

    if (count($res) > 0) {
        $tool_content .= "<div class='alert alert-warning'>$langResourceAlreadyAdded</div>";
    } else {
        if ($unit_id) {
            $action = "manage.php?course=$course_code&manage=1&unit_id=$unit_id";
        } else {
            $action = "index.php?course=$course_code";
        }

        $tool_content .= "<form action=$action method='post'>" .
                "<input type='hidden' name='$element_name' value='$element_id'>" .
                "<table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th class='text-left' style='width:70%;'>&nbsp;$langTitle</th>" .
                "<th style='width:5px;'>&nbsp;$langOperator</th>" .
                "<th style='width:30px;'>$langValue</th>" .
                "<th style='width:20px;' class='text-center'>$langChoice</th>" .
                "</tr>";

            $tool_content .= "<tr>" .
                    "<td>$langNumOfBlogs</td>" .
                    "<td>". selection(get_operators(), "operator") . "</td>".
                    "<td class='text-center'><input style='width:30px;' type='text' name='threshold' value=''></td>" .
                    "<td class='text-center'><input name='blog' value='1' type='checkbox'></td>" .
                    "</tr>";

        $tool_content .= "</table>" .
                "<div align='right'><input class='btn btn-primary' type='submit' name='add_blog' value='$langAddModulesButton'></div></th></form>";
    }
}

/**
 * @brief blog comment display form
 * @param type $element
 * @param type $element_id
 * @param int $unit_id
 */
function display_available_blogcomments($element, $element_id, $unit_id = 0) {

    global $tool_content, $langAddModulesButton, $langBlogEmpty,
           $urlServer, $course_code, $langTitle, $langValue,
           $langChoice, $langDate, $course_id, $langOperator;

    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';

    $result = Database::get()->queryArray("SELECT * FROM blog_post WHERE course_id = ?d AND id NOT IN
                                (SELECT resource FROM {$element}_criterion WHERE $element = ?d
                                    AND resource != ''
                                    AND activity_type = '" . CommentEvent::BLOG_ACTIVITY . "'
                                    AND module = " . MODULE_ID_COMMENTS . ")
                                ORDER BY title", $course_id, $element_id);

    if (count($result) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langBlogEmpty</div>";
    } else {
        if ($unit_id) {
            $action = "manage.php?course=$course_code&manage=1&unit_id=$unit_id";
        } else {
            $action = "index.php?course=$course_code";
        }

        $tool_content .= "<form action=$action method='post'>" .
                "<input type='hidden' name='$element_name' value='$element_id'>" .
                "<table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th class='text-left' style='width:50%;'>&nbsp;$langTitle</th>" .
                "<th class='text-left'>&nbsp;$langDate</th>" .
                "<th style='width:5px;'>&nbsp;$langOperator</th>" .
                "<th style='width:50px;'>$langValue</th>" .
                "<th style='width:20px;' class='text-center'>$langChoice</th>" .
                "</tr>";
        foreach ($result as $row) {
            $blog_id = $row->id;
            $tool_content .= "<tr>" .
                    "<td><a href='{$urlServer}modules/blog/index.php?course=$course_code&amp;action=showPost&amp;pId=$blog_id#comments-title'>" . q($row->title) . "</a></td>" .
                    "<td class='text-center'>" . format_locale_date(strtotime($row->time), 'short') . "</td>
                    <td>". selection(get_operators(), "operator[$blog_id]") . "</td>".
                    "<td class='text-center'><input style='width:50px;' type='text' name='threshold[$blog_id]' value=''></td>" .
                    "<td class='text-center'><input name='blogcomment[]' value='$blog_id' type='checkbox'></td>" .
                    "</tr>";
        }
        $tool_content .= "</table>" .
                "<div align='right'><input class='btn btn-primary' type='submit' name='add_blogcomment' value='$langAddModulesButton'></div></th></form>";
    }
}


function display_available_coursecomments($element, $element_id, $unit_id = 0) {
// TODO: implement for unit completion as well
    global $tool_content;

    $tool_content .= "<div class='alert alert-warning'>....Προς υλοποίηση....</div>";

    return $tool_content;
}

/**
 * @brief number of forums display form
 * @param type $element
 * @param type $element_id
 * @param int $unit_id
 */
function display_available_forums($element, $element_id, $unit_id = 0) {

    global $tool_content, $langAddModulesButton, $langNumInForum,
           $course_code, $langTitle, $langValue, $langResourceAlreadyAdded,
           $langChoice, $langOperator;

    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';

    $res = Database::get()->queryArray("SELECT resource FROM {$element}_criterion WHERE $element = ?d
                                            AND resource IS NULL
                                            AND activity_type = '" . ForumEvent::ACTIVITY . "'
                                            AND module = " . MODULE_ID_FORUM . "", $element_id);

    if (count($res) > 0) {
        $tool_content .= "<div class='alert alert-warning'>$langResourceAlreadyAdded</div>";
    } else {

        if ($unit_id) {
            $action = "manage.php?course=$course_code&manage=1&unit_id=$unit_id";
        } else {
            $action = "index.php?course=$course_code";
        }

        $tool_content .= "<form action=$action method='post'>" .
                "<input type='hidden' name='$element_name' value='$element_id'>" .
                "<table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th class='text-left' style='width:70%;'>&nbsp;$langTitle</th>" .
                "<th style='width:5px;'>&nbsp;$langOperator</th>" .
                "<th style='width:30px;'>$langValue</th>" .
                "<th style='width:20px;' class='text-center'>$langChoice</th>" .
                "</tr>";

            $tool_content .= "<tr>" .
                    "<td>$langNumInForum</td>" .
                    "<td>". selection(get_operators(), "operator") . "</td>".
                    "<td class='text-center'><input style='width:30px;' type='text' name='threshold' value=''></td>" .
                    "<td class='text-center'><input name='forum' value='1' type='checkbox'></td>" .
                    "</tr>";

        $tool_content .= "</table>" .
                "<div align='right'><input class='btn btn-primary' type='submit' name='add_forum' value='$langAddModulesButton'></div></th></form>";
    }

}
/**
 * @brief forum topic display form
 * @param type $element
 * @param type $element_id
 * @param int $unit_id
 * @param int $unit_resource_id
 */
function display_available_forumtopics($element, $element_id, $unit_id = 0, $unit_resource_id = 0) {

    global $tool_content, $urlServer, $course_id,
           $langAddModulesButton, $langChoice, $langNoForumTopic,
           $langTopics, $course_code, $langOperator, $langValue;

    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';

    if ($unit_id) {
        if ($unit_resource_id) {
            $result = Database::get()->queryArray("SELECT forum_topic.id, forum_topic.title, topic_time, forum_id
                                            FROM forum_topic, unit_resources
                                            WHERE forum_topic.id = unit_resources.res_id
                                                AND unit_id = ?d
                                                AND unit_resources.id = ?d"
                                            , $unit_id, $unit_resource_id);
        } else {
            $result = Database::get()->queryArray("SELECT forum_topic.id, forum_topic.title, topic_time, forum_id
                                            FROM forum_topic, unit_resources
                                            WHERE forum_topic.id = unit_resources.res_id
                                                AND unit_id = ?d
                                                AND unit_resources.type = 'topic'
                                                AND visible = 1", $unit_id);
        }

    } else {
        $result = Database::get()->queryArray("SELECT ft.* FROM forum_topic ft JOIN forum f ON (f.id = ft.forum_id) WHERE f.course_id = ?d
                                        AND ft.id NOT IN
                                        (SELECT resource FROM {$element}_criterion WHERE $element = ?d
                                            AND resource != ''
                                            AND activity_type = '" . ForumTopicEvent::ACTIVITY . "'
                                            AND module = " . MODULE_ID_FORUM . ")", $course_id, $element_id);
    }

    $topicinfo = array();
    foreach ($result as $topicrow) {
        $topicinfo[] = array(
            'topic_id' => $topicrow->id,
            'topic_title' => $topicrow->title,
            'topic_time' => $topicrow->topic_time,
            'forum_id' => $topicrow->forum_id);
    }

    if (count($topicinfo) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langNoForumTopic</div>";
    } else {
        if ($unit_id) {
            $action = "manage.php?course=$course_code&manage=1&unit_id=$unit_id";
        } else {
            $action = "index.php?course=$course_code";
        }

        $tool_content .= "<form action=$action method='post'>" .
                "<input type='hidden' name='$element_name' value='$element_id'>" .
                "<table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th>$langTopics</th>" .
                "<th style='width:5px;'>$langOperator</th>" .
                "<th style='width:50px;'>$langValue</th>" .
                "<th style='width:20px;' class='text-center'>$langChoice</th>" .
                "</tr>";

        foreach ($topicinfo as $topicentry) {
            $topic_id = $topicentry['topic_id'];
            $forum_id = $topicentry['forum_id'];
            $tool_content .= "<tr>";
            $tool_content .= "<td>&nbsp;".icon('fa-comments')."&nbsp;&nbsp;<a href='{$urlServer}/modules/forum/viewtopic.php?course=$course_code&amp;topic=$topic_id&amp;forum=$forum_id'>" . q($topicentry['topic_title']) . "</a></td>";
            $tool_content .= "<td>". selection(get_operators(), "operator[$topic_id]") . "</td>";
            $tool_content .= "<td class='text-center'><input style='width:50px;' type='text' name='threshold[$topic_id]' value=''></td>";
            $tool_content .= "<td class='text-center'><input type='checkbox' name='forumtopic[]' value='$topic_id'></td>";
            $tool_content .= "</tr>";
        }
        $tool_content .= "</table>";
        $tool_content .= "<div class='text-right'>
                            <input class='btn btn-primary' type='submit' name='add_forumtopic' value='$langAddModulesButton'>
                        </div></form>";
    }
}

/**
 * @brief learning paths display form
 * @param type $element
 * @param type $element_id
 * @param int $unit_id
 * @param int $unit_resource_id
 */
function display_available_lps($element, $element_id, $activity_type, int $unit_id = 0, $unit_resource_id = 0) {

    global $course_id, $course_code, $urlServer, $tool_content,
           $langNoLearningPath, $langLearningPaths, $langPercentage, $langHours,
           $langChoice, $langAddModulesButton, $langOperator;

    $element_name = ($element == 'certificate') ? 'certificate_id' : 'badge_id';
    $threshold_col_title = $langPercentage;
    $form_submit_name = 'add_lp';
    if ($activity_type == LearningPathDurationEvent::ACTIVITY) {
        $threshold_col_title = $langHours;
        $form_submit_name = 'add_lpduration';
    }

    if ($unit_id) {
        if ($unit_resource_id) {
            $result = Database::get()->queryArray("SELECT learnPath_id, name, comment, `rank`
                                        FROM lp_learnPath, unit_resources
                                            WHERE learnPath_id = unit_resources.res_id
                                                AND unit_id = ?d
                                                AND unit_resources.id = ?d",
                                            $unit_id, $unit_resource_id);
        } else {
            $result = Database::get()->queryArray("SELECT learnPath_id, name, comment, `rank`
                                        FROM lp_learnPath, unit_resources
                                            WHERE learnPath_id = unit_resources.res_id
                                                AND unit_id = ?d
                                                AND unit_resources.type = 'lp'
                                                AND unit_resources.visible = 1", $unit_id);
        }

    } else {
        $result = Database::get()->queryArray("SELECT * FROM lp_learnPath WHERE lp_learnPath.course_id = ?d
                                            AND lp_learnPath.visible = 1
                                            AND lp_learnPath.learnPath_id NOT IN
                                        (SELECT resource FROM {$element}_criterion WHERE $element = ?d
                                                    AND resource!=''
                                                    AND activity_type = '" . $activity_type . "'
                                                    AND module = " . MODULE_ID_LP . ")", $course_id, $element_id);
    }


    $lpinfo = array();
    foreach ($result as $row) {
        $lpinfo[] = array(
            'id' => $row->learnPath_id,
            'name' => $row->name,
            'comment' => $row->comment,
            'rank' => $row->rank);
    }

    if (count($lpinfo) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langNoLearningPath</div>";
    } else {
        if ($unit_id) {
            $action = "manage.php?course=$course_code&manage=1&unit_id=$unit_id";
        } else {
            $action = "index.php?course=$course_code";
        }
        $tool_content .= "<form action=$action method='post'>" .
                "<input type='hidden' name='$element_name' value='$element_id'>" .
                "<table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th>$langLearningPaths</th>" .
                "<th style='width:5px;'>$langOperator</th>" .
                "<th style='width:50px;'>$threshold_col_title</th>" .
                "<th style='width:10px;' class='text-center'>$langChoice</th>" .
                "</tr>";
        foreach ($lpinfo as $entry) {
            $m_id = Database::get()->querySingle("SELECT module_id FROM lp_rel_learnPath_module WHERE learnPath_id = ?d
                                                    AND `rank` = (SELECT MIN(`rank`) FROM lp_rel_learnPath_module WHERE learnPath_id = ?d)",
                                                $entry['id'], $entry['id']);
            if (($m_id) and $m_id->module_id > 0) {
                $lp_id = $entry['id'];
                $comments = empty($entry['comment']) ? '' : "<div style='margin-top: 10px;' class='text-muted'>". $entry['comment']. "</div>";
                $tool_content .= "<tr>";
                $tool_content .= "<td>&nbsp;".icon('fa-ellipsis-h')."&nbsp;&nbsp;<a href='{$urlServer}modules/learnPath/viewer.php?course=$course_code&amp;path_id=$lp_id&amp;module_id=$m_id->module_id'>" . q($entry['name']) . "</a>" . $comments . "</td>";
                $tool_content .= "<td>". selection(get_operators(), "operator[$lp_id]") . "</td>";
                $tool_content .= "<td class='text-center'><input style='width:50px;' type='text' name='threshold[$lp_id]' value=''></td>";
                $tool_content .= "<td class='text-center'><input type='checkbox' name='lp[]' value='$lp_id'></td>";
                $tool_content .= "</tr>";
            }
        }
        $tool_content .= "</table>";
        $tool_content .= "<div class='text-right'>";
        $tool_content .= "<input class='btn btn-primary' type='submit' name='$form_submit_name' value='$langAddModulesButton'></div></form>";

    }
}

function display_available_ratings($element, $element_id) {
    global $tool_content;
    $tool_content .= '..Still working on this...';
    return $tool_content;
}


/**
 * @brief multimedia display form
 * @param type $element
 * @param type $element_id
 * @param int $unit_id
 * @param int $unit_resource_id
 */
function display_available_multimedia($element, $element_id, $unit_id = 0, $unit_resource_id = 0) {

    require_once 'include/lib/mediaresource.factory.php';
    require_once 'include/lib/multimediahelper.class.php';

    global $tool_content, $themeimg, $course_id,
            $langTitle, $langDate, $langChoice,
            $langAddModulesButton, $langNoVideo, $course_code;

    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';
    $video_found = FALSE;
    $cnt1 = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM video WHERE course_id = ?d", $course_id)->cnt;
    $cnt2 = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM videolink WHERE course_id = ?d", $course_id)->cnt;
    $count = $cnt1 + $cnt2;
    if ($count > 0) {
        $video_found = TRUE;

        if ($unit_id) {
            $action = "manage.php?course=$course_code&manage=1&unit_id=$unit_id";
        } else {
            $action = "index.php?course=$course_code";
        }

        $tool_content .= "<form action=$action method='post'>" .
                         "<input type='hidden' name='$element_name' value='$element_id'>";
        $tool_content .= "<table class='table-default'>";
        $tool_content .= "<tr class='list-header'>" .
                         "<th class='text-left'>&nbsp;$langTitle</th>" .
                         "<th width='100'>$langDate</th>" .
                         "<th width='80'>$langChoice</th>" .
                         "</tr>";

        foreach (array('video', 'videolink') as $table) {
            if ($unit_id > 0) {
                if ($table == 'video') {
                    $sql_extra = ' ,path';
                } else {
                    $sql_extra = '';
                }
                if ($unit_resource_id) {
                    $result = Database::get()->queryArray("SELECT $table.id, $table.title, description, url, $table.date $sql_extra
                                    FROM $table, unit_resources
                                    WHERE $table.id = unit_resources.res_id
                                    AND unit_id = ?d
                                    AND unit_resources.id = ?d",
                        $unit_id, $unit_resource_id);
                } else {
                    $result = Database::get()->queryArray("SELECT $table.id, $table.title, description, url, $table.date $sql_extra
                                    FROM $table, unit_resources
                                    WHERE $table.id = unit_resources.res_id
                                    AND unit_id = ?d
                                    AND unit_resources.type = ?s
                                    AND unit_resources.visible = 1",
                        $unit_id, $table);
                }

            } else {
                $result = Database::get()->queryArray("SELECT * FROM $table WHERE (category IS NULL OR category = 0)
                                                        AND course_id = ?d
                                                        AND visible = 1
                                                        AND id NOT IN
                                                (SELECT resource FROM {$element}_criterion WHERE $element = ?d
                                                    AND resource!=''
                                                    AND activity_type IN ('" . ViewingEvent::VIDEO_ACTIVITY . "', '" . ViewingEvent::VIDEOLINK_ACTIVITY . "') AND module = ". MODULE_ID_VIDEO . ")", $course_id, $element_id);
            }

            foreach ($result as $row) {
                $row->course_id = $course_id;
                $description = empty($row->description) ? '' : "<div style='margin-top: 10px;' class='text-muted'>". q($row->description). "</div>";
                if ($table == 'video') {
                    $vObj = MediaResourceFactory::initFromVideo($row);
                    $videolink = MultimediaHelper::chooseMediaAhref($vObj);
                } else {
                    $vObj = MediaResourceFactory::initFromVideoLink($row);
                    $videolink = MultimediaHelper::chooseMedialinkAhref($vObj);
                }
                $tool_content .= "<tr>".
                    "<td>&nbsp;".icon('fa-film')."&nbsp;&nbsp;" . $videolink . $description . "</td>".
                    "<td class='text-center'>" . format_locale_date(strtotime($row->date), 'short', false) . "</td>" .
                    "<td class='text-center'><input type='checkbox' name='video[]' value='$table:$row->id'></td>" .
                    "</tr>";
            }
        }

        $sql = Database::get()->queryArray("SELECT * FROM video_category WHERE course_id = ?d ORDER BY name", $course_id);
        if ($sql) {
            foreach ($sql as $videocat) {
                $description = empty($videocat->description) ? '' : "<div style='margin-top: 10px;' class='text-muted'>". standard_text_escape($videocat->description). "</div>";
                $tool_content .= "<tr>";
                $tool_content .= "<td>".icon('fa-folder-o')."&nbsp;&nbsp;" . q($videocat->name) . $description . "</td>";
                $tool_content .= "<td align='center'><input type='checkbox' name='videocatlink[]' value='$videocat->id'></td>";
                $tool_content .= "</tr>";
                foreach (array('video', 'videolink') as $table) {
                    $sql2 = Database::get()->queryArray("SELECT * FROM $table WHERE category = ?d
                                                        AND visible = 1
                                                        AND id NOT IN
                                                    (SELECT resource FROM {$element}_criterion WHERE $element = ?d
                                                        AND resource!=''
                                                        AND activity_type IN ('" . ViewingEvent::VIDEO_ACTIVITY . "', '" . ViewingEvent::VIDEOLINK_ACTIVITY . "') AND module = " . MODULE_ID_VIDEO . ")", $videocat->id, $element_id);
                    foreach ($sql2 as $linkvideocat) {
                        $linkvideocat_description = empty($linkvideocat->description) ? '' : "<div style='margin-top: 10px;' class='text-muted'>". standard_text_escape($linkvideocat->description). "</div>";
                        $tool_content .= "<tr>";
                        $tool_content .= "<td>&nbsp;&nbsp;&nbsp;&nbsp;<img src='$themeimg/links_on.png' />&nbsp;&nbsp;<a href='" . q($linkvideocat->url) . "' target='_blank'>" .
                                q(($linkvideocat->title == '')? $linkvideocat->url: $linkvideocat->title) . "</a>" . $linkvideocat_description . "</td>";
                        $tool_content .= "<td class='text-center'>" . format_locale_date(strtotime($linkvideocat->date), 'short', false) . "</td>";
                        $tool_content .= "<td class='text-center'><input type='checkbox' name='video[]' value='$table:$linkvideocat->id'></td>";
                        $tool_content .= "</tr>";
                    }
                }
            }
        }
        $tool_content .= "</table>"
                . "<div class='text-right'>"
                . "<input class='btn btn-primary' type='submit' name='add_multimedia' value='".q($langAddModulesButton)."'>&nbsp;&nbsp;"
                . "</div>"
                . "</form>";
    }
    if (!$video_found) {
        $tool_content .= "<div class='alert alert-warning'>$langNoVideo</div>";
    }
}


/**
 * @brief ebook display form
 * @param type $element
 * @param type $element_id
 * @param int $unit_id
 * @param int $unit_resource_id
 */
function display_available_ebooks($element, $element_id, $unit_id = 0, $unit_resource_id = 0) {

  global $course_id, $course_code, $tool_content, $urlServer,
    $langAddModulesButton, $langChoice, $langNoEBook,
    $langEBook;

    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';

    if ($unit_id) {
        if ($unit_resource_id) {
            $result = Database::get()->queryArray("SELECT ebook.id, ebook.title
                                            FROM ebook, unit_resources
                                            WHERE ebook.id = unit_resources.res_id
                                            AND unit_id = ?d
                                            AND unit_resources.id = ?d"
                                        , $unit_id, $unit_resource_id);
        } else {
            $result = Database::get()->queryArray("SELECT ebook.id, ebook.title
                                        FROM ebook, unit_resources
                                        WHERE ebook.id = unit_resources.res_id
                                        AND unit_id = ?d
                                        AND unit_resources.type = 'ebook'
                                        AND unit_resources.visible = 1", $unit_id);
        }

    } else {
        $result = Database::get()->queryArray("SELECT * FROM ebook WHERE ebook.course_id = ?d
                                                AND ebook.visible = 1
                                                AND ebook.id NOT IN
                                        (SELECT resource FROM {$element}_criterion WHERE $element = ?d
                                        AND resource!='' AND activity_type = '" . ViewingEvent::EBOOK_ACTIVITY . "' AND module = " . MODULE_ID_EBOOK . ")", $course_id, $element_id);
    }

    if (count($result) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langNoEBook</div>";
    } else {
        if ($unit_id) {
            $action = "manage.php?course=$course_code&manage=1&unit_id=$unit_id";
        } else {
            $action = "index.php?course=$course_code";
        }
        $tool_content .= "<form action=$action method='post'>" .
                "<input type='hidden' name='$element_name' value='$element_id'>" .
                "<table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th class='text-left'>&nbsp;$langEBook</th>" .
                "<th style='width:20px;' class='text-center'>$langChoice</th>" .
                "</tr>";
        foreach ($result as $catrow) {
            $tool_content .= "<tr>";
            $tool_content .= "<td class='bold'>".icon('fa-book')."&nbsp;&nbsp;" .
                    q($catrow->title) . "</td>";
            $tool_content .= "<td class='text-center'>
                            <input type='checkbox' name='ebook[]' value='$catrow->id' />
                            <input type='hidden' name='ebook_title[$catrow->id]'
                               value='" . q($catrow->title) . "'></td>";
            $tool_content .= "</tr>";
            $q = Database::get()->queryArray("SELECT ebook_section.id AS sid,
                                    ebook_section.public_id AS psid,
                                    ebook_section.title AS section_title,
                                    ebook_subsection.id AS ssid,
                                    ebook_subsection.public_id AS pssid,
                                    ebook_subsection.title AS subsection_title,
                                    document.path,
                                    document.filename
                                    FROM ebook, ebook_section, ebook_subsection, document
                                    WHERE ebook.id = ?d AND
                                        ebook.course_id = ?d AND
                                        ebook_section.ebook_id = ebook.id AND
                                        ebook_section.id = ebook_subsection.section_id AND
                                        document.id = ebook_subsection.file_id AND
                                        document.course_id = ?d AND
                                        document.subsystem = " . EBOOK . "
                                        ORDER BY CONVERT(psid, UNSIGNED), psid,
                                                 CONVERT(pssid, UNSIGNED), pssid", $catrow->id, $course_id, $course_id);

            $ebook_url_base = "{$urlServer}modules/ebook/show.php/$course_code/$catrow->id/";
            $old_sid = false;
            foreach ($q as $row) {
                $sid = $row->sid;
                $unit_parameter = 'unit=' . $sid;
                $ssid = $row->ssid;
                $display_id = $sid . ',' . $ssid;
                $surl = $ebook_url_base . $display_id . '/' . $unit_parameter;
                if ($old_sid != $sid) {
                    $tool_content .= "<tr>
                                    <td class='section'>".icon('fa-link')."&nbsp;&nbsp;
                                        " . q($row->section_title) . "</td>
                                    <td align='center'><input type='checkbox' name='section[]' value='$sid' />
                                        <input type='hidden' name='section_title[$sid]'
                                               value='" . q($row->section_title) . "'></td></tr>";
                }
                $tool_content .= "<tr>
                                <td class='subsection'>".icon('fa-link')."&nbsp;&nbsp;
                                <a href='" . q($surl) . "' target='_blank'>" . q($row->subsection_title) . "</a></td>
                                <td align='center'><input type='checkbox' name='subsection[]' value='$ssid' />
                                   <input type='hidden' name='subsection_title[$ssid]'
                                          value='" . q($row->subsection_title) . "'></td>
                            </tr>";
                $old_sid = $sid;
            }
        }
        $tool_content .=
                "</table>
                <div class='text-right'>
                <input class='btn btn-primary' type='submit' name='add_ebook' value='$langAddModulesButton' /></div></form>";
    }
}


/**
 * @brief poll display form
 * @param type $element
 * @param type $element_id
 * @param int $unit_id
 * @param int $unit_resource_id
 */
function display_available_polls($element, $element_id, $unit_id = 0, int $unit_resource_id = 0) {

    global $course_id, $course_code, $urlServer, $tool_content,
            $langPollNone, $langQuestionnaire, $langChoice, $langAddModulesButton;

    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';

    if ($unit_id) {
        if ($unit_resource_id) {
            $result = Database::get()->queryArray("SELECT poll.pid, name, description FROM poll, unit_resources
                                            WHERE poll.pid = unit_resources.res_id
                                                AND poll.active = 1
                                                AND poll.end_date >= ". DBHelper::timeAfter() . "
                                                AND unit_id = ?d
                                                AND unit_resources.id = ?d"
                                            , $unit_id, $unit_resource_id);
        } else {
            $result = Database::get()->queryArray("SELECT poll.pid, name, description FROM poll, unit_resources
                                            WHERE poll.pid = unit_resources.res_id
                                                AND poll.active = 1
                                                AND poll.end_date >= ". DBHelper::timeAfter() . "
                                                AND unit_id = ?d
                                                AND unit_resources.type = 'poll'
                                                AND visible = 1", $unit_id);
        }

    } else {
        $result = Database::get()->queryArray("SELECT * FROM poll WHERE poll.course_id = ?d
                                    AND poll.active = 1
                                    AND poll.end_date >= ". DBHelper::timeAfter() . "
                                    AND poll.pid NOT IN
                                (SELECT resource FROM {$element}_criterion WHERE $element = ?d
                                    AND resource != '' AND activity_type = '" . ViewingEvent::QUESTIONNAIRE_ACTIVITY . "' AND module = " . MODULE_ID_QUESTIONNAIRE . ")",
            $course_id, $element_id);
    }

    $pollinfo = array();
    foreach ($result as $row) {
        $pollinfo[] = array(
            'id' => $row->pid,
            'title' => $row->name,
            'description' => $row->description);
    }
    if (count($pollinfo) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langPollNone</div>";
    } else {
        if ($unit_id) {
            $action = "manage.php?course=$course_code&manage=1&unit_id=$unit_id";
        } else {
            $action = "index.php?course=$course_code";
        }
        $tool_content .= "<form action=$action method='post'>" .
                "<input type='hidden' name='$element_name' value='$element_id'>" .
                "<table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th class='text-left'>&nbsp;$langQuestionnaire</th>" .
                "<th style='width:80px;' class='text-center'>$langChoice</th>" .
                "</tr>";
        foreach ($pollinfo as $entry) {
            $description = empty($entry['description']) ? '' : "<div style='margin-top: 10px;' class='text-muted'>". $entry['description']. "</div>";
            $tool_content .= "<tr>";
            $tool_content .= "<td>&nbsp;".icon('fa-question-circle')."&nbsp;&nbsp;<a href='{$urlServer}modules/questionnaire/pollresults.php?course=$course_code&amp;pid=$entry[id]'>" . q($entry['title']) . "</a>" . $description ."</td>";
            $tool_content .= "<td class='text-center'><input type='checkbox' name='poll[]' value='$entry[id]'></td>";
            $tool_content .= "</tr>";
        }
        $tool_content .= "</table>";
        $tool_content .= "<div class='text-right'>";
        $tool_content .= "<input class='btn btn-primary' type='submit' name='add_poll' value='$langAddModulesButton'></div></form>";
    }
}

/**
 * @brief wiki display form
 * @param type $element
 * @param type $element_id
 * @param int $unit_id
 */
function display_available_wiki($element, $element_id, $unit_id = 0) {

    global $tool_content, $langResourceAlreadyAdded,
    $langAddModulesButton, $langChoice, $langTitle, $langWikiPages,
    $course_code, $langOperator, $langValue;

    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';

    $result = Database::get()->queryArray("SELECT resource FROM {$element}_criterion WHERE $element = ?d
                                            AND resource IS NULL
                                            AND activity_type = '" . WikiEvent::ACTIVITY . "'
                                            AND module = " . MODULE_ID_WIKI . "", $element_id);

    if (count($result) > 0) {
        $tool_content .= "<div class='alert alert-warning'>$langResourceAlreadyAdded</div>";
    } else {
        if ($unit_id) {
            $action = "manage.php?course=$course_code&manage=1&unit_id=$unit_id";
        } else {
            $action = "index.php?course=$course_code";
        }

        $tool_content .= "<form action=$action method='post'>" .
                "<input type='hidden' name='$element_name' value='$element_id'>" .
                "<table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th class='text-left' style='width:70%;'>&nbsp;$langTitle</th>" .
                "<th style='width:5px;'>&nbsp;$langOperator</th>" .
                "<th style='width:30px;'>$langValue</th>" .
                "<th style='width:20px;' class='text-center'>$langChoice</th>" .
                "</tr>";

        $tool_content .= "<tr>
                            <td>$langWikiPages</td>
                            <td>". selection(get_operators(), "operator") . "</td>
                            <td class='text-center'><input style='width:50px;' type='text' name='threshold' value=''></td>
                            <td class='text-center'><input type='checkbox' name='wiki' value='1'></td>
                        </tr>";

        $tool_content .= "
                    </table>
                <div class='text-right'>
                    <input class='btn btn-primary' type='submit' name='add_wiki' value='$langAddModulesButton'>
                </div></form>";
    }
}

/**
 * @brief display course participation form
 * @param type $element
 * @param type $element_id
 * @param int $unit_id
 */
function display_available_participation($element, $element_id, $unit_id = 0) {

    global $tool_content, $course_code, $langHours,
           $langTitle, $langChoice, $langAddModulesButton,
           $langOperator, $langCourseParticipation, $langResourceAlreadyAdded;

    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';

    $result = Database::get()->queryArray("SELECT resource FROM {$element}_criterion WHERE $element = ?d
                                            AND resource IS NULL
                                            AND activity_type = '" . CourseParticipationEvent::ACTIVITY . "'", $element_id);

    if (count($result) > 0) {
        $tool_content .= "<div class='alert alert-warning'>$langResourceAlreadyAdded</div>";
    } else {
        if ($unit_id) {
            $action = "manage.php?course=$course_code&manage=1&unit_id=$unit_id";
        } else {
            $action = "index.php?course=$course_code";
        }

        $tool_content .= "<form action=$action method='post'>" .
                "<input type='hidden' name='$element_name' value='$element_id'>" .
                "<table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th class='text-left' style='width:70%;'>&nbsp;$langTitle</th>" .
                "<th style='width:5px;'>&nbsp;$langOperator</th>" .
                "<th style='width:30px;'>$langHours</th>" .
                "<th style='width:20px;' class='text-center'>$langChoice</th>" .
                "</tr>";

        $tool_content .= "<tr>
                            <td>$langCourseParticipation</td>
                            <td>". selection(get_operators(), "operator") . "</td>
                            <td class='text-center'><input style='width:50px;' type='text' name='threshold' value=''></td>
                            <td align='center'><input type='checkbox' name='participation' value='1'></td>
                        </tr>";

        $tool_content .= "
                    </table>
                <div class='text-right'>
                    <input class='btn btn-primary' type='submit' name='add_participation' value='$langAddModulesButton'>
                </div></form>";
    }
}

/**
 * @brief gradebooks display form
 * @param type $element
 * @param int $unit_id
 */
function display_available_gradebooks($element, $element_id, $unit_id = 0) {

    global $course_id, $tool_content, $langNoGradeBooks, $course_code, $urlServer,
           $langAvailableGradebooks, $langStart, $langFinish, $langChoice,
           $langAddModulesButton, $langOperator, $langGradebookGrade;

    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';

    $result = Database::get()->queryArray("SELECT * FROM gradebook WHERE course_id = ?d
                                    AND active = 1
                                    AND end_date > " . DBHelper::timeAfter() . "
                                    AND id NOT IN
                                    (SELECT resource FROM {$element}_criterion WHERE $element = ?d
                                        AND resource != ''
                                        AND activity_type = '" . GradebookEvent::ACTIVITY . "'
                                        AND module = " . MODULE_ID_GRADEBOOK . ")
                                    ORDER BY title", $course_id, $element_id);

    if (count($result) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langNoGradeBooks</div>";
    } else {

        if ($unit_id) {
            $action = "manage.php?course=$course_code&manage=1&unit_id=$unit_id";
        } else {
            $action = "index.php?course=$course_code";
        }
        $tool_content .= "<form action=$action method='post'>" .
            "<input type='hidden' name = '$element_name' value='$element_id'>" .
            "<table class='table-default'>" .
            "<tr class='list-header'>" .
            "<th class='text-left'>$langAvailableGradebooks</th>" .
            "<th style='width:160px;'>$langStart</th>" .
            "<th style='width:160px;'>$langFinish</th>" .
            "<th style='width:5px;'>$langOperator</th>" .
            "<th style='width:50px;'>$langGradebookGrade</th>" .
            "<th style='width:10px;' class='text-center'>$langChoice</th>" .
            "</tr>";

        foreach ($result as $row) {
            $gradebook_id = $row->id;
            $start_date = DateTime::createFromFormat('Y-m-d H:i:s', $row->start_date)->format('d/m/Y H:i');
            $end_date = DateTime::createFromFormat('Y-m-d H:i:s', $row->end_date)->format('d/m/Y H:i');
            $tool_content .= "<tr>" .
                "<td><a href ='{$urlServer}modules/gradebook/index.php?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "'>" . q($row->title) . "</a></td>" .
                "<td class='text-center'>" . $start_date . "</td>" .
                "<td class='text-center'>" . $end_date . "</td>" .
                "<td>". selection(get_operators(), "operator[$gradebook_id]") . "</td>".
                "<td class='text-center'><input style='width:50px;' type='text' name='threshold[$gradebook_id]' value=''></td>" .
                "<td class='text-center'><input name='gradebook[]' value='$gradebook_id' type='checkbox'></td>" .
                "</tr>";
        }

        $tool_content .= "</table>" .
            "<div align='right'><input class='btn btn-primary' type='submit' name='add_gradebook' value='$langAddModulesButton'></div></th></form>";
    }
}

/**
 * @brief Course Completion grade display form
 * @param $element
 * @param $element_id
 * @param int $unit_id
 */
function display_available_coursecompletiongrade($element, $element_id, $unit_id = 0) {

    global $tool_content, $langAddModulesButton, $langCourseCompletion,
           $course_code, $langTitle, $langValue, $langResourceAlreadyAdded,
           $langChoice, $langPercentage;

    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';

    $res = Database::get()->queryArray("SELECT id FROM {$element}_criterion WHERE $element = ?d
                                            AND resource IS NULL
                                            AND activity_type = '" . CourseCompletionEvent::ACTIVITY . "'
                                            AND module = " . MODULE_ID_PROGRESS, $element_id);

    if (count($res) > 0) {
        $tool_content .= "<div class='alert alert-warning'>$langResourceAlreadyAdded</div>";
    } else {
        if ($unit_id) {
            $action = "manage.php?course=$course_code&manage=1&unit_id=$unit_id";
        } else {
            $action = "index.php?course=$course_code";
        }
        $tool_content .= "<form action=$action method='post'>" .
            "<input type='hidden' name='$element_name' value='$element_id'>" .
            "<table class='table-default'>" .
            "<tr class='list-header'>" .
            "<th class='text-left' style='width:70%;'>&nbsp;$langTitle</th>" .
            "<th style='width:5px;'>&nbsp;$langValue</th>" .
            "<th style='width:30px;'>$langPercentage</th>" .
            "<th style='width:20px;' class='text-center'>$langChoice</th>" .
            "</tr>";

        $tool_content .= "<tr>" .
            "<td>" . $langCourseCompletion . "</td>" .
            "<td>". selection(get_operators(), "operator") . "</td>".
            "<td class='text-center'><input style='width:30px;' type='text' name='threshold' value=''></td>" .
            "<td class='text-center'><input name='" . CourseCompletionEvent::ACTIVITY . "' value='1' type='checkbox'></td>" .
            "</tr>";

        $tool_content .= "</table>" .
            "<div align='right'><input class='btn btn-primary' type='submit' name='add_coursecompletiongrade' value='$langAddModulesButton'></div></th></form>";
    }
}

/**
 * @brief gradebooks display form
 * @param string $element
 * @param int $element_id
 * @param int $unit_id
 */
function display_available_attendances($element, $element_id, $unit_id = 0) {

    global $course_id, $tool_content, $langNoAttendances, $course_code, $urlServer,
           $langAvailableAttendances, $langStart, $langFinish, $langChoice,
           $langAddModulesButton, $langOperator, $langAttendanceAbsences;

    $element_name = ($element == 'certificate')? 'certificate_id' : 'badge_id';

    $result = Database::get()->queryArray("SELECT * FROM attendance WHERE course_id = ?d
                                    AND active = 1
                                    AND end_date > " . DBHelper::timeAfter() . "
                                    AND id NOT IN
                                    (SELECT resource FROM {$element}_criterion WHERE $element = ?d
                                        AND resource != ''
                                        AND activity_type = '" . AttendanceEvent::ACTIVITY . "'
                                        AND module = " . MODULE_ID_ATTENDANCE . ")
                                    ORDER BY title", $course_id, $element_id);

    if (count($result) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langNoAttendances</div>";
    } else {

        if ($unit_id) {
            $action = "manage.php?course=$course_code&manage=1&unit_id=$unit_id";
        } else {
            $action = "index.php?course=$course_code";
        }
        $tool_content .= "<form action=$action method='post'>" .
            "<input type='hidden' name = '$element_name' value='$element_id'>" .
            "<table class='table-default'>" .
            "<tr class='list-header'>" .
            "<th class='text-left'>$langAvailableAttendances</th>" .
            "<th style='width:160px;'>$langStart</th>" .
            "<th style='width:160px;'>$langFinish</th>" .
            "<th style='width:5px;'>$langOperator</th>" .
            "<th style='width:50px;'>$langAttendanceAbsences</th>" .
            "<th style='width:10px;' class='text-center'>$langChoice</th>" .
            "</tr>";

        foreach ($result as $row) {
            $attendance_id = $row->id;
            $start_date = DateTime::createFromFormat('Y-m-d H:i:s', $row->start_date)->format('d/m/Y H:i');
            $end_date = DateTime::createFromFormat('Y-m-d H:i:s', $row->end_date)->format('d/m/Y H:i');
            $tool_content .= "<tr>" .
                "<td><a href ='{$urlServer}modules/attendance/index.php?course=$course_code&amp;attendance_id=$attendance_id'>" . q($row->title) . "</a></td>" .
                "<td class='text-center'>" . $start_date . "</td>" .
                "<td class='text-center'>" . $end_date . "</td>" .
                "<td>". selection(get_operators(), "operator[$attendance_id]") . "</td>".
                "<td class='text-center'><input style='width:50px;' type='text' name='threshold[$attendance_id]' value=''></td>" .
                "<td class='text-center'><input name='attendance[]' value='$attendance_id' type='checkbox'></td>" .
                "</tr>";
        }

        $tool_content .= "</table>" .
            "<div align='right'><input class='btn btn-primary' type='submit' name='add_attendance' value='$langAddModulesButton'></div></th></form>";
    }
}

/**
 * @brief display badge / certificate settings
 * @param type $element
 * @param type $element_id
 */
function display_settings($element, $element_id, $unit_id = 0): void
{

    global $tool_content, $course_id, $course_code, $urlServer, $langTitle,
           $langDescription, $langMessage, $langProgressBasicInfo, $langCourseCompletion,
           $langpublisher, $langEditChange, $is_editor;

    $field = ($element == 'certificate') ? 'template' : 'icon';

    $data = Database::get()->querySingle("SELECT issuer, $field, title, description, message, active, bundle
                            FROM $element WHERE id = ?d AND course_id = ?d AND unit_id = ?d", $element_id, $course_id, $unit_id);

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
            <div class='row'>
                <div class='col-xs-12'>
                    <div class='panel panel-default'>
                        <div class='panel-body'>
                            <div class='inner-heading'>
                                <div class='row'>
                                    <div class='col-sm-7'>
                                        <strong>$langProgressBasicInfo</strong>
                                    </div>";
                            if ($is_editor) {
                                $tool_content .= "<div class='col-sm-5 text-right'>
                                    <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;{$element}_id=$element_id&amp;edit=1' class='btn btn-primary btn-sm'>"
                                        . "<span class='fa fa-pencil'></span> &nbsp;&nbsp;$langEditChange
                                    </a>
                                </div>";
                            }
                            $tool_content .= "</div>
                            </div>
                            <div class='row'>
                                <div class='col-sm-5'>
                                    <img style='box-shadow: 0 0 15px 1px #bbb' class='img-responsive center-block' src='$icon_link'>
                                </div>
                                <div class='col-sm-7'>
                                    <div class='pn-info-title-sct'>$langTitle</div>
                                    <div class='pn-info-text-sct'>$title</div>
                                    <div class='pn-info-title-sct'>$langDescription</div>
                                    <div class='pn-info-text-sct'>$description</div>
                                    <div class='pn-info-title-sct'>$langMessage</div>
                                    <div class='pn-info-text-sct'>$message</div>
                                    <div class='pn-info-title-sct'>$langpublisher</div>
                                    <div class='pn-info-text-sct'>$issuer</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        ";
    } else { // course completion
        if (!$unit_id) {
            $tool_content .= "
        <div class='row'>
            <div class='col-xs-12'>
                <div class='panel panel-default'>
                    <div class='panel-body'>
                        <div class='row'>
                            <div class='col-sm-7'>
                                <h4><strong>$langCourseCompletion</strong></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>";
        }
    }
}



/**
 * @brief add / edit certificate / badge settings
 * @global string $tool_content
 * @global string type $head_content
 * @global type $course_code
 * @global type $course_id
 * @global type $langTitle
 * @global type $langSave
 * @global type $langInsert
 * @global type $langDescription
 * @global type $langpublisher
 * @global type $langMessage
 * @global type $langTemplate
 * @global type $langCertificateDeadline
 * @global type $langCertDeadlineHelp
 * @global type $language
 * @param type $element
 * @param type $element_id
 */
function certificate_settings($element, $element_id = 0) {

    global $tool_content, $head_content, $course_code,
           $langTemplate, $course_id, $language, $langMessage,
           $langTitle, $langSave, $langInsert, $langCertDeadlineHelp,
           $langDescription, $langpublisher, $langIcon, $langCertificateDeadline, $urlServer;

    load_js('bootstrap-datetimepicker');
    load_js('select2');

    $head_content .= "<script type='text/javascript'>
        $(function() {
            $('#enddatepicker').datetimepicker({
                    format: 'dd-mm-yyyy hh:ii',
                    pickerPosition: 'bottom-right',
                    language: '".$language."',
                    autoclose: true
                });
            $('#enablecertdeadline').change(function() {
                var dateType = $(this).prop('id').replace('enable', '');
                if($(this).prop('checked')) {
                    $('input#'+dateType).prop('disabled', false);
                    $('#late_sub_row').removeClass('hide');
                } else {
                    $('input#'+dateType).prop('disabled', true);
                    $('#late_sub_row').addClass('hide');
                }
            });

         if ($('#selectWithIcon').length > 0) {
            let urlServer = $('#urlServer').val();
            let select2Data;

            if ($('#certificate_hidden').length > 0) {
                let data = JSON.parse($('#certificate_hidden').val());
                select2Data = Object.keys(data).map(key => ({
                  id: key,
                  text: data[key],
                  image: urlServer + 'courses/user_progress_data/cert_templates/certificate' + key + '_thumbnail.png'
                }));
            }

            if ($('#badge_hidden').length > 0) {
                let data = JSON.parse($('#badge_hidden').val());
                select2Data = Object.keys(data).map(key => ({
                  id: key,
                  text: data[key],
                  image: urlServer + 'courses/user_progress_data/badge_templates/' + data[key] + '.png',
                  width: 48
                }));
            }

            $('#selectWithIcon').select2({
                  data: select2Data,
                  templateResult: formatOption,
                });

            function formatOption(option) {
              let dareturn = '<span><img ' + (option.width ? 'width=' + option.width : '') + ' src=' + option.image + ' /> ' + option.text + '</span>';
              return $(dareturn);
            }

            $('#selectWithIcon').on('change', function (e) {
                let dataType = $(this).data('type');

                if (dataType == 'certificate') {
                    let imgPath = urlServer + 'courses/user_progress_data/cert_templates/certificate' + $('#selectWithIcon').val() + '_thumbnail.png';
                    $('#selected_icon').attr('src', imgPath);
                } else if (dataType === 'badge') {
                    let imgPath = urlServer + 'courses/user_progress_data/badge_templates/' + $('#select2-selectWithIcon-container').text() + '.png';
                    $('#selected_icon').attr('src', imgPath);
                }

            });

            if ($('#certificate_hidden').length > 0) {
                let imgPath = urlServer + 'courses/user_progress_data/cert_templates/certificate' + $('#selectWithIcon').val() + '_thumbnail.png';
                $('#selected_icon').attr('src', imgPath);
            }

            if ($('#badge_hidden').length > 0) {
                let imgPath = urlServer + 'courses/user_progress_data/badge_templates/' + $('#select2-selectWithIcon-container').text() + '.png';
                $('#selected_icon').attr('src', imgPath);
                $('#selected_icon').attr('width', 48);
            }

         }

        });
        </script>";

    if ($element_id > 0) {      // edit
        $field = ($element == 'certificate')? 'template' : 'icon';
        $data = Database::get()->querySingle("SELECT issuer, $field, title, description, message, active, bundle, expires
                                FROM $element WHERE id = ?d AND course_id = ?d", $element_id, $course_id);
        $issuer = $data->issuer;
        $template = $data->$field;
        $title = $data->title;
        $description = $data->description;
        $message = $data->message;
        $cert_id = ($element == 'certificate')? "<input type='hidden' name='certificate_id' value='$element_id'>" : "<input type='hidden' name='badge_id' value='$element_id'>";
        $name = 'edit_element';
        if ($data->expires != null) {
            $certdeadline = date_format(date_create_from_format('Y-m-d H:i:s', $data->expires), 'd-m-Y H:i');
            $check_certdeadline = " checked";
            $statuscertdeadline = '';
        } else {
            $certdeadline = '';
            $check_certdeadline = '';
            $statuscertdeadline = "";
        }
    } else {        // add
        $issuer = q(get_config('institution'));
        $template = '';
        $title = '';
        $description = '';
        $message = '';
        $cert_id = '';
        $name = ($element == 'certificate')? 'newCertificate' : 'newBadge';
        $certdeadline = '';
        $check_certdeadline = '';
        $statuscertdeadline = '';
    }
    $tool_content .= "<div class='form-wrapper'>
            <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code' onsubmit=\"return checkrequired(this, 'antitle');\">
                <div class='form-group'>
                    <label for='title' class='col-sm-2 control-label'>$langTitle</label>
                    <div class='col-sm-10'>
                        <input class='form-control' type='text' placeholder='$langTitle' name='title' value='$title'>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='description' class='col-sm-2 control-label'>$langDescription: </label>
                    <div class='col-sm-10'>
                        <textarea class='form-control' name='description' rows='6'>$description</textarea>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='title' class='col-sm-2 control-label'>";
                    $tool_content .= ($element == 'certificate') ? $langTemplate : $langIcon;
                    $tool_content .= "</label>
                        <div class='col-sm-10'>";
                            $tool_content .= ($element == 'certificate') ? selection(get_certificate_templates(), 'template', $template, 'id="selectWithIcon" data-type="certificate"',) : selection(get_badge_icons(), 'template', $template, 'id="selectWithIcon"  data-type="badge"');
//                            if ($element == 'certificate') {
//                                $tool_content .= "<input id='certificate_hidden' type='hidden' value='".json_encode(get_certificate_templates())."'>";
//                            }
//                            if ($element == 'badge') {
//                                $tool_content .= "<input id='badge_hidden' type='hidden' value='".json_encode(get_badge_icons())."'>";
//                            }
                            if ($element == 'certificate' || $element == 'badge') {
                                $inputId = $element . '_hidden';
                                $value = ($element == 'certificate') ? json_encode(get_certificate_templates()) : json_encode(get_badge_icons());
                                $tool_content .= "<input id='$inputId' type='hidden' value='$value'>";
                            }
                            $tool_content .= "<input id='urlServer' type='hidden' value='".$urlServer."'>";
                        $tool_content .= "</div>
                </div>
                <div class='form-group'>
                    <div class='col-sm-2'></div>
                    <div class='col-sm-10'>
                        <img id='selected_icon' src='' alt=''>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='message' class='col-sm-2 control-label'>$langMessage</label>
                    <div class='col-sm-10'>
                        <textarea class='form-control' name='message' rows='3' maxlength='1200'>$message</textarea>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='title' class='col-sm-2 control-label'>$langpublisher</label>
                    <div class='col-sm-10'>
                        <input class='form-control' type='text' name='issuer' value='$issuer'>
                    </div>
                </div>
                <div class='form-group'>
                    <label class='col-sm-2 control-label'>$langCertificateDeadline:</label>
                    <div class='col-sm-10'>
                       <div class='input-group'>
                           <span class='input-group-addon'>
                             <input style='cursor:pointer;' type='checkbox' id='enablecertdeadline' name='enablecertdeadline' value='1' $check_certdeadline>
                           </span>
                           <input class='form-control' name='enddatepicker' id='enddatepicker' type='text' value='$certdeadline' $statuscertdeadline>
                       </div>
                       <span class='help-block'>&nbsp;&nbsp;&nbsp;<i class='fa fa-share fa-rotate-270'></i>$langCertDeadlineHelp</span>
                    </div>
                </div>
                $cert_id";
                $tool_content .= "<div class='form-group'>
                    <div class='col-xs-10 col-xs-offset-2'>".form_buttons(array(
                        array(
                                'text' => $langSave,
                                'name' => $name,
                                'value'=> $langInsert
                            ),
                        array(
                            'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code"
                            )
                        ))."</div>
                </div>
            </form>
        </div>";
}


/**
 * @brief student view certificates / badges / course completion
 */
function student_view_progress() {

    global $uid, $course_id, $urlServer, $tool_content, $langNoCertBadge,
            $langBadges, $course_code, $langCertificates, $langPrintVers,
           $langCourseCompletion, $head_content, $langDetail;

    require_once 'Game.php';

    $head_content .= "<style>
        #progress_circle {
          display: flex;
          width: 100px;
          height: 100px;
          margin: 5px;
          border-radius: 50%;
          background: conic-gradient(red var(--progress), gray 0deg);
          font-size: 0;
        }
        #progress_circle::after {
          content: attr(data-progress) '%';
          display: flex;
          justify-content: center;
          flex-direction: column;
          width: 100%;
          margin: 10px;
          border-radius: 50%;
          background: white;
          font-size: 2rem;
          text-align: center;
        }
    </style>";

    // check for completeness in order to refresh user data
    Game::checkCompleteness($uid, $course_id);
    $found = false;

    $course_completion_id = is_course_completion_active(); // is course completion active?
    if (isset($course_completion_id) and $course_completion_id > 0) {
        $found = true;
        $percentage = get_cert_percentage_completion('badge', $course_completion_id) . "%";
	$percentage_num = intval($percentage);
        $tool_content .= "
            <div class='row'>
                <div class='col-xs-12'>
                    <div class='panel panel-default'>
                        <div class='panel-body'>
                            <div class='inner-heading'>
                                <div class='row'>
                                    <div class='col-sm-7'>
                                        <strong>$langCourseCompletion</strong>
                                    </div>
                                </div>
                            </div>
                            <div class='res-table-wrapper'>
                                <div class='row res-table-row'>
                                    <div class='col-sm-2'>
                                        <i class='fa fa-trophy fa-4x' aria-hidden='true'></i>
                                    </div>
                                    <div class='col-sm-9'>
                                        <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;badge_id=$course_completion_id&amp;u=$uid'>$langCourseCompletion</a>
                                        <div class='progress' style='margin-top: 15px; margin-bottom: 15px;'>
                                            <p class='progress-bar active from-control-static' role='progressbar'
                                                    aria-valuenow='$percentage_num'
                                                    aria-valuemin='0' aria-valuemax='100' style='min-width: 2em; width: $percentage_num%;'>$percentage
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        ";
    }


    $iter = array('certificate', 'badge');
    foreach ($iter as $key) {
        ${'game_'.$key} = array();
    }
    // populate with data
    foreach ($iter as $key) {
        $gameQ = "SELECT a.*, b.title,"
                . " b.description, b.issuer, b.active, b.created, b.id"
                . " FROM user_{$key} a "
                . " JOIN {$key} b ON (a.{$key} = b.id) "
                . " WHERE a.user = ?d "
                . "AND b.course_id = ?d "
                . "AND b.active = 1 "
                . "AND b.bundle != -1 "
                . "AND (b.expires IS NULL OR b.expires > NOW())";
        $sql = Database::get()->queryArray($gameQ, $uid, $course_id);
        foreach ($sql as $game) {
                ${'game_'.$key}[] = $game;
            }
        }
        // display badges
        if (count($game_badge) > 0) {
            $found = true;

            $tool_content .= "
                <div class='row'>
                    <div class='col-xs-12'>
                        <div class='panel panel-default'>
                            <div class='panel-body'>
                                <div class='inner-heading'>
                                    <div class='row'>
                                        <div class='col-sm-7'>
                                            <strong>$langBadges</strong>
                                        </div>
                                    </div>
                                </div>";

            foreach ($game_badge as $key => $badge) {
                // badge icon
                $badge_filename = Database::get()->querySingle("SELECT filename FROM badge_icon WHERE id =
                                                         (SELECT icon FROM badge WHERE id = ?d)", $badge->id)->filename;

                $faded = ($badge->completed != 1) ? "faded" : '';
                if ($badge->total_criteria) {
                    $badge_percentage_num = round($badge->completed_criteria / $badge->total_criteria * 100, 0);
                } else {
                    $badge_percentage_num = 0;
                }
                $badge_percentage = $badge_percentage_num . '%';

                $tool_content .= "<div class='res-table-wrapper'>
                                    <div class='row res-table-row'>
                                        <div class='col-sm-2'>
                                            <img class = '$faded center-block' style='max-height: 60px;' class='img-responsive block-center' src='$urlServer" . BADGE_TEMPLATE_PATH . "$badge_filename'>
                                        </div>";
                                    $tool_content .= "
                                        <div class='col-sm-9'>
                                        <a href='index.php?course=$course_code&amp;badge_id=$badge->badge&amp;u=$badge->user' style='display: block; width: 100%'>" . ellipsize($badge->title, 40) . "</a>
                                            <div class='progress' style='margin-top: 15px; margin-bottom: 15px;'>
                                                <p class='progress-bar active from-control-static' role='progressbar'
                                                        aria-valuenow='$badge_percentage_num'
                                                        aria-valuemin='0' aria-valuemax='100' style='min-width: 2em; width: $badge_percentage;'>$badge_percentage
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>";
            }
            $tool_content .= "</div></div></div></div>";
        }

        // display certificates
        if (count($game_certificate) > 0) {
            $found = true;

            $tool_content .= "
                <div class='row'>
                    <div class='col-xs-12'>
                        <div class='panel panel-default'>
                            <div class='panel-body'>
                                <div class='inner-heading'>
                                    <div class='row'>
                                        <div class='col-sm-7'>
                                            <strong>$langCertificates</strong>
                                        </div>
                                    </div>
                                </div>";


            foreach ($game_certificate as $key => $certificate) {
                if ($certificate->completed == 1) {
                    $dateAssigned = format_locale_date(strtotime($certificate->assigned), null, false);
                } else {
                    $dateAssigned = '';
                }

                $tool_content .= "<div class='res-table-wrapper'>";
                $tool_content .= "<div class='col-xs-12 col-sm-6 col-xl-4'>";
                $tool_content .= "<a style='display:inline-block; width: 100%' href='index.php?course=$course_code&amp;certificate_id=$certificate->certificate&amp;u=$certificate->user'>";
                $tool_content .= "<div class='certificate_panel' style='padding-top: 10px;'>
                        <h4>$certificate->title</h4>
                        <div class='certificate_panel_date'>$dateAssigned</div>
                        <div class='certificate_panel_issuer'>$certificate->issuer</div>";
                if ($certificate->completed == 1) {
                    $tool_content .= "</a>";
                    $tool_content .= "<div class='certificate_panel_viewdetails'>";
                    $tool_content .= "&nbsp;&nbsp;<a href='index.php?course=$course_code&amp;certificate_id=$certificate->certificate&amp;u=$certificate->user&amp;p=1'>$langPrintVers</a>";
                    $tool_content .= "</div>";
                    $tool_content .= "<div class='certificate_panel_state'>
                        <i class='fa fa-check-circle fa-inverse state_success'></i>
                    </div>
                    <div class='certificate_panel_badge'>
                        <img src='" . $urlServer . "template/default/img/game/badge.png'>
                    </div>";
                    $tool_content .= "</div>";
                } else {
                    $score = round($certificate->completed_criteria / $certificate->total_criteria * 100, 0);
                    $angle = round($certificate->completed_criteria / $certificate->total_criteria * 360, 2);
                    $tool_content .= "<div id='progress_circle' data-progress='$score' style='--progress: {$angle}deg; margin-left: 100px; margin-top: 10px;'>$score%</div>";
                    $tool_content .= "</div></a>";
                }
                $tool_content .= "</div>";
                $tool_content .= "</div>";
            }
            $tool_content .= "</div></div></div></div>";
        }

    if (!$found) {
        $tool_content .= "<div class='alert alert-info'>$langNoCertBadge</div>";
    }
}


/**
 * @brief display users progress (teacher view)
 * @param type $element
 * @param type $element_id
 */
function display_users_progress($element, $element_id) {

    global $tool_content, $course_code, $course_id, $langNoCertificateUsers, $langSurnameName, $langUsersS,
           $langAmShort, $langID, $langProgress, $langDetails, $langUsersCertResults, $langCompletedIn;

    if ($element == 'certificate') {
        $sql = Database::get()->queryArray("SELECT user.surname, user.givenname, user.am, user, completed, completed_criteria, total_criteria, assigned
                                            FROM user_certificate
                                            JOIN course_user ON user_certificate.user=course_user.user_id
                                             JOIN user ON user.id = user_certificate.user
                                                AND course_user.status = " .USER_STUDENT . "
                                                AND editor = 0
                                                AND course_id = ?d
                                                AND certificate = ?d
                                            ORDER BY user.surname, user.givenname
                                            ASC", $course_id, $element_id);
        $certified_users = Database::get()->querySingle("SELECT COUNT(*) AS t FROM user_certificate
                                            JOIN course_user ON user_certificate.user=course_user.user_id
                                                AND status = " .USER_STUDENT . "
                                                AND editor = 0
                                                AND course_id = ?d
                                                AND completed = 1
                                                AND certificate = ?d", $course_id,$element_id)->t;
        $param_name = 'certificate_id';
    } else {
        $sql = Database::get()->queryArray("SELECT user.surname, user.givenname, user.am, user, completed, completed_criteria, total_criteria, assigned
                                            FROM user_badge
                                            JOIN course_user ON user_badge.user=course_user.user_id
                                            JOIN user ON user.id = user_badge.user
                                                AND course_user. status = " .USER_STUDENT . "
                                                AND editor = 0
                                                AND course_id = ?d
                                                AND badge = ?d
                                            ORDER BY user.surname, user.givenname
                                            ASC", $course_id, $element_id);
        $certified_users = Database::get()->querySingle("SELECT COUNT(*) AS t FROM user_badge
                                            JOIN course_user ON user_badge.user=course_user.user_id
                                                AND status = " .USER_STUDENT . "
                                                AND editor = 0
                                                AND course_id = ?d
                                                AND completed = 1
                                                AND badge = ?d", $course_id, $element_id)->t;
        $param_name = 'badge_id';
    }
    $all_users = Database::get()->querySingle("SELECT COUNT(*) AS total FROM course_user
                                        WHERE status = " .USER_STUDENT . "
                                            AND editor = 0
                                            AND course_id = ?d", $course_id)->total;

    if (count($sql) > 0) {
        $tool_content .= "<div class='alert alert-info'>$langUsersCertResults $certified_users / $all_users $langUsersS.</div>";
        $tool_content .= "<table class='table-default custom_list_order'>";
            $tool_content .= "<thead>
                        <tr>
                          <th style='width:5%'>$langID</th>
                          <th>$langSurnameName</th>
                          <th class='text-center' style='width: 30%;'>$langProgress</th>
                        </tr>
                    </thead>
                    <tbody>";
        $cnt = 1;
        foreach ($sql as $user_data) {
            if ($user_data->completed == 1) {
                $icon = icon('fa-check-circle');
            } else {
                $icon = icon('fa-hourglass-2');
            }
	    $user_am = q($user_data->am);
	    $user_percentage = $user_data->total_criteria?
		    (round($user_data->completed_criteria / $user_data->total_criteria * 100, 0) . '%'): '';
            $tool_content .= "<tr>
                    <td>". $cnt++ . "</td>
		    <td>" . display_user($user_data->user) .
			($user_am? "<br>($langAmShort: $user_am)": '') . "
                    </td>
                    <td class='text-center'>$user_percentage&nbsp;$icon&nbsp;"
                          . "<small><a href='index.php?course=$course_code&amp;$param_name=$element_id&amp;u=$user_data->user'>$langDetails</a></small>";
            if (!is_null($user_data->assigned)) {
                $tool_content .= "<div><small>$langCompletedIn: " . format_locale_date(strtotime($user_data->assigned), 'short') . "</small></div>";
            }

            $tool_content .= "</td></tr>";
        }
        $tool_content .= "</tbody></table>";
    } else {
        $tool_content .= "<div class='alert alert-info'>$langNoCertificateUsers</div>";
    }
}


/**
 * @brief detailed view of user progress in various subsystems
 * @param type $element
 * @param type $element_id
 * @param type $user_id
 */
function display_user_progress_details($element, $element_id, $user_id) {

    global $tool_content, $langNoUserActivity, $langAttendanceActList, $langpublisher,
           $langInstallEnd, $langTotalPercentCompleteness, $langTitle, $langDescription,
           $langCertAddress, $langRubricCrit;

    $element_title = get_cert_title($element, $element_id);
    $resource_data = array();
    $bundle = Database::get()->querySingle("SELECT bundle FROM $element WHERE id = ?d", $element_id)->bundle;
    // certificate
    if ($element == 'certificate') {
        $cert_public_link = '';
        // create public link if user has completed certificate and there is cert identifier
        if (has_certificate_completed($user_id, $element, $element_id) and get_cert_identifier($element_id, $user_id) != null) {
            $cert_public_link = "<div class='pn-info-title-sct'>$langCertAddress</div>
                                <div class='pn-info-text-sct'>" . certificate_link($element_id, $user_id) . "</div>";
        }
        $sql = Database::get()->queryArray("SELECT certificate_criterion, activity_type, threshold, operator FROM user_certificate_criterion JOIN certificate_criterion
                                                            ON user_certificate_criterion.certificate_criterion = certificate_criterion.id
                                                                AND certificate_criterion.certificate = ?d
                                                                AND user = ?d", $element_id, $user_id);
        // incomplete user resources
        $sql2 = Database::get()->queryArray("SELECT id, activity_type, threshold, operator FROM certificate_criterion WHERE certificate = ?d
                                                    AND id NOT IN
                                            (SELECT certificate_criterion FROM user_certificate_criterion JOIN certificate_criterion
                                                ON user_certificate_criterion.certificate_criterion = certificate_criterion.id
                                                AND certificate_criterion.certificate = ?d AND user = ?d)", $element_id, $element_id, $user_id);
        // completed user resources
        $sql3 = "SELECT completed, completed_criteria, total_criteria FROM user_certificate WHERE certificate = ?d AND user = ?d";
    } else { // badge
        $cert_public_link = '';
        $sql = Database::get()->queryArray("SELECT badge_criterion, activity_type, threshold, operator FROM user_badge_criterion JOIN badge_criterion
                                                            ON user_badge_criterion.badge_criterion = badge_criterion.id
                                                                AND badge_criterion.badge = ?d
                                                                AND user = ?d", $element_id, $user_id);
        // incomplete user resources
        $sql2 = Database::get()->queryArray("SELECT id, activity_type, threshold, operator FROM badge_criterion WHERE badge = ?d
                                                    AND id NOT IN
                                            (SELECT badge_criterion FROM user_badge_criterion JOIN badge_criterion
                                                ON user_badge_criterion.badge_criterion = badge_criterion.id
                                                AND badge_criterion.badge = ?d AND user = ?d)", $element_id, $element_id, $user_id);
        $sql3 = "SELECT completed, completed_criteria, total_criteria FROM user_badge WHERE badge = ?d AND user = ?d";
    }
	$user_data = Database::get()->querySingle($sql3, $element_id, $user_id);
    if (count($sql) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langNoUserActivity</div>";
    }

	$tool_content .= "
        <div class='row'>
            <div class='col-xs-12'>
                <div class='panel panel-default'>
                    <div class='panel-body'>
                        <div class='inner-heading'>
                            <div class='row'>
                                <div class='col-sm-7'>
                                    <strong>$element_title</strong>
                                </div>
                            </div>
                        </div>
                        <div class='row'>
                            <div class='col-sm-12'>
                            	<div class='pn-info-title-sct'>$langTotalPercentCompleteness</div>";
                                if ($user_data) {
                                    $percentage_value = $user_data->total_criteria?
                                        round($user_data->completed_criteria / $user_data->total_criteria * 100, 0): 0;
                                } else {
                                    $percentage_value = 0;
                                }
				$percentage = $percentage_value . '%';
                                $tool_content .= "<div class='progress' style='margin-top: 15px; margin-bottom: 15px;'>
                                            <p class='progress-bar active from-control-static' role='progressbar'
                                                    aria-valuenow='$percentage_value'
                                                    aria-valuemin='0' aria-valuemax='100' style='min-width: 2em; width: $percentage;'>$percentage
                                            </p>
                                        </div>";
                                $cert_desc = get_cert_desc($element, $element_id);
                                if (!empty($cert_desc)) {
                                    $tool_content .= "<div class='pn-info-title-sct'>$langDescription</div>";
                                    $tool_content .= "<div class='pn-info-text-sct'>" . get_cert_desc($element, $element_id) . "</div>";
                                }
                                if ($bundle != -1) { // don't display issuer it if's course completion
                                    $tool_content .= "<div class='pn-info-title-sct'>$langpublisher</div>
                                                    <div class='pn-info-text-sct'>" . get_cert_issuer($element, $element_id) . "</div>";
                                }
                            $tool_content .= "
                                $cert_public_link
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    ";

	$tool_content .= "
            <div class='row'>
            <div class='col-xs-12'>
                <div class='panel panel-default'>
                    <div class='panel-body'>
                        <div class='inner-heading'>
                            <div class='row'>
                                <div class='col-sm-10'>
                                    <strong>$langAttendanceActList</strong>
                                </div>
                            </div>
                        </div>
                        <div class='res-table-wrapper'>
                            <div class='row res-table-header'>
                                <div class='col-sm-8'>
                                    $langTitle
                                </div>
                                <div class='col-sm-2 text-center'>
                                    $langRubricCrit
                                </div>
                                <div class='col-sm-2 text-center'>
                                    $langInstallEnd
                                </div>
                            </div>";

    // completed criteria
	foreach ($sql as $user_criterion) {
        if ($element == 'badge') {
            $resource_data = get_resource_details($element, $user_criterion->badge_criterion);
        } else {
            $resource_data = get_resource_details($element, $user_criterion->certificate_criterion);
        }
		$activity = $resource_data['title'] . "&nbsp;<small>(" .$resource_data['type'] . ")</small>";

        if (!empty($user_criterion->operator) && $user_criterion->activity_type != AssignmentSubmitEvent::ACTIVITY) {
            $op = get_operators();
            $op_content = $op[$user_criterion->operator];
        } else {
            $op_content = "&mdash;";
        }
        $threshold = $user_criterion->threshold;
        if ($user_criterion->activity_type == AssignmentSubmitEvent::ACTIVITY) {
            $threshold = "";
        }

		$tool_content .= "
                <div class='row res-table-row'>
                    <div class='col-sm-8'>$activity</div>
                    <div class='col-sm-2 text-center'>" . $op_content . " " . $threshold . "</div>
                    <div class='col-sm-2 text-center'>" . icon('fa-check-circle') . "</div>
                </div>";
	}
    // uncompleted criteria
	foreach ($sql2 as $user_criterion) {
		$resource_data = get_resource_details($element, $user_criterion->id);
		$activity = $resource_data['title'] . "&nbsp;<small>(" .$resource_data['type'] . ")</small>";
        if (!empty($user_criterion->operator) && $user_criterion->activity_type != AssignmentSubmitEvent::ACTIVITY) {
            $op = get_operators();
            $op_content = $op[$user_criterion->operator];
        } else {
            $op_content = "&mdash;";
        }
        $threshold = $user_criterion->threshold;
        if ($user_criterion->activity_type == AssignmentSubmitEvent::ACTIVITY) {
            $threshold = "";
        }
		$tool_content .= "
                <div class='row res-table-row not_visible'>
                    <div class='col-sm-8'>$activity</div>
                    <div class='col-sm-2 text-center'>" . $op_content . " " . $threshold . "</div>
                </div>";
	}
	$tool_content .= "
            <div class='row res-table-header'>
                <div class='col-sm-9'>$langTotalPercentCompleteness</div>";
                if ($user_data) {
                    $percentage = $user_data->total_criteria?
                        (round($user_data->completed_criteria / $user_data->total_criteria * 100, 0) . "%"): '-';
                    $tool_content .= "<div class='col-sm-3 text-center'><em>$percentage</em></div>";
                } else {
                    $tool_content .= "<div class='col-sm-3 text-center'><em>0%</em></div>";
                }
            $tool_content .= "</div>";
            $tool_content .= "
                    </div></div>
                </div>
            </div>
        </div>
        ";
}


/**
 * @brief return an array of operators
 * @return type
 */
function get_operators() {

    return array('gt' => '>',
                 'get' => '>=',
                 'lt' => '<',
                 'let' => '<=',
                 'eq' => '=',
                 'neq' => '!=');
}


/**
 * @brief return array with criteria having operators
 * @return type
 */
function criteria_with_operators() {

    return array(AssignmentEvent::ACTIVITY,
                 ExerciseEvent::ACTIVITY,
                 LearningPathEvent::ACTIVITY,
                 LearningPathDurationEvent::ACTIVITY,
                 WikiEvent::ACTIVITY,
                 ForumEvent::ACTIVITY,
                 ForumTopicEvent::ACTIVITY,
                 BlogEvent::ACTIVITY,
                 CommentEvent::BLOG_ACTIVITY,
                 GradebookEvent::ACTIVITY,
                 CourseCompletionEvent::ACTIVITY,
                 AttendanceEvent::ACTIVITY);
}
