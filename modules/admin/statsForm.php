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

$qry = "SELECT LEFT(surname, 1) AS first_letter FROM user
            GROUP BY first_letter ORDER BY first_letter";
$letterlinks = '';
foreach (Database::get()->queryArray($qry) as $row) {
    $first_letter = $row->first_letter;
    $letterlinks .= '<a href="?first=' . $first_letter . '">' . q($first_letter) . '</a> ';
}


$qry = "SELECT id, surname, givenname, username, email FROM user";
$terms = array();
if (isset($_GET['first'])) {
    $firstletter = $_GET['first'];
    $qry .= " WHERE surname LIKE ?s";
    $terms[] = $firstletter . '%';
}

$user_opts = '<option value="-1">' . q($langAllUsers) . "</option>\n";
foreach (Database::get()->queryArray($qry, $terms) as $row) {
    if ($u_user_id == $row->id) {
        $selected = 'selected';
    } else {
        $selected = '';
    }
    $user_opts .= '<option ' . $selected . ' value="' . $row->id . '">' . q($row->givenname . ' ' . $row->surname) . "</option>\n";
}

$statsIntervalOptions = '<option value="daily"   ' . (($u_interval == 'daily') ? ('selected') : ('')) . ' >' . $langDaily . "</option>\n" .
        '<option value="weekly"  ' . (($u_interval == 'weekly') ? ('selected') : ('')) . '>' . $langWeekly . "</option>\n" .
        '<option value="monthly" ' . (($u_interval == 'monthly') ? ('selected') : ('')) . '>' . $langMonthly . "</option>\n" .
        '<option value="yearly"  ' . (($u_interval == 'yearly') ? ('selected') : ('')) . '>' . $langYearly . "</option>\n" .
        '<option value="summary" ' . (($u_interval == 'summary') ? ('selected') : ('')) . '>' . $langSummary . "</option>\n";

$tool_content .= '
<form method="post">
  <table class="FormData" width="99%" align="left">
  <tbody>
  <tr>
    <th width="220"  class="left">' . $langStartDate . ':</th>
    <td>';
$tool_content .= "<div class='input-append date form-group' id='u_date_start' data-date = '" . q($u_date_start) . "'>
            <div class='col-xs-11'>        
                <input name='u_date_start' type='text' value = '" . q($u_date_start) . "'>
            </div>
        <span class='add-on'><i class='fa fa-times'></i></span>
        <span class='add-on'><i class='fa fa-calendar'></i></span>
        </div>";
$tool_content .= '</td>
  </tr>
  <tr>
    <th class="left">' . $langEndDate . ':</th>
    <td>';
$tool_content .= "<div class='input-append date form-group' id='u_date_end' data-date= '" . q($u_date_end) . "'>
                <div class='col-xs-11'>
                    <input name='u_date_end' type='text' value= '" . q($u_date_end) . "'>
                </div>
            <span class='add-on'><i class='fa fa-times'></i></span>
            <span class='add-on'><i class='fa fa-calendar'></i></span>
            </div>";        
$tool_content .= '</td>
  </tr>
  <tr>
    <th class="left">' . $langFirstLetterUser . ':</th>
    <td>' . $letterlinks . '</td>
  </tr>
  <tr>
    <th class="left">' . $langUser . ':</th>
    <td><select name="u_user_id">' . $user_opts . '</select></td>
  </tr>
  <tr>
    <th class="left">' . $langInterval . ':</th>
    <td><select name="u_interval">' . $statsIntervalOptions . '</select></td>
  </tr>
  <tr>
    <th>&nbsp;</th>
    <td><input class="btn btn-primary" type="submit" name="btnUsage" value="' . $langSubmit . '"></td>
  </tr>
</table>
</form>';