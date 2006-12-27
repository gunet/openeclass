<?php
/*
=============================================================================
           GUnet e-Class 2.0
        E-learning and Course Management Program
================================================================================
        Copyright(c) 2003-2006  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".

           Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
                    Yannis Exidaridis <jexi@noc.uoa.gr>
                       Alexandros Diamantidis <adia@noc.uoa.gr>

        For a full list of contributors, see "credits.txt".

        This program is a free software under the terms of the GNU
        (General Public License) as published by the Free Software
        Foundation. See the GNU License for more details.
        The full license can be read in "license.txt".

        Contact address: GUnet Asynchronous Teleteaching Group,
        Network Operations Center, University of Athens,
        Panepistimiopolis Ilissia, 15784, Athens, Greece
        eMail: eclassadmin@gunet.gr
==============================================================================
*/

/*
===========================================================================
    usage/oldStats.php
     * @version $Id$
    @last update: 2006-12-27 by Evelthon Prodromou <eprodromou@upnet.gr>
    @authors list: Vangelis Haniotakis haniotak@ucnet.uoc.gr
                Ophelia Neofytou    ophelia@ucnet.uoc.gr
==============================================================================
    @Description: 
        Show old statistics for the course, taken from table "action_summary" of the course's database.

==============================================================================
*/

$require_current_course = TRUE;
$langFiles 				= 'usage';
$require_help 			= true;
$helpTopic 				= 'Usage';
$require_login = true;
$require_prof = true;

include '../../include/baseTheme.php';
include('../../include/action.php');

$tool_content = '';
$tool_content .= "<div id=\"operations_container\">
	<ul id=\"opslist\">";
$tool_content .= "<li><a href='usage.php'>".$langUsage."</a></li>";
$tool_content .= "<li><a href='favourite.php?first='>".$langFavourite."</a></li>";
$tool_content .= "<li><a href='userlogins.php?first='>".$langUserLogins."</a></li>";
$tool_content .= "<li><a href='oldStats.php'>".$langOldStats."</a></li></ul></div>";


$query = "SELECT MIN(date_time) as min_time FROM actions";
$result = db_query($query, $currentCourseID);
while ($row = mysql_fetch_assoc($result)) {
    
    $min_time = strtotime($row['min_time']);
    
}

@mysql_free_result($result);
 
if ( $min_time + 243*24*3600 < time()) { #actions more than eight months old
    $action->summarize();     #move data to action_summary
}

$query = "SELECT MIN(date_time) as min_time FROM actions";
$result = db_query($query, $currentCourseID);
while ($row = mysql_fetch_assoc($result)) {
    $min_time = strtotime($row['min_time']);
}
@mysql_free_result($result);

$min_t = date("d-m-Y", $min_time);
$tool_content .= "<p> $langOldStatsExpl</p>";

$dateNow = date("d-m-Y / H:i:s",time());
$nameTools = $langUsage;
$local_style = '
    .month { font-weight : bold; color: #FFFFFF; background-color: #000066;
     padding-left: 15px; padding-right : 15px; }
    .content {position: relative; left: 25px; }';


include('../../include/jscalendar/calendar.php');
if ($language == 'greek') {
    $lang = 'el';
} else if ($language == 'english') {
    $lang = 'en';
}

