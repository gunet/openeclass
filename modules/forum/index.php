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

/**
 * @file index.php
 * @brief display forum page
 */
$require_login = TRUE;
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'forum';
require_once '../../include/baseTheme.php';
$toolName = $langForums;

/* * ** The following is added for statistics purposes ** */
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_FORUM);
/* * *********************************** */

require_once 'functions.php';
require_once 'modules/group/group_functions.php';

load_js('tools.js');
load_js('datatables');

if ($is_editor) {
    $action_bar = action_bar(array(
                array('title' => $langCategoryAdd,
                    'url' => "forum_admin.php?course=$course_code",
                    'icon' => 'fa-plus-circle',
                    'level' => 'primary-label',
                    'button-class' => 'btn-success'),
                array('title' => $langConfig,
                    'url' => "forum_admin.php?course=$course_code&amp;settings=yes",
                    'icon' => 'fa-cogs',
                    'level' => 'primary',
                    'button-class' => 'btn-secondary')
            ));
    $tool_content .= $action_bar;
}



if (isset($_GET['forumcatnotify'])) { // modify forum category notification
    if (isset($_GET['cat_id'])) {
        $cat_id = $_GET['cat_id'];
    }
    $rows = Database::get()->querySingle("SELECT COUNT(*) AS count FROM forum_notify
        WHERE user_id = ?d AND cat_id = ?d AND course_id = ?d", $uid, $cat_id, $course_id);
    if ($rows->count > 0) {
        Database::get()->query("UPDATE forum_notify SET notify_sent = ?d WHERE user_id = ?d AND cat_id = ?d AND course_id = ?d", $_GET['forumcatnotify'], $uid, $cat_id, $course_id);
    } else {
        Database::get()->query("INSERT INTO forum_notify SET user_id = ?d, cat_id = ?d, notify_sent = 1, course_id = ?d", $uid, $cat_id, $course_id);
    }
    redirect("index.php?course=$course_code");
} elseif (isset($_GET['forumnotify'])) { // modify forum notification
    if (isset($_GET['forum_id'])) {
        $forum_id = $_GET['forum_id'];
    }
    $rows = Database::get()->querySingle("SELECT COUNT(*) AS count FROM forum_notify
        WHERE user_id = ?d AND forum_id = ?d AND course_id = ?d", $uid, $forum_id, $course_id);
    if ($rows->count > 0) {
        Database::get()->query("UPDATE forum_notify SET notify_sent = ?d WHERE user_id = ?d AND forum_id = ?d AND course_id = ?d", $_GET['forumnotify'], $uid, $forum_id, $course_id);
    } else {
        Database::get()->query("INSERT INTO forum_notify SET user_id = ?d, forum_id = ?d, notify_sent = 1, course_id = ?d", $uid, $forum_id, $course_id);
    }
    redirect("index.php?course=$course_code");
}

/*
 * Populate data with forum categories
 */
$categories = Database::get()->queryArray("SELECT id, cat_title FROM forum_category WHERE course_id = ?d ORDER BY id ", $course_id);

$total_categories = count($categories);

if ($total_categories > 0) {
    $forum_row = Database::get()->queryArray("SELECT f.id forum_id, f.*, p.post_time, p.topic_id, p.poster_id
            FROM forum f LEFT JOIN forum_post p ON p.id = f.last_post_id
            WHERE f.course_id = ?d
            ORDER BY f.cat_id, f.id", $course_id);

    $tool_content .= "<div class='col-12'>
                        <div class='row row-cols-1 g-4'>";
    foreach ($categories as $cat_row) {
        $cat_title = q($cat_row->cat_title);
        $catNum = $cat_row->id;
        $sql = Database::get()->querySingle("SELECT notify_sent FROM forum_notify
                                                        WHERE user_id = ?d AND cat_id = ?d AND course_id = ?d", $uid, $catNum, $course_id);
        if ($sql) {
            $action_notify = $sql->notify_sent;
        } else {
            $action_notify = false;
        }

        if (!isset($action_notify)) {
            $link_notify = false;
        } else {
            $link_notify = toggle_link($action_notify);
        }

        $tool_content .= "<div class='col'>";

        $tool_content .= "<div class='card panelCard card-default px-lg-4 py-lg-3 h-100'>
                            <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>";
        $tool_content .= "
                                <div>
                                        <strong>$langCategory :</strong> $cat_title
                                </div>";

                            $tool_content .= action_button(
                                    array(
                                        array(
                                            'title' => $langEditChange,
                                            'url' => "forum_admin.php?course=$course_code&amp;forumcatedit=yes&amp;cat_id=$catNum",
                                            'icon' => 'fa-edit',
                                            'show' => $is_editor,
                                            'btn_class' => 'submitAdminBtn'
                                        ),
                                        array(
                                            'title' => $langNewForum,
                                            'url' => "forum_admin.php?course=$course_code&amp;forumgo=yes&amp;cat_id=$catNum",
                                            'icon' => 'fa-plus-circle',
                                            'show' => $is_editor,
                                            'btn_class' => 'submitAdminBtn'
                                        ),
                                        array(
                                            'title' => $action_notify ? $langStopNotify : $langNotify,
                                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;forumcatnotify=$link_notify&amp;cat_id=$catNum",
                                            'icon' => $action_notify ? 'fa-envelope-open' : 'fa-envelope',
                                            'btn_class' => $action_notify ? 'submitAdminBtn' : 'submitAdminBtn',
                                            'show' => (!setting_get(SETTING_COURSE_FORUM_NOTIFICATIONS)),
                                        ),
                                        array('title' => $langDelete,
                                            'url' => "forum_admin.php?course=$course_code&amp;forumcatdel=yes&amp;cat_id=$catNum",
                                            'icon' => 'fa-xmark',
                                            'class' => 'delete',
                                            'btn_class' => 'deleteAdminBtn',
                                            'confirm' => $langConfirmDelete,
                                            'show' => $is_editor

                                        )
                                    )
                                );
             $tool_content .= " 
                            </div>
        <div class='card-body'>";

        $tool_content .= "<div class='table-responsive mt-0'><table class='table-default forum_index'>";
        $tool_content .= "<thead>";
        $tool_content .= "<tr class='list-header'>
            <th>$toolName</th>
            <th class='text-center'>$langTopics</th>
            <th class='text-center'>$langPosts</th>
            <th>$langLastPost</th>
            <th aria-label='$langSettingSelect'></th>
          </tr>";
        $tool_content .= "</thead>";

        $tool_content .= "<tbody>";
        // display forum topics
        if ($forum_row) {
            foreach ($forum_row as $forum_data) {
                unset($last_post);
                $cat_id = $cat_row->id;
                $human_last_post_time = '';
                if (Database::get()->querySingle("SELECT COUNT(*) AS count FROM forum WHERE cat_id = ?d AND course_id = ?d", $cat_id, $course_id)->count > 0) {
                    // if category forum topics are found
                    if ($forum_data->cat_id == $cat_id) {
                        if ($forum_data->post_time) {
                            $last_post = $forum_data->post_time;
                            $last_post_datetime = $forum_data->post_time;
                            list($last_post_date, $last_post_time) = explode(' ', $last_post_datetime);
                            list($year, $month, $day) = explode('-', $last_post_date);
                            list($hour, $min) = explode(':', $last_post_time);
                            $last_post_time = mktime($hour, $min, 0, $month, $day, $year);
                            $human_last_post_time = date('d/m/Y -  H:i', $last_post_time);
                        }
                        if (empty($last_post)) {
                            $last_post = $langNoPosts;
                        }
                        $forum_name = q($forum_data->name);
                        if ($forum_data->poster_id) {
                            $last_user_post = uid_to_name($forum_data->poster_id);
                        } else {
                            $last_user_post = '';
                        }
                        $last_post_topic_id = $forum_data->topic_id;
                        $total_posts = $forum_data->num_posts;
                        $total_topics = $forum_data->num_topics;
                        $desc = q($forum_data->desc);
                        $forum_id = $forum_data->id;
                        $is_member = false;
                        $group_id = init_forum_group_info($forum_id);
                        $member = $is_member ? "&nbsp;&nbsp;($langMyGroup)" : '';
                        // Show link to forum if:
                        //  - user is admin of course
                        //  - forum doesn't belong to group
                        //  - forum belongs to group and group forums are enabled and
                        //     - user is member of group
                        if (setting_get(SETTING_COURSE_FORUM_NOTIFICATIONS)) { // first lookup for course setting
                            $forum_action_notify = false;
                        } else { // if it's not set lookup user setting
                            $forum_action_notify = Database::get()->querySingle("SELECT notify_sent FROM forum_notify
                                WHERE user_id = ?d
                                      AND forum_id = ?d
                                      AND course_id = ?d", $uid, $forum_id, $course_id);
                            if ($forum_action_notify) {
                                $forum_action_notify = $forum_action_notify->notify_sent;
                            } else {
                                $forum_action_notify = false;
                            }
                        }
                        $tool_content .= "<tr><td>";
                        if ($is_editor or !$group_id or ($has_forum and $is_member)) {
                            $forum_active = true;
                            if ($forum_action_notify) {
                                $tool_content .= "<span class='float-end label label-primary' data-bs-toggle='tooltip' data-bs-placement='bottom' title='" . q($langNotify) . "'><i class='fa fa-envelope'></i></span>";
                            }
                            $tool_content .= "<a href='viewforum.php?course=$course_code&amp;forum=$forum_id'>
                                                                $forum_name
                                                                </a><div class='smaller'>" . $member . "</div>";
                        } else {
                            $forum_active = $is_editor || ($has_forum && $is_member);
                            $tool_content .= $forum_name;
                        }
                        $tool_content .= "<div class='smaller'>$desc</div>" .
                            "</td>" .
                            "<td class='text-center'>$total_topics</td>" .
                            "<td class='text-center'>$total_posts</td>";
                        if ($total_topics > 0 && $total_posts > 0) {
                            $tool_content .= "<td data-order='$human_last_post_time'><span class='smaller'>" . q($last_user_post) . "&nbsp;";
                            if ($is_editor or ! $group_id or ($has_forum and $is_member)) {
                                $tool_content .= "<a aria-label='$langLastPost' href='viewtopic.php?course=$course_code&amp;topic=$last_post_topic_id&amp;forum=$forum_id'>".icon('fa-comment-o', $langLastPost) ."</a>";
                            }
                            $tool_content .= "<br>$human_last_post_time</span></td>";
                        } else {
                            $tool_content .= "<td data-order='00/00/0000 - 00:00'><div class='inactive'>$langNoPosts</div></td>";
                        }

                        if (!isset($forum_action_notify)) {
                            $forum_link_notify = false;
                        } else {
                            $forum_link_notify = toggle_link($forum_action_notify);
                        }
                        $tool_content .= "<td class='text-end'>";



                        $tool_content .= action_button(
                            array(
                                array(
                                    'title' => $langEditChange,
                                    'url' => "forum_admin.php?course=$course_code&amp;forumgoedit=yes&amp;forum_id=$forum_id&amp;cat_id=$catNum",
                                    'icon' => 'fa-edit',
                                    'show' => $is_editor),
                                array(
                                    'title' => $forum_action_notify ? $langStopNotify : $langNotify,
                                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;forumnotify=$forum_link_notify&amp;forum_id=$forum_id",
                                    'icon' => $action_notify ? 'fa-envelope-open' : 'fa-envelope',
                                    'show' => $forum_active && !setting_get(SETTING_COURSE_FORUM_NOTIFICATIONS)),
                                array(
                                    'title' => $langDelete,
                                    'url' => "forum_admin.php?course=$course_code&amp;forumgodel=yes&amp;forum_id=$forum_id&amp;cat_id=$catNum",
                                    'icon' => 'fa-xmark',
                                    'class' => 'delete',
                                    'confirm' => $langConfirmDelete,
                                    'show' => $is_editor))
                                );





                    }

                } else {
                    $tool_content .= "<tr>" .
                        "<td colspan='6' class='alert2'><span class='not_visible'> - $langNoForumsCat - </span></td>" .
                        "</tr>";
                    break;
                }
            }
        } else {
            $tool_content .= "<tr><td colspan='8'><span class='not_visible'> - ".$langNoForumTopic." - </td></tr>";
        }
        $tool_content .= "</tbody></table></div></div></div>";


        $tool_content .= "</div>";

    }

    $tool_content .= "</div>
    
        <script>
            $(document).ready(function() {
                $('.table-default').DataTable({
                    ordering: true,
                    searching: true,
                    columnDefs: [
                        {
                            targets: 0, // Enable searching only for the first column
                            searchable: true
                        },
                        {
                            targets: '_all', // Disable searching for all other columns
                            searchable: false
                        },
                        {
                            targets: -1, // No orderable for last column
                            orderable: false
                        }
                    ]
                });
            });
        </script>

    </div>";

} else {
    $tool_content .= "<div class='col-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoForums</span></div></div>";
}
add_units_navigation(true);
if ($is_editor) {
    draw($tool_content, 2, null, $head_content);
} else {
    draw($tool_content, 2);
}
