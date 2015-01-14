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

function statistics_tools($course_code, $self_link = "", $relative_path = "") {
    
    global $tool_content, $langStat, $langUsersLog, $langFavourite, $langUserLogins, 
            $langUserDuration, $langLearningPaths, $langGroupUsage, $langOldStats, 
            $langAccept, $langOldStatsExpireConfirm;
    
    return $tool_content .= action_bar(array(
                array('title' => $langStat,
                    'url' => $relative_path . "index.php?course=$course_code",
                    'icon' => 'fa-bar-chart',
                    'show' => $self_link != "index",
                    'level' => 'primary-label'),
                array('title' => $langUsersLog,
                    'url' => $relative_path . "displaylog.php?course=$course_code",
                    'icon' => 'fa-user',
                    'show' => $self_link != "displaylog",
                    'level' => 'primary'),
                array('title' => $langFavourite,
                    'url' => $relative_path . "favourite.php?course=$course_code&amp;first=",
                    'icon' => 'fa-gear',
                    'show' => $self_link != "favourite",
                    'level' => 'primary'),
                array('title' => $langUserLogins,
                    'url' => $relative_path . "userlogins.php?course=$course_code&amp;first=",
                    'icon' => ' fa-comments-o',
                    'show' => $self_link != "userlogins",
                    'level' => 'primary'),
                array('title' => $langUserDuration,
                    'url' => $relative_path . "userduration.php?course=$course_code",
                    'icon' => 'fa-clock-o',
                    'show' => $self_link != "userduration",
                    'level' => 'primary'),
                array('title' => $langLearningPaths,
                    'url' => $relative_path . "../learnPath/detailsAll.php?course=$course_code&amp;from_stats=1",
                    'icon' => 'fa-ellipsis-h',
                    'show' => $self_link != "detailsAll"),
                array('title' => $langGroupUsage,
                    'url' => $relative_path . "group.php?course=$course_code",
                    'icon' => 'fa-users',
                    'show' => $self_link != "group"),
                array('title' => $langOldStats,
                    'url' => $relative_path . 'oldStats.php?course=' . $course_code,
                    'icon' => 'fa-calendar',
                    'confirm_title' => $langOldStats,
                    'confirm_button' => $langAccept,
                    'confirm' => $langOldStatsExpireConfirm,
                    'show' => $self_link != "oldStats"),
            ));
}
