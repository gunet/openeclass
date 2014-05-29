<?php
/* ========================================================================
 * Open eClass 2.9
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

$require_power_user = true;
include '../../include/baseTheme.php';


if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
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
        $searchcours[] = "intitule LIKE " . quote('%' . $searchtitle . '%');
    }
    if (!empty($searchcode)) {
        $searchcours[] = "cours.code LIKE " . quote('%' . $searchcode . '%');
    }
    if ($searchtype != "-1") {
        $searchcours[] = "visible = $searchtype";
    }
    if ($searchfaculte) {
		$searchcours[] = "faculteid = $searchfaculte";
    }
    if (isset($_GET['reg_flag'])) {
        $searchcours[] = "first_create " . (($_GET['reg_flag'] == 1)? '>=': '<=') . " '$_GET[date]'";        
    }
    $query = join(' AND ', $searchcours);
    
    ///internal search
    if (!empty($_GET['sSearch'])) {
        $keyword = quote('%' . $_GET['sSearch'] . '%');
        $query .= "AND intitule LIKE" . quote('%' . $_GET['sSearch'] . '%');
    } else {
        $query .= "";
        $keyword = "'%%'";
    }    
   
    // sorting
    $extra_query = "ORDER BY cours.intitule ".$_GET['sSortDir_0'];
    // pagination
    ($limit > 0) ? $extra_query .= " LIMIT $offset,$limit" : $extra_query .= "";
    
    if (!empty($search)) {
       $q = "AND ";
    } else {
        $q = "";
    }
    $sql = db_query("SELECT faculte.name AS faculte, cours.code, intitule, titulaires, visible, cours_id
                                           FROM cours, faculte
                                           WHERE faculte.id = cours.faculteid $q $query $extra_query");
    
    $all_results = db_query_get_single_value("SELECT COUNT(*) FROM cours, faculte
                                           WHERE faculte.id = cours.faculteid $q $query");
    $filtered_results = db_query_get_single_value("SELECT COUNT(*) FROM cours, faculte
                                           WHERE faculte.id = cours.faculteid $q $query AND intitule LIKE $keyword");        
                                                        
    $data['iTotalRecords'] = $all_results;
    $data['iTotalDisplayRecords'] = $filtered_results;
    
    $data['aaData'] = array();
    
    while ($logs = mysql_fetch_array($sql)) {
        $course_title = "<a href='{$urlServer}courses/" . $logs['code'] . "/'>".$logs['intitule']."</a> (" . q($logs['code']) . ")<br /><i>" . q($logs['titulaires']) . "";
        // Define course type
        switch ($logs['visible']) {
                case COURSE_CLOSED:
			$access_icon = "<img src='$themeimg/lock_closed.png' title='".q($langClosedCourse)."' />";
			break;
                case COURSE_REGISTRATION:
			$access_icon = "<img src='$themeimg/lock_registration.png' title='".q($langRegCourse)."' />";
			break;
		case COURSE_OPEN:
			$access_icon = "<img src='$themeimg/lock_open.png' title='".q($langOpenCourse)."' />";
			break;				
                case COURSE_INACTIVE:
			$access_icon = "<img src='$themeimg/lock_inactive.png' title='".q($langCourseInactiveShort)."' />";
			break;				
	}
        $course_faculte = "<b>" . q($logs['faculte']) . "</b>";
        
        // Add links to course users, delete course and course edit
        $icon_content = "<a href='listusers.php?c=$logs[cours_id]'><img src='$themeimg/user_list.png' title='".q($langUsers)."' /></a>&nbsp;
                        <a href='editcours.php?c=$logs[code]'><img src='$themeimg/edit.png' title='".q($langEdit)."'></a>
                        <a href='delcours.php?c=$logs[cours_id]'><img src='$themeimg/delete.png' title='".q($langDelete)."'></a>";
        
        $data['aaData'][] = array(
                        '0' => $course_title,
                        '1' => $access_icon,
                        '2' => $course_faculte,
                        '3' => $icon_content
                    );
    }
    echo json_encode($data);
    exit();
}

load_js('tools.js');
load_js('jquery');
load_js('datatables');
load_js('datatables_filtering_delay');
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
            }).fnSetFilteringDelay(1000);
            $('.dataTables_filter input').attr('placeholder', '$langTitle');
        });
        </script>";


$nameTools = $langListCours;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);


// Display Actions Toolbar
$tool_content .= "
    <div id='operations_container'>
    <ul id='opslist'>
    <li><a href='listcours.php'>$langAllCourses</a></li>
    </ul>
    </div>";

// Construct course list table
$tool_content .= "<table id='course_results_table' class='display'>
    <thead>
    <tr>
    <th align='left'>$langCourseCode</th>
    <th>$langGroupAccess</th>
    <th width='260' align='left'>$langFaculty</th>
    <th>$langActions</th>
    </tr></thead>";

$tool_content .= "<tbody></tbody></table>";   
$tool_content .= "<div align='center' style='margin-top: 60px; margin-bottom:10px;'>";
$tool_content .= "<a href='searchcours.php'>$langReturnSearch</a></div>";
    
draw($tool_content, 3, null, $head_content);