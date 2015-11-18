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

function admin_statistics_tools($self_link = "") {
    global $tool_content, $langPlatformGenStats, $langVisitsStats, $langVisitsCourseStats, 
            $langBack, $langOldStats, $langAccept, $langOldStatsExpireConfirm, $langMonthlyReport;
    
    $tool_content .= action_bar(array(                
                array('title' => $langBack,
                    'url' => "",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label'),
                array('title' => $langPlatformGenStats,
                    'url' => "stateclass.php",
                    'icon' => 'fa-bar-chart',
                    'show' => $self_link != "stateclass",
                    'level' => 'primary-label'),
                array('title' => $langVisitsStats,
                    'url' => "platformStats.php?first=",
                    'icon' => 'fa-sign-in',
                    'show' => $self_link != "platformStats",
                    'level' => 'primary'),
                array('title' => $langVisitsCourseStats,
                    'url' => "visitsCourseStats.php?first=",
                    'icon' => 'fa-user',
                    'show' => $self_link != "visitsCourseStats",
                    'level' => 'primary'),                
                array('title' => $langMonthlyReport,
                    'url' => "monthlyReport.php",
                    'icon' => 'fa-calendar',
                    'show' => $self_link != "monthlyReport",
                    'level' => 'primary'),                
                array('title' => $langOldStats,
                    'url' => "oldStats.php",
                    'icon' => 'fa-file-text-o',                    
                    'confirm_title' => $langOldStats,
                    'confirm_button' => $langAccept,
                    'show' => $self_link != "oldStats",
                    'confirm' => $langOldStatsExpireConfirm),
            ));
}
