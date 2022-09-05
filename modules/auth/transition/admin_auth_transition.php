<?php
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
        'level' => 'primary-label')));



if (isset($_GET['action'])) {
    $action = $_GET['action'];
    Transition::change_exception_status($_GET['eid'], $action);
}

$tool_content .= "<div class='alert alert-info'>Αιτήματα εξαίρεσης</div>";
$tool_content .= "<div class='table-responsive'>";

$q = Database::get()->queryArray("SELECT * FROM sso_exception");
if (count($q) > 0) {
    $tool_content .= "<table class='table-default'>";
    $tool_content .= "<th>Α/Α</th><th>Χρήστης</th><th>Σχόλια</th><th>Κατάσταση</th><th>Ημερομηνία</th><th>Ενέργειες</th>";
    $i = 1;
    foreach ($q as $data) {
        $tool_content .= "<tr class=" . Transition::row_style($data->status) . ">";
        $tool_content .= "<td class='text-center'>" . $i++ . "</td>";
        $tool_content .= "<td>" . display_user($data->uid) . "</td>";
        $tool_content .= "<td>" . q($data->comments) . "</td>";
        $tool_content .= "<td>" . Transition::exception_status($data->status) . "</td>";
        $tool_content .= "<td>" . format_locale_date(strtotime($data->timestamp), 'short') . "</td>";
        $tool_content .= "<td class='option-btn-cell'>" . action_button(array(
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
    $tool_content .= "<div class='alert alert-warning'>Δεν υπάρχουν αιτήματα εξαίρεσης.</div>";
}

$tool_content .= "</div>";

draw($tool_content, 3);
