<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/



// * @version $Id$
 //   @last update: 2006-12-27 by Evelthon Prodromou <eprodromou@upnet.gr>
$require_current_course=true;
$require_login = true;
$require_prof = true;

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


$qry = "SELECT id, rubrique AS name FROM accueil WHERE define_var != '' AND visible <> 0 ORDER BY name ";
$mod_opts = '<option value="-1">'.$langAllModules."</option>\n";
$result = db_query($qry, $currentCourseID);
while ($row = mysql_fetch_assoc($result)) {
    if ($u_module_id == $row['id']) { $selected = 'selected'; } else { $selected = ''; }
    $mod_opts .= '<option '.$selected.' value="'.$row["id"].'">'.$row['name']."</option>\n";
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
<form method="post">
  <table class="FormData" width="99%" align="left">
  <tbody>
  <tr>
    <th width="220" class="left">&nbsp;</th>
    <td><b>'.$langUsageVisits.'</b><br />'.$langCreateStatsGraph.':</td>
  </tr>
  <tr>
    <th class="left">'.$langValueType.':</th>
    <td><select name="u_stats_value" class="auth_input">'.$statsValueOptions.'</select></td>
  </tr>
  <tr>
    <th class="left">'.$langStartDate.':</th>
    <td>'."$start_cal".'</td>
  </tr>
  <tr>
    <th class="left">'.$langEndDate.':</th>
    <td>'."$end_cal".'</td>
  </tr>
  <tr>
    <th class="left">'.$langModule.':</th>
    <td><select name="u_module_id" class="auth_input">'.$mod_opts.'</select></td>
  </tr>
  <tr>
    <th class="left">'.$langInterval.':</th>
    <td><select name="u_interval" class="auth_input">'.$statsIntervalOptions.'</select></td>
  </tr>
  <tr>
    <th class="left">&nbsp;</th>
    <td><input type="submit" name="btnUsage" value="'.$langSubmit.'">
        <div align="right"><a href="oldStats.php">'.$langOldStats.'</a></div>
    </td>
  </tr>
  </tbody>
  </table>
</form>';
?>
