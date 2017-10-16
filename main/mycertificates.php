<?php

/* ========================================================================
 * Open eClass 3.7
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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

$require_login = true;
$require_help = true;
$helpTopic = 'Gradebook';

require_once '../include/baseTheme.php';
require_once 'modules/progress/Game.php';

if (is_module_disable(MODULE_ID_PROGRESS)) {
    redirect_to_home_page();
}

$toolName = $langMyCertificates;
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
            <tr><th>$langCourse</th><th style='text-align:center; width:20%;'>$langResults</th></tr>";
    
    // get completed certificates with public url
    $sql = Database::get()->queryArray("SELECT course_title, cert_title, cert_id, identifier "
                                        . "FROM certified_users "
                                        . "WHERE user_fullname = ?s", uid_to_name($uid, 'fullname'));
    if (count($sql) > 0) {
        foreach ($sql as $data) {
                $icon_content = "<span style='padding-left: 5px;' class='fa fa-check-circle'></span>";
                $table_content .= "<tr><td>" . $data->course_title . " ($data->cert_title)</td>
                    <td style='text-align:center;'>
                    <a href= '{$urlServer}main/out.php?i=$data->identifier'>" . "100%" . "</a>" . $icon_content . 
                            "</td></tr>";
        }
    }
        
    
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
            foreach ($game_certificate as $key => $certificate) {
                $cert_content = round($certificate->completed_criteria / $certificate->total_criteria * 100, 0) . "%";                
                $invisible = 'not_visible';
                if ($certificate->completed == 1) {
                    continue;
                }
                $table_content .= "<tr class='not_visible'><td>" . $course1->title . " ($certificate->title)</td>
                    <td style='text-align:center;'>
                    <a href= '{$urlServer}modules/progress/index.php?course=$code&amp;certificate_id=$certificate->certificate&amp;u=$uid'>" . $cert_content . "</a> 
                            </td></tr>";
            }            
        }
                
        // get badges
        if (count($game_badge) > 0) {
            foreach ($game_badge as $key => $badge) {
                $cert_content = round($badge->completed_criteria / $badge->total_criteria * 100, 0) . "%";
                $icon_content = '';
                $invisible = 'not_visible';
                if ($badge->completed == 1) {
                    $icon_content = "<span style='padding-left: 5px;' class='fa fa-check-circle'></span>";
                    $invisible = '';
                }
                $table_content .= "<tr class='$invisible'><td>" . $course1->title . " ($badge->title)</td>
                    <td style='text-align:center;'>
                    <a href= '{$urlServer}modules/progress/index.php?course=$code&amp;badge_id=$badge->badge&amp;u=$uid'>" . $cert_content . "</a>" . $icon_content . 
                            "</td></tr>";
            }
        }
    }
    $table_content .= "</table></div>";
    if (!$table_content) {
        $tool_content .= "<div class='alert alert-warning'>$langNoCertBadge</div>";
    } else {
        $tool_content .= $table_content;
    }
} else {
    $tool_content .= "<div class='alert alert-warning'>$langNoCertBadge</div>";
}

draw($tool_content, 1);