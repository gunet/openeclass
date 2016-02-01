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
require_once 'modules/search/indexer.class.php';
require_once 'modules/tags/moduleElement.class.php';
// The following is added for statistics purposes
require_once 'include/action.php';

$action = new action();
$action->record(MODULE_ID_ANNOUNCE);

define_rss_link();

//Identifying ajax request
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (isset($_POST['action']) && $is_editor) {
        if ($_POST['action']=='delete') {
           /* delete announcement */
            $row_id = intval($_POST['value']);
            $announce = Database::get()->querySingle("SELECT title, content FROM announcement WHERE id = ?d ", $row_id);
            $txt_content = ellipsize_html(canonicalize_whitespace(strip_tags($announce->content)), 50, '+');
            Database::get()->query("DELETE FROM announcement WHERE id= ?d", $row_id);
            Indexer::queueAsync(Indexer::REQUEST_REMOVE, Indexer::RESOURCE_ANNOUNCEMENT, $row_id);
            Log::record($course_id, MODULE_ID_ANNOUNCE, LOG_DELETE, array('id' => $row_id,
                                                                          'title' => $announce->title,
                                                                          'content' => $txt_content));
            exit();
        } elseif ($_POST['action']=='visible') {
          /* modify visibility */
           $row_id = intval($_POST['value']);
           $visible = intval($_POST['visible']) ? 1 : 0;
           Database::get()->query("UPDATE announcement SET visible = ?d WHERE id = ?d", $visible, $row_id);
           Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_ANNOUNCEMENT, $row_id);
           exit();
        }
    }
    $limit = intval($_GET['iDisplayLength']);
    $offset = intval($_GET['iDisplayStart']);
    $keyword = '%' . $_GET['sSearch'] . '%';

    $student_sql = $is_editor? '': 'AND visible = 1 AND (start_display <= CURDATE() OR start_display IS NULL) AND (stop_display >= CURDATE() OR stop_display IS NULL)';
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
                $status_icon_list = '<li><span class="fa fa-eye-slash"></span> '.$langAdminAnNotVis.'</li>';
                $vis_icon = 'fa-eye-slash';
                $vis_class = 'not_visible';
            } else {
                $visible = 0;
                $status_icon_list = '<li><span class="fa fa-eye"></span> '.$langAdminAnVis.'</li>';
                $vis_icon = 'fa-eye';
                $vis_class = 'visible';
            }
            //setting datables column data
            $data['aaData'][] = array(
                'DT_RowId' => $myrow->id,
                'DT_RowClass' => $vis_class,
                '0' => '<a href="'.$_SERVER['SCRIPT_NAME'].'?course='.$course_code.'&an_id='.$myrow->id.'">'.q($myrow->title).'</a>',
                '1' => claro_format_locale_date($dateFormatLong, strtotime($myrow->date)),
                '2' => '<ul class="list-unstyled">'.$status_icon_list.'</ul>',
                '3' => action_button(array(
                    array('title' => $langEditChange,
                          'icon' => 'fa-edit',
                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;modify=$myrow->id"),
                    array('title' => !$myrow->visible == '0' ? $langViewHide : $langViewShow,
                          'icon' => !$myrow->visible == '0' ? 'fa-eye-slash' : 'fa-eye',
                          'icon-class' => 'vis_btn',
                          'icon-extra' => "data-vis='$visible' data-id='$myrow->id'"),
                    array('title' => $langDelete,
                          'class' => 'delete',
                          'icon' => 'fa-times',
                          'icon-class' => 'delete_btn',
                          'icon-extra' => "data-id='$myrow->id'"),
                    array('title' => $langMove,
                          'level' => 'primary',
                          'icon' => 'fa-arrow-up',
                          'disabled' => !($iterator != 1 || $offset > 0),
                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;up=$myrow->id"),
                    array('title' => $langMove,
                          'level' => 'primary',
                          'disabled' => $offset + $iterator >= $all_announc->total,
                          'icon' => 'fa-arrow-down',
                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;down=$myrow->id"))));
            $iterator++;
        }
    } else {
        foreach ($result as $myrow) {
            $data['aaData'][] = array(
                '0' => '<a href="'.$_SERVER['SCRIPT_NAME'].'?course='.$course_code.'&an_id='.$myrow->id.'">' . q($myrow->title) . '</a>',
                '1' => claro_format_locale_date($dateFormatLong, strtotime($myrow->date))
                );
        }
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}
load_js('tools.js');
//check if Datables code is needed
if (!isset($_GET['addAnnounce']) && !isset($_GET['modify']) && !isset($_GET['an_id'])) {
load_js('datatables');
$head_content .= "<script type='text/javascript'>
        $(document).ready(function() {
           var oTable = $('#ann_table{$course_id}').DataTable ({
                ".(($is_editor)?"'aoColumnDefs':[{'sClass':'option-btn-cell', 'aTargets':[-1]}],":"")."
                'bStateSave': true,
                'bProcessing': true,
                'bServerSide': true,
                'sScrollX': true,
                'responsive': true,
                'searchDelay': 1000,
                'sAjaxSource': '$_SERVER[REQUEST_URI]',
                'aLengthMenu': [
                   [10, 15, 20 , -1],
                   [10, 15, 20, '$langAllOfThem'] // change per page values here
               ],
                'fnDrawCallback': function( oSettings ) {
                    popover_init();
                    $('#ann_table{$course_id}_filter label input').attr({
                          class : 'form-control input-sm',
                          placeholder : '$langSearch...'
                        });
},
                'sPaginationType': 'full_numbers',
                'bSort': false,
                'oLanguage': {
                       'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
                       'sZeroRecords':  '".$langNoResult."',
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
            $(document).on( 'click','.delete_btn', function (e) {
                e.preventDefault();
                var row_id = $(this).data('id');
                bootbox.confirm('".js_escape($langSureToDelAnnounce)."', function(result) {
                    if(result) {
                        $.ajax({
                          type: 'POST',
                          url: '',
                          datatype: 'json',
                          data: {
                             action: 'delete',
                             value: row_id
                          },
                          success: function(data){
                            var info = oTable.page.info();
                            /*var num_page_records = info.recordsDisplay;
                            var per_page = info.iLength;*/
                            var page_number = info.page;
                            /*if(num_page_records==1){
                                if(page_number!=0) {
                                    page_number--;
                                }
                            } */
                            oTable.draw(false);
                          },
                          error: function(xhr, textStatus, error){
                              console.log(xhr.statusText);
                              console.log(textStatus);
                              console.log(error);
                          }
                        });
                        $.ajax({
                            type: 'POST',
                            url: '{$urlAppend}/modules/search/idxasync.php'
                        });
                    }
                });
            });
            $(document).on( 'click','.vis_btn', function (g) {
                g.preventDefault();
                var vis = $(this).data('vis');
                var row_id = $(this).data('id');
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
                    oTable.draw(false);
                  },
                  error: function(xhr, textStatus, error){
                      console.log(xhr.statusText);
                      console.log(textStatus);
                      console.log(error);
                  }
                });
                $.ajax({
                    type: 'POST',
                    url: '{$urlAppend}/modules/search/idxasync.php'
                });
            });
            $('.success').delay(3000).fadeOut(1500);

        });
        </script>";
}
ModalBoxHelper::loadModalBox();

$public_code = course_id_to_public_code($course_id);
$toolName = $langAnnouncements;

if (isset($_GET['an_id'])) {
    (!$is_editor)? $student_sql = "AND visible = 1 AND (start_display <= CURDATE() OR start_display IS NULL) AND (stop_display >= CURDATE() OR stop_display IS NULL)" : $student_sql = "";
    $row = Database::get()->querySingle("SELECT * FROM announcement WHERE course_id = ?d AND id = ". intval($_GET['an_id']) ." ".$student_sql, $course_id);
    if(empty($row)){
        redirect_to_home_page("modules/announcements/");
    }
}
if ($is_editor) {
  $head_content .= '<script type="text/javascript">var langEmptyGroupName = "' . $langEmptyAnTitle . '";</script>';
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
           redirect_to_home_page("modules/announcements/index.php?course=$course_code");
  }

    /* modify */
    if (isset($_GET['modify'])) {
        $modify = intval($_GET['modify']);
        $announce = Database::get()->querySingle("SELECT * FROM announcement WHERE id=?d", $modify);
        if ($announce) {
            $AnnouncementToModify = $announce->id;
            $contentToModify = $announce->content;
            $titleToModify = q($announce->title);
            if ($announce->start_display) {
                $startDate_obj = DateTime::createFromFormat('Y-m-d', $announce->start_display);
                $startdate = $startDate_obj->format('d-m-Y');
                $showFrom = q($startdate);
            }
            if ($announce->stop_display) {
                $endDate_obj = DateTime::createFromFormat('Y-m-d', $announce->stop_display);
                $enddate = $endDate_obj->format('d-m-Y');
                $showUntil = q($enddate);
            }
        }
    }

    /* submit */
    if (isset($_POST['submitAnnouncement'])) { // modify announcement
        if ($language == 'el') {
            $datetime = claro_format_locale_date($dateTimeFormatShort);
        } else {
            $datetime = date('l jS \of F Y h:i A');
        }
        $antitle = $_POST['antitle'];
        $newContent = purify($_POST['newContent']);
        $send_mail = isset($_POST['recipients']) && (count($_POST['recipients'])>0);
        if (isset($_POST['startdate']) && !empty($_POST['startdate'])) {
            $startDate_obj = DateTime::createFromFormat('d-m-Y', $_POST['startdate']);
            $start_display = $startDate_obj->format('Y-m-d');
        } else {
            $start_display = null;
        }
        if (isset($_POST['enddate']) && !empty($_POST['enddate'])) {
            $endDate_obj = DateTime::createFromFormat('d-m-Y', $_POST['enddate']);
            $stop_display = $endDate_obj->format('Y-m-d');
        } else {
            $stop_display = null;
        }

        if (!empty($_POST['id'])) {
            $id = intval($_POST['id']);
            Database::get()->query("UPDATE announcement SET content = ?s, title = ?s, `date` = " . DBHelper::timeAfter() . ", start_display = ?t, stop_display = ?t  WHERE id = ?d", $newContent, $antitle, $start_display, $stop_display, $id);
            $log_type = LOG_MODIFY;
            $message = "<div class='alert alert-success'>$langAnnModify</div>";

            if (isset($_POST['tags'])) {
                $tagsArray = explode(',', $_POST['tags']);
                $moduleTag = new ModuleElement($id);
                $moduleTag->syncTags($tagsArray);
            }
        } else { // add new announcement
            $orderMax = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM announcement
                                                   WHERE course_id = ?d", $course_id)->maxorder;
            $order = $orderMax + 1;
            // insert
            $id = Database::get()->query("INSERT INTO announcement
                                         SET content = ?s,
                                             title = ?s, `date` = " . DBHelper::timeAfter() . ",
                                             course_id = ?d, `order` = ?d,
                                             visible = 1,
                                             start_display = ?t,
                                             stop_display = ?t", $newContent, $antitle, $course_id, $order, $start_display, $stop_display)->lastInsertID;
            $log_type = LOG_INSERT;

            if (isset($_POST['tags'])) {
                $tagsArray = explode(',', $_POST['tags']);
                $moduleTag = new ModuleElement($id);
                $moduleTag->attachTags($tagsArray);
            }
        }
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_ANNOUNCEMENT, $id);
        $txt_content = ellipsize_html(canonicalize_whitespace(strip_tags($_POST['newContent'])), 50, '+');
        Log::record($course_id, MODULE_ID_ANNOUNCE, $log_type, array('id' => $id,
                                                                     'email' => $send_mail,
                                                                     'title' => $_POST['antitle'],
                                                                     'content' => $txt_content));

        // send email
        if ($send_mail) {
            $title = course_id_to_title($course_id);
            $recipients_emaillist = "";
            if ($_POST['recipients'][0] == -1) { // all users
                $cu = Database::get()->queryArray("SELECT cu.user_id FROM course_user cu
                                                        JOIN user u ON cu.user_id=u.id
                                                    WHERE cu.course_id = ?d
                                                    AND u.email <> ''
                                                    AND u.email IS NOT NULL", $course_id);
                foreach($cu as $re) {
                    $recipients_emaillist .= (empty($recipients_emaillist))? "'$re->user_id'":",'$re->user_id'";
                }
            } else { // selected users
                foreach($_POST['recipients'] as $re) {
                    $recipients_emaillist .= (empty($recipients_emaillist))? "'$re'":",'$re'";
                }
            }

            $emailHeaderContent = "
                <!-- Header Section -->
                <div id='mail-header'>
                    <br>
                    <div>
                        <div id='header-title'>$langAnnHasPublished <a href='{$urlServer}courses/$course_code/'>" . q($title) . "</a>.</div>
                        <ul id='forum-category'>
                            <li><span><b>$langSender:</b></span> <span class='left-space'>" . q($_SESSION['givenname']) . " " . q($_SESSION['surname']) . "</span></li>
                            <li><span><b>$langdate:</b></span> <span class='left-space'>$datetime</span></li>
                        </ul>
                    </div>
                </div>";

            $emailBodyContent = "
                <!-- Body Section -->
                <div id='mail-body'>
                    <br>
                    <div><b>$langSubject:</b> <span class='left-space'>".q($_POST['antitle'])."</span></div><br>
                    <div><b>$langMailBody</b></div>
                    <div id='mail-body-inner'>
                        $newContent
                    </div>
                </div>";

            $emailFooterContent = "
                <!-- Footer Section -->
                <div id='mail-footer'>
                    <br>
                    <div>
                        <small>" . sprintf($langLinkUnsubscribe, q($title)) ." <a href='${urlServer}main/profile/emailunsubscribe.php?cid=$course_id'>$langHere</a></small>
                    </div>
                </div>";

            $emailContent = $emailHeaderContent.$emailBodyContent.$emailFooterContent;

            $emailSubject = "$professorMessage ($public_code - " . q($title) . " - $langAnnouncement)";
            // select students email list
            $countEmail = 0;
            $invalid = 0;
            $recipients = array();
            $emailBody = html2text($emailContent);
            $general_to = 'Members of course ' . $course_code;
            Database::get()->queryFunc("SELECT course_user.user_id as id, user.email as email
                                                   FROM course_user, user
                                                   WHERE course_id = ?d AND user.id IN ($recipients_emaillist) AND
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
            Session::Messages("$langAnnAdd $langEmailSent<br>$messageInvalid", 'alert-success');
        }
        else {
            Session::Messages($langAnnAdd, 'alert-success');
        }
        redirect_to_home_page("modules/announcements/index.php?course=$course_code");
    } // end of if $submit

    /* display form */
    if (isset($_GET['addAnnounce']) or isset($_GET['modify'])) {

        if (isset($_GET['modify'])) {
            $langAdd = $pageName = $langModifAnn;
        } else {
            $pageName = $langAddAnn;
        }
        $navigation[] = array("url" => "index.php?course=$course_code", "name" => $langAnnouncements);

        if (!isset($AnnouncementToModify)) $AnnouncementToModify = "";
        if (!isset($contentToModify)) $contentToModify = "";
        if (!isset($titleToModify)) $titleToModify = "";
        if (!isset($showFrom)) $showFrom = "";
        if (!isset($showUntil)) $showUntil = "";

        load_js('bootstrap-datepicker');
        $head_content .= "
            <script type='text/javascript'>
            $(function() {
                $('#startdate').datepicker({
                    format: 'dd-mm-yyyy',
                    language: '$language',
                    autoclose: true
                });
                $('#enddate').datepicker({
                    format: 'dd-mm-yyyy',
                    language: '$language',
                    autoclose: true
                });
            });"
        . "</script>";
    $tool_content .= action_bar(array(
                array('title' => $langBack,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                      'icon' => 'fa-reply',
                      'level' => 'primary-label')));
    $tool_content .= "<div class='form-wrapper'>";
    $tool_content .= "<form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=".$course_code."' onsubmit=\"return checkrequired(this, 'antitle');\">
        <fieldset>
        <div class='form-group'>
            <label for='AnnTitle' class='col-sm-2 control-label'>$langAnnTitle:</label>
            <div class='col-sm-10'>
                <input class='form-control' type='text' name='antitle' value='$titleToModify' size='50' />
            </div>
        </div>
        <div class='form-group'>
          <label for='AnnBody' class='col-sm-2 control-label'>$langAnnBody:</label>
            <div class='col-sm-10'>".rich_text_editor('newContent', 4, 20, $contentToModify)."</div>
        </div>
        <div class='form-group'><label for='Email' class='col-sm-offset-2 col-sm-12 control-panel'>$langEmailOption:</label></div>
        <div class='form-group'>
            <div class='col-sm-offset-2 col-sm-10'>
                <select class='form-control' name='recipients[]' multiple class='form-control' id='select-recipients'>";
                $course_users = Database::get()->queryArray("SELECT cu.user_id, CONCAT(u.surname, ' ', u.givenname) name, u.email
                                                    FROM course_user cu
                                                        JOIN user u ON cu.user_id=u.id
                                                    WHERE cu.course_id = ?d
                                                    AND u.email<>''
                                                    AND u.email IS NOT NULL ORDER BY u.surname, u.givenname", $course_id);

                $tool_content .= "<option value='-1' selected><h2>$langAllUsers</h2></option>";
                foreach($course_users as $cu) {
                   $tool_content .= "<option value='" . q($cu->user_id) . "'>" . q($cu->name) . " (" . q($cu->email) . ")</option>";
                }
                $tool_content .= "</select>
                <a href='#' id='selectAll'>$langJQCheckAll</a> | <a href='#' id='removeAll'>$langJQUncheckAll</a>
            </div>
        </div>
        " . Tag::tagInput($AnnouncementToModify) . "
        <div class='form-group'><label for='Email' class='col-sm-offset-2 col-sm-12 control-panel'>$langAnnouncementActivePeriod:</label></div>

        <div class='form-group'>
            <label for='From' class='col-sm-2 control-label'>$langFrom:</label>
            <div class='col-sm-10'><input class='form-control' type='text' name='startdate' id='startdate' value='$showFrom'></div>
        </div>
        <div class='form-group'>
            <label for='From' class='col-sm-2 control-label'>$langUntil:</label>
            <div class='col-sm-10'><input class='form-control' type='text' name='enddate' id='enddate' value='$showUntil'></div>
        </div>
        <div class='form-group'>
        <div class='col-sm-offset-2 col-sm-10'>".form_buttons(array(
                array(
                    'text' => $langSubmit,
                    'name' => 'submitAnnouncement',
                    'value'=> $langAdd
                ),
                array(
                    'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                )
            ))."</div>
        <input type='hidden' name='id' value='$AnnouncementToModify'>
        </div>
        </fieldset>
        </form>
        </div>";
    }
} // end: teacher only

if ($uid and $status != USER_GUEST and !get_user_email_notification($uid, $course_id)) {
    $tool_content .= "<div class='alert alert-warning'>$langNoUserEmailNotification
        (<a href='{$urlServer}main/profile/emailunsubscribe.php?cid=$course_id'>$langModify</a>)</div>";
}
if (isset($_GET['an_id'])) {
    $pageName = $row->title;
    $tool_content .= action_bar(array(
        array('title' => $langModify,
              'url' => $_SERVER['SCRIPT_NAME'] . "?course=" . $course_code . "&amp;modify=$row->id",
              'icon' => 'fa-edit',
              'level' => 'primary-label',
               'show' => $is_editor),
        array('title' => $langBack,
              'url' => $_SERVER['SCRIPT_NAME'] . "?course=" . $course_code,
              'icon' => 'fa-reply',
              'level' => 'primary-label'),
        array('title' => $langDelete,
              'url' => $_SERVER['SCRIPT_NAME'] . "?course=" .$course_code . "&amp;delete=$row->id",
              'icon' => 'fa-times',
              'confirm' => $langSureToDelAnnounce,
              'show' => $is_editor)));
    } elseif (!isset($_GET['modify']) && !isset($_GET['addAnnounce'])) {
        $tool_content .= action_bar(array(
            array('title' => $langAddAnn,
                  'url' => $_SERVER['SCRIPT_NAME'] . "?course=" .$course_code . "&amp;addAnnounce=1",
                  'icon' => 'fa-plus-circle',
                  'level' => 'primary-label',
                  'button-class' => 'btn-success',
                  'show' => $is_editor)));
    }
    /* display announcements */
    if (isset($_GET['an_id'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langAnnouncements);
        $tool_content .= "<div class='panel'>";
        $tool_content .= "<div class='panel-body'>";
        $tool_content .= "<div class='not_visible margin-bottom-thin'>$langDate: $row->date</div>";
        $tool_content .= $row->content;

        $moduleTag = new ModuleElement($row->id);
        $tags_list = $moduleTag->showTags();
        if ($tags_list) $tool_content .= "<div>$langTags: $tags_list</div>";
        $tool_content .= "
                    </div>
                </div>";
    }
    if (!isset($_GET['addAnnounce']) && !isset($_GET['modify']) && !isset($_GET['an_id'])) {
        $tool_content .= "<table id='ann_table{$course_id}' class='table-default'>";
        $tool_content .= "<thead>";
        $tool_content .= "<tr class='list-header'><th>$langAnnouncement</th><th>$langDate</th><th>$langNewBBBSessionStatus</th>";

        if ($is_editor) {
            $tool_content .= "<th class='text-center'><i class='fa fa-cogs'></i></th>";
        }
        $tool_content .= "</tr></thead><tbody></tbody></table>";
    }


add_units_navigation(TRUE);
load_js('select2');
$head_content .= "<script type='text/javascript'>
    $(document).ready(function () {
        $('#select-recipients').select2();
        $('#selectAll').click(function(e) {
            e.preventDefault();
            var stringVal = [];
            $('#select-recipients').find('option').each(function(){
                stringVal.push($(this).val());
            });
            $('#select-recipients').val(stringVal).trigger('change');
        });
        $('#removeAll').click(function(e) {
            e.preventDefault();
            var stringVal = [];
            $('#select-recipients').val(stringVal).trigger('change');
        });
    });
    </script>";

draw($tool_content, 2, null, $head_content);
