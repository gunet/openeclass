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

$user_opts = '<option value="-1">' . q($langAllUsers) . "</option>";
foreach (Database::get()->queryArray($qry, $terms) as $row) {
    if ($u_user_id == $row->id) {
        $selected = 'selected';
    } else {
        $selected = '';
    }
    $user_opts .= '<option ' . $selected . ' value="' . $row->id . '">' . q($row->givenname . ' ' . $row->surname) . "</option>";
}

$statsIntervalOptions = '<option value="daily"   ' . (($u_interval == 'daily') ? ('selected') : ('')) . ' >' . $langDaily . "</option>" .
        '<option value="weekly"  ' . (($u_interval == 'weekly') ? ('selected') : ('')) . '>' . $langWeekly . "</option>" .
        '<option value="monthly" ' . (($u_interval == 'monthly') ? ('selected') : ('')) . '>' . $langMonthly . "</option>" .
        '<option value="yearly"  ' . (($u_interval == 'yearly') ? ('selected') : ('')) . '>' . $langYearly . "</option>" .
        '<option value="summary" ' . (($u_interval == 'summary') ? ('selected') : ('')) . '>' . $langSummary . "</option>";

$tool_content .= '<div class="col-12"><div class="form-wrapper form-edit rounded"><form class="form-horizontal" role="form" method="post">';   
$tool_content .= "
<div class='input-append date form-group' id='user_date_start' data-date = '" . q($user_date_start) . "' data-date-format='dd-mm-yyyy'>
    <label class='col-sm-6 control-label-notes'>$langStartDate:</label>
    <div class='input-group'>         
        <input class='form-control mt-0' name='user_date_start' type='text' value = '" . q($user_date_start) . "'>
        <span class='add-on input-group-text h-30px border-0 BordersRightInput bgEclass'><i class='fa fa-times'></i></span>
        <span class='add-on input-group-text h-30px border-0 BordersRightInput bgEclass'><i class='fa fa-calendar'></i></span>
    </div>
</div>";        
$tool_content .= "
<div class='input-append date form-group' id='user_date_end' data-date= '" . q($user_date_end) . "' data-date-format='dd-mm-yyyy'>
    <label class='col-sm-6 control-label-notes'>$langEndDate:</label>
    <div class='input-group'>      
        <input class='form-control' name='user_date_end' type='text' value= '" . q($user_date_end) . "'>
        <span class='add-on input-group-text h-30px border-0 BordersRightInput bgEclass'><i class='fa fa-times'></i></span>
        <span class='add-on input-group-text h-30px border-0 BordersRightInput bgEclass'><i class='fa fa-calendar'></i></span> 
    </div>
</div>";
$tool_content .= '<div class="form-group mt-4">  
    <label class="col-sm-6 control-label-notes">' . $langFirstLetterUser . ':</label>
    <div class="col-sm-12">' . $letterlinks . '</div>
  </div>
  <div class="form-group mt-4">  
    <label class="col-sm-6 control-label-notes">' . $langUser . ':</label>
     <div class="col-sm-12"><select name="u_user_id" class="form-select">' . $user_opts . '</select></div>
  </div>
  <div class="form-group mt-4">  
    <label class="col-sm-6 control-label-notes">' . $langInterval . ':</label>
     <div class="col-sm-12"><select name="u_interval" class="form-select">' . $statsIntervalOptions . '</select></div>
  </div>
  <div class="col-sm-offset-2 col-sm-10">    
    <input class="btn submitAdminBtn" type="submit" name="btnUsage" value="' . $langSubmit . '">
    </div>  
</form></div></div>';