$jscalendar = new DHTML_Calendar($urlServer.'include/jscalendar/', $lang, 'calendar-win2k-2', false);
$local_head = $jscalendar->get_load_files_code();

 if (!extension_loaded('gd')) {
        $tool_content .= "<p>$langGDRequired</p>";
 } else {
        $made_chart = true;

        //make chart
        require_once '../../include/libchart/libchart.php';
        $usage_defaults = array (
            'u_stats_value' => 'visits',
            'u_module_id' => -1,
            'u_date_start' => strftime('%Y-%m-%d', strtotime('now -4 month')),
            'u_date_end' => strftime('%Y-%m-%d', strtotime('now -1 month')),
        );

        foreach ($usage_defaults as $key => $val) {
            if (!isset($_POST[$key])) {
                $$key = $val;
            } else {
                $$key = $_POST[$key];
            }
        }

    $date_fmt = '%Y-%m-%d';
    $date_where = " (start_date BETWEEN '$u_date_start 00:00:00' AND '$u_date_end 23:59:59') ";   #AUTO PREPEI NA ALLA#EI


   if ($u_module_id != -1) {
    $mod_where = " (module_id = '$u_module_id') ";
   } else {
    $mod_where = " (1) ";
   }

    #check if statistics exist
    $chart_content=0;

    switch ($u_stats_value) {
        case "visits":
            $query = "SELECT module_id, MONTH(start_date) AS month, YEAR(start_date) AS year, SUM(visits) AS visits FROM actions_summary ".
            " WHERE $date_where AND $mod_where GROUP BY MONTH(start_date)";

            $result = db_query($query, $currentCourseID);
   
            $chart = new VerticalChart(200, 300);
   
            while ($row = mysql_fetch_assoc($result)) {
                $mont = $langMonths[$row['month']];
                $chart->addPoint(new Point($mont." - ".$row['year'], $row['visits']));
                $chart->width += 25;
                 $chart_content=1;
            }
       
            $chart->setTitle("$langOldStats");

        break;

        case "duration":
            $query = "SELECT module_id, MONTH(start_date) AS month, YEAR(start_date) AS year, ".
                     " SUM(duration) AS tot_dur FROM actions_summary ".
                     " WHERE $date_where AND $mod_where GROUP BY MONTH(start_date)";

            $result = db_query($query, $currentCourseID);

            $chart = new VerticalChart(200, 300);

            while ($row = mysql_fetch_assoc($result)) {
                $mont = $langMonths[$row['month']];
                $chart->addPoint(new Point($mont." - ".$row['year'], $row['tot_dur']));
                $chart->width += 25;
                 $chart_content=1;
            }

            $chart->setTitle("$langOldStats");
            $tool_content .= "<p> $langDurationExpl</p>";

        break;
    }
    mysql_free_result($result);
    $chart_path = 'courses/'.$currentCourseID.'/temp/chart_'.md5(serialize($chart)).'.png';

    $chart->render($webDir.$chart_path);

    if ($chart_content) {
        $tool_content .= '<img src="'.$urlServer.$chart_path.'" />';
    }
    else   {
      $tool_content .='<br><p>'.$langNoStatistics.'</p>';
    }
    $tool_content .= '<br>';


    //make form
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

  
    $qry = "SELECT id, rubrique AS name FROM accueil WHERE define_var != '' AND visible = 1 ORDER BY name ";

    $mod_opts = '<option value="-1">'.$langAllModules."</option>\n";
    $result = db_query($qry, $currentCourseID);
    while ($row = mysql_fetch_assoc($result)) {
        if ($u_module_id == $row['id']) { $selected = 'selected'; } else { $selected = ''; }
        $mod_opts .= '<option '.$selected.' value="'.$row["id"].'">'.$row['name']."</option>\n";
    }
    @mysql_free_result($qry);

    $statsValueOptions =
        '<option value="visits" '.	 (($u_stats_value=='visits')?('selected'):(''))	  .'>'.$langVisits."</option>\n".
        '<option value="duration" '.(($u_stats_value=='duration')?('selected'):('')) .'>'.$langDuration."</option>\n";

  
    $tool_content .= '
    <form method="post">
    &nbsp;&nbsp;
        <table>
        <thead>
        <tr>
            <th>'.$langValueType.'</th>
            <td><select name="u_stats_value">'.$statsValueOptions.'</select></td>
        </tr>

        <tr>
            <th>'.$langStartDate.'</th>
            <td>'."$start_cal".'</td>
        </tr>
        <tr>
            <th>'.$langEndDate.'</th>
            <td>'."$end_cal".'</td>
        </tr>
        <tr>
            <th>'.$langModule.'</th>
            <td><select name="u_module_id">'.$mod_opts.'</select></td>
        </tr>
        </thead>
        </table>
        <br/>
            <input type="submit" name="btnUsage" value="'.$langSubmit.'">
        
    </form>';
        
    }


draw($tool_content, 2, '', $local_head, '');
/*
if ($made_chart) {
		while (ob_get_level() > 0) {
    	 ob_end_flush();
    }    
    ob_flush();
    flush();
    sleep(5);
    unlink ($webDir.$chart_path);
}
*/
?>
