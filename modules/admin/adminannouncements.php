<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

$require_admin = TRUE;
$require_help = true;
$helpTopic = 'system_settings';
$helpSubTopic = 'admin_announcements';

require_once '../../include/baseTheme.php';
require_once 'modules/search/indexer.class.php';
require_once 'include/log.class.php';

$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$toolName = $langAdmin;
$pageName = $langAdminAn;

// modify visibility
if (isset($_GET['vis'])) {
    $id = $_GET['id'];
    $vis = $_GET['vis'] ? 0 : 1;
    Database::get()->query("UPDATE admin_announcement SET visible = ?d WHERE id = ?d", $vis, $id);
    redirect_to_home_page('modules/admin/adminannouncements.php');
} elseif(isset($_GET['imp'])){
    if($_GET['imp'] == 0){
        $check = database::get()->queryArray("SELECT * FROM admin_announcement WHERE important = ?d",1);
        if(count($check) == 0){
            $id = $_GET['id'];
            Database::get()->query("UPDATE admin_announcement SET important = ?d WHERE id = ?d", 1, $id);
            Session::flash('message',$langFaqEditSuccess);
            Session::flash('alert-class', 'alert-success');
        }else{
            Session::flash('message',$langExistImportantAnnounce);
            Session::flash('alert-class', 'alert-danger');
        }
    }else{
        $id = $_GET['id'];
        Database::get()->query("UPDATE admin_announcement SET important = ?d WHERE id = ?d", 0, $id);
        Session::flash('message',$langFaqEditSuccess);
        Session::flash('alert-class', 'alert-success');
    }
    redirect_to_home_page('modules/admin/adminannouncements.php');
} elseif (isset($_GET['delete'])) {
    // delete announcement command
    $id = intval($_GET['delete']);
    Database::get()->query("DELETE FROM admin_announcement WHERE id = ?d", $id)->affectedRows;
    $message = $langAdminAnnDel;
} elseif (isset($_POST['submitAnnouncement'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('title'));
    $v->labels(array('title' => "$langTheField $langAnnTitle"));
    if($v->validate()) {
        $title = $_POST['title'];
        $lang_admin_ann = $_POST['lang_admin_ann'];
        // submit announcement command
        $dates = array();
        if (isset($_POST['show_public'])){
            $show_public =  1;
        } else {
            $show_public =  0;
        }
        if (isset($_POST['startdate_active']) and isset($_POST['startdate']) and !empty($_POST['startdate'])) {
            $start_sql = 'begin = ?s';
            $date_started = DateTime::createFromFormat("d-m-Y H:i", $_POST['startdate']);
            $dates[] = $date_started->format("Y-m-d H:i:s");
        } else {
            $start_sql = 'begin = NULL';
        }
        if (isset($_POST['enddate_active']) and isset($_POST['enddate']) and !empty($_POST['enddate'])) {
            $end_sql = 'end = ?s';
            $date_ended = DateTime::createFromFormat("d-m-Y H:i", $_POST['enddate']);
            $dates[] = $date_ended->format("Y-m-d H:i:s");
        } else {
            $end_sql = 'end = NULL';
        }
        $newContent = purify($_POST['newContent']);
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
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page("modules/admin/adminannouncements.php?addAnnounce=1");
    }
} elseif (isset($_GET['down'])) {
    $thisAnnouncementId = q($_GET['down']);
    $sortDirection = "DESC";
} elseif (isset($_GET['up'])) {
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

// action message
if (isset($message) && !empty($message)) {
    Session::flash('message',$message);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("/modules/admin/adminannouncements.php");
}

load_js('tools.js');
load_js('bootstrap-datetimepicker');
load_js('trunk8');

if (isset($_GET['addAnnounce']) || isset($_GET['modify'])) {
        if (isset($_GET['addAnnounce'])) {
            $pageName = $langAdminAddAnn;
        } else {
            $pageName = $langModify;
        }
        // display add announcement command
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]", "name" => $langAdminAn);

    if (isset($_GET['modify'])) {
        $id = $_GET['modify'];
        $data['announcement'] = $announcement = Database::get()->querySingle("SELECT id, title, body, `date`, `begin`,`end`,
                                                    lang, `order`, visible FROM admin_announcement WHERE id = ?d", $id);
    }
    if (isset($announcement)) {
        $data['newContentTextarea'] = rich_text_editor('newContent', 5, 40, standard_text_escape($data['announcement']->body));
        $begindate = NULL;
        if(!is_null($announcement->begin) and !empty($announcement->begin)){
            $begindate = DateTime::createFromFormat('Y-m-d H:i:s', $announcement->begin);
        }
        $enddate = NULL;
        if(!is_null($announcement->end) and !empty($announcement->end)){
            $enddate = DateTime::createFromFormat('Y-m-d H:i:s', $announcement->end);
        }


        $data['checked_public'] = $announcement->visible == 1 ? " checked" : "";
        $data['start_checkbox'] = !is_null($begindate) ? " checked" : "";
        if($begindate){
            $data['startdate'] = $begindate->format("d-m-Y H:i");
        }else{
            $data['startdate'] = NULL;
        }
        $data['end_checkbox'] = !is_null($enddate) ? " checked" : "";
        if($enddate){
            $data['enddate'] = $enddate->format("d-m-Y H:i");
        }else{
            $data['enddate'] = NULL;
        }

    } else {
        $newContent = '';
        if (Session::has('newContent')) {
            $newContent = Session::get('newContent');
        }
        $data['newContentTextarea'] = rich_text_editor('newContent', 5, 40, $newContent);

        $data['checked_public'] = " checked";
        $data['start_checkbox'] = $data['end_checkbox'] = '';
        $data['startdate'] = date('d-m-Y H:i', strtotime('now'));
        $data['enddate'] = date('d-m-Y H:i', strtotime('now +1 month'));

        if (Session::has('startdate_active')) {
            $data['start_checkbox'] = ' checked';
        }
        if (Session::has('enddate_active')) {
            $data['end_checkbox'] = ' checked';
        }
        if (Session::has('startdate')) {
            $data['startdate'] = Session::get('startdate');
        }
        if (Session::has('enddate')) {
            $data['enddate'] = Session::get('enddate');
        }
    }
    $view = 'admin.other.announcements.create';
}

elseif (isset($_GET['ann_id'])) {
    $row = Database::get()->querySingle("SELECT * FROM admin_announcement WHERE id = ". intval($_GET['ann_id']));
    if (empty($row)) {
        redirect_to_home_page("modules/admin/adminannouncements.php");
    } else {
        $data['announcementsID'] = $row;
        $view = 'admin.other.announcements.show';
    }
} else {
    $data['action_bar'] = action_bar([
                                [
                                    'title' => $langAdminAddAnn,
                                    'url' => $_SERVER['SCRIPT_NAME'] . "?addAnnounce=1",
                                    'icon' => 'fa-plus-circle',
                                    'level' => 'primary-label',
                                    'button-class' => 'btn-success'
                                ]
                            ]);
    $data['announcements'] = Database::get()->queryArray("SELECT * FROM admin_announcement ORDER BY `order` DESC");
    $view = 'admin.other.announcements.index';
}

view($view, $data);
