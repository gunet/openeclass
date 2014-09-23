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
$require_help = true;
$helpTopic = 'Announce';
$guest_allowed = true;


include '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';
require_once 'include/sendMail.inc.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/log.php';
require_once 'modules/search/announcementindexer.class.php';
// The following is added for statistics purposes
require_once 'include/action.php';

$action = new action();
$action->record(MODULE_ID_ANNOUNCE);

define('RSS', 'modules/announcements/rss.php?c=' . $course_code);

//Identifying ajax request
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (isset($_POST['action']) && $is_editor) {
        $aidx = new AnnouncementIndexer();
        if ($_POST['action']=='delete') {
           /* delete announcement */
            $row_id = intval($_POST['value']);
            $announce = Database::get()->querySingle("SELECT title, content FROM announcement WHERE id = ?d ", $row_id);
            $txt_content = ellipsize_html(canonicalize_whitespace(strip_tags($announce->content)), 50, '+');
            Database::get()->query("DELETE FROM announcement WHERE id= ?d", $row_id);
            $aidx->remove($row_id);
            Log::record($course_id, MODULE_ID_ANNOUNCE, LOG_DELETE, array('id' => $row_id,
                                                                          'title' => $announce->title,
                                                                          'content' => $txt_content));
            exit();
        } elseif ($_POST['action']=='visible') {
          /* modify visibility */
           $row_id = intval($_POST['value']);
           $visible = intval($_POST['visible']) ? 1 : 0;
           Database::get()->query("UPDATE announcement SET visible = ?d WHERE id = ?d", $visible, $row_id);
           $aidx->store($row_id);
           exit();
        }
    }
    $limit = intval($_GET['iDisplayLength']);
    $offset = intval($_GET['iDisplayStart']);
    $keyword = '%' . $_GET['sSearch'] . '%';

    $student_sql = $is_editor? '': 'AND visible = 1 AND start_display<=CURDATE() AND stop_display>=CURDATE() ';
    $all_announc = Database::get()->querySingle("SELECT COUNT(*) AS total FROM announcement WHERE course_id = ?d $student_sql", $course_id);
    $filtered_announc = Database::get()->querySingle("SELECT COUNT(*) AS total FROM announcement WHERE course_id = ?d AND title LIKE ?s $student_sql", $course_id, $keyword);
    if ($limit>0) {
        $extra_sql = 'LIMIT ?d, ?d';
        $extra_terms = array($offset, $limit);
    } else {
        $extra_sql = '';
        $extra_terms = array();
    }

    $result = Database::get()->queryArray("SELECT * FROM announcement WHERE course_id = ?d AND title LIKE ?s $student_sql ORDER BY `order` DESC $extra_sql", $course_id, $keyword, $extra_terms);

    $data['iTotalRecords'] = $all_announc->total;
    $data['iTotalDisplayRecords'] = $filtered_announc->total;
    $data['aaData'] = array();
    if ($is_editor) {
        $iterator = 1;
        foreach ($result as $myrow) {
            //checking visible status
            if ($myrow->visible == '0') {
                $visible = 1;
                $vis_icon = 'fa-eye-slash';
                $vis_class = 'not_visible';
            } else {
                $visible = 0;
                $vis_icon = 'fa-eye';
                $vis_class= 'visible';
            }
            //checking ordering status and initializing appropriate arrows
            $up_arrow = $down_arrow = '';
            if ($iterator != 1 or $offset > 0)  {
                $up_arrow = icon('fa-arrow-up', $langMove, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;up=$myrow->id");
            }
            if ($offset + $iterator < $all_announc->total) {
                $down_arrow = icon('fa-arrow-down', $langMove, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;down=$myrow->id");
            }
            //setting datables column data
            $data['aaData'][] = array(
                'DT_RowId' => $myrow->id,
                'DT_RowClass' => $vis_class,
                '0' => date('d-m-Y', strtotime($myrow->date)),
                '1' => '<a href="'.$_SERVER['SCRIPT_NAME'].'?course='.$course_code.'&an_id='.$myrow->id.'">'.$myrow->title.'</a>',
                '2' => icon('fa-edit', $langModify, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;modify=$myrow->id")  .
                       "&nbsp;" . icon('fa-times', $langDelete, "#", "class=\"delete_btn\"") .
                       "&nbsp;" . icon($vis_icon, $langVisible, "#", "class=\"vis_btn\" data-vis=\"$visible\"") .
                       "&nbsp;" . $down_arrow . $up_arrow
                );
            $iterator++;
        }
    } else {
        foreach ($result as $myrow) {
            $data['aaData'][] = array(
                '0' => date('d-m-Y', strtotime($myrow->date)),
                '1' => '<a href="'.$_SERVER['SCRIPT_NAME'].'?course='.$course_code.'&an_id='.$myrow->id.'">'.$myrow->title.'</a>');
        }
    }
    echo json_encode($data);
    exit();
}
load_js('tools.js');
load_js('jquery');
//check if Datables code is needed
if (!isset($_GET['addAnnounce']) && !isset($_GET['modify']) && !isset($_GET['an_id'])) {
load_js('datatables');
load_js('datatables_bootstrap');
load_js('datatables_filtering_delay');
$head_content .= "<script type='text/javascript'>
        $(document).ready(function() {
           var oTable = $('#ann_table{$course_id}').dataTable ({
                'bStateSave': true,
                'bProcessing': true,
                'bServerSide': true,
                'sScrollX': true,
                'responsive': true,
                'sDom': '<\"top\"pfl<\"clear\">>rt<\"bottom\"ip<\"clear\">>',
                'sAjaxSource': '$_SERVER[REQUEST_URI]',
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
            $(document).on( 'click','.delete_btn', function (e) {
                e.preventDefault();
                if (confirmation('$langSureToDelAnnounce')) {
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
            $(document).on( 'click','.vis_btn', function (g) {
                g.preventDefault();
                var vis = $(this).data('vis');
                var row_id = $(this).closest('tr').attr('id');
                $.ajax({
                  type: 'POST',
                  url: '',
                  datatype: 'json',
                  data: {
                        action: 'visible', 
                        value: row_id, 
                        visible: vis
                  },
                  success: function(data){
                    var page_number = oTable.fnPagingInfo().iPage;
                    var per_page = oTable.fnPagingInfo().iLength
                    oTable.fnPageChange(page_number);
                  },
                  error: function(xhr, textStatus, error){
                      console.log(xhr.statusText);
                      console.log(textStatus);
                      console.log(error);
                  }
                });                
            });
            $('.success').delay(3000).fadeOut(1500);
            $('.dataTables_filter input').attr('placeholder', '$langTitle');
        });
        </script>";
}
ModalBoxHelper::loadModalBox();

$public_code = course_id_to_public_code($course_id);
$nameTools = $langAnnouncements;

if (isset($_GET['an_id'])) {
    (!$is_editor)? $student_sql = "AND visible = '1' AND start_display<=CURDATE() AND stop_display>=CURDATE()" : $student_sql = "";
    $row = Database::get()->querySingle("SELECT * FROM announcement WHERE course_id = ?d AND id = ". intval($_GET['an_id']) ." ".$student_sql, $course_id);
    if(empty($row)){
        redirect_to_home_page("modules/announcements/");
    }
}
if ($is_editor) {
	$head_content .= '<script type="text/javascript">var langEmptyGroupName = "' .
			 $langEmptyAnTitle . '";</script>';
        $aidx = new AnnouncementIndexer();
	$displayForm = true;
	/* up and down commands */
	if (isset($_GET['down'])) {
		$thisAnnouncementId = $_GET['down'];
		$sortDirection = "DESC";
	}
	if (isset($_GET['up'])) {
		$thisAnnouncementId = $_GET['up'];
		$sortDirection = "ASC";
	}

        $thisAnnouncementOrderFound = false;
	if (isset($thisAnnouncementId) && $thisAnnouncementId && isset($sortDirection) && $sortDirection) {
            $ids = Database::get()->queryArray("SELECT id, `order` FROM announcement
                                           WHERE course_id = ?d
                                           ORDER BY `order` $sortDirection",$course_id );
            foreach ($ids as $announcement) {
                if ($thisAnnouncementOrderFound) {
                    $nextAnnouncementId = $announcement->id;
                    $nextAnnouncementOrder = $announcement->order;
                    Database::get()->query("UPDATE announcement SET `order` = ?d WHERE id = ?d", $nextAnnouncementOrder, $thisAnnouncementId);
                    Database::get()->query("UPDATE announcement SET `order` = ?d WHERE id = ?d", $thisAnnouncementOrder, $nextAnnouncementId);
                    break;
                }
                // find the order
                if ($announcement->id == $thisAnnouncementId) {
                    $thisAnnouncementOrder = $announcement->order;
                    $thisAnnouncementOrderFound = true;
                }
           }
	}

    /* modify */
    if (isset($_GET['modify'])) {
        $modify = intval($_GET['modify']);
        $announce = Database::get()->querySingle("SELECT * FROM announcement WHERE id=?d", $modify);
        if ($announce) {
            $AnnouncementToModify = $announce->id;
            $contentToModify = $announce->content;
            $titleToModify = q($announce->title);
            $showFrom = q($announce->start_display);
            $showUntil = q($announce->stop_display);
        }
    }

    /* submit */
    if (isset($_POST['submitAnnouncement'])) {
        // modify announcement
        $antitle = $_POST['antitle'];
        $newContent = purify($_POST['newContent']);
        $send_mail = isset($_POST['recipients']) && (count($_POST['recipients'])>0);
        $start_display = (isset($_POST['startdate']) && !empty($_POST['startdate']))? $_POST['startdate']:"2014-01-01";
        $stop_display = (isset($_POST['enddate']) && !empty($_POST['enddate']))? $_POST['enddate']:"2094-12-31";
        if (!empty($_POST['id'])) {
            $id = intval($_POST['id']);
            Database::get()->query("UPDATE announcement SET content = ?s, title = ?s, `date` = NOW(), start_display = ?t, stop_display = ?t  WHERE id = ?d", $newContent, $antitle, $start_display, $stop_display, $id);
            $log_type = LOG_MODIFY;
            $message = "<p class='success'>$langAnnModify</p>";
        } else { // add new announcement
            $orderMax = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM announcement
                                                   WHERE course_id = ?d", $course_id)->maxorder;
            $order = $orderMax + 1;
            // insert
            $id = Database::get()->query("INSERT INTO announcement
                                         SET content = ?s,
                                             title = ?s, `date` = NOW(),
                                             course_id = ?d, `order` = ?d,
                                             visible = 1,
                                             start_display = ?t,
                                             stop_display = ?t", $newContent, $antitle, $course_id, $order, $start_display, $stop_display)->lastInsertID;
            $log_type = LOG_INSERT;
        }
        $aidx->store($id);
        $txt_content = ellipsize_html(canonicalize_whitespace(strip_tags($_POST['newContent'])), 50, '+');
        Log::record($course_id, MODULE_ID_ANNOUNCE, $log_type, array('id' => $id,
                                                               'email' => $send_mail,
                                                               'title' => $_POST['antitle'],
                                                               'content' => $txt_content));

        // send email
        if ($send_mail) {
            $recipients_emaillist = "";
            foreach($_POST['recipients'] as $re){
                $recipients_emaillist .= (empty($recipients_emaillist))? "'$re'":",'$re'";
            }
            $emailContent = "$professorMessage: $_SESSION[givenname] $_SESSION[surname]<br>\n<br>\n" .
                    $_POST['antitle'] .
                    "<br>\n<br>\n" .
                    $_POST['newContent'];
            $emailSubject = "$professorMessage ($public_code - $title - $langAnnouncement)";
            // select students email list
            $countEmail = 0;
            $invalid = 0;
            $recipients = array();
            $emailBody = html2text($emailContent);
            $linkhere = "&nbsp;<a href='${urlServer}main/profile/emailunsubscribe.php?cid=$course_id'>$langHere</a>.";
            $unsubscribe = "<br /><br />$langNote: " . sprintf($langLinkUnsubscribe, $title);
            $emailContent .= $unsubscribe . $linkhere;
            $general_to = 'Members of course ' . $course_code;
            Database::get()->queryFunc("SELECT course_user.user_id as id, user.email as email
                                                   FROM course_user, user
                                                   WHERE course_id = ?d AND user.email IN ($recipients_emaillist) AND 
                                                         course_user.user_id = user.id", function ($person)
                    use (&$countEmail, &$recipients, &$invalid, $course_id, $general_to, $emailSubject, $emailBody, $emailContent, $charset) {
                $countEmail++;
                $emailTo = $person->email;
                $user_id = $person->id;
                // check email syntax validity
                if (!email_seems_valid($emailTo)) {
                    $invalid++;
                } elseif (get_user_email_notification($user_id, $course_id)) {
                    // checks if user is notified by email
                    array_push($recipients, $emailTo);
                }
                // send mail message per 50 recipients
                if (count($recipients) >= 50) {
                    send_mail_multipart("$_SESSION[givenname] $_SESSION[surname]", $_SESSION['email'], $general_to, $recipients, $emailSubject, $emailBody, $emailContent, $charset);
                    $recipients = array();
                }
            }, $course_id);
            if (count($recipients) > 0) {
                send_mail_multipart("$_SESSION[givenname] $_SESSION[surname]", $_SESSION['email'], $general_to, $recipients, $emailSubject, $emailBody, $emailContent, $charset);
            }
            $messageInvalid = " $langOn $countEmail $langRegUser, $invalid $langInvalidMail";
            $message = "<p class='success'>$langAnnAdd $langEmailSent<br />$messageInvalid</p>";
        } // if $emailOption==1
        else {
            $message = "<p class='success'>$langAnnAdd</p>";
        }
    } // end of if $submit


    // teacher display
    if (isset($message) && $message) {
        $tool_content .= $message . "<br/>";
        $displayForm = false; //do not show form
    }
    /* display form */
    if ($displayForm && (isset($_GET['addAnnounce']) || isset($_GET['modify']))) {
        load_js('jquery-ui');
        load_js('jquery-ui-timepicker-addon.min.js');
        $head_content .= "<link rel='stylesheet' type='text/css' href='{$urlAppend}js/jquery-ui-timepicker-addon.min.css'>
            <script type='text/javascript'>
            $(function() {
            $('input[name=startdate]').datepicker({
                dateFormat: 'yy-mm-dd'
                });
            $('input[name=enddate]').datepicker({
                dateFormat: 'yy-mm-dd'
                });
               });"
        . "</script>";
        $tool_content .= "
        <form method='post' action='$_SERVER[SCRIPT_NAME]?course=".$course_code."' onsubmit=\"return checkrequired(this, 'antitle');\">
        <fieldset>
        <legend>$langAnnouncement</legend>
	<table class='tbl' width='100%'>";
        if (isset($_GET['modify'])) {
            $langAdd = $nameTools = $langModifAnn;
        } else {
	    $nameTools = $langAddAnn;
        }
	$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langAnnouncements);
        if (!isset($AnnouncementToModify)) $AnnouncementToModify = "";
        if (!isset($contentToModify)) $contentToModify = "";
        if (!isset($titleToModify)) $titleToModify = "";
        if (!isset($showFrom)) $showFrom = "";
        if (!isset($showUntil)) $showUntil = "";
        $tool_content .= "
        <tr>
          <th>$langAnnTitle:</th>
        </tr>
        <tr>
          <td><input type='text' name='antitle' value='$titleToModify' size='50' /></td>
	</tr>
	<tr>
          <th>$langAnnBody:</th>
        </tr>
        <tr>
          <td>".rich_text_editor('newContent', 4, 20, $contentToModify)."</td>
        </tr>
	<tr>
          <th><img src='$themeimg/email.png' title='email' /> $langEmailOption:</th>
        </tr>
        <tr>
          <td>
            <select name='recipients[]' multiple='true' class='auth_input' id='select-recipients' style='min-width:400px;'>";
            $course_users = Database::get()->queryArray("SELECT cu.user_id, CONCAT(u.surname, ' ', u.givenname) name, u.email FROM course_user cu JOIN user u ON cu.user_id=u.id WHERE cu.course_id = ?d AND u.email<>'' AND u.email IS NOT NULL ORDER BY u.surname, u.givenname", $course_id);
            foreach($course_users as $cu){
               $tool_content .= "<option value='{$cu->email}'>{$cu->name} ($cu->email)</option>"; 
            } 
            $tool_content .= "</select>
          </td>
	</tr>
	<tr>
          <th>$langAnnouncementActivePeriod:</th>
        </tr>
        <tr>
          <td>$langFrom: <input type='text' name='startdate' value='$showFrom'>&nbsp;  $langUntil:<input type='text' name='enddate' value='$showUntil'></td>
        </tr>
        <tr>
          <td class='right'><input type='submit' name='submitAnnouncement' value='".q($langAdd)."' /></td>
	</tr>
	</table>
	<input type='hidden' name='id' value='$AnnouncementToModify' />
        </fieldset>
	</form>";
    } else {
	/* display actions toolbar */
	$tool_content .= "
	<div id='operations_container'>
	  <ul id='opslist'>";
        if (isset($_GET['an_id'])) {
            $tool_content .= "<li><a href='" . $_SERVER['SCRIPT_NAME'] . "?course=" .$course_code . "&amp;modify=$row->id'>" . $langModify . "</a></li>
                              <li><a href='" . $_SERVER['SCRIPT_NAME'] . "?course=" .$course_code . "&amp;delete=$row->id' onClick=\"return confirmation('$langSureToDelAnnounce');\">" . $langDelete . "</a></li>";
        } else {
            $tool_content .= "<li><a href='" . $_SERVER['SCRIPT_NAME'] . "?course=" .$course_code . "&amp;addAnnounce=1'>" . $langAddAnn . "</a></li>";
        }
        $tool_content .= "</ul></div>";
    }
} // end: teacher only

    /* display announcements */
    if (isset($_GET['an_id'])) {
        $nameTools = $row->title;
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langAnnouncements);
        $tool_content .= $row->content;
    }
    if (!isset($_GET['addAnnounce']) && !isset($_GET['modify']) && !isset($_GET['an_id'])) {
        $tool_content .= "<table id='ann_table{$course_id}' cellspacing='0' class='table table-bordered' width='100%'>";
        $tool_content .= "<thead>";
        $tool_content .= "<tr><th>$langDate</th><th>$langAnnouncement</th>";
        if ($is_editor) {
            $tool_content .= "<th class='center'>$langActions</th>";
        }
        $tool_content .= "</tr></thead><tbody></tbody></table>";
    }
add_units_navigation(TRUE);
load_js('jquery-ui');
load_js('jquery.multiselect.min.js');
$head_content .= "<script type='text/javascript'>$(document).ready(function () {
        $('#select-recipients').multiselect({
                selectedText: '$langJQSelectNum',
                noneSelectedText: '$langJQNoneSelected',
                checkAllText: '$langJQCheckAll',
                uncheckAllText: '$langJQUncheckAll'
        });
});</script>
<link href='../../js/jquery.multiselect.css' rel='stylesheet' type='text/css'>";
draw($tool_content, 2, null, $head_content);
