<?php
/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2016  Greek Universities Network - GUnet
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
$public_code = course_id_to_public_code($course_id);
$toolName = $langAnnouncements;

define_rss_link();

// STICKY ANNOUNCEMENT
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
                $status_icon_list = "<li data-toggle='tooltip' data-placement='left' title='$langAnnouncementIsNotVis'><span class='fa fa-eye-slash'></span> $langAnnouncementIsNotVis</li>";
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
                        $status_icon_list = "<li data-toggle='tooltip' data-placement='left' title='$langAnnouncementIsVis'><span class='fa fa-eye'></span> $langAnnouncementIsVis</li>";
                        $vis_class = 'visible';
                    }
                }else{
                    $status_icon_list = "<li data-toggle='tooltip' data-placement='left' title='$langAnnouncementIsVis'><span class='fa fa-eye'></span> $langAnnouncementIsVis</li>";
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


ModalBoxHelper::loadModalBox();
add_units_navigation(TRUE);

load_js('tools.js');

if ($uid and $status != USER_GUEST and !get_user_email_notification($uid, $course_id)) {
    $tool_content .= "<div class='alert alert-warning'>$langNoUserEmailNotification
        (<a href='{$urlServer}main/profile/emailunsubscribe.php?cid=$course_id'>$langModify</a>)</div>";
}

// INDEX
if (!isset($_GET['addAnnounce']) && !isset($_GET['modify']) && !isset($_GET['an_id'])) {

    $data['action_bar'] = action_bar(
        [
            ['title' => $langAddAnn,
                'url' => $_SERVER['SCRIPT_NAME'] . "?course=" .$course_code . "&amp;addAnnounce=1",
                'icon' => 'fa-plus-circle',
                'level' => 'primary-label',
                'button-class' => 'btn-success',
                'show' => $is_editor
            ]
        ]);

    view('modules.announcements.index', $data);
}

// SHOW
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

    $data['action_bar'] = action_bar(
        [
            ['title' => $langBack,
                'url' => $_SERVER['SCRIPT_NAME'] . "?course=" . $course_code,
                'icon' => 'fa-reply',
                'level' => 'primary-label'
            ]
        ],false);

    $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langAnnouncements);

    $data['title'] = standard_text_escape($row->title);
    $data['date'] = claro_format_locale_date($dateFormatLong, strtotime($row->date));
    $data['content'] = standard_text_escape($row->content);

    $moduleTag = new ModuleElement($row->id);
    $data['tags_list'] = $moduleTag->showTags();

    view('modules.announcements.show', $data);
}

