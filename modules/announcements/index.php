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


$require_current_course = true;
$require_help = true;
$helpTopic = 'announcements';
$guest_allowed = true;

include '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';
require_once 'include/sendMail.inc.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/log.class.php';
require_once 'modules/search/indexer.class.php';
require_once 'modules/tags/moduleElement.class.php';
require_once 'include/action.php';

$action = new action();
$action->record(MODULE_ID_ANNOUNCE);

define_rss_link();

if ($is_editor && isset($_POST['pin_announce'])) {
    if (isset($_GET['pin']) && ($_GET['pin'] == 1)) {
        $top_order = Database::get()->querySingle("SELECT MAX(`order`) as max from announcement WHERE course_id = ?d", $course_id)->max + 1;
        Database::get()->query("UPDATE announcement SET `order` = ?d  where id = ?d and course_id = ?d", $top_order, $_GET['pin_an_id'], $course_id);
    } elseif (isset($_GET['pin']) && ($_GET['pin'] == 0)) {
        Database::get()->query("UPDATE announcement SET `order` = 0  where id = ?d and course_id = ?d", $_GET['pin_an_id'], $course_id);
    }
    exit();
}

// Identifying ajax request
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

    $student_sql = $is_editor? '': 'AND visible = 1 AND (start_display <= NOW() OR start_display IS NULL) AND (stop_display >= NOW() OR stop_display IS NULL)';
    $all_announc = Database::get()->querySingle("SELECT COUNT(*) AS total FROM announcement WHERE course_id = ?d $student_sql", $course_id);
    $filtered_announc = Database::get()->querySingle("SELECT COUNT(*) AS total FROM announcement WHERE course_id = ?d AND title LIKE ?s $student_sql", $course_id, $keyword);
    if ($limit>0) {
        $extra_sql = 'LIMIT ?d, ?d';
        $extra_terms = array($offset, $limit);
    } else {
        $extra_sql = '';
        $extra_terms = array();
    }
    $result = Database::get()->queryArray("SELECT * FROM announcement WHERE course_id = ?d AND title LIKE ?s $student_sql ORDER BY `order` DESC , `date` DESC  $extra_sql", $course_id, $keyword, $extra_terms);

    $data['iTotalRecords'] = $all_announc->total;
    $data['iTotalDisplayRecords'] = $filtered_announc->total;
    $data['aaData'] = array();
    if ($is_editor) {
        $iterator = 1;
        $now = date("Y-m-d H:i:s");
        $pinned_greater = Database::get()->querySingle("SELECT MAX(`order`) AS max_order FROM announcement WHERE course_id = ?d", $course_id)->max_order;
        foreach ($result as $myrow) {

            $to_top = "";

            //checking visible status
            if ($myrow->visible == '0') {
                $visible = 1;
                $status_icon_list = "<li data-toggle='tooltip' data-placement='left' title='$langAnnouncementIsNotVis'><span class='fa fa-eye-slash'></span> $langInvisible</li>";
                $vis_class = 'not_visible';
            } else {
                $visible = 0;
                if (isset($myrow->start_display)) {
                    if (isset($myrow->stop_display) && $myrow->stop_display < $now) {
                        $vis_class = 'not_visible';
                        $status_icon_list = "<li class='text-danger'  data-toggle='tooltip' data-placement='left' title='$langAnnouncementWillNotBeVis$myrow->stop_display'><span class='fa fa-clock-o'></span> $langHasExpired</li>";
                    } elseif ($myrow->start_display > $now) {
                        $vis_class = 'not_visible';
                        $status_icon_list = "<li class='text-success'  data-toggle='tooltip' data-placement='left' title='$langAnnouncementWillBeVis$myrow->start_display'><span class='fa fa-clock-o'></span> $langAdminWaiting</li>";
                    } else {
                        $status_icon_list = "<li data-toggle='tooltip' data-placement='left' title='$langAnnouncementIsVis'><span class='fa fa-eye'></span> $langVisible</li>";
                        $vis_class = 'visible';
                    }
                }else{
                    $status_icon_list = "<li data-toggle='tooltip' data-placement='left' title='$langAnnouncementIsVis'><span class='fa fa-eye'></span> $langVisible</li>";
                    $vis_class = 'visible';
                }
            }

            //setting datables column data
            if ($myrow->order != 0) {
                $pinned_class = "text-danger";
                $pinned = 0;
                $tooltip = "data-toggle='tooltip' data-placement='top' title='$langAdminPinnedOff'";
                if ($myrow->order != $pinned_greater) {
                    $to_top = "<a class='reorder' href='$_SERVER[SCRIPT_NAME]?course=$course_code&pin_an_id=$myrow->id&pin=1'><span class='fa fa-arrow-up  pull-right' data-toggle='tooltip' data-placement='top' title='$langAdminPinnedToTop'></span></a>";
                }
            } elseif ($myrow->order == 0) {
                $pinned_class = "not_visible";
                $pinned = 1;
                $tooltip = "data-toggle='tooltip' data-placement='top' title='$langAdminPinnedOn'";
            }

            $data['aaData'][] = array(
                'DT_RowId' => $myrow->id,
                'DT_RowClass' => $vis_class,
                '0' => "<div class='table_td'>
                        <div class='table_td_header clearfix'>
                            <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&an_id=$myrow->id'>".standard_text_escape($myrow->title)."</a>
                            <a class='reorder' href='$_SERVER[SCRIPT_NAME]?course=$course_code&pin_an_id=$myrow->id&pin=$pinned'>
                                <span class='fa fa-thumb-tack $pinned_class pull-right' $tooltip></span>
                            </a>
                            $to_top
                        </div>
                        <div class='table_td_body' data-id='$myrow->id'>".standard_text_escape($myrow->content)."</div>
                        </div>",
                //'0' => '<a href="'.$_SERVER['SCRIPT_NAME'].'?course='.$course_code.'&an_id='.$myrow->id.'">'.q($myrow->title).'</a>',
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
                        'icon-extra' => "data-id='$myrow->id'")
                    )));
            $iterator++;
        }
    } else {
        foreach ($result as $myrow) {

            if ($myrow->order != 0) {
                $pinned = "<span class='fa fa-thumb-tack pull-right text-danger' data-toggle='tooltip' data-placement='top' title='$langAdminPinned'></span>";
            } else {
                $pinned = "";
            }

            $data['aaData'][] = array(
                '0' => "<div class='table_td'>
                        <div class='table_td_header clearfix'>
                            <a href='".$_SERVER['SCRIPT_NAME']."?course=".$course_code."&an_id=".$myrow->id."'>".standard_text_escape($myrow->title)."</a>
                            $pinned
                        </div>
                        <div class='table_td_body' data-id='$myrow->id'>".standard_text_escape($myrow->content)."</div>
                        </div>",
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
                    tooltip_init();
                    $('.table_td_body').each(function() {
                $(this).trunk8({
                    lines: '3',
                    fill: '&hellip;<div class=\"clearfix\"></div><a style=\"float:right;\" href=\"$_SERVER[SCRIPT_NAME]?course={$course_code}&an_id='+ $(this).data('id')+'\">$langMore</div>'
                })
            });
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

            $(document).on( 'click', '.reorder', function(e) {
                e.preventDefault();
                var link = $(this).attr('href');
                var tr_affected = $(this).closest('tr');

                $.ajax({
                    type: 'POST',
                    url: link,
                    data: {
                        pin_announce: 1
                    },
                    beforeSend: function(){
                        console.log(tr_affected);
                        tr_affected.css('backgroundColor','rgba(100,100,100,0.3)');
                    },
                    success: function(data){
                        oTable.ajax.reload(null, false);
                    }
                });
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
    $sql = 'SELECT * FROM announcement WHERE course_id = ?d AND id = ?d';
    if (!$is_editor) {
        $sql .= ' AND visible = 1 AND
            (start_display <= NOW() OR start_display IS NULL) AND
            (stop_display >= NOW() OR stop_display IS NULL)';
    }
    $row = Database::get()->querySingle($sql, $course_id, $_GET['an_id']);
    if (!$row) {
        redirect_to_home_page('modules/announcements/');
    }
}
if ($is_editor) {
    $head_content .= '<script type="text/javascript">var langEmptyGroupName = "' . js_escape($langEmptyAnTitle) . '";</script>';
    /* up and down commands */
    if (isset($_GET['down'])) {
        $thisAnnouncementId = $_GET['down'];
        $sortDirection = 'DESC';
    }
    if (isset($_GET['up'])) {
        $thisAnnouncementId = $_GET['up'];
        $sortDirection = 'ASC';
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
            $titleToModify = Session::has('antitle') ? Session::get('antitle') : q($announce->title);
            if ($announce->start_display) {
                $startDate_obj = DateTime::createFromFormat('Y-m-d H:i:s', $announce->start_display);
                $showFrom = q($startDate_obj->format('d-m-Y H:i'));
            }
            if ($announce->stop_display) {
                $endDate_obj = DateTime::createFromFormat('Y-m-d H:i:s', $announce->stop_display);
                $showUntil = q($endDate_obj->format('d-m-Y H:i'));
            }
        }
    }

    /* submit */
    if (isset($_POST['submitAnnouncement'])) { // modify announcement
        $v = new Valitron\Validator($_POST);
        $v->rule('required', array('antitle'));
        $v->labels(array('antitle' => "$langTheField $langAnnTitle"));
        if (isset($_POST['startdate_active'])) {
            $v->rule('required', array('startdate'));
            $v->labels(array('startdate' => "$langTheField $langStartDate"));
        }
        if (isset($_POST['enddate_active'])) {
            $v->rule('required', array('enddate'));
            $v->labels(array('enddate' => "$langTheField $langEndDate"));
        }
        if($v->validate()) {
            if ($language == 'el') {
                $datetime = claro_format_locale_date($dateTimeFormatShort);
            } else {
                $datetime = date('l jS \of F Y h:i A');
            }
            if (isset($_POST['show_public'])) {
                $is_visible = 1;
            } else {
                $is_visible = 0;
            }

            $antitle = $_POST['antitle'];
            $newContent = purify($_POST['newContent']);
            $send_mail = isset($_POST['recipients']) && (count($_POST['recipients'])>0);
            if (isset($_POST['startdate_active']) && isset($_POST['startdate']) && !empty($_POST['startdate'])) {
                $startDate_obj = DateTime::createFromFormat('d-m-Y H:i', $_POST['startdate']);
                $start_display = $startDate_obj->format('Y-m-d H:i:s');
            } else {
                $start_display = null;
            }
            if (isset($_POST['enddate_active']) && isset($_POST['enddate']) && !empty($_POST['enddate'])) {
                $endDate_obj = DateTime::createFromFormat('d-m-Y H:i', $_POST['enddate']);
                $stop_display = $endDate_obj->format('Y-m-d H:i:s');
            } else {
                $stop_display = null;
            }

            if (!empty($_POST['id'])) {
                $id = intval($_POST['id']);
                Database::get()->query("UPDATE announcement
                    SET content = ?s,
                        title = ?s,
                        `date` = " . DBHelper::timeAfter() . ",
                        start_display = ?t,
                        stop_display = ?t,
                        visible = ?d
                    WHERE id = ?d",
                    $newContent, $antitle, $start_display, $stop_display, $is_visible, $id);
                $log_type = LOG_MODIFY;
                $message = "<div class='alert alert-success'>$langAnnModify</div>";

                if (isset($_POST['tags'])) {
                    $tagsArray = explode(',', $_POST['tags']);
                    $moduleTag = new ModuleElement($id);
                    $moduleTag->syncTags($tagsArray);
                }
            } else { // add new announcement

                // insert
                $id = Database::get()->query("INSERT INTO announcement
                                             SET content = ?s,
                                                 title = ?s, `date` = " . DBHelper::timeAfter() . ",
                                                 course_id = ?d, `order` = 0,
                                                 start_display = ?t,
                                                 stop_display = ?t,
                                                 visible = ?d", $newContent, $antitle, $course_id, $start_display, $stop_display, $is_visible)->lastInsertID;
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
                use (&$countEmail, &$recipients, &$invalid, $course_id, $general_to, $emailSubject, $emailBody, $emailContent) {
                    $countEmail++;
                    $emailTo = $person->email;
                    $user_id = $person->id;
                    // check email syntax validity
                    if (!valid_email($emailTo)) {
                        $invalid++;
                    } elseif (get_user_email_notification($user_id, $course_id)) {
                        // checks if user is notified by email
                        array_push($recipients, $emailTo);
                    }
                    // send mail message per 50 recipients
                    if (count($recipients) >= 50) {
                        send_mail_multipart("$_SESSION[givenname] $_SESSION[surname]", $_SESSION['email'], $general_to, $recipients, $emailSubject, $emailBody, $emailContent);
                        $recipients = array();
                    }
                }, $course_id);
                if (count($recipients) > 0) {
                    send_mail_multipart("$_SESSION[givenname] $_SESSION[surname]", $_SESSION['email'], $general_to, $recipients, $emailSubject, $emailBody, $emailContent);
                }
                Session::Messages("$langAnnAddWithEmail $countEmail $langRegUser", 'alert-success');
                if ($invalid > 0) { // info about invalid emails (if exist)
                    Session::Messages("$langInvalidMail $invalid", 'alert-warning');
                }
            } else {
                Session::Messages($langAnnAdd, 'alert-success');
            }
            redirect_to_home_page("modules/announcements/index.php?course=$course_code");
        } else {
            Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
            redirect_to_home_page("modules/announcements/index.php?course=$course_code&addAnnounce=1");
        }
    } // end of if $submit

    /* display form */
    if (isset($_GET['addAnnounce']) or isset($_GET['modify'])) {
        $require_editor = true;

        if (isset($_GET['modify'])) {
            $langAdd = $pageName = $langModifAnn;
            $checked_public = $announce->visible? 'checked' : '';
            if (!is_null($announce->start_display)) {
                // $showFrom is set earlier
                $start_checkbox = 'checked';
                $start_text_disabled = '';
                $end_disabled = "";
                if (!is_null($announce->stop_display)) {
                    // $showUntil is set earlier
                    $end_checkbox = 'checked';
                    $end_text_disabled = '';
                } else {
                    $showUntil = '';
                    $end_checkbox = '';
                    $end_text_disabled = 'disabled';
                }
            } else {
                $start_checkbox = '';
                $start_text_disabled = 'disabled';
                $end_checkbox = '';
                $end_disabled = 'disabled';
                $end_text_disabled = 'disabled';
                $showFrom = '';
                $showUntil = '';
            }


        } else {
            $pageName = $langAddAnn;
            $checked_public = 'checked';
            $start_checkbox = Session::has('startdate_active') ? 'checked' : '';
            $end_checkbox = Session::has('enddate_active') ? 'checked' : '';
            $showFrom = Session::has('startdate') ? Session::get('startdate') : '';
            $end_disabled = Session::has('startdate_active') ? '' : 'disabled';
            $showUntil = Session::has('enddate') ? Session::get('enddate') : '';
            $titleToModify = Session::has('antitle') ? Session::get('antitle') : '';
        }
        $navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langAnnouncements);

        if (!isset($AnnouncementToModify)) $AnnouncementToModify = '';
        if (!isset($contentToModify)) $contentToModify = '';

        $antitle_error = Session::getError('antitle', "<span class='help-block'>:message</span>");
        $startdate_error = Session::getError('startdate', "<span class='help-block'>:message</span>");
        $enddate_error = Session::getError('enddate', "<span class='help-block'>:message</span>");

        load_js('bootstrap-datetimepicker');
        $head_content .= "
            <script type='text/javascript'>
            $(function() {
                $('#startdate').datetimepicker({
                    format: 'dd-mm-yyyy hh:ii',
                    pickerPosition: 'bottom-right',
                    language: '".$language."',
                    autoclose: true
                });
                $('#enddate').datetimepicker({
                    format: 'dd-mm-yyyy hh:ii',
                    pickerPosition: 'bottom-right',
                    language: '".$language."',
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
        $tool_content .= "<form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=".$course_code."'>
        <fieldset>
        <div class='form-group".($antitle_error ? " has-error" : "")."'>
            <label for='AnnTitle' class='col-sm-2 control-label'>$langAnnTitle:</label>
            <div class='col-sm-10'>
                <input class='form-control' type='text' name='antitle' value='$titleToModify' size='50' />
                <span class='help-block'>$antitle_error</span>
            </div>
        </div>
        <div class='form-group'>
          <label for='AnnBody' class='col-sm-2 control-label'>$langAnnBody:</label>
            <div class='col-sm-10'>".rich_text_editor('newContent', 4, 20, $contentToModify)."</div>
        </div>
        <div class='form-group'><label for='Email' class='col-sm-offset-2 col-sm-10 control-panel'>$langEmailOption:</label></div>
        <div class='form-group'>
            <div class='col-sm-offset-2 col-sm-10'>
                <select class='form-control' name='recipients[]' multiple id='select-recipients'>";
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
        " . eClassTag::tagInput($AnnouncementToModify) . "
        <div class='form-group'><label for='Email' class='col-sm-offset-2 col-sm-10 control-panel'>$langAnnouncementActivePeriod:</label></div>


        <div class='form-group".($startdate_error ? " has-error" : "")."'>
            <label for='startdate' class='col-sm-2 control-label'>$langStartDate :</label>
            <div class='col-sm-10'>
                <div class='input-group'>
                    <span class='input-group-addon'>
                        <input type='checkbox' name='startdate_active' $start_checkbox>
                    </span>
                    <input class='form-control' name='startdate' id='startdate' type='text' value = '$showFrom'>
                </div>
                <span class='help-block'>$startdate_error</span>
            </div>
        </div>
        <div class='form-group".($enddate_error ? " has-error" : "")."'>
            <label for='enddate' class='col-sm-2 control-label'>$langEndDate :</label>
            <div class='col-sm-10'>
                <div class='input-group'>
                    <span class='input-group-addon'>
                        <input type='checkbox' name='enddate_active' $end_checkbox $end_disabled>
                    </span>
                    <input class='form-control' name='enddate' id='enddate' type='text' value = '$showUntil'>
                </div>
                <span class='help-block'>$enddate_error</span>
            </div>
        </div>


        <div class='form-group'>
            <div class='col-sm-10 col-sm-offset-2'>
                <div class='checkbox'>
                    <label>
                        <input type='checkbox' name='show_public' $checked_public> $langViewShow
                    </label>
                </div>
            </div>
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
    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => $_SERVER['SCRIPT_NAME'] . "?course=" . $course_code,
            'icon' => 'fa-reply',
            'level' => 'primary-label')),false);
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
    $tool_content .= "<div class='row'><div class='col-xs-12'><div class='panel'>";
    $tool_content .= "<div class='panel-body'>";
    $tool_content .= "
                        <div class='single_announcement'>
                            <div class='announcement-title'>
                                ".standard_text_escape($row->title)."
                            </div>
                            <span class='announcement-date'>
                                - ".claro_format_locale_date($dateFormatLong, strtotime($row->date))." -
                            </span>
                            <div class='announcement-main'>
                                ".standard_text_escape($row->content)."
                            </div>
                        </div>";

    $moduleTag = new ModuleElement($row->id);
    $tags_list = $moduleTag->showTags();
    if ($tags_list) $tool_content .= "<hr><div>$langTags: $tags_list</div>";
    $tool_content .= "
                    </div>
                </div></div></div>";
}
if (!isset($_GET['addAnnounce']) && !isset($_GET['modify']) && !isset($_GET['an_id'])) {
    $tool_content .= "<table id='ann_table{$course_id}' class='table-default'>";

    if (!$is_editor) {
        $tool_content .= "<thead>";
        $tool_content .= "<tr class='list-header'><th>$langAnnouncement</th><th>$langDate</th>";
    }

    if ($is_editor) {
        $tool_content .= "<thead>";
        $tool_content .= "<tr class='list-header'><th>$langAnnouncement</th>";
        $tool_content .= "<th>$langDate</th><th>$langNewBBBSessionStatus</th><th class='text-center'><i class='fa fa-cogs'></i></th>";
    }
    $tool_content .= "</tr></thead><tbody></tbody></table>";
}

add_units_navigation(TRUE);
load_js('select2');
load_js('trunk8');
$head_content .= "<script type='text/javascript'>
    $(document).ready(function () {
        $('input[name=startdate_active]').prop('checked') ? $('input[name=startdate_active]').parents('.input-group').children('input').prop('disabled', false) : $('input[type=checkbox]').eq(0).parents('.input-group').children('input').prop('disabled', true);
        $('input[name=enddate_active]').prop('checked') ? $('input[name=enddate_active]').parents('.input-group').children('input').prop('disabled', false) : $('input[name=enddate_active]').parents('.input-group').children('input').prop('disabled', true);

        $('input[name=startdate_active]').on('click', function() {
            if ($('input[name=startdate_active]').prop('checked')) {
                $('input[name=enddate_active]').prop('disabled', false);
            } else {
                $('input[name=enddate_active]').prop('disabled', true);
                $('input[name=enddate_active]').prop('checked', false);
                $('input[name=enddate_active]').parents('.input-group').children('input').prop('disabled', true);
            }
        });

        $('.input-group-addon input[type=checkbox]').on('click', function(){
        var prop = $(this).parents('.input-group').children('input').prop('disabled');
            if(prop){
                $(this).parents('.input-group').children('input').prop('disabled', false);
            } else {
                $(this).parents('.input-group').children('input').prop('disabled', true);
            }
        });

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
