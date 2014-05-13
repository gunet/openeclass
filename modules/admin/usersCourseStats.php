<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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


/*
  ===========================================================================
  admin/usersCourseStats.php
  @last update: 23-09-2006
  @authors list: ophelia neofytou
  ==============================================================================
  @Description: Shows chart with the number of users per course.

  ==============================================================================
 */

$require_admin = true;
$require_help = true;
$helpTopic = 'Usage';

require_once '../../include/baseTheme.php';

// Define $nameTools
$nameTools = $langUsersCourse;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

$tool_content .= "
  <div id='operations_container'>
    <ul id='opslist'>
      <li><a href='stateclass.php'>$langPlatformGenStats</a></li>
      <li><a href='platformStats.php?first='>$langVisitsStats</a></li>
      <li><a href='usersCourseStats.php'>$langUsersCourse</a></li>
      <li><a href='visitsCourseStats.php?first='>$langVisitsCourseStats</a></li>
      <li><a href='oldStats.php' onClick='return confirmation(\"$langOldStatsExpireConfirm\");'>" . $langOldStats . "</a></li>
      <li><a href='monthlyReport.php'>$langMonthlyReport</a>></li>
    </ul>
  </div>";


require_once 'include/jscalendar/calendar.php';
$lang = ($language == 'el') ? 'el' : 'en';

//make chart
require_once 'modules/graphics/plotter.php';

$chart = new Plotter();
$chart->setTitle($langUsersCourse);
    Database::get()->queryFunc("SELECT course.title AS name, COUNT(user_id) AS cnt FROM course_user LEFT JOIN course ON course.id = course_user.course_id GROUP BY course.id"
        , function ($row) use($chart) {
    $chart->growWithPoint($row->name, $row->cnt);
});

$tool_content .= $chart->plot();

load_js('tools.js');
draw($tool_content, 3, 'admin', $head_content);
