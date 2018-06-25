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
$toolName = $langAnnouncements;

define_rss_link();

if ($is_editor) {
    // Pin sticky announcement
    if (isset($_POST['pin_announce'])) {
        if (isset($_GET['pin']) && ($_GET['pin'] == 1)) {
            $top_order = Database::get()->querySingle("SELECT MAX(`order`) as max from announcement WHERE course_id = ?d", $course_id)->max + 1;
            Database::get()->query("UPDATE announcement SET `order` = ?d  where id = ?d and course_id = ?d", $top_order, $_GET['pin_an_id'], $course_id);
        } elseif (isset($_GET['pin']) && ($_GET['pin'] == 0)) {
            Database::get()->query("UPDATE announcement SET `order` = 0  where id = ?d and course_id = ?d", $_GET['pin_an_id'], $course_id);
        }
        exit();
    }

    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'delete') {
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
        } elseif ($_POST['action'] == 'visible') {
            /* modify visibility */
            $row_id = intval($_POST['value']);
            $visible = intval($_POST['visible']) ? 1 : 0;
            Database::get()->query("UPDATE announcement SET visible = ?d WHERE id = ?d", $visible, $row_id);
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_ANNOUNCEMENT, $row_id);
            exit();
        }
    }
}

// AJAX request for DataTables
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

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

            // checking visible status
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
                } else {
                    $status_icon_list = "<li data-toggle='tooltip' data-placement='left' title='$langAnnouncementIsVis'><span class='fa fa-eye'></span> $langAnnouncementIsVis</li>";
                    $vis_class = 'visible';
                }
            }

            // setting datables column data
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
                        'url' => $urlAppend . "modules/announcements/edit.php?course=$course_code&amp;modify=$myrow->id"),
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

if (isset($_GET['an_id'])) {
    // Show specific announcement
    $sql = 'SELECT * FROM announcement WHERE course_id = ?d AND id = ?d';
    if (!$is_editor) {
        $sql .= ' AND visible = 1 AND
            (start_display <= NOW() OR start_display IS NULL) AND
            (stop_display >= NOW() OR stop_display IS NULL)';
    }
    $row = Database::get()->querySingle($sql, $course_id, $_GET['an_id']);
    if (!$row) {
        redirect_to_home_page('modules/announcements/?course=' . $course_code);
    }

    $data['action_bar'] = action_bar([
            [ 'title' => $langBack,
              'url' => $_SERVER['SCRIPT_NAME'] . "?course=" . $course_code,
              'icon' => 'fa-reply',
              'level' => 'primary-label' ]
        ], false);

    $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langAnnouncements);

    $data['title'] = standard_text_escape($row->title);
    $data['date'] = claro_format_locale_date($dateFormatLong, strtotime($row->date));
    $data['content'] = standard_text_escape($row->content);

    $moduleTag = new ModuleElement($row->id);
    $data['tags_list'] = $moduleTag->showTags();

    view('modules.announcements.show', $data);

} else {
    // Show index
    $data['action_bar'] = action_bar([
            [ 'title' => $langAddAnn,
              'url' => $urlAppend . "modules/announcements/new.php?course=$course_code",
              'icon' => 'fa-plus-circle',
              'level' => 'primary-label',
              'button-class' => 'btn-success',
              'show' => $is_editor ]
        ]);

    $data['subscribeUrl'] = $urlAppend . 'main/profile/emailunsubscribe.php?cid=' . $course_id;
    $data['showSubscribeWarning'] = $uid && $status != USER_GUEST &&
        !get_user_email_notification($uid, $course_id);

    view('modules.announcements.index', $data);
}

