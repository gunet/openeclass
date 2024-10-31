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

/**
 * Created by PhpStorm.
 * User: jexi
 * Date: 10/11/18
 * Time: 11:16 AM
 */

$require_admin = TRUE;

require_once '../../../include/baseTheme.php';
require_once 'modules/auth/transition/Transition.class.php';

$toolName = "Διαχείριση αιτημάτων εξαίρεσης";
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

$tool_content .= action_bar(array(
    array('title' => $langBack,
        'url' => "../../admin/auth.php",
        'icon' => 'fa-reply',
        'level' => 'primary')));



if (isset($_GET['action'])) {
    $action = $_GET['action'];
    Transition::change_exception_status($_GET['eid'], $action);
}

$tool_content .= "<div class='col-sm-12'><div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>Αιτήματα εξαίρεσης</span></div></div>";
$tool_content .= "<div class='table-responsive'>";

$q = Database::get()->queryArray("SELECT * FROM sso_exception");
if (count($q) > 0) {
    $tool_content .= "<table class='table-default'>";
    $tool_content .= "<th>Α/Α</th><th>Χρήστης</th><th>Σχόλια</th><th>Κατάσταση</th><th>Ημερομηνία</th><th>Ενέργειες</th>";
    $i = 1;
    foreach ($q as $data) {
        $tool_content .= "<tr class=" . Transition::row_style($data->status) . ">";
        $tool_content .= "<td>" . $i++ . "</td>";
        $tool_content .= "<td>" . display_user($data->uid) . "</td>";
        $tool_content .= "<td>" . q($data->comments) . "</td>";
        $tool_content .= "<td>" . Transition::exception_status($data->status) . "</td>";
        $tool_content .= "<td>" . format_locale_date(strtotime($data->timestamp), 'short') . "</td>";
        $tool_content .= "<td class='option-btn-cell text-end'>" . action_button(array(
                array('title' => 'Αποδοχή',
                    'url' => "$_SERVER[SCRIPT_NAME]?eid=$data->id&amp;action=yes",
                    'icon' => 'fa-check-circle'),
                array('title' => 'Απόρριψη',
                    'url' => "$_SERVER[SCRIPT_NAME]?eid=$data->id&amp;action=close",
                    'icon' => 'fa-times-circle'),
                array('title' => 'Αποκλεισμός',
                    'url' => "$_SERVER[SCRIPT_NAME]?eid=$data->id&amp;action=reject",
                    'icon' => 'fa-ban')
            )) . "</td>";
        $tool_content .= "</tr>";
    }
    $tool_content .= "</table>";
} else {
    $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>Δεν υπάρχουν αιτήματα εξαίρεσης.</span></div></div>";
}

$tool_content .= "</div>";

draw($tool_content, 3);
