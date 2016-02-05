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


$require_admin = TRUE;
require_once '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';

$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$toolName = $langAdminAn;

load_js('tools.js');
load_js('bootstrap-datetimepicker');
load_js('trunk8');

$head_content .= <<<hContent
<script type='text/javascript'>
function toggle(id, checkbox, name)
{
        var f = document.getElementById('f-calendar-field-' + id);
        f.disabled = !checkbox.checked;
}
</script>
hContent;

$head_content .= "<script type='text/javascript'>
        $(document).ready(function () {
            $('.table_td_body').each(function() {
                $(this).trunk8({
                    lines: '3',
                    fill: '&hellip;<div class=\"clearfix\"></div><a style=\"float:right;\" href=\"adminannouncements_single.php?ann_id='
                    + $(this).data('id')+'\">$langMore</div>'
                })
            });

            if ( $('#submitAnnouncement').length > 0 ) {

            $('input[type=checkbox]').eq(0).prop('checked') ? $('input[type=checkbox]').eq(0).parents('.input-group').children('input').prop('disabled', false) : $('input[type=checkbox]').eq(0).parents('.input-group').children('input').prop('disabled', true);
            $('input[type=checkbox]').eq(1).prop('checked') ? $('input[type=checkbox]').eq(1).parents('.input-group').children('input').prop('disabled', false) : $('input[type=checkbox]').eq(1).parents('.input-group').children('input').prop('disabled', true);

                $('input[type=checkbox]').eq(0).on('click', function() {
                    if ($('input[type=checkbox]').eq(0).prop('checked')) {
                        $('input[type=checkbox]').eq(1).prop('disabled', false);
                    } else {
                        $('input[type=checkbox]').eq(1).prop('disabled', true);
                        $('input[type=checkbox]').eq(1).prop('checked', false);
                        $('input[type=checkbox]').eq(1).parents('.input-group').children('input').prop('disabled', true);
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
            }
        });
        $(function() {
            $('#startdate, #enddate').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-left',
                language: '".$language."',
                autoclose: true
            });            
        });
    </script>";

// display settings
$displayAnnouncementList = true;
$displayForm = true;

$newContent = isset($_POST['newContent']) ? $_POST['newContent'] : '';

foreach (array('title', 'lang_admin_ann') as $var) {
    if (isset($_POST[$var])) {
        $GLOBALS[$var] = q($_POST[$var]);
    } else {
        $GLOBALS[$var] = '';
    }
}

if (isset($_GET['addAnnounce']) or isset($_GET['modify'])) {
        if (isset($_GET['addAnnounce'])) {
            $pageName = $langAdminAddAnn;
        } else {
            $pageName = $langAdminModifAnn;
        }
        $tool_content .= action_bar(array(
                    array('title' => $langBack,
                        'url' => $_SERVER['SCRIPT_NAME'],
                        'icon' => 'fa-reply',
                        'level' => 'primary-label')
                    ));
    } else {
        $tool_content .= action_bar(array(
                array('title' => $langAdminAddAnn,
                    'url' => $_SERVER['SCRIPT_NAME'] . "?addAnnounce=1",
                    'icon' => 'fa-plus-circle',
                    'level' => 'primary-label',
                    'button-class' => 'btn-success')
                ));

    }

// modify visibility
if (isset($_GET['vis'])) {
    $id = q($_GET['id']);
    $vis = q($_GET['vis']);
    Database::get()->query("UPDATE admin_announcement SET visible = ?b WHERE id = ?d", $vis, $id);
}

if (isset($_GET['delete'])) {
    // delete announcement command
    $id = intval($_GET['delete']);
    Database::get()->query("DELETE FROM admin_announcement WHERE id = ?d", $id)->affectedRows;
    $message = $langAdminAnnDel;
} elseif (isset($_GET['modify'])) {
    // modify announcement command
    $id = intval($_GET['modify']);
    $myrow = Database::get()->querySingle("SELECT id, title, body, `date`, `begin`,`end`,
                                                lang, `order`, visible FROM admin_announcement WHERE id = ?d", $id);
    if ($myrow) {
        $titleToModify = q($myrow->title);
        $contentToModify = standard_text_escape($myrow->body);
        $displayAnnouncementList = true;
        $d1 = DateTime::createFromFormat('Y-m-d H:i:s', $myrow->begin);
        if ($d1) {
            $begindate = $d1->format("d-m-Y H:i");
        }
        $d2 = DateTime::createFromFormat('Y-m-d H:i:s', $myrow->end);
        if ($d2) {
            $enddate = $d2->format("d-m-Y H:i");
        }
    }
} elseif (isset($_POST['submitAnnouncement'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    // submit announcement command
    $dates = array();
    if (isset($_POST['show_public'])){
        $show_public =  1;
    } else {
        $show_public =  0;
    }
    if (isset($_POST['startdate_active']) and isset($_POST['startdate'])) {
        $start_sql = 'begin = ?s';
        $date_started = DateTime::createFromFormat("d-m-Y H:i", $_POST['startdate']);
        $dates[] = $date_started->format("Y-m-d H:i:s");
    } else {
        $start_sql = 'begin = NULL';
    }
    if (isset($_POST['enddate_active']) and isset($_POST['enddate'])) {
        $end_sql = 'end = ?s';
        $date_ended = DateTime::createFromFormat("d-m-Y H:i", $_POST['enddate']);
        $dates[] = $date_ended->format("Y-m-d H:i:s");
    } else {
        $end_sql = 'end = NULL';
    }
    $newContent = purify($newContent);
    if (isset($_POST['id'])) {
        // modify announcement
        $id = $_POST['id'];
        Database::get()->query("UPDATE admin_announcement
                        SET title = ?s, body = ?s, lang = ?s,
                            `date` = " . DBHelper::timeAfter() . ", $start_sql, $end_sql, `visible`=?d
                        WHERE id = ?d", $title, $newContent, $lang_admin_ann, $dates, $show_public, $id);
        $message = $langAdminAnnModify;
    } else {
        // add new announcement
        // order
        $orderMax = Database::get()->querySingle("SELECT MAX(`order`) as max FROM admin_announcement")->max;
        $order = $orderMax + 1;
        Database::get()->query("INSERT INTO admin_announcement
                        SET title = ?s,
                            body = ?s,
                            lang = ?s,
                            `date` = " . DBHelper::timeAfter() . ",
                            `order` = ?d,
                            $start_sql,
                            $end_sql, `visible`=?d", $title, $newContent, $lang_admin_ann, $order, $dates, $show_public);
        $message = $langAdminAnnAdd;
    }
}

// action message
if (isset($message) && !empty($message)) {
    Session::Messages($message, 'alert-success');
    redirect_to_home_page("/modules/admin/adminannouncements.php");
}

// display form
if ($displayForm && isset($_GET['addAnnounce']) || isset($_GET['modify'])) {
    $displayAnnouncementList = false;
    // display add announcement command
    $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]", "name" => $langAdminAn);

    $checked_public = "checked";

    if (!isset($contentToModify)) {
        $contentToModify = '';
    }
    if (!isset($titleToModify)) {
        $titleToModify = '';
    }
    $tool_content .= "<div class='form-wrapper'>";
    $tool_content .= "<form role='form' class='form-horizontal' method='post' action='$_SERVER[SCRIPT_NAME]'>";
    if (isset($_GET['modify'])) {
        $tool_content .= "<input type='hidden' name='id' value='$id' />";
    }
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='title' class='col-sm-2 control-label'>$langTitle:</label>
                        <div class='col-sm-10'><input class='form-control' type='text' name='title' value='$titleToModify' size='50' /></div>
                    </div>
                    <div class='form-group'>
                        <label for='newContent' class='col-sm-2 control-label'>$langAnnouncement:</label>
                    <div class='col-sm-10'>" . rich_text_editor('newContent', 5, 40, $contentToModify) . "</div></div>";
    $tool_content .= "<div class='form-group'><label class='col-sm-2 control-label'>$langLanguage:</label>";
    if (isset($_GET['modify'])) {
        if ($myrow->visible == 1) {
            $checked_public = "checked";
        } else {
            $checked_public = "";
        }
        if (isset($begindate)) {
            $start_checkbox = "checked";
            $start_text_disabled = "";
            $end_disabled = "";
            $startdate = $begindate;
            if (isset($enddate)) {
                $end_checkbox = "checked";
                $end_text_disabled = "";
            } else {
                $end_checkbox = "";
                $end_text_disabled = "disabled";
            }
        } else {
            $start_checkbox = "";
            $start_text_disabled = "disabled";
            $end_checkbox = "";
            $end_disabled = "disabled";
            $end_text_disabled = "disabled";
            $startdate = '';
        }
        if (isset($enddate)) {
            $end_checkbox = 'checked';
        } else {
            $end_checkbox = '';
            $enddate = '';
        }
        $tool_content .= "<div class='col-sm-10'>" . lang_select_options('lang_admin_ann', "class='form-control'", $myrow->lang) . "</div>";
    } else {
        $start_checkbox = $end_checkbox = '';
        $start_text_disabled = "disabled";
        $end_disabled = "disabled";
        $end_text_disabled = "disabled";

        $startdate = '';
        $enddate = '';
        $tool_content .= "<div class='col-sm-10'>" . lang_select_options('lang_admin_ann', "class='form-control'") . "</div>";
    }
    $tool_content .= "<small class='text-right'><span class='help-block'>$langTipLangAdminAnn</span></small></div>
        <div class='form-group'>
            <label for='startdate' class='col-sm-2 control-label'>$langStartDate :</label>
            <div class='col-sm-10'>
                <div class='input-group'>
                    <span class='input-group-addon'>
                        <input type='checkbox' name='startdate_active' $start_checkbox>
                    </span>
                    <input class='form-control' name='startdate' id='startdate' type='text' value = '" .$startdate . "' $start_text_disabled>
                </div>
            </div>
        </div>
        <div class='form-group'>
            <label for='enddate' class='col-sm-2 control-label'>$langEndDate :</label>
            <div class='col-sm-10'>
                <div class='input-group'>
                    <span class='input-group-addon'>
                        <input type='checkbox' name='enddate_active' $end_checkbox $end_disabled>
                    </span>
                    <input class='form-control' name='enddate' id='enddate' type='text' value = '" .$enddate . "' $end_text_disabled>
                </div>
            </div>
        </div>
        <div class='form-group'>
            <div class='col-sm-10 col-sm-offset-2'>
                <div class='checkbox'>
                    <label>
                        <input type='checkbox' name='show_public' $checked_public> $showall
                    </label>
                </div>
            </div>
        </div>
        <div class='form-group'>
            <div class='col-sm-offset-2 col-sm-10'>
                <input id='submitAnnouncement' class='btn btn-primary' type='submit' name='submitAnnouncement' value='$langSubmit'>
            </div>
        </div>
        ". generate_csrf_token_form_field() ."
        </form>
    </div>";
}

if (isset($_GET['down'])) {
    $thisAnnouncementId = q($_GET['down']);
    $sortDirection = "DESC";
}
if (isset($_GET['up'])) {
    $thisAnnouncementId = q($_GET['up']);
    $sortDirection = "ASC";
}

// if there are announcements without ordering -> order by id, latest is first
$no_order = Database::get()->querySingle("SELECT id, `order` FROM admin_announcement WHERE `order`=0");
if ($no_order) {
    Database::get()->query("UPDATE admin_announcement SET `order`=`id`+1");
}

if (isset($thisAnnouncementId) && $thisAnnouncementId && isset($sortDirection) && $sortDirection) {
    Database::get()->queryFunc("SELECT id, `order` FROM admin_announcement ORDER BY `order` $sortDirection", function ($announcement) use(&$thisAnnouncementOrderFound, &$nextAnnouncementId, &$nextAnnouncementOrder, &$thisAnnouncementOrder, &$thisAnnouncementId) {
        if (isset($thisAnnouncementOrderFound) && $thisAnnouncementOrderFound == true) {
            $nextAnnouncementId = $announcement->id;
            $nextAnnouncementOrder = $announcement->order;
            Database::get()->query("UPDATE admin_announcement SET `order` = ?s WHERE id = ?d", $nextAnnouncementOrder, $thisAnnouncementId);
            Database::get()->query("UPDATE admin_announcement SET `order` = ?s WHERE id = ?d", $thisAnnouncementOrder, $nextAnnouncementId);
            return true;
        }
        // find the order
        if ($announcement->id == $thisAnnouncementId) {
            $thisAnnouncementOrder = $announcement->order;
            $thisAnnouncementOrderFound = true;
        }
    });
}

// display admin announcements
if ($displayAnnouncementList == true) {
    $result = Database::get()->queryArray("SELECT * FROM admin_announcement ORDER BY `order` DESC");
    $bottomAnnouncement = $announcementNumber = count($result);
    if ($announcementNumber > 0) {
        $tool_content .= "<div class='table-responsive'><table class='table-default'>
                        <tr class='list-header'>
                            <th style='width: 70%;'>$langAnnouncement</th>
                            <th>$langDate</th>
                            <th>$langNewBBBSessionStatus</th>
                            <th><div align='center'>" . icon('fa-gears') . "</th>
                        </tr>";
        foreach ($result as $myrow) {

            $myrow->date = claro_format_locale_date($dateTimeFormatShort, strtotime($myrow->date));

            if ($myrow->visible == 1) {
                $visibility = 0;
                $classvis = '';
                $status_icon_list = "<li data-toggle='tooltip' data-placement='left' title='$langAnnouncementIsVis'><span class='fa fa-eye'></span> $langAdminAnVis</li>";
            } else {
                $visibility = 1;
                $classvis = 'not_visible';
                $status_icon_list = "<li data-toggle='tooltip' data-placement='left' title='$langAnnouncementIsNotVis'><span class='fa fa-eye-slash'></span> $langAdminAnNotVis</li>";
            }

            $now = date("Y-m-d H:i:s");

            if (!is_null($myrow->end) && ($myrow->end <= $now )) {
                $status_icon_list .= "<li class='text-danger'  data-toggle='tooltip' data-placement='left' title='$langAnnouncementWillNotBeVis$myrow->end'><span class='fa fa-clock-o'></span> $langAdminExpired</li>";
                $classvis = 'not_visible';
            } elseif ( !is_null($myrow->begin) && ($myrow->begin >= $now ) ) {
                $status_icon_list .= "<li class='text-success'  data-toggle='tooltip' data-placement='left' title='$langAnnouncementWillBeVis$myrow->begin'><span class='fa fa-clock-o'></span> $langAdminWaiting</li>";
                $classvis = 'not_visible';
            } else {
                $status_icon_list .= "";
            }

            $tool_content .= "<tr class='$classvis'>
                <td>
                    <div class='table_td'>
                        <div class='table_td_header clearfix'><a href='adminannouncements_single.php?ann_id=$myrow->id'>" . q($myrow->title) . "</a></div>
                        <div class='table_td_body' data-id='$myrow->id'>".standard_text_escape($myrow->body)."</div>
                    </div>
                </td>
                <td>$myrow->date</td>
                <td><div><ul class='list-unstyled'>$status_icon_list</ul></div></td>
                <td>" .
                    action_button(array(
                        array('title' => $langEditChange,
                            'url' => "$_SERVER[SCRIPT_NAME]?modify=$myrow->id",
                            'icon' => 'fa-edit'),
                        array('title' => $visibility == 0 ? $langViewHide : $langViewShow,
                            'url' => "$_SERVER[SCRIPT_NAME]?id=$myrow->id&amp;vis=$visibility",
                            'icon' => $visibility == 0 ? 'fa-eye-slash' : 'fa-eye'),
                        array('title' => $langUp,
                            'url' => "$_SERVER[SCRIPT_NAME]?up=$myrow->id",
                            'icon' => 'fa-arrow-up'),
                        array('title' => $langDown,
                            'url' => "$_SERVER[SCRIPT_NAME]?down=$myrow->id",
                            'icon' => 'fa-arrow-down'),
                        array('title' => $langDelete,
                            'class' => 'delete',
                            'url' => "$_SERVER[SCRIPT_NAME]?delete=$myrow->id",
                            'confirm' => $langConfirmDelete,
                            'icon' => 'fa-times')
                    )) . "
                </td></tr>";
        }
        $tool_content .= "</table>";
        $tool_content .= "</div>";
    } else {
        $tool_content .= "<div class='row'><div class='col-xs-12'><div class='alert alert-warning'>$langNoAnnounce</div></div></div>";
    }
}

draw($tool_content, 3, null, $head_content);