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

$require_login = true;
$require_help = true;
$helpTopic = 'Gradebook';

require_once '../include/baseTheme.php';
require_once 'modules/progress/Game.php';

if (is_module_disable(MODULE_ID_PROGRESS,MODULE_ID_PROGRESS)) {
    redirect_to_home_page();
}

$toolName = $langPortfolio;
$pageName = $langMyCertificates;
$content = false;

$table_content = '';
$courses = Database::get()->queryArray('SELECT course.id course_id, code, title
                FROM course, course_user, user, course_module
                    WHERE course.id = course_user.course_id
                      AND course.visible <> ' . COURSE_INACTIVE . '
                      AND course_module.course_id = course_user.course_id
                      AND module_id = ' . MODULE_ID_PROGRESS . '
                      AND course_module.visible <> 0
                      AND course_user.user_id = ?d
                      AND user.id = ?d', $uid, $uid);

if (count($courses) > 0) {
    $table_content .= "<div class = 'table-responsive'>
            <table class='table-default'>
            <thead><tr class='list-header'><th>$langCourse</th><th>$langResults</th></tr></thead>";

    // get completed certificates with public url
    $sql = Database::get()->queryArray("SELECT course_title, cert_title, cert_id, identifier "
                                        . "FROM certified_users "
                                        . "WHERE user_fullname = ?s", uid_to_name($uid, 'fullname'));
    if (count($sql) > 0) {
        foreach ($sql as $data) {
                $icon_content = "<span style='padding-left: 5px;' class='fa fa-check-circle'></span>";
                $table_content .= "<tr><td>" . $data->course_title . " ($data->cert_title)</td>
                    <td>
                    <a href= '{$urlServer}main/out.php?i=$data->identifier'>" . "100%" . "</a>" . $icon_content .
                            "</td></tr>";
        }
    }

    $counter_game_certificate = 0;
    $counter_game_badge = 0;
    foreach ($courses as $course1) {
        $course_id = $course1->course_id;
        $code = $course1->code;

        // check for completeness in order to refresh user data
        Game::checkCompleteness($uid, $course_id);
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
            if ($key == 'badge') { // get badge icon
                $badge_filename = Database::get()->querySingle("SELECT filename FROM badge_icon WHERE id = 
                                                         (SELECT icon FROM badge WHERE id = ?d)", $game->id)->filename;
                }
                ${'game_'.$key}[] = $game;
            }
        }
        // get incomplete certificates
        $cert_content = '';
        if (count($game_certificate) > 0) {
            $counter_game_certificate++;
            foreach ($game_certificate as $key => $certificate) {
                $cert_content = round($certificate->completed_criteria / $certificate->total_criteria * 100, 0) . "%";
                $invisible = 'not_visible';
                if ($certificate->completed == 1) {
                    continue;
                }
                $table_content .= "<tr class='not_visible'><td>" . $course1->title . " ($certificate->title)</td>
                    <td>
                    <a href= '{$urlServer}modules/progress/index.php?course=$code&amp;certificate_id=$certificate->certificate&amp;u=$uid'>" . $cert_content . "</a> 
                            </td></tr>";
            }
        }

        // get badges
        if (count($game_badge) > 0) {
            $counter_game_badge++;
            foreach ($game_badge as $key => $badge) {
                $cert_content = round($badge->completed_criteria / $badge->total_criteria * 100, 0) . "%";
                $icon_content = '';
                $invisible = 'not_visible';
                if ($badge->completed == 1) {
                    $icon_content = "<span style='padding-left: 5px;' class='fa fa-check-circle'></span>";
                    $invisible = '';
                }
                $table_content .= "<tr class='$invisible'><td>" . $course1->title . " ($badge->title)</td>
                    <td>
                        <div class='d-flex justify-content-between gap-5'>
                            <div><a href= '{$urlServer}modules/progress/index.php?course=$code&amp;badge_id=$badge->badge&amp;u=$uid'>" . $cert_content . "</a>" . $icon_content ."</div>
                            <div>
                                ". action_button(array(
                                    array(
                                        'title' => $langAddResePortfolio,
                                        'url' => "$urlServer"."main/eportfolio/resources.php?token=".token_generate('eportfolio' . $uid)."&amp;action=add&amp;type=my_badges&amp;rid=".$badge->badge,
                                        'icon' => 'fa-star',
                                        'show' => (get_config('eportfolio_enable'))
                                    ),
                                ))."
                            </div>
                        </div>
                    </td></tr>";
            }
        }
    }
    if (count($sql) == 0 && $counter_game_certificate == 0 && $counter_game_badge == 0){
        $table_content .= "<tr><td>$langNoInfoAvailable</td><td></td></tr>";
    }
    $table_content .= "</table></div>";
    if (!$table_content) {
        $tool_content .= "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoCertBadge</span></div>";
    } else {
        $tool_content .= $table_content;
    }
} else {
    $tool_content .= "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoCertBadge</span></div>";
}

draw($tool_content, 1);
