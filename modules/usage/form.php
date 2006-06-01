<?php

$start_cal = $jscalendar->make_input_field(
           array('showsTime'      => false,
                 'showOthers'     => true,
                 'ifFormat'       => '%Y-%m-%d',
                 'timeFormat'     => '24'),
           array('style'       => 'width: 15em; color: #840; background-color: #ff8; border: 1px solid #000; text-align: center',
                 'name'        => 'date_start',
                 'value'       => strftime('%Y-%m-%d', strtotime('now -1 year'))));

$end_cal = $jscalendar->make_input_field(
           array('showsTime'      => false,
                 'showOthers'     => true,
                 'ifFormat'       => '%Y-%m-%d',
                 'timeFormat'     => '24'),
           array('style'       => 'width: 15em; color: #840; background-color: #ff8; border: 1px solid #000; text-align: center',
                 'name'        => 'date_end',
                 'value'       => strftime('%Y-%m-%d', strtotime('now'))));

$qry = "SELECT a.user_id, a.nom, a.prenom, a.username, a.email, b.statut
    FROM user AS a LEFT JOIN cours_user AS b ON a.user_id = b.user_id
    WHERE b.code_cours='".$currentCourseID."'";


$user_opts .= '<option value="-1">'.$langAllUsers."</option>\n";
$result = db_query($qry, $mysqlMainDb);
while ($row = mysql_fetch_assoc($result)) {
    $user_opts .= '<option value="'.$row["user_id"].'">'.$row['prenom'].' '.$row['nom']."</option>\n";
}


$qry = "SELECT id, rubrique AS name FROM accueil WHERE define_var != '' AND visible = 1 ORDER BY name ";

$mod_opts .= '<option value="-1">'.$langAllModules."</option>\n";
$result = db_query($qry, $currentCourseID);
while ($row = mysql_fetch_assoc($result)) {
    $mod_opts .= '<option value="'.$row["id"].'">'.$row['name']."</option>\n";
}


//die($out);
$tool_content .= '
<form method="post">
    <table>
        <tr><td colspan="2" align="center">'.$statsImage.'</td></tr>
        <tr>
            <td>'.$langStatsType.'</td>
            <td><select>'.$statsTypeOptions.'</select></td>
        </tr>
        <tr>
            <td>'.$langStartDate.'</td>
            <td>'."$start_cal".'</td>
        </tr>
        <tr>
            <td>'.$langEndDate.'</td>
            <td>'."$end_cal".'</td>
        </tr>
        <tr>
            <td>'.$langUser.'</td>
            <td>
                <select name="user_id">
                  '.$user_opts.'
               </select>
            </td>
        </tr>
        <tr>
            <td>'.$langModules.'</td>
            <td>
                <select name="module_id">
                  '.$mod_opts.'
               </select>
            </td>
        </tr>
        <tr>
            <td>Συγκεντρωτικά</td>
            <td>
                <input type="checkbox" name="uncompress" value="1">
            </td>
        </tr>
        <tr>
            <td>Προβολή Χρηστών</td>
            <td>
                <input type="checkbox" name="uncompress" value="1">
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>
                <input type="submit" name="btnUsage" value="'.$langSubmit.'">
            </td>
        </tr>
</table>
</form>';

?>
