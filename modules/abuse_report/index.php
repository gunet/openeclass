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

if (isset($_GET['choice']) && $_GET['choice'] == 'close') { //close report
    if (isset($_GET['report'])) {
        $id = intval($_GET['report']);
        //check if user has right to close report
        $res = Database::get()->querySingle("SELECT id FROM abuse_report WHERE id = ?d
                    AND course_id = ?d", $id, $course_id);
        if ($res) { //if report id actually belongs to this course then the editor may close it
            Database::get()->query("UPDATE abuse_report SET status = ?d WHERE id = ?d", 0, $id);
            Session::Messages($langCloseReportSuccess, 'alert-success');
            redirect_to_home_page("modules/abuse_report/index.php?course=$course_code");
        }
    }
}

// maximum number of reports on a page
$limitReportsPage = 15;
if (isset($_GET['page'])) {
    $page = intval($_GET['page']);
} else {
    $page = 0;
}
// selects $limitReportsPage reports at the same time
$from = $page * $limitReportsPage;

$result = Database::get()->queryArray("SELECT * FROM abuse_report WHERE course_id = ?d AND status = ?d ORDER BY timestamp DESC LIMIT ?d, ?d", $course_id, 1, $from, $limitReportsPage);
//Number of all reports for this course
$total_reports = Database::get()->querySingle("SELECT COUNT(*) as count FROM abuse_report WHERE course_id = ?d AND status = ?d", $course_id, 1)->count;
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
            <th>$langContent</th>
            <th>$langUser</th>
            <th class='text-center'>".icon('fa-gears')."</th>
          </tr>";
    
    foreach ($result as $report) {
        
        $options = action_button(array(
                       array('title' => $langAbuseReportClose,
                             'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=close&amp;report=$report->id",
                             'icon' => 'fa-archive',
                             'confirm' => $langConfirmAbuseReportClose,
                             'confirm_title' => $langAbuseReportClose,
                             'confirm_button' => $langClose),
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
                            <td></td>
                            <td>".display_user($report->user_id)."</td>                                    
                            <td class='option-btn-cell'>".$options."</td>
                          </tr>";
    }
    
    $tool_content .= "</table></div>";
}

draw($tool_content, 2, null, $head_content);
