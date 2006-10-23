<?php
/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        а full copyright notice can be read in "/info/copyright.txt".
        
       	Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
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
==============================================================================*/


/**===========================================================================
	jscalendar.inc.php
	@last update: 19-10-2006 by Dionysios G. Synodinos
	@authors list: Dionysios G. Synodinos <synodinos@gmail.com>
==============================================================================        
    @Description: Library for the pop-up calendar


 		@Comments: For this to work you need to add:

		1.) draw($tool_content, 2, '', $local_head, '');
		2.) $tool_content .=  "<form method=\"post\"><tr><td>глеяолгмиа</td><td>".$start_cal."</td></tr></form>";
 

==============================================================================*/


$dateNow = date("d-m-Y / H:i:s",time());

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

$u_date_start = strftime('%Y-%m-%d %H:%M:%S', strtotime('now -0 day'));
$u_date_end = strftime('%Y-%m-%d %H:%M:%S', strtotime('now +1 year'));

$start_cal_Excercise = $jscalendar->make_input_field(
           array('showsTime'      => true,
                 'showOthers'     => true,
                 'ifFormat'       => '%Y-%m-%d %H:%M:%S',
                 'timeFormat'     => '24'),
           array('style'       => 'width: 15em; color: #840; background-color: #ff8; border: 1px solid #000; text-align: center',
                 'name'        => 'exerciseStartDate',
                 'value'       => $u_date_start));
$end_cal_Excercise = $jscalendar->make_input_field(
           array('showsTime'      => true,
                 'showOthers'     => true,
                 'ifFormat'       => '%Y-%m-%d %H:%M:%S',
                 'timeFormat'     => '24'),
           array('style'       => 'width: 15em; color: #840; background-color: #ff8; border: 1px solid #000; text-align: center',
                 'name'        => 'exerciseEndDate',
                 'value'       => $u_date_end));
                 
 
?>