<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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
$require_course_admin = TRUE;
$require_login = true;

$start_cal = $jscalendar->make_input_field(
           array('showsTime'      => false,
                 'showOthers'     => true,
                 'ifFormat'       => '%Y-%m-%d',
                 'timeFormat'     => '24'),
           array('style'       => 'width: 10em; color: #727266; background-color: #fbfbfb; border: 1px solid #CAC3B5; text-align: center',
                 'name'        => 'u_date_start',
                 'value'       => $u_date_start));

$end_cal = $jscalendar->make_input_field(
           array('showsTime'      => false,
                 'showOthers'     => true,
                 'ifFormat'       => '%Y-%m-%d',
                 'timeFormat'     => '24'),
           array('style' => 'width: 10em; color: #727266; background-color: #fbfbfb; border: 1px solid #CAC3B5; text-align: center',
                 'name'  => 'u_date_end',
                 'value' => $u_date_end));


$qry = "SELECT module_id FROM modules WHERE visible = 1 AND course_id = ".course_code_to_id($currentCourseID);        
$mod_opts = '<option value="-1">'.$langAllModules."</option>";
$result = db_query($qry);
while ($row = mysql_fetch_assoc($result)) {
        $mid = $row['module_id'];   
        $extra = '';
        if ($u_module_id == $mid) {
                $extra = 'selected';
        }
        $mod_opts .= "<option value=".$mid." $extra>".$modules[$mid]['title']."</option>";            
}

$statsValueOptions =
    '<option value="visits" '.	 (($u_stats_value=='visits')?('selected'):(''))	  .'>'.$langVisits."</option>\n".
    '<option value="duration" '.(($u_stats_value=='duration')?('selected'):('')) .'>'.$langDuration."</option>\n";

$statsIntervalOptions =
    '<option value="daily"   '.(($u_interval=='daily')?('selected'):(''))  .' >'.$langDaily."</option>\n".
    '<option value="weekly"  '.(($u_interval=='weekly')?('selected'):('')) .'>'.$langWeekly."</option>\n".
    '<option value="monthly" '.(($u_interval=='monthly')?('selected'):('')).'>'.$langMonthly."</option>\n".
    '<option value="yearly"  '.(($u_interval=='yearly')?('selected'):('')) .'>'.$langYearly."</option>\n".
    '<option value="summary" '.(($u_interval=='summary')?('selected'):('')).'>'.$langSummary."</option>\n";

$tool_content .= '
<form method="post" action="'.$_SERVER['PHP_SELF'].'?course='.$code_cours.'">
<fieldset>
  <legend>'.$langUsageVisits.'</legend>
  <table class="tbl">
  <tr>
    <th>&nbsp;</th>
    <td>'.$langCreateStatsGraph.'</td>
  </tr>
  <tr>
    <th>'.$langValueType.':</th>
    <td><select name="u_stats_value">'.$statsValueOptions.'</select></td>
  </tr>
  <tr>
    <th>'.$langStartDate.':</th>
    <td>'."$start_cal".'</td>
  </tr>
  <tr>
    <th>'.$langEndDate.':</th>
    <td>'."$end_cal".'</td>
  </tr>
  <tr>
    <th>'.$langModule.':</th>
    <td><select name="u_module_id">'.$mod_opts.'</select></td>
  </tr>
  <tr>
    <th>'.$langInterval.':</th>
    <td><select name="u_interval">'.$statsIntervalOptions.'</select></td>
  </tr>
  <tr>
    <th>&nbsp;</th>
    <td><input type="submit" name="btnUsage" value="'.$langSubmit.'">
        <div><br /><a href="oldStats.php?course='.$code_cours.'">'.$langOldStats.'</a></div>
    </td>
  </tr>
  </table>
</fieldset>
</form>';