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
    usage/userlogins.php
 * @version $Id$
    @last update: 2006-12-27 by Evelthon Prodromou <eprodromou@upnet.gr>
    @authors list: Vangelis Haniotakis haniotak@ucnet.uoc.gr,
                    Ophelia Neofytou ophelia@ucnet.uoc.gr
==============================================================================
    @Description: Shows logins made by a user or all users of a course, during a specific period.
    Takes data from table 'logins' (and also from table 'stat_accueil' if still exists).

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



$tool_content .= "<p> $langUserLogins </p>";


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



        
$usage_defaults = array (
    'u_user_id' => -1,
    'u_date_start' => strftime('%Y-%m-%d', strtotime('now -2 day')),
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
    $user_where = " (a.user_id = '$u_user_id') ";
} else {
    $user_where = " (1) ";
}


$sql_1=" SELECT user_id, ip, date_time FROM logins AS a WHERE ".$date_where." AND ".$user_where." order by date_time desc";

$sql_2=" SELECT a.user_id as user_id, a.nom as nom, a.prenom as prenom, a.username
            FROM user AS a LEFT JOIN cours_user AS b ON a.user_id = b.user_id
            WHERE b.code_cours='".$currentCourseID."' AND ".$user_where;



$sql_3="SHOW TABLES FROM `$currentCourseID` LIKE 'stat_accueil'";
$result_3=db_query($sql_3, $currentCourseID);
$exist_stat_accueil=0;
if (mysql_fetch_assoc($result_3)) {
    $exist_stat_accueil=1;
}

$sql_4="  SELECT host, address, `date` FROM stat_accueil WHERE `date` BETWEEN '$u_date_start 00:00:00' AND '$u_date_end 23:59:59' order by `date` desc";




///Take data from logins
$result_2= db_query($sql_2, $mysqlMainDb);

$users = array();
while ($row = mysql_fetch_assoc($result_2)) {
    $users[$row['user_id']] = $row['nom'].' '.$row['prenom'];
}

$result = db_query($sql_1, $currentCourseID);
$table_cont ='';
while ($row = mysql_fetch_assoc($result)) {
    $user = $users[$row['user_id']];
    $table_cont .= '<tr><td> '.$user. '</td>
                    <td>'.$row['ip'].'</td>
                    <td>'.$row['date_time'].'</td></tr>';
}


//Take data from stat_accueil
 $table2_cont = '';
if ($exist_stat_accueil){
    $result_4= db_query($sql_4, $currentCourseID);
    while ($row = mysql_fetch_assoc($result_4)) {
        $table2_cont .= '<tr><td> '.$row['host']. '</td>
                    <td>'.$row['address'].'</td>
                    <td>'.$row['date'].'</td></tr>';
    }
}


//Records exist?
if ($table_cont) {
    $tool_content .=  '<table><thead>
        <tr> <th>'.$langUser.'</th> <th>'.$langAddress.' </th> <th>'.$langLoginDate.'</th>'.
        $table_cont.'</thead></table>';
        
}
if ($table2_cont) {
    $tool_content .=  '<br><p>'.$langStatAccueil.'</p><table><thead>
        <tr> <th>'.$langHost.'</th> <th>'.$langAddress.' </th> <th>'.$langLoginDate.'</th>'.
        $table2_cont.'</thead></table>';

}
if (!($table_cont || $table2_cont)) {

    $tool_content .= '<p>'.$langNoLogins.'</p>';
}

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



    $qry = "SELECT LEFT(a.nom, 1) AS first_letter
        FROM user AS a LEFT JOIN cours_user AS b ON a.user_id = b.user_id
        WHERE b.code_cours='".$currentCourseID."'
        GROUP BY first_letter ORDER BY first_letter";
    $result = db_query($qry, $mysqlMainDb);

    $letterlinks = '';
    while ($row = mysql_fetch_assoc($result)) {
        $first_letter = $row['first_letter'];
        $letterlinks .= '<a href="?first='.$first_letter.'">'.$first_letter.'</a> ';
    }

    if (isset($_GET['first'])) {
        $firstletter = mysql_real_escape_string($_GET['first']);
        $qry = "SELECT a.user_id, a.nom, a.prenom, a.username, a.email, b.statut
            FROM user AS a LEFT JOIN cours_user AS b ON a.user_id = b.user_id
            WHERE b.code_cours='".$currentCourseID."' AND LEFT(a.nom,1) = '$firstletter'";
    } else {
        $qry = "SELECT a.user_id, a.nom, a.prenom, a.username, a.email, b.statut
            FROM user AS a LEFT JOIN cours_user AS b ON a.user_id = b.user_id
            WHERE b.code_cours='".$currentCourseID."'";
    }


    $user_opts = '<option value="-1">'.$langAllUsers."</option>\n";
    $result = db_query($qry, $mysqlMainDb);
    while ($row = mysql_fetch_assoc($result)) {
        if ($u_user_id == $row['user_id']) { $selected = 'selected'; } else { $selected = ''; }
        $user_opts .= '<option '.$selected.' value="'.$row["user_id"].'">'.$row['prenom'].' '.$row['nom']."</option>\n";
    }

   
    $tool_content .= '
    <form method="post">
    &nbsp;&nbsp;
        <table>
        <thead>
        <tr>
            <th>'.$langStartDate.'</th>
            <td>'."$start_cal".'</td>
        </tr>
        <tr>
            <th>'.$langEndDate.'</th>
            <td>'."$end_cal".'</td>
        </tr>
        <tr>
            <th>'.$langUser.'</th>
            <td>'.$langFirstLetterUser.':<br/>'.$letterlinks.'<br />
            <select name="u_user_id">'.$user_opts.'</select></td>
        </tr>
        </thead>
        </table>
        <br/>
            <input type="submit" name="btnUsage" value="'.$langSubmit.'">
    </form>';


draw($tool_content, 2, '', $local_head, '');

?>
