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

/**
 * @file form.php
 * @brief display form for creating graph statistics
 */
$require_current_course = true;
$require_course_admin = true;
$require_login = true;

$mod_opts = '<option value="-1">' . $langAllModules . "</option>";
$result = Database::get()->queryArray("SELECT module_id FROM course_module WHERE visible = 1 AND course_id = ?d", $course_id);
foreach ($result as $row) {
    $mid = $row->module_id;
    $extra = '';
    if ($u_module_id == $mid) {
        $extra = 'selected';
    }
    $mod_opts .= "<option value=" . $mid . " $extra>" . $modules[$mid]['title'] . "</option>";
}

$statsValueOptions = '<option value="visits" ' . (($u_stats_value == 'visits') ? ('selected') : ('')) . '>' . $langVisits . "</option>\n" .
        '<option value="duration" ' . (($u_stats_value == 'duration') ? ('selected') : ('')) . '>' . $langDuration . "</option>\n";

$statsIntervalOptions = '<option value="daily"   ' . (($u_interval == 'daily') ? ('selected') : ('')) . ' >' . $langDaily . "</option>\n" .
        '<option value="weekly"  ' . (($u_interval == 'weekly') ? ('selected') : ('')) . '>' . $langWeekly . "</option>\n" .
        '<option value="monthly" ' . (($u_interval == 'monthly') ? ('selected') : ('')) . '>' . $langMonthly . "</option>\n" .
        '<option value="yearly"  ' . (($u_interval == 'yearly') ? ('selected') : ('')) . '>' . $langYearly . "</option>\n" .
        '<option value="summary" ' . (($u_interval == 'summary') ? ('selected') : ('')) . '>' . $langSummary . "</option>\n";

$tool_content .= '
<form method="post" action="' . $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '">
<fieldset>
  <legend>' . $langUsageVisits . '</legend>
  <table class="tbl">
  <tr>
    <th>&nbsp;</th>
    <td>' . $langCreateStatsGraph . '</td>
  </tr>
  <tr>
    <th>' . $langValueType . ':</th>
    <td><select name="u_stats_value">' . $statsValueOptions . '</select></td>
  </tr>
  <tr>
    <th>' . $langStartDate . ':</th>
    <td><input type="text" name="u_date_start" value="' . $u_date_start . '"></td>
  </tr>
  <tr>
    <th>' . $langEndDate . ':</th>
    <td><input type="text" name="u_date_end" value="' . $u_date_end . '"></td>    
  </tr>
  <tr>
    <th>' . $langModule . ':</th>
    <td><select name="u_module_id">' . $mod_opts . '</select></td>
  </tr>
  <tr>
    <th>' . $langInterval . ':</th>
    <td><select name="u_interval">' . $statsIntervalOptions . '</select></td>
  </tr>
  </table>
</fieldset>
</form>';
