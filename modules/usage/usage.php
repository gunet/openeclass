<?php
/*
=============================================================================
           GUnet e-Class 2.0
        E-learning and Course Management Program
================================================================================
        Copyright(c) 2003-2006  Greek Universities Network - GUnet
        Α full copyright notice can be read in "/info/copyright.txt".

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
    usage/index.php
    @last update: 2006-05-07 by Vangelis Haniotakis
    @authors list: Vangelis Haniotakis haniotak@ucnet.uoc.gr
==============================================================================
    @Description: Main script for the usage statistics module


    @todo: Make it work.
==============================================================================
*/
//Μετατροπή του εργαλείου για να χρησιμοποιεί το baseTheme
$require_current_course = TRUE;
$langFiles = 'usage';
$require_help = false;
$helpTopic = 'Usage';

// include('../../include/init.php');
include '../../include/baseTheme.php';

include('../../include/action.php');

action::record(MODULE_ID_USAGE);

$tool_content = '';


$dateNow = date("d-m-Y / H:i:s",time());
$nameTools = $langUsage;
$local_style = '
    .month { font-weight : bold; color: #FFFFFF; background-color: #000066;
     padding-left: 15px; padding-right : 15px; }
    .content {position: relative; left: 25px; }';

// add javascripts for jscalendar - haniotak
include('../../include/jscalendar/calendar.php');
if ($language == 'greek') {
    $lang = 'el';
} else if ($language == 'english') {
    $lang = 'en';
}

$jscalendar = new DHTML_Calendar($urlServer.'include/jscalendar/', $lang, 'calendar-win2k-2', false);
$local_head = $jscalendar->get_load_files_code();

//begin_page();


//mysql_select_db($dbname);

if (!$_POST['btnUsage']) {
    require_once "form.php";
} else {
    require_once "results.php";
}

//$tool_content = 'Hello! {foo}';

// echo $tool_content;

draw($tool_content, 2, '', $local_head, '');

//end_page();
?>