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

$require_current_course = true;
$require_course_admin = true;

require_once '../../include/baseTheme.php';

// maximum number of reports on a page
$limitReportsPage = 15;
if (isset($_GET['page'])) {
    $page = intval($_GET['page']);
} else {
    $page = 0;
}
// selects $limitReportsPage reports at the same time
$from = $page * $limitReportsPage;

$result = Database::get()->queryArray("SELECT * FROM abuse_report WHERE course_id = ?d ORDER BY timestamp DESC LIMIT ?d, ?d", $course_id, $from, $limitReportsPage);
//Number of all reports for this course
$total_reports = Database::get()->querySingle("SELECT COUNT(*) as count FROM exercise WHERE course_id = ?d", $course_id)->count;
//Number of reports for current page
$nbrReports = count($result);

if (!$nbrReports) {
    $tool_content .= "<div class='alert alert-warning'>$langNoAbuseReports</div>";
} else {
    $reports_cats = array('rudeness' => $langRudeness,
                          'spam' => $langSpam,
                          'other' => $langOther);
    
    $resource_types = array('comment' => $langComment,
                            'forum_post' => $langForumPost);
    
    $maxpage = 1 + intval($total_reports / $limitReportsPage);
    if ($maxpage > 0) {
        $prevpage = $page - 1;
        $nextpage = $page + 1;
        if ($prevpage >= 0) {
            $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;page=$prevpage'>&lt;&lt; $langPreviousPage</a>&nbsp;";
        }
        if ($nextpage < $maxpage) {
            $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;page=$nextpage'>$langNextPage &gt;&gt;</a>";
        }
    }
    
    $tool_content .= "<div class='table-responsive'><table class='table-default'><tr>";
    $tool_content .= "
            <th>$langAbuseReportCat</th>
            <th>$langMessage</th>
            <th>$langAbuseResourceType</th>
            <th>$langUser</th>
            <th class='text-center'>".icon('fa-gears')."</th>
          </tr>";
    
    foreach ($result as $report) {
        
        $options = action_button(array(
                       array('title' => $langAbuseReportClose,
                             'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=close&amp;report=$report->id",
                             'icon' => 'fa-archive',
                             'confirm' => $langConfirmAbuseReportClose),
                       array('title' => $langVisitReportedResource,
                             'url' => "",
                             'icon' => 'fa-external-link'),
                       array('title' => $langEditReportedResource,
                             'url' => "",
                             'icon' => 'fa-edit'),
                       array('title' => $langDeleteReportedResource,
                             'url' => "",
                             'icon' => 'fa-times',
                             'class' => 'delete',
                             'confirm' => $langConfirmDeleteReportedResource),
                   ));
        
        $tool_content .= "<tr>
                            <td>".$reports_cats[$report->reason]."</td>
                            <td>".q($report->message)."</td>
                            <td>".$resource_types[$report->rtype]."</td>
                            <td>".display_user($report->user_id)."</td>                                    
                            <td class='option-btn-cell'>".$options."</td>
                          </tr>";
    }
    
    $tool_content .= "</table></div>";
}

draw($tool_content, 2, null, $head_content);
