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
$out = "<pre>$start_cal \n $end_cal</pre>";
//die($out);
$tool_content .= '
<form>
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
            <td>Χρήστης</td>
            <td>
                <select>
                    <option>Γιώργος Παπαδάκης</option>
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
</table>
</form>';

?>
