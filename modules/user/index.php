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

$require_login = true;
$require_current_course = TRUE;
$require_course_admin = TRUE;
$require_help = TRUE;
$helpTopic = 'User';

require_once '../../include/baseTheme.php';
require_once 'modules/admin/admin.inc.php';
require_once 'include/log.php';

//Identifying ajax request
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && $is_editor) {
    if (isset($_POST['action']) && $_POST['action']=='delete') {
            $unregister_gid = intval($_POST['value']);
            $unregister_ok = true;            
            // Security: don't remove myself except if there is another prof
            if ($unregister_gid == $uid) {
                    $result = Database::get()->querySingle("SELECT COUNT(user_id) AS cnt FROM course_user
                                            WHERE course_id = ?d AND
                                                  status = " . USER_TEACHER . " AND
                                                  user_id != ?d
                                            LIMIT 1", $course_id, $uid);
                    
                    if ($result) {
                        if ($result->cnt == 0) {
                            $unregister_ok = false;
                        }
                    }
            }
            if ($unregister_ok) {
                    Database::get()->query("DELETE FROM course_user
                                            WHERE user_id = ?d AND
                                                course_id = ?d", $unregister_gid, $course_id);
                    if (check_guest($unregister_gid)) {
                        Database::get()->query("DELETE FROM user WHERE id = ?d", $unregister_gid);
                    }
                    Database::get()->query("DELETE FROM group_members
                                    WHERE user_id = ?d AND
                                          group_id IN (SELECT id FROM `group` WHERE course_id = ?d)", $unregister_gid, $course_id);
            }
    exit();
    }    
    
    $limit = intval($_GET['iDisplayLength']);
    $offset = intval($_GET['iDisplayStart']);
    
    if (!empty($_GET['sSearch'])) {
        $search_values = array_fill(0, 4, '%' . $_GET['sSearch'] . '%');
        $search_sql = 'AND (user.surname LIKE ?s OR user.givenname LIKE ?s OR user.username LIKE ?s OR user.email LIKE ?s)';     
    } else {
        $search_sql='';
        $search_values = array();
    }
    if (!empty($_GET['iSortCol_0'])){
        $order_sql = 'ORDER BY ';
        $order_sql .= ($_GET['iSortCol_0']==1)?'user.givenname ':'course_user.reg_date ';
        $order_sql .= $_GET['sSortDir_0'];
    } else {
        $order_sql='';
    }
    $limit_sql = ($limit>0) ? "LIMIT $offset,$limit" : "";
    
    $all_users = Database::get()->querySingle("SELECT COUNT(*) AS total FROM course_user, user 
                                                WHERE `user`.`id` = `course_user`.`user_id`
                                                AND `course_user`.`course_id` = ?d", $course_id)->total;
    $filtered_users = Database::get()->querySingle("SELECT COUNT(*) AS total FROM course_user, user 
                                                WHERE `user`.`id` = `course_user`.`user_id`
                                                AND `course_user`.`course_id` = ?d $search_sql", $course_id, $search_values)->total;
    $result = Database::get()->queryArray("SELECT user.id, user.surname, user.givenname, user.email,
                           user.am, user.has_icon, course_user.status,
                           course_user.tutor, course_user.editor, course_user.reviewer, 
                           course_user.reg_date
                    FROM course_user, user
                    WHERE `user`.`id` = `course_user`.`user_id` 
                    AND `course_user`.`course_id` = ?d
                    $search_sql $order_sql $limit_sql", $course_id, $search_values);
    
    $data['iTotalRecords'] = $all_users;
    $data['iTotalDisplayRecords'] = $filtered_users;
    $data['aaData'] = array();
        $iterator = 1;
        foreach ($result as $myrow) {
            $full_name = $myrow->givenname . " " . $myrow->surname;
            $am_message = empty($myrow->am)? '': ("<div class='right'>($langAm: " . q($myrow->am) . ")</div>");
            /*$link_parent_email = "";
            if (get_config('enable_secondary_email')) {
                    if ($myrow->editor == 1 or $myrow->tutor == 1 or $myrow->status == 1 or empty($myrow['parent_email'])) {
                            $link_parent_email = "";
                    } else {
                            $link_parent_email = "<a href='emailparent.php?course=$course_code&amp;id=$myrow->id'>
                                    <img src='$themeimg/email.png' title='".q($langEmailToParent)."' alt='".q($langEmailToParent)."' />
                                    </a>";                
                    }
            } */           
            //create date field with unregister button
            $date_field = ($myrow->reg_date == '0000-00-00')? $langUnknownDate : nice_format($myrow->reg_date);
            if ($myrow->status != '1') {
                $date_field .= "&nbsp;&nbsp;".icon('fa-times', $langUnregCourse, '', 'class="delete_btn"');
            }            
            //Create appropriate role control buttons
            //Tutor right
            if ($myrow->tutor == '0') {
                $user_role_controls = "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;giveTutor=$myrow->id'><img src='$themeimg/group_manager_add.png' alt='$langGiveRightTutor' title='$langGiveRightTutor'></a>";                
            } else {
                $user_role_controls = "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;removeTutor=$myrow->id'><img src='$themeimg/group_manager_remove.png' alt='$langRemoveRightTutor' title='$langRemoveRightTutor'></a>";              
            }
            //Editor right
            if ($myrow->editor == '0') {
                $user_role_controls .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;giveEditor=$myrow->id'><img src='$themeimg/assistant_add.png' alt='$langGiveRightEditor' title='$langGiveRightEditor'></a>";                
            } else {
                $user_role_controls .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;removeEditor=$myrow->id'><img src='$themeimg/assistant_remove.png' alt='$langRemoveRightEditor' title='$langRemoveRightEditor'></a>";                
            }
            // Admin right
            if ($myrow->id != $_SESSION["uid"]) {
                    if ($myrow->status == '1') {
                        if (get_config('opencourses_enable') && $myrow->reviewer == '1') {
                            $user_role_controls .= "<img src='$themeimg/teacher.png' alt='$langTutor' title='$langTutor'>";                            
                        } else {
                            $user_role_controls .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;removeAdmin=$myrow->id'><img src='$themeimg/teacher_remove.png' alt='$langRemoveRightAdmin' title='$langRemoveRightAdmin'></a>";                            
                        }
                    } else {
                            $user_role_controls .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;giveAdmin=$myrow->id'><img src='$themeimg/teacher_add.png' alt='$langGiveRightAdmin' title='$langGiveRightAdmin'></a>";                            
                    }                 
            } else {
                    if ($myrow->status == '1') {
                            $user_role_controls .= "<img src='$themeimg/teacher.png' alt='$langTutor' title='$langTutor'>";                            
                    } else {
                            $user_role_controls .= icon('fa-plus', $langGiveRightAdmin, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;giveAdmin=$myrow->id");                            
                    }
            }
            // opencourses reviewer right
            if (get_config('opencourses_enable')) {
                if ($myrow->id != $_SESSION["uid"]) {
                    if ($is_opencourses_reviewer and !$is_admin) {
                        // do nothing as the reviewer cannot give the reviewer right to other users
                        $user_role_controls .= "";
                    } else {
                        if ($myrow->reviewer == '1') {
                            $user_role_controls .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;removeReviewer=$myrow->id'><img src='$themeimg/reviewer_remove.png' alt='$langRemoveRightReviewer' title='$langRemoveRightReviewer'></a>";                            
                        } else {
                            $user_role_controls .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;giveReviewer=$myrow->id'><img src='$themeimg/reviewer_add.png' alt='$langGiveRightReviewer' title='$langGiveRightReviewer'></a>";                            
                        }
                    }
                } else {
                    if ($myrow->reviewer == '1') {
                        $user_role_controls .= "<img src='$themeimg/reviewer.png' alt='$langOpenCoursesReviewer' title='$langOpenCoursesReviewer'>";                                 
                    } else {
                        // do nothing as the course teacher cannot make himeself a reviewer
                        $user_role_controls .= "";
                    }
                }
            }               
            //setting datables column data
            $data['aaData'][] = array(
                'DT_RowId' => $myrow->id,
                'DT_RowClass' => 'smaller',
                '0' => $iterator, 
                '1' => display_user($myrow->id). "&nbsp<span>(<a href='mailto:". $myrow->email . "'>".$myrow->email."</a>) $am_message</span>", 
                '2' => user_groups($course_id, $myrow->id),
                '3' => $date_field,
                '4' => $user_role_controls 
                );
            $iterator++;
        }      
    echo json_encode($data);
    exit();
}

$limit = isset($_REQUEST['limit'])? intval($_REQUEST['limit']): 0;

$nameTools = $langUsers;
load_js('tools.js');
load_js('datatables');
load_js('datatables_filtering_delay');
$head_content .= "
<script type='text/javascript'>
        $(document).ready(function() {
           var oTable = $('#users_table{$course_id}').dataTable ({
                'bStateSave': true,
                'bProcessing': true,
                'bServerSide': true,
                'sScrollX': true,
                'sDom': '<\"top\"pfl<\"clear\">>rt<\"bottom\"ip<\"clear\">>',
                'sAjaxSource': '$_SERVER[REQUEST_URI]',                   
                'aLengthMenu': [
                   [10, 15, 20 , -1],
                   [10, 15, 20, '$langAllOfThem'] // change per page values here
               ],                    
                'sPaginationType': 'full_numbers',              
                'bSort': true,
                'aoColumnDefs': [{ 'bSortable': false, 'aTargets': [ 0 ] }, { 'bSortable': false, 'aTargets': [ 2 ] }, { 'bSortable': false, 'aTargets': [ 4 ] }],
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
            $(document).on( 'click','.delete_btn', function (e) {
                e.preventDefault();
                if (confirmation('".js_escape($langDeleteUser)." ".js_escape($langDeleteUser2). "')) {
                    var row_id = $(this).closest('tr').attr('id');
                    $.ajax({
                      type: 'POST',
                      url: '',
                      datatype: 'json',
                      data: {
                        action: 'delete', 
                        value: row_id
                      },
                      success: function(data){
                        var num_page_records = oTable.fnGetData().length;
                        var per_page = oTable.fnPagingInfo().iLength;
                        var page_number = oTable.fnPagingInfo().iPage;
                        if(num_page_records==1){
                            if(page_number!=0) {
                                page_number--;
                            }
                        }
                        $('#tool_title').after('<p class=\"success\">$langUserDeleted</p>');
                        $('.success').delay(3000).fadeOut(1500);    
                        oTable.fnPageChange(page_number);
                      },
                      error: function(xhr, textStatus, error){
                          console.log(xhr.statusText);
                          console.log(textStatus);
                          console.log(error);
                      }
                    });                    
                 }
            });
            $('.dataTables_filter input').attr('placeholder', '$langName, Username, Email');
            $('.success').delay(3000).fadeOut(1500);
        });
        </script>";

$limit_sql = '';
// Handle user removal / status change
if (isset($_GET['giveAdmin'])) {
        $new_admin_gid = intval($_GET['giveAdmin']);
        Database::get()->query("UPDATE course_user SET status = " .USER_TEACHER. "
                        WHERE user_id = ?d
                        AND course_id = ?d", $new_admin_gid, $course_id);
} elseif (isset($_GET['giveTutor'])) {
        $new_tutor_gid = intval($_GET['giveTutor']);
        Database::get()->query("UPDATE course_user SET tutor = 1
                        WHERE user_id = ?d
                        AND course_id = ?d", $new_tutor_gid, $course_id);
        Database::get()->query("UPDATE group_members, `group` SET is_tutor = 0
                        WHERE `group`.id = group_members.group_id AND 
                              `group`.course_id = ?d AND
                              group_members.user_id = ?d", $course_id, $new_tutor_gid);
} elseif (isset($_GET['giveEditor'])) {
        $new_editor_gid = intval($_GET['giveEditor']);
        Database::get()->query("UPDATE course_user SET editor = 1
                        WHERE user_id = ?d
                        AND course_id = ?d", $new_editor_gid, $course_id);
} elseif (isset($_GET['removeAdmin'])) {
        $removed_admin_gid = intval($_GET['removeAdmin']);
        Database::get()->query("UPDATE course_user SET status = " .USER_STUDENT. "
                        WHERE user_id <> ?d AND
                              user_id = ?d AND
                              course_id = ?d", $uid, $removed_admin_gid, $course_id);
} elseif (isset($_GET['removeTutor'])) {
        $removed_tutor_gid = intval($_GET['removeTutor']);
        Database::get()->query("UPDATE course_user SET tutor = 0
                        WHERE user_id = ?d 
                              AND course_id = ?d", $removed_tutor_gid, $course_id);
} elseif (isset($_GET['removeEditor'])) {
        $removed_editor_gid = intval($_GET['removeEditor']);
        Database::get()->query("UPDATE course_user SET editor = 0
                        WHERE user_id = ?d 
                        AND course_id = ?d", $removed_editor_gid, $course_id);
}

if (get_config('opencourses_enable')) {
    if (isset($_GET['giveReviewer'])) {
        $new_reviewr_gid = intval($_GET['giveReviewer']);
        Database::get()->query("UPDATE course_user SET status = ".USER_TEACHER.", reviewer = 1
                        WHERE user_id = ?d 
                        AND course_id = ?d", $new_reviewr_gid, $course_id);
    } elseif (isset($_GET['removeReviewer'])) {
        $removed_reviewer_gid = intval($_GET['removeReviewer']);
        Database::get()->query("UPDATE course_user SET status = ".USER_STUDENT.", reviewer = 0
                        WHERE user_id <> ?d AND
                              user_id = ?d AND
                              course_id = ?d", $uid, $removed_reviewer_gid, $course_id);
    }
}

// show help link and link to Add new user, search new user and management page of groups
$tool_content .= "
<div id='operations_container'>
  <ul id='opslist'>
    <li><b>$langAdd:</b>&nbsp; <a href='adduser.php?course=$course_code'>$langOneUser</a></li>
    <li><a href='muladduser.php?course=$course_code'>$langManyUsers</a></li>
    <li><a href='guestuser.php?course=$course_code'>$langGUser</a>&nbsp;</li>
    <li><a href='../group/index.php?course=$course_code'>$langGroupUserManagement</a></li>
    <li><a href='../course_info/refresh_course.php?course=$course_code'>$langDelUsers</a></li>
  </ul>
</div>";

// display number of users
$tool_content .= "
<div class='info'><b>$langDumpUser $langCsv</b>: 1. <a href='dumpuser.php?course=$course_code'>$langcsvenc2</a>
       2. <a href='dumpuser.php?course=$course_code&amp;enc=1253'>$langcsvenc1</a>
  </div>";


$tool_content .= "
<table width='100%' id='users_table{$course_id}' class='tbl_alt custom_list_order'>
    <thead>
        <tr>
          <th width='1'>$langID</th>
          <th><div align='left' width='100'>$langName $langSurname</div></th>
          <th class='center'>$langGroup</th>
          <th class='center' width='80'>$langRegistrationDateShort</th>
          <th class='center' width='100'>$langAddRole</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>";

draw($tool_content, 2, null, $head_content);
