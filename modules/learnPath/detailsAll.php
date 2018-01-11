<?php

/* ========================================================================
 * Open eClass 3.6
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

/**
 * @file details.php
 * @author Thanos Kyritsis <atkyritsis@upnet.gr>
 * @author Piraux Sebastien <pir@cerdecam.be>
 * @brief Displays course user progress in LPs
 */

$require_current_course = TRUE;
$require_editor = TRUE;

require_once '../../include/baseTheme.php';
require_once 'include/lib/learnPathLib.inc.php';

$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langLearningPaths);
$pageName = $langTrackAllPathExplanation;

load_js('datatables');

$head_content .= "<script type='text/javascript'>
        $(document).ready(function() {
            $('#lp_users_progress').DataTable ({
                'sPaginationType': 'full_numbers',
                'bAutoWidth': true,
                'searchDelay': 1000,
                'order' : [[1, 'desc']],
                'oLanguage': {
                   'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
                   'sZeroRecords':  '" . $langNoResult . "',
                   'sInfo':         '$langDisplayed _START_ $langTill _END_ $langFrom2 _TOTAL_ $langTotalResults',
                   'sInfoEmpty':    '$langDisplayed 0 $langTill 0 $langFrom2 0 $langResults2',
                   'sInfoFiltered': '',
                   'sInfoPostFix':  '',
                   'sSearch':       '',
                   'sUrl':          '',
                   'oPaginate': {
                       'sFirst':    '&laquo;',
                       'sPrevious': '&lsaquo;',
                       'sNext':     '&rsaquo;',
                       'sLast':     '&raquo;'
                   }
               }
            });
            $('.dataTables_filter input').attr({
                          class : 'form-control input-sm',
                          placeholder : '$langSearch...'
                        });
        });
        </script>";

$tool_content .= action_bar(array(
                array('title' => $langBack,
                      'url' => "index.php",
                      'icon' => 'fa-reply',
                      'level' => 'primary-label')),false);

// check if there are learning paths available
$lcnt = Database::get()->querySingle("SELECT COUNT(*) AS count FROM lp_learnPath WHERE course_id = ?d", $course_id)->count;
if ($lcnt == 0) {
    $tool_content .= "<div class='alert alert-warning'>$langNoLearningPath</div>";
    draw($tool_content, 2, null, $head_content);
    exit;
} else {
    $tool_content .= "<div class='alert alert-info'>
          $langSave <a href='dumpuserlearnpathdetails.php?course=$course_code'>$langDumpUserDurationToFile</a>
                (<a href='dumpuserlearnpathdetails.php?course=$course_code&amp;enc=UTF-8'>$langcsvenc2</a>)
          </div>";
}

$tool_content .= "<div class='table-responsive'>
        <table id='lp_users_progress' class='table-default'>
        <thead>
            <tr class='list-header'>
                <th>$langStudent</th>
                <th width='120'>$langAm</th>
                <th>$langGroup</th>
                <th>$langProgress</th>
            </tr>
        </thead>";

$usersList = Database::get()->queryArray("SELECT U.`surname`, U.`givenname`, U.`id`
                FROM `user` AS U, `course_user` AS CU
                    WHERE U.`id`= CU.`user_id`
                    AND CU.`course_id` = ?d
                    ORDER BY U.`surname` ASC", $course_id);

$tool_content .= "<tbody>";
foreach ($usersList as $user) {
    // list available learning paths
    $learningPathList = Database::get()->queryArray("SELECT learnPath_id FROM lp_learnPath WHERE course_id = ?d", $course_id);
    $iterator = 1;
    $globalprog = 0;    
    
    foreach ($learningPathList as $learningPath) {
        // % progress
        $prog = get_learnPath_progress($learningPath->learnPath_id, $user->id);
        if ($prog >= 0) {
            $globalprog += $prog;
        }
        $iterator++;
    }
    $total = round($globalprog / ($iterator - 1));
    $tool_content .= "<tr>";
    $tool_content .= "<td><a href='detailsUser.php?course=$course_code&amp;uInfo=$user->id'>" . uid_to_name($user->id) . "</a></td>
            <td class='text-center'>" . q(uid_to_am($user->id)) . "</td>
            <td class='text-left'>" . user_groups($course_id, $user->id) . "</td>
            <td class='text-right' width='120'>"
            . disp_progress_bar($total, 1) . "
            </td>";
    $tool_content .= "</tr>";
}
$tool_content .= "</tbody></table></div>";

draw($tool_content, 2, null, $head_content);