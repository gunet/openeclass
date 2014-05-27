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

/**
 * @file listcours.php
 * @brief display list of courses
 */

$require_departmentmanage_user = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/course.class.php';
require_once 'include/lib/user.class.php';
require_once 'hierarchy_validations.php';

if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $tree = new Hierarchy();
    $course = new Course();
    $user = new User();

    // A search has been submitted
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $searchurl = "&search=yes";        
    $searchtitle = isset($_GET['formsearchtitle'])? $_GET['formsearchtitle'] : '';
    $searchcode  = isset($_GET['formsearchcode'])? $_GET['formsearchcode'] : '';
    $searchtype = isset($_GET['formsearchtype'])? intval($_GET['formsearchtype']) : '-1';
    $searchfaculte = isset($_GET['formsearchfaculte']) ? intval($_GET['formsearchfaculte']) : '';
    // pagination
    $limit = intval($_GET['iDisplayLength']);
    $offset = intval($_GET['iDisplayStart']);
    
    // Search for courses
    $searchcours = array();
    if (!empty($searchtitle)) {
        $searchcours[] = "title LIKE " . quote('%' . $searchtitle . '%');
    }
    if (!empty($searchcode)) {
        $searchcours[] = "course.code LIKE " . quote('%' . $searchcode . '%');
    }
    if ($searchtype != "-1") {
        $searchcours[] = "visible = $searchtype";
    }
    if ($searchfaculte) {
        $subs = $tree->buildSubtrees(array($searchfaculte));
        $ids = '';
        foreach ($subs as $key => $id) {
            $ids .= $id . ',';
        }
        // remove last ',' from $ids
        $facs = substr($ids, 0, -1);
        $searchcours[] = "hierarchy.id IN ($facs)";
    }
    if (isset($_GET['reg_flag'])) {
        $searchcours[] = "created " . (($_GET['reg_flag'] == 1) ? '>=' : '<=') . " '$_GET[date]'";
    }
    $query = join(' AND ', $searchcours);
    
    ///internal search
    if (!empty($_GET['sSearch'])) {
        $keyword = quote('%' . $_GET['sSearch'] . '%');
        $query .= "AND title LIKE" . quote('%' . $_GET['sSearch'] . '%');
    } else {
        $query .= "";
        $keyword = "'%%'";
    }
    $depwh = (isDepartmentAdmin()) ? ' AND course_department.department IN (' . implode(', ', $user->getDepartmentIds($uid)) . ') ' : '';
     
    // sorting
    $extra_query = "ORDER BY course.title ".$_GET['sSortDir_0'];   
    // pagination
    ($limit > 0) ? $extra_query .= " LIMIT $offset,$limit" : $extra_query .= "";
    
    
    $sql = Database::get()->queryArray("SELECT DISTINCT course.code, course.title, course.prof_names, course.visible, course.id
                               FROM course, course_department, hierarchy
                              WHERE course.id = course_department.course
                                AND hierarchy.id = course_department.department AND $query $depwh $extra_query");       

    $depq = (isDepartmentAdmin()) ? ", course_department WHERE course.id = course_department.course " . $depwh : '';            
    $depq .= (empty($depq)) ? "WHERE ": "AND ";
    
    $all_results = Database::get()->querySingle("SELECT COUNT(*) as total FROM course $depq $query")->total;
    $filtered_results = Database::get()->querySingle("SELECT COUNT(*) as total FROM course $depq $query
                                                        AND title LIKE $keyword")->total;    
    $data['iTotalRecords'] = $all_results;
    $data['iTotalDisplayRecords'] = $filtered_results;
    $data['aaData'] = array();
    
    foreach ($sql as $logs) {                            
        $course_title = "<a href='{$urlServer}courses/" . $logs->code . "/'><b>" . q($logs->title) . "</b>
                        </a> (" . q($logs->code) . ")<br /><i>" . q($logs->prof_names) . "";
        // Define course type
        switch ($logs->visible) {
            case COURSE_CLOSED:
                $icon = 'lock_closed';
                $title = $langClosedCourse;
                break;
            case COURSE_REGISTRATION:
                $icon = 'lock_registration';
                $title = $langRegCourse;                
                break;
            case COURSE_OPEN:
                $icon = 'lock_open';
                $title = $langOpenCourse;                
                break;
            case COURSE_INACTIVE:
                $icon = 'lock_inactive';
                $title = $langCourseInactiveShort;                
                break;
        }

        $departments = $course->getDepartmentIds($logs->id);
        $i = 1;
        $dep = '';
        foreach ($departments as $dep) {
            $br = ($i < count($departments)) ? '<br/>' : '';
            $dep .= $tree->getFullPath($dep) . $br;
            $i++;
        }
        
        // Add links to course users, delete course and course edit
        $icon_content = icon('user_list', $langUsers, "listusers.php?c=$logs->id")."&nbsp;";
        if (!isDepartmentAdmin()) {
            $icon_content .= icon('user_list', $langUsersLog, "../usage/displaylog.php?c=$logs->id&amp;from_admin=TRUE")."&nbsp;";
        }        
        $icon_content .= icon('edit', $langEdit, "editcours.php?c=$logs->code")."&nbsp;";
        $icon_content .= icon('delete', $langDelete, "delcours.php?c=$logs->id");
        
        $data['aaData'][] = array(
                        '0' => $course_title,
                        '1' => icon($icon, $title),
                        '2' => $dep,
                        '3' => $icon_content
                    );
    }
    echo json_encode($data);
    exit();
}

load_js('tools.js');
load_js('jquery');
load_js('datatables');
$head_content .= "<script type='text/javascript'>
        $(document).ready(function() {
            $('#course_results_table').DataTable ({            
                'bProcessing': true,
                'bServerSide': true,                
                'sAjaxSource': '$_SERVER[REQUEST_URI]',
                'aLengthMenu': [
                   [10, 15, 20 , -1],
                   [10, 15, 20, '$langAllOfThem'] // change per page values here
                ],                
                'sPaginationType': 'full_numbers',
                    'aoColumns': [
                        null,                        
                        {'bSortable' : false },
                        {'bSortable' : false },
                        {'bSortable' : false },
                    ],
                    'oLanguage': {                       
                       'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
                       'sZeroRecords':  '".$langNoResult."',
                       'sInfo':         '$langDisplayed _START_ $langTill _END_ $langFrom2 _TOTAL_ $langTotalResults',
                       'sInfoEmpty':    '$langDisplayed 0 $langTill 0 $langFrom2 0 $langResults2',
                       'sInfoFiltered': '',
                       'sInfoPostFix':  '',
                       'sSearch':       '".$langSearch."',
                       'sUrl':          '',
                       'oPaginate': {
                           'sFirst':    '&laquo;',
                           'sPrevious': '&lsaquo;',
                           'sNext':     '&rsaquo;',
                           'sLast':     '&raquo;'
                       }
                   }
            });
            $('.dataTables_filter input').attr('placeholder', '$langTitle');
        });
        </script>";


$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'searchcours.php', 'name' => $langSearchCourses);
$nameTools = $langListCours;

// Display Actions Toolbar
$tool_content .= "<div id='operations_container'>
    <ul id='opslist'>
      <li><a href='listcours.php'>$langAllCourses</a></li>
    </ul>
    </div>";

$width = (!isDepartmentAdmin()) ? 100 : 80;
// Construct course list table
$tool_content .= "<table id='course_results_table' class='display'>
    <thead>
    <tr>
    <th align='left'>$langCourseCode</th>
    <th>$langGroupAccess</th>
    <th width='260' align='left'>$langFaculty</th>
    <th width='$width'>$langActions</th>
    </tr></thead>";

$tool_content .= "<tbody></tbody></table>";   
$tool_content .= "<div align='center' style='margin-top: 60px; margin-bottom:10px;'>";
$tool_content .= "<a href='searchcours.php'>$langReturnSearch</a></div>";
    
draw($tool_content, 3, null, $head_content);
