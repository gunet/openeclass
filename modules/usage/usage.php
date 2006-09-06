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
    usage/usage.php
    @last update: 2006-06-04 by Vangelis Haniotakis
    @authors list: Vangelis Haniotakis haniotak@ucnet.uoc.gr
==============================================================================
    @Description: Main script for the usage statistics module


    @todo: Nothing much; most functionality is already in form.php and results.php
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

$tool_content .= "<a href=".$_SERVER['PHP_SELF'].">".$langUsage."</a> | ";
$tool_content .= "<a href='favourite.php'>".$langFavourite."</a> | ";
$tool_content .= "<a href='oldStats.php'>".$langOldStats."</a><p>&nbsp</p>";



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
if (isset($_POST['u_analyze']) && isset($_POST['user_id']) && $_POST['user_id'] != -1) {
    require_once "analyze.php";
} else {
    if (!extension_loaded('gd')) {
        $tool_content .= "<p>$langGDRequired</p>";
    } else {
        $made_chart = true;
        require_once "results.php";
        require_once "form.php";
    }
}


draw($tool_content, 2, '', $local_head, '');

if ($made_chart) {
    ob_end_flush();
    ob_flush();
    flush();
    sleep(5);
    unlink ($webDir.$chart_path);
}

?>
