<?php
/* ========================================================================
 * Open eClass 2.6
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

$require_login = true;
$require_current_course = TRUE;
$require_course_admin = TRUE;
$require_help = TRUE;
$helpTopic = 'User';

include '../../include/baseTheme.php';
include '../admin/admin.inc.php';

define ('COURSE_USERS_PER_PAGE', 15);

//Identifying ajax request
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $limit = intval($_GET['iDisplayLength']);
    $offset = intval($_GET['iDisplayStart']);
    
    if (!empty($_GET['sSearch'])) {
        $keyword = quote("%".$_GET['sSearch']."%");
        $search_sql = 'AND (user.nom LIKE '.$keyword.' OR user.prenom LIKE '.$keyword.' OR user.email LIKE '.$keyword.')';     
    } else {
        $search_sql='';
    }
    $limit_sql = ($limit>0) ? "LIMIT $offset,$limit" : "";
    
    $all_users = db_query("SELECT COUNT(*) AS total FROM cours_user, user WHERE `user`.`user_id` = `cours_user`.`user_id`
        AND `cours_user`.`cours_id` = $cours_id");
    $all_users = mysql_fetch_assoc($all_users);
    $filtered_users = db_query("SELECT COUNT(*) AS total FROM cours_user, user WHERE `user`.`user_id` = `cours_user`.`user_id`
        AND `cours_user`.`cours_id` = $cours_id $search_sql");
    $filtered_users = mysql_fetch_assoc($filtered_users);    
    $result = db_query("SELECT user.user_id, user.nom, user.prenom, user.email, user.parent_email,
                           user.am, user.has_icon, cours_user.statut,
                           cours_user.tutor, cours_user.editor, cours_user.reviewer, 
                           cours_user.reg_date
                    FROM cours_user, user
                    WHERE `user`.`user_id` = `cours_user`.`user_id` 
                    AND `cours_user`.`cours_id` = $cours_id
                    $search_sql $limit_sql"); 
    
//    $student_sql = $is_editor? '': "AND visibility = 'v'";
//    $all_announc = db_query("SELECT COUNT(*) AS total FROM annonces WHERE cours_id = $cours_id $student_sql", $mysqlMainDb);
//    $all_announc = mysql_fetch_assoc($all_announc);
//    $filtered_announc = db_query("SELECT COUNT(*) AS total FROM annonces WHERE cours_id = $cours_id AND title LIKE $keyword $student_sql", $mysqlMainDb);
//    $filtered_announc = mysql_fetch_assoc($filtered_announc);
//    ($limit>0) ? $extra_sql = "LIMIT $offset,$limit" : $extra_sql = "";
//
//    $result = db_query("SELECT * FROM annonces WHERE cours_id = $cours_id AND title LIKE $keyword $student_sql ORDER BY ordre DESC $extra_sql", $mysqlMainDb);
    
    $data['iTotalRecords'] = $all_users['total'];
    $data['iTotalDisplayRecords'] = $filtered_users['total'];
    $data['aaData'] = array();
        $iterator = 1;
        while ($myrow = mysql_fetch_array($result)) {
            
            //Create appropriate role control buttons
            //Tutor right
            if ($myrow['tutor'] == '0') {
                $user_role_controls = "<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;giveTutor=$myrow[user_id]'>
                        <img src='$themeimg/group_manager_add.png' title='".q($langGiveRightTutor)."' alt='".q($langGiveRightTutor)."' /></a>";
            } else {
                $user_role_controls = "<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;removeTutor=$myrow[user_id]' title='".q($langRemoveRightTutor)."'>
                        <img src='$themeimg/group_manager_remove.png' title='".q($langRemoveRightTutor)."' alt='".q($langRemoveRightTutor)."' /></a>";
            }
            //Editor right
            if ($myrow['editor'] == '0') {
                $user_role_controls .= "<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;giveEditor=$myrow[user_id]'>
                        <img src='$themeimg/assistant_add.png' title='".q($langGiveRightEditor)."' alt='".q($langGiveRightEditor)."' /></a>";
            } else {
                $user_role_controls .= "<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;removeEditor=$myrow[user_id]' title='$langRemoveRightEditor'>
                        <img src='$themeimg/assistant_remove.png' title ='".q($langRemoveRightEditor)."' alt='".q($langRemoveRightEditor)."' /></a>";
            }
            // Admin right
            if ($myrow['user_id'] != $_SESSION["uid"]) {
                    if ($myrow['statut']=='1') {
                        if (get_config('opencourses_enable') && $myrow['reviewer'] == '1') {
                            $user_role_controls .= "<img src='$themeimg/teacher.png' title='".q($langTutor)."' alt='".q($langTutor)."' />";
                        } else {
                            $user_role_controls .= "<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;removeAdmin=$myrow[user_id]' title='".q($langRemoveRightAdmin)."'>
                                            <img src='$themeimg/teacher_remove.png' title='".q($langRemoveRightAdmin)."' alt='".q($langRemoveRightAdmin)."' /></a>";
                        }
                    } else {
                            $user_role_controls .= "<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;giveAdmin=$myrow[user_id]'>
                                    <img src='$themeimg/teacher_add.png' title='".q($langGiveRightAdmin)."' alt='".q($langGiveRightAdmin)."' /></a>";
                    }
            } else {
                    if ($myrow['statut']=='1') {
                            $user_role_controls .= "<img src='$themeimg/teacher.png' title='".q($langTutor)."' alt='".q($langTutor)."' />";
                    } else {
                            $user_role_controls .= "<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;giveAdmin=$myrow[user_id]\'>
                                            <img src='$themeimg/add.png' title='".q($langGiveRightAdmin)."' alt='".q($langGiveRightAdmin)."' /></a>";
                    }
            }              
            //setting datables column data
            $data['aaData'][] = array(
                'DT_RowId' => $myrow['user_id'],
                '0' => $iterator, 
                '1' => $myrow['prenom'].' '.$myrow['nom'], 
                '2' => 'test2',
                '3' => ($myrow['reg_date'] == '0000-00-00')? $langUnknownDate : nice_format($myrow['reg_date']),
                '4' => $user_role_controls 
                );
            $iterator++;
        }  
    echo json_encode($data);
    exit();
}

$limit = isset($_REQUEST['limit'])? intval($_REQUEST['limit']): 0;

$nameTools = $langAdminUsers;
load_js('jquery');
load_js('datatables');
load_js('datatables_filtering_delay');
$head_content .= "
<script type='text/javascript'>
        $(document).ready(function() {
           var oTable = $('#users_table{$cours_id}').DataTable ({
                'bStateSave': true,
                'bProcessing': true,
                'bServerSide': true,
                'sDom': '<\"top\"pfl<\"clear\">>rt<\"bottom\"ip<\"clear\">>',
                'sAjaxSource': '$_SERVER[SCRIPT_NAME]',                   
                'aLengthMenu': [
                   [10, 15, 20 , -1],
                   [10, 15, 20, '$langAllOfThem'] // change per page values here
               ],                    
                'sPaginationType': 'full_numbers',              
                'bSort': false,               
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
        });
        </script>";

$sql = "SELECT user.user_id, cours_user.statut FROM cours_user, user
	WHERE cours_user.cours_id = $cours_id AND cours_user.user_id = user.user_id";
$result_numb = db_query($sql, $mysqlMainDb);
$countUser = mysql_num_rows($result_numb);

$teachers = $students = $visitors = 0;

while ($numrows = mysql_fetch_array($result_numb)) {
	switch ($numrows['statut']) {
		case 1:	 $teachers++; break;
		case 5:	 $students++; break;
		case 10: $visitors++; break;
		default: break;
	}
}

$limit_sql = '';
// Handle user removal / status change
if (isset($_GET['giveAdmin'])) {
        $new_admin_gid = intval($_GET['giveAdmin']);
        db_query("UPDATE cours_user SET statut = 1
                        WHERE user_id = $new_admin_gid 
                        AND cours_id = $cours_id", $mysqlMainDb);
} elseif (isset($_GET['giveTutor'])) {
        $new_tutor_gid = intval($_GET['giveTutor']);
        db_query("UPDATE cours_user SET tutor = 1
                        WHERE user_id = $new_tutor_gid 
                        AND cours_id = $cours_id", $mysqlMainDb);
        db_query("UPDATE group_members, `group` SET is_tutor = 0
                        WHERE `group`.id = group_members.group_id AND 
                              `group`.course_id = $cours_id AND
                              group_members.user_id = $new_tutor_gid");
} elseif (isset($_GET['giveEditor'])) {
        $new_editor_gid = intval($_GET['giveEditor']);
        db_query("UPDATE cours_user SET editor = 1
                        WHERE user_id = $new_editor_gid 
                        AND cours_id = $cours_id", $mysqlMainDb);            
} elseif (isset($_GET['removeAdmin'])) {
        $removed_admin_gid = intval($_GET['removeAdmin']);
        db_query("UPDATE cours_user SET statut = 5
                        WHERE user_id <> $uid AND
                              user_id = $removed_admin_gid AND
                              cours_id = $cours_id", $mysqlMainDb);
} elseif (isset($_GET['removeTutor'])) {
        $removed_tutor_gid = intval($_GET['removeTutor']);
        db_query("UPDATE cours_user SET tutor = 0
                        WHERE user_id = $removed_tutor_gid 
                              AND cours_id = $cours_id", $mysqlMainDb);
} elseif (isset($_GET['removeEditor'])) {
        $removed_editor_gid = intval($_GET['removeEditor']);
        db_query("UPDATE cours_user SET editor = 0
                        WHERE user_id = $removed_editor_gid 
                        AND cours_id = $cours_id", $mysqlMainDb);
} elseif (isset($_GET['unregister'])) {
        $unregister_gid = intval($_GET['unregister']);
        $unregister_ok = true;
        // Security: don't remove myself except if there is another prof
        if ($unregister_gid == $uid) {
                $result = db_query("SELECT user_id FROM cours_user
                                        WHERE cours_id = $cours_id AND
                                              statut = 1 AND
                                              user_id != $uid
                                        LIMIT 1", $mysqlMainDb);
                if (mysql_num_rows($result) == 0) {
                        $unregister_ok = false;
                }
        }
        if ($unregister_ok) {
                db_query("DELETE FROM cours_user
                                WHERE user_id = $unregister_gid AND
                                      cours_id = $cours_id");
                db_query("DELETE FROM group_members
                                WHERE user_id = $unregister_gid AND
                                      group_id IN (SELECT id FROM `group` WHERE course_id = $cours_id)");
        }
}

if (get_config('opencourses_enable')) {
    if (isset($_GET['giveReviewer'])) {
        $new_reviewr_gid = intval($_GET['giveReviewer']);
        db_query("UPDATE cours_user SET statut = 1, reviewer = 1
                        WHERE user_id = $new_reviewr_gid 
                        AND cours_id = $cours_id", $mysqlMainDb);
    } elseif (isset($_GET['removeReviewer'])) {
        $removed_reviewer_gid = intval($_GET['removeReviewer']);
        db_query("UPDATE cours_user SET statut = 5, reviewer = 0
                        WHERE user_id <> $uid AND
                              user_id = $removed_reviewer_gid AND
                              cours_id = $cours_id", $mysqlMainDb);
    }
}

// show help link and link to Add new user, search new user and management page of groups
$tool_content .= "

<div id='operations_container'>
  <ul id='opslist'>
    <li><b>$langAdd:</b>&nbsp; <a href='adduser.php?course=$code_cours'>$langOneUser</a></li>
    <li><a href='muladduser.php?course=$code_cours'>$langManyUsers</a></li>
    <li><a href='guestuser.php?course=$code_cours'>$langGUser</a>&nbsp;</li>
    <li><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;search=1'>$langSearchUser</a></li>
    <li><a href='../group/group.php?course=$code_cours'>$langGroupUserManagement</a></li>
    <li><a href='../course_info/refresh_course.php?course=$code_cours'>$langDelUsers</a></li>
  </ul>
</div>";

// display number of users
$tool_content .= "
<div class='info'><b>$langTotal</b>: <span class='grey'><b>$countUser </b><em>$langUsers &nbsp;($teachers $langTeachers, $students $langStudents, $visitors $langVisitors)</em></span><br />
  <b>$langDumpUser $langCsv</b>: 1. <a href='dumpuser.php?course=$code_cours'>$langcsvenc2</a>
       2. <a href='dumpuser.php?course=$code_cours&amp;enc=1253'>$langcsvenc1</a>
  </div>";

// display and handle search form if needed
$search_sql = '';
if (isset($_GET['search'])) {
        $search_params = "&amp;search=1&amp;course=".$code_cours;
        $search_nom = $search_prenom = $search_uname = ''; 
        if (!empty($_REQUEST['search_nom'])) {
                $search_nom = ' value="' . q($_REQUEST['search_nom']) . '"';
                $search_sql .= " AND user.nom LIKE " . autoquote(mysql_escape_string($_REQUEST['search_nom']).'%');
                $search_params .= "&amp;search_nom=" . urlencode($_REQUEST['search_nom']);
        }
        if (!empty($_REQUEST['search_prenom'])) {
                $search_prenom = ' value="' . q($_REQUEST['search_prenom']) . '"';
                $search_sql .= " AND user.prenom LIKE " . autoquote(mysql_escape_string($_REQUEST['search_prenom']).'%');
                $search_params .= "&amp;search_prenom=" . urlencode($_REQUEST['search_prenom']);
        }
        if (!empty($_REQUEST['search_uname'])) {
                $search_uname = ' value="' . q($_REQUEST['search_uname']) . '"';
                $search_sql .= " AND user.username LIKE " . autoquote(mysql_escape_string($_REQUEST['search_uname']).'%');
                $search_params .= "&amp;search_uname=" . urlencode($_REQUEST['search_uname']);
        }
        
        $tool_content .= "<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;search=1'>
        <fieldset>
        <legend>$langUserData</legend>
        <table width='100%' class='tbl'>
        <tr>
          <th class='left' width='180'>$langSurname:</th>
          <td><input type='text' name='search_nom'$search_nom></td>
        </tr>
        <tr>
          <th class='left'>$langName:</th>
          <td><input type='text' name='search_prenom'$search_prenom></td>
        </tr>
        <tr>
          <th class='left'>$langUsername:</th>
          <td><input type='text' name='search_uname'$search_uname></td>
        </tr>
        <tr>
          <th class='left'>&nbsp;</th>
          <td class='right'><input type='submit' value='".q($langSearch)."'></td>
        </tr>
        </table>
        </fieldset>
        </form>";
} else {
        $search_params = '&amp;course='.$code_cours;
}

// display navigation links if course users > COURSE_USERS_PER_PAGE
if ($countUser > COURSE_USERS_PER_PAGE and !isset($_GET['all'])) {
        $limit_sql = "LIMIT $limit, " . COURSE_USERS_PER_PAGE;
        $tool_content .= show_paging($limit, COURSE_USERS_PER_PAGE, $countUser,
                                     $_SERVER['SCRIPT_NAME'], $search_params, TRUE);
}

if (isset($_GET['all'])) {
        $extra_link = '&amp;all=true';
} else {
        $extra_link = '&amp;limit=' . $limit;
}

$addRoleSpan = (get_config('opencourses_enable')) ? 4 : 3;

$tool_content .= "
<table width='100%' id='users_table{$cours_id}' class='tbl_alt custom_list_order'>
<thead>
<tr>
  <th width='1'>$langID</th>
  <th><div align='left'><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;ord=s$extra_link'>$langName $langSurname</a></div></th>
  <th class='center' width='160'>$langGroup</th>
  <th class='center' width='90'><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;ord=rd$extra_link'>$langRegistrationDateShort</a></th>
  <th class='center' width='90'>$langAddRole</th>
</tr>";


// Numerating the items in the list to show: starts at 1 and not 0
//$i = $limit + 1;
//$ord = isset($_GET['ord'])?$_GET['ord']:'';
//
//switch ($ord) {
//        case 's': $order_sql = 'ORDER BY nom';
//                break;
//        case 'e': $order_sql = 'ORDER BY email';
//                break;
//        case 'am': $order_sql = 'ORDER BY am';
//                break;
//        case 'rd': $order_sql = 'ORDER BY cours_user.reg_date DESC';
//                break;
//        default: $order_sql = 'ORDER BY statut, editor DESC, tutor DESC, nom, prenom';
//                break;
//}
//$result = db_query("SELECT user.user_id, user.nom, user.prenom, user.email, user.parent_email,
//                           user.am, user.has_icon, cours_user.statut,
//                           cours_user.tutor, cours_user.editor, cours_user.reviewer, 
//                           cours_user.reg_date
//                    FROM cours_user, user
//                    WHERE `user`.`user_id` = `cours_user`.`user_id` 
//                    AND `cours_user`.`cours_id` = $cours_id
//                    $search_sql $order_sql $limit_sql"); 
//
//while ($myrow = mysql_fetch_array($result)) {
//        // bi colored table
//        if ($i%2 == 0) {
//                $tool_content .= "<tr class='odd'>";
//        } else {
//                $tool_content .= "<tr class='even'>";
//        }
//        // show public list of users
//        $am_message = empty($myrow['am'])? '': ("<div class='right'>($langAm: " . q($myrow['am']) . ")</div>");
//        $link_parent_email = "";
//        if (get_config('enable_secondary_email')) {
//                if ($myrow['editor'] == 1 or $myrow['tutor'] == 1 or $myrow['statut'] == 1 or empty($myrow['parent_email'])) {
//                        $link_parent_email = "";
//                } else {
//                        $link_parent_email = "<a href='emailparent.php?course=$code_cours&amp;id=$myrow[user_id]'>
//                                <img src='$themeimg/email.png' title='".q($langEmailToParent)."' alt='".q($langEmailToParent)."' />
//                                </a>";                
//                }
//        }
//        $tool_content .= "
//        <td class='smaller' valign='top' align='right'>$i.</td>\n" .
//                "<td valign='top' class='smaller'>" . display_user($myrow) . "&nbsp;&nbsp;(". mailto($myrow['email']) . ")  $link_parent_email $am_message</td>\n";
//        $tool_content .= "\n" .
//                "<td class='smaller' valign='top' width='150'>" . user_groups($cours_id, $myrow['user_id']) . "</td>\n" .
//                "<td align='center' class='smaller'>";
//        if ($myrow['reg_date'] == '0000-00-00') {
//                $tool_content .= $langUnknownDate;
//        } else {
//                $tool_content .= nice_format($myrow['reg_date']);
//        }
//        $alert_uname = $myrow['prenom'] . " " . $myrow['nom'];
//        $tool_content .= "&nbsp;&nbsp;<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;unregister=$myrow[user_id]$extra_link'
//                         onClick=\"return confirmation('" . js_escape($alert_uname) .
//                         "');\"><img src='$themeimg/cunregister.png' title='".q($langUnregCourse)."' alt='".q($langUnregCourse)."' /></a>";
//
//        $tool_content .= "</td>";
//        // tutor right
//        if ($myrow['tutor'] == '0') {
//                $tool_content .= "<td valign='top' align='center' class='add_user'>
//                                <a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;giveTutor=$myrow[user_id]$extra_link'>
//                                <img src='$themeimg/group_manager_add.png' title='".q($langGiveRightTutor)."' alt='".q($langGiveRightTutor)."' /></a></td>";
//        } else {
//                $tool_content .= "<td class='add_teacherLabel' align='center'  width='30'>
//                                <a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;removeTutor=$myrow[user_id]$extra_link' title='".q($langRemoveRightTutor)."'>
//                                <img src='$themeimg/group_manager_remove.png' title='".q($langRemoveRightTutor)."' alt='".q($langRemoveRightTutor)."' /></a></td>";
//        }
//        // editor right
//        if ($myrow['editor'] == '0') {
//            $tool_content .= "<td valign='top' align='center' class='add_user'>
//                                <a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;giveEditor=$myrow[user_id]$extra_link'>
//                                <img src='$themeimg/assistant_add.png' title='".q($langGiveRightEditor)."' alt='".q($langGiveRightEditor)."' /></a></td>";
//        } else {
//                $tool_content .= "<td class='add_teacherLabel' align='center' width='30'><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;removeEditor=$myrow[user_id]$extra_link' title='$langRemoveRightEditor'>
//                                <img src='$themeimg/assistant_remove.png' title ='".q($langRemoveRightEditor)."' alt='".q($langRemoveRightEditor)."' /></a></td>";
//        }
//        // admin right
//        if ($myrow['user_id'] != $_SESSION["uid"]) {
//                if ($myrow['statut']=='1') {
//                    if (get_config('opencourses_enable') && $myrow['reviewer'] == '1') {
//                        $tool_content .= "<td valign='top' class='add_teacherLabel' align='center'  width='30'>
//                                        <img src='$themeimg/teacher.png' title='".q($langTutor)."' alt='".q($langTutor)."' /></td>";
//                    } else {
//                        $tool_content .= "<td class='add_teacherLabel' align='center'  width='30'>
//                                        <a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;removeAdmin=$myrow[user_id]$extra_link' title='".q($langRemoveRightAdmin)."'>
//                                        <img src='$themeimg/teacher_remove.png' title='".q($langRemoveRightAdmin)."' alt='".q($langRemoveRightAdmin)."' /></a></td>";
//                    }
//                } else {
//                        $tool_content .= "<td valign='top' align='center' class='add_user'>
//                                <a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;giveAdmin=$myrow[user_id]$extra_link'>
//                                <img src='$themeimg/teacher_add.png' title='".q($langGiveRightAdmin)."' alt='".q($langGiveRightAdmin)."' /></a></td>";
//                }
//        } else {
//                if ($myrow['statut']=='1') {
//                        $tool_content .= "<td valign='top' class='add_teacherLabel' align='center'  width='30'>
//                                        <img src='$themeimg/teacher.png' title='".q($langTutor)."' alt='".q($langTutor)."' /></td>";
//                } else {
//                        $tool_content .= "<td class='smaller' valign='top' align='center'>
//                                        <a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;giveAdmin=$myrow[user_id]$extra_link'>
//                                        <img src='$themeimg/add.png' title='".q($langGiveRightAdmin)."' alt='".q($langGiveRightAdmin)."' /></a></td>";
//                }
//        }
//        
//        // opencourses reviewer right
//        if (get_config('opencourses_enable')) {
//            if ($myrow['user_id'] != $_SESSION["uid"]) {
//                if ($is_opencourses_reviewer and !$is_admin) {
//                    // do nothing as the reviewer cannot give the reviewer right to other users
//                    $tool_content .= "<td></td>";
//                } else {
//                    if ($myrow['reviewer'] == '1') {
//                        $tool_content .= "<td class='add_teacherLabel' align='center' width='30'>
//                                                    <a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;removeReviewer=$myrow[user_id]$extra_link' title='" . q($langRemoveRightReviewer) . "'>
//                                                    <img src='$themeimg/reviewer_remove.png' title='" . q($langRemoveRightReviewer) . "' alt='" . q($langRemoveRightReviewer) . "' /></a></td>";
//                    } else {
//                        $tool_content .= "<td valign='top' align='center' class='add_user'>
//                                            <a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;giveReviewer=$myrow[user_id]$extra_link'>
//                                            <img src='$themeimg/reviewer_add.png' title='" . q($langGiveRightReviewer) . "' alt='" . q($langGiveRightReviewer) . "' /></a></td>";
//                    }
//                }
//            } else {
//                if ($myrow['reviewer'] == '1') {
//                    $tool_content .= "<td valign='top' class='add_teacherLabel' align='center' width='30'>
//                                                <img src='$themeimg/reviewer.png' title='" . q($langOpenCoursesReviewer) . "' alt='" . q($langOpenCoursesReviewer) . "' /></td>";
//                } else {
//                    // do nothing as the course teacher cannot make himeself a reviewer
//                    $tool_content .= "<td></td>";
//                }
//            }
//        }
//        
//        $tool_content .= "</tr>";
//        $i++;
//}
$tool_content .= "</thead><tbody></tbody></table>";

draw($tool_content, 2, null, $head_content);
