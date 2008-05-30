<?php
/**=============================================================================
       	GUnet eClass 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".
        
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
		2.) $tool_content .=  "<form method=\"post\"><tr><td>ΗΜΕΡΟΜΗΝΙΑ</td><td>".$start_cal."</td></tr></form>";
 

==============================================================================*/

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

$jscalendar = new DHTML_Calendar($urlServer.'include/jscalendar/', $lang, 'calendar-blue2', false);
$local_head = $jscalendar->get_load_files_code();

$u_date_end = strftime('%Y-%m-%d', strtotime('now +1 year'));


$end_cal_Work = $jscalendar->make_input_field(
           array('showsTime'      => false,
                 'showOthers'     => true,
                 'ifFormat'       => '%Y-%m-%d',
                 'timeFormat'     => '24'),
           array('style'       => 'width: 100px; color: #840; font-weight:bold; font-size:10px; background-color: #fff; border: 1px dotted #000; text-align: center',
                 'name'        => 'WorkEnd',
                 'value'       => $u_date_end));

function getJsDeadline($deadline) {
	global $language, $lang, $jscalendar, $local_head;
	
	$end_cal_Work_db = $jscalendar->make_input_field(
  	array('showsTime'      => false,
    	'showOthers'     => true,
      'ifFormat'       => '%Y-%m-%d',
      'timeFormat'     => '24'),
    array('style'       => 'width: 100px; color: #840; font-weight:bold; font-size:10px; background-color: #fff; border: 1px dotted #000; text-align: center',
    	'name'        => 'WorkEnd',
      'value'       => $deadline));
	
	return $end_cal_Work_db;
	
}
                 
 
?>
