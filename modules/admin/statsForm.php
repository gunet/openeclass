<?php

$start_cal = $jscalendar->make_input_field(
           array('showsTime'      => false,
                 'showOthers'     => true,
                 'ifFormat'       => '%Y-%m-%d',
                 'timeFormat'     => '24'),
           array('style'       => 'width: 15em; color: #840; background-color: #ff8; border: 1px solid #000; text-align: center',
                 'name'        => 'u_date_start',
                 'value'       => $u_date_start));

$end_cal = $jscalendar->make_input_field(
           array('showsTime'      => false,
                 'showOthers'     => true,
                 'ifFormat'       => '%Y-%m-%d',
                 'timeFormat'     => '24'),
           array('style'       => 'width: 15em; color: #840; background-color: #ff8; border: 1px solid #000; text-align: center',
                 'name'        => 'u_date_end',
                 'value'       => $u_date_end));

$qry = "SELECT user_id, nom, prenom, username, email
    FROM user";


$user_opts = '<option value="-1">'.$langAllUsers."</option>\n";
$result = db_query($qry, $mysqlMainDb);
while ($row = mysql_fetch_assoc($result)) {
    if ($u_user_id == $row['user_id']) { $selected = 'selected'; } else { $selected = ''; }
    $user_opts .= '<option '.$selected.' value="'.$row["user_id"].'">'.$row['prenom'].' '.$row['nom']."</option>\n";
}




$statsTypeOptions =
    '<option value="visits" '.	 (($u_stats_type=='visits')?('selected'):(''))	  .'>'.$langVisits."</option>\n";
  
$statsIntervalOptions =
    '<option value="daily"   '.(($u_interval=='daily')?('selected'):(''))  .' >'.$langDaily."</option>\n".
    '<option value="weekly"  '.(($u_interval=='weekly')?('selected'):('')) .'>'.$langWeekly."</option>\n".
    '<option value="monthly" '.(($u_interval=='monthly')?('selected'):('')).'>'.$langMonthly."</option>\n".
    '<option value="yearly"  '.(($u_interval=='yearly')?('selected'):('')) .'>'.$langYearly."</option>\n".
    '<option value="summary" '.(($u_interval=='summary')?('selected'):('')).'>'.$langSummary."</option>\n";

//die($out);
$tool_content .= '
<form method="post">
    <table>
        <tr>
            <td>'.$langStatsType.'</td>
            <td><select name="u_stats_type">'.$statsTypeOptions.'</select></td>
        </tr>
        <tr>
            <td>'.$langStartDate.'</td>
            <td>'.$start_cal.'</td>
        </tr>
        <tr>
            <td>'.$langEndDate.'</td>
            <td>'.$end_cal.'</td>
        </tr>
        <tr>
            <td>'.$langUser.'</td>
            <td><select name="u_user_id">'.$user_opts.'</select></td>
        </tr>
        
        <tr>
            <td>'.$langInterval.'</td>
            <td><select name="u_interval">'.$statsIntervalOptions.'</select></td>
        </tr>
        
        <tr>
            <td>&nbsp;</td>
            <td><input type="submit" name="btnUsage" value="'.$langSubmit.'"></td>
        </tr>
        
        
        
</table>
</form>';

?>