// CREATE & EDIT
if (isset($_GET['addAnnounce']) or isset($_GET['modify'])) {
    if ($is_editor) {
        $head_content .= "<script type='text/javascript'>
        $(document).ready(function () {

            var langEmptyGroupName = \"' . $langEmptyAnTitle . '\";

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
        $require_editor = true;

        if (isset($_GET['modify'])) {

            $modify = intval($_GET['modify']);
            $announce = Database::get()->querySingle("SELECT * FROM announcement WHERE id=?d", $modify);
            if ($announce) {
                $AnnouncementToModify = $announce->id;
                $contentToModify = $announce->content;
                $data['titleToModify'] = Session::has('antitle') ? Session::get('antitle') : q($announce->title);
                if ($announce->start_display) {
                    $startDate_obj = DateTime::createFromFormat('Y-m-d H:i:s', $announce->start_display);
                    $data['showFrom'] = q($startDate_obj->format('d-m-Y H:i'));
                }
                if ($announce->stop_display) {
                    $endDate_obj = DateTime::createFromFormat('Y-m-d H:i:s', $announce->stop_display);
                    $data['showUntil'] = q($endDate_obj->format('d-m-Y H:i'));
                }
            }

            $langAdd = $pageName = $langModifAnn;
            $data['checked_public'] = $announce->visible ? 'checked' : '';
            if (!is_null($announce->start_display)) {
                // $showFrom is set earlier
                $data['start_checkbox'] = 'checked';
                $data['start_text_disabled'] = '';
                $data['end_disabled'] = "";
                if (!is_null($announce->stop_display)) {
                    // $data['showUntil'] is set earlier
                    $data['end_checkbox'] = 'checked';
                    $data['end_text_disabled'] = '';
                } else {
                    $data['showUntil'] = '';
                    $data['end_checkbox'] = '';
                    $end_text_disabled = 'disabled';
                }
            } else {
                $data['start_checkbox'] = '';
                $start_text_disabled = 'disabled';
                $data['end_checkbox'] = '';
                $data['end_disabled'] = 'disabled';
                $end_text_disabled = 'disabled';
                $data['showFrom'] = '';
                $data['showUntil'] = '';
            }


        } else {
            $pageName = $langAddAnn;
            $data['checked_public'] = 'checked';
            $data['start_checkbox'] = Session::has('startdate_active') ? 'checked' : '';
            $data['end_checkbox'] = Session::has('enddate_active') ? 'checked' : '';
            $data['showFrom'] = Session::has('startdate') ? Session::get('startdate') : '';
            $data['end_disabled'] = Session::has('startdate_active') ? '' : 'disabled';
            $data['showUntil'] = Session::has('enddate') ? Session::get('enddate') : '';
            $data['titleToModify'] = Session::has('antitle') ? Session::get('antitle') : '';
        }
        $navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langAnnouncements);

        if (!isset($AnnouncementToModify)) $AnnouncementToModify = '';
        if (!isset($contentToModify)) $contentToModify = '';

        $antitle_error = Session::getError('antitle', "<span class='help-block'>:message</span>");
        $data['startdate_error'] = Session::getError('startdate', "<span class='help-block'>:message</span>");
        $data['enddate_error'] = Session::getError('enddate', "<span class='help-block'>:message</span>");

        load_js('select2');
        load_js('bootstrap-datetimepicker');
        $head_content .= "
            <script type='text/javascript'>
            $(function() {
                $('#startdate').datetimepicker({
                    format: 'dd-mm-yyyy hh:ii',
                    pickerPosition: 'bottom-right',
                    language: '" . $language . "',
                    autoclose: true
                });
                $('#enddate').datetimepicker({
                    format: 'dd-mm-yyyy hh:ii',
                    pickerPosition: 'bottom-right',
                    language: '" . $language . "',
                    autoclose: true
                });
            });"
            . "</script>";



        $data['action_bar'] = action_bar(
            [
                ['title' => $langBack,
                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                'icon' => 'fa-reply',
                'level' => 'primary-label'
                ]
            ]);

        $data['antitle_error'] = ($antitle_error ? " has-error" : "");
        $data['contentToModify'] = rich_text_editor('newContent', 4, 20, $contentToModify);

        $data['course_users'] = Database::get()->queryArray("SELECT cu.user_id, CONCAT(u.surname, ' ', u.givenname) name, u.email
                                                    FROM course_user cu
                                                        JOIN user u ON cu.user_id=u.id
                                                    WHERE cu.course_id = ?d
                                                    AND u.email<>''
                                                    AND u.email IS NOT NULL ORDER BY u.surname, u.givenname", $course_id);

        $data['tags'] = eClassTag::tagInput($AnnouncementToModify);
        $data['startdate_error'] = $data['startdate_error'] ? " has-error" : "";
        $data['enddate_error'] = $data['enddate_error'] ? " has-error" : "";


    }

    view('modules.announcements.create_edit', $data);
}

// STORE & UPDATE
if ($is_editor && isset($_POST['submitAnnouncement'])) {
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
            use (&$countEmail, &$recipients, &$invalid, $course_id, $general_to, $emailSubject, $emailBody, $emailContent, $charset) {
                $countEmail++;
                $emailTo = $person->email;
                $user_id = $person->id;
                // check email syntax validity
                if (!Swift_Validate::email($emailTo)) {
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
            redirect_to_home_page("modules/announcements/index.php?course=$course_code");
        }
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page("modules/announcements/index.php?course=$course_code&addAnnounce=1");
    }
}
