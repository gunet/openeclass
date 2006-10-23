<?php
/*
=============================================================================
           GUnet e-Class 2.0
        E-learning and Course Management Program
================================================================================
        Copyright(c) 2003-2006  Greek Universities Network - GUnet
        Á full copyright notice can be read in "/info/copyright.txt".

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
    usage/favourite.php
    @last update: 2006-06-04 by Vangelis Haniotakis
    @authors list: Vangelis Haniotakis haniotak@ucnet.uoc.gr
==============================================================================
    @Description: 


   
==============================================================================
*/

$require_current_course = TRUE;
$langFiles 				= 'usage';
$require_help 			= true;
$helpTopic 				= 'Usage';

include '../../include/baseTheme.php';
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_USAGE');

$tool_content = '';
$tool_content .= "<a href='usage.php'>".$langUsage."</a> | ";
$tool_content .= "<a href='favourite.php'>".$langFavourite."</a> | ";
$tool_content .= "<a href='oldStats.php'>".$langOldStats."</a><p>&nbsp</p>";

$tool_content .= "<p> $langFavouriteExpl </p>";

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
            'u_user_id' => -1,
            'u_date_start' => strftime('%Y-%m-%d', strtotime('now -15 day')),
            'u_date_end' => strftime('%Y-%m-%d', strtotime('now')),
        );

        foreach ($usage_defaults as $key => $val) {
            if (!isset($_POST[$key])) {
                $$key = $val;
            } else {
                $$key = $_POST[$key];
            }
        }

    $date_fmt = '%Y-%m-%d';
    $date_where = " (date_time BETWEEN '$u_date_start 00:00:00' AND '$u_date_end 23:59:59') ";
    $date_what  = "DATE_FORMAT(MIN(date_time), '$date_fmt') AS date_start, DATE_FORMAT(MAX(date_time), '$date_fmt') AS date_end ";



    if ($u_user_id != -1) {
        $user_where = " (user_id = '$u_user_id') ";
    } else {
        $user_where = " (1) ";
    }

    #check if statistics exist
    $chart_content=0;

    switch ($u_stats_value) {
        case "visits":
            $query = "SELECT module_id, COUNT(*) AS cnt, accueil.rubrique AS name FROM actions ".
            " LEFT JOIN accueil ON actions.module_id = accueil.id  WHERE $date_where AND $user_where GROUP BY module_id";

            $result = db_query($query, $currentCourseID);
   
            $chart = new PieChart(500, 300);
   
            while ($row = mysql_fetch_assoc($result)) {
                $chart->addPoint(new Point($row['name'], $row['cnt']));
                $chart->width += 25;
                $chart_content=1;
            }
       
            $chart->setTitle("$langFavourite");

        break;

        case "duration":
            $query = "SELECT module_id, SUM(duration) AS tot_dur, accueil.rubrique AS name FROM actions ".
            " LEFT JOIN accueil ON actions.module_id = accueil.id  WHERE $date_where AND $user_where GROUP BY module_id";

            $result = db_query($query, $currentCourseID);

            $chart = new PieChart(500, 300);

            while ($row = mysql_fetch_assoc($result)) {
                $chart->addPoint(new Point($row['name'], $row['tot_dur']));
                $chart->width += 25;
                $chart_content=1;
            }

            $chart->setTitle("$langFavourite");
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

    $qry = "SELECT a.user_id, a.nom, a.prenom, a.username, a.email, b.statut
        FROM user AS a LEFT JOIN cours_user AS b ON a.user_id = b.user_id
        WHERE b.code_cours='".$currentCourseID."'";


    $user_opts = '<option value="-1">'.$langAllUsers."</option>\n";
    $result = db_query($qry, $mysqlMainDb);
    while ($row = mysql_fetch_assoc($result)) {
        if ($u_user_id == $row['user_id']) { $selected = 'selected'; } else { $selected = ''; }
        $user_opts .= '<option '.$selected.' value="'.$row["user_id"].'">'.$row['prenom'].' '.$row['nom']."</option>\n";
    }


/*    $qry = "SELECT id, rubrique AS name FROM accueil WHERE define_var != '' AND visible = 1 ORDER BY name ";

    $mod_opts = '<option value="-1">'.$langAllModules."</option>\n";
    $result = db_query($qry, $currentCourseID);
    while ($row = mysql_fetch_assoc($result)) {
        if ($u_module_id == $row['id']) { $selected = 'selected'; } else { $selected = ''; }
        $mod_opts .= '<option '.$selected.' value="'.$row["id"].'">'.$row['name']."</option>\n";
    }
*/

    $statsValueOptions =
        '<option value="visits" '.	 (($u_stats_value=='visits')?('selected'):(''))	  .'>'.$langVisits."</option>\n".
        '<option value="duration" '.(($u_stats_value=='duration')?('selected'):('')) .'>'.$langDuration."</option>\n";

  
    $tool_content .= '
    <form method="post">
    &nbsp;&nbsp;
        <table>
        <tr>
            <td>'.$langValueType.'</td>
            <td><select name="u_stats_value">'.$statsValueOptions.'</select></td>
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
            <td><select name="u_user_id">'.$user_opts.'</select></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td><input type="submit" name="btnUsage" value="'.$langSubmit.'"></td>
        </tr>
        </table>
    </form>';
        
    }


draw($tool_content, 2, '', $local_head, '');

if ($made_chart) {
		while (ob_get_level() > 0) {
     ob_end_flush();
    }
    ob_flush();
    flush();
    sleep(5);
    unlink ($webDir.$chart_path);
}

?>
