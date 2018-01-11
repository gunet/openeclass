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
 * @brief Displays course user progress in specific LP
 */

$require_current_course = TRUE;
$require_editor = TRUE;

require_once '../../include/baseTheme.php';
require_once 'include/lib/learnPathLib.inc.php';

$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langLearningPaths);
$toolName = $langStatsOfLearnPath;

if (empty($_REQUEST['path_id'])) { // path id can not be empty
    header("Location: ./index.php?course=$course_code");
    exit();
} else {
    $path_id = intval($_REQUEST['path_id']);
}

load_js('datatables');

$head_content .= "<script type='text/javascript'>
        $(document).ready(function() {
            $('#lpu_progress').DataTable ({
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

// get infos about the learningPath
$learnPathName = Database::get()->querySingle("SELECT `name` FROM `lp_learnPath` WHERE `learnPath_id` = ?d AND `course_id` = ?d", $path_id, $course_id);

if ($learnPathName) {
    $titleTab['subTitle'] = htmlspecialchars($learnPathName->name);
    $pageName = $langLearnPath.": ".disp_tool_title($titleTab);
    
    $tool_content .= action_bar(array(
                array('title' => $langBack,
                      'url' => "index.php",
                      'icon' => 'fa-reply',
                      'level' => 'primary-label')));
        
    $tool_content .= "<div class='table-responsive'>
                    <table id='lpu_progress' class='table-default'>
                    <thead>
                        <tr class='list-header'>
                            <th class='text-left'>$langStudent</th>
                            <th width='5px;'>$langProgress</th>
                        </tr>
                    </thead>";
        
    $usersList = Database::get()->queryArray("SELECT U.`surname`, U.`givenname`, U.`id`
		FROM `user` AS U,
		     `course_user` AS CU
		WHERE U.`id` = CU.`user_id`
		AND CU.`course_id` = ?d
		ORDER BY U.`surname` ASC, U.`givenname` ASC", $course_id);
        
    $tool_content .= "<tbody>";
    foreach ($usersList as $user) {
        $lpProgress = get_learnPath_progress($path_id, $user->id);
        $tool_content .= "<tr>";
        $tool_content .= "<td>
                            <a href='detailsUserPath.php?course=$course_code&amp;uInfo=$user->id&amp;path_id=$path_id'>" . q($user->surname) . " " . q($user->givenname) . "</a>
                        </td>
                        <td align='right'>"
                            . disp_progress_bar($lpProgress, 1) .
                        "</td>";
        $tool_content .= "</tr>";
    }    
    $tool_content .= "</tbody></table></div>";
}

draw($tool_content, 2, null, $head_content);