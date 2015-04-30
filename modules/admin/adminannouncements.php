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
        $(function() {
            $('#startdatecal').datetimepicker({
                format: 'dd-mm-yyyy hh:ii', pickerPosition: 'bottom-left', 
                language: '".$language."',
                autoclose: true
            });
            $('#enddatecal').datetimepicker({
                format: 'dd-mm-yyyy hh:ii', pickerPosition: 'bottom-left', 
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
    // submit announcement command
    $dates = array();
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
                            `date` = " . DBHelper::timeAfter() . ", $start_sql, $end_sql
                        WHERE id = ?d", $title, $newContent, $lang_admin_ann, $dates, $id);
        $message = $langAdminAnnModify;
    } else {
        // add new announcement
        // order
        $orderMax = Database::get()->querySingle("SELECT MAX(`order`) as max FROM admin_announcement")->max;
        $order = $orderMax + 1;
        Database::get()->query("INSERT INTO admin_announcement
                        SET title = ?s, 
                            body = ?s,
                            visible = 1, 
                            lang = ?s,
                            `date` = " . DBHelper::timeAfter() . ", 
                            `order` = ?d, 
                            $start_sql, 
                            $end_sql", $title, $newContent, $lang_admin_ann, $order, $dates);
        $message = $langAdminAnnAdd;
    }
}

// action message
if (isset($message) && !empty($message)) {
    $tool_content .= "<div class='alert alert-success'>$message</div><br/>";
    $displayAnnouncementList = true;
    $displayForm = false; //do not show form
}

// display form
if ($displayForm && isset($_GET['addAnnounce']) || isset($_GET['modify'])) {
    $displayAnnouncementList = false;
    // display add announcement command    
    $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]", "name" => $langAdminAn);

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
        if (isset($begindate)) {
            $start_checkbox = 'checked';
            $startdate = $begindate;
        } else {
            $start_checkbox = '';
            $startdate = date('d-m-Y H:i', strtotime('now'));
        }
        if (isset($enddate)) {
            $end_checkbox = 'checked';            
        } else {
            $end_checkbox = '';
            $enddate = date('d-m-Y H:i', strtotime('now +1 month'));
        }
        $tool_content .= "<div class='col-sm-10'>" . lang_select_options('lang_admin_ann', "class='form-control'", $myrow->lang) . "</div>";
    } else {
        $start_checkbox = $end_checkbox = '';
        $startdate = date('d-m-Y H:i', strtotime('now'));
        $enddate = date('d-m-Y H:i', strtotime('now +1 month'));        
        $tool_content .= "<div class='col-sm-10'>" . lang_select_options('lang_admin_ann', "class='form-control'") . "</div>";
    }
    $tool_content .= "<small class='text-right'><span class='help-block'>$langTipLangAdminAnn</span></small></div>
        <div class='form-group'>
            <div class='col-sm-offset-2 col-sm-10'>
            <div class='checkbox'>
                <label><input type='checkbox' name='startdate_active' " .
                    "$start_checkbox onClick=\"toggle(1,this,'startdate')\">&nbsp;" .
                    "$langActivate
                </label>
            </div>
            </div>
        </div>
        <div class='input-append date form-group' id='startdatecal' data-date='$langDate' data-date-format='dd-mm-yyyy'>
                <label for='startdate' class='col-sm-2 control-label'>$langStartDate :</label>
                <div class='col-xs-10 col-sm-9'>        
                    <input class='form-control' name='startdate' id='startdate' type='text' value = '" .$startdate . "'>
                </div>
                <div class='col-xs-2 col-sm-1'>  
                    <span class='add-on'><i class='fa fa-times'></i></span>
                    <span class='add-on'><i class='fa fa-calendar'></i></span>
                </div>
            </div>";
    $tool_content .= "<div class='form-group'>
                <div class='col-sm-offset-2 col-sm-10'>
                    <div class='checkbox'>
                        <label><input type='checkbox' name='enddate_active' " .
                        "$end_checkbox onClick=\"toggle(2,this,'enddate')\">&nbsp;" .
                        "$langActivate</label>
                    </div>
                </div>
                </div>
                <div class='input-append date form-group' id='enddatecal' data-date='$langDate' data-date-format='dd-mm-yyyy'>
                    <label for='enddate' class='col-sm-2 control-label'>$langEndDate :</label>
                    <div class='col-xs-10 col-sm-9'>        
                        <input class='form-control' name='enddate' id='enddate' type='text' value = '" .$enddate . "'>
                    </div>
                    <div class='col-xs-2 col-sm-1'>  
                        <span class='add-on'><i class='fa fa-times'></i></span>
                        <span class='add-on'><i class='fa fa-calendar'></i></span>
                    </div>
                </div>               
             <div class='form-group'>
                <div class='col-sm-offset-2 col-sm-10'>
                    <input class='btn btn-primary' type='submit' name='submitAnnouncement' value='$langSubmit'>
                </div>
            </div>
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
                        <tr><th>$langTitle</th>
                            <th>$langAnnouncement</th>
                            <th colspan='2'><div align='center'>" . icon('fa-gears') . "</th>";
        foreach ($result as $myrow) {
            if ($myrow->visible == 1) {
                $visibility = 0;
                $classvis = '';
            } else {
                $visibility = 1;
                $classvis = 'not_visible';
            }
            $myrow->date = claro_format_locale_date($dateFormatLong, strtotime($myrow->date));
            $tool_content .= "<tr class='$classvis'>
                <td width='200'><b>" . q($myrow->title) . "</b><br><span class='smaller'>$myrow->date</span></td>
                <td>" . standard_text_escape($myrow->body) . "</td>
                <td width='6'>" .
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