<?php

/*
===========================================================================
    admin/monthlyReport.php
    @last update: 23-09-2006
    @authors list: ophelia neofytou
==============================================================================
    @Description: Shows a form in order for the user to choose a month and display
    a report regarding this month. The report is based on information stored in table
    'monthly_summary' in database.
==============================================================================
*/


// Set the langfiles needed
$langFiles = array('usage', 'admin');
// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = TRUE;
// Include baseTheme
include '../../include/baseTheme.php';
// Define $nameTools
$nameTools = $langMonthlyReport;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
// Initialise $tool_content
$tool_content = "";


$tool_content .=  "<a href='statClaro.php'>".$langPlatformGenStats."</a> <br> ".
                "<a href='platformStats.php?first='>".$langVisitsStats."</a> <br> ".
             "<a href='visitsCourseStats.php?first='>".$langVisitsCourseStats."</a> <br> ".
              "<a href='oldStats.php'>".$langOldStats."</a> <br> ".
               "<a href='monthlyReport.php'>".$langMonthlyReport."</a>".
          "<p>&nbsp</p>";



#require_once "summarizeMonthlyData.php";


include('../../include/jscalendar/calendar.php');
if ($language == 'greek') {
    $lang = 'el';
} else if ($language == 'english') {
    $lang = 'en';
}

$months = "";
for ($i=0; $i<12; $i++)
{
  $mon = mktime(0, 0, 0, date("m")-$i-1, date("d"),  date("Y"));
  $mval = date("m Y", $mon);
  $months .= "<option value='$mval'>".$langMonths[date("n", $mon)] . date(" Y", $mon);
}


$tool_content .= '
    <form method="post">
    &nbsp;&nbsp;
        <table>

        <tr>
            <select name="selectedMonth">
            '.$months.'
           </select>
            
        </tr>

        <tr>
            <input type="submit" name="btnUsage" value="'.$langSubmit.'">
        </tr>
        </table>
    </form>';



if (isset($_POST["selectedMonth"])) {

    $month = $_POST["selectedMonth"];
    
    list($m, $y) = split(' ',$month);  //only month

    
    $sql = "SELECT profesNum, studNum, visitorsNum, coursNum, logins, details FROM monthly_summary ".
        "WHERE `month` = '$month'";
        
    $result = db_query($sql, $mysqlMainDb);
    $coursNum='';
    while ($row = mysql_fetch_assoc($result)) {
            $profesNum = $row['profesNum'];
            $studNum = $row['studNum'];
            $visitorsNum = $row['visitorsNum'];
            $coursNum = $row['coursNum'];
            $logins = $row['logins'];
            $details = $row['details'];
    }
    mysql_free_result($result);
    
    
    
    if ($coursNum)
    {
        $tool_content .= '<br><table > <tr>

          <th> '.$langReport.' '.$langMonths[$m].' '.$y.' </th></tr>
            <tr><td>

              '.$langNbProf.': <b>'.$profesNum.'</b>

             <br> '.$langNbStudents.': <b>'.$studNum.'</b>
        
             <br> '.$langNbVisitors.': <b>'.$visitorsNum.'</b>
       
             <br> '.$langNbCourses.':  <b>'.$coursNum.'</b>
       
             <br> '.$langNbLogin.': <b>'.$logins.'</b>

            
          <p>&nbsp;</p>'.$details. '</td></tr></table>';  //$details includes an html table with all details
          
    }
    else {
        $tool_content .= '<br><p>'.$langNoReport.' '.$langMonths[$m].' '.$y.'</p>';
      }
               
}





draw($tool_content, 3, 'admin');



?>
